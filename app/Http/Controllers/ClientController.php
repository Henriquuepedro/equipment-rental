<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;

class ClientController extends Controller
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function index()
    {
        $getClients = $this->client->getClients();

        $dataClients = $getClients;

        return view('client.index', compact('dataClients'));
    }

    public function create()
    {
        return view('client.create');
    }

    public function insert(Request $request)
    {
        return 'create client';
    }

    public function edit($id)
    {
        return 'editar cliente: ' . $id;
    }

    public function update(Request $request)
    {
        return 'update client';
    }

    public function delete(Request $request)
    {
        return 'delete client';
    }
}
