<?php

namespace App\Http\Controllers;

use App\Http\Requests\EquipamentCreatePost;
use App\Http\Requests\EquipamentDeletePost;
use App\Http\Requests\EquipamentUpdatePost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Equipament;
use App\Models\EquipamentWallet;

class EquipamentController extends Controller
{
    public $equipament;
    public $equipament_wallet;

    public function __construct(Equipament $equipament, EquipamentWallet $equipament_wallet)
    {
        $this->equipament = $equipament;
        $this->equipament_wallet = $equipament_wallet;
    }

    public function index()
    {
        if (!$this->hasPermission('EquipamentView')) {
            return redirect()->route('dashboard')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        return view('equipament.index');
    }

    public function create()
    {
        if (!$this->hasPermission('EquipamentCreatePost')) {
            return redirect()->route('equipament.index')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        return view('equipament.create');
    }

    public function insert(EquipamentCreatePost $request)
    {
        // data equipament
        $dataEquipament = $this->formatDataEquipament($request);

        DB::beginTransaction();// Iniciando transação manual para evitar updates não desejáveis

        $createEquipament = $this->equipament->insert(array(
            'company_id'    => $dataEquipament->company_id,
            'name'          => $dataEquipament->name,
            'reference'     => $dataEquipament->reference,
            'stock'         => $dataEquipament->stock,
            'value'         => $dataEquipament->value,
            'manufacturer'  => $dataEquipament->manufacturer,
            'volume'        => $dataEquipament->volume,
            'user_insert'   => $dataEquipament->user_id
        ));
        $isAjax = $this->isAjax();

        $createPeriods = true;
        $equipamentId = $createEquipament->id;

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

            $queryPeriods = $this->equipament_wallet->insert(array(
                'company_id'    => $dataEquipament->company_id,
                'equipament_id' => $equipamentId,
                'day_start'     => $dataPeriod->day_start,
                'day_end'       => $dataPeriod->day_end,
                'value'         => $dataPeriod->value_period,
                'user_insert'   => $dataEquipament->user_id
            ));
            if (!$queryPeriods) $createPeriods = false;
        }

        if($createEquipament && $createPeriods) {
            DB::commit();
            if ($isAjax)
                return response()->json(['success' => true, 'message' => 'Equipamento cadastrado com sucesso!']);

            return redirect()->route('equipament.index')
                ->with('success', "Equipamento com o código {$equipamentId}, cadastrado com sucesso!");
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

        $equipament = $this->equipament->getEquipament($id, $company_id);
        if (!$equipament)
            return redirect()->route('equipament.index');

        $equipament_wallet = $this->equipament_wallet->getWalletsEquipament($company_id, $id);

        return view('equipament.update', compact('equipament', 'equipament_wallet'));
    }

    public function update(EquipamentUpdatePost $request)
    {
        // data equipament
        $dataEquipament = $this->formatDataEquipament($request);

        if (!$this->equipament->getEquipament($dataEquipament->equipament_id, $dataEquipament->company_id))
            return redirect()->back()
                ->withErrors(['Não foi possível localizar o equipamento para atualizar!'])
                ->withInput();

        DB::beginTransaction();// Iniciando transação manual para evitar updates não desejáveis

        $updateEquipament = $this->equipament->edit(
            array(
                'name'          => $dataEquipament->name,
                'reference'     => $dataEquipament->reference,
                'stock'         => $dataEquipament->stock,
                'value'         => $dataEquipament->value,
                'manufacturer'  => $dataEquipament->manufacturer,
                'volume'        => $dataEquipament->volume,
                'user_update'   => $dataEquipament->user_id
            ),
            $dataEquipament->equipament_id
        );

        $updatePeriods = true;

        // data period
        $qtyPeriods = isset($request->day_start) ? count($request->day_start) : 0;
        // remover todos os períodos do equipamento
        $this->equipament_wallet->removeAllEquipament($dataEquipament->equipament_id, $dataEquipament->company_id);
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

            $queryPeriods = $this->equipament_wallet->insert(array(
                'company_id'    => $dataEquipament->company_id,
                'equipament_id' => $dataEquipament->equipament_id,
                'day_start'     => $dataPeriod->day_start,
                'day_end'       => $dataPeriod->day_end,
                'value'         => $dataPeriod->value_period,
                'user_insert'   => $dataEquipament->user_id
            ));

            if (!$queryPeriods) $updatePeriods = false;
        }

        if($updateEquipament && $updatePeriods) {
            DB::commit();
            return redirect()->route('equipament.index')
                ->with('success', "Equipamento com o código {$dataEquipament->equipament_id}, alterado com sucesso!");
        }

        DB::rollBack();
        return redirect()->back()
            ->withErrors(['Não foi possível alterar o equipamento, tente novamente!'])
            ->withInput();
    }

    public function delete(EquipamentDeletePost $request)
    {
        $company_id = $request->user()->company_id;
        $equipament_id = $request->equipament_id;

        if (!$this->equipament->getEquipament($equipament_id, $company_id))
            return response()->json(['success' => false, 'message' => 'Não foi possível localizar o equipamento!']);

        if (!$this->equipament->remove($equipament_id, $company_id))
            return response()->json(['success' => false, 'message' => 'Não foi possível excluir o equipamento!']);

        return response()->json(['success' => true, 'message' => 'Equipamento excluído com sucesso!']);
    }

    public function fetchEquipaments(Request $request)
    {
        if (!$this->hasPermission('EquipamentView'))
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

            $fieldsOrder = array('id','name','reference','stock', '');
            $fieldOrder =  $fieldsOrder[$request->order[0]['column']];
            if ($fieldOrder != "") {
                $orderBy['field'] = $fieldOrder;
                $orderBy['order'] = $direction;
            }
        }

        if (!empty($searchUser)) {
            $filtered = $this->equipament->getCountEquipaments($company_id, $searchUser);
        } else {
            $filtered = 0;
        }

        $data = $this->equipament->getEquipaments($company_id, $ini, $length, $searchUser, $orderBy);

        // get string query
        // DB::getQueryLog();

        $permissionUpdate = $this->hasPermission('EquipamentUpdatePost');
        $permissionDelete = $this->hasPermission('EquipamentDeletePost');

        $i = 0;
        foreach ($data as $key => $value) {
            $i++;
            $buttons = "<a href='".route('equipament.edit', ['id' => $value['id']])."' class='btn btn-primary btn-sm btn-rounded btn-action' data-toggle='tooltip'";
            $buttons .= $permissionUpdate ? "title='Editar' ><i class='fas fa-edit'></i></a>" : "title='Visualizar' ><i class='fas fa-eye'></i></a>";
            $buttons .= $permissionDelete ? "<button class='btn btn-danger btnRemoveEquipament btn-sm btn-rounded btn-action ml-md-1' data-toggle='tooltip' title='Excluir' equipament-id='{$value['id']}'><i class='fas fa-times'></i></button>" : '';

            $result[$key] = array(
                $value['id'],
                $value['name'] ?? "Caçamba {$value['volume']}m³",
                $value['reference'],
                $value['stock'],
                $buttons
            );
        }

        if ($filtered == 0) $filtered = $i;

        $output = array(
            "draw" => $draw,
            "recordsTotal" => $this->equipament->getCountEquipaments($company_id),
            "recordsFiltered" => $filtered,
            "data" => $result
        );

        return response()->json($output);
    }

