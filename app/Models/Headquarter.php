<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Headquarter extends Model
{
    protected $fillable = ['name', 'address', 'city', 'phone', 'email', 'is_active', 'latitude', 'longitude'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function products()
    {
        $pivotFields = ['stock', 'price'];
        if (Product::hasIsAvailableColumn()) {
            $pivotFields[] = 'is_available';
        }

        return $this->belongsToMany(Product::class)
                    ->withPivot($pivotFields)
                    ->withTimestamps();
    }

    public function deliveryZones()
    {
        return $this->hasMany(DeliveryZone::class);
    }
}
