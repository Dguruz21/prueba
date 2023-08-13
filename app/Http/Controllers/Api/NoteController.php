<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SunatService;
use App\Traits\SunatTrait;
use Greenter\Report\XmlUtils;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NoteController extends Controller
{
   use SunatTrait;
   public function send(Request $request)
   {
      //****************************************
      $request->validate([
         'company' => 'required|array',
         'company.address' => 'required|array',
         'client' => 'required|array',
         'details' => 'required|array',
         'details.*' => 'required|array'
      ]);
      //****************************************
      $certificate = $request->certProduction ?
         Storage::path('certs/prod/PRODUCCION.txt') :
         Storage::path('certs/desa/DESARROLLO.txt');
      $isDevelopment = !$request->certProduction;
      $rucEmisor = $request['company']['ruc'];
      //****************************************

      $data = request()->all();

      $this->setTotales($data);

      $this->setLegends($data);

      $sunat = new SunatService();

      if (Storage::exists("credentials/{$rucEmisor}.txt")) {

         $fileContents = file_get_contents(Storage::path("credentials/{$rucEmisor}.txt"));
         preg_match('/usuario:(.*),contraseña:(.*)/', $fileContents, $matches);

         $see = $sunat->getSee($certificate, $isDevelopment, $rucEmisor, $matches[1], $matches[2]);

         $note = $sunat->getNote($data);

         $result = $see->send($note);

         $response['xml'] = $see->getFactory()->getLastXml();
         $response['hash'] = (new XmlUtils())->getHashSign($response['xml']);
         $response['sunatResponse'] = $sunat->sunatResponse($result);

         return response()->json($response, 200);

      } else {
         return response()->json([
            'error' => 'No se encontró el usuario y contraseña para el RUC: ' . $rucEmisor
         ]);
      }
   }

   public function xml(Request $request)
   {
      //****************************************
      $request->validate([
         'company' => 'required|array',
         'company.address' => 'required|array',
         'client' => 'required|array',
         'details' => 'required|array',
         'details.*' => 'required|array'
      ]);
      //****************************************
      $certificate = $request->certProduction ?
         Storage::path('certs/prod/PRODUCCION.txt') :
         Storage::path('certs/desa/DESARROLLO.txt');
      $isDevelopment = !$request->certProduction;
      $rucEmisor = $request['company']['ruc'];
      //****************************************

      $data = request()->all();

      $this->setTotales($data);

      $this->setLegends($data);

      $sunat = new SunatService();


      if (Storage::exists("credentials/{$rucEmisor}.txt")) {

         $fileContents = file_get_contents(Storage::path("credentials/{$rucEmisor}.txt"));
         preg_match('/usuario:(.*),contraseña:(.*)/', $fileContents, $matches);

         $see = $sunat->getSee($certificate, $isDevelopment, $rucEmisor, $matches[1], $matches[2]);

         $note = $sunat->getNote($data);

         $response['xml'] = $see->getXmlSigned($note);
         $response['hash'] = (new XmlUtils())->getHashSign($response['xml']);

         return response()->json($response, 200);

      } else {
         return response()->json([
            'error' => 'No se encontró el usuario y contraseña para el RUC: ' . $rucEmisor
         ]);
      }

   }

   public function pdf(Request $request)
   {
      //****************************************
      $request->validate([
         'company' => 'required|array',
         'company.address' => 'required|array',
         'client' => 'required|array',
         'details' => 'required|array',
         'details.*' => 'required|array'
      ]);
      //****************************************
      $certificate = $request->certProduction ?
         Storage::path('certs/prod/PRODUCCION.txt') :
         Storage::path('certs/desa/DESARROLLO.txt');
      $isDevelopment = !$request->certProduction;
      $rucEmisor = $request['company']['ruc'];
      //****************************************

      $data = request()->all();

      $this->setTotales($data);
      $this->setLegends($data);

      $sunat = new SunatService();

      if (Storage::exists("credentials/{$rucEmisor}.txt")) {

         $fileContents = file_get_contents(Storage::path("credentials/{$rucEmisor}.txt"));
         preg_match('/usuario:(.*),contraseña:(.*)/', $fileContents, $matches);

         $see = $sunat->getSee($certificate, $isDevelopment, $rucEmisor, $matches[1], $matches[2]);
         $note = $sunat->getNote($data);

         $response['xml'] = $see->getXmlSigned($note);
         $hash = (new XmlUtils())->getHashSign($response['xml']);

         if (Storage::exists("logos/{$rucEmisor}.png")) {
            return $sunat->getHtmlReport($note, $rucEmisor, $hash);
         } else {
            return response()->json([
               'error' => 'No se encontró el logo para el RUC: ' . $rucEmisor
            ]);
         }

      } else {
         return response()->json([
            'error' => 'No se encontró el usuario y contraseña para el RUC: ' . $rucEmisor
         ]);
      }
   }
}