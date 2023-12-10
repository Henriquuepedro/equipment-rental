<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class CheckPlan
{
    /**
     * Handle an incoming request.
     *
     * @param   Request $request
     * @param   Closure(Request): (Response|RedirectResponse) $next
     * @return  Response|RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $company_id = Auth::user()->__get('company_id');
        $company = new Company();

        $data_company = $company->getCompany($company_id);

        if ($request->route()->getName() === 'expired_plan') {
            if (strtotime($data_company->plan_expiration_date) >= strtotime(dateNowInternational())) {
                return redirect()->route('dashboard');
            }
        } else if (strtotime($data_company->plan_expiration_date) < strtotime(dateNowInternational())) {
            $exp_route = explode('.', $request->route()->getName());

            // Se são rotas de plano não deve bloquear.
            if ($exp_route[0] !== 'plan' && ($exp_route[0] !== 'ajax' || $exp_route[1] !== 'plan')) {
                return redirect()->route('expired_plan');
            }
        }

        return $next($request);
    }
}
