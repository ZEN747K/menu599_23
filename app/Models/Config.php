<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'color1',
        'color2',
        'color_font',
        'color_category',
        'promptpay',
        'image_bg',
        'image_qr'
    ];

 
    public function promptpays()
    {
        return $this->hasMany(ConfigPromptpay::class, 'config_id');
    }

   
    public function activePromptpay()
    {
        return $this->hasOne(ConfigPromptpay::class, 'config_id')->where('is_active', 1);
    }
}