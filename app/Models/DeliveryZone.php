<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryZone extends Model
{
    protected $fillable = [
        'headquarter_id',
        'name',
        'price',
        'is_active',
        'coordinates'
    ];

    protected $casts = [
        'price' => 'float',
        'is_active' => 'boolean'
    ];

    public function headquarter()
    {
        return $this->belongsTo(Headquarter::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
