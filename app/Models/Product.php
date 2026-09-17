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
                    ->withPivot('stock', 'price', 'is_available')
                    ->withTimestamps();
    }

    public function getPriceForHeadquarter($headquarterId = null)
    {
        if (!$headquarterId) {
            $headquarterId = session('selected_headquarter_id');
        }

        if ($headquarterId) {
            $hq = $this->relationLoaded('headquarters')
                ? $this->headquarters->firstWhere('id', $headquarterId)
                : $this->headquarters()->where('headquarters.id', $headquarterId)->first();

            if ($hq && $hq->pivot && $hq->pivot->price !== null && (float)$hq->pivot->price > 0) {
                return (float)$hq->pivot->price;
            }
        }

        return (float)$this->base_price;
    }

    public function isAvailableInHeadquarter($headquarterId = null)
    {
        if (!$headquarterId) {
            $headquarterId = session('selected_headquarter_id');
        }

        if (!$headquarterId) {
            return true;
        }

        $hq = $this->relationLoaded('headquarters')
            ? $this->headquarters->firstWhere('id', $headquarterId)
            : $this->headquarters()->where('headquarters.id', $headquarterId)->first();

        return $hq && $hq->pivot && (bool)$hq->pivot->is_available;
    }

    public function getStockForHeadquarter($headquarterId = null)
    {
        if (!$headquarterId) {
            $headquarterId = session('selected_headquarter_id');
        }

        if (!$headquarterId) {
            return 0;
        }

        $hq = $this->relationLoaded('headquarters')
            ? $this->headquarters->firstWhere('id', $headquarterId)
            : $this->headquarters()->where('headquarters.id', $headquarterId)->first();

        return $hq && $hq->pivot ? (int)$hq->pivot->stock : 0;
    }

    public function scopeAvailableInHeadquarter($query, $headquarterId)
    {
        if (!$headquarterId) {
            return $query;
        }

        return $query->whereHas('headquarters', function ($q) use ($headquarterId) {
            $q->where('headquarters.id', $headquarterId)
              ->where('headquarter_product.is_available', true);
        });
    }

    public function options()
    {
        return $this->hasMany(ProductOption::class);
    }

    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }

    public function supplies()
    {
        return $this->belongsToMany(Supply::class, 'recipes')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }
}
