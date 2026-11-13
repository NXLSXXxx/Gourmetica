<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Traits\HasAudit;

class Product extends Model
{
    use HasAudit;
    protected $fillable = [
        'category_id',
        'sort_order',
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
                $baseSlug = \Illuminate\Support\Str::slug($product->name) ?: 'producto';
                $slug = $baseSlug;
                $suffix = 2;

                // Product names are allowed to repeat; the URL identifier is not.
                // Avoid a database unique-key exception when registering a second
                // product with the same name.
                while (static::where('slug', $slug)->exists()) {
                    $slug = $baseSlug . '-' . $suffix++;
                }

                $product->slug = $slug;
            }

            if (static::hasSortOrderColumn() && ($product->sort_order === null || $product->sort_order === '')) {
                $maxOrder = static::where('category_id', $product->category_id)->max('sort_order');
                $product->sort_order = ((int) $maxOrder) + 1;
            }
        });
    }

    protected static ?bool $hasSortOrderColumn = null;

    public static function hasSortOrderColumn(): bool
    {
        if (static::$hasSortOrderColumn === null) {
            try {
                if (\Illuminate\Support\Facades\Schema::hasColumn('products', 'sort_order')) {
                    static::$hasSortOrderColumn = true;
                } elseif (\Illuminate\Support\Facades\Schema::hasTable('products')) {
                    \Illuminate\Support\Facades\Schema::table('products', function (\Illuminate\Database\Schema\Blueprint $table) {
                        $table->unsignedInteger('sort_order')->default(0)->after('category_id');
                    });

                    $categories = \Illuminate\Support\Facades\DB::table('products')
                        ->select('category_id')
                        ->distinct()
                        ->pluck('category_id');

                    foreach ($categories as $categoryId) {
                        $productIds = \Illuminate\Support\Facades\DB::table('products')
                            ->where('category_id', $categoryId)
                            ->orderBy('id', 'asc')
                            ->pluck('id');

                        $position = 1;
                        foreach ($productIds as $productId) {
                            \Illuminate\Support\Facades\DB::table('products')
                                ->where('id', $productId)
                                ->update(['sort_order' => $position++]);
                        }
                    }

                    static::$hasSortOrderColumn = true;
                } else {
                    static::$hasSortOrderColumn = false;
                }
            } catch (\Throwable $e) {
                static::$hasSortOrderColumn = false;
            }
        }

        return static::$hasSortOrderColumn;
    }

    protected static ?bool $hasIsAvailableColumn = true;

    public static function hasIsAvailableColumn(): bool
    {
        if (static::$hasIsAvailableColumn === null) {
            try {
                static::$hasIsAvailableColumn = \Illuminate\Support\Facades\Schema::hasColumn(
                    'headquarter_product',
                    'is_available'
                );
            } catch (\Throwable $e) {
                // Keep the catalog usable while the database is unavailable or
                // before the optional compatibility migration has run.
                static::$hasIsAvailableColumn = false;
            }
        }

        return static::$hasIsAvailableColumn;
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
