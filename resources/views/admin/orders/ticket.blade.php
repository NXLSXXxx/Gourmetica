<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket de Pedido #{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        @page { margin: 0; }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            color: #000;
            margin: 0;
            padding: 10px;
            width: 300px; /* 80mm standard width */
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .border-b { border-bottom: 1px dashed #000; padding-bottom: 5px; margin-bottom: 5px; }
        .border-t { border-top: 1px dashed #000; padding-top: 5px; margin-top: 5px; }
        
        .header h1 { font-size: 18px; margin: 0 0 5px 0; letter-spacing: 2px; }
        .header p { margin: 2px 0; font-size: 10px; }
        
        .info { margin: 15px 0; font-size: 11px; }
        .info p { margin: 3px 0; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { text-align: left; font-size: 10px; border-bottom: 1px dashed #000; padding-bottom: 3px; }
        td { font-size: 11px; padding: 3px 0; vertical-align: top; }
        .col-qty { width: 15%; }
        .col-desc { width: 55%; }
        .col-total { width: 30%; text-align: right; }
        
        .totals { margin-top: 10px; }
        .totals-row { display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 3px; }
        .totals-row.grand-total { font-size: 16px; font-weight: bold; margin-top: 5px; border-top: 1px dashed #000; padding-top: 5px; }
        
        .footer { margin-top: 20px; font-size: 10px; }
        .footer p { margin: 2px 0; }
        
        /* Auto-print and hide elements */
        @media print {
            .no-print { display: none; }
            body { width: auto; padding: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print text-center" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #E2B182; border: none; font-weight: bold; cursor: pointer;">IMPRIMIR TICKET</button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #eee; border: none; font-weight: bold; cursor: pointer;">CERRAR</button>
    </div>

    <div class="header text-center border-b">
        <h1>GOURMETICA</h1>
        <p>SEDE: {{ strtoupper($order->headquarter->name) }}</p>
        <p>{{ $order->headquarter->address }}</p>
        <p>RUC: {{ \App\Models\Setting::get('sunat_ruc', '20123456789') }}</p>
    </div>

    <div class="info border-b">
        <p><span class="font-bold">PEDIDO:</span> #{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</p>
        <p><span class="font-bold">FECHA:</span> {{ $order->created_at->format('d/m/Y H:i') }}</p>
        <p><span class="font-bold">CLIENTE:</span> {{ strtoupper($order->user->name) }}</p>
        @if($order->payment_method)
        <p><span class="font-bold">PAGO:</span> {{ strtoupper($order->payment_method) }}</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th class="col-qty">CANT</th>
                <th class="col-desc">DESCRIPCIÓN</th>
                <th class="col-total">TOTAL</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td class="col-qty">{{ $item->quantity }}</td>
                <td class="col-desc">
                    {{ strtoupper($item->product->name) }}
                    @if($item->options)
                        <div style="font-size: 9px; margin-top: 2px;">
                        @foreach($item->options as $key => $val)
                            - {{ strtoupper($key) }}: {{ strtoupper($val) }}<br>
                        @endforeach
                        </div>
                    @endif
                </td>
                <td class="col-total">S/ {{ number_format($item->price * $item->quantity, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals border-t">
        <div class="totals-row grand-total">
            <span>TOTAL</span>
            <span>S/ {{ number_format($order->total, 2) }}</span>
        </div>
    </div>

    <div class="footer text-center border-t">
        <p>¡Gracias por su compra!</p>
        <p>Conservar este comprobante para cualquier reclamo.</p>
        <p style="margin-top: 10px; font-size: 8px;">Sistema Gourmetica POS</p>
    </div>

    <script>
        // Auto-print when loaded
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
