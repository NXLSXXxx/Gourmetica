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
        'delivery_date',
        'delivery_time',
        'phone',
        'email',
        'invoice_type',
        'invoice_document',
        'is_gift',
        'has_card',
        'has_dedication',
        'stock_decremented',
        'transaction_id',
        'payment_payload',
        'nakama_id',
        'nakama_token',
        'nakama_status',
    ];

    protected $casts = [
        'payment_payload' => 'array',
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

    public function sale()
    {
        return $this->hasOne(Sale::class);
    }

    /**
     * Genera el texto formateado de la Comanda según la plantilla Gourmetica:
     * Comanda:
     * Nombre Cliente:
     * Detalle Pedido:
     * Fecha/Hora de entrega programada:
     * Direccion y celular:
     */
    public function getFormattedComandaText(): string
    {
        $orderNumber = str_pad($this->id, 5, '0', STR_PAD_LEFT);
        $clientName = $this->customer_name ?: ($this->user->name ?? 'Cliente General');

        $itemsText = [];
        foreach ($this->items as $item) {
            $line = "• {$item->quantity}x " . ($item->product->name ?? 'Producto');
            if (!empty($item->options) && is_array($item->options)) {
                $opts = [];
                foreach ($item->options as $k => $v) {
                    $opts[] = ucfirst($k) . ": " . $v;
                }
                $line .= " (" . implode(', ', $opts) . ")";
            }
            $itemsText[] = $line;
        }
        $detallePedido = !empty($itemsText) ? implode("\n", $itemsText) : 'Sin productos';

        $fecha = $this->delivery_date ? \Carbon\Carbon::parse($this->delivery_date)->format('d/m/Y') : 'Hoy';
        $hora = $this->delivery_time ?: 'Lo antes posible';
        $programada = "{$fecha} - {$hora}";

        $direccion = $this->address ?: 'Recojo en Sede ' . ($this->headquarter->name ?? 'Central');
        $celular = $this->phone ?: ($this->user->phone ?? 'Sin celular');
        $direccionCelular = "{$direccion} | Cel: {$celular}";

        $text = "Comanda: #{$orderNumber}\n\n"
              . "Nombre Cliente: {$clientName}\n\n"
              . "Detalle Pedido:\n{$detallePedido}\n\n"
              . "Fecha/Hora de entrega programada: {$programada}\n\n"
              . "Direccion y celular: {$direccionCelular}";

        $extras = [];
        if ($this->is_gift) $extras[] = "🎁 Regalo (Sin boleta)";
        if ($this->has_card) $extras[] = "💌 Incluir Tarjeta";
        if ($this->has_dedication) $extras[] = "✍️ Dedicatoria en Torta";
        if (!empty($extras)) {
            $text .= "\n\nExtras: " . implode(" | ", $extras);
        }

        if (!empty($this->notes)) {
            $text .= "\n\nNotas: {$this->notes}";
        }

        return $text;
    }

    /**
     * Genera el texto formateado de Boleta o Factura según la plantilla Gourmetica:
     * Boleta o factura:
     * Dni/RUC/ Nombre Cliente
     * Direccion:
     * Detalle Pedido:
     * Total S/.
     * Logo (imagen)
     * Gourmetica
     * Pastelería Saludable
     * ruc (10737725648)
     * Torres Paz 567 Chiclayo
     * Fecha y Hora de emision
     */
    public function getFormattedInvoiceText(): string
    {
        $orderNumber = str_pad($this->id, 5, '0', STR_PAD_LEFT);
        $clientName = $this->customer_name ?: ($this->user->name ?? 'Cliente General');
        $doc = $this->invoice_document ?: ($this->user->document_number ?? '');
        $dniRucName = $doc ? "{$doc} / {$clientName}" : $clientName;
        $direccion = $this->address ?: 'Recojo en tienda';

        $ruc = \App\Models\Setting::get('sunat_ruc', '10737725648');
        $addressEmpresa = \App\Models\Setting::get('company_address', 'Torres Paz 567 Chiclayo');
        $emision = $this->created_at ? $this->created_at->format('d/m/Y H:i A') : now()->format('d/m/Y H:i A');

        $docType = 'Boleta o factura';
        if ($this->sale) {
            $docType = ($this->sale->document_type === '01' ? 'Factura: ' : 'Boleta: ') . "{$this->sale->series}-" . str_pad($this->sale->correlative, 8, '0', STR_PAD_LEFT);
        } elseif ($this->invoice_type === 'factura') {
            $docType = "Factura: #{$orderNumber}";
        } elseif ($this->invoice_type === 'boleta') {
            $docType = "Boleta: #{$orderNumber}";
        } else {
            $docType = "Boleta o factura: #{$orderNumber}";
        }

        $itemsText = [];
        foreach ($this->items as $item) {
            $lineTotal = number_format($item->price * $item->quantity, 2);
            $line = "• {$item->quantity}x " . ($item->product->name ?? 'Producto') . " (S/ " . number_format($item->price, 2) . ") = S/ {$lineTotal}";
            if (!empty($item->options) && is_array($item->options)) {
                $opts = [];
                foreach ($item->options as $k => $v) {
                    $opts[] = ucfirst($k) . ": " . $v;
                }
                $line .= " [" . implode(', ', $opts) . "]";
            }
            $itemsText[] = $line;
        }
        if ($this->delivery_price > 0) {
            $itemsText[] = "• Costo de Envío: S/ " . number_format($this->delivery_price, 2);
        }
        $detallePedido = !empty($itemsText) ? implode("\n", $itemsText) : 'Sin productos';
        $totalFormatted = number_format($this->total, 2);

        $text = "Gourmetica\n"
              . "Pastelería Saludable\n"
              . "RUC: {$ruc}\n"
              . "{$addressEmpresa}\n"
              . "Fecha y Hora de emision: {$emision}\n"
              . "------------------------------------\n"
              . "{$docType}\n\n"
              . "Dni/RUC/ Nombre Cliente: {$dniRucName}\n"
              . "Direccion: {$direccion}\n\n"
              . "Detalle Pedido:\n{$detallePedido}\n\n"
              . "Total S/. {$totalFormatted}\n"
              . "------------------------------------\n"
              . "¡Gracias por elegir Gourmetica!";

        return $text;
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
