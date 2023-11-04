<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CashFlowController extends Controller
{
    public function __construct()
    {
    }

    public function index(): Factory|View|RedirectResponse|Application
    {
        if (!hasPermission('BillsToPayView') || !hasPermission('BillsToReceiveView')) {
            return redirect()->route('dashboard')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        return view('cash_flow.index');
    }
}
