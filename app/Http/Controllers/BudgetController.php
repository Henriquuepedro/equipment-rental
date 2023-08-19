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
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Throwable;

class BudgetController extends Controller
{
    private Budget $budget;
    private RentalController $rentalController;
    private BudgetEquipment $budget_equipment;
    private BudgetResidue $budget_residue;
    private BudgetPayment $budget_payment;
    private Rental $rental;
    private RentalEquipment $rental_equipment;
    private RentalResidue $rental_residue;
    private RentalPayment $rental_payment;

    public function __construct()
    {
        $this->rentalController = new RentalController();
        $this->budget = new Budget();
        $this->budget_equipment = new BudgetEquipment();
        $this->budget_residue = new BudgetResidue();
        $this->budget_payment = new BudgetPayment();
        $this->rental = new Rental();
        $this->rental_equipment = new RentalEquipment();
        $this->rental_residue = new RentalResidue();
        $this->rental_payment = new RentalPayment();
    }

    public function index(): Factory|View|RedirectResponse|Application
    {
        if (!hasPermission('BudgetView')) {
            return redirect()->route('dashboard')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        return view('budget.index');
    }

    public function fetchBudgets(Request $request): JsonResponse
    {
        $result     = array();
        $draw       = $request->input('draw');
        $company_id = $request->user()->company_id;

        try {
            $filters        = array();
            $filter_default = array();
            $fields_order   = array(
                'budgets.id',
                'budgets.code',
                [
                    'clients.name',
                    'budgets.address_name',
                    'budgets.address_name',
                    'budgets.address_number',
                    'budgets.address_zipcode',
                    'budgets.address_neigh',
                    'budgets.address_city',
                    'budgets.address_state'
                ],
                'budgets.created_at',
                ''
            );

            $filter_default[]['where']['budgets.company_id'] = $company_id;

            $query = array();
            $query['select'] = [
                'budgets.id',
                'budgets.code',
                'clients.name as client_name',
                'budgets.address_name',
                'budgets.address_number',
                'budgets.address_zipcode',
                'budgets.address_complement',
                'budgets.address_neigh',
                'budgets.address_city',
                'budgets.address_state',
                'budgets.created_at'
            ];
            $query['from'] = 'budgets';
            $query['join'][] = ['clients','clients.id','=','budgets.client_id'];

            $data = fetchDataTable(
                $query,
                array('budgets.code', 'asc'),
                null,
                ['BudgetView'],
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

        $permissionUpdate = hasPermission('BudgetUpdatePost');
        $permissionDelete = hasPermission('BudgetDeletePost');

        foreach ($data['data'] as $value) {
            $buttons = "<button class='dropdown-item btnApproveBudget' budget-id='$value->id'><i class='fas fa-check'></i> Aprovar Orçamento</button>";
            $buttons .= $permissionUpdate ? "<a href='".route('budget.update', ['id' => $value->id])."' class='dropdown-item'><i class='fas fa-edit'></i> Alterar Orçamento</a>" : '';
            $buttons .= $permissionDelete ? "<button class='dropdown-item btnRemoveBudget' budget-id='$value->id'><i class='fas fa-trash'></i> Excluir Orçamento</button>" : '';
            $buttons .= "<a href='".route('print.budget', ['budget' => $value->id])."' target='_blank' class='dropdown-item'><i class='fas fa-print'></i> Imprimir Orçamento</a>";

            $buttons = dropdownButtonsDataList($buttons, $value->id);

            $result[] = array(
                formatCodeRental($value->code),
                "<div class='d-flex flex-wrap'><span class='font-weight-bold w-100'>$value->client_name</span><span class='mt-1 w-100'>$value->address_name, $value->address_number - $value->address_zipcode - $value->address_neigh - $value->address_city/$value->address_state</span></div>",
                date('d/m/Y H:i', strtotime($value->created_at)),
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

    public function create(): Factory|View|RedirectResponse|Application
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

        try {
            $data_validation = $this->rentalController->makeValidationRental($request, false, true);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }

        // Orçamento.
        $arrBudget      = $data_validation['rental'];
        $arrEquipment   = $data_validation['arrEquipment'];
        $arrResidue     = $data_validation['arrResidue'];
        $arrPayment     = $data_validation['arrPayment'];

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

            $data_rental = $this->formatConfirmBudget($this->budget->getBudget($budget_id, $company_id)->toArray());
            $create_rental  = $this->rental->insert($data_rental);

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
            return response()->json(['success' => true, 'message' => "Orçamento aprovado com sucesso!<br> Foi gerado o código <b>$data_rental[code]</b> para a locação.", 'rental_id' => $create_rental->id]);
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

    public function edit(int $id): Factory|View|RedirectResponse|Application
    {
        if (!hasPermission('BudgetUpdatePost')) {
            return redirect()->route('budget.index')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        $company_id     = Auth::user()->__get('company_id');
        $budget         = true;
        $rental         = $this->budget->getBudget($id, $company_id);
        $rental_residue = $this->budget_residue->getResidues($company_id, $id);

        return view('rental.update', compact('budget', 'rental', 'rental_residue'));
    }

    public function update(int $id, BudgetCreatePost $request): JsonResponse
    {
        if (!hasPermission('BudgetUpdatePost')) {
            return response()->json(['success' => false, 'message' => "Você não tem permissão para atualizar orçamentos."]);
        }

        $company_id = $request->user()->company_id;
        // Define os dados para ser usado na Trait.
        $this->rentalController->setDataRental($this->budget->getBudget($id, $company_id));
        $this->rentalController->setDataRentalEquipment($this->budget_equipment->getEquipments($company_id, $id));
        $this->rentalController->setDataRentalPayment($this->budget_payment->getPayments($company_id, $id));

        if (!$this->rentalController->getDataRental()) {
            return response()->json(['success' => false, 'message' => "Orçamento não encontrado."]);
        }

        DB::beginTransaction();// Iniciando transação manual para evitar updates não desejáveis.

        try {
            // Faz as validações iniciais padrões para poder seguir com a atualização.
            $data_validation = $this->rentalController->makeValidationRental($request, $id, true);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }

        // Orçamento.
        $arrBudget      = $data_validation['rental'];
        $arrEquipment   = $data_validation['arrEquipment'];
        $arrResidue     = $data_validation['arrResidue'];
        $arrPayment     = $data_validation['arrPayment'];

        // remove o campo 'code' da atualização.
        unset($arrBudget['code']);

        $updateBudget = $this->budget->updateByBudgetAndCompany($id, $company_id, $arrBudget);

        // Remove os equipamentos e cria novamente.
        $this->budget_equipment->remove($id, $company_id);
        $this->budget_equipment->inserts($arrEquipment);

        // Remove os pagamento e cria novamente.
        $this->budget_payment->remove($id, $company_id);
        $this->budget_payment->inserts($arrPayment);

        // Remove os resíduos para serem criados novamente.
        $this->budget_residue->remove($id, $company_id);
        $this->budget_residue->inserts($arrResidue);

        if ($updateBudget) {
            DB::commit();
            return response()->json(['success' => true, 'urlPrint' => route('print.budget', ['budget' => $this->rentalController->getDataRental()->id]), 'code' => $this->rentalController->getDataRental()->code]);
        }

        DB::rollBack();

        return response()->json(['success' => false, 'message' => 'Não foi possível gravar o orçamento, recarregue a página e tente novamente.']);
    }
}
