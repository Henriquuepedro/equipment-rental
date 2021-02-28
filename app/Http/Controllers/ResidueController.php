<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResidueCreatePost;
use App\Http\Requests\ResidueDeletePost;
use App\Http\Requests\ResidueUpdatePost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Residue;

class ResidueController extends Controller
{
    public $residue;

    public function __construct(Residue $residue)
    {
        $this->residue = $residue;
    }

    public function index()
    {
        if (!$this->hasPermission('ResidueView')) {
            return redirect()->route('dashboard')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        return view('residue.index');
    }

    public function fetchResidues(Request $request): JsonResponse
    {
        if (!$this->hasPermission('ResidueView'))
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

            $fieldsOrder = array('name', '');
            $fieldOrder =  $fieldsOrder[$request->order[0]['column']];
            if ($fieldOrder != "") {
                $orderBy['field'] = $fieldOrder;
                $orderBy['order'] = $direction;
            }
        }

        $data = $this->residue->getFetchResidues($company_id, $ini, $length, $searchUser, $orderBy);

        // get string query
        // DB::getQueryLog();

        $permissionUpdate = $this->hasPermission('ResidueUpdatePost');
        $permissionDelete = $this->hasPermission('ResidueDeletePost');

        foreach ($data as $key => $value) {
            $buttons  = '';
            $buttons .= $permissionUpdate ? "<button class='btn btn-primary btn-sm btn-rounded btn-action editResidueModal' data-toggle='tooltip' title='Editar' residue-id='{$value['id']}'><i class='fas fa-edit'></i></button>" : '';
            $buttons .= $permissionDelete ? "<button class='btn btn-danger btnRemoveResidue btn-sm btn-rounded btn-action ml-md-1' data-toggle='tooltip' title='Excluir' residue-id='{$value['id']}'><i class='fas fa-times'></i></button>" : '';

            $result[$key] = array(
                $value['name'],
                date('d/m/Y H:i', strtotime($value['created_at'])),
                $buttons
            );
        }

        $output = array(
            "draw" => $draw,
            "recordsTotal" => $this->residue->getCountFetchResidues($company_id),
            "recordsFiltered" => $this->residue->getCountFetchResidues($company_id, $searchUser),
            "data" => $result
        );

        return response()->json($output);
    }

    public function update(ResidueUpdatePost $request)
    {
        // data residue
        $dataResidue = $this->formatDataResidue($request);
        $isAjax = $this->isAjax();

        if (!$this->residue->getResidue($dataResidue->company_id, $dataResidue->residue_id)) {

            if ($isAjax)
                return response()->json(['success' => false, 'message' => 'Não foi possível localizar o resíduo para atualizar!']);

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

        if($updateResidue) {
            if ($isAjax)
                return response()->json(['success' => true, 'message' => 'Resíduo atualizado com sucesso!']);

            return redirect()->route('equipment.index')
                ->with('success', "Resíduo atualizado com sucesso!");
        }

        if ($isAjax)
            return response()->json(['success' => false, 'message' => 'Não foi possível alterar o resíduo, tente novamente!']);

        return redirect()->back()
            ->withErrors(['Não foi possível alterar o resíduo, tente novamente!'])
            ->withInput();
    }

    public function getResidues(Request $request)
    {
        $company_id = $request->user()->company_id;
        $residueData = array();
        $lastId = 0;

        $residues = $this->residue->getResidues($company_id);

        foreach ($residues as $residue) {
            array_push($residueData, ['id' => $residue->id, 'name' => $residue->name]);
            if ($residue->id > $lastId) $lastId = $residue->id;
        }

        return response()->json(['data' => $residueData, 'lastId' => $lastId]);
    }

    private function formatDataResidue($request)
    {
        $obj = new \stdClass;

        $obj->company_id    = $request->user()->company_id;
        $obj->user_id       = $request->user()->id;
        $obj->name          = filter_var($request->name, FILTER_SANITIZE_STRING);
        $obj->residue_id    = isset($request->residue_id) ? (int)$request->residue_id : null;

        return $obj;
    }

    public function insert(ResidueCreatePost $request)
    {
        // data residue
        $dataResidue = $this->formatDataResidue($request);
        $isAjax = $this->isAjax();

        $createResidue = $this->residue->insert(array(
            'company_id'    => $dataResidue->company_id,
            'name'          => $dataResidue->name,
            'user_insert'   => $dataResidue->user_id
        ));

        $residueId = $createResidue->id;

        if($createResidue) {

            if ($isAjax)
                return response()->json(['success' => true, 'message' => 'Resíduo cadastrado com sucesso.', 'residue_id' => $residueId]);

            return redirect()->route('residue.index')
                ->with('success', "Resíduo com o código {$residueId}, cadastrado com sucesso!");
        }

        if ($isAjax)
            return response()->json(['success' => false, 'message' => 'Não foi possível cadastrar o resíduo, tente novamente!']);

        return redirect()->back()
            ->withErrors(['Não foi possível cadastrar o resíduo, tente novamente!'])
            ->withInput();
    }

    public function delete(ResidueDeletePost $request)
    {
        $company_id = $request->user()->company_id;
        $residue_id = $request->residue_id;

        if (!$this->residue->getResidue($company_id, $residue_id))
            return response()->json(['success' => false, 'message' => 'Não foi possível localizar o resíduo!']);

        if (!$this->residue->remove($company_id, $residue_id))
            return response()->json(['success' => false, 'message' => 'Não foi possível excluir o resíduo!']);

        return response()->json(['success' => true, 'message' => 'Resíduo excluído com sucesso!']);
    }
}
