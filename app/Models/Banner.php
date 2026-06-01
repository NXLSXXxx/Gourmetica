<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = [
        'title',
        'subtitle',
        'button_text',
        'button_url',
        'image_path',
        'type',
        'is_active',
        'order_index'
    ];

    public function scopeHero($query)
    {
        return $query->where('type', 'hero')->where('is_active', true)->orderBy('order_index');
    }

    public function scopePromo($query)
    {
        return $query->where('type', 'promo')->where('is_active', true)->orderBy('order_index');
    }
}
