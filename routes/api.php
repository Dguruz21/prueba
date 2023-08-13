<?php


use App\Http\Controllers\Api\NoteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\InvoiceController;


Route::post('register', [RegisterController::class, 'store']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout']);
Route::post('refresh', [AuthController::class, 'refresh']);
Route::post('me', [AuthController::class, 'me']);
Route::get('get-date', function () {
   return date('Y-m-d H:i:s');
});

Route::apiResource('companies', CompanyController::class)->middleware('auth:api');

Route::post('invoices/send', [InvoiceController::class, 'send']);
Route::post('invoices/xml', [InvoiceController::class, 'xml']);
Route::post('invoices/pdf', [InvoiceController::class, 'pdf']);

Route::post('notes/send', [NoteController::class, 'send']);
Route::post('notes/xml', [NoteController::class, 'xml']);
Route::post('notes/pdf', [NoteController::class, 'pdf']);