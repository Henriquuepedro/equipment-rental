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

    // Equipmento
    Route::group(['prefix' => '/equipamento', 'as' => 'equipment.'], function () {

        Route::get('/', [App\Http\Controllers\EquipmentController::class, 'index'])->name('index');
        Route::get('/novo', [App\Http\Controllers\EquipmentController::class, 'create'])->name('create');
        Route::post('/cadastro', [App\Http\Controllers\EquipmentController::class, 'insert'])->name('insert');

        Route::get('/{id}', [App\Http\Controllers\EquipmentController::class, 'edit'])->name('edit');
        Route::post('/atualizar', [App\Http\Controllers\EquipmentController::class, 'update'])->name('update');

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

    });

    // Impressões
    Route::group(['prefix' => '/impressao', 'as' => 'print.'], function () {

        Route::get('/locacao/{rental}', [App\Http\Controllers\PrintController::class, 'rental'])->name('rental');

    });

    // Impressões
    Route::group(['prefix' => '/residuo', 'as' => 'residue.'], function () {

        Route::get('/', [App\Http\Controllers\ResidueController::class, 'index'])->name('index');

    });

    // Consulta AJAX
    Route::group(['prefix' => '/ajax', 'as' => 'ajax.'], function () {
        Route::group(['prefix' => '/cliente', 'as' => 'client.'], function () {
            Route::post('/buscar', [App\Http\Controllers\ClientController::class, 'fetchClients'])->name('fetch');
            Route::post('/delete', [App\Http\Controllers\ClientController::class, 'delete'])->name('delete');
            Route::get('/visualizar-clientes', [App\Http\Controllers\ClientController::class, 'getClients'])->name('get-clients');
            Route::get('/visualizar-cliente', [App\Http\Controllers\ClientController::class, 'getClient'])->name('get-client');
            Route::post('/novo-cliente', [App\Http\Controllers\ClientController::class, 'insert'])->name('new-client');
        });
        Route::group(['prefix' => '/endereco', 'as' => 'address.'], function () {
            Route::post('/visualizar-enderecos', [App\Http\Controllers\AddressController::class, 'getAddresses'])->name('get-addresses');
            Route::post('/visualizar-endereco', [App\Http\Controllers\AddressController::class, 'getAddress'])->name('get-address');
        });
        Route::group(['prefix' => '/equipamento', 'as' => 'equipment.'], function () {
            Route::post('/buscar', [App\Http\Controllers\EquipmentController::class, 'fetchEquipments'])->name('fetch');
            Route::post('/delete', [App\Http\Controllers\EquipmentController::class, 'delete'])->name('delete');
            Route::post('/visualizar-equipamentos', [App\Http\Controllers\EquipmentController::class, 'getEquipments'])->name('get-equipments');
            Route::post('/visualizar-equipamento', [App\Http\Controllers\EquipmentController::class, 'getEquipment'])->name('get-equipment');
            Route::post('/novo-equipamento', [App\Http\Controllers\EquipmentController::class, 'insert'])->name('new-equipment');
            Route::post('/visualizar-estoque', [App\Http\Controllers\EquipmentController::class, 'getStockEquipment'])->name('get-stock');
            Route::post('/visualizar-preco', [App\Http\Controllers\EquipmentController::class, 'getPriceEquipment'])->name('get-price');
            Route::post('/visualizar-preco-estoque', [App\Http\Controllers\EquipmentController::class, 'getPriceStockEquipment'])->name('get-price-stock');
            Route::post('/visualizar-preco-por-periodo', [App\Http\Controllers\EquipmentController::class, 'getPricePerPeriod'])->name('get-price-per-period');
            Route::post('/visualizar-preco-estoque', [App\Http\Controllers\EquipmentController::class, 'getCheckPriceStockEquipment'])->name('get-price-stock-check');
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
            Route::post('/novo-veiculo', [App\Http\Controllers\VehicleController::class, 'insert'])->name('new-vehicle');
            Route::get('/visualizar-veiculos', [App\Http\Controllers\VehicleController::class, 'getVehicles'])->name('get-vehicles');
            Route::get('/visualizar-veiculo', [App\Http\Controllers\VehicleController::class, 'getVehicle'])->name('get-vehicle');
        });
        Route::group(['prefix' => '/residuo', 'as' => 'residue.'], function () {
            Route::get('/visualizar-residuos', [App\Http\Controllers\ResidueController::class, 'getResidues'])->name('get-residues');
            Route::post('/novo-residuo', [App\Http\Controllers\ResidueController::class, 'insert'])->name('new-residue');
            Route::post('/atualizar-residuo', [App\Http\Controllers\ResidueController::class, 'update'])->name('edit-residue');
            Route::post('/buscar', [App\Http\Controllers\ResidueController::class, 'fetchResidues'])->name('fetch');
            Route::post('/delete', [App\Http\Controllers\ResidueController::class, 'delete'])->name('delete');
        });
        Route::group(['prefix' => '/locacao', 'as' => 'rental.'], function () {
            Route::post('/nova-locacao', [App\Http\Controllers\RentalController::class, 'insert'])->name('new-rental');
            Route::post('/buscar', [App\Http\Controllers\RentalController::class, 'fetchRentals'])->name('fetch');
            Route::post('/delete', [App\Http\Controllers\RentalController::class, 'delete'])->name('delete');
        });
    });
});
