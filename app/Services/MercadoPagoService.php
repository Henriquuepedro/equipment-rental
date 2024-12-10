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

    /**
     * @todo pegar os dias faltantes para o dia 10 e adicionar no plano.
     *
     * @param string $code
     * @return int
     */
    public function updatePayment(string $code): int
    {
        try {
            $data_payment = $this->getPayment($code);

            // Validação do cartão.
            if ($data_payment->operation_type == 'card_validation') {
                $this->debugEcho("card validation");
                return Response::HTTP_OK;
            }

            $plan_payment = $this->plan_payment->getPaymentByCodePayment($data_payment->external_reference);

            if ($plan_payment->is_subscription) {
                $preapproval_payment = $this->plan_preapproval_payment->getByGatewayPaymentId($data_payment->id);

                if ($preapproval_payment) {
                    $this->plan_preapproval_payment->editById(array(
                        'status_detail'         => $data_payment->status_detail,
                        'status'                => $data_payment->status,
                        'gateway_debit_date'    => $data_payment->date_last_updated,
                        'gateway_last_modified' => $data_payment->date_last_updated,
                    ), $preapproval_payment->id);
                } else {
                    $this->plan_preapproval_payment->insert(array(
                        'company_id'            => $plan_payment->company_id,
                        'plan_payment_id'       => $plan_payment->id,
                        'preapproval_id'        => $plan_payment->id_transaction,
                        'status_detail'         => $data_payment->status_detail,
                        'status'                => $data_payment->status,
                        'transaction_amount'    => $data_payment->transaction_amount,
                        'gateway_payment_id'    => $data_payment->id,
                        'gateway_debit_date'    => $data_payment->date_last_updated,
                        'gateway_date_created'  => $data_payment->date_created,
                        'gateway_last_modified' => $data_payment->date_last_updated,
                    ));
                }
            }

            $plan_payment_id    = (int)$plan_payment->id;
            $company_id         = (int)$plan_payment->company_id;
            $plan_config_id     = (int)$plan_payment->plan_id;

            try {
                $status         = $data_payment->status;
                $status_detail  = $data_payment->status_detail;
                $last_modified  = $data_payment->date_last_updated;
                $last_modified  = formatDateInternational($last_modified) ?? dateNowInternational();
                $observation    = null;

                if ($plan_payment->is_subscription) {
                    $subscription_sequence_total  = $data_payment->point_of_interaction->transaction_data->subscription_sequence->total;
                    $subscription_sequence_number = $data_payment->point_of_interaction->transaction_data->subscription_sequence->number;
                    $observation = "preapproval: $subscription_sequence_number/$subscription_sequence_total";
                }
            } catch(Exception | UnexpectedValueException $e) {
                $this->debugEcho("get payment ($code) to mercadoPago found a error. {$e->getMessage()}");
                return Response::HTTP_BAD_REQUEST;
            }

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
                $this->debugEcho("payment ($code) and plan_id ($plan_payment_id) is " . implode(', ', $this->approve_status));
                // Pagamento já teve uma aprovação anteriormente, não deve adicionar mais dias no plano.
                if (!$this->plan_history->getStatusByPayment($plan_payment_id, $observation, $this->approve_status)) {
                    $this->debugEcho("payment ($code) and plan_id ($plan_payment_id) wasn't " . implode(', ', $this->approve_status));
                    // Pagamento não tem indício de cancelamento, continuar com a aprovação e adicionar os dias.
                    if (!$this->plan_history->getStatusByPayment($plan_payment_id, $observation, $this->cancel_status)) {
                        $this->debugEcho("payment ($code) and plan_id ($plan_payment_id) wasn't " . implode(', ', $this->cancel_status));
                        // Adicionar quantidade de meses conforme o plano e atualiza o plano da empresa.
                        $this->company->setDatePlanAndUpdatePlanCompany($company_id, $plan_config_id, $month_plan);
                        $this->debugEcho("Add $month_plan month to payment_id ($code).");
                    }
                }
            }
            // Pedido perdeu a sua aprovação, deve verificar se chegou a ocorrer alguma aprovação para reverter.
            elseif (in_array($status, $this->cancel_status)) {
                $this->debugEcho("payment ($code) and plan_id ($plan_payment_id) is " . implode(', ', $this->cancel_status));
                // Pagamento já teve uma aprovação anteriormente, deve reverter a aprovação.
                if ($this->plan_history->getStatusByPayment($plan_payment_id, $observation, $this->approve_status)) {
                    $this->debugEcho("payment ($code) and plan_id ($plan_payment_id) has already been " . implode(', ', $this->approve_status));
                    // Pagamento já perdeu a aprovação anteriormente, não deve reverter a aprovação novamente.
                    if (!$this->plan_history->getStatusByPayment($plan_payment_id, $observation, $this->cancel_status)) {
                        $this->debugEcho("payment ($code) and plan_id ($plan_payment_id) wasn't " . implode(', ', $this->cancel_status));
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
