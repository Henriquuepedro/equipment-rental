<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResidueCreatePost;
use App\Http\Requests\ResidueDeletePost;
use App\Http\Requests\ResidueUpdatePost;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\Residue;
use Illuminate\Support\Facades\Auth;

class ResidueController extends Controller
{
    public Residue $residue;

    public function __construct()
    {
        $this->residue = new Residue();
    }

    public function index(): Factory|View|RedirectResponse|Application
    {
        if (!hasPermission('ResidueView')) {
            return redirect()->route('dashboard')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        return view('residue.index');
    }

    public function fetchResidues(Request $request): JsonResponse
    {
        $result     = array();
        $draw       = $request->input('draw');
        $company_id = $request->user()->company_id;

        try {
            $filters        = array();
            $filter_default = array();
            $fields_order   = array('name', 'created_at', '');

            $filter_default[]['where']['company_id'] = $company_id;

            $query = array(
                'from' => 'residues'
            );

            $data = fetchDataTable(
                $query,
                array('name', 'asc'),
                null,
                ['ResidueView'],
                $filters,
                $fields_order,
                $filter_default
            );

        } catch (Exception $exception) {
            return response()->json(getErrorDataTables($exception->getMessage(), $draw));
        }

        $permissionUpdate = hasPermission('ResidueUpdatePost');
        $permissionDelete = hasPermission('ResidueDeletePost');

        foreach ($data['data'] as $value) {
            $result[] = array(
                $value->name,
                date('d/m/Y H:i', strtotime($value->created_at)),
                newDropdownButtonsDataList([
                    [
                        'tag'       => 'button',
                        'title'     => 'Atualizar Resíduo',
                        'icon'      => 'fas fa-edit',
                        'class'     => 'editResidueModal',
                        'attribute' => "residue-id='$value->id'",
                        'can'       => $permissionUpdate
                    ],
                    [
                        'tag'       => 'button',
                        'title'     => 'Excluir Resíduo',
                        'icon'      => 'fas fa-times',
                        'class'     => 'btnRemoveResidue',
                        'attribute' => "residue-id='$value->id'",
                        'can'       => $permissionDelete
                    ]
                ], $value->id)
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

    public function update(ResidueUpdatePost $request): JsonResponse|RedirectResponse
    {
        // data residue
        $dataResidue = $this->formatDataResidue($request);
        $isAjax = isAjax();

        if (!$this->residue->getResidue($dataResidue->company_id, $dataResidue->residue_id)) {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => 'Não foi possível localizar o resíduo para atualizar!']);
            }

            return redirect()->back()
                ->withErrors(['Não foi possível localizar o resíduo para atualizar!'])
                ->withInput();
        }

        $updateResidue = $this->residue->edit(
            array(
                'name'          => $dataResidue->name,
                'user_update'   => $dataResidue->user_id
            ),
            $dataResidue->residue_id
        );

        if ($updateResidue) {
            if ($isAjax) {
                return response()->json(['success' => true, 'message' => 'Resíduo atualizado com sucesso!']);
            }

            return redirect()->route('equipment.index')
                ->with('success', "Resíduo atualizado com sucesso!");
        }

        if ($isAjax) {
            return response()->json(['success' => false, 'message' => 'Não foi possível alterar o resíduo, tente novamente!']);
        }

        return redirect()->back()
            ->withErrors(['Não foi possível alterar o resíduo, tente novamente!'])
            ->withInput();
    }

    public function getResidues(Request $request): JsonResponse
    {
        $company_id = $request->user()->company_id;
        $residueData = array();
        $lastId = 0;

        $residues = $this->residue->getResidues($company_id);

        foreach ($residues as $residue) {
            $residueData[] = ['id' => $residue->id, 'name' => $residue->name];
            if ($residue->id > $lastId) {
                $lastId = $residue->id;
            }
        }

        return response()->json(['data' => $residueData, 'lastId' => $lastId]);
    }

    private function formatDataResidue($request): \stdClass
    {
        $obj = new \stdClass;

        $obj->company_id    = $request->user()->company_id;
        $obj->user_id       = $request->user()->id;
        $obj->name          = filter_var($request->name);
        $obj->residue_id    = isset($request->residue_id) ? (int)$request->residue_id : null;

        return $obj;
    }

    public function insert(ResidueCreatePost $request): JsonResponse|RedirectResponse
    {
        // data residue
        $dataResidue = $this->formatDataResidue($request);
        $isAjax = isAjax();

        $createResidue = $this->residue->insert(array(
            'company_id'    => $dataResidue->company_id,
            'name'          => $dataResidue->name,
            'user_insert'   => $dataResidue->user_id
        ));

        $residueId = $createResidue->id;

        if($createResidue) {
            if ($isAjax) {
                return response()->json(['success' => true, 'message' => 'Resíduo cadastrado com sucesso.', 'residue_id' => $residueId]);
            }

            return redirect()->route('residue.index')
                ->with('success', "Resíduo com o código {$residueId}, cadastrado com sucesso!");
        }

        if ($isAjax) {
            return response()->json(['success' => false, 'message' => 'Não foi possível cadastrar o resíduo, tente novamente!']);
        }

        return redirect()->back()
            ->withErrors(['Não foi possível cadastrar o resíduo, tente novamente!'])
            ->withInput();
    }

    public function delete(ResidueDeletePost $request): JsonResponse
    {
        $company_id = $request->user()->company_id;
        $residue_id = $request->input('residue_id');

        if (!$this->residue->getResidue($company_id, $residue_id)) {
            return response()->json(['success' => false, 'message' => 'Não foi possível localizar o resíduo!']);
        }

        if (!$this->residue->remove($company_id, $residue_id)) {
            return response()->json(['success' => false, 'message' => 'Não foi possível excluir o resíduo!']);
        }

        return response()->json(['success' => true, 'message' => 'Resíduo excluído com sucesso!']);
    }

    public function get(int $id): JsonResponse
    {
        $company_id = Auth::user()->__get('company_id');
        $residues = $this->residue->getResidue($company_id, $id);

        return response()->json(count($residues) ? array_map(function($residue) {
            return $residue['name'];
        }, $residues->toArray()): array());
    }
}
