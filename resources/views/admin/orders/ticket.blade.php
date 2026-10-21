<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $currentType = request('type', $type ?? 'boleta');
        $isComanda = $currentType === 'comanda';
        
        $orderNumber = str_pad($order->id, 5, '0', STR_PAD_LEFT);
        $clientName = strtoupper($order->customer_name ?: ($order->user->name ?? 'CLIENTE GENERAL'));
        $clientDoc = $order->invoice_document ?: ($order->user->document_number ?? '');
        $clientDocName = $clientDoc ? "{$clientDoc} / {$clientName}" : $clientName;
        
        $ruc = \App\Models\Setting::get('sunat_ruc', '10737725648');
        $addressEmpresa = \App\Models\Setting::get('company_address', 'Torres Paz 567 Chiclayo');
        $emision = $order->created_at ? $order->created_at->format('d/m/Y H:i A') : now()->format('d/m/Y H:i A');

        $docTitle = 'TICKET DE VENTA';
        $docNumber = "#{$orderNumber}";
        if ($order->sale) {
            $docTitle = $order->sale->document_type === '01' ? 'FACTURA ELECTRÓNICA' : 'BOLETA DE VENTA ELECTRÓNICA';
            $docNumber = "{$order->sale->series}-" . str_pad($order->sale->correlative, 8, '0', STR_PAD_LEFT);
        } elseif ($order->invoice_type === 'factura') {
            $docTitle = 'FACTURA ELECTRÓNICA';
            $docNumber = "F001-{$orderNumber}";
        } elseif ($order->invoice_type === 'boleta') {
            $docTitle = 'BOLETA DE VENTA ELECTRÓNICA';
            $docNumber = "B001-{$orderNumber}";
        }

        $fechaEntrega = $order->delivery_date ? \Carbon\Carbon::parse($order->delivery_date)->format('d/m/Y') : 'Hoy';
        $horaEntrega = $order->delivery_time ?: 'Lo antes posible';
        $programada = "{$fechaEntrega} - {$horaEntrega}";

        $phone = $order->phone ?: ($order->user->phone ?? '');
        $whatsappText = $isComanda ? $order->getFormattedComandaText() : $order->getFormattedInvoiceText();
        $whatsappTargetPhone = preg_replace('/[^0-9]/', '', $phone);
    @endphp
    <title>{{ $isComanda ? "Comanda #{$orderNumber}" : "{$docTitle} {$docNumber}" }} - Gourmetica</title>
    <style>
        @page {
            margin: 0;
            size: auto;
        }
        * {
            box-sizing: border-box;
        }
        body {
            font-family: 'Courier New', Courier, monospace, sans-serif;
            font-size: 12px;
            line-height: 1.35;
            color: #000;
            margin: 0;
            padding: 10px;
            background: #f8fafc;
        }
        .ticket-wrapper {
            width: 300px; /* 80mm standard */
            margin: 0 auto;
            background: #fff;
            padding: 14px 12px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
            border-radius: 6px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        
        .border-b { border-bottom: 1px dashed #000; padding-bottom: 7px; margin-bottom: 7px; }
        .border-t { border-top: 1px dashed #000; padding-top: 7px; margin-top: 7px; }
        .border-box { border: 1px dashed #000; padding: 6px; margin: 8px 0; }
        
        /* Brand Header */
        .brand-logo {
            width: 68px;
            height: 68px;
            border-radius: 50%;
            object-fit: cover;
            display: block;
            margin: 0 auto 6px auto;
        }
        .brand-name {
            font-size: 17px;
            font-weight: 900;
            letter-spacing: 1.5px;
            margin: 0;
        }
        .brand-sub {
            font-size: 11px;
            font-weight: 600;
            margin: 2px 0 4px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .brand-meta {
            font-size: 10px;
            margin: 2px 0;
            line-height: 1.25;
        }

        /* Comanda Header */
        .comanda-badge {
            display: inline-block;
            background: #000;
            color: #fff;
            padding: 3px 10px;
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 1px;
            border-radius: 3px;
            margin-bottom: 4px;
        }

        /* Info fields */
        .field-row {
            margin: 4px 0;
            font-size: 11px;
            line-height: 1.3;
        }
        .field-label {
            font-weight: bold;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
        }
        th {
            font-size: 10px;
            border-bottom: 1px dashed #000;
            padding: 4px 0;
            text-align: left;
        }
        td {
            font-size: 11px;
            padding: 4px 0;
            vertical-align: top;
        }
        .col-qty { width: 14%; font-weight: bold; }
        .col-desc { width: 56%; }
        .col-total { width: 30%; text-align: right; }

        .item-options {
            font-size: 9px;
            margin-top: 2px;
            padding-left: 4px;
            line-height: 1.2;
        }

        /* Totals */
        .totals-row {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            margin: 3px 0;
        }
        .totals-row.grand-total {
            font-size: 15px;
            font-weight: bold;
            border-top: 1px dashed #000;
            padding-top: 6px;
            margin-top: 6px;
        }

        /* Toolbar */
        .toolbar {
            max-width: 480px;
            margin: 0 auto 16px auto;
            background: #1e293b;
            color: #fff;
            padding: 12px;
            border-radius: 12px;
            box-shadow: 0 4px 14px rgba(0,0,0,0.25);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        .toolbar-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 12px;
        }
        .tab-btn {
            flex: 1;
            padding: 8px 12px;
            font-size: 12px;
            font-weight: bold;
            border: 1px solid #334155;
            background: #0f172a;
            color: #94a3b8;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            transition: all 0.2s;
        }
        .tab-btn.active {
            background: #f59e0b;
            color: #0f172a;
            border-color: #f59e0b;
        }
        .toolbar-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .action-btn {
            flex: 1;
            min-width: 110px;
            padding: 8px 12px;
            font-size: 11px;
            font-weight: bold;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            text-decoration: none;
            transition: opacity 0.2s;
        }
        .action-btn:hover { opacity: 0.9; }
        .btn-print { background: #10b981; color: #fff; }
        .btn-copy { background: #3b82f6; color: #fff; }
        .btn-wa { background: #25d366; color: #fff; }
        .btn-close { background: #475569; color: #fff; }

        .toast {
            display: none;
            background: #10b981;
            color: #fff;
            font-size: 11px;
            font-weight: bold;
            text-align: center;
            padding: 6px;
            border-radius: 6px;
            margin-top: 8px;
        }

        /* Print media query */
        @media print {
            .no-print { display: none !important; }
            body {
                background: #fff;
                padding: 0;
            }
            .ticket-wrapper {
                width: 100% !important;
                max-width: 100% !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                padding: 4px 2px !important;
            }
            .brand-logo {
                filter: grayscale(100%) contrast(150%);
            }
        }
    </style>
</head>
<body>

    <!-- Controls Toolbar (Hidden when printing) -->
    <div class="toolbar no-print">
        <div class="toolbar-tabs">
            <a href="?type=boleta" class="tab-btn {{ !$isComanda ? 'active' : '' }}">
                🧾 Boleta / Factura
            </a>
            <a href="?type=comanda" class="tab-btn {{ $isComanda ? 'active' : '' }}">
                📋 Comanda (Cocina/Despacho)
            </a>
        </div>

        <div class="toolbar-actions">
            <button onclick="window.print()" class="action-btn btn-print">
                🖨️ IMPRIMIR TICKET
            </button>
            <button onclick="copyToClipboard()" class="action-btn btn-copy" id="btnCopyText">
                📋 COPIAR WHATSAPP
            </button>
            @if($whatsappTargetPhone)
            <a href="https://wa.me/{{ $whatsappTargetPhone }}?text={{ urlencode($whatsappText) }}" target="_blank" class="action-btn btn-wa">
                💬 ABRIR WHATSAPP
            </a>
            @else
            <a href="https://wa.me/?text={{ urlencode($whatsappText) }}" target="_blank" class="action-btn btn-wa">
                💬 ENVIAR WHATSAPP
            </a>
            @endif
            <button onclick="window.close()" class="action-btn btn-close">
                ✖️ CERRAR
            </button>
        </div>
        <div id="copyToast" class="toast">¡Texto copiado al portapapeles listo para enviar por WhatsApp!</div>
    </div>

    <!-- Printable Ticket Container -->
    <div class="ticket-wrapper">
        @if(!$isComanda)
            {{-- =================================================== --}}
            {{-- FORMATO 2: BOLETA O FACTURA CON LOGO               --}}
            {{-- =================================================== --}}
            <div class="header text-center border-b">
                <img src="{{ asset('images/ticket_logo.png') }}" class="brand-logo" alt="Gourmetica">
                <h1 class="brand-name">GOURMETICA</h1>
                <p class="brand-sub">Pastelería Saludable</p>
                <p class="brand-meta">RUC: {{ $ruc }}</p>
                <p class="brand-meta">{{ $addressEmpresa }}</p>
                <p class="brand-meta"><span class="font-bold">Fecha y Hora de emisión:</span> {{ $emision }}</p>
            </div>

            <div class="info border-b">
                <div class="field-row text-center font-bold" style="font-size: 13px; margin: 4px 0 6px 0;">
                    {{ $docTitle }}<br>
                    <span>{{ $docNumber }}</span>
                </div>
                <div class="field-row">
                    <span class="field-label">Dni/RUC/ Nombre Cliente:</span><br>
                    {{ $clientDocName }}
                </div>
                <div class="field-row">
                    <span class="field-label">Dirección:</span><br>
                    {{ $order->address ?: 'Recojo en tienda' }}
                </div>
                @if($order->phone)
                <div class="field-row">
                    <span class="field-label">Celular:</span> {{ $order->phone }}
                </div>
                @endif
            </div>

            <div class="field-row font-bold" style="font-size: 11px; margin-top: 6px;">
                Detalle Pedido:
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
                            @if($item->options && is_array($item->options))
                                <div class="item-options">
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
                @if($order->delivery_price > 0)
                <div class="totals-row">
                    <span>Subtotal:</span>
                    <span>S/ {{ number_format($order->total - $order->delivery_price, 2) }}</span>
                </div>
                <div class="totals-row">
                    <span>Envío / Delivery:</span>
                    <span>S/ {{ number_format($order->delivery_price, 2) }}</span>
                </div>
                @endif
                <div class="totals-row grand-total">
                    <span>Total S/.</span>
                    <span>S/ {{ number_format($order->total, 2) }}</span>
                </div>
                @if($order->payment_method)
                <div class="totals-row" style="font-size: 10px; margin-top: 4px;">
                    <span>Forma de Pago:</span>
                    <span class="font-bold uppercase">{{ $order->payment_method }}</span>
                </div>
                @endif
            </div>

            <div class="footer text-center border-t">
                <p class="font-bold" style="margin: 3px 0;">¡Gracias por su compra!</p>
                <p style="margin: 2px 0; font-size: 9px;">Gourmetica • Pastelería Saludable</p>
                <p style="margin: 4px 0 0 0; font-size: 8px;">Comprobante emitido electrónicamente</p>
            </div>

        @else
            {{-- =================================================== --}}
            {{-- FORMATO 1: COMANDA (COCINA / DESPACHO / PRODUCCIÓN)  --}}
            {{-- =================================================== --}}
            <div class="header text-center border-b">
                <div class="comanda-badge">COMANDA DE PRODUCCIÓN</div>
                <div style="font-size: 16px; font-weight: 900; letter-spacing: 1px; margin: 3px 0;">
                    Comanda: #{{ $orderNumber }}
                </div>
                <p class="brand-meta">SEDE: {{ strtoupper($order->headquarter->name) }}</p>
                <p class="brand-meta">REGISTRADO: {{ $order->created_at->format('d/m/Y H:i') }}</p>
            </div>

            <div class="info border-b">
                <div class="field-row">
                    <span class="field-label">Nombre Cliente:</span><br>
                    <span style="font-size: 13px; font-weight: bold;">{{ $clientName }}</span>
                </div>
                <div class="field-row" style="margin-top: 6px;">
                    <span class="field-label">Fecha/Hora de entrega programada:</span><br>
                    <span style="font-size: 12px; font-weight: bold; background: #eee; padding: 2px 4px; display: inline-block;">
                        ⏰ {{ $programada }}
                    </span>
                </div>
                <div class="field-row" style="margin-top: 6px;">
                    <span class="field-label">Direccion y celular:</span><br>
                    <span>📍 {{ $order->address ?: 'Recojo en tienda' }}</span><br>
                    <span>📱 Tel/Cel: {{ $order->phone ?: ($order->user->phone ?? 'Sin celular') }}</span>
                </div>
            </div>

            <div class="field-row font-bold" style="font-size: 12px; margin-top: 6px;">
                Detalle Pedido:
            </div>
            <table>
                <thead>
                    <tr>
                        <th class="col-qty">CANT</th>
                        <th class="col-desc" style="width: 86%;">DESCRIPCIÓN / ESPECIFICACIONES</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td class="col-qty" style="font-size: 14px; font-weight: 900;">
                            [{{ $item->quantity }}]
                        </td>
                        <td class="col-desc">
                            <span style="font-size: 12px; font-weight: bold;">{{ strtoupper($item->product->name) }}</span>
                            @if($item->options && is_array($item->options))
                                <div class="item-options" style="font-size: 10px; font-weight: bold; margin-top: 3px;">
                                @foreach($item->options as $key => $val)
                                    ★ {{ strtoupper($key) }}: {{ strtoupper($val) }}<br>
                                @endforeach
                                </div>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @if($order->is_gift || $order->has_card || $order->has_dedication)
            <div class="border-box text-center font-bold" style="font-size: 11px;">
                @if($order->is_gift)
                    <div>🎁 ES REGALO - NO ENVIAR BOLETA CON PRECIO</div>
                @endif
                @if($order->has_card)
                    <div>💌 ADJUNTAR TARJETA FÍSICA PERSONALIZADA</div>
                @endif
                @if($order->has_dedication)
                    <div>✍️ LLEVA DEDICATORIA EN TORTA</div>
                @endif
            </div>
            @endif

            @if($order->notes)
            <div class="border-box" style="font-size: 11px;">
                <span class="font-bold">NOTAS ESPECIALES DEL CLIENTE:</span><br>
                <i>"{{ $order->notes }}"</i>
            </div>
            @endif

            <div class="border-t" style="margin-top: 10px; padding-top: 6px; font-size: 9px;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <span>[ ] Preparado por: _________________</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                    <span>[ ] Hora Salida: __________________</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                    <span>[ ] Entregado / Repartidor: _______</span>
                </div>
            </div>
        @endif
    </div>

    <!-- Hidden Raw Text for Clipboard Copying -->
    <textarea id="rawWhatsappText" style="display: none;">{{ $whatsappText }}</textarea>

    <script>
        function copyToClipboard() {
            const copyText = document.getElementById("rawWhatsappText");
            navigator.clipboard.writeText(copyText.value).then(function() {
                const toast = document.getElementById("copyToast");
                toast.style.display = "block";
                setTimeout(() => {
                    toast.style.display = "none";
                }, 3000);
            }).catch(function(err) {
                // Fallback
                copyText.style.display = "block";
                copyText.select();
                document.execCommand("copy");
                copyText.style.display = "none";
                alert("¡Copiado al portapapeles!");
            });
        }

        // Auto print only if user hasn't explicitly set noprint
        const urlParams = new URLSearchParams(window.location.search);
        if (!urlParams.has('noprint')) {
            window.onload = function() {
                // Short timeout to guarantee image loading
                setTimeout(() => {
                    window.print();
                }, 400);
            };
        }
    </script>
</body>
</html>
