<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'customer_name',
        'headquarter_id',
        'total',
        'status',
        'address',
        'latitude',
        'longitude',
        'payment_method',
        'payment_status',
        'delivery_zone_id',
        'delivery_price',
        'nakama_id',
        'nakama_token',
        'nakama_status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function headquarter()
    {
        return $this->belongsTo(Headquarter::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function deliveryZone()
    {
        return $this->belongsTo(DeliveryZone::class);
    }

    public function decrementProductStock()
    {
        if ($this->stock_decremented) {
            return;
        }

        foreach ($this->items as $item) {
            $product = $item->product;
            if ($product) {
                $hqRelation = $product->headquarters()->where('headquarter_id', $this->headquarter_id)->first();
                if ($hqRelation) {
                    $currentStock = $hqRelation->pivot->stock;
                    $newStock = max(0, $currentStock - intval($item->quantity));
                    $product->headquarters()->updateExistingPivot($this->headquarter_id, ['stock' => $newStock]);
                }
            }
        }

        $this->stock_decremented = true;
        $this->save();
    }
}
