<?php

namespace App\Http\Controllers;

use App\Models\BudgetEquipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class BudgetEquipmentController extends Controller
{
    private BudgetEquipment $budget_equipment;

    public function __construct()
    {
        $this->budget_equipment = new BudgetEquipment();
    }

    public function getEquipmentsBudget(int $budget_id): JsonResponse
    {
        if (!hasPermission('BudgetUpdatePost')) {
            return response()->json();
        }

        $company_id = Auth::user()->__get('company_id');

        $equipments = $this->budget_equipment->getEquipments($company_id, $budget_id);

        return response()->json($equipments);
    }
}
