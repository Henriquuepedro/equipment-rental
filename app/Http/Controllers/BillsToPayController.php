<?php

namespace App\Http\Controllers;

use App\Models\BillToPay;
use App\Models\BillToPayPayment;
use App\Models\FormPayment;
use App\Models\Provider;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use StdClass;

class BillsToPayController extends Controller
{
    private Provider $provider;
    private BillToPay $bill_to_pay;
    private BillToPayPayment $bill_to_pay_payment;
    private FormPayment $form_payment;

    public function __construct()
    {
        $this->provider = new Provider();
        $this->form_payment = new FormPayment();
        $this->bill_to_pay = new BillToPay();
        $this->bill_to_pay_payment = new BillToPayPayment();
    }

    public function index(): Factory|View|Application
    {
        $company_id = Auth::user()->__get('company_id');
        $providers = $this->provider->getProviders($company_id);

        return view('bills_to_pay.index', compact('providers'));
    }

    public function getQtyTypeBills(Request $request): JsonResponse
    {
        $company_id = $request->user()->company_id;
        $provider   = $request->input('provider');
        $start_date = $request->input('start_date');
        $end_date   = $request->input('end_date');

        $typesQuery = $this->bill_to_pay->getCountTypePayments($company_id, $provider, $start_date, $end_date);

        $arrTypes = array(
            'late'          => $typesQuery['late'],
            'without_pay'   => $typesQuery['without_pay'],
            'paid'          => $typesQuery['paid']
        );

        return response()->json($arrTypes);
    }

    public function fetchBills(Request $request): JsonResponse
    {
        if (!hasPermission('BillsToPayView')) {
            return response()->json();
        }

        $orderBy    = array();
        $result     = array();
        $searchUser = null;

        $filters    = [];
        $ini        = $request->input('start');
        $draw       = $request->input('draw');
        $length     = $request->input('length');
        $company_id = $request->user()->company_id;
        $typeBill   = $request->input('type');
        $start_date = $request->input('start_date');
        $end_date   = $request->input('end_date');

        // Filtro fornecedor
        $provider = $request->input('provider') ?? (int)$request->input('provider');
        if (empty($provider)) {
            $provider = null;
        }
        $filters['provider']    = $provider;
        $filters['start_date']  = $start_date;
        $filters['end_date']    = $end_date;

        $search = $request->input('search');
        if ($search['value']) {
            $searchUser = $search['value'];
        }

        if ($request->input('order')) {
            if ($request->input('order')[0]['dir'] == "asc") {
                $direction = "asc";
            } else {
                $direction = "desc";
            }

            $fieldsOrder = array('bill_to_pays.code','providers.name','bill_to_pay_payments.due_value','bill_to_pay_payments.due_date', '');
            $fieldOrder =  $fieldsOrder[$request->input('order')[0]['column']];
            if ($fieldOrder != "") {
                $orderBy['field'] = $fieldOrder;
                $orderBy['order'] = $direction;
            }
        }

        $data = $this->bill_to_pay->getBills($company_id, $filters, $ini, $length, $searchUser, $orderBy, $typeBill);

        $permissionUpdate = hasPermission('BillsToPayUpdatePost');
        $permissionDelete = hasPermission('BillsToPayDeletePost');

        foreach ($data as $key => $value) {
            $bill_code = formatCodeRental($value['code']);
            $data_prop_button = "data-bill-payment-id='{$value['bill_payment_id']}' data-bill-code='$bill_code' data-name-provider='{$value['provider_name']}' data-date-bill='" . date('d/m/Y H:i', strtotime($value['created_at'])) . "' data-due-date='" . date('d/m/Y', strtotime($value['due_date'])) . "' data-payment-id='{$value['payment_id']}' data-payday='" . date('d/m/Y', strtotime($value['payday'])) . "' data-due-value='" . number_format($value['due_value'], 2, ',', '.') . "'";

            $txt_btn_paid = $typeBill == 'paid' ? 'Visualizar Pagamento' : 'Visualizar Lançamento';
            $buttons = "<button class='dropdown-item btnViewPayment' $data_prop_button><i class='fas fa-eye'></i> $txt_btn_paid</button>";

            if ($permissionUpdate && in_array($typeBill, array('late', 'without_pay'))) {
                $buttons .= "<button class='dropdown-item btnConfirmPayment' $data_prop_button><i class='fas fa-check'></i> Confirmar Pagamento</button>";
            }

            $buttons = "<div class='row'><div class='col-12'><div class='dropdown dropleft'>
                            <button class='btn btn-outline-primary icon-btn dropdown-toggle' type='button' id='dropActionsBill-{$value['bill_payment_id']}' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                              <i class='fa fa-cog'></i>
                            </button>
                            <div class='dropdown-menu' aria-labelledby='dropActionsBill-{$value['bill_payment_id']}'>$buttons</div</div>
                        </div>";

            $result[$key] = array(
                $bill_code,
                "<div class='d-flex flex-wrap'><span class='font-weight-bold w-100'>{$value['provider_name']}</span></div>",
                'R$ ' . number_format($value['due_value'], 2, ',', '.'),
                date('d/m/Y', strtotime($value['due_date'])),
                $buttons
            );
        }

        $output = array(
            "draw" => $draw,
            "recordsTotal" => $this->bill_to_pay->getBills($company_id, $filters, null, null, null, array(), $typeBill, true),
            "recordsFiltered" => $this->bill_to_pay->getBills($company_id, $filters, null, null, $searchUser, array(), $typeBill, true),
            "data" => $result
        );

        return response()->json($output);
    }

