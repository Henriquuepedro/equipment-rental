<?php

namespace App\Http\Controllers;

use App\Models\Guide;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class GuideController extends Controller
{
    private Guide $guide;

    public function __construct()
    {
        $this->guide = new Guide();
    }

    public function index(): Factory|View|RedirectResponse|Application
    {
        return view('guide.index');
    }

    public function fetch(Request $request): JsonResponse
    {
        $result     = array();
        $draw       = $request->input('draw');

        try {
            $filters        = array();
            $filter_default = array();
            $fields_order   = array('title', '');

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
                newDropdownButtonsDataList([
                    [
                        'tag'       => 'a',
                        'title'     => 'Visualizar Manual',
                        'icon'      => 'fas fa-eye',
                        'href'      => url("assets/files/guides/$value->file/guide.pdf"),
                        'attribute' => 'target="_blank"'
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
}
