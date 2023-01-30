<?php

namespace App\Http\Controllers;

use App\Models\Nationality;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NationalityController extends Controller
{
    private $nationality;

    public function __construct()
    {
        $this->nationality = new Nationality();
    }

    public function getNationalities(): JsonResponse
    {
        return response()->json($this->nationality->get());
    }
}
