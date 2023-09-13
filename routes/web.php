<?php

use App\Http\Middleware\CheckPlan;
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
Route::group(['middleware' => ['auth', CheckPlan::class]], function (){

    Route::get('', [App\Http\Controllers\DashboardController::class, 'dashboard']);
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/plano_expirado', [App\Http\Controllers\UserController::class, 'expiredPlan'])->name('expired_plan');

    // Configuração
    Route::group(['prefix' => '/configurar', 'as' => 'config.'], function () {
        Route::get('/', [App\Http\Controllers\CompanyController::class, 'index'])->name('index');
        Route::post('/atualizar-empresa', [App\Http\Controllers\CompanyController::class, 'updateCompany'])->name('update.company');
        Route::post('/atualizar-configuracao', [App\Http\Controllers\ConfigController::class, 'updateConfig'])->name('update.config');
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

    // Veículo
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
        Route::get('/atualizar/{id}', [App\Http\Controllers\RentalController::class, 'edit'])->name('edit');
        Route::get('/trocar-equipamento/{id}', [App\Http\Controllers\RentalController::class, 'exchange'])->name('exchange');
        Route::post('/cadastro', [App\Http\Controllers\RentalController::class, 'insert'])->name('insert');
        Route::post('/atualizar/{id}', [App\Http\Controllers\RentalController::class, 'update'])->name('update');

    });

    // Impressões
    Route::group(['prefix' => '/impressao', 'as' => 'print.'], function () {

        Route::get('/locacao/{rental}', [App\Http\Controllers\PrintController::class, 'rental'])->name('rental');
        Route::get('/orcamento/{budget}', [App\Http\Controllers\PrintController::class, 'budget'])->name('budget');
        Route::group(['prefix' => '/relatorio'], function () {
            Route::post('/locacao', [App\Http\Controllers\PrintController::class, 'reportRental'])->name('report_rental');
            Route::post('/financeiro', [App\Http\Controllers\PrintController::class, 'reportBill'])->name('report_bill');
        });

    });

    // Impressões
    Route::group(['prefix' => '/residuo', 'as' => 'residue.'], function () {

        Route::get('/', [App\Http\Controllers\ResidueController::class, 'index'])->name('index');

    });

    // Orçamento
    Route::group(['prefix' => '/orcamento', 'as' => 'budget.'], function () {

        Route::get('/', [App\Http\Controllers\BudgetController::class, 'index'])->name('index');
        Route::get('/novo', [App\Http\Controllers\BudgetController::class, 'create'])->name('create');
        Route::post('/cadastro', [App\Http\Controllers\BudgetController::class, 'insert'])->name('insert');
        Route::get('/atualizar/{id}', [App\Http\Controllers\BudgetController::class, 'edit'])->name('edit');
        Route::post('/atualizar/{id}', [App\Http\Controllers\BudgetController::class, 'update'])->name('update');

    });

    // Fornecedores
    Route::group(['prefix' => '/fornecedor', 'as' => 'provider.'], function () {

        Route::get('/', [App\Http\Controllers\ProviderController::class, 'index'])->name('index');
        Route::get('/novo', [App\Http\Controllers\ProviderController::class, 'create'])->name('create');
        Route::post('/cadastro', [App\Http\Controllers\ProviderController::class, 'insert'])->name('insert');

        Route::get('/{id}', [App\Http\Controllers\ProviderController::class, 'edit'])->name('edit');
        Route::post('/atualizar', [App\Http\Controllers\ProviderController::class, 'update'])->name('update');

    });

    // Contas a receber
    Route::group(['prefix' => '/contas-a-receber', 'as' => 'bills_to_receive.'], function () {

        Route::get('/', [App\Http\Controllers\BillsToReceiveController::class, 'index'])->name('index');

    });

    // Contas a pagar
    Route::group(['prefix' => '/contas-a-pagar', 'as' => 'bills_to_pay.'], function () {

        Route::get('/', [App\Http\Controllers\BillsToPayController::class, 'index'])->name('index');
        Route::get('/novo', [App\Http\Controllers\BillsToPayController::class, 'create'])->name('create');
        Route::post('/cadastro', [App\Http\Controllers\BillsToPayController::class, 'insert'])->name('insert');

    });

    // Relatório
    Route::group(['prefix' => '/relatorio', 'as' => 'report.'], function () {

        Route::get('/locacao', [App\Http\Controllers\ReportController::class, 'rental'])->name('rental');
        Route::get('/financeiro', [App\Http\Controllers\ReportController::class, 'bill'])->name('bill');
        Route::get('/cadastro', [App\Http\Controllers\ReportController::class, 'register'])->name('register');

    });

    // Consulta AJAX
    Route::group(['prefix' => '/ajax', 'as' => 'ajax.'], function () {
        Route::group(['prefix' => '/cliente', 'as' => 'client.'], function () {
            Route::post('/buscar', [App\Http\Controllers\ClientController::class, 'fetchClients'])->name('fetch');
            Route::post('/delete', [App\Http\Controllers\ClientController::class, 'delete'])->name('delete');
            Route::get('/visualizar-clientes', [App\Http\Controllers\ClientController::class, 'getClients'])->name('get-clients');
            Route::get('/visualizar-cliente/{client_id?}', [App\Http\Controllers\ClientController::class, 'getClient'])->name('get-client');
            Route::post('/novo-cliente', [App\Http\Controllers\ClientController::class, 'insert'])->name('new-client');
        });
        Route::group(['prefix' => '/endereco', 'as' => 'address.'], function () {
            Route::get('/visualizar-enderecos/{client_id?}', [App\Http\Controllers\AddressController::class, 'getAddresses'])->name('get-addresses');
            Route::get('/visualizar-endereco/{client_id?}/{address_id?}', [App\Http\Controllers\AddressController::class, 'getAddress'])->name('get-address');
        });
        Route::group(['prefix' => '/equipamento', 'as' => 'equipment.'], function () {
            Route::post('/buscar', [App\Http\Controllers\EquipmentController::class, 'fetchEquipments'])->name('fetch');
            Route::post('/delete', [App\Http\Controllers\EquipmentController::class, 'delete'])->name('delete');
            Route::post('/visualizar-equipamentos', [App\Http\Controllers\EquipmentController::class, 'getEquipments'])->name('get-equipments');
            Route::get('/visualizar-equipamento/{id?}/{validStock?}', [App\Http\Controllers\EquipmentController::class, 'getEquipment'])->name('get-equipment');
            Route::post('/novo-equipamento', [App\Http\Controllers\EquipmentController::class, 'insert'])->name('new-equipment');
            Route::post('/visualizar-estoque', [App\Http\Controllers\EquipmentController::class, 'getStockEquipment'])->name('get-stock');
            Route::post('/visualizar-preco', [App\Http\Controllers\EquipmentController::class, 'getPriceEquipment'])->name('get-price');
            Route::post('/visualizar-preco-estoque', [App\Http\Controllers\EquipmentController::class, 'getPriceStockEquipment'])->name('get-price-stock');
            Route::post('/visualizar-preco-por-periodo', [App\Http\Controllers\EquipmentController::class, 'getPricePerPeriod'])->name('get-price-per-period');
            Route::post('/visualizar-preco-estoque', [App\Http\Controllers\EquipmentController::class, 'getCheckPriceStockEquipment'])->name('get-price-stock-check');
            Route::get('/estoque-disponivel/{id?}', [App\Http\Controllers\EquipmentController::class, 'availableStock'])->name('available_stock');
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
            Route::get('/visualizar-veiculo/{id?}', [App\Http\Controllers\VehicleController::class, 'getVehicle'])->name('get-vehicle');
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
            Route::post('/alterar-locacao/{id}', [App\Http\Controllers\RentalController::class, 'update'])->name('update-rental');
            Route::post('/trocar-equipamento/{id}', [App\Http\Controllers\RentalController::class, 'exchangePost'])->name('exchange-rental');
            Route::post('/buscar', [App\Http\Controllers\RentalController::class, 'fetchRentals'])->name('fetch');
            Route::post('/delete', [App\Http\Controllers\RentalController::class, 'delete'])->name('delete');
            Route::post('/quantidade-tipo-locacoes', [App\Http\Controllers\RentalController::class, 'getQtyTypeRentals'])->name('get-qty-type-rentals');
            Route::post('/equipamentos-para-entregar', [App\Http\Controllers\RentalEquipmentController::class, 'getEquipmentsRentalToDeliver'])->name('get-equipments-to-deliver');
            Route::post('/equipamentos-para-retirar', [App\Http\Controllers\RentalEquipmentController::class, 'getEquipmentsRentalToWithdraw'])->name('get-equipments-to-withdraw');
            Route::post('/atualizar-para-entregue', [App\Http\Controllers\RentalEquipmentController::class, 'deliverEquipment'])->name('delivery_equipment');
            Route::post('/atualizar-para-retirado', [App\Http\Controllers\RentalEquipmentController::class, 'withdrawEquipment'])->name('withdrawal_equipment');

            Route::get('/equipamentos/{rental_id}', [App\Http\Controllers\RentalEquipmentController::class, 'getEquipmentsRental'])->name('get_equipments_rental');
            Route::get('/pagamentos/{rental_id}', [App\Http\Controllers\BillsToReceiveController::class, 'getPaymentsRental'])->name('get_payments_rental');
        });
        Route::group(['prefix' => '/orcamento', 'as' => 'budget.'], function () {
            Route::post('/novo-orcamento', [App\Http\Controllers\BudgetController::class, 'insert'])->name('new-rental');
            Route::post('/alterar-orcamento/{id}', [App\Http\Controllers\BudgetController::class, 'update'])->name('update-rental');
            Route::post('/buscar', [App\Http\Controllers\BudgetController::class, 'fetchBudgets'])->name('fetch');
            Route::post('/delete', [App\Http\Controllers\BudgetController::class, 'delete'])->name('delete');
            Route::post('/confirmar', [App\Http\Controllers\BudgetController::class, 'confirm'])->name('confirm');

            Route::get('/equipamentos/{budget_id}', [App\Http\Controllers\BudgetEquipmentController::class, 'getEquipmentsBudget'])->name('get_equipments_budget');
            Route::get('/pagamentos/{budget_id}', [App\Http\Controllers\BudgetPaymentController::class, 'getPaymentsBudget'])->name('get_payments_budget');
        });
        Route::group(['prefix' => '/nacionalidade', 'as' => 'nationality.'], function () {
            Route::get('', [App\Http\Controllers\NationalityController::class, 'getNationalities'])->name('get-nationalities');
        });
        Route::group(['prefix' => '/estado-civil', 'as' => 'marital_status.'], function () {
            Route::get('', [App\Http\Controllers\MaritalStatusController::class, 'getMaritalStatus'])->name('get-marital-status');
        });
        Route::group(['prefix' => '/forma-de-pagamento', 'as' => 'form_payment.'], function () {
            Route::get('', [App\Http\Controllers\FormPaymentController::class, 'getFormPayments'])->name('get-form-payments');
        });
        Route::group(['prefix' => '/fornecedor', 'as' => 'provider.'], function () {
            Route::post('/buscar', [App\Http\Controllers\ProviderController::class, 'fetchProviders'])->name('fetch');
            Route::post('/delete', [App\Http\Controllers\ProviderController::class, 'delete'])->name('delete');
            Route::get('/visualizar-fornecedores', [App\Http\Controllers\ProviderController::class, 'getProviders'])->name('get-providers');
            Route::get('/visualizar-fornecedor/{provider_id?}', [App\Http\Controllers\ProviderController::class, 'getProvider'])->name('get-provider');
            Route::post('/novo-fornecedor', [App\Http\Controllers\ProviderController::class, 'insert'])->name('new-provider');
        });
        Route::group(['prefix' => '/contas-a-receber', 'as' => 'bills_to_receive.'], function () {
            Route::post('/buscar', [App\Http\Controllers\BillsToReceiveController::class, 'fetchRentals'])->name('fetch');
            Route::post('/delete', [App\Http\Controllers\BillsToReceiveController::class, 'delete'])->name('delete');
            Route::post('/quantidade-tipos', [App\Http\Controllers\BillsToReceiveController::class, 'getQtyTypeRentals'])->name('get-qty-type-rentals');
            Route::post('/confirmar-pagamento', [App\Http\Controllers\BillsToReceiveController::class, 'confirmPayment'])->name('confirm_payment');
        });
        Route::group(['prefix' => '/contas-a-pagar', 'as' => 'bills_to_pay.'], function () {
            Route::post('/nova-locacao', [App\Http\Controllers\BillsToPayController::class, 'insert'])->name('new-bill-to-pay');
            Route::post('/buscar', [App\Http\Controllers\BillsToPayController::class, 'fetchBills'])->name('fetch');
            Route::post('/delete', [App\Http\Controllers\BillsToPayController::class, 'delete'])->name('delete');
            Route::post('/quantidade-tipos', [App\Http\Controllers\BillsToPayController::class, 'getQtyTypeBills'])->name('get-qty-type-bills');
            Route::post('/confirmar-pagamento', [App\Http\Controllers\BillsToPayController::class, 'confirmPayment'])->name('confirm_payment');
        });
        Route::group(['prefix' => '/exportar', 'as' => 'export.'], function () {
            Route::get('/fields/{option}', [App\Http\Controllers\ExportController::class, 'getFields'])->name('client_fields');
        });
    });

    // Exportação
    Route::group(['prefix' => '/exportar', 'as' => 'export.'], function () {

        Route::post('/cadastro', [App\Http\Controllers\ExportController::class, 'register'])->name('register');

    });
});
