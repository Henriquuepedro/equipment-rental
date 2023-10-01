<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    private Plan $plan;
    public function __construct()
    {
        $this->plan = new Plan();
    }

    public function index(): Factory|View|Application
    {
        return view('plan.index');
    }

    public function request(): Factory|View|Application
    {
        return view('plan.request');
    }

    public function getPlans(string $type = 'monthly'): JsonResponse
    {
        return response()->json($this->plan->getByType($type));
    }
}
