<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\FormPayment;
use App\Models\RentalPayment;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BillsToReceiveController extends Controller
{
    private Client $client;
    private RentalPayment $rental_payment;
    private FormPayment $form_payment;

    public function __construct()
    {
        $this->client = new Client();
        $this->rental_payment = new RentalPayment();
        $this->form_payment = new FormPayment();
    }

    public function index(): Factory|View|RedirectResponse|Application
    {
        if (!hasPermission('BillsToReceiveView')) {
            return redirect()->route('dashboard')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        $company_id = Auth::user()->__get('company_id');
        $clients = $this->client->getClients($company_id);

        return view('bills_to_receive.index', compact('clients'));
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
        $result         = array();
        $draw           = $request->input('draw');
        $company_id     = $request->user()->company_id;
        $type_rental    = $request->input('type');
        $filters        = array();
        $filter_default = array();

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
            return response()->json(array(
                    "draw"              => $draw,
                    "recordsTotal"      => 0,
                    "recordsFiltered"   => 0,
                    "data"              => $result,
                    "message"           => $exception->getMessage()
                )
            );
        }

        $permissionUpdate = hasPermission('BillsToReceiveUpdatePost');
        $permissionDelete = hasPermission('BillsToReceiveDeletePost');

        foreach ($data['data'] as $value) {
            $rental_code = formatCodeRental($value->code);
            $data_prop_button = "data-rental-payment-id='$value->rental_payment_id' data-rental-code='$rental_code' data-name-client='$value->client_name' data-date-rental='" . date('d/m/Y H:i', strtotime($value->created_at)) . "' data-due-date='" . date('d/m/Y', strtotime($value->due_date)) . "' data-payment-id='$value->payment_id' data-payday='" . date('d/m/Y', strtotime($value->payday)) . "' data-due-value='" . number_format($value->due_value, 2, ',', '.') . "'";

            $txt_btn_paid = $type_rental == 'paid' ? 'Visualizar Pagamento' : 'Visualizar Lançamento';
            $buttons = "<button class='dropdown-item btnViewPayment' $data_prop_button><i class='fas fa-eye'></i> $txt_btn_paid</button>";

            if ($permissionUpdate && in_array($type_rental, array('late', 'without_pay'))) {
                $buttons .= "<button class='dropdown-item btnConfirmPayment' $data_prop_button><i class='fas fa-check'></i> Confirmar Pagamento</button>";
            }

            $buttons = dropdownButtonsDataList($buttons, $value->rental_payment_id);

            $due_date = date('d/m/Y', strtotime($value->due_date));

            $color_badge = 'success';
            if (in_array($type_rental, array('late', 'without_pay'))) {
                if (strtotime($value->due_date) === strtotime(dateNowInternational(null, DATE_INTERNATIONAL))) {
                    $color_badge = 'warning';
                } elseif (strtotime($value->due_date) < strtotime(dateNowInternational(null, DATE_INTERNATIONAL))) {
                    $color_badge = 'danger';
                }
            }

            $due_date = "<div class='badge badge-pill badge-lg badge-$color_badge'>$due_date</div>";

            $result[] = array(
                $rental_code,
                "<div class='d-flex flex-wrap'>
                    <span class='font-weight-bold w-100'>$value->client_name</span>
                    <span class='mt-1 w-100'>$value->address_name, $value->address_number - $value->address_zipcode - $value->address_neigh - $value->address_city/$value->address_state</span>
                </div>",
                'R$ ' . number_format($value->due_value, 2, ',', '.'),
                $due_date,
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

    public function confirmPayment(Request $request): JsonResponse
    {
        $payment_id     = $request->input('payment_id');
        $form_payment_id= $request->input('form_payment');
        $date_payment   = $request->input('date_payment');
        $company_id     = $request->user()->company_id;

        if (!$this->rental_payment->getPayment($company_id, $payment_id)) {
            if (!hasPermission('BillsToReceiveUpdatePost')) {
                return response()->json(null, 400);
            }
        }

        $data_form_payment = $this->form_payment->getById($form_payment_id);

        if (!$data_form_payment) {
            return response()->json(array('success' => false, 'message' => "Forma de pagamento não encontrado."));
        }

        $this->rental_payment->updateById(array(
            'payday'        => $date_payment,
            'payment_name'  => $data_form_payment->name,
            'payment_id'    => $data_form_payment->id
        ), $payment_id);

        return response()->json(array('success' => true, 'message' => "Pagamento confirmado!"));
    }

    public function getPaymentsRental(int $rental_id): JsonResponse
    {
        if (!hasPermission('RentalUpdatePost')) {
            return response()->json();
        }

        $company_id = Auth::user()->__get('company_id');

        $equipments = $this->rental_payment->getPayments($company_id, $rental_id);

        return response()->json($equipments);
    }
}
