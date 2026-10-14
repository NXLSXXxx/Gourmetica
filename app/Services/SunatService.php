<?php

namespace App\Services;

use Greenter\Model\Company\Address;
use Greenter\Model\Company\Company;
use Greenter\See;
use Greenter\Model\Sale\PaymentTerms;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Legend;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class SunatService
{
    protected $see;
    protected bool $isProduction = false;

    public function __construct()
    {
        $this->see = new See();

        // 1. Selección explícita y auditable del entorno (Producción vs Demo/Homologación)
        $this->isProduction = (bool) config('services.sunat.production', env('SUNAT_PRODUCTION', false));
        if (!$this->isProduction && auth('admin')->check()) {
            $user = auth('admin')->user();
            if ($user && $user->headquarter && isset($user->headquarter->is_production)) {
                $this->isProduction = (bool) $user->headquarter->is_production;
            }
        }

        $serviceUrl = $this->isProduction
            ? config('services.sunat.endpoint_production', 'https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService')
            : config('services.sunat.endpoint_demo', 'https://demo-ose.nubefact.com/ol-ti-itcpe/billService');

        $this->see->setService($serviceUrl);

        // 2. Certificado digital
        $certPath = config('services.sunat.certificate_path', storage_path('app/sunat/certificate.pem'));
        if (file_exists($certPath)) {
            $this->see->setCertificate(file_get_contents($certPath));
        }

        // 3. Credenciales SOL
        $ruc = Setting::get('sunat_ruc', config('services.sunat.ruc', '10737725648'));
        $user = Setting::get('sunat_user', config('services.sunat.user', 'MODDATOS'));
        $pass = Setting::get('sunat_pass', config('services.sunat.pass', 'MODDATOS'));

        $this->see->setClaveSOL($ruc, $user, $pass);
    }

    public function isProduction(): bool
    {
        return $this->isProduction;
    }

    public function getCompany(): Company
    {
        $ruc = Setting::get('sunat_ruc', config('services.sunat.ruc', '10737725648'));
        $name = Setting::get('company_name', 'GOURMETICA S.A.C.');
        $address = Setting::get('company_address', 'AV. LAS ARTES 123');

        return (new Company())
            ->setRuc($ruc)
            ->setRazonSocial($name)
            ->setNombreComercial('GOURMETICA')
            ->setAddress((new Address())
                ->setUbigueo('150101')
                ->setDepartamento('LIMA')
                ->setProvincia('LIMA')
                ->setDistrito('LIMA')
                ->setUrbanizacion('-')
                ->setDireccion($address));
    }

    public function getClient($sale): \Greenter\Model\Client\Client
    {
        $isFactura = ($sale->document_type ?? '03') === '01';
        $doc = null;
        $name = null;

        if ($sale->order) {
            $name = $sale->order->customer_name;
            $doc = $sale->order->invoice_document;
        }

        if (!$name && $sale->user) {
            $name = $sale->user->name;
        }

        $client = new \Greenter\Model\Client\Client();

        if ($isFactura) {
            // Validación estricta de FACTURA (01):
            // Requiere obligatoriamente un RUC válido de 11 dígitos numéricos
            $docCleaned = preg_replace('/[^0-9]/', '', (string)$doc);
            if (empty($docCleaned) || strlen($docCleaned) !== 11) {
                throw new \InvalidArgumentException(
                    "No se puede emitir una Factura electrónica (tipo 01) sin un RUC válido de 11 dígitos. Documento recibido: '" . ($doc ?? 'vacío') . "'. Corrija los datos tributarios del cliente antes de declarar ante SUNAT."
                );
            }

            if (empty(trim((string)$name))) {
                throw new \InvalidArgumentException(
                    "No se puede emitir una Factura electrónica sin la Razón Social o denominación del comprador."
                );
            }

            $client->setTipoDoc('6')
                ->setNumDoc($docCleaned)
                ->setRznSocial(trim($name));
        } else {
            // Boleta de Venta (03)
            $docCleaned = preg_replace('/[^0-9]/', '', (string)$doc);
            $saleTotal = (float)$sale->total;

            if (!empty($docCleaned)) {
                if (strlen($docCleaned) === 8) {
                    $client->setTipoDoc('1') // DNI
                        ->setNumDoc($docCleaned)
                        ->setRznSocial(trim($name) ?: 'CLIENTE FINAL');
                } elseif (strlen($docCleaned) === 11) {
                    $client->setTipoDoc('6') // RUC en boleta
                        ->setNumDoc($docCleaned)
                        ->setRznSocial(trim($name) ?: 'CLIENTE FINAL');
                } else {
                    throw new \InvalidArgumentException(
                        "El documento proporcionado '{$doc}' no es válido para Boleta de Venta. Debe ser un DNI de 8 dígitos o RUC de 11 dígitos."
                    );
                }
            } else {
                // Sin documento: Permitido por SUNAT únicamente para importes menores a S/ 700.00
                if ($saleTotal >= 700.00) {
                    throw new \InvalidArgumentException(
                        "SUNAT exige identificación obligatoria (DNI o RUC) para boletas de venta con importe igual o superior a S/ 700.00 (Monto de la venta: S/ " . number_format($saleTotal, 2) . ")."
                    );
                }

                $client->setTipoDoc('0')
                    ->setNumDoc('00000000')
                    ->setRznSocial(trim($name) ?: 'CLIENTES VARIOS');
            }
        }

        if ($sale->order && !empty($sale->order->address)) {
            $client->setAddress((new Address())->setDireccion($sale->order->address));
        }

        return $client;
    }

    /**
     * Construye un comprobante electrónico UBL 2.1 Greenter a partir de la venta y sus productos reales.
     */
    public function buildInvoice($sale): Invoice
    {
        $invoice = new Invoice();
        $invoice->setUblVersion('2.1')
            ->setTipoOperacion('0101') // Venta interna
            ->setTipoDoc($sale->document_type ?? '03')
            ->setSerie($sale->series ?: (($sale->document_type ?? '03') === '01' ? 'F001' : 'B001'))
            ->setCorrelativo($sale->correlative ?: 1)
            ->setFechaEmision(new \DateTime())
            ->setFormaPago((new PaymentTerms())->setTipo('Contado'))
            ->setTipoMoneda('PEN')
            ->setCompany($this->getCompany())
            ->setClient($this->getClient($sale));

        $details = [];
        $order = $sale->order;
        $items = ($order && $order->items) ? $order->items()->with('product')->get() : collect();

        if ($items->isEmpty()) {
            throw new \InvalidArgumentException(
                "La venta #{$sale->id} no tiene productos o ítems registrados. SUNAT prohíbe emitir comprobantes sin líneas de detalle de productos reales."
            );
        }

        $totalGravada = 0;
        $totalIgv = 0;

        foreach ($items as $item) {
            $qty = max(1, (float)$item->quantity);
            $linePriceWithIgv = (float)$item->price;
            $lineTotal = round($linePriceWithIgv * $qty, 2);

            $unitValue = round($linePriceWithIgv / 1.18, 4);
            $itemValorVenta = round($unitValue * $qty, 2);
            $itemIgv = round($lineTotal - $itemValorVenta, 2);

            $prodCode = $item->product ? ($item->product->sku ?: 'P' . str_pad($item->product_id, 4, '0', STR_PAD_LEFT)) : 'P001';
            $prodName = $item->product ? $item->product->name : 'Producto Gourmetica';

            $detail = new SaleDetail();
            $detail->setCodProducto($prodCode)
                ->setUnidad('NIU')
                ->setCantidad($qty)
                ->setDescripcion($prodName)
                ->setMtoValorUnitario($unitValue)
                ->setMtoPrecioUnitario($linePriceWithIgv)
                ->setMtoValorVenta($itemValorVenta)
                ->setTipAfeIgv('10') // Gravado - Operación Onerosa
                ->setIgv($itemIgv)
                ->setTotalImpuestos($itemIgv)
                ->setMtoBaseIgv($itemValorVenta)
                ->setPorcentajeIgv(18.00);

            $details[] = $detail;
            $totalGravada += $itemValorVenta;
            $totalIgv += $itemIgv;
        }

        // Servicio de entrega / delivery si existe
        if ($order && $order->delivery_price && (float)$order->delivery_price > 0) {
            $delPriceWithIgv = (float)$order->delivery_price;
            $delUnitValue = round($delPriceWithIgv / 1.18, 4);
            $delValorVenta = round($delUnitValue, 2);
            $delIgv = round($delPriceWithIgv - $delValorVenta, 2);

            $delDetail = new SaleDetail();
            $delDetail->setCodProducto('SERV-DELIVERY')
                ->setUnidad('ZZ')
                ->setCantidad(1)
                ->setDescripcion('Servicio de Delivery')
                ->setMtoValorUnitario($delUnitValue)
                ->setMtoPrecioUnitario($delPriceWithIgv)
                ->setMtoValorVenta($delValorVenta)
                ->setTipAfeIgv('10')
                ->setIgv($delIgv)
                ->setTotalImpuestos($delIgv)
                ->setMtoBaseIgv($delValorVenta)
                ->setPorcentajeIgv(18.00);

            $details[] = $delDetail;
            $totalGravada += $delValorVenta;
            $totalIgv += $delIgv;
        }

        $saleTotal = (float)$sale->total;
        $invoice->setDetails($details)
            ->setMtoOperGravadas(round($totalGravada, 2))
            ->setMtoIGV(round($totalIgv, 2))
            ->setTotalImpuestos(round($totalIgv, 2))
            ->setValorVenta(round($totalGravada, 2))
            ->setSubTotal(round($saleTotal, 2))
            ->setMtoImpVenta(round($saleTotal, 2));

        $invoice->setLegends([
            (new Legend())
                ->setCode('1000')
                ->setValue('SON ' . $this->montoLetras($saleTotal) . ' SOLES')
        ]);

        return $invoice;
    }

    public function generateInvoice($sale)
    {
        $invoice = $this->buildInvoice($sale);
        return $this->see->send($invoice);
    }

    /**
     * Convierte cualquier monto numérico a su expresión literal en castellano con centavos.
     */
    public function montoLetras($amount): string
    {
        $amount = round((float)$amount, 2);
        $entero = (int) floor($amount);
        $centavos = (int) round(($amount - $entero) * 100);
        $centavosStr = str_pad((string)$centavos, 2, '0', STR_PAD_LEFT) . '/100';

        if ($entero === 0) {
            return "CERO CON {$centavosStr}";
        }

        $texto = $this->convertirNumeroEnLetras($entero);
        return trim($texto) . " CON {$centavosStr}";
    }

    protected function convertirNumeroEnLetras(int $num): string
    {
        if ($num === 0) return 'CERO';
        if ($num === 100) return 'CIEN';

        $unidades = ['', 'UN', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
        $especiales = [
            10 => 'DIEZ', 11 => 'ONCE', 12 => 'DOCE', 13 => 'TRECE', 14 => 'CATORCE', 15 => 'QUINCE',
            16 => 'DIECISEIS', 17 => 'DIECISIETE', 18 => 'DIECIOCHO', 19 => 'DIECINUEVE',
            20 => 'VEINTE', 21 => 'VEINTIUN', 22 => 'VEINTIDOS', 23 => 'VEINTITRES', 24 => 'VEINTICUATRO',
            25 => 'VEINTICINCO', 26 => 'VEINTISEIS', 27 => 'VEINTISIETE', 28 => 'VEINTIOCHO', 29 => 'VEINTINUEVE'
        ];
        $decenas = ['', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
        $centenas = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];

        $resultado = '';

        // Millones
        if ($num >= 1000000) {
            $millones = (int) floor($num / 1000000);
            $num %= 1000000;
            if ($millones === 1) {
                $resultado .= 'UN MILLON ';
            } else {
                $resultado .= $this->convertirNumeroEnLetras($millones) . ' MILLONES ';
            }
        }

        // Miles
        if ($num >= 1000) {
            $miles = (int) floor($num / 1000);
            $num %= 1000;
            if ($miles === 1) {
                $resultado .= 'MIL ';
            } else {
                $resultado .= $this->convertirNumeroEnLetras($miles) . ' MIL ';
            }
        }

        // Centenas
        if ($num >= 100) {
            if ($num === 100) {
                $resultado .= 'CIEN ';
                $num = 0;
            } else {
                $c = (int) floor($num / 100);
                $resultado .= $centenas[$c] . ' ';
                $num %= 100;
            }
        }

        // Decenas y unidades
        if ($num > 0) {
            if (isset($especiales[$num])) {
                $resultado .= $especiales[$num] . ' ';
            } else {
                $d = (int) floor($num / 10);
                $u = $num % 10;
                if ($d > 0 && $u > 0) {
                    $resultado .= $decenas[$d] . ' Y ' . $unidades[$u] . ' ';
                } elseif ($d > 0) {
                    $resultado .= $decenas[$d] . ' ';
                } else {
                    $resultado .= $unidades[$u] . ' ';
                }
            }
        }

        return trim($resultado);
    }
}

