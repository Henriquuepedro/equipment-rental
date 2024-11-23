<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use GuzzleHttp\Client;

class PlanController extends Controller
{
    private Plan $plan;

    public function __construct()
    {
        $this->plan = new Plan();
    }

    public function index(): Factory|View|Application
    {
        return view('master.plan.index');
    }

    public function edit(int $id): View|Factory|RedirectResponse|Application
    {
        $plan = $this->plan->getById($id);

        if (!$plan) {
            return redirect()->route('master.plan.index');
        }

        return view('master.plan.update', compact('plan'));
    }

    public function create(): View|Factory|RedirectResponse|Application
    {
        return view('master.plan.update');
    }

    public function fetch(Request $request): JsonResponse
    {
        $result     = array();
        $draw       = $request->input('draw');

        try {
            $filters        = array();
            $filter_default = array();
            $fields_order   = array('name', 'value', 'quantity_equipment', 'allowed_users', 'month_time', '');

            $query = array(
                'from' => 'plans'
            );

            $data = fetchDataTable(
                $query,
                array('month_time', 'desc'),
                null,
                [],
                $filters,
                $fields_order,
                $filter_default
            );

        } catch (Exception $exception) {
            return response()->json(getErrorDataTables($exception->getMessage(), $draw));
        }

        foreach ($data['data'] as $value) {
            $buttons = [
                [
                    'tag'       => 'a',
                    'title'     => 'Atualizar Plano',
                    'icon'      => 'fas fa-edit',
                    'href'      => route('master.plan.edit', ['id' => $value->id])
                ]
            ];

            if ($value->month_time == 1) {
                $buttons[] = [
                    'tag'       => 'button',
                    'title'     => $value->plan_id_gateway ? 'Atualizar plano no gateway' : 'Criar plano no gateway',
                    'icon'      => 'fas fa-arrow-up-from-bracket',
                    'attribute' => "data-plan-id='$value->id' data-discount-subscription='".formatMoney($value->discount_subscription)."' data-plan-id-gateway='$value->plan_id_gateway'",
                    'class'     => 'btnCreatePlanGateway',
                ];
            }
            $buttons = newDropdownButtonsDataList($buttons);

            $highlight = $value->highlight ? ' <i class="fa fa-star text-warning"></i>' : '';
            $month_time = $value->month_time == 1 ? ' mês' : ' meses';
            $allowed_users = empty($value->allowed_users) ? '' : ' usuários';
            $from_value = empty($value->from_value) ? '' : "<s>".formatMoney($value->from_value, 2, 'R$ ')."</s> <i class='fa fa-arrow-right'></i> ";

            $result[] = array(
                $value->name . $highlight,
                $from_value . formatMoney($value->value, 2, 'R$ '),
                $value->quantity_equipment ?  $value->quantity_equipment . ' equipamentos' : 'Ilimitado',
                ((int)$value->allowed_users ?: 'Ilimitado') . $allowed_users,
                $value->month_time . $month_time,
                $buttons
            );
        }

        $output = array(
            "draw"              => $draw,
            "recordsTotal"      => $data['recordsTotal'],
            "recordsFiltered"   => $data['recordsFiltered'],
            "data"              => $result
        );

        return response()->json($output);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        try {
            $update = $this->plan->updateById($this->formatDataPlanToSave($request), $id);
        } catch (Exception $exception) {
            return redirect()->back()
                ->withErrors(["Não foi possível cadastrar o plano! {$exception->getMessage()}"])
                ->withInput();
        }

        if (!$update) {
            return redirect()->back()
                ->withErrors(['Não foi possível atualizar o plano, tente novamente!'])
                ->withInput();
        }

        return redirect()->route('master.plan.index')
            ->with('success', "Plano atualizado com sucesso!");
    }

