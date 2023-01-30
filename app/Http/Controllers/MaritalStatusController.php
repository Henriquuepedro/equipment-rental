<?php

namespace App\Http\Controllers;

use App\Models\MaritalStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaritalStatusController extends Controller
{
    private $marital_status;

    public function __construct()
    {
        $this->marital_status = new MaritalStatus();
    }

    public function getMaritalStatus(): JsonResponse
    {
        return response()->json($this->marital_status->get());
    }
}
