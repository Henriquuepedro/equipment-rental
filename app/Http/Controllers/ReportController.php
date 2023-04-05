<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Company;
use App\Models\Driver;
use App\Models\Equipment;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReportController extends Controller
{
    private $client;
    private $vehicle;
    private $driver;
    private $equipment;
    private $company;
    public function __construct()
    {
        $this->client    = new Client;
        $this->driver    = new Driver;
        $this->vehicle   = new Vehicle;
        $this->equipment = new Equipment;
        $this->company   = new Company;
    }

    public function rental()
    {
        if (!hasPermission('ReportView')) {
            return redirect()->route('dashboard')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        $company_id = Auth::user()->company_id;

        $drivers    = $this->driver->getDrivers($company_id);
        $vehicles   = $this->vehicle->getVehicles($company_id);
        $equipments = $this->equipment->getEquipments($company_id);

        $companies = array();
        if (hasAdminMaster()) {
            $companies = $this->company->getAllCompaniesActive();
        }

        return view('report.rental', compact('drivers', 'vehicles', 'equipments', 'companies'));
    }

    public function bill()
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

    public function register()
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
