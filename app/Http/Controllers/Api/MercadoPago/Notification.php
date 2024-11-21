<?php

namespace App\Http\Controllers\Api\MercadoPago;

use App\Http\Controllers\Controller;
use App\Services\MercadoPagoService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
                $request_signature_ts = $request_signature_ts[1];
                $check_sognature_data_id = $request->input('data')['id'] ?? null;
                $check_sognature_type = $request->input('type');
                $check_sognature_action = $request->input('action');
                $check_sognature_api_version = $request->input('api_version');
                $check_sognature_date_created = $request->input('date_created');
                $check_sognature_id = $request->input('id');
                $check_sognature_live_mode = $request->input('live_mode');
                $check_sognature_user_id = $request->input('user_id');
                $check_sognature_host = $request->header('host');

                $data_ = "post;$check_sognature_host/api/mercado-pago/notificacao;data.id=$check_sognature_data_id;type=$check_sognature_type;user-agent:mercadopago webhook v1.0;$request_signature_ts;action:$check_sognature_action;api_version:$check_sognature_api_version;date_created:$check_sognature_date_created;id:$check_sognature_id;live_mode:$check_sognature_live_mode;type:$check_sognature_type;user_id:$check_sognature_user_id;";

                $decode_signature = hash_hmac('sha256', $data_, $signature_mp);

                /*if ($request_signature_check != $decode_signature) {
                    $mercado_pago_service->debugEcho("signature invalid. step 3. [x-signature={$request->header('x-signature')}].");
                    return response()->json(array(), 401);
                }*/
            }

            if (
                in_array($request->input('action'), array("test.created", "test.updated")) &&
                $request->input('type') == "test"
            ) {
                return response()->json();
            }

            if (
                !in_array($request->input('action'), array("payment.created", "payment.updated", "payment.create", "payment.update")) ||
                !in_array($request->input('type'), array("payment", "preapproval"))
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