    public function insert(Request $request): RedirectResponse
    {
        try {
            $insert = $this->plan->insert($this->formatDataPlanToSave($request));
        } catch (Exception $exception) {
            return redirect()->back()
                ->withErrors(["Não foi possível cadastrar o plano! {$exception->getMessage()}"])
                ->withInput();
        }

        if (!$insert) {
            return redirect()->back()
                ->withErrors(['Não foi possível cadastrar o plano, tente novamente!'])
                ->withInput();
        }

        return redirect()->route('master.plan.index')
            ->with('success', "Plano cadastrado com sucesso!");
    }

    /**
     * @param Request $request
     * @return array
     * @throws Exception
     */
    private function formatDataPlanToSave(Request $request): array
    {
        $response = [
            'name'                  => filter_var($request->input('name')),
            'description'           => filter_var($request->input('description')),
            'value'                 => transformMoneyBr_En(filter_var($request->input('value'))),
            'from_value'            => transformMoneyBr_En(filter_var($request->input('from_value'))) ?: null,
            'quantity_equipment'    => filter_var($request->input('quantity_equipment')) ?: null,
            'highlight'             => (bool)$request->input('highlight'),
            'month_time'            => filter_var($request->input('month_time'), FILTER_VALIDATE_INT),
            'allowed_users'         => filter_var($request->input('allowed_users'), FILTER_VALIDATE_INT, FILTER_FLAG_EMPTY_STRING_NULL) ?: null,
            'discount_subscription' => transformMoneyBr_En(filter_var($request->input('discount_subscription'))) ?: null,
        ];

        if (!empty($response['discount_subscription']) && ($response['discount_subscription'] < 0 || $response['discount_subscription'] > 100)) {
            throw new Exception("O valor do percentual de desconto deve ser entre 0,00 e 100,00");
        }

        return $response;
    }

    public function createPlanGateway(Request $request): JsonResponse
    {
        $plan_id = $request->input('plan_id');

        $plan = $this->plan->getById($plan_id);

        if (!$plan) {
            return response()->json(array('success' => false, 'message' => "Plano não encontrado."));
        }

        if ($plan->month_time != 1) {
            return response()->json(array('success' => false, 'message' => "Plano não encontrado."));
        }

        $access_token = env('MP_ACCESS_TOKEN');

        $client = new Client();

        try {
            if (!empty($plan->discount_subscription) && $plan->discount_subscription < 100) {
                $plan->value -= ($plan->value * ($plan->discount_subscription / 100));
            }

            $options = array(
                'headers' => array(
                    'Authorization' => 'Bearer ' . $access_token,
                ),
                'json' => [
                    "reason" => $plan->name,
                    "auto_recurring" => [
                        "frequency" => 1,
                        "frequency_type" => "months",
                        "repetitions" => 12,
                        "billing_day" => 10,
                        "billing_day_proportional" => true,
                        /*"free_trial" => [
                            "frequency" => 0,
                            "frequency_type" => "days"
                        ],*/
                        "transaction_amount" => roundDecimal($plan->value),
                        "currency_id" => "BRL"
                    ],
                    "payment_methods_allowed" => [
                        "payment_types" => [
                            [
                                "id" => "credit_card"
                            ]
                        ]
                    ],
                    "back_url" => str_Replace('http://localhost:8000', 'https://teste.locai.com.br', route('plan.request'))
                ]
            );

            if (empty($plan->plan_id_gateway)) {
                $request = $client->post('https://api.mercadopago.com/preapproval_plan', $options);
                $response = json_decode($request->getBody()->getContents());

                if (empty($response->id))  {
                    return response()->json(array('success' => false, 'message' => "Não foi possível identificar o ID do plano criado. " . json_encode($response, JSON_UNESCAPED_UNICODE)));
                }

                $id = $response->id;

                $this->plan->updateById(array('plan_id_gateway' => $id), $plan_id);
            } else {
                $client->put("https://api.mercadopago.com/preapproval_plan/$plan->plan_id_gateway", $options);
            }
        } catch (Exception | GuzzleException $exception) {
            return response()->json(array('success' => false, 'message' => $exception->getMessage()));
        }

        return response()->json(array('success' => true, 'message' => "Plano criado com sucesso no gateway"));
    }
}
