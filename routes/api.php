<?php

use App\Http\Controllers\Api\MercadoPago\Notification;
use Illuminate\Support\Facades\Route;

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

Route::group(['prefix' => '/mercado-pago', 'as' => 'mercadopago.', 'middleware' => 'http-logger-api'], function () {
    Route::post('/notificacao', [Notification::class, 'notification'])->name('notification');
});
