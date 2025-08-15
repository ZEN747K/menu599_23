<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pay extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_number',
        'table_id', 
        'user_id',
        'total',
        'is_type'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ความสัมพันธ์กับ User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ความสัมพันธ์กับ Table
    public function table()
    {
        return $this->belongsTo(Table::class, 'table_id');
    }

    // ความสัมพันธ์กับ PayGroup
    public function payGroups()
    {
        return $this->hasMany(PayGroup::class, 'pay_id');
    }

    // ความสัมพันธ์กับ Orders ผ่าน PayGroup
    public function orders()
    {
        return $this->hasManyThrough(Orders::class, PayGroup::class, 'pay_id', 'id', 'id', 'order_id');
    }
}