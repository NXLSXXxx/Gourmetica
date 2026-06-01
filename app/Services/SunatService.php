<?php

namespace App\Services;

use Greenter\Model\Company\Address;
use Greenter\Model\Company\Company;
use Greenter\See;
use Greenter\Ws\Reader\XmlReader;
use Greenter\Model\Sale\PaymentTerms;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Legend;
use App\Models\Setting;

class SunatService
{
    protected $see;

    public function __construct()
    {
        $this->see = new See();
        $this->see->setCertificate(file_get_contents(storage_path('app/sunat/certificate.pem')));
        
        $isProduction = false;
        if (auth('admin')->check()) {
            $user = auth('admin')->user();
            if ($user && $user->headquarter) {
                $isProduction = (bool) ($user->headquarter->is_production ?? false);
            }
        }
        
        $this->see->setService($isProduction ? 'https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService' : 'https://demo-ose.nubefact.com/ol-ti-itcpe/billService');
        
        $ruc = Setting::get('sunat_ruc', '20000000001');
        $user = Setting::get('sunat_user', 'MODDATOS');
        $pass = Setting::get('sunat_pass', 'MODDATOS');
        
        $this->see->setClaveSOL($ruc, $user, $pass);
    }

    public function getCompany()
    {
        $ruc = Setting::get('sunat_ruc', '20000000001');
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

    public function generateInvoice($sale)
    {
        $invoice = new Invoice();
        $invoice->setUblVersion('2.1')
            ->setTipoOperacion('0101') // Venta interna
            ->setTipoDoc($sale->document_type ?? '01') // Factura (01) o Boleta (03)
            ->setSerie($sale->series)
            ->setCorrelativo($sale->correlative)
            ->setFechaEmision(new \DateTime())
            ->setFormaPago((new PaymentTerms())->setTipo('Contado'))
            ->setTipoMoneda('PEN')
            ->setCompany($this->getCompany())
            ->setClient($this->getClient($sale));

        $details = [];
        // Simulating items for now
        $detail = new SaleDetail();
        $detail->setCodProducto('P001')
            ->setUnidad('NIU')
            ->setCantidad(1)
            ->setDescripcion('Consumo Gourmetica')
            ->setMtoValorUnitario($sale->total / 1.18)
            ->setMtoPrecioUnitario($sale->total)
            ->setMtoValorVenta($sale->total / 1.18)
            ->setTipAfeIgv('10') // Gravado
            ->setIgv($sale->total - ($sale->total / 1.18))
            ->setTotalImpuestos($sale->total - ($sale->total / 1.18))
            ->setMtoBaseIgv($sale->total / 1.18)
            ->setPorcentajeIgv(18);

        $details[] = $detail;

        $invoice->setDetails($details)
            ->setMtoOperGravadas($sale->total / 1.18)
            ->setMtoIGV($sale->total - ($sale->total / 1.18))
            ->setTotalImpuestos($sale->total - ($sale->total / 1.18))
            ->setValorVenta($sale->total / 1.18)
            ->setSubTotal($sale->total)
            ->setMtoImpVenta($sale->total);

        $invoice->setLegends([
            (new Legend())
                ->setCode('1000')
                ->setValue('SON ' . $this->montoLetras($sale->total) . ' SOLES')
        ]);

        return $this->see->send($invoice);
    }

    private function getClient($sale)
    {
        $isBoleta = ($sale->document_type ?? '01') === '03';
        return (new \Greenter\Model\Client\Client())
            ->setTipoDoc($isBoleta ? '1' : '6') // 1: DNI, 6: RUC
            ->setNumDoc($isBoleta ? '00000000' : '20405060701')
            ->setRznSocial($sale->user->name ?? 'CLIENTE FINAL');
    }

    private function montoLetras($monto)
    {
        return "CIENTO CINCUENTA Y 00/100";
    }
}
