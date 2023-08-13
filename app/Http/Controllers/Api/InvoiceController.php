<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Greenter\Report\XmlUtils;

use App\Services\SunatService;
use Illuminate\Support\Facades\Storage;



class InvoiceController extends Controller
{
   public function send(Request $request)
   {

      $data = request()->all();

      $certificate = $request->certProduction ?
         Storage::path('certs/prod/PRODUCCION.txt') :
         Storage::path('certs/desa/DESARROLLO.txt');

      $isDevelopment = !$request->certProduction;

      $rucEmisor = $request['company']['ruc'];


      $this->setTotales($data);

      $sunat = new SunatService();

      $see = $sunat->getSee($certificate, $isDevelopment, $rucEmisor);

      $invoice = $sunat->getInvoice($data);

      $result = $see->send($invoice);

      $response['xml'] = $see->getFactory()->getLastXml();

      $response['hash'] = (new XmlUtils())->getHashSign($response['xml']);

      $response['sunatResponse'] = $sunat->sunatResponse($result);

      return response()->json($response, 200);

   }

   public function setTotales(&$data)
   {
      $details = collect($data['details']);
      $data['mtoOperGravadas'] = $details->where('tipAfeIgv', 10)->sum('mtoValorVenta');
      $data['mtoOperExoneradas'] = $details->where('tipAfeIgv', 20)->sum('mtoValorVenta');
      $data['mtoOperInafectas'] = $details->where('tipAfeIgv', 30)->sum('mtoValorVenta');
      $data['mtoOperExportacion'] = $details->where('tipAfeIgv', 40)->sum('mtoValorVenta');
      $data['mtoOperGratuitas'] = $details->whereNotIn('tipAfeIgv', [10, 20, 30, 40])->sum('mtoValorVenta');

      $data['mtoIGV'] = $details->whereIn('tipAfeIgv', [10, 20, 30, 40])->sum('igv');
      $data['mtoIGVGratuitas'] = $details->whereNotIn('tipAfeIgv', [10, 20, 30, 40])->sum('igv');
      $data['totalImpuestos'] = $data['mtoIGV'];
      $data['valorVenta'] = $details->whereIn('tipAfeIgv', [10, 20, 30, 40])->sum('mtoValorVenta');
      $data['subTotal'] = $data['valorVenta'] + $data['mtoIGV'];
      $data['mtoImpVenta'] = floor($data['subTotal'] * 10) / 10;
      $data['redondeo'] = $data['mtoImpVenta'] - $data['subTotal'];
   }
}