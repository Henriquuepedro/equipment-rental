<?php

namespace App\Http\Controllers;

use App\Http\Requests\VehicleCreatePost;
use App\Http\Requests\VehicleDeletePost;
use App\Http\Requests\VehicleUpdatePost;
use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VehicleController extends Controller
{
    public $vehicle;
    public $driver;

    public function __construct(Vehicle $vehicle, Driver $driver)
    {
        $this->vehicle = $vehicle;
        $this->driver = $driver;
    }

    public function index()
    {
        if (!$this->hasPermission('VehicleView')) {
            return redirect()->route('dashboard')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        return view('vehicle.index');
    }

    public function fetchVehicles(Request $request)
    {
        if (!$this->hasPermission('VehicleView'))
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

            $fieldsOrder = array('id','name','brand','model','reference', '');
            $fieldOrder =  $fieldsOrder[$request->order[0]['column']];
            if ($fieldOrder != "") {
                $orderBy['field'] = $fieldOrder;
                $orderBy['order'] = $direction;
            }
        }

        if (!empty($searchUser)) $filtered = $this->vehicle->getCountVehicles($company_id, $searchUser);
        else $filtered = 0;


        $data = $this->vehicle->getVehicles($company_id, $ini, $length, $searchUser, $orderBy);

        // get string query
        // DB::getQueryLog();

        $permissionUpdate = $this->hasPermission('VehicleUpdatePost');
        $permissionDelete = $this->hasPermission('VehicleDeletePost');

        $i = 0;
        foreach ($data as $key => $value) {
            $i++;
            $buttons = "<a href='".route('vehicle.edit', ['id' => $value['id']])."' class='btn btn-primary btn-sm btn-rounded btn-action' data-toggle='tooltip'";
            $buttons .= $permissionUpdate ? "title='Editar' ><i class='fas fa-edit'></i></a>" : "title='Visualizar' ><i class='fas fa-eye'></i></a>";
            $buttons .= $permissionDelete ? "<button class='btn btn-danger btnRemoveVehicle btn-sm btn-rounded btn-action ml-md-1' data-toggle='tooltip' title='Excluir' vehicle-id='{$value['id']}'><i class='fas fa-times'></i></button>" : '';

            $result[$key] = array(
                $value['id'],
                $value['name'],
                $value['brand'],
                $value['model'],
                $value['reference'],
                $buttons
            );
        }

        if ($filtered == 0) $filtered = $i;

        $output = array(
            "draw" => $draw,
            "recordsTotal" => $this->vehicle->getCountVehicles($company_id),
            "recordsFiltered" => $filtered,
            "data" => $result
        );

        return response()->json($output);
    }

    public function delete(VehicleDeletePost $request)
    {
        $company_id = $request->user()->company_id;
        $vehicle_id = $request->vehicle_id;

        if (!$this->vehicle->getVehicle($vehicle_id, $company_id))
            return response()->json(['success' => false, 'message' => 'Não foi possível localizar o veículo!']);

        if (!$this->vehicle->remove($vehicle_id, $company_id))
            return response()->json(['success' => false, 'message' => 'Não foi possível excluir o veículo!']);

        return response()->json(['success' => true, 'message' => 'Veículo excluído com sucesso!']);
    }

    public function create()
    {
        if (!$this->hasPermission('VehicleCreatePost')) {
            return redirect()->route('driver.index')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        $company_id = Auth::user()->company_id;
        $drivers = $this->driver->getDrivers($company_id);

        return view('vehicle.create', compact('drivers'));
    }

    public function insert(VehicleCreatePost $request)
    {
        // data driver
        $dataVehicle = $this->formatDataVehicle($request);

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
            return redirect()->route('vehicle.index')
                ->with('success', "Veículo com o código {$updateVehicle->id}, cadastrado com sucesso!");
        }

        DB::rollBack();
        return redirect()->back()
            ->withErrors(['Não foi possível cadastrar o veículo, tente novamente!'])
            ->withInput();
    }

    public function edit($id)
    {
        $company_id = Auth::user()->company_id;

        $vehicle = $this->vehicle->getVehicle($id, $company_id);
        if (!$vehicle)
            return redirect()->route('vehicle.index');

        $drivers = $this->driver->getDrivers($company_id);

        return view('vehicle.update', compact('vehicle', 'drivers'));
    }

    public function update(VehicleUpdatePost $request)
    {
        // data driver
        $dataVehicle = $this->formatDataVehicle($request);

        if (!$this->vehicle->getVehicle($dataVehicle->vehicle_id, $dataVehicle->company_id))
            return redirect()->back()
                ->withErrors(['Não foi possível localizar o veículo para atualizar!'])
                ->withInput();

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
                ->with('success', "Veículo com o código {$dataVehicle->vehicle_id}, alterado com sucesso!");
        }

        DB::rollBack();
        return redirect()->back()
            ->withErrors(['Não foi possível alterar o veículo, tente novamente!'])
            ->withInput();
    }

    private function formatDataVehicle($request)
    {
        $obj = new \stdClass;

        $obj->company_id    = $request->user()->company_id;
        $obj->user_id       = $request->user()->id;
        $obj->name          = filter_var($request->name, FILTER_SANITIZE_STRING);
        $obj->reference     = $request->reference ? (filter_var($request->reference, FILTER_SANITIZE_STRING) ? $request->reference : null) : null;
        $obj->driver        = $request->driver ? (filter_var($request->driver, FILTER_VALIDATE_INT) ? (int)$request->driver : null) : null;
        $obj->reference     = $request->reference ? filter_var($request->reference, FILTER_SANITIZE_STRING) : null;
        $obj->brand         = $request->brand ? filter_var($request->brand, FILTER_SANITIZE_STRING) : null;
        $obj->model         = $request->model ? filter_var($request->model, FILTER_SANITIZE_STRING) : null;
        $obj->board         = $request->board ? filter_var($request->board, FILTER_SANITIZE_STRING) : null;
        $obj->observation   = $request->observation ? filter_var($request->observation, FILTER_SANITIZE_STRING) : null;
        $obj->vehicle_id    = isset($request->vehicle_id) ? (int)$request->vehicle_id : null;

        return $obj;
    }
}
