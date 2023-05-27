<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function dashboard()
    {
        $company_id = Auth::user()->__get('company_id');
        $indicator = array(
            'clients' => $this->client->getCountClients($company_id)
        );

        return view('dashboard.home', compact('indicator'));
    }
}