    public function confirmPayment(Request $request): JsonResponse
    {
        $payment_id     = $request->input('payment_id');
        $form_payment_id= $request->input('form_payment');
        $date_payment   = $request->input('date_payment');
        $company_id     = $request->user()->company_id;

        if (!$this->bill_to_pay->getBill($company_id, $payment_id)) {
            if (!hasPermission('BillsToPayUpdatePost')) {
                return response()->json(null, 400);
            }
        }

        $data_form_payment = $this->form_payment->getById($form_payment_id);

        if (!$data_form_payment) {
            return response()->json(array('success' => false, 'message' => "Forma de pagamento nnão encontrado."));
        }

        $this->bill_to_pay->updateById(array(
            'payday'        => $date_payment,
            'payment_name'  => $data_form_payment->name,
            'payment_id'    => $data_form_payment->id
        ), $payment_id);

        return response()->json(array('success' => true, 'message' => "Pagamento confirmado!"));
    }

    public function create(): Factory|View|RedirectResponse|Application
    {
        if (!hasPermission('BillsToPayCreatePost')) {
            return redirect()->route('bills_to_pay.index')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        return view('bills_to_pay.create');
    }

    private function formatDataToCreateAndUpdate(Request $request, bool $create = true): array
    {
        // data provider
        $company_id                     = $request->user()->company_id;
        $user_id                        = $request->user()->id;
        $provider                       = (int)filter_var($request->input('provider'), FILTER_SANITIZE_NUMBER_INT);
        $description                    = strip_tags($request->input('description'), $this->allowableTags);
        $value                          = transformMoneyBr_En(filter_var($request->input('value'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL));
        $form_payment                   = (int)filter_var($request->input('form_payment'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $calculate_net_amount_automatic = (bool)$request->input('calculate_net_amount_automatic');
        $automatic_parcel_distribution  = (bool)$request->input('automatic_parcel_distribution');
        $user_field                     = $create ? 'user_insert' : 'user_update';

        return array(
            'company_id'                    => $company_id,
            'code'                          => $this->bill_to_pay->getNextCode($company_id),
            'provider_id'                   => $provider,
            'gross_value'                   => $value,
            'extra_value'                   => 0,
            'discount_value'                => 0,
            'net_value'                     => $value,
            'calculate_net_amount_automatic'=> $calculate_net_amount_automatic,
            'automatic_parcel_distribution' => $automatic_parcel_distribution,
            'form_payment'                  => $form_payment,
            'observation'                   => $description,
            $user_field                     => $user_id
        );
    }

    public function insert(Request $request): JsonResponse|RedirectResponse
    {
        $isAjax = isAjax();
        DB::beginTransaction();// Iniciando transação manual para evitar updates não desejáveis

        // Pagamento
        $responsePayment = $this->setPaymentRental($request);
        if (isset($responsePayment->error)) {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $responsePayment->error]);
            }

            return redirect()->back()
                ->withErrors([$responsePayment->error])
                ->withInput();
        }

        $arrPayments         = $responsePayment->arrPayment;
        $createBillToPay    = $this->bill_to_pay->insert($this->formatDataToCreateAndUpdate($request));
        $bill_to_pay_id     = $createBillToPay->id;

        foreach ($arrPayments as $keyPayment => $_) {
            if (isset($_['bill_to_pay_id'])) {
                $arrPayments[$keyPayment]['bill_to_pay_id'] = $bill_to_pay_id;
            }
        }

        foreach ($arrPayments as $arrPayment) {
            $this->bill_to_pay_payment->insert($arrPayment);
        }

        if ($createBillToPay) {
            DB::commit();
            if ($isAjax) {
                return response()->json(['success' => true, 'message' => 'Pagamento cadastrado com sucesso!', 'bill_to_pay_id' => $bill_to_pay_id]);
            }

            return redirect()->route('bills_to_pay.index')
                ->with('success', "Pagamento com o código $bill_to_pay_id, cadastrado com sucesso!");
        }

        DB::rollBack();

        if ($isAjax) {
            return response()->json(['success' => false, 'message' => 'Não foi possível cadastrar o pagamento, tente novamente!']);
        }

        return redirect()->back()
            ->withErrors(['Não foi possível cadastrar o pagamento, tente novamente!'])
            ->withInput();

    }

    public function setPaymentRental(Request $request): StdClass
    {
        $company_id = $request->user()->company_id;
        $response = new StdClass();
        $response->arrPayment = array();

        $value = transformMoneyBr_En($request->input('value'));

        $automaticParcelDistribution = (bool)$request->input('automatic_parcel_distribution');

        // existe parcelamento
        $daysTemp = null;
        $priceTemp = 0;

        $valueSumParcel = 0;
        $qtyParcel = count($request->input('due_date'));
        $valueParcel = (float)number_format($value / $qtyParcel, 2,'.','');

        foreach ($request->input('due_date') as $parcel => $_) {
            if ($automaticParcelDistribution) {
                if (($parcel + 1) === $qtyParcel) {
                    $valueParcel = (float)number_format($value - $valueSumParcel,2,'.','');
                }
                $valueSumParcel += $valueParcel;
            } else {
                $valueParcel = transformMoneyBr_En($request->input('value_parcel')[$parcel]);
            }

            if ($daysTemp === null) {
                $daysTemp = $request->input('due_day')[$parcel];
            } elseif ($daysTemp >= $request->input('due_day')[$parcel]) {
                $response->error = 'A ordem dos vencimentos devem ser informados em ordem crescente.';
                return $response;
            } else {
                $daysTemp = $request->input('due_day')[$parcel];
            }

            $priceTemp += $valueParcel;

            $response->arrPayment[] = array(
                'company_id'    => $company_id,
                'bill_to_pay_id'=> 0,
                'parcel'        => $parcel + 1,
                'due_day'       => $request->input('due_day')[$parcel],
                'due_date'      => $request->input('due_date')[$parcel],
                'due_value'     => $valueParcel,
                'user_insert'   => $request->user()->id
            );
        }

        // os valores das parcelas não corresponde ao valor líquido
        if (number_format($priceTemp,2, '.','') != number_format($value,2, '.','')) {
            $response->error = 'A soma das parcelas deve corresponder ao valor líquido.';
            return $response;
        }

        // Pagamento não encontrado, cria o pagamento para o dia de hoje.
        if (!count($response->arrPayment)) {
            $response->arrPayment[] = array(
                'company_id'    => $company_id,
                'bill_to_pay_id'=> 0,
                'parcel'        => 1,
                'due_day'       => 0,
                'due_date'      => date(DATE_INTERNATIONAL),
                'due_value'     => $value,
                'user_insert'   => $request->user()->id
            );
        }

        return $response;
    }
}
