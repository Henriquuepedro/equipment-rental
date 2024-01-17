<?php

namespace App\Http\Controllers\Api\MercadoPago;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Plan;
use App\Models\PlanHistory;
use App\Models\PlanPayment;
use App\Services\MercadoPagoService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\MercadoPagoConfig;

class Notification extends Controller
{


    public function __construct()
    {
    }

    public function notification(Request $request): JsonResponse
    {
        try {
            $debug = (bool)$request->input('debug');
            $mercado_pago_service = new MercadoPagoService($debug);

            if (
                (
                    in_array($request->input('action'), array("test.created", "test.updated")) &&
                    $request->input('type') == "test"
                ) || (
                    $request->input('type') == "subscription_preapproval" &&
                    $request->input('entity') == "preapproval"
                )
            ) {
                return response()->json();
            }

            if (
                !in_array($request->input('action'), array("payment.created", "payment.updated", "payment.create", "payment.update")) ||
                $request->input('type') != "payment"
            ) {
                $mercado_pago_service->debugEcho("type or action don't accept. [action={$request->input('action')} | type={$request->input('type')}].");
                return response()->json(array(), 406);
            }

            $code = $request->input('data')['id'];

            return response()->json(array(), $mercado_pago_service->updatePayment($code));
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
