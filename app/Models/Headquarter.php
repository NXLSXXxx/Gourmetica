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
        return $this->belongsToMany(Product::class)
                    ->withPivot('stock', 'price')
                    ->withTimestamps();
    }

    public function deliveryZones()
    {
        return $this->hasMany(DeliveryZone::class);
    }
}
