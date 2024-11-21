<?php

namespace App\Http\Controllers;

use App\Http\Requests\DriverCreatePost;
use App\Http\Requests\DriverDeletePost;
use App\Http\Requests\DriverUpdatePost;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\Driver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DriverController extends Controller
{
    public Driver $driver;

    public function __construct()
    {
        $this->driver = new Driver();
    }

    public function index(): Factory|View|RedirectResponse|Application
    {
        if (!hasPermission('DriverView')) {
            return redirect()->route('dashboard')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        return view('driver.index');
    }

    public function fetchDrivers(Request $request): JsonResponse
    {
        $result     = array();
        $draw       = $request->input('draw');
        $company_id = $request->user()->company_id;

        try {
            $filters        = array();
            $filter_default = array();
            $fields_order   = array('id','name','cpf','phone', '');

            $filter_default[]['where']['company_id'] = $company_id;

            $query = array(
                'from' => 'drivers'
            );

            $data = fetchDataTable(
                $query,
                array('name', 'asc'),
                null,
                ['DriverView'],
                $filters,
                $fields_order,
                $filter_default
            );

        } catch (Exception $exception) {
            return response()->json(getErrorDataTables($exception->getMessage(), $draw));
        }

        $permissionUpdate = hasPermission('DriverUpdatePost');
        $permissionDelete = hasPermission('DriverDeletePost');

        foreach ($data['data'] as $value) {
            $result[] = array(
                $value->id,
                $value->name,
                formatCPF_CNPJ($value->cpf),
                formatPhone($value->phone),
                newDropdownButtonsDataList([
                    [
                        'tag'   => 'a',
                        'title' => $permissionUpdate ? 'Atualizar Motorista' : 'Visualizar Motorista',
                        'icon'  => 'fas fa-edit',
                        'href'  => route('driver.edit', ['id' => $value->id])
                    ],
                    [
                        'tag'       => 'button',
                        'title'     => 'Excluir Motorista',
                        'icon'      => 'fas fa-times',
                        'class'     => 'btnRemoveDriver',
                        'attribute' => "driver-id='$value->id'",
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

    public function create(): Factory|View|RedirectResponse|Application
    {
        if (!hasPermission('DriverCreatePost')) {
            return redirect()->route('driver.index')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        return view('driver.create');
    }

    public function insert(DriverCreatePost $request): JsonResponse|RedirectResponse
    {
        // data driver
        $dataDriver = $this->formatDataDriver($request);
        $isAjax = isAjax();

        $createDriver = $this->driver->insert(array(
            'company_id'            => $dataDriver->company_id,
            'name'                  => $dataDriver->name,
            'cpf'                   => $dataDriver->cpf,
            'rg'                    => $dataDriver->rg,
            'cnh'                   => $dataDriver->cnh,
            'cnh_exp'               => $dataDriver->cnh_exp,
            'email'                 => $dataDriver->email,
            'phone'                 => $dataDriver->phone,
            'observation'           => $dataDriver->observation,
            'address_zipcode'       => $dataDriver->address_zipcode,
            'address_name'          => $dataDriver->address_name,
            'address_number'        => $dataDriver->address_number,
            'address_complement'    => $dataDriver->address_complement,
            'address_reference'     => $dataDriver->address_reference,
            'address_neigh'         => $dataDriver->address_neigh,
            'address_city'          => $dataDriver->address_city,
            'address_state'         => $dataDriver->address_state,
            'user_insert'           => $dataDriver->user_id
        ));

        $driverId = $createDriver->id;

        if($createDriver) {
            if ($isAjax) {
                return response()->json(['success' => true, 'message' => 'Motorista cadastrado com sucesso.', 'driver_id' => $driverId]);
            }

            return redirect()->route('driver.index')
                ->with('success', "Motorista com o código {$driverId}, cadastrado com sucesso!");
        }

        if ($isAjax) {
            return response()->json(['success' => false, 'message' => 'Não foi possível cadastrar o motorista, tente novamente!']);
        }

        return redirect()->back()
            ->withErrors(['Não foi possível cadastrar o motorista, tente novamente!'])
            ->withInput();
    }

    public function delete(DriverDeletePost $request): JsonResponse
    {
        $company_id = $request->user()->company_id;
        $driver_id = $request->input('driver_id');

        if (!$this->driver->getDriver($driver_id, $company_id)) {
            return response()->json(['success' => false, 'message' => 'Não foi possível localizar o motorista!']);
        }

        if (!$this->driver->remove($driver_id, $company_id)) {
            return response()->json(['success' => false, 'message' => 'Não foi possível excluir o motorista!']);
        }

        return response()->json(['success' => true, 'message' => 'Motorista excluído com sucesso!']);
    }

    public function edit($id): View|Factory|RedirectResponse|Application
    {
        $company_id = Auth::user()->__get('company_id');

        $driver = $this->driver->getDriver($id, $company_id);
        if (!$driver) {
            return redirect()->route('driver.index');
        }

        return view('driver.update', compact('driver'));
    }

    public function update(DriverUpdatePost $request): RedirectResponse
    {
        // data driver
        $dataDriver = $this->formatDataDriver($request);

        if (!$this->driver->getDriver($dataDriver->driver_id, $dataDriver->company_id)) {
            return redirect()->back()
                ->withErrors(['Não foi possível localizar o motorista para atualizar!'])
                ->withInput();
        }

        $updateDriver = $this->driver->edit(
            array(
                'name'                  => $dataDriver->name,
                'cpf'                   => $dataDriver->cpf,
                'rg'                    => $dataDriver->rg,
                'cnh'                   => $dataDriver->cnh,
                'cnh_exp'               => $dataDriver->cnh_exp,
                'email'                 => $dataDriver->email,
                'phone'                 => $dataDriver->phone,
                'observation'           => $dataDriver->observation,
                'address_zipcode'       => $dataDriver->address_zipcode,
                'address_name'          => $dataDriver->address_name,
                'address_number'        => $dataDriver->address_number,
                'address_complement'    => $dataDriver->address_complement,
                'address_reference'     => $dataDriver->address_reference,
                'address_neigh'         => $dataDriver->address_neigh,
                'address_city'          => $dataDriver->address_city,
                'address_state'         => $dataDriver->address_state,
                'user_update'           => $dataDriver->user_id
            ),
            $dataDriver->driver_id
        );

        if($updateDriver) {
            DB::commit();
            return redirect()->route('driver.index')
                ->with('success', "Motorista com o código {$dataDriver->driver_id}, alterado com sucesso!");
        }

        DB::rollBack();
        return redirect()->back()
            ->withErrors(['Não foi possível alterar o motorista, tente novamente!'])
            ->withInput();
    }

    private function formatDataDriver(DriverCreatePost|DriverUpdatePost $request): \stdClass
    {
        $obj = new \stdClass;

        $obj->company_id            = $request->user()->company_id;
        $obj->user_id               = $request->user()->id;
        $obj->name                  = filter_var($request->input('name'));
        $obj->email                 = $request->input('email') ? (filter_var($request->input('email'), FILTER_VALIDATE_EMAIL) ? $request->input('email') : null) : null;
        $obj->phone                 = $request->input('phone') ? filter_var(onlyNumbers($request->input('phone')), FILTER_SANITIZE_NUMBER_INT) : null;
        $obj->cpf                   = $request->input('cpf') ? filter_var(onlyNumbers($request->input('cpf')), FILTER_SANITIZE_NUMBER_INT) : null;
        $obj->rg                    = $request->input('rg') ? filter_var(onlyNumbers($request->input('rg')), FILTER_SANITIZE_NUMBER_INT) : null;
        $obj->cnh                   = $request->input('cnh') ? filter_var(onlyNumbers($request->input('cnh')), FILTER_SANITIZE_NUMBER_INT) : null;
        $obj->cnh_exp               = $request->input('cnh_exp');
        $obj->observation           = $request->input('observation') ? filter_var($request->input('observation')) : null;
        $obj->address_zipcode       = filter_var(onlyNumbers($request->input('address_zipcode')), FILTER_FLAG_EMPTY_STRING_NULL) ?: null;
        $obj->address_name          = filter_var($request->input('address_name'), FILTER_FLAG_EMPTY_STRING_NULL) ?: null;
        $obj->address_number        = filter_var($request->input('address_number'), FILTER_FLAG_EMPTY_STRING_NULL) ?: null;
        $obj->address_complement    = filter_var($request->input('address_complement'), FILTER_FLAG_EMPTY_STRING_NULL) ?: null;
        $obj->address_reference     = filter_var($request->input('address_reference'), FILTER_FLAG_EMPTY_STRING_NULL) ?: null;
        $obj->address_neigh         = filter_var($request->input('address_neigh'), FILTER_FLAG_EMPTY_STRING_NULL) ?: null;
        $obj->address_city          = filter_var($request->input('address_city'), FILTER_FLAG_EMPTY_STRING_NULL) ?: null;
        $obj->address_state         = filter_var($request->input('address_state'), FILTER_FLAG_EMPTY_STRING_NULL) ?: null;
        $obj->driver_id             = $request->input('driver_id') ? (int)$request->input('driver_id') : null;

        return $obj;
    }

    public function getDrivers(): JsonResponse
    {
        $company_id = Auth::user()->__get('company_id');
        $driverData = [];
        $lastId = 0;

        $drivers = $this->driver->getDrivers($company_id);

        foreach ($drivers as $driver) {
            $driverData[] = ['id' => $driver->id, 'name' => $driver->name];
            if ($driver->id > $lastId) {
                $lastId = $driver->id;
            }
        }

        return response()->json(['data' => $driverData, 'lastId' => $lastId]);
    }

    public function get(int $id): JsonResponse
    {
        $company_id = Auth::user()->__get('company_id');
        $driver = $this->driver->getDriver($id, $company_id);

        return response()->json($driver ? array(
            "name"          => $driver->name,
            "cpf"           => $driver->cpf,
            "rg"            => $driver->rg,
            "cnh"           => $driver->cnh,
            "cnh_exp"       => $driver->cnh_exp,
            "email"         => $driver->email,
            "phone"         => $driver->phone,
            "observation"   => $driver->observation,
        ): array());
    }
}
