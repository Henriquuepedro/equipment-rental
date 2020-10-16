<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;

class DashboardController extends Controller
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function dashboard()
    {
        $indicator = array(
            'clients' => $this->client->getCountClients()
        );

        return view('dashboard.home', compact('indicator'));
    }
}
