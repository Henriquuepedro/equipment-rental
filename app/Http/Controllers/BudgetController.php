<?php

namespace App\Http\Controllers;

use App\Http\Requests\BudgetCreatePost;
use App\Models\Budget;
use App\Models\BudgetEquipment;
use App\Models\BudgetResidue;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class BudgetController extends Controller
{
    private $budget;
    private $rentalController;
    private $client;
    private $budget_equipment;
    private $budget_residue;

    public function __construct(Budget $budget, RentalController $rentalController, Client $client, BudgetEquipment $budget_equipment, BudgetResidue $budget_residue)
    {
        $this->budget = $budget;
        $this->rentalController = $rentalController;
        $this->client = $client;
        $this->budget_equipment = $budget_equipment;
        $this->budget_residue = $budget_residue;
    }

    public function index()
    {
        if (!$this->hasPermission('BudgetView')) {
            return redirect()->route('dashboard')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        return view('budget.index');
    }

    public function fetchBudgets(Request $request): JsonResponse
    {
        if (!$this->hasPermission('BudgetView'))
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

            $fieldsOrder = array('budgets.code','clients.name','budgets.address_name','budgets.created_at', '');
            $fieldOrder =  $fieldsOrder[$request->order[0]['column']];
            if ($fieldOrder != "") {
                $orderBy['field'] = $fieldOrder;
                $orderBy['order'] = $direction;
            }
        }

        $data = $this->budget->getBudgets($company_id, $ini, $length, $searchUser, $orderBy);

        // get string query
        // DB::getQueryLog();

        $permissionUpdate = $this->hasPermission('BudgetUpdatePost');
        $permissionDelete = $this->hasPermission('BudgetDeletePost');

        foreach ($data as $key => $value) {
            $buttons = $permissionDelete ? "<button class='btn btn-danger btnRemoveBudget btn-sm btn-rounded btn-action ml-md-1' data-toggle='tooltip' title='Excluir' budget-id='{$value['id']}'><i class='fas fa-times'></i></button>" : '';
            $buttons .= "<a href='".route('print.budget', ['budget' => $value['id']])."' target='_blank' class='btn btn-primary btn-sm btn-rounded btn-action ml-md-1' data-toggle='tooltip' title='Imprimir'><i class='fas fa-print'></i></a>";

            $result[$key] = array(
                str_pad($value['code'], 5, 0, STR_PAD_LEFT),
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
        if (!$this->hasPermission('BudgetCreatePost')) {
            return redirect()->route('budget.index')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }
        $budget = true;

        return view('rental.create', compact('budget'));
    }


    public function insert(BudgetCreatePost $request)
    {
        if (!$this->hasPermission('BudgetCreatePost'))
            return response()->json(['success' => false, 'message' => "Você não tem permissão para criar orçamentos."]);

        $company_id = $request->user()->company_id;

        $clientId   = (int)$request->client;
        $zipcode    = $request->cep ? filter_var(preg_replace('/[^0-9]/', '', $request->cep), FILTER_SANITIZE_NUMBER_INT) : null;
        $address    = $request->address ? filter_var($request->address, FILTER_SANITIZE_STRING) : null;
        $number     = $request->number ? filter_var($request->number, FILTER_SANITIZE_STRING) : null;
        $complement = $request->complement ? filter_var($request->complement, FILTER_SANITIZE_STRING) : null;
        $reference  = $request->reference ? filter_var($request->reference, FILTER_SANITIZE_STRING) : null;
        $neigh      = $request->neigh ? filter_var($request->neigh, FILTER_SANITIZE_STRING) : null;
        $city       = $request->city ? filter_var($request->city, FILTER_SANITIZE_STRING) : null;
        $state      = $request->state ? filter_var($request->state, FILTER_SANITIZE_STRING) : null;
        $lat        = $request->lat ? filter_var($request->lat, FILTER_SANITIZE_STRING) : null;
        $lng        = $request->lng ? filter_var($request->lng, FILTER_SANITIZE_STRING) : null;

        if (empty($clientId) || !$this->client->getClient($clientId, $company_id))
            return response()->json(['success' => false, 'message' => "Cliente não foi encontrado. Revise a aba de Cliente e Endereço."]);

        if ($address == '') return response()->json(['success' => false, 'message' => 'Informe um endereço. Revise a aba de Cliente e Endereço.']);
        if ($number == '') return response()->json(['success' => false, 'message' => 'Informe um número para o endereço. Revise a aba de Cliente e Endereço.']);
        if ($neigh == '') return response()->json(['success' => false, 'message' => 'Informe um bairro. Revise a aba de Cliente e Endereço.']);
        if ($city == '') return response()->json(['success' => false, 'message' => 'Informe uma cidade. Revise a aba de Cliente e Endereço.']);
        if ($state == '') return response()->json(['success' => false, 'message' => 'Informe um estado. Revise a aba de Cliente e Endereço.']);
        if ($lat == '' || $lng == '') return response()->json(['success' => false, 'message' => 'Confirme o endereço no mapa. Revise a aba de Cliente e Endereço.']);

        // datas do orçamento
        $dateDelivery = $request->date_delivery ? \DateTime::createFromFormat('d/m/Y H:i', $request->date_delivery) : null;
        $dateWithdrawal = $request->date_withdrawal ? \DateTime::createFromFormat('d/m/Y H:i', $request->date_withdrawal) : null;
        $notUseDateWithdrawal = $request->not_use_date_withdrawal ? true : false;

        if (!$dateDelivery) // não reconheceu a data de entrega
            return response()->json(['success' => false, 'message' => "Data prevista de entrega precisa ser informada corretamente dd/mm/yyyy hh:mm."]);

        if (!$notUseDateWithdrawal) { // usará data de retirada

            if (!$dateWithdrawal) // não reconheceu a data de retirada
                return response()->json(['success' => false, 'message' => "Data prevista de retirada precisa ser informada corretamente dd/mm/yyyy hh:mm."]);

            if ($dateDelivery->getTimestamp() >= $dateWithdrawal->getTimestamp()) // data de entrega é maior ou igual a data de retirada
                return response()->json(['success' => false, 'message' => "Data prevista de entrega não pode ser maior ou igual que a data prevista de retirada."]);
        }

        // Equipamentos
        $responseEquipment = $this->rentalController->setEquipmentRental($request, true);
        if (isset($responseEquipment->error))
            return response()->json(['success' => false, 'message' => $responseEquipment->error]);
        $arrEquipment = $responseEquipment->arrEquipment;

        $responsePayment = $this->rentalController->setPaymentRental($request, $responseEquipment->grossValue, true);
        if (isset($responsePayment->error))
            return response()->json(['success' => false, 'message' => $responsePayment->error]);

        // Resíduo
        $arrResidue = $this->rentalController->setResidueRental($request, true);
        if (isset($arrResidue['error']))
            return response()->json(['success' => false, 'message' => $arrResidue['error']]);

        // Orçamento
        $arrBudget = array(
            'code' => $this->budget->getNextCode($company_id), // get last code
            'company_id' => $company_id,
            'client_id' => $clientId,
            'address_zipcode' => $zipcode,
            'address_name' => $address,
            'address_number' => $number,
            'address_complement' => $complement,
            'address_reference' => $reference,
            'address_neigh' => $neigh,
            'address_city' => $city,
            'address_state' => $state,
            'address_lat' => $lat,
            'address_lng' => $lng,
            'expected_delivery_date' => $dateDelivery->format('Y-m-d H:i:s'),
            'expected_withdrawal_date' => $dateWithdrawal ? $dateWithdrawal->format('Y-m-d H:i:s') : null,
            'not_use_date_withdrawal' => $notUseDateWithdrawal,
            'gross_value' => $responseEquipment->grossValue,
            'extra_value'   => $responsePayment->extraValue,
            'discount_value' => $responsePayment->discountValue,
            'net_value' => $responsePayment->netValue,
            'calculate_net_amount_automatic' => $request->calculate_net_amount_automatic ? true : false,
            'observation' => strip_tags($request->observation, $this->allowableTags),
            'user_insert' => $request->user()->id
        );

        DB::beginTransaction();// Iniciando transação manual para evitar updates não desejáveis

        $insertBudget = $this->budget->insert($arrBudget);

        $arrEquipment = $this->rentalController->addRentalIdArray($arrEquipment, $insertBudget->id, true);
        $arrResidue = $this->rentalController->addRentalIdArray($arrResidue, $insertBudget->id, true);

        $this->budget_equipment->inserts($arrEquipment);
        $this->budget_residue->inserts($arrResidue);

        if ($insertBudget) {
            DB::commit();
            return response()->json(['success' => true, 'urlPrint' => route('print.budget', ['budget' => $insertBudget->id]), 'code' => $insertBudget->code]);
        }

        DB::rollBack();

        return response()->json(['success' => false, 'message' => 'Não foi possível gravar o orçamento, recarregue a página e tente novamente.']);

    }
}
