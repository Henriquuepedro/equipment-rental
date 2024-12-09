<?php

namespace App\Http\Controllers;

use App\Exports\DriverCommissionsExport;
use App\Exports\RegistersExport;
use App\Models\Client;
use App\Models\Company;
use App\Models\Driver;
use App\Models\Equipment;
use App\Models\Provider;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Barryvdh\DomPDF\PDF;

class ExportController extends Controller
{
    private Client $client;
    private Equipment $equipment;
    private Vehicle $vehicle;
    private Driver $driver;
    private Provider $provider;
    private Company $company;
    private PDF $pdf;

    public function __construct(PDF $pdf)
    {
        $this->client       = new Client;
        $this->driver       = new Driver;
        $this->equipment    = new Equipment;
        $this->provider     = new Provider;
        $this->vehicle      = new Vehicle;
        $this->company      = new Company();
        $this->pdf          = $pdf;
    }

    /**
     * @param Request $request
     * @return BinaryFileResponse|Response|RedirectResponse
     */
    public function register(Request $request): BinaryFileResponse | Response | RedirectResponse
    {
        $company_id = hasAdminMaster() ? $request->input('company') : $request->user()->company_id;

        if (empty($company_id)) {
            return redirect()->route('report.register')
                ->withErrors("Selecione uma empresa.");
        }

        $dataToExport = new RegistersExport(
            $request->input('type'),
            $company_id,
            $request->input('fields-selected')
        );

        if ($request->has('export_csv')) {
            return Excel::download(
                $dataToExport,
                $request->input('type') . '.xlsx'
            );
        }

        $company_data = $this->company->getCompany($company_id);
        $contentPrint = [
            'company'       => $company_data,
            'logo_company'  => getImageCompanyBase64($company_data),
            'data'          => $dataToExport->collection()->toArray()
        ];

        $pdf = $this->pdf->loadView('print.report.register', $contentPrint)->setPaper('a4', $request->has('print_a4_v') ? 'portrait' : 'landscape');
        return $pdf->stream();

    }

    /**
     * @param Request $request
     * @return BinaryFileResponse|Response|RedirectResponse
     */
    public function driver_commission(Request $request): BinaryFileResponse | Response | RedirectResponse
    {
        $company_id = hasAdminMaster() ? $request->input('company') : $request->user()->company_id;

        if (empty($company_id)) {
            return redirect()->route('report.commission')
                ->withErrors("Selecione uma empresa.");
        }

        $driver = $this->driver->getDriver($request->input('drivers'), $company_id);

        if (empty($driver)) {
            return redirect()->route('report.commission')
                ->withErrors("Selecione um motorista.");
        }

        if (empty($driver['commission'])) {
            return redirect()->route('report.commission')
                ->withErrors("Motorista sem comissão configurada.");
        }

        $interval_dates = explode(' - ', $request->input('intervalDates'));
        $date_start     = dateBrazilToDateInternational($interval_dates[0]);
        $date_end       = dateBrazilToDateInternational($interval_dates[1]);

        $dataToExport = new DriverCommissionsExport(
            $company_id,
            $driver['id'],
            $date_start,
            $date_end
        );

        if ($request->has('export_csv')) {
            return Excel::download(
                $dataToExport,
                $request->input('type') . '.xlsx'
            );
        }

        $company_data = $this->company->getCompany($company_id);
        $contentPrint = [
            'company'       => $company_data,
            'logo_company'  => getImageCompanyBase64($company_data),
            'data'          => $dataToExport->collection()->toArray(),
            'commission'    => $driver['commission'],
            'driver_id'     => $driver['id'],
            'driver_name'   => $driver['name'],
            'date_start'    => $date_start,
            'date_end'      => $date_end
        ];

        $pdf = $this->pdf->loadView('print.report.driver_commission', $contentPrint)->setPaper('a4', $request->has('print_a4_v') ? 'portrait' : 'landscape');
        return $pdf->stream();

    }

    public function getFields(string $option): JsonResponse
    {
        $table = $this->$option->getTable();
        $columns = array();

        foreach ($this->$option->getConnection()->getSchemaBuilder()->getColumnListing($table) as $column) {
            if (in_array($column, array('id', 'company_id'))) {
                continue;
            }

            $columns[$column] = Lang::get("field.$column");
        }
        return response()->json($columns);

    }
}