    private function formatDataEquipament($request)
    {
        $obj = new \stdClass;

        $obj->company_id    = $request->user()->company_id;
        $obj->user_id       = $request->user()->id;
        $obj->name          = $request->type_equipament === "cacamba" ? null : filter_var($request->name, FILTER_SANITIZE_STRING);
        $obj->volume        = $request->type_equipament === "others" ? null : filter_var($request->volume, FILTER_VALIDATE_INT);
        $obj->reference     = filter_var($request->reference, FILTER_SANITIZE_STRING);
        $obj->manufacturer  = $request->manufacturer ? filter_var($request->manufacturer, FILTER_SANITIZE_STRING) : null;
        $obj->value         = $request->value ? $this->transformMoneyBr_En($request->value) : 0.00;
        $obj->stock         = $request->stock ? filter_var($request->stock, FILTER_VALIDATE_INT) : 0;
        $obj->equipament_id = isset($request->equipament_id) ? (int)$request->equipament_id : null;

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

    public function getEquipaments(Request $request)
    {
        $company_id         = $request->user()->company_id;
        $searchEquipament   = str_replace('*','', filter_var($request->searchEquipament, FILTER_SANITIZE_STRING));
        $equipamentData     = [];
        $getCacamba         = false;
        $equipamentInUse    = $request->equipamentInUse;

        if ($this->likeText('%'.strtolower(str_replace(['ç', 'Ç'],'c',$searchEquipament)).'%', 'cacamba'))
            $getCacamba = true;

        $equipaments = $this->equipament->getEquipamentRental($company_id, $searchEquipament, $getCacamba, $equipamentInUse);

        foreach ($equipaments as $equipament)
            array_push($equipamentData, [
                'id'        => $equipament->id,
                'name'      => $equipament->name ?? "Caçamba {$equipament->volume}m³",
                'reference' => $equipament->reference,
                'stock'     => $equipament->stock
            ]);

        return response()->json($equipamentData);
    }

    public function getEquipament(Request $request)
    {
        $company_id     = $request->user()->company_id;
        $equipament_id  = $request->idEquipament;

        $validStock = $request->validStock ? true : false;

        $equipament = $this->equipament->getEquipament($equipament_id, $company_id);

        if (!$equipament)
            return response()->json(['success' => false, 'data' => 'Equipamento não encontrado.']);

        if ($validStock && $equipament->stock <= 0)
            return response()->json(['success' => false, 'data' => 'Equipamento sem estoque para uso.']);

        $equipamentData = [
            'id'        => $equipament->id,
            'name'      => $equipament->name ?? "Caçamba {$equipament->volume}m³",
            'reference' => $equipament->reference,
            'stock'     => $equipament->stock
        ];


        return response()->json(['success' => true, 'data' => $equipamentData]);
    }

    public function getStockEquipament(Request $request)
    {
        $company_id     = $request->user()->company_id;
        $equipament_id  = $request->idEquipament;

        $equipament = $this->equipament->getEquipament($equipament_id, $company_id);

        return response()->json($equipament->stock);
    }

    public function getPriceEquipament(Request $request)
    {
        $company_id     = $request->user()->company_id;
        $equipament_id  = $request->idEquipament;
        $diff_days      = $request->diffDays;

        $equipament = $this->equipament->getEquipament($equipament_id, $company_id);

        // não encontrou o equipamento, retorna zerioo
        if (!$equipament) return 0;

        $equipamentWallet = $this->equipament_wallet->getValueWalletsEquipament($company_id, $equipament_id, $diff_days);

        if (!$equipamentWallet) return response()->json($equipament->value);

        return response()->json($equipamentWallet->value);
    }

    public function getPriceStockEquipament(Request $request)
    {
        $company_id     = $request->user()->company_id;
        $equipament_id  = $request->idEquipament;
        $diff_days      = $request->diffDays;

        $equipament = $this->equipament->getEquipament($equipament_id, $company_id);

        // não encontrou o equipamento, retorna zerioo
        if (!$equipament) return response()->json(['price' => 0, 'stock' => 0]);

        $equipamentWallet = $this->equipament_wallet->getValueWalletsEquipament($company_id, $equipament_id, $diff_days);

        if (!$equipamentWallet) return response()->json(['price' => $equipament->value, 'stock' => $equipament->stock]);

        return response()->json(['price' => $equipamentWallet->value, 'stock' => $equipament->stock]);
    }
}
