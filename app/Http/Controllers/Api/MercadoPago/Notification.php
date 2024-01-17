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

            // Valida assinatura enviada no cabeçalho.
            $signature_mp = env('MP_SIGNATURE');
            if (!empty($signature_mp)) {
                $request_signature = explode(',', $request->header('x-signature'));
                if (count($request_signature) !== 2) {
                    $mercado_pago_service->debugEcho("signature invalid. step 1. [x-signature={$request->header('x-signature')}].");
                    return response()->json(array(), 401);
                }

                $request_signature_ts = explode('=', $request_signature[0]);
                $request_signature_v1 = explode('=', $request_signature[1]);

                if (count($request_signature_v1) !== 2) {
                    $mercado_pago_service->debugEcho("signature invalid. step 2. [x-signature={$request->header('x-signature')}].");
                    return response()->json(array(), 401);
                }

                $request_signature_check = $request_signature_v1[1];

                if ($request_signature_check != $signature_mp) {
                    $mercado_pago_service->debugEcho("signature invalid. step 3. [x-signature={$request->header('x-signature')}].");
                    return response()->json(array(), 401);
                }
            }

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
