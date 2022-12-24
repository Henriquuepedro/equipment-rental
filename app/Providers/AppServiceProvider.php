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

            if (auth()->user()) {

                $company = new Company();
                $dataCompany = $company->getCompany(auth()->user()->company_id);

                $settings = array();

                $settings['img_profile'] = asset(auth()->user()->profile ? "assets/images/profile/" . auth()->user()->id . "/" . auth()->user()->profile : "assets/images/profile/profile.png");
                $settings['img_company'] = asset($dataCompany->logo ? "assets/images/company/{$dataCompany->id}/{$dataCompany->logo}" : "assets/images/company/company.png");
                $settings['name_company'] = $dataCompany->name;
                $settings['type_user'] = auth()->user()->type_user;

                $months = 2;
                $settings['intervalDates'] = [
                    'start' => date('d/m/Y', strtotime("-{$months} months", time())),
                    'finish' => date('d/m/Y')
                ];

                $view->with('settings', $settings);

                // permissões
                $arrNamesPermissions = [];
                if (auth()->user()->type_user == 1 || auth()->user()->type_user == 2) { // adm permissão total
                    foreach (Permission::query()->get() as $permission) {
                        array_push($arrNamesPermissions, $permission->name);
                    }
                } else { // permissão por usuário
                    $permissions = empty(auth()->user()->permission) ? [] : json_decode(auth()->user()->permission);
                    foreach ($permissions as $permission) {
                        array_push($arrNamesPermissions, Permission::query()->where('id', $permission)->first()->name);
                    }
                }
                $view->with('permissions', $arrNamesPermissions);


            }
        });
    }
}
