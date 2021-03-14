<?php

namespace App\Http\Controllers;

use App\Http\Requests\EquipmentCreatePost;
use App\Http\Requests\EquipmentDeletePost;
use App\Http\Requests\EquipmentUpdatePost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Equipment;
use App\Models\EquipmentWallet;

class EquipmentController extends Controller
{
    public $equipment;
    public $equipment_wallet;

    public function __construct(Equipment $equipment, EquipmentWallet $equipment_wallet)
    {
        $this->equipment = $equipment;
        $this->equipment_wallet = $equipment_wallet;
    }

    public function index()
    {
        if (!$this->hasPermission('EquipmentView')) {
            return redirect()->route('dashboard')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        return view('equipment.index');
    }

    public function create()
    {
        if (!$this->hasPermission('EquipmentCreatePost')) {
            return redirect()->route('equipment.index')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        return view('equipment.create');
    }

    public function insert(EquipmentCreatePost $request)
    {
        // data equipment
        $dataEquipment = $this->formatDataEquipment($request);

        DB::beginTransaction();// Iniciando transação manual para evitar updates não desejáveis

        $createEquipment = $this->equipment->insert(array(
            'company_id'    => $dataEquipment->company_id,
            'name'          => $dataEquipment->name,
            'reference'     => $dataEquipment->reference,
            'stock'         => $dataEquipment->stock,
            'value'         => $dataEquipment->value,
            'manufacturer'  => $dataEquipment->manufacturer,
            'volume'        => $dataEquipment->volume,
            'user_insert'   => $dataEquipment->user_id
        ));
        $isAjax = $this->isAjax();

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

                if ($isAjax)
                    return response()->json(['success' => false, 'message' => "Existem erros no período. O dia final do {$periodUser}º período não pode ser menor que o inicial, deve ser informado em ordem crescente."]);

                return redirect()->back()
                    ->withErrors(["Existem erros no período. O dia final do {$periodUser}º período não pode ser menor que o inicial, deve ser informado em ordem crescente."])
                    ->withInput();
            }

            // adiciona valor em array para validação
            for ($countPer = $dataPeriod->day_start; $countPer <= $dataPeriod->day_end; $countPer++) {
                // dia informado já está dentro de um prazo
                if (in_array($countPer, $arrDaysVerify)) {
                    if ($isAjax)
                        return response()->json(['success' => false, 'message' => "Existem erros no período. O {$periodUser}º período está inválido, já existe algum dia em outros perído."]);

                    return redirect()->back()
                        ->withErrors(["Existem erros no período. O {$periodUser}º período está inválido, já existe algum dia em outros perído."])
                        ->withInput();
                }

                array_push($arrDaysVerify, $countPer);
            }

            if ($dataPeriod->day_start < 0 || $dataPeriod->day_end <= 0 || $dataPeriod->value_period <= 0) {
                if ($isAjax)
                    return response()->json(['success' => false, 'message' => 'Existem erros no período. Dia inicial não pode ser negativo. Dia final deve ser maior que zero e valor deve ser maior que zero']);

                return redirect()->back()
                    ->withErrors(['Existem erros no período. Dia inicial não pode ser negativo. Dia final deve ser maior que zero e valor deve ser maior que zero'])
                    ->withInput();
            }

            $queryPeriods = $this->equipment_wallet->insert(array(
                'company_id'    => $dataEquipment->company_id,
                'equipment_id' => $equipmentId,
                'day_start'     => $dataPeriod->day_start,
                'day_end'       => $dataPeriod->day_end,
                'value'         => $dataPeriod->value_period,
                'user_insert'   => $dataEquipment->user_id
            ));
            if (!$queryPeriods) $createPeriods = false;
        }

        if($createEquipment && $createPeriods) {
            DB::commit();
            if ($isAjax)
                return response()->json(['success' => true, 'message' => 'Equipmento cadastrado com sucesso!']);

            return redirect()->route('equipment.index')
                ->with('success', "Equipamento com o código {$equipmentId}, cadastrado com sucesso!");
        }

        DB::rollBack();
        if ($isAjax)
            return response()->json(['success' => true, 'message' => 'Não foi possível cadastrar o equipamento, tente novamente!']);

        return redirect()->back()
            ->withErrors(['Não foi possível cadastrar o equipamento, tente novamente!'])
            ->withInput();
    }

