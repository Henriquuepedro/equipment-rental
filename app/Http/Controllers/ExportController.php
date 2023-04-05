<?php

namespace App\Http\Controllers;

use App\Exports\RegistersExport;
use App\Models\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends Controller
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client;
    }

    /**
     * @param   Request             $request
     * @return  BinaryFileResponse
     */
    public function register(Request $request): BinaryFileResponse
    {
        $company_id = hasAdminMaster() ? $request->input('company') : $request->user()->company_id;

        return Excel::download(
            new RegistersExport(
                $request->input('type'),
                $company_id,
                $request->input('fields-selected')
            ),
            $request->input('type') . '.xlsx'
        );
    }

    public function getFields(string $option): JsonResponse
    {
        $table = $this->$option->getTable();
        $columns = array();

        foreach (Schema::getColumnListing($table) as $column) {
            if (in_array($column, array('id', 'company_id'))) {
                continue;
            }

            $columns[] = $column;
        }
        return response()->json($columns);

    }
}
