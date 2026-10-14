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
        return \Illuminate\Support\Facades\DB::transaction(function () {
            // Bloqueo de fila para evitar condiciones de carrera entre peticiones simultáneas
            $order = self::where('id', $this->id)->lockForUpdate()->first();
            if (!$order || $order->stock_decremented) {
                return;
            }

            foreach ($order->items()->lockForUpdate()->get() as $item) {
                $product = $item->product;
                if ($product) {
                    $pivotRow = \Illuminate\Support\Facades\DB::table('headquarter_product')
                        ->where('product_id', $item->product_id)
                        ->where('headquarter_id', $order->headquarter_id)
                        ->lockForUpdate()
                        ->first();

                    if ($pivotRow) {
                        $qty = intval($item->quantity);
                        $currentStock = (int)$pivotRow->stock;
                        $newStock = max(0, $currentStock - $qty);

                        \Illuminate\Support\Facades\DB::table('headquarter_product')
                            ->where('id', $pivotRow->id)
                            ->update(['stock' => $newStock]);
                    }
                }
            }

            $order->stock_decremented = true;
            $order->save();
            $this->stock_decremented = true;
        });
    }
}
