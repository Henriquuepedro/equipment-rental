<?php

namespace App\Providers;

use App\Models\Permission;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Contracts\View\View;
use App\Models\Company;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (env('APP_ENV') !== 'local') {
            URL::forceScheme('https');
        }

        Schema::defaultStringLength(191);

        // Variaveis para serem usadas em todas as views
        // Definir um array chamado settings contendo suas
        // respectivas possições para variaveis
        view()->composer('*',function( View $view ) {
            $settings = array('style_template' => 1);

            if (auth()->user()) {
                $company = new Company();
                $dataCompany = $company->getCompany(auth()->user()->__get('company_id'));

                $settings['img_profile'] = asset(auth()->user()->__get('profile') ? "assets/images/profile/" . auth()->user()->__get('id') . "/" . auth()->user()->__get('profile') : "assets/images/profile/profile.png");
                $settings['img_company'] = asset($dataCompany->logo ? "assets/images/company/$dataCompany->id/$dataCompany->logo" : "assets/images/company/company.png");
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
                if (auth()->user()->__get('type_user') == 1 || auth()->user()->__get('type_user') == 2) { // administrador permissão total
                    $permissions = Permission::query()->select('name')->get()->toArray();
                } else { // permissão por usuário
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
}
