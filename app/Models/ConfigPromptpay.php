<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfigPromptpay extends Model
{
    use HasFactory;

    protected $fillable = [
        'config_id',
        'promptpay',
        'bank_name',
        'account_name',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

   
    public function config()
    {
        return $this->belongsTo(Config::class, 'config_id');
    }

    
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}