<?php

namespace App\Providers;

use App\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Contracts\View\View;
use App\Models\Company;

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
                $settings['plan_expiration_date'] = date('d/m/Y H:i', strtotime($dataCompany->plan_expiration_date));

                $months = 2;
                $settings['intervalDates'] = [
                    'start' => date('d/m/Y', strtotime("-$months months", time())),
                    'finish' => date('d/m/Y')
                ];

                // permissões
                $arrNamesPermissions = [];
                if (auth()->user()->__get('type_user') == 1 || auth()->user()->__get('type_user') == 2) { // administrador permissão total
                    foreach (Permission::query()->get() as $permission) {
                        $arrNamesPermissions[] = $permission->name;
                    }
                } else { // permissão por usuário
                    $permissions = empty(auth()->user()->__get('permission')) ? [] : json_decode(auth()->user()->__get('permission'));
                    foreach ($permissions as $permission) {
                        $arrNamesPermissions[] = Permission::query()->where('id', $permission)->first()->name;
                    }
                }
                $view->with('permissions', $arrNamesPermissions);
            }

            $view->with('settings', $settings);
        });
    }
}
