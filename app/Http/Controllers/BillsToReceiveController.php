<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\FormPayment;
use App\Models\Rental;
use App\Models\RentalPayment;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BillsToReceiveController extends Controller
{
    private Client $client;
    private RentalPayment $rental_payment;
    private Rental $rental;
    private FormPayment $form_payment;

    public function __construct()
    {
        $this->client = new Client();
        $this->rental_payment = new RentalPayment();
        $this->rental = new Rental();
        $this->form_payment = new FormPayment();
    }

    public function index(string $filter_start_date = null, string $filter_end_date = null, int $client_id = null): Factory|View|RedirectResponse|Application
    {
        if (!hasPermission('BillsToReceiveView')) {
            return redirect()->route('dashboard')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        $company_id = Auth::user()->__get('company_id');
        $clients = $this->client->getClients($company_id);

        return view('bills_to_receive.index', compact('clients', 'filter_start_date', 'filter_end_date', 'client_id'));
    }

    public function getQtyTypeRentals(Request $request): JsonResponse
    {
        if (!hasPermission('BillsToReceiveView')) {
            return response()->json(array(
                //'late'          => 0,
                'without_pay'   => 0,
                'paid'          => 0
            ));
        }

        $company_id = $request->user()->company_id;
        $client = $request->input('client');
        $start_date = $request->input('start_date');
        $end_date   = $request->input('end_date');

        $typesQuery = $this->rental_payment->getCountTypePayments($company_id, $client, $start_date, $end_date);

        $arrTypes = array(
            //'late'          => $typesQuery['late'],
            'without_pay'   => $typesQuery['without_pay'],
            'paid'          => $typesQuery['paid']
        );

        return response()->json($arrTypes);
    }

    public function fetchRentals(Request $request): JsonResponse
    {
        $result                 = array();
        $draw                   = $request->input('draw');
        $company_id             = $request->user()->company_id;
        $type_rental            = $request->input('type');
        $show_client_name_list  = $request->input('show_client_name_list');
        $filters                = array();
        $filter_default         = array();

        try {
            // Filtro datas
            $filters_date['dateStart']   = $request->input('start_date');
            $filters_date['dateFinish']  = $request->input('end_date');

            // Filtro cliente
            $client = $request->input('client');

            $filter_default[]['where']['rentals.company_id'] = $company_id;
            $filter_default[]['whereBetween']['rental_payments.due_date'] = [$filters_date['dateStart'], $filters_date['dateFinish']];

            if (!empty($client)) {
                $filters[]['where']['rentals.client_id'] = $client;
            }

            $fields_order   = array(
                'rentals.code',
                [
                    'clients.name',
                    'rentals.address_name',
                    'rentals.address_number',
                    'rentals.address_zipcode',
                    'rentals.address_neigh',
                    'rentals.address_city',
                    'rentals.address_state'
                ],
                'rental_payments.due_value',
                'rental_payments.due_date',
                ''
            );

            switch ($type_rental) {
//                case 'late':
//                    $filter_default[]['where']['rental_payments.due_date <'] = date(DATE_INTERNATIONAL);
//                    $filter_default[]['where']['rental_payments.payday'] = null;
//                    break;
                case 'without_pay':
                    //$filter_default[]['where']['rental_payments.due_date >='] = date(DATE_INTERNATIONAL);
                    $filter_default[]['where']['rental_payments.payday'] = null;
                    break;
                case 'paid':
                    $filter_default[]['where']['rental_payments.payday <>'] = null;
                    break;
            }

            $query = array();
            $query['select'] = [
                'rentals.id',
                'rentals.code',
                'clients.name as client_name',
                'rentals.address_name',
                'rentals.address_number',
                'rentals.address_zipcode',
                'rentals.address_complement',
                'rentals.address_neigh',
                'rentals.address_city',
                'rentals.address_state',
                'rentals.created_at',
                'rental_payments.due_date',
                'rental_payments.due_value',
                'rental_payments.id as rental_payment_id',
                'rental_payments.payment_id',
                'rental_payments.payday',
            ];
            $query['from'] = 'rental_payments';
            $query['join'][] = ['rentals','rental_payments.rental_id','=','rentals.id'];
            $query['join'][] = ['clients','clients.id','=','rentals.client_id'];

            $data = fetchDataTable(
                $query,
                array('rentals.code', 'asc'),
                null,
                ['BillsToReceiveView'],
                $filters,
                $fields_order,
                $filter_default
            );

        } catch (Exception $exception) {
            return response()->json(getErrorDataTables($exception->getMessage(), $draw));
        }

        $permissionUpdate = hasPermission('BillsToReceiveUpdatePost');
        $permissionDelete = hasPermission('BillsToReceiveDeletePost');

        foreach ($data['data'] as $value) {
            $rental_code = formatCodeIndex($value->code);
            $data_prop_button = "data-rental-payment-id='$value->rental_payment_id' data-rental-code='$rental_code' data-name-client='$value->client_name' data-date-rental='" . date(DATETIME_BRAZIL_NO_SECONDS, strtotime($value->created_at)) . "' data-due-date='" . date(DATE_BRAZIL, strtotime($value->due_date)) . "' data-payment-id='$value->payment_id' data-payday='" . date(DATE_BRAZIL, strtotime($value->payday)) . "' data-due-value='" . number_format($value->due_value, 2, ',', '.') . "'";

            $txt_btn_paid = $type_rental == 'paid' ? 'Visualizar Pagamento' : 'Visualizar Lançamento';
            $buttons = "<button class='dropdown-item btnViewPayment' $data_prop_button><i class='fas fa-eye'></i> $txt_btn_paid</button>";

            if ($permissionUpdate && in_array($type_rental, array('late', 'without_pay'))) {
                $buttons .= "<button class='dropdown-item btnConfirmPayment' $data_prop_button><i class='fas fa-check'></i> Confirmar Pagamento</button>";
            }
            if ($type_rental == 'paid' && $permissionDelete) {
                $buttons .= "<button class='dropdown-item btnReopenPayment' $data_prop_button><i class='fa-solid fa-rotate-left'></i> Reabrir Pagamento</button>";
            }

            $buttons = dropdownButtonsDataList($buttons, $value->rental_payment_id);

            $due_date = dateInternationalToDateBrazil($type_rental == 'paid' ? $value->payday : $value->due_date, DATE_BRAZIL);

            $color_badge = 'success';
            if (in_array($type_rental, array('late', 'without_pay'))) {
                if (strtotime($value->due_date) === strtotime(dateNowInternational(null, DATE_INTERNATIONAL))) {
                    $color_badge = 'warning';
                } elseif (strtotime($value->due_date) < strtotime(dateNowInternational(null, DATE_INTERNATIONAL))) {
                    $color_badge = 'danger';
                }
            }

            $due_date = "<div class='badge badge-pill badge-lg badge-$color_badge'>$due_date</div>";

            $data_info_client = "<div class='d-flex flex-wrap'>";
            $data_info_client .= $show_client_name_list ? "<span class='font-weight-bold w-100'>$value->client_name</span>" : '';
            $data_info_client .= "<span class='mt-1 w-100'>$value->address_name, $value->address_number - $value->address_zipcode - $value->address_neigh - $value->address_city/$value->address_state</span></div>";

            $result[] = array(
                $rental_code,
                $data_info_client,
                'R$ ' . number_format($value->due_value, 2, ',', '.'),
                $due_date,
                $buttons,
                "payment_id" => $value->rental_payment_id,
                "due_date"   => strtotime($type_rental == 'paid' ? $value->payday : $value->due_date),
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
        if (!hasPermission('BillsToReceiveUpdatePost')) {
            return response()->json(null, 400);
        }

        $payment_id     = explode('-',$request->input('payment_id'));
        $form_payment_id= $request->input('form_payment');
        $date_payment   = $request->input('date_payment');
        $company_id     = $request->user()->company_id;
        $payments       = $this->rental_payment->getPayment($company_id, $payment_id);
        $user_id        = $request->user()->id;

        if (!count($payments)) {
            return response()->json(array('success' => false, 'message' => "Pagamento não encontrado."));
        }

        $rental_read = array();
        $client_id = null;
        foreach ($payments as $payment) {
            $rental = $this->rental->getRental($company_id, $payment->rental_id);

            // Locação não encontrada ou já lida.
            if (!$rental || in_array($rental->id, $rental_read)) {
                continue;
            }

            if (is_null($client_id)) {
                $client_id = $rental->client_id;
            } elseif ($client_id != $rental->client_id) {
                return response()->json(array('success' => false, 'message' => "Selecione um cliente para efetuar múltiplos pagamentos"));
            }

            $rental_read[] = $rental->id;
        }

        $data_form_payment = $this->form_payment->getById($form_payment_id);

        if (!$data_form_payment) {
            return response()->json(array('success' => false, 'message' => "Forma de pagamento não encontrado."));
        }

        foreach ($payments as $payment) {
            $this->rental_payment->updateById(array(
                'payday'        => $date_payment,
                'payment_name'  => $data_form_payment->name,
                'payment_id'    => $data_form_payment->id,
                'user_update'   => $user_id
            ), $payment->id);
        }

        return response()->json(array('success' => true, 'message' => "Pagamento confirmado!"));
    }

    public function getPaymentsRental(int $rental_id): JsonResponse
    {
        if (!hasPermission('BillsToReceiveView')) {
            return response()->json();
        }

        $company_id = Auth::user()->__get('company_id');

        $equipments = $this->rental_payment->getPayments($company_id, $rental_id);

        return response()->json($equipments);
    }

    public function reopenPayment(Request $request): JsonResponse
    {
        if (!hasPermission('BillsToReceiveDeletePost')) {
            return response()->json(null, 400);
        }

        $payment_id = explode('-',$request->input('payment_id'));
        $company_id = $request->user()->company_id;
        $payments   = $this->rental_payment->getPayment($company_id, $payment_id);
        $user_id    = $request->user()->id;

        if (!count($payments)) {
            return response()->json(array('success' => false, 'message' => "Pagamento não encontrado."));
        }

        foreach ($payments as $payment) {
            $this->rental_payment->updateById(array(
                'payday'        => null,
                'payment_name'  => null,
                'payment_id'    => null,
                'user_update'   => $user_id
            ), $payment->id);
        }

        return response()->json(array('success' => true, 'message' => "Pagamento reaberto!"));
    }

    public function getBillsForDate(string $date): JsonResponse
    {
        if (!hasPermission('BillsToReceiveView')) {
            return response()->json();
        }

        $company_id = Auth::user()->__get('company_id');

        return response()->json(array('total' => $this->rental_payment->getBillsForDate($company_id, $date)));
    }

    public function fetchBillForDate(Request $request): JsonResponse
    {
        $result         = array();
        $draw           = $request->input('draw');
        $client_id      = $request->input('client_id');
        $only_is_open   = $request->input('only_is_open');
        $show_address   = $request->input('show_address');
        $company_id     = $request->user()->company_id;
        $date_filter    = dateBrazilToDateInternational($request->input('date_filter'));
        $filters        = array();
        $filter_default = array();
        DB::enableQueryLog();

        try {
            $filter_default[]['where']['rentals.company_id'] = $company_id;

            if ($only_is_open) {
                $filter_default[]['whereDate']['rental_payments.due_date'] = $date_filter;
                $filter_default[]['where']['rental_payments.payment_id'] = null;
            } else {
                $filter_default[]['whereDate']['rental_payments.payday'] = $date_filter;
            }

            if ($client_id) {
                $filter_default[]['where']['rentals.client_id'] = $client_id;
            }

            $fields_order   = array(
                'rentals.code',
                [
                    'clients.name',
                    'rentals.address_name',
                    'rentals.address_number',
                    'rentals.address_zipcode',
                    'rentals.address_neigh',
                    'rentals.address_city',
                    'rentals.address_state'
                ],
                'rental_payments.due_value'
            );

            $query = array();
            $query['select'] = [
                'rentals.id',
                'rentals.code',
                'rentals.client_id',
                'clients.name as client_name',
                'rentals.address_name',
                'rentals.address_number',
                'rentals.address_zipcode',
                'rentals.address_complement',
                'rentals.address_neigh',
                'rentals.address_city',
                'rentals.address_state',
                'rentals.created_at',
                'rental_payments.due_date',
                'rental_payments.due_value',
                'rental_payments.id as rental_payment_id',
                'rental_payments.payment_id',
                'rental_payments.payday',
            ];
            $query['from'] = 'rental_payments';
            $query['join'][] = ['rentals','rental_payments.rental_id','=','rentals.id'];
            $query['join'][] = ['clients','clients.id','=','rentals.client_id'];

            $data = fetchDataTable(
                $query,
                array('rentals.code', 'asc'),
                null,
                ['BillsToReceiveView'],
                $filters,
                $fields_order,
                $filter_default
            );

        } catch (Exception $exception) {
            return response()->json(getErrorDataTables($exception->getMessage(), $draw));
        }

        foreach ($data['data'] as $value) {
            $data_info_client = "<div class='d-flex flex-wrap'>";
            $data_info_client .= "<span class='font-weight-bold w-100'>$value->client_name</span>";
            $data_info_client .= "<span class='mt-1 w-100'>$value->address_name, $value->address_number - $value->address_zipcode - $value->address_neigh - $value->address_city/$value->address_state</span></div>";

            if (!$show_address) {
                $data_info_client = $value->client_name;
            }

            $result[] = array(
                formatCodeIndex($value->code),
                $data_info_client,
                formatMoney($value->due_value, 2, 'R$ '),
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

    public function getBillsForDateAndClient(string $date): JsonResponse
    {
        if (!hasPermission('BillsToReceiveView')) {
            return response()->json();
        }

        $company_id = Auth::user()->__get('company_id');

        return response()->json(
            array_map(function($payment) {
                    $payment['total'] = roundDecimal($payment['total']);
                    return $payment;
                },
                $this->rental_payment->getBillClientByDate($company_id, $date)->toArray()
            )
        );
    }
}
