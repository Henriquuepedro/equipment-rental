<?php

namespace App\Http\Controllers;

use App\Models\FormPayment;
use Illuminate\Http\JsonResponse;

class FormPaymentController extends Controller
{
    public $form_payment;

    public function __construct()
    {
        $this->form_payment = new FormPayment();
    }

    public function getFormPayments(): JsonResponse
    {
        return response()->json($this->form_payment->get());
    }
}
