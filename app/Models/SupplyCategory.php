<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Traits\HasAudit;

class SupplyCategory extends Model
{
    use HasAudit;

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($category) {
            if (!$category->slug) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function supplies()
    {
        return $this->hasMany(Supply::class);
    }
}
