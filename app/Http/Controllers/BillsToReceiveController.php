<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\FormPayment;
use App\Models\RentalPayment;
use Illuminate\Http\JsonResponse;
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

    public function index()
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
                'late'          => 0,
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
            'late'          => $typesQuery['late'],
            'without_pay'   => $typesQuery['without_pay'],
            'paid'          => $typesQuery['paid']
        );

        return response()->json($arrTypes);
    }

    public function fetchRentals(Request $request): JsonResponse
    {
        if (!hasPermission('BillsToReceiveView')) {
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
        $typeRental = $request->input('type');
        $start_date = $request->input('start_date');
        $end_date   = $request->input('end_date');

        // Filtro cliente
        $client = $request->input('client') ?? (int)$request->input('client');
        if (empty($client)) {
            $client = null;
        }

        $filters['client']      = $client;
        $filters['start_date']  = $start_date;
        $filters['end_date']    = $end_date;

        $search = $request->input('search');
        if ($search['value']) {
            $searchUser = $search['value'];
        }

        if ($request->input('order')) {
            if ($request->input('order')[0]['dir'] == "asc") $direction = "asc";
            else $direction = "desc";

            $fieldsOrder = array('rentals.code','clients.name','rental_payments.due_value', 'rental_payments.due_date', '');
            $fieldOrder =  $fieldsOrder[$request->input('order')[0]['column']];
            if ($fieldOrder != "") {
                $orderBy['field'] = $fieldOrder;
                $orderBy['order'] = $direction;
            }
        }

        $data = $this->rental_payment->getRentals($company_id, $filters, $ini, $length, $searchUser, $orderBy, $typeRental);

        $permissionUpdate = hasPermission('BillsToReceiveUpdatePost');
        $permissionDelete = hasPermission('BillsToReceiveDeletePost');

        foreach ($data as $key => $value) {
            $rental_code = formatCodeRental($value['code']);
            $data_prop_button = "data-rental-payment-id='{$value['rental_payment_id']}' data-rental-code='$rental_code' data-name-client='{$value['client_name']}' data-date-rental='" . date('d/m/Y H:i', strtotime($value['created_at'])) . "' data-due-date='" . date('d/m/Y', strtotime($value['due_date'])) . "' data-payment-id='{$value['payment_id']}' data-payday='" . date('d/m/Y', strtotime($value['payday'])) . "' data-due-value='" . number_format($value['due_value'], 2, ',', '.') . "'";

            $txt_btn_paid = $typeRental == 'paid' ? 'Visualizar Pagamento' : 'Visualizar Lançamento';
            $buttons = "<button class='dropdown-item btnViewPayment' $data_prop_button><i class='fas fa-eye'></i> $txt_btn_paid</button>";

            if ($permissionUpdate && in_array($typeRental, array('late', 'without_pay'))) {
                $buttons .= "<button class='dropdown-item btnConfirmPayment' $data_prop_button><i class='fas fa-check'></i> Confirmar Pagamento</button>";
            }

            $buttons = dropdownButtonsDataList($buttons, $value['rental_payment_id']);

            $result[$key] = array(
                $rental_code,
                "<div class='d-flex flex-wrap'>
                    <span class='font-weight-bold w-100'>{$value['client_name']}</span>
                    <span class='mt-1 w-100'>{$value['address_name']}, {$value['address_number']} - {$value['address_zipcode']} - {$value['address_neigh']} - {$value['address_city']}/{$value['address_state']}</span>
                </div>",
                'R$ ' . number_format($value['due_value'], 2, ',', '.'),
                date('d/m/Y', strtotime($value['due_date'])),
                $buttons
            );
        }

        $output = array(
            "draw" => $draw,
            "recordsTotal" => $this->rental_payment->getRentals($company_id, $filters, null, null, null, array(), $typeRental, true),
            "recordsFiltered" => $this->rental_payment->getRentals($company_id, $filters, null, null, $searchUser, array(), $typeRental, true),
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

        if (!$this->rental_payment->getPayment($company_id, $payment_id)) {
            if (!hasPermission('BillsToReceiveUpdatePost')) {
                return response()->json(null, 400);
            }
        }

        $data_form_payment = $this->form_payment->getById($form_payment_id);

        if (!$data_form_payment) {
            return response()->json(array('success' => false, 'message' => "Forma de pagamento nnão encontrado."));
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
