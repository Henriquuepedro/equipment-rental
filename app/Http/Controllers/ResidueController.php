<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResidueCreatePost;
use Illuminate\Http\Request;
use App\Models\Residue;

class ResidueController extends Controller
{
    public $residue;

    public function __construct(Residue $residue)
    {
        $this->residue = $residue;
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
}
