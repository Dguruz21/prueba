<?php

namespace App\Services;

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

   public function getSee($certificate, $isDevelopment, $rucEmisor)
   {
      $see = new See();
      $see->setCertificate(file_get_contents($certificate));
      $see->setService(!$isDevelopment ? SunatEndpoints::FE_PRODUCCION : SunatEndpoints::FE_BETA);

      $fileContents = file_get_contents(Storage::path("credentials/{$rucEmisor}.txt"));

      preg_match('/usuario:(.*),contraseña:(.*)/', $fileContents, $matches);

      if (count($matches) === 3) {
         $usuario = $matches[1];
         $contrasenia = $matches[2];
         $see->setClaveSOL($rucEmisor, $usuario, $contrasenia);
      } else {
         return response()->json([
            'error' => 'No se encontró el usuario y contraseña para el RUC: ' . $rucEmisor
         ]);
      }
      return $see;
   }

   public function getInvoice($data)
   {
      return (new Invoice())
         ->setUblVersion($data['ublVersion'])
         ->setTipoOperacion($data['tipoOperacion'])
         ->setTipoDoc($data['tipoDoc'])
         ->setSerie($data['serie'])
         ->setCorrelativo($data['correlativo'])
         ->setFechaEmision(new DateTime($data['fechaEmision']))
         ->setFormaPago(new FormaPagoContado()) // FormaPago: Contado
         ->setTipoMoneda($data['tipoMoneda'])
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
         ->setRuc($company['ruc'])
         ->setRazonSocial($company['razonSocial'])
         ->setNombreComercial($company['nombreComercial'])
         ->setAddress($this->getAddress($company['address']));
   }

   public function getClient($client)
   {
      return (new Client())
         ->setTipoDoc($client['tipoDoc'])
         ->setNumDoc($client['numDoc'])
         ->setRznSocial($client['rznSocial']);
   }

   public function getAddress($address)
   {
      return (new Address())
         ->setUbigueo($address['ubigueo'])
         ->setDepartamento($address['departamento'])
         ->setProvincia($address['provincia'])
         ->setDistrito($address['distrito'])
         ->setUrbanizacion($address['urbanizacion'])
         ->setDireccion($address['direccion'])
         ->setCodLocal($address['codLocal']);
   }

   public function getDetails($details)
   {
      $greenterDetails = [];

      foreach ($details as $detail) {

         $greenterDetails[] = (new SaleDetail())
            ->setTipAfeIgv($detail['tipAfeIgv'])
            ->setCodProducto($detail['codProducto'])
            ->setUnidad($detail['unidad'])
            ->setDescripcion($detail['descripcion'])
            ->setCantidad($detail['cantidad'])
            ->setMtoValorUnitario($detail['mtoValorUnitario'])
            ->setMtoValorVenta($detail['mtoValorVenta'])
            ->setMtoBaseIgv($detail['mtoBaseIgv'])
            ->setPorcentajeIgv($detail['porcentajeIgv'])
            ->setIgv($detail['igv'])
            ->setTotalImpuestos($detail['totalImpuestos'])
            ->setMtoPrecioUnitario($detail['mtoPrecioUnitario']);
      }
      return $greenterDetails;
   }

   public function getLegends($legends)
   {
      $greenterLegends = [];
      foreach ($legends as $legend) {
         $greenterLegends[] = (new Legend())
            ->setCode($legend['code'])
            ->setValue($legend['value']);
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
}