<?php

namespace App\Http\Controllers;

use App\Models\BillToPay;
use App\Models\BillToPayPayment;
use App\Models\FormPayment;
use App\Models\Provider;
use Exception;
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

    public function index(): Factory|View|RedirectResponse|Application
    {
        if (!hasPermission('BillsToPayView')) {
            return redirect()->route('dashboard')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

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
            //'late'          => $typesQuery['late'],
            'without_pay'   => $typesQuery['without_pay'],
            'paid'          => $typesQuery['paid']
        );

        return response()->json($arrTypes);
    }

    public function fetchBills(Request $request): JsonResponse
    {
        $result                 = array();
        $draw                   = $request->input('draw');
        $company_id             = $request->user()->company_id;
        $type_bill              = $request->input('type');
        $show_client_name_list  = $request->input('show_client_name_list');
        $filters                = array();
        $filter_default         = array();

        try {
            // Filtro datas
            $filters_date['dateStart']   = $request->input('start_date');
            $filters_date['dateFinish']  = $request->input('end_date');

            // Filtro fornecedor
            $provider = $request->input('provider');

            $filter_default[]['where']['bill_to_pays.company_id'] = $company_id;
            $filter_default[]['whereBetween']['bill_to_pay_payments.due_date'] = [$filters_date['dateStart'], $filters_date['dateFinish']];

            if (!empty($provider)) {
                $filters[]['where']['bill_to_pays.provider_id'] = $provider;
            }

            $fields_order = array('bill_to_pays.code','providers.name','bill_to_pay_payments.due_value','bill_to_pay_payments.due_date', '');

            switch ($type_bill) {
//                case 'late':
//                    $filter_default[]['where']['bill_to_pay_payments.due_date <'] = date(DATE_INTERNATIONAL);
//                    $filter_default[]['where']['bill_to_pay_payments.payday'] = null;
//                    break;
                case 'without_pay':
                    //$filter_default[]['where']['bill_to_pay_payments.due_date >='] = date(DATE_INTERNATIONAL);
                    $filter_default[]['where']['bill_to_pay_payments.payday'] = null;
                    break;
                case 'paid':
                    $filter_default[]['where']['bill_to_pay_payments.payday <>'] = null;
                    break;
            }

            $query = array();
            $query['select'] = [
                'bill_to_pays.id',
                'bill_to_pays.observation',
                'bill_to_pays.code',
                'providers.name as provider_name',
                'bill_to_pays.created_at',
                'bill_to_pay_payments.due_date',
                'bill_to_pay_payments.due_value',
                'bill_to_pay_payments.id as bill_payment_id',
                'bill_to_pay_payments.payment_id',
                'bill_to_pay_payments.payday'
            ];
            $query['from'] = 'bill_to_pays';
            $query['join'][] = ['bill_to_pay_payments','bill_to_pay_payments.bill_to_pay_id','=','bill_to_pays.id'];
            $query['join'][] = ['providers','providers.id','=','bill_to_pays.provider_id'];

            $data = fetchDataTable(
                $query,
                array('bill_to_pays.code', 'asc'),
                null,
                ['BillsToPayView'],
                $filters,
                $fields_order,
                $filter_default
            );

        } catch (Exception $exception) {
            return response()->json(getErrorDataTables($exception->getMessage(), $draw));
        }

        $permissionUpdate = hasPermission('BillsToPayUpdatePost');
        $permissionDelete = hasPermission('BillsToPayDeletePost');

        foreach ($data['data'] as $value) {
            $bill_code = formatCodeRental($value->code);
            $data_prop_button = "data-bill-payment-id='$value->bill_payment_id' data-bill-code='$bill_code' data-name-provider='$value->provider_name' data-date-bill='" . date('d/m/Y H:i', strtotime($value->created_at)) . "' data-due-date='" . date('d/m/Y', strtotime($value->due_date)) . "' data-payment-id='{$value->payment_id}' data-payday='" . date('d/m/Y', strtotime($value->payday)) . "' data-due-value='" . number_format($value->due_value, 2, ',', '.') . "' data-description='$value->observation' ";

            $txt_btn_paid = $type_bill == 'paid' ? 'Visualizar Pagamento' : 'Visualizar Lançamento';
            $buttons = "<button class='dropdown-item btnViewPayment' $data_prop_button><i class='fas fa-eye'></i> $txt_btn_paid</button>";

            if ($permissionUpdate) {
                $buttons .= "<a href='".route('bills_to_pay.edit', ['id' => $value->id])."' class='dropdown-item'><i class='fas fa-edit'></i> Gerenciar Compra</a>";
                if (in_array($type_bill, array('late', 'without_pay'))) {
                    $buttons .= "<button class='dropdown-item btnConfirmPayment' $data_prop_button><i class='fas fa-check'></i> Confirmar Pagamento</button>";
                }
            }
            if ($type_bill == 'paid' && $permissionDelete) {
                $buttons .= "<button class='dropdown-item btnReopenPayment' $data_prop_button><i class='fa-solid fa-rotate-left'></i> Reabrir Pagamento</button>";
            }

            $buttons = dropdownButtonsDataList($buttons, $value->bill_payment_id);

            $due_date = dateInternationalToDateBrazil($type_bill == 'paid' ? $value->payday : $value->due_date, DATE_BRAZIL);

            $color_badge = 'success';
            if (in_array($type_bill, array('late', 'without_pay'))) {
                if (strtotime($value->due_date) === strtotime(dateNowInternational(null, DATE_INTERNATIONAL))) {
                    $color_badge = 'warning';
                } elseif (strtotime($value->due_date) < strtotime(dateNowInternational(null, DATE_INTERNATIONAL))) {
                    $color_badge = 'danger';
                }
            }

            $due_date = "<div class='badge badge-pill badge-lg badge-$color_badge'>$due_date</div>";

            $result[] = array(
                $bill_code,
                "<div class='d-flex flex-wrap'><span class='font-weight-bold w-100'>$value->provider_name</span></div>",
                'R$ ' . number_format($value->due_value, 2, ',', '.'),
                $due_date,
                $buttons,
                "payment_id" => $value->bill_payment_id,
                "due_date"   => strtotime($type_bill == 'paid' ? $value->payday : $value->due_date),
                "due_value"  => $value->due_value,
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

    public function confirmPayment(Request $request): JsonResponse
    {
        if (!hasPermission('BillsToPayUpdatePost')) {
            return response()->json(null, 400);
        }

        $payment_id     = explode('-',$request->input('payment_id'));
        $form_payment_id= $request->input('form_payment');
        $date_payment   = $request->input('date_payment');
        $company_id     = $request->user()->company_id;
        $payments       = $this->bill_to_pay_payment->getPayments($company_id, $payment_id);
        $user_id        = $request->user()->id;

        if (!count($payments)) {
            return response()->json(array('success' => false, 'message' => "Pagamento não encontrado."));
        }

        $bill_to_pay_read = array();
        $provider_id = null;
        foreach ($payments as $payment) {
            $bill_to_pay = $this->bill_to_pay_payment->getPayments($company_id, $payment->bill_to_pay_id);

            // Conta não encontrada ou já lida.
            if (!$bill_to_pay || in_array($bill_to_pay->id, $bill_to_pay_read)) {
                continue;
            }

            if (is_null($provider_id)) {
                $provider_id = $bill_to_pay->provider_id;
            } elseif ($provider_id != $bill_to_pay->provider_id) {
                return response()->json(array('success' => false, 'message' => "Selecione uma loja para efetuar múltiplos pagamentos"));
            }

            $bill_to_pay_read[] = $bill_to_pay->id;
        }

        $data_form_payment = $this->form_payment->getById($form_payment_id);

        if (!$data_form_payment) {
            return response()->json(array('success' => false, 'message' => "Forma de pagamento não encontrado."));
        }

        foreach ($payments as $payment) {
            $this->bill_to_pay->updateById(array(
                'payday'        => $date_payment,
                'payment_name'  => $data_form_payment->name,
                'payment_id'    => $data_form_payment->id,
                'user_update'   => $user_id
            ), $payment->id);
        }

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

    public function edit(int $id): Factory|View|RedirectResponse|Application
    {
        if (!hasPermission('BillsToPayUpdatePost')) {
            return redirect()->route('bills_to_pay.index')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        $company_id = Auth::user()->__get('company_id');
        $bill_to_pay = $this->bill_to_pay->getBill($company_id, $id);
        $bill_to_pay_payment = $this->bill_to_pay_payment->getPayments($company_id, $id);

        return view('bills_to_pay.update', compact('bill_to_pay'));
    }

    private function formatDataToCreateAndUpdate(Request $request, bool $create = true): array
    {
        // data provider
        $company_id                     = $request->user()->company_id;
        $user_id                        = $request->user()->id;
        $provider                       = (int)filter_var($request->input('provider'), FILTER_SANITIZE_NUMBER_INT);
        $description                    = strip_tags($request->input('description'), ALLOWABLE_TAGS);
        $value                          = transformMoneyBr_En(filter_var($request->input('value'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL));
        $form_payment                   = (int)filter_var($request->input('form_payment'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $calculate_net_amount_automatic = (bool)$request->input('calculate_net_amount_automatic');
        $automatic_parcel_distribution  = (bool)$request->input('automatic_parcel_distribution');
        $user_field                     = $create ? 'user_insert' : 'user_update';

        $bill = array(
            'company_id'                    => $company_id,
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

        if ($create) {
            $bill['code'] = $this->bill_to_pay->getNextCode($company_id);
        }

        return $bill;
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

        $arrPayments        = $responsePayment->arrPayment;
        $create             = $this->formatDataToCreateAndUpdate($request);
        $createBillToPay    = $this->bill_to_pay->insert($create);
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
                return response()->json(['success' => true, 'message' => 'Compra cadastrada com sucesso!', 'bill_to_pay_id' => $bill_to_pay_id]);
            }

            return redirect()->route('bills_to_pay.index')
                ->with('success', "Compra com o código $create[code], cadastrada com sucesso!");
        }

        DB::rollBack();

        if ($isAjax) {
            return response()->json(['success' => false, 'message' => 'Não foi possível cadastrar a compra, tente novamente!']);
        }

        return redirect()->back()
            ->withErrors(['Não foi possível cadastrar a compra, tente novamente!'])
            ->withInput();

    }

    public function update(int $id, Request $request): JsonResponse|RedirectResponse
    {
        $isAjax      = isAjax();
        $company_id  = $request->user()->company_id;
        $bill_to_pay = $this->bill_to_pay->getBill($company_id, $id);

        if (!$bill_to_pay) {
            return response()->json(['success' => true, 'message' => 'Compra não encontrada']);
        }

        // Pagamento
        $responsePayment = $this->setPaymentRental($request, $id);
        if (isset($responsePayment->error)) {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => $responsePayment->error]);
            }

            return redirect()->back()
                ->withErrors([$responsePayment->error])
                ->withInput();
        }

        $arrPayments         = $responsePayment->arrPayment;
        $bill_to_pay_payment = $this->bill_to_pay_payment->getPaymentsByBillId($company_id, $id);
        $recreate_payments   = !$this->makeValidationPaymentToUpdate($company_id, $bill_to_pay,$bill_to_pay_payment, $request, $arrPayments);

        // Mudou o preço.
        if ($recreate_payments) {
            // Não mostramos o alerta ainda.
            if ($request->has('confirm_update_payment') && !$request->input('confirm_update_payment')) {
                $show_alert_payment = count($this->bill_to_pay_payment->getPaymentsPaidByBill($company_id, $id)) > 0;

                // Se já tinha algum pagamento pago, precisa mostrar o alerta.
                if ($show_alert_payment) {
                    return response()->json(['success' => true, 'message' => null, 'show_alert_update_payment' => true]);
                }
            }
        }

        DB::beginTransaction();// Iniciando transação manual para evitar updates não desejáveis.

        $updateBillToPay = $this->bill_to_pay->edit($this->formatDataToCreateAndUpdate($request, false), $id);

        if ($recreate_payments) {
            $this->bill_to_pay_payment->remove($company_id, $id);
            foreach ($arrPayments as $arrPayment) {
                $this->bill_to_pay_payment->insert($arrPayment);
            }
        }

        if ($updateBillToPay) {
            DB::commit();
            if ($isAjax) {
                return response()->json(['success' => true, 'message' => 'Compra alterada com sucesso!', 'bill_to_pay_id' => $id]);
            }

            return redirect()->route('bills_to_pay.index')
                ->with('success', "Compra com o código $bill_to_pay->code, alterada com sucesso!");
        }

        DB::rollBack();

        if ($isAjax) {
            return response()->json(['success' => false, 'message' => 'Não foi possível cadastrar a compra, tente novamente!']);
        }

        return redirect()->back()
            ->withErrors(['Não foi possível cadastrar a compra, tente novamente!'])
            ->withInput();
    }

    private function makeValidationPaymentToUpdate(int $company_id, $arrBillToPay, $arrPayment, $request, $arrPaymentsRequest): bool
    {
        /**
         * * Pagamento
         * Valor líquido mudou, limpa pagamentos.
         * Valor bruto mudou, limpa pagamentos.
         * Valor de desconto mudou, limpa pagamentos.
         * Valor de acréscimo mudou, limpa pagamentos.
         * Ler todos os pagamentos (existente e enviados):
         * - Alterou alguma informação do pagamento, limpa pagamentos.
         * - Pagamento tem no banco e não tem na requisição, é uma remoção.
         * - Pagamento não tem no banco e tem na requisição, é uma inclusão.
         */

        // A quantidade de pagamentos no banco de dados difere da quantidade enviada na requisição.
        if (count($arrPaymentsRequest) != count($arrPayment)) {
            // Limpar pagamentos.
            return false;
        }

        if (roundDecimal($arrBillToPay->gross_value) != roundDecimal(transformMoneyBr_En(filter_var($request->input('value'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL)))) {
            // Limpar pagamentos.
            return false;
        }

        foreach ($arrPayment as $payment) {
            // Um dos pagamentos enviado na requisição, não existe no banco de dados.
            if (!$this->bill_to_pay_payment->getPaymentByRentalAndDueDateAndValue($company_id, $arrBillToPay->id, $payment['due_date'], $payment['due_value'])) {
                // Limpar pagamentos.
                return false;
            }
        }

        // Ler os pagamentos já existente no banco de dados.
        foreach ($arrPayment as $payment) {
            $payment_found = false;
            // Ler os pagamentos enviados na requisição.
            foreach ($arrPaymentsRequest as $payment_request) {
                // Se o pagamento do banco de dados foi encontrado na requisição enviada, defino '$payment_found' como 'true', para não excluir as pagamentos.
                if ($payment->due_date == $payment_request['due_date'] && $payment->due_value == $payment_request['due_value']) {
                    $payment_found = true;
                    break;
                }
            }
            if (!$payment_found) {
                // Limpar pagamentos.
                return false;
            }
        }

        return true;
    }

    public function setPaymentRental(Request $request, int $id = 0): StdClass
    {
        $company_id = $request->user()->company_id;
        $response = new StdClass();
        $response->arrPayment = array();

        $value = transformMoneyBr_En($request->input('value'));

        $automaticParcelDistribution = (bool)$request->input('automatic_parcel_distribution');

        // existe parcelamento
        $priceTemp = 0;

        $valueSumParcel = 0;
        $qtyParcel = count($request->input('due_date'));
        $valueParcel = roundDecimal($value / $qtyParcel);

        foreach ($request->input('due_date') as $parcel => $_) {
            if ($automaticParcelDistribution) {
                if (($parcel + 1) === $qtyParcel) {
                    $valueParcel = roundDecimal($value - $valueSumParcel);
                }
                $valueSumParcel += $valueParcel;
            } else {
                $valueParcel = transformMoneyBr_En($request->input('value_parcel')[$parcel]);
            }

            $priceTemp += $valueParcel;

            $response->arrPayment[] = array(
                'company_id'    => $company_id,
                'bill_to_pay_id'=> $id,
                'parcel'        => $parcel + 1,
                'due_day'       => $request->input('due_day')[$parcel],
                'due_date'      => $request->input('due_date')[$parcel],
                'due_value'     => $valueParcel,
                'user_insert'   => $request->user()->id
            );
        }

        // os valores das parcelas não corresponde ao valor líquido
        if (roundDecimal($priceTemp) != roundDecimal($value)) {
            $response->error = 'A soma das parcelas deve corresponder ao valor líquido.';
            return $response;
        }

        // Pagamento não encontrado, cria o pagamento para o dia de hoje.
        if (!count($response->arrPayment)) {
            $response->arrPayment[] = array(
                'company_id'    => $company_id,
                'bill_to_pay_id'=> $id,
                'parcel'        => 1,
                'due_day'       => 0,
                'due_date'      => dateNowInternational(),
                'due_value'     => $value,
                'user_insert'   => $request->user()->id
            );
        }

        return $response;
    }

    public function reopenPayment(Request $request): JsonResponse
    {
        if (!hasPermission('BillsToPayDeletePost')) {
            return response()->json(null, 400);
        }

        $payment_id = explode('-',$request->input('payment_id'));
        $company_id = $request->user()->company_id;
        $payments   = $this->bill_to_pay_payment->getPayments($company_id, $payment_id);
        $user_id    = $request->user()->id;

        if (!count($payments)) {
            return response()->json(array('success' => false, 'message' => "Pagamento não encontrado."));
        }

        foreach ($payments as $payment) {
            $this->bill_to_pay_payment->updateById(array(
                'payday'        => null,
                'payment_name'  => null,
                'payment_id'    => null,
                'user_update'   => $user_id
            ), $payment->id);
        }

        return response()->json(array('success' => true, 'message' => "Pagamento reaberto!"));
    }

    public function getPayments(int $id): JsonResponse
    {
        if (!hasPermission('BillsToPayView')) {
            return response()->json();
        }

        $company_id = Auth::user()->__get('company_id');
        $equipments = $this->bill_to_pay_payment->getPaymentsByBillId($company_id, $id);

        return response()->json($equipments);
    }

    public function delete(int $id, Request $request): JsonResponse
    {
        $company_id = $request->user()->company_id;

        if (!$this->bill_to_pay->getBill($company_id, $id)) {
            return response()->json(['success' => false, 'message' => 'Não foi possível localizar a compra!']);
        }

        DB::beginTransaction();// Iniciando transação manual para evitar updates não desejáveis.

        if (!$this->bill_to_pay_payment->remove($company_id, $id) || !$this->bill_to_pay->remove($company_id, $id)) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Não foi possível excluir a compra!']);
        }

        DB::commit();

        return response()->json(['success' => true, 'message' => 'Compra excluída com sucesso!']);
    }

    public function getBillsForDate(string $date): JsonResponse
    {
        if (!hasPermission('BillsToReceiveView')) {
            return response()->json();
        }

        $company_id = Auth::user()->__get('company_id');

        return response()->json(array('total' => $this->bill_to_pay_payment->getBillsForDate($company_id, $date)));
    }

    public function fetchBillForDate(Request $request): JsonResponse
    {
        $result         = array();
        $draw           = $request->input('draw');
        $company_id     = $request->user()->company_id;
        $date_filter    = dateBrazilToDateInternational($request->input('date_filter'));
        $filters        = array();
        $filter_default = array();

        try {
            $filter_default[]['where']['bill_to_pays.company_id'] = $company_id;
            $filter_default[]['whereDate']['bill_to_pay_payments.payday'] = $date_filter;

            $fields_order = array('bill_to_pays.code','providers.name','bill_to_pay_payments.due_value');

            $query = array();
            $query['select'] = [
                'bill_to_pays.id',
                'bill_to_pays.code',
                'providers.name as provider_name',
                'bill_to_pays.created_at',
                'bill_to_pay_payments.due_date',
                'bill_to_pay_payments.due_value',
                'bill_to_pay_payments.id as bill_payment_id',
                'bill_to_pay_payments.payment_id',
                'bill_to_pay_payments.payday'
            ];
            $query['from'] = 'bill_to_pays';
            $query['join'][] = ['bill_to_pay_payments','bill_to_pay_payments.bill_to_pay_id','=','bill_to_pays.id'];
            $query['join'][] = ['providers','providers.id','=','bill_to_pays.provider_id'];

            $data = fetchDataTable(
                $query,
                array('bill_to_pays.code', 'asc'),
                null,
                ['BillsToPayView'],
                $filters,
                $fields_order,
                $filter_default
            );

        } catch (Exception $exception) {
            return response()->json(getErrorDataTables($exception->getMessage(), $draw));
        }

        foreach ($data['data'] as $value) {
            $result[] = array(
                formatCodeRental($value->code),
                $value->provider_name,
                formatMoney($value->due_value, 2, 'R$ ')
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
}
