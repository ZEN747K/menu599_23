<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    use HasFactory;

    protected $fillable = [
        'users_id',
        'table_id', 
        'address_id',
        'total',
        'status',
        'remark',
        'is_pay',
        'is_type',
        'image',
        'is_print_cook'
    ];

    // เพิ่ม relationship นี้
    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    // relationships อื่นๆ ที่มีอยู่แล้ว
    public function details()
    {
        return $this->hasMany(OrdersDetails::class, 'order_id');
    }

    public function table()
    {
        return $this->belongsTo(Table::class, 'table_id');
    }

    public function address()
    {
        return $this->belongsTo(UsersAddress::class, 'address_id');
    }

    public function payGroups()
    {
        return $this->hasMany(PayGroup::class, 'order_id');
    }

    public function riderSend()
    {
        return $this->hasOne(RiderSend::class, 'order_id');
    }
     public function orderdetail()
    {
        return $this->hasMany(OrdersDetails::class, 'order_id');
    }
}