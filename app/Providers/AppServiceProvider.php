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
use App\Models\Guide;
use App\Models\Notification;
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
use App\Observers\GuideObserver;
use App\Observers\NotificationObserver;
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
use Opcodes\LogViewer\Facades\LogViewer;

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

        LogViewer::auth(function ($request) {
            return $request->user()
                && likeText('%@locai.com.br%', $request->user()->email);
        });

        // Variaveis para serem usadas em todas as views
        // Definir um array chamado settings contendo a suas
        // respectivas possições para variaveis
        view()->composer('*',function( View $view ) {
            $settings = array('style_template' => User::$STYLE_TEMPLATE['black']);

            if (auth()->user()) {
                $logo_company_no_logotipo = auth()->user()->__get('style_template') == 1 ? 'assets/images/system/logotipo-horizontal-black.png' : 'assets/images/system/logotipo-horizontal-white.png';

                $company = new Company();
                $notification = new Notification();
                $config = new Config();
                $dataCompany = $company->getCompany(auth()->user()->__get('company_id'));

                $settings['notifications'] = $notification->getNotReadLastRows($dataCompany->id, 6);
                $settings['notifications_count'] = count($settings['notifications']);
                if (count($settings['notifications']) == 6) {
                    $settings['notifications_count'] = $notification->getNotReadLastRows($dataCompany->id);
                }

                $settings['img_profile'] = asset(auth()->user()->__get('profile') ? "assets/images/profile/" . auth()->user()->__get('id') . "/" . auth()->user()->__get('profile') : "assets/images/system/profile.png");
                $settings['img_company'] = asset($dataCompany->logo ? "assets/images/company/$dataCompany->id/$dataCompany->logo" : $logo_company_no_logotipo);
                $settings['name_company'] = $dataCompany->name;
                $settings['type_user'] = auth()->user()->__get('type_user');
                $settings['style_template'] = auth()->user()->__get('style_template');
                $settings['company_id'] = str_pad($dataCompany->id, 5, 0, STR_PAD_LEFT);
                $settings['plan_expiration_date'] = date(DATETIME_BRAZIL_NO_SECONDS, strtotime($dataCompany->plan_expiration_date));

                $settings['notices'] = '';

                if (strtotime($dataCompany->plan_expiration_date) < strtotime(sumDate(dateNowInternational(), null, null, 4))) {
                    $datetime_plan_expiration_date = new DateTime(formatDateInternational($dataCompany->plan_expiration_date, DATE_INTERNATIONAL));
                    $datetime_now = new DateTime(dateNowInternational(null, DATE_INTERNATIONAL));
                    $interval_plan_expiration = $datetime_now->diff($datetime_plan_expiration_date);
                    $diff_date_plan_expiration = (int)$interval_plan_expiration->format("%r%a");

                    $color_alert_plan_expiration = 'warning';
                    $message_pre_alert_plan_expiration = "Seu plano vence em: $settings[plan_expiration_date].";
                    if ($diff_date_plan_expiration <= 1) {
                        $color_alert_plan_expiration = 'danger';
                    }
                    if ($diff_date_plan_expiration == 0) {
                        $color_alert_plan_expiration = 'danger';
                        $message_pre_alert_plan_expiration = "Seu plano vence hoje.";
                    }
                    if ($diff_date_plan_expiration < 0) {
                        $color_alert_plan_expiration = 'danger';
                        $message_pre_alert_plan_expiration = "Seu plano venceu em: $settings[plan_expiration_date].";
                    }

                    $settings['notices'] .= "<div class='alert alert-$color_alert_plan_expiration mt-3 text-center' role='alert'><i class='mdi mdi-alert-circle'></i> $message_pre_alert_plan_expiration <a href='".route('plan.index')."' class='ml-2 btn btn-rounded btn-fw btn-sm btn-light'>Renovar</a> </div>";
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

                $settings['company_config'] = array_filter($config->getByCompany($dataCompany->id)->toArray(), function($config, $config_name){
                    $field_remove = array('id', 'company_id', 'user_update', 'created_at', 'updated_at');
                    return !in_array($config_name, $field_remove);
                }, ARRAY_FILTER_USE_BOTH);

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
        Guide::observe(GuideObserver::class);
        Notification::observe(NotificationObserver::class);
    }
}
