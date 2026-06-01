<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\HasAudit;

class Product extends Model
{
    use HasAudit;
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'base_price',
        'image',
        'is_active',
    ];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($product) {
            if (!$product->slug) {
                $product->slug = \Illuminate\Support\Str::slug($product->name);
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function headquarters()
    {
        return $this->belongsToMany(Headquarter::class)
                    ->withPivot('stock', 'price')
                    ->withTimestamps();
    }

    public function options()
    {
        return $this->hasMany(ProductOption::class);
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }
}
