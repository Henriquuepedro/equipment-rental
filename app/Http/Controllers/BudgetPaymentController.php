<?php

namespace App\Http\Controllers;

use App\Models\BudgetPayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class BudgetPaymentController extends Controller
{
    private BudgetPayment $budget_payment;

    public function __construct()
    {
        $this->budget_payment = new BudgetPayment();
    }

    public function getPaymentsBudget(int $budget_id): JsonResponse
    {
        if (!hasPermission('BudgetUpdatePost')) {
            return response()->json();
        }

        $company_id = Auth::user()->__get('company_id');

        $equipments = $this->budget_payment->getPayments($company_id, $budget_id);

        return response()->json($equipments);
    }
}
