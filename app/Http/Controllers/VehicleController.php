<?php

namespace App\Http\Controllers;

use App\Http\Requests\VehicleCreatePost;
use App\Http\Requests\VehicleDeletePost;
use App\Http\Requests\VehicleUpdatePost;
use App\Models\Driver;
use App\Models\Vehicle;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use stdClass;

class VehicleController extends Controller
{
    public Vehicle $vehicle;
    public Driver $driver;

    public function __construct()
    {
        $this->vehicle = new Vehicle();
        $this->driver = new Driver();
    }

    public function index(): Factory|View|RedirectResponse|Application
    {
        if (!hasPermission('VehicleView')) {
            return redirect()->route('dashboard')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        return view('vehicle.index');
    }

    public function fetchVehicles(Request $request): JsonResponse
    {
        $result     = array();
        $draw       = $request->input('draw');
        $company_id = $request->user()->company_id;

        try {
            $filters        = array();
            $filter_default = array();
            $fields_order   = array('id','name','brand','model','reference', '');

            $filter_default[]['where']['company_id'] = $company_id;

            $query = array(
                'from' => 'vehicles'
            );

            $data = fetchDataTable(
                $query,
                array('name', 'asc'),
                null,
                ['VehicleView'],
                $filters,
                $fields_order,
                $filter_default
            );

        } catch (Exception $exception) {
            return response()->json(array(
                    "draw"              => $draw,
                    "recordsTotal"      => 0,
                    "recordsFiltered"   => 0,
                    "data"              => $result,
                    "message"           => $exception->getMessage()
                )
            );
        }

        $permissionUpdate = hasPermission('VehicleUpdatePost');
        $permissionDelete = hasPermission('VehicleDeletePost');

        foreach ($data['data'] as $value) {
            $buttons = "<a href='".route('vehicle.edit', ['id' => $value->id])."' class='btn btn-primary btn-sm btn-rounded btn-action' data-toggle='tooltip'";
            $buttons .= $permissionUpdate ? "title='Editar' ><i class='fas fa-edit'></i></a>" : "title='Visualizar' ><i class='fas fa-eye'></i></a>";
            $buttons .= $permissionDelete ? "<button class='btn btn-danger btnRemoveVehicle btn-sm btn-rounded btn-action ml-md-1' data-toggle='tooltip' title='Excluir' vehicle-id='{$value->id}'><i class='fas fa-times'></i></button>" : '';

            $result[] = array(
                $value->id,
                $value->name,
                $value->brand,
                $value->model,
                $value->reference,
                $buttons
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

    public function delete(VehicleDeletePost $request): JsonResponse
    {
        $company_id = $request->user()->company_id;
        $vehicle_id = $request->input('vehicle_id');

        if (!$this->vehicle->getVehicle($vehicle_id, $company_id)) {
            return response()->json(['success' => false, 'message' => 'Não foi possível localizar o veículo!']);
        }

        if (!$this->vehicle->remove($vehicle_id, $company_id)) {
            return response()->json(['success' => false, 'message' => 'Não foi possível excluir o veículo!']);
        }

        return response()->json(['success' => true, 'message' => 'Veículo excluído com sucesso!']);
    }

    public function create(): Factory|View|RedirectResponse|Application
    {
        if (!hasPermission('VehicleCreatePost')) {
            return redirect()->route('driver.index')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        $company_id = Auth::user()->__get('company_id');
        $drivers = $this->driver->getDrivers($company_id);

        return view('vehicle.create', compact('drivers'));
    }

    public function insert(VehicleCreatePost $request): JsonResponse|RedirectResponse
    {
        // data driver
        $dataVehicle = $this->formatDataVehicle($request);
        $isAjax = isAjax();

        $updateVehicle = $this->vehicle->insert(
            array(
                'company_id'    => $dataVehicle->company_id,
                'name'          => $dataVehicle->name,
                'brand'         => $dataVehicle->brand,
                'model'         => $dataVehicle->model,
                'reference'     => $dataVehicle->reference,
                'board'         => $dataVehicle->board,
                'driver_id'     => $dataVehicle->driver,
                'observation'   => $dataVehicle->observation,
                'user_insert'   => $dataVehicle->user_id
            )
        );

        if($updateVehicle) {
            DB::commit();

            if ($isAjax) {
                return response()->json(['success' => true, 'message' => 'Veículo cadastrado com sucesso.', 'vehicle_id' => $updateVehicle->id]);
            }

            return redirect()->route('vehicle.index')
                ->with('success', "Veículo com o código {$updateVehicle->id}, cadastrado com sucesso!");
        }

        DB::rollBack();

        if ($isAjax) {
            return response()->json(['success' => false, 'message' => 'Não foi possível cadastrar o veículo, tente novamente!']);
        }

        return redirect()->back()
            ->withErrors(['Não foi possível cadastrar o veículo, tente novamente!'])
            ->withInput();
    }

    public function edit($id): View|Factory|RedirectResponse|Application
    {
        $company_id = Auth::user()->__get('company_id');

        $vehicle = $this->vehicle->getVehicle($id, $company_id);
        if (!$vehicle) {
            return redirect()->route('vehicle.index');
        }

        $drivers = $this->driver->getDrivers($company_id);

        return view('vehicle.update', compact('vehicle', 'drivers'));
    }

    public function update(VehicleUpdatePost $request): RedirectResponse
    {
        // data driver
        $dataVehicle = $this->formatDataVehicle($request);

        if (!$this->vehicle->getVehicle($dataVehicle->vehicle_id, $dataVehicle->company_id)) {
            return redirect()->back()
                ->withErrors(['Não foi possível localizar o veículo para atualizar!'])
                ->withInput();
        }

        $updateVehicle = $this->vehicle->edit(
            array(
                'name'          => $dataVehicle->name,
                'brand'         => $dataVehicle->brand,
                'model'         => $dataVehicle->model,
                'reference'     => $dataVehicle->reference,
                'board'         => $dataVehicle->board,
                'driver_id'     => $dataVehicle->driver,
                'observation'   => $dataVehicle->observation,
                'user_update'   => $dataVehicle->user_id
            ),
            $dataVehicle->vehicle_id
        );

        if($updateVehicle) {
            DB::commit();
            return redirect()->route('vehicle.index')
                ->with('success', "Veículo com o código $dataVehicle->vehicle_id, alterado com sucesso!");
        }

        DB::rollBack();
        return redirect()->back()
            ->withErrors(['Não foi possível alterar o veículo, tente novamente!'])
            ->withInput();
    }

    private function formatDataVehicle(VehicleCreatePost|VehicleUpdatePost $request): stdClass
    {
        $obj = new stdClass;

        $obj->company_id    = $request->user()->company_id;
        $obj->user_id       = $request->user()->id;
        $obj->name          = filter_var($request->input('name'));
        $obj->reference     = $request->input('reference')    ? (filter_var($request->input('reference')) ? $request->input('reference') : null) : null;
        $obj->driver        = $request->input('driver')       ? (filter_var($request->input('driver'), FILTER_VALIDATE_INT) ? (int)$request->input('driver') : null) : null;
        $obj->reference     = $request->input('reference')    ? filter_var($request->input('reference')) : null;
        $obj->brand         = $request->input('brand')        ? filter_var($request->input('brand')) : null;
        $obj->model         = $request->input('model')        ? filter_var($request->input('model')) : null;
        $obj->board         = $request->input('board')        ? filter_var($request->input('board')) : null;
        $obj->observation   = $request->input('observation')  ? filter_var($request->input('observation')) : null;
        $obj->vehicle_id    = $request->input('vehicle_id')   ? (int)$request->input('vehicle_id') : null;

        return $obj;
    }

    public function getVehicles(Request $request): JsonResponse
    {
        $company_id = $request->user()->company_id;
        $vehicleData = [];
        $lastId = 0;

        $vehicles = $this->vehicle->getVehicles($company_id, null, null, null, array('field' => 'name', 'order' => 'ASC'));

        foreach ($vehicles as $vehicle) {
            $vehicleData[] = ['id' => $vehicle->id, 'name' => $vehicle->name];
            if ($vehicle->id > $lastId) {
                $lastId = $vehicle->id;
            }
        }

        return response()->json(['data' => $vehicleData, 'lastId' => $lastId]);
    }

    public function getVehicle(int $id = null): JsonResponse
    {
        if (is_null($id)) {
            return response()->json();
        }

        $company_id = Auth::user()->__get('company_id');
        $driver = false;

        $vehicles = $this->vehicle->getVehicle($id, $company_id);
        if ($vehicles->driver_id) {
            $driver = $this->driver->getDriver($vehicles->driver_id, $company_id);
        }

        return response()->json(array(
            'name'          => $vehicles->name,
            'brand'         => $vehicles->brand,
            'model'         => $vehicles->model,
            'reference'     => $vehicles->reference,
            'board'         => $vehicles->board,
            'observation'   => $vehicles->observation,
            'driver_id'     => $vehicles->driver_id,
            'driver_name'   => $driver->name ?? null
        ));
    }

    public function get(int $id): JsonResponse
    {
        $company_id = Auth::user()->__get('company_id');

        return response()->json($this->vehicle->getVehicle($id, $company_id));
    }
}
