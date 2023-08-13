<?php

namespace App\Services;

use Greenter\Model\Sale\Note;
use Greenter\Report\HtmlReport;
use Greenter\Report\Resolver\DefaultTemplateResolver;
use Greenter\See;
use Greenter\Ws\Services\SunatEndpoints;

use DateTime;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;
use Greenter\Model\Sale\FormaPagos\FormaPagoContado;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Legend;
use Illuminate\Support\Facades\Storage;

class SunatService
{

   public function getSee($certificate, $isDevelopment, $rucEmisor, $usuario, $contrasenia)
   {
      $see = new See();
      $see->setCertificate(file_get_contents($certificate));
      $see->setService(!$isDevelopment ? SunatEndpoints::FE_PRODUCCION : SunatEndpoints::FE_BETA);
      $see->setClaveSOL($rucEmisor, $usuario, $contrasenia);
      return $see;
   }

   public function getInvoice($data)
   {
      return (new Invoice())
         ->setUblVersion($data['ublVersion'] ?? '2.1')
         ->setTipoOperacion($data['tipoOperacion'] ?? null)
         ->setTipoDoc($data['tipoDoc'] ?? null)
         ->setSerie($data['serie'] ?? null)
         ->setCorrelativo($data['correlativo'] ?? null)
         ->setFechaEmision(new DateTime($data['fechaEmision']) ?? null)
         ->setFormaPago(new FormaPagoContado()) // FormaPago: Contado
         ->setTipoMoneda($data['tipoMoneda'] ?? null)
         ->setCompany($this->getCompany($data['company']))
         ->setClient($this->getClient($data['client']))

         //mto operaciones gravadas
         ->setMtoOperGravadas($data['mtoOperGravadas'])
         ->setMtoOperExoneradas($data['mtoOperExoneradas'])
         ->setMtoOperInafectas($data['mtoOperInafectas'])
         ->setMtoOperExportacion($data['mtoOperExportacion'])
         ->setMtoOperGratuitas($data['mtoOperGratuitas'])


         //impuestos

         ->setMtoIGV($data['mtoIGV'])
         ->setMtoIGVGratuitas($data['mtoIGVGratuitas'])
         ->setIcbper($data['icbper'])
         ->setTotalImpuestos($data['totalImpuestos'])


         //totales
         ->setValorVenta($data['valorVenta'])
         ->setSubTotal($data['subTotal'])
         ->setRedondeo($data['redondeo'])
         ->setMtoImpVenta($data['mtoImpVenta'])


         ->setDetails($this->getDetails($data['details']))
         ->setLegends($this->getLegends($data['legends']));
   }

   public function getNote($data)
   {
      return (new Note)
         ->setUblVersion($data['ublVersion'] ?? '2.1')
         ->setTipoDoc($data['tipoDoc'] ?? null)
         ->setSerie($data['serie'] ?? null)
         ->setCorrelativo($data['correlativo'] ?? null)
         ->setFechaEmision(new DateTime($data['fechaEmision']) ?? null)
         ->setTipDocAfectado($data['tipDocAfectado'] ?? null)
         ->setNumDocfectado($data['numDocfectado'] ?? null)
         ->setCodMotivo($data['codMotivo'] ?? null)
         ->setDesMotivo($data['desMotivo'] ?? null)
         ->setTipoMoneda($data['tipoMoneda'] ?? null)
         ->setCompany($this->getCompany($data['company']))
         ->setClient($this->getClient($data['client']))

         //mto operaciones gravadas
         ->setMtoOperGravadas($data['mtoOperGravadas'])
         ->setMtoOperExoneradas($data['mtoOperExoneradas'])
         ->setMtoOperInafectas($data['mtoOperInafectas'])
         ->setMtoOperExportacion($data['mtoOperExportacion'])
         ->setMtoOperGratuitas($data['mtoOperGratuitas'])

         //impuestos
         ->setMtoIGV($data['mtoIGV'])
         ->setMtoIGVGratuitas($data['mtoIGVGratuitas'])
         ->setIcbper($data['icbper'])
         ->setTotalImpuestos($data['totalImpuestos'])

         //totales
         ->setValorVenta($data['valorVenta'])
         ->setSubTotal($data['subTotal'])
         ->setRedondeo($data['redondeo'])
         ->setMtoImpVenta($data['mtoImpVenta'])

         ->setDetails($this->getDetails($data['details']))
         ->setLegends($this->getLegends($data['legends']));

   }

