<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    private $client;
    private $driver;
    public function __construct()
    {
        $this->client = new Client();
        $this->driver = new Driver();
    }

    public function rental()
    {
        if (!hasPermission('ReportView')) {
            return redirect()->route('dashboard')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        $clients = $this->client->getClients(Auth::user()->company_id);
        $drivers = $this->driver->getDrivers(Auth::user()->company_id);

        return view('report.rental', compact('clients', 'drivers'));
    }
}
