<?php

namespace App\Http\Controllers;

use App\Http\Requests\EquipmentCreatePost;
use App\Http\Requests\EquipmentDeletePost;
use App\Http\Requests\EquipmentUpdatePost;
use App\Models\RentalEquipment;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Equipment;
use App\Models\EquipmentWallet;
use stdClass;

class EquipmentController extends Controller
{
    public Equipment $equipment;
    public EquipmentWallet $equipment_wallet;
    public RentalEquipment $rental_equipment;

    public function __construct()
    {
        $this->equipment = new Equipment();
        $this->equipment_wallet = new EquipmentWallet();
        $this->rental_equipment = new RentalEquipment();
    }

    public function index(): Factory|View|RedirectResponse|Application
    {
        if (!hasPermission('EquipmentView')) {
            return redirect()->route('dashboard')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        return view('equipment.index');
    }

    public function create(): Factory|View|RedirectResponse|Application
    {
        if (!hasPermission('EquipmentCreatePost')) {
            return redirect()->route('equipment.index')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        return view('equipment.create');
    }

    public function insert(EquipmentCreatePost $request): JsonResponse|RedirectResponse
    {
        // data equipment
        $dataEquipment = $this->formatDataEquipment($request);

        $isAjax = isAjax();

        // valida se tem estoque disponível na conta.
        $available_stock = $this->equipment->getAllStockEquipment($dataEquipment->company_id);
        if (!is_null($available_stock) && $dataEquipment->stock > $available_stock) {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => "Você tem disponível somente $available_stock unidades para cadastro."]);
            }

            return redirect()->back()
                ->withErrors(["Você tem disponível somente $available_stock unidades para cadastro."])
                ->withInput();
        }

        DB::beginTransaction();// Iniciando transação manual para evitar updates não desejáveis

        $createEquipment = $this->equipment->insert(array(
            'company_id'    => $dataEquipment->company_id,
            'name'          => $dataEquipment->name,
            'reference'     => $dataEquipment->reference,
            'stock'         => $dataEquipment->stock,
            'value'         => roundDecimal($dataEquipment->value),
            'manufacturer'  => $dataEquipment->manufacturer,
            'volume'        => $dataEquipment->volume,
            'user_insert'   => $dataEquipment->user_id
        ));

        $createPeriods = true;
        $equipmentId = $createEquipment->id;

        // data period
        $qtyPeriods = isset($request->day_start) ? count($request->day_start) : 0;
        $arrDaysVerify = array();
        for ($per = 0; $per < $qtyPeriods; $per++) {
            $periodUser = $per+1;
            $dataPeriod = $this->formatDataPeriod($request, $per);

            // dia inicial maior que o final
            if ($dataPeriod->day_start > $dataPeriod->day_end) {

                if ($isAjax) {
                    return response()->json(['success' => false, 'message' => "Existem erros no período. O dia final do {$periodUser}º período não pode ser menor que o inicial, deve ser informado em ordem crescente."]);
                }

                return redirect()->back()
                    ->withErrors(["Existem erros no período. O dia final do {$periodUser}º período não pode ser menor que o inicial, deve ser informado em ordem crescente."])
                    ->withInput();
            }

            // adiciona valor em array para validação
            for ($countPer = $dataPeriod->day_start; $countPer <= $dataPeriod->day_end; $countPer++) {
                // dia informado já está dentro de um prazo
                if (in_array($countPer, $arrDaysVerify)) {
                    if ($isAjax) {
                        return response()->json(['success' => false, 'message' => "Existem erros no período. O {$periodUser}º período está inválido, já existe algum dia em outros período."]);
                    }

                    return redirect()->back()
                        ->withErrors(["Existem erros no período. O {$periodUser}º período está inválido, já existe algum dia em outros período."])
                        ->withInput();
                }

                $arrDaysVerify[] = $countPer;
            }

            if ($dataPeriod->day_start < 0 || $dataPeriod->day_end <= 0 || $dataPeriod->value_period <= 0) {
                if ($isAjax) {
                    return response()->json(['success' => false, 'message' => 'Existem erros no período. Dia inicial não pode ser negativo. Dia final deve ser maior que zero e valor deve ser maior que zero']);
                }

                return redirect()->back()
                    ->withErrors(['Existem erros no período. Dia inicial não pode ser negativo. Dia final deve ser maior que zero e valor deve ser maior que zero'])
                    ->withInput();
            }

            $queryPeriods = $this->equipment_wallet->insert(array(
                'company_id'    => $dataEquipment->company_id,
                'equipment_id'  => $equipmentId,
                'day_start'     => $dataPeriod->day_start,
                'day_end'       => $dataPeriod->day_end,
                'value'         => roundDecimal($dataPeriod->value_period),
                'user_insert'   => $dataEquipment->user_id
            ));
            if (!$queryPeriods) {
                $createPeriods = false;
            }
        }