   public function getCompany($company)
   {
      return (new Company())
         ->setRuc($company['ruc'] ?? null)
         ->setRazonSocial($company['razonSocial'] ?? null)
         ->setNombreComercial($company['nombreComercial'] ?? null)
         ->setAddress($this->getAddress($company['address']));
   }

   public function getClient($client)
   {
      return (new Client())
         ->setTipoDoc($client['tipoDoc'] ?? null)
         ->setNumDoc($client['numDoc'] ?? null)
         ->setRznSocial($client['rznSocial'] ?? null);
   }

   public function getAddress($address)
   {
      return (new Address())
         ->setUbigueo($address['ubigueo'] ?? null)
         ->setDepartamento($address['departamento'] ?? null)
         ->setProvincia($address['provincia'] ?? null)
         ->setDistrito($address['distrito'] ?? null)
         ->setUrbanizacion($address['urbanizacion'] ?? null)
         ->setDireccion($address['direccion'] ?? null)
         ->setCodLocal($address['codLocal'] ?? null);
   }

   public function getDetails($details)
   {
      $greenterDetails = [];

      foreach ($details as $detail) {

         $greenterDetails[] = (new SaleDetail())
            ->setTipAfeIgv($detail['tipAfeIgv'] ?? null)
            ->setCodProducto($detail['codProducto'] ?? null)
            ->setUnidad($detail['unidad'] ?? null)
            ->setDescripcion($detail['descripcion'] ?? null)
            ->setCantidad($detail['cantidad'] ?? null)
            ->setMtoValorUnitario($detail['mtoValorUnitario'] ?? null)
            ->setMtoValorVenta($detail['mtoValorVenta'] ?? null)
            ->setMtoBaseIgv($detail['mtoBaseIgv'] ?? null)
            ->setPorcentajeIgv($detail['porcentajeIgv'] ?? null)
            ->setIgv($detail['igv'] ?? null)
            ->setFactorIcbper($detail['factorIcbper'] ?? null)
            ->setIcbper($detail['icbper'] ?? null)
            ->setTotalImpuestos($detail['totalImpuestos'] ?? null)
            ->setMtoPrecioUnitario($detail['mtoPrecioUnitario'] ?? null);
      }
      return $greenterDetails;
   }

   public function getLegends($legends)
   {
      $greenterLegends = [];
      foreach ($legends as $legend) {
         $greenterLegends[] = (new Legend())
            ->setCode($legend['code'] ?? null)
            ->setValue($legend['value'] ?? null);
      }
      return $greenterLegends;

   }

   public function sunatResponse($result)
   {

      $response['success'] = $result->isSuccess();

      if (!$response['success']) {
         $response['error'] = [
            'code' => $result->getError()->getCode(),
            'message' => $result->getError()->getMessage()
         ];
         return $response;
      }

      $response['cdrZip'] = base64_encode($result->getCdrZip());

      $cdr = $result->getCdrResponse();

      $response['cdrResponse'] = [
         'code' => (int) $cdr->getCode(),
         'description' => $cdr->getDescription(),
         'notes' => $cdr->getNotes()
      ];

      return $response;

   }

   public function getHtmlReport($invoice, $rucEmisor, $hash)
   {
      $report = new HtmlReport();
      $resolver = new DefaultTemplateResolver();

      $report->setTemplate($resolver->getTemplate($invoice));

      $params = [
         'system' => [
            'logo' => file_get_contents(Storage::path("logos/{$rucEmisor}.png")),
            // Logo de Empresa
            'hash' => $hash,
            // Valor Resumen 
         ],
         'user' => [
            'header' => 'Telf: <b>(01) 123375</b>',
            // Texto que se ubica debajo de la dirección de empresa
            'extras' => [
               // Leyendas adicionales
               ['name' => 'CONDICION DE PAGO', 'value' => 'Efectivo'],
               ['name' => 'VENDEDOR', 'value' => 'SELLER'],
            ],
            'footer' => '<p>Nro Resolucion: <b>1010155</b></p>'
         ]
      ];

      $html = $report->render($invoice, $params);

      return $html;
   }
}