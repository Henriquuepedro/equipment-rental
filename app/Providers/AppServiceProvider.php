<?php

namespace App\Providers;

use App\Models\Address;
use App\Models\BillToPay;
use App\Models\BillToPayPayment;
use App\Models\Budget;
use App\Models\BudgetEquipment;
use App\Models\BudgetPayment;
use App\Models\BudgetResidue;
use App\Models\Client;
use App\Models\Config;
use App\Models\Driver;
use App\Models\Equipment;
use App\Models\EquipmentWallet;
use App\Models\Plan;
use App\Models\PlanHistory;
use App\Models\PlanPayment;
use App\Models\Provider;
use App\Models\Rental;
use App\Models\RentalEquipment;
use App\Models\RentalPayment;
use App\Models\RentalResidue;
use App\Models\Residue;
use App\Models\Vehicle;
use App\Models\Permission;
use App\Models\User;
use App\Observers\AddressObserver;
use App\Observers\BillToPayObserver;
use App\Observers\BillToPayPaymentObserver;
use App\Observers\BudgetObserver;
use App\Observers\BudgetEquipmentObserver;
use App\Observers\BudgetPaymentObserver;
use App\Observers\BudgetResidueObserver;
use App\Observers\ClientObserver;
use App\Observers\ConfigObserver;
use App\Observers\DriverObserver;
use App\Observers\EquipmentObserver;
use App\Observers\EquipmentWalletObserver;
use App\Observers\PlanObserver;
use App\Observers\PlanHistoryObserver;
use App\Observers\PlanPaymentObserver;
use App\Observers\ProviderObserver;
use App\Observers\RentalObserver;
use App\Observers\RentalEquipmentObserver;
use App\Observers\RentalPaymentObserver;
use App\Observers\RentalResidueObserver;
use App\Observers\ResidueObserver;
use App\Observers\VehicleObserver;
use App\Observers\CompanyObserver;
use App\Observers\UserObserver;
use DateTime;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Contracts\View\View;
use App\Models\Company;
use Illuminate\Support\Facades\URL;

class
AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('path.public', function() {
            return base_path().'/public_html';
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadObservers();
        Schema::defaultStringLength(191);

        if (env('APP_ENV') !== 'local') {
            URL::forceScheme('https');
        }

        // Variaveis para serem usadas em todas as views
        // Definir um array chamado settings contendo a suas
        // respectivas possições para variaveis
        view()->composer('*',function( View $view ) {
            $settings = array('style_template' => User::$STYLE_TEMPLATE['black']);

            if (auth()->user()) {
                $company = new Company();
                $dataCompany = $company->getCompany(auth()->user()->__get('company_id'));

                $settings['img_profile'] = asset(auth()->user()->__get('profile') ? "assets/images/profile/" . auth()->user()->__get('id') . "/" . auth()->user()->__get('profile') : "assets/images/system/profile.png");
                $settings['img_company'] = asset($dataCompany->logo ? "assets/images/company/$dataCompany->id/$dataCompany->logo" : "assets/images/system/company.png");
                $settings['name_company'] = $dataCompany->name;
                $settings['type_user'] = auth()->user()->__get('type_user');
                $settings['style_template'] = auth()->user()->__get('style_template');
                $settings['company_id'] = str_pad($dataCompany->id, 5, 0, STR_PAD_LEFT);
                $settings['plan_expiration_date'] = date(DATETIME_BRAZIL_NO_SECONDS, strtotime($dataCompany->plan_expiration_date));

                $settings['notices'] = '';

                if (strtotime($dataCompany->plan_expiration_date) < strtotime(sumDate(dateNowInternational(), null, null, 4))) {
                    $datetime_plan_expiration_date = new DateTime($dataCompany->plan_expiration_date);
                    $datetime_now = new DateTime(dateNowInternational());
                    $interval_plan_expiration = $datetime_plan_expiration_date->diff($datetime_now);

                    $color_alert_plan_expiration = 'warning';
                    if ($interval_plan_expiration->d <= 1) {
                        $color_alert_plan_expiration = 'danger';
                    }

                    $settings['notices'] .= "<div class='alert alert-fill-$color_alert_plan_expiration mt-3 text-center' role='alert'><i class='mdi mdi-alert-circle'></i> Seu plano vence em: $settings[plan_expiration_date]. <a href='".route('plan.index')."' class='ml-2 btn btn-rounded btn-fw btn-sm btn-light'>Renovar</a> </div>";
                }

                $months = 2;
                $settings['intervalDates'] = [
                    'start' => date(DATE_BRAZIL, strtotime("-$months months", time())),
                    'finish' => date(DATE_BRAZIL)
                ];
                $settings['intervalBillDates'] = [
                    'start' => date(DATE_BRAZIL, strtotime("-1 months", time())),
                    'finish' => date(DATE_BRAZIL, strtotime("+1 months", time())),
                ];

                // permissões
                if (auth()->user()->__get('type_user') == User::$TYPE_USER['admin'] || auth()->user()->__get('type_user') == User::$TYPE_USER['master']) { // administrador permissão total
                    $permissions = Permission::query()->select('name')->get()->toArray();
                } else { // permissão por usuário.
                    $permissions = empty(auth()->user()->__get('permission')) ? [] : json_decode(auth()->user()->__get('permission'), true);
                    $permissions = Permission::query()->select('name')->whereIn('id', $permissions)->get()->toArray();
                }

                $arrNamesPermissions = array_map(function ($permission) {
                    return $permission['name'];
                }, $permissions);

                $view->with('permissions', $arrNamesPermissions);
            }

            $view->with('settings', $settings);
        });
    }

    private function loadObservers(): void
    {
        Address::observe(AddressObserver::class);
        BillToPay::observe(BillToPayObserver::class);
        BillToPayPayment::observe(BillToPayPaymentObserver::class);
        Budget::observe(BudgetObserver::class);
        BudgetEquipment::observe(BudgetEquipmentObserver::class);
        BudgetPayment::observe(BudgetPaymentObserver::class);
        BudgetResidue::observe(BudgetResidueObserver::class);
        Client::observe(ClientObserver::class);
        Company::observe(CompanyObserver::class);
        Config::observe(ConfigObserver::class);
        Driver::observe(DriverObserver::class);
        Equipment::observe(EquipmentObserver::class);
        EquipmentWallet::observe(EquipmentWalletObserver::class);
        Plan::observe(PlanObserver::class);
        PlanHistory::observe(PlanHistoryObserver::class);
        PlanPayment::observe(PlanPaymentObserver::class);
        Provider::observe(ProviderObserver::class);
        Rental::observe(RentalObserver::class);
        RentalEquipment::observe(RentalEquipmentObserver::class);
        RentalPayment::observe(RentalPaymentObserver::class);
        RentalResidue::observe(RentalResidueObserver::class);
        Residue::observe(ResidueObserver::class);
        Vehicle::observe(VehicleObserver::class);
        User::observe(UserObserver::class);
    }
}
