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

    // Configuração
    Route::group(['prefix' => '/configurar', 'as' => 'config.'], function () {
        Route::get('/', [App\Http\Controllers\CompanyController::class, 'index'])->name('index');
        Route::post('/atualizar-empresa', [App\Http\Controllers\CompanyController::class, 'updateCompany'])->name('update.company');
    });

    // Perfil
    Route::group(['prefix' => '/meu-perfil', 'as' => 'profile.'], function () {
        Route::get('/', [App\Http\Controllers\UserController::class, 'profile'])->name('index');
        Route::post('/atualizar', [App\Http\Controllers\UserController::class, 'update'])->name('update');
    });

    // Clientes
    Route::group(['prefix' => '/cliente', 'as' => 'client.'], function () {

        Route::get('/', [App\Http\Controllers\ClientController::class, 'index'])->name('index');
        Route::get('/novo', [App\Http\Controllers\ClientController::class, 'create'])->name('create');
        Route::post('/cadastro', [App\Http\Controllers\ClientController::class, 'insert'])->name('insert');

        Route::get('/{id}', [App\Http\Controllers\ClientController::class, 'edit'])->name('edit');
        Route::post('/atualizar', [App\Http\Controllers\ClientController::class, 'update'])->name('update');

    });

    // Equipamento
    Route::group(['prefix' => '/equipamento', 'as' => 'equipament.'], function () {

        Route::get('/', [App\Http\Controllers\EquipamentController::class, 'index'])->name('index');
        Route::get('/novo', [App\Http\Controllers\EquipamentController::class, 'create'])->name('create');
        Route::post('/cadastro', [App\Http\Controllers\EquipamentController::class, 'insert'])->name('insert');

        Route::get('/{id}', [App\Http\Controllers\EquipamentController::class, 'edit'])->name('edit');
        Route::post('/atualizar', [App\Http\Controllers\EquipamentController::class, 'update'])->name('update');

    });

    // Motoristas
    Route::group(['prefix' => '/motorista', 'as' => 'driver.'], function () {

        Route::get('/', [App\Http\Controllers\DriverController::class, 'index'])->name('index');
        Route::get('/novo', [App\Http\Controllers\DriverController::class, 'create'])->name('create');
        Route::post('/cadastro', [App\Http\Controllers\DriverController::class, 'insert'])->name('insert');

        Route::get('/{id}', [App\Http\Controllers\DriverController::class, 'edit'])->name('edit');
        Route::post('/atualizar', [App\Http\Controllers\DriverController::class, 'update'])->name('update');

    });

    // Motoristas
    Route::group(['prefix' => '/veiculo', 'as' => 'vehicle.'], function () {

        Route::get('/', [App\Http\Controllers\VehicleController::class, 'index'])->name('index');
        Route::get('/novo', [App\Http\Controllers\VehicleController::class, 'create'])->name('create');
        Route::post('/cadastro', [App\Http\Controllers\VehicleController::class, 'insert'])->name('insert');

        Route::get('/{id}', [App\Http\Controllers\VehicleController::class, 'edit'])->name('edit');
        Route::post('/atualizar', [App\Http\Controllers\VehicleController::class, 'update'])->name('update');

    });

    // Locação
    Route::group(['prefix' => '/locacao', 'as' => 'rental.'], function () {

        Route::get('/', [App\Http\Controllers\RentalController::class, 'index'])->name('index');
        Route::get('/novo', [App\Http\Controllers\RentalController::class, 'create'])->name('create');
        Route::post('/cadastro', [App\Http\Controllers\RentalController::class, 'insert'])->name('insert');

        Route::get('/{id}', [App\Http\Controllers\RentalController::class, 'edit'])->name('edit');
        Route::post('/atualizar', [App\Http\Controllers\RentalController::class, 'update'])->name('update');

    });

    // Consulta AJAX
    Route::group(['prefix' => '/ajax', 'as' => 'ajax.'], function () {
        Route::group(['prefix' => '/cliente', 'as' => 'client.'], function () {
            Route::post('/buscar', [App\Http\Controllers\ClientController::class, 'fetchClients'])->name('fetch');
            Route::post('/delete', [App\Http\Controllers\ClientController::class, 'delete'])->name('delete');
            Route::get('/visualizar-clientes', [App\Http\Controllers\ClientController::class, 'getClients'])->name('get-clients');
            Route::post('/novo-cliente', [App\Http\Controllers\ClientController::class, 'insert'])->name('new-client');
        });
        Route::group(['prefix' => '/endereco', 'as' => 'address.'], function () {
            Route::post('/visualizar-enderecos', [App\Http\Controllers\AddressController::class, 'getAddresses'])->name('get-addresses');
            Route::post('/visualizar-endereco', [App\Http\Controllers\AddressController::class, 'getAddress'])->name('get-address');
        });
        Route::group(['prefix' => '/equipamento', 'as' => 'equipament.'], function () {
            Route::post('/buscar', [App\Http\Controllers\EquipamentController::class, 'fetchEquipaments'])->name('fetch');
            Route::post('/delete', [App\Http\Controllers\EquipamentController::class, 'delete'])->name('delete');
            Route::post('/visualizar-equipamentos', [App\Http\Controllers\EquipamentController::class, 'getEquipaments'])->name('get-equipaments');
            Route::post('/visualizar-equipamento', [App\Http\Controllers\EquipamentController::class, 'getEquipament'])->name('get-equipament');
            Route::post('/novo-equipamento', [App\Http\Controllers\EquipamentController::class, 'insert'])->name('new-equipament');
            Route::post('/visualizar-estoque', [App\Http\Controllers\EquipamentController::class, 'getStockEquipament'])->name('get-stock');
        });
        Route::group(['prefix' => '/meu-perfil', 'as' => 'profile.'], function () {
            Route::post('/atualizar-imagem', [App\Http\Controllers\UserController::class, 'updateImage'])->name('update.image');
        });
        Route::group(['prefix' => '/configurar', 'as' => 'user.'], function () {
            Route::post('/inativar-usuario', [App\Http\Controllers\UserController::class, 'inactivateUser'])->name('inactivate');
            Route::post('/novo-usuario', [App\Http\Controllers\UserController::class, 'newUser'])->name('new-user');
            Route::get('/visualizar-usuarios', [App\Http\Controllers\UserController::class, 'getUsers'])->name('get-users');
            Route::post('/visualizar-permissao', [App\Http\Controllers\UserController::class, 'getPermissionsUsers'])->name('get-permission');
            Route::post('/atualizar-permissao', [App\Http\Controllers\UserController::class, 'updatePermissionsUsers'])->name('update-permission');
            Route::post('/alterar-tipo', [App\Http\Controllers\UserController::class, 'changeTypeUser'])->name('change-type');
            Route::post('/excluir', [App\Http\Controllers\UserController::class, 'deleteUser'])->name('delete');
            Route::post('/atualizar', [App\Http\Controllers\UserController::class, 'update'])->name('update');
            Route::post('/usuario', [App\Http\Controllers\UserController::class, 'getUser'])->name('get-user');
        });
        Route::group(['prefix' => '/motorista', 'as' => 'driver.'], function () {
            Route::post('/buscar', [App\Http\Controllers\DriverController::class, 'fetchDrivers'])->name('fetch');
            Route::post('/delete', [App\Http\Controllers\DriverController::class, 'delete'])->name('delete');
            Route::post('/novo-motorista', [App\Http\Controllers\DriverController::class, 'insert'])->name('new-driver');
            Route::get('/visualizar-motoristas', [App\Http\Controllers\DriverController::class, 'getDrivers'])->name('get-drivers');
        });
        Route::group(['prefix' => '/veiculo', 'as' => 'vehicle.'], function () {
            Route::post('/buscar', [App\Http\Controllers\VehicleController::class, 'fetchVehicles'])->name('fetch');
            Route::post('/delete', [App\Http\Controllers\VehicleController::class, 'delete'])->name('delete');
        });
    });
});
