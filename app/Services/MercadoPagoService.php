<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Plan;
use App\Models\PlanHistory;
use App\Models\PlanPayment;
use App\Models\PlanPreapprovalPayment;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\PreApproval\PreApprovalClient;
use MercadoPago\MercadoPagoConfig;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;

class MercadoPagoService
{
    private bool $debug = false;
    public string $log_payment_data = '';
    private PlanPayment $plan_payment;
    private PlanHistory $plan_history;
    private Plan $plan;
    private Company $company;
    private PlanPreapprovalPayment $plan_preapproval_payment;

    protected array $cancel_status = array('rejected', 'cancelled', 'refunded', 'charged_back', 'expired', 'paused');

    protected array $approve_status = array('approved', 'authorized');

    public function __construct(bool $debug = false)
    {
        $this->plan_payment = new PlanPayment();
        $this->plan_history = new PlanHistory();
        $this->plan = new Plan();
        $this->company = new Company();
        $this->plan_preapproval_payment = new PlanPreapprovalPayment();
        $this->debug = $debug;
        $this->log_payment_data = '';
    }

    public function updatePayment(string $code, string $type): int
    {
        try {
            $plan_payment = $this->plan_payment->getPaymentByTransaction($code);

            if (!$plan_payment) {
                if ($type !== 'subscription_authorized_payment') {
                    $this->debugEcho("plan code ($code) not found.");
                    return Response::HTTP_NOT_FOUND;
                }

                $preapproval_payment = $this->plan_preapproval_payment->getByGatewayPaymentId($code);

                if (!$preapproval_payment) {
                    $preapproval = $this->getPreapprovalAuthorizedPayments($code);
                    $preapproval_id = $preapproval->preapproval_id;
                    $data_preapproval = $this->plan_payment->getPaymentByTransaction($preapproval_id);
                    if (!$data_preapproval) {
                        $this->debugEcho("plan preapproval code ($code) and id_transaction ($preapproval_id) not found.");
                        return Response::HTTP_NOT_FOUND;
                    }

                    $this->plan_preapproval_payment->insert(array(
                        'company_id'            => $data_preapproval->company_id,
                        'plan_payment_id'       => $data_preapproval->id,
                        'preapproval_id'        => $preapproval_id,
                        'status_detail'         => $preapproval->payment->status_detail,
                        'status'                => $preapproval->payment->status,
                        'transaction_amount'    => $preapproval->transaction_amount,
                        'gateway_payment_id'    => $preapproval->id,
                        'gateway_debit_date'    => $preapproval->debit_date,
                        'gateway_date_created'  => $preapproval->date_created,
                    ));
                } else {
                    $preapproval_id = $preapproval_payment->preapproval_id;
                }

                $code = $preapproval_id;
                $plan_payment = $this->plan_payment->getPaymentByTransaction($code);
            }

            $plan_payment_id    = (int)$plan_payment->id;
            $company_id         = (int)$plan_payment->company_id;
            $plan_config_id     = (int)$plan_payment->plan_id;

            try {
                if ($plan_payment->is_subscription) {
                    $data_payment = $this->getPreapproval($code);
                } else {
                    $data_payment = $this->getPayment($code);
                }
            } catch(Exception | UnexpectedValueException $e) {
                $this->debugEcho("get payment ($code) to mercadoPago found a error. {$e->getMessage()}");
                return Response::HTTP_BAD_REQUEST;
            }

            $status         = $data_payment->status;
            $status_detail  = $plan_payment->is_subscription ? null : $data_payment->status_detail;
            $last_modified  = $plan_payment->is_subscription ? $data_payment->last_modified : $data_payment->date_last_updated;
            $last_modified  = formatDateInternational($last_modified) ?? dateNowInternational();
            $observation    = $plan_payment->is_subscription ? 'preapproval: '.(($data_payment->summarized->quotas - $data_payment->summarized->pending_charge_quantity) + 1).'/'.$data_payment->summarized->quotas : null;

            $this->debugEcho("[CODE_TRANSACTION=$code]");
            $this->debugEcho("[PLAN=$plan_payment_id]");
            $this->debugEcho("[STATUS=$status]");
            $this->debugEcho("[STATUS_DETAIL=$status_detail]");
            $this->debugEcho("[COMPANY=$company_id]");

            // verificar se o status já existe
            if ($this->plan_history->getHistoryByStatusAndStatusDetail($plan_payment_id, $status, $status_detail, $observation)) {
                $this->debugEcho("status ($status) and status_detail ($status_detail) and observation ($observation) in use to plan_id ($plan_payment_id).");
                return Response::HTTP_OK;
            }

            $plan_config = $this->plan->getById($plan_config_id);
            $month_plan  = $plan_config->month_time;

            $this->debugEcho("[LAST_MODIFIED=$last_modified]");
            $this->debugEcho("[PLAN_CONFIG=$plan_config_id]");
            $this->debugEcho("[MONTH=$month_plan]");

            // Pedido aprovado, liberar dias do plano.
            if (in_array($status, $this->approve_status)) {
                $this->debugEcho("payment ($code) and plan_id ($plan_payment_id) is approved or authorized.");
                // Pagamento já teve uma aprovação anteriormente, não deve adicionar mais dias no plano.
                if (!$this->plan_history->getStatusByPayment($plan_payment_id, $observation, $this->approve_status)) {
                    $this->debugEcho("payment ($code) and plan_id ($plan_payment_id) isn't approved or authorized.");
                    // Pagamento não tem indício de cancelamento, continuar com a aprovação e adicionar os dias.
                    if (!$this->plan_history->getStatusByPayment($plan_payment_id, $observation, $this->cancel_status)) {
                        $this->debugEcho("payment ($code) and plan_id ($plan_payment_id) isn't rejected, cancelled, refunded or charged_back.");
                        // Adicionar quantidade de meses conforme o plano e atualiza o plano da empresa.
                        $this->company->setDatePlanAndUpdatePlanCompany($company_id, $plan_config_id, $month_plan);
                        $this->debugEcho("Add $month_plan month to payment_id ($code).");
                    }
                }
            }
            // Pedido perdeu sua aprovação, deve verificar se chegou a ocorrer alguma aprovação para reverter.
            elseif (in_array($status, $this->cancel_status)) {
                $this->debugEcho("payment ($code) and plan_id ($plan_payment_id) is rejected or cancelled or refunded or charged_back.");
                // Pagamento já teve uma aprovação anteriormente, deve reverter a aprovação.
                if ($this->plan_history->getStatusByPayment($plan_payment_id, $observation, $this->approve_status)) {
                    $this->debugEcho("payment ($code) and plan_id ($plan_payment_id) has already been approved or authorized.");
                    // Pagamento já perdeu a aprovação anteriormente, não deve reverter a aprovação novamente.
                    if (!$this->plan_history->getStatusByPayment($plan_payment_id, $observation, $this->cancel_status)) {
                        $this->debugEcho("payment ($code) and plan_id ($plan_payment_id) isn't rejected, cancelled, refunded or charged_back.");
                        // identificar qual o plano anterior do que precisa ser cancelado.
                        $plan_id_old = $this->plan_history->getPenultimatePlanConfirmedCompany($company_id, $plan_payment_id);
                        $this->debugEcho("Remove $month_plan month to payment_id ($code).");

                        // reverter os dias, pois ocorreu um cancelamento no pagamento.
                        $this->company->setDatePlanAndUpdatePlanCompany($company_id, $plan_id_old, -$month_plan);
                    }
                }
            }

            $plan_history = array(
                'payment_id'    => $plan_payment_id,
                'status_detail' => $status_detail,
                'status'        => $status,
                'status_date'   => $last_modified,
                'observation'   => $observation
            );
            $this->plan_history->insert($plan_history);
            $this->debugEcho("New history created. " . json_encode($plan_history, JSON_UNESCAPED_UNICODE) . "\n");

            $this->plan_payment->edit(array(
                'status_detail' => $status_detail,
                'status'        => $status
            ), $company_id, $plan_payment_id);

            return Response::HTTP_CREATED;
        } catch (Exception $e) {
            $this->debugEcho("Exception to get payment. {$e->getMessage()}");
            return Response::HTTP_BAD_REQUEST;
        }
    }

