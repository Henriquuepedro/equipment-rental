<?php

use App\Http\Controllers\Api\MercadoPago\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Spatie\HttpLogger\Middlewares\HttpLogger;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

Route::group([], function () {
    Route::group(['prefix' => '/mercado-pago', 'as' => 'mercadopago.'], function () {
        Route::post('/notificacao', [Notification::class, 'notification'])->name('notification');
    });
})->middleware(HttpLogger::class);
