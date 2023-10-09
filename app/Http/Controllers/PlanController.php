<?php

namespace App\Http\Controllers;

use App\Exceptions\MercadoPagoException;
use App\Models\Company;
use App\Models\Plan;
use App\Models\PlanHistory;
use App\Models\PlanPayment;
use DateTime;
use DateTimeZone;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Common\RequestOptions;
use DateTimeImmutable;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\Clock\SystemClock;
use Lcobucci\JWT\UnencryptedToken;
use Lcobucci\JWT\Validation\Constraint\LooseValidAt;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;

class PlanController extends Controller
{
    private Plan $plan;
    private Company $company;
    private PlanPayment $plan_payment;
    private PlanHistory $plan_history;
    private MercadoPagoException $mercado_pago_exception;

    public function __construct()
    {
        $this->plan = new Plan();
        $this->company = new Company();
        $this->plan_payment = new PlanPayment();
        $this->plan_history = new PlanHistory();
        $this->mercado_pago_exception = new MercadoPagoException();
    }

    public function index(): Factory|View|Application
    {
        return view('plan.index');
    }

    public function confirm(int $plan): Factory|View|RedirectResponse|Application
    {
        $plan = $this->plan->getById($plan);
        $company_id = Auth::user()->__get('company_id');

        if (!$plan) {
            return redirect()->route('plan.index')
                ->with('warning', "Plano não encontrado!");
        }

        $now    = new DateTimeImmutable("now");
        $config = Configuration::forSymmetricSigner(new Sha256(), InMemory::plainText(config('app.key')));

        $token = $config->builder()
            ->issuedBy(url()->current())
            ->withHeader('iss', url()->current())
            ->permittedFor(url()->route('plan.insert', array('plan' => $plan)))
            ->issuedAt($now)
            ->expiresAt($now->modify('+12 hours'))
            ->withClaim('uid', 1)
            ->withClaim('plan_value', (float)$plan->value)
            ->withClaim('plan_id', $plan->id)
            ->withClaim('plan_name', $plan->name)
            ->getToken($config->signer(), $config->signingKey());

        $tokenStr = $token->toString();

        $company_data = $this->company->getCompany($company_id);
        $company_data->first_company_name = explode(' ', $company_data->name)[0];
        $company_data->last_company_name  = str_replace("$company_data->first_company_name ", '', $company_data->name);

        return view('plan.confirm', compact('plan', 'tokenStr', 'company_data'));
    }

    public function request(): Factory|View|Application
    {
        return view('plan.request');
    }

    public function getPlans(string $type = 'monthly'): JsonResponse
    {
        return response()->json($this->plan->getByType($type));
    }