        if($createEquipment && $createPeriods) {
            DB::commit();
            if ($isAjax) {
                return response()->json(['success' => true, 'message' => 'Equipamento cadastrado com sucesso!']);
            }

            return redirect()->route('equipment.index')
                ->with('success', "Equipamento com o código {$equipmentId}, cadastrado com sucesso!");
        }

        DB::rollBack();
        if ($isAjax) {
            return response()->json(['success' => true, 'message' => 'Não foi possível cadastrar o equipamento, tente novamente!']);
        }

        return redirect()->back()
            ->withErrors(['Não foi possível cadastrar o equipamento, tente novamente!'])
            ->withInput();
    }

    public function edit($id): View|Factory|RedirectResponse|Application
    {
        if (!hasPermission('EquipmentUpdatePost')) {
            return redirect()->route('equipment.index')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        $company_id = Auth::user()->__get('company_id');

        $equipment = $this->equipment->getEquipment($id, $company_id);
        if (!$equipment) {
            return redirect()->route('equipment.index');
        }

        $equipment->value = number_format($equipment->value, 2, ',', '.');

        $equipment_wallet = $this->equipment_wallet->getWalletsEquipment($company_id, $id);
        $dataEquipmentWallet = [];
        foreach ($equipment_wallet as $wallet) {
            $dataEquipmentWallet[] = [
                'day_start' => $wallet->day_start,
                'day_end'   => $wallet->day_end,
                'value'     => roundDecimal($wallet->value)
            ];
        }

        return view('equipment.update', compact('equipment', 'dataEquipmentWallet'));
    }

    public function update(EquipmentUpdatePost $request): RedirectResponse
    {
        // data equipment
        $dataEquipment = $this->formatDataEquipment($request);

        // valida se tem estoque disponível na conta.
        $available_stock = $this->equipment->getAllStockEquipment($dataEquipment->company_id, $dataEquipment->equipment_id);
        if (!is_null($available_stock) && $dataEquipment->stock > $available_stock) {
            return redirect()->back()
                ->withErrors(["Você tem disponível somente $available_stock unidades para cadastro."])
                ->withInput();
        }

        // valida se o estoque será menor que o que está em uso.
        $stock_in_use = $this->rental_equipment->getEquipmentsInUse($dataEquipment->company_id, $dataEquipment->equipment_id);
        if ($dataEquipment->stock < $stock_in_use) {
            return redirect()->back()
                ->withErrors(["Você tem $stock_in_use unidade em uso em locações, após a conclusão da locação, será possível reduzir o estoque."])
                ->withInput();
        }

        if (!$this->equipment->getEquipment($dataEquipment->equipment_id, $dataEquipment->company_id)) {
            return redirect()->back()
                ->withErrors(['Não foi possível localizar o equipamento para atualizar!'])
                ->withInput();
        }

        DB::beginTransaction();// Iniciando transação manual para evitar updates não desejáveis

        $updateEquipment = $this->equipment->edit(
            array(
                'name'          => $dataEquipment->name,
                'reference'     => $dataEquipment->reference,
                'stock'         => $dataEquipment->stock,
                'value'         => roundDecimal($dataEquipment->value),
                'manufacturer'  => $dataEquipment->manufacturer,
                'volume'        => $dataEquipment->volume,
                'user_update'   => $dataEquipment->user_id
            ),
            $dataEquipment->equipment_id
        );

        $updatePeriods = true;

        // data period
        $qtyPeriods = isset($request->day_start) ? count($request->day_start) : 0;
        // remover todos os períodos do equipamento
        $this->equipment_wallet->removeAllEquipment($dataEquipment->equipment_id, $dataEquipment->company_id);
        $arrDaysVerify = array();
        for ($per = 0; $per < $qtyPeriods; $per++) {
            $periodUser = $per+1;
            $dataPeriod = $this->formatDataPeriod($request, $per);

            // dia inicial maior que o final
            if ($dataPeriod->day_start > $dataPeriod->day_end) {
                return redirect()->back()
                    ->withErrors(["Existem erros no período. O dia final do {$periodUser}º período não pode ser menor que o inicial, deve ser informado em ordem crescente."])
                    ->withInput();
            }

            // adiciona valor em array para validação
            for ($countPer = $dataPeriod->day_start; $countPer <= $dataPeriod->day_end; $countPer++) {
                // dia informado já está dentro de um prazo
                if (in_array($countPer, $arrDaysVerify)) {
                    return redirect()->back()
                        ->withErrors(["Existem erros no período. O {$periodUser}º período está inválido, já existe algum dia em outros período."])
                        ->withInput();
                }

                $arrDaysVerify[] = $countPer;
            }

            // valor zerados ou negativo
            if ($dataPeriod->day_start < 0 || $dataPeriod->day_end <= 0 || $dataPeriod->value_period <= 0) {
                return redirect()->back()
                    ->withErrors(['Existem erros no período. Dia inicial não pode ser negativo. Dia final e valor deve ser maior que zero.'])
                    ->withInput();
            }

            $queryPeriods = $this->equipment_wallet->insert(array(
                'company_id'    => $dataEquipment->company_id,
                'equipment_id' => $dataEquipment->equipment_id,
                'day_start'     => $dataPeriod->day_start,
                'day_end'       => $dataPeriod->day_end,
                'value'         => roundDecimal($dataPeriod->value_period),
                'user_insert'   => $dataEquipment->user_id
            ));

            if (!$queryPeriods) {
                $updatePeriods = false;
            }
        }

        if($updateEquipment && $updatePeriods) {
            DB::commit();
            return redirect()->route('equipment.index')
                ->with('success', "Equipamento com o código {$dataEquipment->equipment_id}, alterado com sucesso!");
        }

        DB::rollBack();
        return redirect()->back()
            ->withErrors(['Não foi possível alterar o equipamento, tente novamente!'])
            ->withInput();
    }

    public function delete(EquipmentDeletePost $request): JsonResponse
    {
        $company_id = $request->user()->company_id;
        $equipment_id = $request->input('equipment_id');

        if (!$this->equipment->getEquipment($equipment_id, $company_id)) {
            return response()->json(['success' => false, 'message' => 'Não foi possível localizar o equipamento!']);
        }

        if (!$this->equipment->remove($equipment_id, $company_id)) {
            return response()->json(['success' => false, 'message' => 'Não foi possível excluir o equipamento!']);
        }

        return response()->json(['success' => true, 'message' => 'Equipamento excluído com sucesso!']);
    }

    public function fetchEquipments(Request $request): JsonResponse
    {
        $orderBy    = array();
        $result     = array();
        $searchUser = null;
        $getCacamba = false;

        $ini        = $request->input('start');
        $draw       = $request->input('draw');
        $length     = $request->input('length');
        $company_id = $request->user()->company_id;

        if (!hasPermission('EquipmentView')) {
            return response()->json(array(
                "draw" => $draw,
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => array()
            ));
        }

        $search = $request->input('search');
        $search['value'] = str_replace('*','', filter_var($search['value'], FILTER_DEFAULT));

        if (likeText('%'.strtolower(str_replace(['ç', 'Ç'],'c',$search['value'])).'%', 'cacamba')) {
            $getCacamba = true;
        }

        if ($search && $search['value']) {
            $searchUser = $search['value'];
        }

        if (isset($request->order)) {
            if ($request->order[0]['dir'] == "asc") {
                $direction = "asc";
            } else {
                $direction = "desc";
            }

            $fieldsOrder = array('id','name','reference','stock', '');
            $fieldOrder =  $fieldsOrder[$request->order[0]['column']];
            if ($fieldOrder != "") {
                $orderBy['field'] = $fieldOrder;
                $orderBy['order'] = $direction;
            }
        }

        $data = $this->equipment->getEquipments($company_id, $ini, $length, $searchUser, $orderBy, $getCacamba);

        $permissionUpdate = hasPermission('EquipmentUpdatePost');
        $permissionDelete = hasPermission('EquipmentDeletePost');

        foreach ($data as $key => $value) {
            $buttons = "<a href='".route('equipment.edit', ['id' => $value['id']])."' class='dropdown-item'>";
            $buttons .= $permissionUpdate ? "<i class='fas fa-edit'></i> Atualizar Equipamento</a>" : "<i class='fas fa-eye'></i> Visualizar Equipamento</a>";
            $buttons .= $permissionDelete ? "<button class='dropdown-item btnRemoveEquipment' equipment-id='{$value['id']}'><i class='fas fa-times'></i> Excluir Equipamento</button>" : '';

            $result[$key] = array(
                $value['id'],
                $value['name'] ?? "Caçamba {$value['volume']}m³",
                $value['reference'],
                $value['stock'],
                dropdownButtonsDataList($buttons, $value->id)
            );
        }

        $output = array(
            "draw" => $draw,
            "recordsTotal" => $this->equipment->getCountEquipments($company_id),
            "recordsFiltered" => $this->equipment->getCountEquipments($company_id, $searchUser, $getCacamba),
            "data" => $result
        );

        return response()->json($output);
    }

    private function formatDataEquipment(EquipmentCreatePost|EquipmentUpdatePost $request): stdClass
    {
        $obj = new stdClass;

        $obj->company_id    = $request->user()->company_id;
        $obj->user_id       = $request->user()->id;
        $obj->name          = $request->input('type_equipment') === "cacamba" ? null : filter_var($request->input('name'));
        $obj->volume        = $request->input('type_equipment') === "others" ? null : filter_var($request->input('volume'), FILTER_VALIDATE_INT);
        $obj->reference     = filter_var($request->input('reference'));
        $obj->manufacturer  = $request->input('manufacturer') ? filter_var($request->input('manufacturer')) : null;
        $obj->value         = $request->input('value') ? transformMoneyBr_En($request->input('value')) : 0.00;
        $obj->stock         = $request->input('stock') ? filter_var($request->input('stock'), FILTER_VALIDATE_INT) : 0;
        $obj->equipment_id = $request->input('equipment_id') ? (int)$request->input('equipment_id') : null;

        return $obj;
    }

    private function formatDataPeriod($request, $per): stdClass
    {
        $obj = new stdClass;

        $obj->day_start      = filter_var((int)$request->day_start[$per], FILTER_VALIDATE_INT);
        $obj->day_end        = filter_var((int)$request->day_end[$per], FILTER_VALIDATE_INT);
        $obj->value_period   = transformMoneyBr_En($request->value_period[$per]);

        return $obj;
    }

    public function getEquipments(Request $request): JsonResponse
    {
        if (!hasPermission('EquipmentView')) {
            return response()->json();
        }

        //DB::enableQueryLog();
        $company_id         = $request->user()->company_id;
        $searchEquipment    = str_replace('*','', filter_var($request->input('searchEquipment'), FILTER_DEFAULT));
        $equipmentData      = [];
        $getCacamba         = false;
        $equipmentInUse     = $request->input('equipmentInUse');

        if (likeText('%'.strtolower(str_replace(['ç', 'Ç'],'c',$searchEquipment)).'%', 'cacamba')) {
            $getCacamba = true;
        }

        $equipments = $this->equipment->getEquipmentRental($company_id, $searchEquipment, $getCacamba, $equipmentInUse);

        foreach ($equipments as $equipment) {
            $equipmentData[] = [
                'id'        => $equipment->id,
                'name'      => $equipment->name ?? "Caçamba {$equipment->volume}m³",
                'reference' => $equipment->reference,
                'stock'     => $equipment->stock,
                'value'     => roundDecimal($equipment->value)
            ];
        }

        return response()->json($equipmentData);
    }

    public function getEquipment(int $id, bool $validStock = true): JsonResponse
    {
        if (!hasPermission('EquipmentView')) {
            return response()->json(['success' => false, 'data' => 'Você não tem permissão para acessar essa página!']);
        }

        $company_id = Auth::user()->__get('company_id');

        $equipment = $this->equipment->getEquipment($id, $company_id);

        if (!$equipment) {
            return response()->json(['success' => false, 'data' => 'Equipamento não encontrado.']);
        }

        if ($validStock && $equipment->stock <= 0) {
            return response()->json(['success' => false, 'data' => 'Equipamento sem estoque para uso.']);
        }

        $equipmentData = [
            'id'        => $equipment->id,
            'name'      => $equipment->name ?? "Caçamba {$equipment->volume}m³",
            'reference' => $equipment->reference,
            'stock'     => $equipment->stock,
            'cacamba'   => (bool)$equipment->volume
        ];

        $permissions = [
            'vehicle' => hasPermission('VehicleCreatePost'),
            'driver' => hasPermission('DriverCreatePost')
        ];

        return response()->json(['success' => true, 'data' => $equipmentData, 'permissions' => $permissions]);
    }

    public function getStockEquipment(Request $request): JsonResponse
    {
        if (!hasPermission('EquipmentView')) {
            return response()->json();
        }

        $company_id     = $request->user()->company_id;
        $equipment_id   = $request->input('idEquipment');

        $equipment = $this->equipment->getEquipment($equipment_id, $company_id);

        return response()->json($equipment->stock);
    }

    public function getPriceEquipment(Request $request): JsonResponse|int
    {
        if (!hasPermission('EquipmentView')) {
            return 0;
        }

        $company_id     = $request->user()->company_id;
        $equipment_id   = $request->input('idEquipment');
        $diff_days      = $request->input('diffDays');

        $equipment = $this->equipment->getEquipment($equipment_id, $company_id);

        // não encontrou o equipamento, retorna zero
        if (!$equipment) {
            return 0;
        }

        //recebeu false porque a data de retirada não foi definida
        if (!$diff_days) {
            $equipmentWallet = false;
        } else {
            $equipmentWallet = $this->equipment_wallet->getValueWalletsEquipment($company_id, $equipment_id, $diff_days);
        }

        if (!$equipmentWallet) {
            return response()->json($equipment->value);
        }

        return response()->json($equipmentWallet->value);
    }

    public function getPriceStockEquipment(Request $request): JsonResponse
    {
        if (!hasPermission('EquipmentView')) {
            return response()->json(['price' => 0, 'stock' => 0]);
        }

        $company_id     = $request->user()->company_id;
        $equipment_id   = $request->input('idEquipment');
        $diff_days      = $request->input('diffDays');

        $equipment = $this->equipment->getEquipment($equipment_id, $company_id);

        // não encontrou o equipamento, retorna zero para preço e estoque
        if (!$equipment) {
            return response()->json(['price' => 0, 'stock' => 0]);
        }

        //recebeu false porque a data de retirada não foi definida
        if ($diff_days === "false") {
            $equipmentWallet = false;
        } else {
            $equipmentWallet = $this->equipment_wallet->getValueWalletsEquipment($company_id, $equipment_id, $diff_days);
        }

        if (!$equipmentWallet) {
            return response()->json(['price' => $equipment->value, 'stock' => $equipment->stock, 'x'=>$diff_days]);
        }

        return response()->json(['price' => $equipmentWallet->value, 'stock' => $equipment->stock, 'x'=>$diff_days]);
    }

    public function getPricePerPeriod(Request $request): JsonResponse
    {
        if (!hasPermission('EquipmentView')) {
            return response()->json();
        }

        $company_id     = $request->user()->company_id;
        $equipment_id   = $request->input('idEquipment');

        $equipmentWallet = $this->equipment_wallet->getWalletsEquipment($company_id, $equipment_id);

        return response()->json($equipmentWallet);
    }

    public function getCheckPriceStockEquipment(Request $request): JsonResponse
    {
        if (!hasPermission('EquipmentView')) {
            return response()->json([0 => ['price' => 0, 'stock' => 0]]);
        }

        $rsEquipment   = [];
        $company_id     = $request->user()->company_id;
        $equipmentsId  = $request->input('arrEquipments');
        $diffsDays      = $request->input('arrDiffDays');

        $equipments = $this->equipment->getMultipleEquipments($equipmentsId, $company_id);

        // não encontrou todos od equipamentos, retorna zero para preço e estoque
        if (count($equipments) !== count($equipmentsId)) {
            return response()->json([0 => ['price' => 0, 'stock' => 0]]);
        }

        foreach ($equipments as $equipment) {
            //recebeu false porque a data de retirada não foi definida
            if ($diffsDays[$equipment['id']] === "false") {
                $equipmentWallet = false;
            } else {
                $equipmentWallet = $this->equipment_wallet->getValueWalletsEquipment($company_id, $equipment['id'], $diffsDays[$equipment['id']]);
            }

            if (!$equipmentWallet) {
                $price = $equipment->value;
            } else {
                $price = $equipmentWallet->value;
            }

            $rsEquipment[$equipment['id']] = [
                'price' => $price,
                'stock' => $equipment->stock
            ];
        }
        return response()->json($rsEquipment);
    }

    public function availableStock(int $id = null): JsonResponse|int
    {
        $is_ajax = isAjax();
        $company_id = Auth::user()->__get('company_id');

        $available_stock = $this->equipment->getAllStockEquipment($company_id, $id);

        if ($is_ajax) {
            return response()->json(array('total_equipment' => $available_stock));
        }

        return $available_stock;
    }
}
