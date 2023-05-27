<?php

namespace App\Http\Controllers;

use App\Http\Requests\BudgetCreatePost;
use App\Http\Requests\BudgetDeletePost;
use App\Models\Budget;
use App\Models\BudgetEquipment;
use App\Models\BudgetPayment;
use App\Models\BudgetResidue;
use App\Models\Rental;
use App\Models\RentalEquipment;
use App\Models\RentalPayment;
use App\Models\RentalResidue;
use App\Models\Client;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class BudgetController extends Controller
{
    private $budget;
    private $rentalController;
    private $client;
    private $address;
    private $budget_equipment;
    private $budget_residue;
    private $budget_payment;

    public function __construct(
        RentalController $rentalController,
        Client $client,
        Address $address,
        Budget $budget,
        BudgetEquipment $budget_equipment,
        BudgetResidue $budget_residue,
        BudgetPayment $budget_payment,
        Rental $rental,
        RentalEquipment $rental_equipment,
        RentalResidue $rental_residue,
        RentalPayment $rental_payment
    )
    {
        $this->rentalController = $rentalController;
        $this->client = $client;
        $this->address = $address;
        $this->budget = $budget;
        $this->budget_equipment = $budget_equipment;
        $this->budget_residue = $budget_residue;
        $this->budget_payment = $budget_payment;
        $this->rental = $rental;
        $this->rental_equipment = $rental_equipment;
        $this->rental_residue = $rental_residue;
        $this->rental_payment = $rental_payment;
    }

    public function index()
    {
        if (!hasPermission('BudgetView')) {
            return redirect()->route('dashboard')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        return view('budget.index');
    }

    public function fetchBudgets(Request $request): JsonResponse
    {
        if (!hasPermission('BudgetView'))
            return response()->json([]);

        $orderBy    = array();
        $result     = array();
        $searchUser = null;

        $ini        = $request->start;
        $draw       = $request->draw;
        $length     = $request->length;
        $company_id = $request->user()->company_id;

        $search = $request->search;
        if ($search['value']) $searchUser = $search['value'];

        if (isset($request->order)) {
            if ($request->order[0]['dir'] == "asc") $direction = "asc";
            else $direction = "desc";

            $fieldsOrder = array('budgets.code','clients.name','budgets.created_at', '');
            $fieldOrder =  $fieldsOrder[$request->order[0]['column']];
            if ($fieldOrder != "") {
                $orderBy['field'] = $fieldOrder;
                $orderBy['order'] = $direction;
            }
        }

        $data = $this->budget->getBudgets($company_id, $ini, $length, $searchUser, $orderBy);

        $permissionUpdate = hasPermission('BudgetUpdatePost');
        $permissionDelete = hasPermission('BudgetDeletePost');

        foreach ($data as $key => $value) {
            $buttons = "<button class='dropdown-item btnApproveBudget' budget-id='{$value['id']}'><i class='fas fa-check'></i> Aprovar Orçamento</button>";
            $buttons .= $permissionDelete ? "<button class='dropdown-item btnRemoveBudget' budget-id='{$value['id']}'><i class='fas fa-trash'></i> Excluir Orçamento</button>" : '';
            $buttons .= "<a href='".route('print.budget', ['budget' => $value['id']])."' target='_blank' class='dropdown-item'><i class='fas fa-print'></i> Imprimir Orçamento</a>";

            $buttons = "<div class='row'><div class='col-12'><div class='dropdown dropleft'>
                            <button class='btn btn-outline-primary icon-btn dropdown-toggle' type='button' id='dropActionsBudget-{$value['id']}' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                              <i class='fa fa-cog'></i>
                            </button>
                            <div class='dropdown-menu' aria-labelledby='dropActionsBudget-{$value['id']}'>$buttons</div</div>
                        </div>";

            $result[$key] = array(
                formatCodeRental($value['code']),
                "<div class='d-flex flex-wrap'><span class='font-weight-bold w-100'>{$value['client_name']}</span><span class='mt-1 w-100'>{$value['address_name']}, {$value['address_number']} - {$value['address_zipcode']} - {$value['address_neigh']} - {$value['address_city']}/{$value['address_state']}</span></div>",
                date('d/m/Y H:i', strtotime($value['created_at'])),
                $buttons
            );
        }

        $output = array(
            "draw" => $draw,
            "recordsTotal" => $this->budget->getCountBudgets($company_id),
            "recordsFiltered" => $this->budget->getCountBudgets($company_id, $searchUser),
            "data" => $result
        );

        return response()->json($output);
    }

    public function create()
    {
        if (!hasPermission('BudgetCreatePost')) {
            return redirect()->route('budget.index')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }
        $budget = true;

        return view('rental.create', compact('budget'));
    }


    public function insert(BudgetCreatePost $request): JsonResponse
    {
        if (!hasPermission('BudgetCreatePost')) {
            return response()->json(['success' => false, 'message' => "Você não tem permissão para criar orçamentos."]);
        }

        $company_id  = $request->user()->company_id;
        $haveCharged = !$request->input('type_rental'); // true = com cobrança

        $clientId   = (int)$request->input('client');
        $zipcode    = onlyNumbers($request->input('cep'));
        $address    = filter_var($request->input('address'), FILTER_SANITIZE_STRING, FILTER_FLAG_EMPTY_STRING_NULL);
        $number     = filter_var($request->input('number'), FILTER_SANITIZE_STRING, FILTER_FLAG_EMPTY_STRING_NULL);
        $complement = filter_var($request->input('complement'), FILTER_SANITIZE_STRING, FILTER_FLAG_EMPTY_STRING_NULL);
        $reference  = filter_var($request->input('reference'), FILTER_SANITIZE_STRING, FILTER_FLAG_EMPTY_STRING_NULL);
        $neigh      = filter_var($request->input('neigh'), FILTER_SANITIZE_STRING, FILTER_FLAG_EMPTY_STRING_NULL);
        $city       = filter_var($request->input('city'), FILTER_SANITIZE_STRING, FILTER_FLAG_EMPTY_STRING_NULL);
        $state      = filter_var($request->input('state'), FILTER_SANITIZE_STRING, FILTER_FLAG_EMPTY_STRING_NULL);
        $lat        = filter_var($request->input('lat'), FILTER_SANITIZE_STRING, FILTER_FLAG_EMPTY_STRING_NULL);
        $lng        = filter_var($request->input('lng'), FILTER_SANITIZE_STRING, FILTER_FLAG_EMPTY_STRING_NULL);

        if (empty($clientId) || !$this->client->getClient($clientId, $company_id)) {
            return response()->json(['success' => false, 'message' => "Cliente não foi encontrado. Revise a aba de Cliente e Endereço."]);
        }

        if ($address == '') {
            return response()->json(['success' => false, 'message' => 'Informe um endereço. Revise a aba de Cliente e Endereço.']);
        }
        if ($number == '') {
            return response()->json(['success' => false, 'message' => 'Informe um número para o endereço. Revise a aba de Cliente e Endereço.']);
        }
        if ($neigh == '') {
            return response()->json(['success' => false, 'message' => 'Informe um bairro. Revise a aba de Cliente e Endereço.']);
        }
        if ($city == '') {
            return response()->json(['success' => false, 'message' => 'Informe uma cidade. Revise a aba de Cliente e Endereço.']);
        }
        if ($state == '') {
            return response()->json(['success' => false, 'message' => 'Informe um estado. Revise a aba de Cliente e Endereço.']);
        }
        if ($lat == '' || $lng == '') {
            return response()->json(['success' => false, 'message' => 'Confirme o endereço no mapa. Revise a aba de Cliente e Endereço.']);
        }

        // datas da locação
        $dateDelivery = $request->input('date_delivery') ? \DateTime::createFromFormat('d/m/Y H:i', $request->input('date_delivery')) : null;
        $dateWithdrawal = $request->input('date_withdrawal') ? \DateTime::createFromFormat('d/m/Y H:i', $request->input('date_withdrawal')) : null;
        $notUseDateWithdrawal = (bool)$request->input('not_use_date_withdrawal');

        if (!$dateDelivery) { // não reconheceu a data de entrega
            return response()->json(['success' => false, 'message' => "Data prevista de entrega precisa ser informada corretamente dd/mm/yyyy hh:mm."]);
        }

        if (!$notUseDateWithdrawal) { // usará data de retirada

            if (!$dateWithdrawal) { // não reconheceu a data de retirada
                return response()->json(['success' => false, 'message' => "Data prevista de retirada precisa ser informada corretamente dd/mm/yyyy hh:mm."]);
            }

            if ($dateDelivery->getTimestamp() >= $dateWithdrawal->getTimestamp()) { // data de entrega é maior ou igual a data de retirada
                return response()->json(['success' => false, 'message' => "Data prevista de entrega não pode ser maior ou igual que a data prevista de retirada."]);
            }
        }

        // Equipamentos
        $responseEquipment = $this->rentalController->setEquipmentRental($request, true);
        if (isset($responseEquipment->error)) {
            return response()->json(['success' => false, 'message' => $responseEquipment->error]);
        }
        $arrEquipment = $responseEquipment->arrEquipment;

        // Pagamento
        $arrPayment = array();
        if ($haveCharged) {
                $responsePayment = $this->rentalController->setPaymentRental($request, $responseEquipment->grossValue, true);
            if (isset($responsePayment->error)) {
                return response()->json(['success' => false, 'message' => $responsePayment->error]);
            }

            $arrPayment = $responsePayment->arrPayment;
        }

        // Resíduo
        $arrResidue = $this->rentalController->setResidueRental($request, true);
        if (isset($arrResidue['error'])) {
            return response()->json(['success' => false, 'message' => $arrResidue['error']]);
        }

        // Orçamento
        $arrBudget = array(
            'code'                          => $this->budget->getNextCode($company_id), // get last code
            'company_id'                    => $company_id,
            'type_rental'                   => $haveCharged,
            'client_id'                     => $clientId,
            'address_zipcode'               => $zipcode,
            'address_name'                  => $address,
            'address_number'                => $number,
            'address_complement'            => $complement,
            'address_reference'             => $reference,
            'address_neigh'                 => $neigh,
            'address_city'                  => $city,
            'address_state'                 => $state,
            'address_lat'                   => $lat,
            'address_lng'                   => $lng,
            'expected_delivery_date'        => $dateDelivery->format(DATETIME_INTERNATIONAL),
            'expected_withdrawal_date'      => $dateWithdrawal ? $dateWithdrawal->format(DATETIME_INTERNATIONAL) : null,
            'not_use_date_withdrawal'       => $notUseDateWithdrawal,
            'gross_value'                   => $haveCharged ? $responseEquipment->grossValue : null,
            'extra_value'                   => $haveCharged ? $responsePayment->extraValue : null,
            'discount_value'                => $haveCharged ? $responsePayment->discountValue : null,
            'net_value'                     => $haveCharged ? $responsePayment->netValue : null,
            'calculate_net_amount_automatic'=> (bool)$request->input('calculate_net_amount_automatic'),
            'use_parceled'                  => (bool)$request->input('is_parceled'),
            'automatic_parcel_distribution' => (bool)$request->input('automatic_parcel_distribution'),
            'observation'                   => strip_tags($request->input('observation'), $this->allowableTags),
            'user_insert'                   => $request->user()->id
        );

        DB::beginTransaction();// Iniciando transação manual para evitar atualizações não desejáveis.

        $this->rentalController->updateLatLngAddressSelected($request);

        $insertBudget   = $this->budget->insert($arrBudget);

        $arrEquipment   = $this->rentalController->addRentalIdArray($arrEquipment, $insertBudget->id, true);
        $arrResidue     = $this->rentalController->addRentalIdArray($arrResidue, $insertBudget->id, true);
        $arrPayment     = $this->rentalController->addRentalIdArray($arrPayment, $insertBudget->id, true);

        $this->budget_equipment->inserts($arrEquipment);
        $this->budget_residue->inserts($arrResidue);
        if (count($arrPayment)) {
            $this->budget_payment->inserts($arrPayment);
        }

        if ($insertBudget) {
            DB::commit();
            return response()->json(['success' => true, 'urlPrint' => route('print.budget', ['budget' => $insertBudget->id]), 'code' => $insertBudget->code]);
        }

        DB::rollBack();

        return response()->json(['success' => false, 'message' => 'Não foi possível gravar o orçamento, recarregue a página e tente novamente.']);
    }

    public function delete(BudgetDeletePost $request): JsonResponse
    {
        $company_id = $request->user()->company_id;
        $budget_id  = $request->input('budget_id');

        if (!$this->budget->getBudget($budget_id, $company_id)) {
            return response()->json(['success' => false, 'message' => 'Não foi possível localizar o orçamento!']);
        }

        DB::beginTransaction();// Iniciando transação manual para evitar atualizações não desejáveis.

        $delPayment     = $this->budget_payment->remove($budget_id, $company_id);
        $delResidue     = $this->budget_residue->remove($budget_id, $company_id);
        $delEquipment   = $this->budget_equipment->remove($budget_id, $company_id);
        $delBudget      = $this->budget->remove($budget_id, $company_id);

        if ($delEquipment && $delBudget) {
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Orçamento excluído com sucesso!']);
        }

        DB::rollBack();
        return response()->json(['success' => false, 'message' => 'Não foi possível excluir o orçamento!']);
    }

    public function confirm(Request $request): JsonResponse
    {
        $company_id = $request->user()->company_id;
        $budget_id  = $request->input('budget_id');

        if (!$this->budget->getBudget($budget_id, $company_id)) {
            return response()->json(['success' => false, 'message' => 'Não foi possível localizar o orçamento!']);
        }

        try {
            DB::beginTransaction();// Iniciando transação manual para evitar atualizações não desejáveis.

            $create_rental  = $this->rental->insert($this->formatConfirmBudget($this->budget->getBudget($budget_id, $company_id)->toArray()));

            foreach ($this->budget_payment->getPayments($company_id, $budget_id) as $payment) {
                $this->rental_payment->insert($this->formatConfirmBudget($payment->toArray(), $create_rental->id));
            }
            foreach ($this->budget_residue->getResidues($company_id, $budget_id) as $residue) {
                $this->rental_residue->insert($this->formatConfirmBudget($residue->toArray(), $create_rental->id));
            }
            foreach ($this->budget_equipment->getEquipments($company_id, $budget_id) as $equipment) {
                $this->rental_equipment->insert($this->formatConfirmBudget($equipment->toArray(), $create_rental->id));
            }

            $this->budget_payment->remove($budget_id, $company_id);
            $this->budget_residue->remove($budget_id, $company_id);
            $this->budget_equipment->remove($budget_id, $company_id);
            $this->budget->remove($budget_id, $company_id);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Orçamento aprovado com sucesso!', 'rental_id' => $create_rental->id]);
        } catch (Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Não foi possível aprovado o orçamento!' . $e->getMessage()]);
        }
    }

    private function formatConfirmBudget(array $budget, int $rental_id = null): array
    {
        unset($budget['id']);

        if (array_key_exists('budget_id', $budget)) {
            unset($budget['budget_id']);
        }

        if (!empty($rental_id)) {
            $budget['rental_id'] = $rental_id;
        } else {
            $budget['code'] = $this->rental->getNextCode($budget['company_id']);
        }

        return $budget;
    }
}
