<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\Rental;
use App\Models\Vehicle;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    private Client $client;
    private Vehicle $vehicle;
    private Equipment $equipment;
    private Rental $rental;

    public function __construct()
    {
        $this->client       = new Client();
        $this->vehicle      = new Vehicle();
        $this->equipment    = new Equipment();
        $this->rental       = new Rental();
    }

    public function dashboard(): Factory|View|Application
    {
        $company_id = Auth::user()->__get('company_id');
        $indicator = array(
            'clients'       => $this->client->getCountClientsActive($company_id),
            'vehicles'      => $this->vehicle->getCountVehicles($company_id),
            'equipments'    => $this->equipment->getCountEquipments($company_id),
            'rentals'       => $this->rental->getCountRentals($company_id)
        );

        return view('dashboard.daily_dashboard', compact('indicator'));
    }
}
