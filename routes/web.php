<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();

//Route::get('/home', function() {
//    return view('dashboard.home');
//})->name('home')->middleware('auth');

/** ROTAS AUTENTICADO */
Route::group(['middleware' => 'auth'], function (){

    Route::get('', [App\Http\Controllers\DashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'dashboard'])->name('dashboard');

    // Clientes
    Route::group(['prefix' => '/clientes', 'as' => 'client.'], function () {

        Route::get('/', [App\Http\Controllers\ClientController::class, 'index'])->name('index');
        Route::get('/novo', [App\Http\Controllers\ClientController::class, 'create'])->name('create');
        Route::post('/cadastro', [App\Http\Controllers\ClientController::class, 'insert'])->name('insert');

        Route::get('/{id}', [App\Http\Controllers\ClientController::class, 'edit'])->name('edit');
        Route::post('/atualizar', [App\Http\Controllers\ClientController::class, 'update'])->name('update');

    });
    // Clientes
    Route::group(['prefix' => '/ajax', 'as' => 'ajax.'], function () {
        Route::group(['prefix' => '/clientes', 'as' => 'client.'], function () {
            Route::post('/buscar', [App\Http\Controllers\ClientController::class, 'fetchClients'])->name('fetch');
            Route::post('/delete', [App\Http\Controllers\ClientController::class, 'delete'])->name('delete');
        });
    });
});
