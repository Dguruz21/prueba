<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return JWTAuth::user()->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data= $request->validate([
               'razon_social' => 'required|string',
               'ruc' => [
                     'required',
                     'string',
                     'regex:/^(10|20)\d{9}$/'
               ],
               'direccion' => 'required|string',
               'logo' => 'nullable|image',
               'sol_user' => 'required|string',
               'sol_pass' => 'required|string',
               //extension .pem
               'cert' => 'required|file|mimes:pem,txt',
               'client_id' => 'nullable|string',
               'client_secret' => 'nullable|string',
               'production' => 'nullable|boolean',
         ]);
        
         if ($request->hasFile('logo')) {
               $data['logo_path'] = $request->file('logo')->store('logos');
         }

         $data['cert_path'] = $request->file('cert')->store('certs');
         $data['user_id'] = JWTAuth::user()->id;

         $company = Company::create($data);

         return $data;
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function show(Company $company)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Company $company)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Company  $company
     * @return \Illuminate\Http\Response
     */
    public function destroy(Company $company)
    {
        //
    }
}