    public function insert(int $plan, Request $request)
    {
        try {
            $config = Configuration::forSymmetricSigner(new Sha256(), InMemory::plainText(config('app.key')));
            $clock = new SystemClock(new DateTimeZone('America/Sao_Paulo'));
            assert($config instanceof Configuration);

            $token  = $config->parser()->parse($request->input('token_plan'));
            $claims = $token->claims();
            assert($token instanceof UnencryptedToken);

            $config->setValidationConstraints(
                new LooseValidAt($clock),
                new PermittedFor(url()->route('plan.insert', array('plan' => $plan)))
            );

            $constraints = $config->validationConstraints();

            if (!$config->validator()->validate($token, ...$constraints)) {
                return response()->json(['errors' => 'Nao foi possível identificar o plano de pagamento. Recarregue a página.'], 400);
            }

            $plan_id    = $claims->get('plan_id');
            $plan_value = $claims->get('plan_value');

            MercadoPagoConfig::setAccessToken(env('MP_ACCESS_TOKEN'));

            $company_id         = $request->user()->company_id;
            $check_payment_method  = $request->input('payment_method_id');
            $company_data       = $this->company->getCompany($company_id);
            $plan_data          = $this->plan->getById($plan);
            $first_company_name = explode(' ', $company_data->name)[0];
            $last_company_name  = str_replace("$first_company_name ", '', $company_data->name);
            $code_payment       = getKeyRandom();

            if ($request->input('token') && $request->input('issuer_id')) {
                $check_payment_method = 'card';
            }

            if (
                roundDecimal($plan_data->value) != roundDecimal($request->input('transaction_amount')) ||
                roundDecimal($plan_data->value) != roundDecimal($plan_value)
            ) {
                return response()->json(['errors' => 'Valor não corresponde ao valor do plano selecionado.'], 400);
            }

            if ($plan_id != $plan) {
                return response()->json(['errors' => 'Plano não reconhecido.'], 400);
            }

            switch ($check_payment_method) {
                case 'pix':
                    $createRequest = [
                        'external_reference' => $code_payment,
                        "transaction_amount" => roundDecimal($plan_data->value),
                        "description"        => $plan_data->name,
                        "payment_method_id"  => $request->input('payment_method_id'),
                        "payer" => array(
                            "email"          => $request->input('payer')['email'],
                            "first_name"     => $first_company_name,
                            "last_name"      => $last_company_name,
                            "identification" => array(
                                "type"       => $company_data->type_person === 'pf' ? "CPF" : "CNPJ",
                                "number"     => onlyNumbers($company_data->cpf_cnpj)
                            ),
                            "address" => array(
                                "zip_code"      => $company_data->cep,
                                "street_name"   => $company_data->address,
                                "street_number" => $company_data->number,
                                "neighborhood"  => $company_data->neigh,
                                "city"          => $company_data->city,
                                "federal_unit"  => $company_data->state,
                            )
                        )
                    ];
                    break;
                case 'bolbradesco':
                case 'pec':
                    $payer = $request->input('payer');
                    $createRequest = [
                        'external_reference' => $code_payment,
                        "transaction_amount" => roundDecimal($plan_data->value),
                        "description"        => $plan_data->name,
                        "payment_method_id"  => $request->input('payment_method_id'),
                        "payer" => array(
                            "email"          => $payer['email'],
                            "first_name"     => $payer['first_name'],
                            "last_name"      => $payer['last_name'],
                            "identification" => array(
                                "type"       => $payer['identification']['type'],
                                "number"     => onlyNumbers($payer['identification']['number'])
                            ),
                            "address" => array(
                                "zip_code"      => $payer['address']['zip_code'],
                                "street_name"   => $payer['address']['street_name'],
                                "street_number" => $payer['address']['street_number'],
                                "neighborhood"  => $payer['address']['neighborhood'],
                                "city"          => $payer['address']['city'],
                                "federal_unit"  => $payer['address']['federal_unit'],
                            )
                        )
                    ];
                    break;
                case 'card':
                    $payer = $request->input('payer');
                    $createRequest = [
                        'external_reference' => $code_payment,
                        "transaction_amount" => roundDecimal($plan_data->value),
                        "description"        => $plan_data->name,
                        "payment_method_id"  => $request->input('payment_method_id'),
                        "token"              => $request->input('token'),
                        "installments"       => $request->input('installments'),
                        "issuer_id"          => $request->input('issuer_id'),
                        "payer" => array(
                            "email"          => $payer['email'],
                            "identification" => array(
                                "type"       => $payer['identification']['type'],
                                "number"     => onlyNumbers($payer['identification']['number'])
                            )
                        )
                    ];
                    break;
                default:
                    return response()->json(['errors' => 'Tipo de pagamento não encontrado.'], 400);
            }

            $client = new PaymentClient();
            $request_options = new RequestOptions();
//            $request_options->setCustomHeaders(["X-Idempotency-Key: <SOME_UNIQUE_VALUE>"]);
            $request_options->setCustomHeaders(["X-meli-session-id: {$request->input('device_id')}"]);
            $payment = $client->create($createRequest, $request_options);

            $this->validatePaymentResult($payment);
        } catch (MPApiException $exception) {
            return response()->json(['errors' => $exception->getApiResponse()], 400);
        } catch (Exception $exception) {
            return response()->json(['errors' => $exception->getMessage()], 400);
        }

        // Taxas: https://www.mercadopago.com.br/ajuda/custo-receber-pagamentos_220
        if ($payment->payment_type_id === 'bank_transfer' && $payment->payment_method_id === 'pix') {
            $netAmount = $payment->transaction_amount * 0.99; // taxa de 0.99% no pix
        } elseif ($payment->payment_type_id === 'credit_card') {
            $netAmount = $payment->transaction_amount - 3.49; // taxa de R$ 3.49 no boleto
        } else {
            $netAmount = $payment->transaction_details->net_received_amount;
        }

        $dateOfExpiration = formatDateInternational($payment->date_of_expiration ?? null);
        $dateCreated = formatDateInternational($payment->date_created) ?? dateNowInternational();

        $paymentPlan = $this->plan_payment->insert(array(
            'id_transaction'    => $payment->id,
            'link_billet'       => $payment->transaction_details->external_resource_url ?? null,
            'barcode_billet'    => $payment->barcode->content ?? null,
            'date_of_expiration'=> $dateOfExpiration,
            'key_pix'           => $payment->point_of_interaction->transaction_data->qr_code ?? null,
            'base64_key_pix'    => $payment->point_of_interaction->transaction_data->qr_code_base64 ?? null,
            'payment_method_id' => $payment->payment_method_id,
            'payment_type_id'   => $payment->payment_type_id,
            'plan_id'           => $plan,
            'status_detail'     => $payment->status_detail,
            'installments'      => $payment->installments,
            'status'            => $payment->status,
            'gross_amount'      => $payment->transaction_amount,
            'net_amount'        => $netAmount,
            'client_amount'     => $payment->transaction_details->total_paid_amount,
            'company_id'        => $company_id,
            'user_created'      => $request->user()->id
        ));

        $this->plan_history->insert(array(
            'payment_id'    => $paymentPlan->id,
            'status_detail' => $payment->status_detail,
            'status'        => $payment->status,
            'status_date'   => $dateCreated
        ));

        // Pagamento foi criado. Validar a situação. Ele poder ter sido rejeitado diretamente.
        try {
            $this->mercado_pago_exception->setPayment($payment);
            $verify = $this->mercado_pago_exception->verifyTransaction();
        }  catch (Exception $exception) {
            return response()->json(['errors' => $exception->getMessage()], 400);
        }
        if ($verify['class'] == 'error') {
            return response()->json(['errors' => $verify['message']], 400);
        }

        return response()->json(['message' => $verify['message']]);
    }

    /**
     * @param   object      $payment
     * @throws  Exception
     */
    private function validatePaymentResult(object $payment) {
        if ($payment->id === null) {
            $error_message = 'Unknown error cause';

            if(!property_exists($payment, 'id') || $payment->error !== null) {
                $sdk_error_message = $payment->error->message ?? null;
                $error_message = $sdk_error_message !== null ? $sdk_error_message : $error_message;
            }

            throw new Exception($error_message);
        }
    }
}
