<?php

namespace App\Http\Controllers\Api\MercadoPago;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Plan;
use App\Models\PlanHistory;
use App\Models\PlanPayment;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\MercadoPagoConfig;

class Notification extends Controller
{

    private bool $debug = false;

    private PlanPayment $plan_payment;
    private PlanHistory $plan_history;
    private Plan $plan;
    private Company $company;

    public function __construct()
    {
        $this->plan_payment = new PlanPayment();
        $this->plan_history = new PlanHistory();
        $this->plan = new Plan();
        $this->company = new Company();
    }

    public function notification(Request $request): JsonResponse
    {
        try {
            // Veio via IPN, não será usado, apenas webhook.
            if($request->input('source_news') != 'webhook'){
                $this->debugEcho("data_id not found.");
                return response()->json(array('success' => true), 406);
            }

            $this->debug = (bool)$request->input('debug');

            if (
                !in_array($request->input('action'), array("payment.updated", "payment.updated")) ||
                $request->input('type') != "payment"
            ) {
                $this->debugEcho("type or action don't accept. [action={$request->input('action')} | type={$request->input('type')}].");
                return response()->json(array(), 406);
            }

            $code = $request->input('data_id');

            $plan_payment = $this->plan_payment->getPaymentByTransaction($code);

            if (!$plan_payment) {
                $this->debugEcho("plan code ($code) not found.");
                return response()->json(array(), 404);
            }

            $plan_payment_id    = (int)$plan_payment->id;
            $company_id         = (int)$plan_payment->company_id;
            $plan_config_id     = (int)$plan_payment->plan_id;

            // recupera dados do mercado pago
            MercadoPagoConfig::setAccessToken(env('MP_ACCESS_TOKEN'));

            try {
                $payment = new PaymentClient();
                $data_payment = $payment->get($code);
            } catch(\Exception $e) {
                $this->debugEcho("get payment ($code) to mercadoPago found a error. {$e->getMessage()}");
                return response()->json(array($e->getMessage()), 400);
            }

            $status         = $data_payment->status;
            $status_detail  = $data_payment->status_detail;
            $last_modified  = formatDateInternational($data_payment->date_last_updated) ?? dateNowInternational();

            $this->debugEcho("[CODE_TRANSACTION=$code]");
            $this->debugEcho("[PLAN=$plan_payment_id]");
            $this->debugEcho("[STATUS=$status]");
            $this->debugEcho("[STATUS_DETAIL=$status_detail]");
            $this->debugEcho("[COMPANY=$company_id]");

            // verificar se o status já existe
            if ($this->plan_history->getHistoryByStatusAndStatusDetail($plan_payment_id, $status, $status_detail)) {
                $this->debugEcho("status ($status) and status_detail ($status_detail) in use to plan_id ($plan_payment_id).");
                return response()->json(array('success' => true));
            }

            $plan_config = $this->plan->getById($plan_config_id);
            $month_plan  = $plan_config->month_time;

            $this->debugEcho("[LAST_MODIFIED=$last_modified]");
            $this->debugEcho("[PLAN_CONFIG=$plan_config_id]");
            $this->debugEcho("[MONTH=$month_plan]");

            // Pedido aprovado, liberar dias do plano.
            if (in_array($status, array('approved', 'authorized'))) {
                $this->debugEcho("payment ($code) and plan_id ($plan_payment_id) is approved or authorized.");
                // Pagamento já teve uma aprovação anteriormente, não deve adicionar mais dias no plano.
                if (!$this->plan_history->getStatusByPayment($plan_payment_id, array('approved', 'authorized'))) {
                    $this->debugEcho("payment ($code) and plan_id ($plan_payment_id) isn't approved or authorized.");
                    // Pagamento não tem indício de cancelamento, continuar com a aprovação e adicionar os dias.
                    if (!$this->plan_history->getStatusByPayment($plan_payment_id, array('rejected', 'cancelled', 'refunded', 'charged_back'))) {
                        $this->debugEcho("payment ($code) and plan_id ($plan_payment_id) isn't rejected, cancelled, refunded or charged_back.");
                        // Adicionar quantidade de meses conforme o plano e atualiza o plano da empresa.
                        $this->company->setDatePlanAndUpdatePlanCompany($company_id, $plan_config_id, $month_plan);
                        $this->debugEcho("Add $month_plan month to payment_id ($code).");
                    }
                }
            }
            // Pedido perdeu sua aprovação, deve verificar se chegou a ocorrer alguma aprovação para reverter.
            elseif (in_array($status, array('rejected', 'cancelled', 'refunded', 'charged_back'))) {
                $this->debugEcho("payment ($code) and plan_id ($plan_payment_id) is rejected or cancelled or refunded or charged_back.");
                // Pagamento já teve uma aprovação anteriormente, deve reverter a aprovação.
                if ($this->plan_history->getStatusByPayment($plan_payment_id, array('approved', 'authorized'))) {
                    $this->debugEcho("payment ($code) and plan_id ($plan_payment_id) has already been approved or authorized.");
                    // Pagamento já perdeu a aprovação anteriormente, não deve reverter a aprovação novamente.
                    if (!$this->plan_history->getStatusByPayment($plan_payment_id, array('rejected', 'cancelled', 'refunded', 'charged_back'))) {
                        $this->debugEcho("payment ($code) and plan_id ($plan_payment_id) isn't rejected, cancelled, refunded or charged_back.");
                        // identificar qual o plano anterior do que precisa ser cancelado.
                        $plan_id_old = $this->plan_history->getPenultimatePlanConfirmedCompany($company_id, $plan_payment_id);
                        $this->debugEcho("Remove $month_plan month to payment_id ($code).");

                        // reverter os dias, pois ocorreu um cancelamento no pagamento.
                        $this->company->setDatePlanAndUpdatePlanCompany($company_id, $plan_id_old, -$month_plan);
                    }
                }
            }

            $this->plan_history->insert(array(
                'payment_id'    => $plan_payment_id,
                'status_detail' => $status_detail,
                'status'        => $status,
                'status_date'   => $last_modified
            ));

            $this->plan_payment->edit(array(
                'status_detail' => $status_detail,
                'status'        => $status
            ), $company_id, $plan_config_id);

            return response()->json(array('success' => true), 201);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Mostra dados na saída, caso esteja em modo de debug.
     *
     * @param   string  $text   Texto para exibição.
     */
    private function debugEcho(string $text): void
    {
        if ($this->debug) {
            echo $text . "\n";
        }
    }
}
