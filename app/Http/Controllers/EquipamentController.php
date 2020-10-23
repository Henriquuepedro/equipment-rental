<?php

namespace App\Http\Controllers;

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
        return view('equipament.index');
    }

    public function create()
    {
        return view('equipament.create');
    }

    public function insert(Request $request)
    {
        // data equipament
        $company_id     = $request->user()->company_id;
        $user_id        = $request->user()->id;
        $name           = filter_var($request->type_equipament, FILTER_SANITIZE_STRING) === "cacamba" ? null : filter_var($request->name, FILTER_SANITIZE_STRING);
        $volume         = filter_var($request->type_equipament, FILTER_SANITIZE_STRING) === "cacamba" ? filter_var($request->volume, FILTER_VALIDATE_INT) : null;
        $reference      = filter_var($request->reference, FILTER_SANITIZE_STRING);
        $manufacturer   = $request->manufacturer ? filter_var($request->manufacturer, FILTER_SANITIZE_STRING) : null;
        $value          = $request->value ? $this->transformMoneyBr_En($request->value) : 0.00;
        $stock          = $request->stock ? filter_var($request->stock, FILTER_VALIDATE_INT) : 0;

        DB::beginTransaction();// Iniciando transação manual para evitar updates não desejáveis

        $createEquipament = $this->equipament->insert(array(
            'company_id'    => $company_id,
            'name'          => $name,
            'reference'     => $reference,
            'stock'         => $stock,
            'value'         => $value,
            'manufacturer'  => $manufacturer,
            'volume'        => $volume,
            'user_insert'   => $user_id
        ));

        $createPeriods = true;
        $equipamentId = $createEquipament->id;

        // data period
        $qtyPeriods = isset($request->day_start) ? count($request->day_start) : 0;
        for ($per = 0; $per < $qtyPeriods; $per++) {
            $day_start      = filter_var((int)$request->day_start[$per], FILTER_VALIDATE_INT);
            $day_end        = filter_var((int)$request->day_end[$per], FILTER_VALIDATE_INT);
            $value_period   = $this->transformMoneyBr_En($request->value_period[$per]);

            if ($day_start < 0 || $day_end <= 0 || $value_period <= 0)
                return redirect()->back()
                    ->withErrors(['Existem erros no período. Dia inicial não pode ser negativo. Dia final deve ser maior que zero e valor deve ser maior que zero'])
                    ->withInput();

            $createPeriods = $this->equipament_wallet->insert(array(
                'company_id'    => $company_id,
                'equipament_id' => $equipamentId,
                'day_start'     => $day_start,
                'day_end'       => $day_end,
                'value'         => $value_period,
                'user_insert'   => $user_id
            ));
        }

        if($createEquipament && $createPeriods) {
            DB::commit();
            return redirect()->route('equipament.index')
                ->with('success', "Equipamento com o código {$equipamentId}, cadastrado com sucesso!");
        }

        DB::rollBack();
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

    public function update(Request $request)
    {
        // data equipament
        $company_id     = $request->user()->company_id;
        $equipament_id  = (int)$request->equipament_id;


        if (!$this->equipament->getEquipament($equipament_id, $company_id))
            return redirect()->back()
                ->withErrors(['Não foi possível localizar o equipamento para atualizar!'])
                ->withInput();

        $user_id        = $request->user()->id;
        $name           = filter_var($request->type_equipament, FILTER_SANITIZE_STRING) === "cacamba" ? null : filter_var($request->name, FILTER_SANITIZE_STRING);
        $volume         = filter_var($request->type_equipament, FILTER_SANITIZE_STRING) === "cacamba" ? filter_var($request->volume, FILTER_VALIDATE_INT) : null;
        $reference      = filter_var($request->reference, FILTER_SANITIZE_STRING);
        $manufacturer   = $request->manufacturer ? filter_var($request->manufacturer, FILTER_SANITIZE_STRING) : null;
        $value          = $request->value ? $this->transformMoneyBr_En($request->value) : 0.00;
        $stock          = $request->stock ? filter_var($request->stock, FILTER_VALIDATE_INT) : 0;

        DB::beginTransaction();// Iniciando transação manual para evitar updates não desejáveis

        $updateEquipament = $this->equipament->edit(array(
            'name'          => $name,
            'reference'     => $reference,
            'stock'         => $stock,
            'value'         => $value,
            'manufacturer'  => $manufacturer,
            'volume'        => $volume,
            'user_update'   => $user_id
        ), $equipament_id);

        $updatePeriods = true;

        // data period
        $qtyPeriods = isset($request->day_start) ? count($request->day_start) : 0;
        // remover todos os períodos do equipamento
        $this->equipament_wallet->removeAllEquipament($equipament_id, $company_id);
        for ($per = 0; $per < $qtyPeriods; $per++) {
            $day_start      = filter_var((int)$request->day_start[$per], FILTER_VALIDATE_INT);
            $day_end        = filter_var((int)$request->day_end[$per], FILTER_VALIDATE_INT);
            $value_period   = $this->transformMoneyBr_En($request->value_period[$per]);

            if ($day_start < 0 || $day_end <= 0 || $value_period <= 0)
                return redirect()->back()
                    ->withErrors(['Existem erros no período. Dia inicial não pode ser negativo. Dia final deve ser maior que zero e valor deve ser maior que zero'])
                    ->withInput();

            $updatePeriods = $this->equipament_wallet->insert(array(
                'company_id'    => $company_id,
                'equipament_id' => $equipament_id,
                'day_start'     => $day_start,
                'day_end'       => $day_end,
                'value'         => $value_period,
                'user_insert'   => $user_id
            ));
        }

        if($updateEquipament && $updatePeriods) {
            DB::commit();
            return redirect()->route('equipament.index')
                ->with('success', "Equipamento com o código {$equipament_id}, alterado com sucesso!");
        }

        DB::rollBack();
        return redirect()->back()
            ->withErrors(['Não foi possível alterar o equipamento, tente novamente!'])
            ->withInput();
    }

    public function delete(Request $request)
    {
        $company_id = $request->user()->company_id;
        $equipament_id = $request->equipament_id;

        if (!$this->equipament->getEquipament($equipament_id, $company_id)) {
            echo json_encode(['success' => false, 'message' => 'Não foi possível localizar o equipamento!']);
            die;
        }

        if (!$this->equipament->remove($equipament_id, $company_id)) {
            echo json_encode(['success' => false, 'message' => 'Não foi possível excluir o equipamento!']);
            die;
        }

        echo json_encode(['success' => true, 'message' => 'Equipamento excluído com sucesso!']);
    }

    public function fetchEquipaments(Request $request)
    {
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

        $i = 0;
        foreach ($data as $key => $value) {
            $i++;
            $buttons = "<a href='".route('equipament.edit', ['id' => $value['id']])."' class='btn btn-primary btn-sm btn-rounded btn-action' data-toggle='tooltip' title='Editar' ><i class='fas fa-edit'></i></a>
                        <button class='btn btn-danger btnRemoveEquipament btn-sm btn-rounded btn-action ml-md-1' data-toggle='tooltip' title='Excluir' equipament-id='{$value['id']}'><i class='fas fa-times'></i></button>";

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

        echo json_encode($output);
    }
}
