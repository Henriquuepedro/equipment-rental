<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Guide;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GuideController extends Controller
{
    private Guide $guide;

    public function __construct()
    {
        $this->guide = new Guide();
    }

    public function index(): Factory|View|RedirectResponse|Application
    {
        return view('master.guide.index');
    }

    public function fetch(Request $request): JsonResponse
    {
        $result     = array();
        $draw       = $request->input('draw');

        try {
            $filters        = array();
            $filter_default = array();
            $fields_order   = array('title', 'created_at', '');

            $query = array(
                'from' => 'guides'
            );

            $data = fetchDataTable(
                $query,
                array('title', 'asc'),
                null,
                [],
                $filters,
                $fields_order,
                $filter_default
            );

        } catch (Exception $exception) {
            return response()->json(getErrorDataTables($exception->getMessage(), $draw));
        }

        foreach ($data['data'] as $value) {
            $result[] = array(
                $value->title,
                formatDateInternational($value->created_at, DATETIME_BRAZIL_NO_SECONDS),
                newDropdownButtonsDataList([
                    [
                        'tag'       => 'a',
                        'title'     => 'Visualizar Manual',
                        'icon'      => 'fas fa-eye',
                        'href'      => url("assets/files/guides/$value->file/guide.pdf"),
                        'attribute' => 'target="_blank"'
                    ],
                    [
                        'tag'   => 'a',
                        'title' => 'Atualizar Manual',
                        'icon'  => 'fas fa-edit',
                        'href'  => route('master.guide.edit', ['id' => $value->id])
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

    public function create(): Factory|View|RedirectResponse|Application
    {
        return view('master.guide.create');
    }


    public function insert(Request $request): JsonResponse|RedirectResponse
    {
        $validator_file = Validator::make($request->file(),
            [
                'file' => 'required|mimes:pdf',
            ], [
                'file.required' => 'Imagem é obrigatório.',
                'file.mimes'    => 'É aceito somente o tipo pdf.',
            ]
        );

        $validator = Validator::make($request->all(),
            [
                'title' => 'required',
            ], [
                'subject.title' => 'Título do manual é inválido.'
            ]
        );

        if ($validator_file->fails()) {
            return redirect()->back()
                ->withErrors( $validator_file->errors()->all())
                ->withInput();
        }

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors( $validator->errors()->all())
                ->withInput();
        }

        $uniqid = uniqid();

        $this->guide->insert([
            'title' => $request->input('title'),
            'file'  => $uniqid
        ]);

        try {
            checkPathExistToCreate("assets/files/guides/$uniqid");
            uploadFile("assets/files/guides/$uniqid", $request->file('file'), 'guide.pdf', ['pdf']);
        } catch (Exception $exception) {
            return redirect()->back()
                ->withErrors([$exception->getMessage()])
                ->withInput();
        }

        return redirect()->route('master.guide.index')
            ->with('success', "Manual cadastrado com sucesso!");
    }

    public function edit(int $id): Factory|View|RedirectResponse|Application
    {
        $guide = $this->guide->get($id);

        if (!$guide) {
            return redirect()->back()
                ->withErrors(['Manual não encontrado'])
                ->withInput();
        }

        return view('master.guide.update', ['guide' => $guide]);
    }


    public function update(Request $request, int $id): JsonResponse|RedirectResponse
    {
        $guide = $this->guide->get($id);

        if (!$guide) {
            return redirect()->back()
                ->withErrors(['Manual não encontrado'])
                ->withInput();
        }

        if ($request->has('file')) {
            $validator_file = Validator::make($request->file(),
                [
                    'file' => 'required|mimes:pdf',
                ], [
                    'file.required' => 'Imagem é obrigatório.',
                    'file.mimes' => 'É aceito somente o tipo pdf.',
                ]
            );

            if ($validator_file->fails()) {
                return redirect()->back()
                    ->withErrors($validator_file->errors()->all())
                    ->withInput();
            }
        }

        $validator = Validator::make($request->all(),
            [
                'title' => 'required',
            ], [
                'subject.title' => 'Título do manual é inválido.'
            ]
        );

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator->errors()->all())
                ->withInput();
        }

        $this->guide->edit(['title' => $request->input('title')], $id);

        try {
            if ($request->has('file')) {
                checkPathExistToCreate("assets/files/guides/$guide->file");
                uploadFile("assets/files/guides/$guide->file", $request->file('file'), 'guide.pdf', ['pdf']);
            }
        } catch (Exception $exception) {
            return redirect()->back()
                ->withErrors([$exception->getMessage()])
                ->withInput();
        }

        return redirect()->route('master.guide.index')
            ->with('success', "Manual atualizado com sucesso!");
    }
}
