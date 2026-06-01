<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'user_id',
        'headquarter_id',
        'document_type',
        'series',
        'correlative',
        'total',
        'status',
        'sunat_status',
        'sunat_response',
        'xml_path',
        'pdf_path',
        'cdr_path',
        'order_id',
        'table_number',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function headquarter()
    {
        return $this->belongsTo(Headquarter::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public static function createFromOrder($order)
    {
        $existing = self::where('order_id', $order->id)->first();
        if ($existing) {
            return $existing;
        }

        $documentType = '03'; // Boleta
        $series = 'B001';

        $lastSale = self::where('document_type', $documentType)
            ->where('series', $series)
            ->orderBy('correlative', 'desc')
            ->first();

        $correlative = $lastSale ? $lastSale->correlative + 1 : 1;

        return self::create([
            'user_id' => $order->user_id,
            'headquarter_id' => $order->headquarter_id,
            'document_type' => $documentType,
            'series' => $series,
            'correlative' => $correlative,
            'total' => $order->total,
            'status' => 'completed',
            'sunat_status' => 'pending',
            'order_id' => $order->id,
        ]);
    }
}
