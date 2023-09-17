<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Driver;
use App\Models\Equipment;
use App\Models\Vehicle;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    private Vehicle $vehicle;
    private Driver $driver;
    private Equipment $equipment;
    private Company $company;
    public function __construct()
    {
        $this->driver    = new Driver();
        $this->vehicle   = new Vehicle();
        $this->equipment = new Equipment();
        $this->company   = new Company();
    }

    public function rental(): Factory|View|RedirectResponse|Application
    {
        if (!hasPermission('ReportView')) {
            return redirect()->route('dashboard')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        $company_id = Auth::user()->__get('company_id');

        $drivers    = $this->driver->getDrivers($company_id);
        $vehicles   = $this->vehicle->getVehicles($company_id);
        $equipments = $this->equipment->getEquipments($company_id);

        $companies = array();
        if (hasAdminMaster()) {
            $companies = $this->company->getAllCompaniesActive();
        }

        return view('report.rental', compact('drivers', 'vehicles', 'equipments', 'companies'));
    }

    public function bill(): Factory|View|RedirectResponse|Application
    {
        if (!hasPermission('ReportView')) {
            return redirect()->route('dashboard')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        $companies = array();
        if (hasAdminMaster()) {
            $companies = $this->company->getAllCompaniesActive();
        }

        return view('report.bill', compact('companies'));
    }

    public function register(): Factory|View|RedirectResponse|Application
    {
        if (!hasPermission('ReportView')) {
            return redirect()->route('dashboard')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        $companies = array();
        if (hasAdminMaster()) {
            $companies = $this->company->getAllCompaniesActive();
        }

        return view('report.register', compact('companies'));
    }
}
