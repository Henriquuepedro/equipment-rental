<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;

class RentalController extends Controller
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function index()
    {
        if (!$this->hasPermission('RentalView')) {
            return redirect()->route('dashboard')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        return view('rental.index');
    }

    public function create()
    {
        if (!$this->hasPermission('RentalCreatePost')) {
            return redirect()->route('rental.index')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        return view('rental.create');
    }
}