    /**
     * Mostra dados na saída, caso esteja em modo de debug.
     *
     * @param   string  $text   Texto para exibição.
     */
    public function debugEcho(string $text): void
    {
        $message = "$text\n";
        $this->log_payment_data .= $message;

        if ($this->debug) {
            echo $message;
        }
    }

    /**
     * @param   string $uri
     * @return  null|object
     * @throws  Exception
     */
    private function request(string $uri): ?object
    {
        try {
            $client = new Client([
                'base_uri' => 'https://api.mercadopago.com',
                'headers'  => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . env('MP_ACCESS_TOKEN'),
                    'timeout' => 2, // Response timeout
                    'connect_timeout' => 2.1, // Connection timeout
                ]
            ]);
            $request = $client->get($uri);
            return json_decode($request->getBody()->getContents());
        } catch(Exception | GuzzleException | UnexpectedValueException $e) {
            throw new Exception("get payment by uri ($uri) to MercadoPago found a error. {$e->getMessage()}",  Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @param   string $id
     * @return  object|null
     * @throws  Exception
     */
    private function getPayment(string $id): ?object
    {
        /*
        $mp_config = new MercadoPagoConfig();
        $mp_config->setAccessToken(env('MP_ACCESS_TOKEN'));
        $mp_config->setConnectionTimeout(2000);
        $payment = new PaymentClient();
        return $payment->get($id);
        */

        return $this->request("/v1/payments/$id");
    }

    /**
     * @param   string $id
     * @return  object|null
     * @throws  Exception
     */
    private function getPreapproval(string $id): ?object
    {
        /*
        $mp_config = new MercadoPagoConfig();
        $mp_config->setAccessToken(env('MP_ACCESS_TOKEN'));
        $mp_config->setConnectionTimeout(2000);
        $payment = new PreApprovalClient();
        return $payment->get($id);
        */

        return $this->request("/preapproval/$id");
    }

    /**
     * @param   string $id
     * @return  object|null
     * @throws  Exception
     */
    private function getPreapprovalAuthorizedPayments(string $id): ?object
    {
        /*
        $mp_config = new MercadoPagoConfig();
        $mp_config->setAccessToken(env('MP_ACCESS_TOKEN'));
        $mp_config->setConnectionTimeout(2000);
        $payment = new PreApprovalClient();
        return $payment->get($id);
        */

        return $this->request("/authorized_payments/$id");
    }
}
