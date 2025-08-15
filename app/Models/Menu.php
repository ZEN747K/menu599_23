<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Menu extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'categories_id',
        'base_price',
        'detail',
        'is_active',
        'has_time_restriction',
        'available_from',
        'available_until',
        'available_days',
        'is_out_of_stock',
        'stock_quantity',
        'unavailable_message'
    ];

    protected $casts = [
        'available_days' => 'array',
        'is_active' => 'boolean',
        'has_time_restriction' => 'boolean',
        'is_out_of_stock' => 'boolean',
        'available_from' => 'datetime:H:i',
        'available_until' => 'datetime:H:i',
    ];

    
    public function category()
    {
        return $this->belongsTo(Categories::class, 'categories_id')->withTrashed();
    }

    public function files()
    {
        return $this->hasOne(MenuFiles::class, 'menu_id');
    }

    public function option()
    {
        return $this->hasMany(MenuTypeOption::class, 'menu_id');
    }

    public function typeOptions()
    {
        return $this->hasMany(MenuTypeOption::class, 'menu_id');
    }

   
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeInStock($query)
    {
        return $query->where('is_out_of_stock', 0);
    }

  
    public function scopeAvailableNow($query)
    {
        $now = Carbon::now();
        $currentTime = $now->format('H:i:s');
        $currentDay = $now->dayOfWeek; 
        
        return $query->where('is_active', 1)
                    ->where('is_out_of_stock', 0)
                    ->where(function($q) use ($currentTime, $currentDay) {
                        $q->where('has_time_restriction', 0)
                          ->orWhere(function($q2) use ($currentTime, $currentDay) {
                              $q2->where('has_time_restriction', 1)
                                 ->where(function($q3) use ($currentTime) {
                                     $q3->whereNull('available_from')
                                        ->orWhere('available_from', '<=', $currentTime);
                                 })
                                 ->where(function($q4) use ($currentTime) {
                                     $q4->whereNull('available_until')
                                        ->orWhere('available_until', '>=', $currentTime);
                                 })
                                 ->where(function($q5) use ($currentDay) {
                                     $q5->whereNull('available_days')
                                        ->orWhereJsonContains('available_days', $currentDay);
                                 });
                          });
                    });
    }

    /**
     * ตรวจสอบว่าเมนูสามารถสั่งได้หรือไม่
     */
    public function isAvailable()
    {
        // ตรวจสอบสถานะพื้นฐาน
        if (!$this->is_active || $this->is_out_of_stock) {
            return false;
        }

        // ตรวจสอบ soft delete
        if ($this->deleted_at) {
            return false;
        }

        // ถ้าไม่มีการจำกัดเวลา
        if (!$this->has_time_restriction) {
            return true;
        }

        $now = Carbon::now();
        $currentTime = $now->format('H:i:s');
        $currentDay = $now->dayOfWeek;

        // ตรวจสอบเวลา
        if ($this->available_from && $currentTime < $this->available_from->format('H:i:s')) {
            return false;
        }

        if ($this->available_until && $currentTime > $this->available_until->format('H:i:s')) {
            return false;
        }

        // ตรวจสอบวัน
        if ($this->available_days && !in_array($currentDay, $this->available_days)) {
            return false;
        }

        return true;
    }

    /**
     * ข้อความสถานะการขาย
     */
    public function getAvailabilityMessage()
    {
        if ($this->deleted_at) {
            return 'เมนูนี้ถูกลบแล้ว';
        }

        if (!$this->is_active) {
            return $this->unavailable_message ?: 'เมนูนี้ปิดขายชั่วคราว';
        }

        if ($this->is_out_of_stock) {
            return $this->unavailable_message ?: 'สินค้าหมด';
        }

        if (!$this->isAvailable()) {
            if ($this->available_from && $this->available_until) {
                return "ขายเวลา {$this->available_from->format('H:i')} - {$this->available_until->format('H:i')}";
            }
            return $this->unavailable_message ?: 'ไม่ได้อยู่ในช่วงเวลาขาย';
        }

        return 'พร้อมขาย';
    }

    /**
     * รายชื่อวันในสัปดาห์
     */
    public static function getDayNames()
    {
        return [
            0 => 'อาทิตย์',
            1 => 'จันทร์',
            2 => 'อังคาร',
            3 => 'พุธ',
            4 => 'พฤหัสบดี',
            5 => 'ศุกร์',
            6 => 'เสาร์'
        ];
    }

    /**
     * แสดงวันที่ขาย
     */
    public function getAvailableDaysText()
    {
        if (!$this->available_days) {
            return 'ทุกวัน';
        }

        $dayNames = self::getDayNames();
        $availableDayNames = array_map(function($day) use ($dayNames) {
            return $dayNames[$day];
        }, $this->available_days);

        return implode(', ', $availableDayNames);
    }

    /**
     * ตรวจสอบสถานะการลบ (Soft Delete)
     */
    public function scopeWithoutDeleted($query)
    {
        return $query->whereNull('deleted_at');
    }

    /**
     * ตรวจสอบจำนวนสต็อกคงเหลือ
     */
    public function hasStock($quantity = 1)
    {
        if ($this->stock_quantity === null) {
            return true; // ไม่จำกัดจำนวน
        }

        return $this->stock_quantity >= $quantity;
    }

    /**
     * ลดจำนวนสต็อก
     */
    public function decreaseStock($quantity = 1)
    {
        if ($this->stock_quantity !== null) {
            $this->stock_quantity = max(0, $this->stock_quantity - $quantity);
            
            if ($this->stock_quantity <= 0) {
                $this->is_out_of_stock = true;
            }
            
            $this->save();
        }
    }

    /**
     * เพิ่มจำนวนสต็อก
     */
    public function increaseStock($quantity = 1)
    {
        if ($this->stock_quantity !== null) {
            $this->stock_quantity += $quantity;
            
            if ($this->stock_quantity > 0) {
                $this->is_out_of_stock = false;
            }
            
            $this->save();
        }
    }
}