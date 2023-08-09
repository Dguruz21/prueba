<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class RegisterController extends Controller
{
    public function store (Request $request){

         $request->validate([
               'name' => 'required',
               'email' => 'required|email|unique:users,email',
               'password'=> 'required|min:6|confirmed'
         ]);

         $user = User::create([
               'name' => $request->name,
               'email' => $request->email,
               'password' => bcrypt($request->password)
         ]); 

         $token = JWTAuth::fromUser($user);

         return  response()->json([
               'user' => $user,
               'token' => $token,
               'expires_in' => JWTAuth::factory()->getTTL() * 60
         ],201);
    }
}