    public function edit($id)
    {
        $company_id = Auth::user()->company_id;

        $equipment = $this->equipment->getEquipment($id, $company_id);
        if (!$equipment)
            return redirect()->route('equipment.index');

        $equipment->value = number_format($equipment->value, 2, ',', '.');

        $equipment_wallet = $this->equipment_wallet->getWalletsEquipment($company_id, $id);
        $dataEquipmentWallet = [];
        foreach ($equipment_wallet as $wallet) {
            array_push($dataEquipmentWallet, [
                'day_start' => $wallet->day_start,
                'day_end'   => $wallet->day_end,
                'value'     => number_format($wallet->value, 2, ',', '.')
            ]);
        }

        return view('equipment.update', compact('equipment', 'dataEquipmentWallet'));
    }

    public function update(EquipmentUpdatePost $request)
    {
        // data equipment
        $dataEquipment = $this->formatDataEquipment($request);

        if (!$this->equipment->getEquipment($dataEquipment->equipment_id, $dataEquipment->company_id))
            return redirect()->back()
                ->withErrors(['Não foi possível localizar o equipamento para atualizar!'])
                ->withInput();

        DB::beginTransaction();// Iniciando transação manual para evitar updates não desejáveis

        $updateEquipment = $this->equipment->edit(
            array(
                'name'          => $dataEquipment->name,
                'reference'     => $dataEquipment->reference,
                'stock'         => $dataEquipment->stock,
                'value'         => $dataEquipment->value,
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
            if ($dataPeriod->day_start > $dataPeriod->day_end)
                return redirect()->back()
                    ->withErrors(["Existem erros no período. O dia final do {$periodUser}º período não pode ser menor que o inicial, deve ser informado em ordem crescente."])
                    ->withInput();

            // adiciona valor em array para validação
            for ($countPer = $dataPeriod->day_start; $countPer <= $dataPeriod->day_end; $countPer++) {
                // dia informado já está dentro de um prazo
                if (in_array($countPer, $arrDaysVerify))
                    return redirect()->back()
                        ->withErrors(["Existem erros no período. O {$periodUser}º período está inválido, já existe algum dia em outros perído."])
                        ->withInput();

                array_push($arrDaysVerify, $countPer);
            }

            // valor zerados ou negativo
            if ($dataPeriod->day_start < 0 || $dataPeriod->day_end <= 0 || $dataPeriod->value_period <= 0)
                return redirect()->back()
                    ->withErrors(['Existem erros no período. Dia inicial não pode ser negativo. Dia final e valor deve ser maior que zero.'])
                    ->withInput();

            $queryPeriods = $this->equipment_wallet->insert(array(
                'company_id'    => $dataEquipment->company_id,
                'equipment_id' => $dataEquipment->equipment_id,
                'day_start'     => $dataPeriod->day_start,
                'day_end'       => $dataPeriod->day_end,
                'value'         => $dataPeriod->value_period,
                'user_insert'   => $dataEquipment->user_id
            ));

            if (!$queryPeriods) $updatePeriods = false;
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

    public function delete(EquipmentDeletePost $request)
    {
        $company_id = $request->user()->company_id;
        $equipment_id = $request->equipment_id;

        if (!$this->equipment->getEquipment($equipment_id, $company_id))
            return response()->json(['success' => false, 'message' => 'Não foi possível localizar o equipamento!']);

        if (!$this->equipment->remove($equipment_id, $company_id))
            return response()->json(['success' => false, 'message' => 'Não foi possível excluir o equipamento!']);

        return response()->json(['success' => true, 'message' => 'Equipamento excluído com sucesso!']);
    }

    public function fetchEquipments(Request $request)
    {
//        DB::enableQueryLog();
        if (!$this->hasPermission('EquipmentView'))
            return response()->json([]);

        $orderBy    = array();
        $result     = array();
        $searchUser = null;
        $getCacamba = false;

        $ini        = $request->start;
        $draw       = $request->draw;
        $length     = $request->length;
        $company_id = $request->user()->company_id;

        $search = $request->search;
        $search['value'] = str_replace('*','', filter_var($search['value'], FILTER_SANITIZE_STRING));

        if ($this->likeText('%'.strtolower(str_replace(['ç', 'Ç'],'c',$search['value'])).'%', 'cacamba'))
            $getCacamba = true;

        if ($search['value']) $searchUser = $search['value'];

        if (isset($request->order)) {
            if ($request->order[0]['dir'] == "asc") $direction = "asc";
            else $direction = "desc";

            $fieldsOrder = array('id','name','reference','stock', '');
            $fieldOrder =  $fieldsOrder[$request->order[0]['column']];
            if ($fieldOrder != "") {
                $orderBy['field'] = $fieldOrder;
                $orderBy['order'] = $direction;
            }
        }

        $data = $this->equipment->getEquipments($company_id, $ini, $length, $searchUser, $orderBy, $getCacamba);

        // get string query
//         DB::getQueryLog();

        $permissionUpdate = $this->hasPermission('EquipmentUpdatePost');
        $permissionDelete = $this->hasPermission('EquipmentDeletePost');

        foreach ($data as $key => $value) {
            $buttons = "<a href='".route('equipment.edit', ['id' => $value['id']])."' class='btn btn-primary btn-sm btn-rounded btn-action' data-toggle='tooltip'";
            $buttons .= $permissionUpdate ? "title='Editar' ><i class='fas fa-edit'></i></a>" : "title='Visualizar' ><i class='fas fa-eye'></i></a>";
            $buttons .= $permissionDelete ? "<button class='btn btn-danger btnRemoveEquipment btn-sm btn-rounded btn-action ml-md-1' data-toggle='tooltip' title='Excluir' equipment-id='{$value['id']}'><i class='fas fa-times'></i></button>" : '';

            $result[$key] = array(
                $value['id'],
                $value['name'] ?? "Caçamba {$value['volume']}m³",
                $value['reference'],
                $value['stock'],
                $buttons
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

    private function formatDataEquipment($request)
    {
        $obj = new \stdClass;

        $obj->company_id    = $request->user()->company_id;
        $obj->user_id       = $request->user()->id;
        $obj->name          = $request->type_equipment === "cacamba" ? null : filter_var($request->name, FILTER_SANITIZE_STRING);
        $obj->volume        = $request->type_equipment === "others" ? null : filter_var($request->volume, FILTER_VALIDATE_INT);
        $obj->reference     = filter_var($request->reference, FILTER_SANITIZE_STRING);
        $obj->manufacturer  = $request->manufacturer ? filter_var($request->manufacturer, FILTER_SANITIZE_STRING) : null;
        $obj->value         = $request->value ? $this->transformMoneyBr_En($request->value) : 0.00;
        $obj->stock         = $request->stock ? filter_var($request->stock, FILTER_VALIDATE_INT) : 0;
        $obj->equipment_id = isset($request->equipment_id) ? (int)$request->equipment_id : null;

        return $obj;
    }

    private function formatDataPeriod($request, $per)
    {
        $obj = new \stdClass;

        $obj->day_start      = filter_var((int)$request->day_start[$per], FILTER_VALIDATE_INT);
        $obj->day_end        = filter_var((int)$request->day_end[$per], FILTER_VALIDATE_INT);
        $obj->value_period   = $this->transformMoneyBr_En($request->value_period[$per]);

        return $obj;
    }

    public function getEquipments(Request $request)
    {
        //DB::enableQueryLog();
        $company_id         = $request->user()->company_id;
        $searchEquipment   = str_replace('*','', filter_var($request->searchEquipment, FILTER_SANITIZE_STRING));
        $equipmentData     = [];
        $getCacamba         = false;
        $equipmentInUse    = $request->equipmentInUse;

        if ($this->likeText('%'.strtolower(str_replace(['ç', 'Ç'],'c',$searchEquipment)).'%', 'cacamba'))
            $getCacamba = true;

        $equipments = $this->equipment->getEquipmentRental($company_id, $searchEquipment, $getCacamba, $equipmentInUse);

        foreach ($equipments as $equipment)
            array_push($equipmentData, [
                'id'        => $equipment->id,
                'name'      => $equipment->name ?? "Caçamba {$equipment->volume}m³",
                'reference' => $equipment->reference,
                'stock'     => $equipment->stock,
                'value'     => number_format($equipment->value, 2, ',', '.')
            ]);

        return response()->json($equipmentData);
    }

    public function getEquipment(Request $request)
    {
        $company_id     = $request->user()->company_id;
        $equipment_id  = $request->idEquipment;

        $validStock = $request->validStock ? true : false;

        $equipment = $this->equipment->getEquipment($equipment_id, $company_id);

        if (!$equipment)
            return response()->json(['success' => false, 'data' => 'Equipamento não encontrado.']);

        if ($validStock && $equipment->stock <= 0)
            return response()->json(['success' => false, 'data' => 'equipmento sem estoque para uso.']);

        $equipmentData = [
            'id'        => $equipment->id,
            'name'      => $equipment->name ?? "Caçamba {$equipment->volume}m³",
            'reference' => $equipment->reference,
            'stock'     => $equipment->stock,
            'cacamba'   => $equipment->volume ? true : false
        ];

        $permissions = [
            'vehicle' => $this->hasPermission('VehicleCreatePost'),
            'driver' => $this->hasPermission('DriverCreatePost')
        ];

        return response()->json(['success' => true, 'data' => $equipmentData, 'permissions' => $permissions]);
    }

    public function getStockEquipment(Request $request)
    {
        $company_id     = $request->user()->company_id;
        $equipment_id  = $request->idEquipment;

        $equipment = $this->equipment->getEquipment($equipment_id, $company_id);

        return response()->json($equipment->stock);
    }

    public function getPriceEquipment(Request $request)
    {
        $company_id     = $request->user()->company_id;
        $equipment_id  = $request->idEquipment;
        $diff_days      = $request->diffDays;

        $equipment = $this->equipment->getEquipment($equipment_id, $company_id);

        // não encontrou o equipamento, retorna zerioo
        if (!$equipment) return 0;

        //recebeu false porque a data de retirada não foi definida
        if ($diff_days == false) $equipmentWallet = false;
        else $equipmentWallet = $this->equipment_wallet->getValueWalletsEquipment($company_id, $equipment_id, $diff_days);

        if (!$equipmentWallet) return response()->json($equipment->value);

        return response()->json($equipmentWallet->value);
    }

    public function getPriceStockEquipment(Request $request)
    {
        $company_id     = $request->user()->company_id;
        $equipment_id  = $request->idEquipment;
        $diff_days      = $request->diffDays;

        $equipment = $this->equipment->getEquipment($equipment_id, $company_id);

        // não encontrou o equipamento, retorna zero para preço e estoque
        if (!$equipment) return response()->json(['price' => 0, 'stock' => 0]);

        //recebeu false porque a data de retirada não foi definida
        if ($diff_days === "false") $equipmentWallet = false;
        else $equipmentWallet = $this->equipment_wallet->getValueWalletsEquipment($company_id, $equipment_id, $diff_days);

        if (!$equipmentWallet) return response()->json(['price' => $equipment->value, 'stock' => $equipment->stock, 'x'=>$diff_days]);

        return response()->json(['price' => $equipmentWallet->value, 'stock' => $equipment->stock, 'x'=>$diff_days]);
    }

    public function getPricePerPeriod(Request $request)
    {
        $company_id     = $request->user()->company_id;
        $equipment_id  = $request->idEquipment;

        $equipmentWallet = $this->equipment_wallet->getWalletsEquipment($company_id, $equipment_id);

        return response()->json($equipmentWallet);
    }

    public function getCheckPriceStockEquipment(Request $request)
    {
        //DB::enableQueryLog();
        $rsEquipment   = [];
        $company_id     = $request->user()->company_id;
        $equipmentsId  = $request->arrEquipments;
        $diffsDays      = $request->arrDiffDays;

        $equipments = $this->equipment->getMultipleEquipments($equipmentsId, $company_id);

        // não encontrou todos od equipamentos, retorna zero para preço e estoque
        if (count($equipments) !== count($equipmentsId)) return response()->json([0 => ['price' => 0, 'stock' => 0]]);

        foreach ($equipments as $equipment) {
            //recebeu false porque a data de retirada não foi definida
            if ($diffsDays[$equipment['id']] === "false") $equipmentWallet = false;
            else $equipmentWallet = $this->equipment_wallet->getValueWalletsEquipment($company_id, $equipment['id'], $diffsDays[$equipment['id']]);

            if (!$equipmentWallet) $price = $equipment->value;
            else $price = $equipmentWallet->value;

            $rsEquipment[$equipment['id']] = [
                'price' => $price,
                'stock' => $equipment->stock
            ];
        }
        //DB::getQueryLog()
        return response()->json($rsEquipment);
    }
}
