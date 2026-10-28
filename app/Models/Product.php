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

    protected static ?bool $hasIsAvailableColumn = true;

    public static function hasIsAvailableColumn(): bool
    {
        return true;
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function headquarters()
    {
        $pivotFields = ['stock', 'price'];
        if (static::hasIsAvailableColumn()) {
            $pivotFields[] = 'is_available';
        }

        return $this->belongsToMany(Headquarter::class)
                    ->withPivot($pivotFields)
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

            // Respetar precio específico de la sede incluso si es 0.00
            if ($hq && $hq->pivot && $hq->pivot->price !== null && $hq->pivot->price !== '') {
                return (float)$hq->pivot->price;
            }
        }

        return (float)$this->base_price;
    }

    public function isAvailableInHeadquarter($headquarterId = null, bool $checkStock = false)
    {
        if (!$this->is_active) {
            return false;
        }

        if (!$headquarterId) {
            $headquarterId = session('selected_headquarter_id');
        }

        if (!$headquarterId) {
            return (bool)$this->is_active;
        }

        $hq = $this->relationLoaded('headquarters')
            ? $this->headquarters->firstWhere('id', $headquarterId)
            : $this->headquarters()->where('headquarters.id', $headquarterId)->first();

        if (!$hq || !$hq->pivot) {
            return false;
        }

        $isAvailable = isset($hq->pivot->is_available) ? (bool)$hq->pivot->is_available : true;
        if (!$isAvailable) {
            return false;
        }

        if ($checkStock && (int)($hq->pivot->stock ?? 0) <= 0) {
            return false;
        }

        return true;
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
            $q->where('headquarters.id', $headquarterId);
            if (static::hasIsAvailableColumn()) {
                $q->where('headquarter_product.is_available', true);
            }
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
