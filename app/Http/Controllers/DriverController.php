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
            return response()->json(array(
                    "draw"              => $draw,
                    "recordsTotal"      => 0,
                    "recordsFiltered"   => 0,
                    "data"              => $result,
                    "message"           => $exception->getMessage()
                )
            );
        }

        $permissionUpdate = hasPermission('DriverUpdatePost');
        $permissionDelete = hasPermission('DriverDeletePost');

        foreach ($data['data'] as $value) {
            $buttons = "<a href='".route('driver.edit', ['id' => $value->id])."' class='btn btn-primary btn-sm btn-rounded btn-action' data-toggle='tooltip'";
            $buttons .= $permissionUpdate ? "title='Atualizar' ><i class='fas fa-edit'></i></a>" : "title='Visualizar' ><i class='fas fa-eye'></i></a>";
            $buttons .= $permissionDelete ? "<button class='btn btn-danger btnRemoveDriver btn-sm btn-rounded btn-action ml-md-1' data-toggle='tooltip' title='Excluir' driver-id='{$value->id}'><i class='fas fa-times'></i></button>" : '';

            $result[] = array(
                $value->id,
                $value->name,
                $value->cpf ? mask($value->cpf, '###.###.###-##') : '',
                $value->phone ? mask($value->phone, strlen($value->phone) === 10 ? '(##) ####-####' : '(##) #####-####') : '',
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
            'company_id'    => $dataDriver->company_id,
            'name'          => $dataDriver->name,
            'cpf'           => $dataDriver->cpf,
            'rg'            => $dataDriver->rg,
            'cnh'           => $dataDriver->cnh,
            'cnh_exp'       => $dataDriver->cnh_exp,
            'email'         => $dataDriver->email,
            'phone'         => $dataDriver->phone,
            'observation'   => $dataDriver->observation,
            'user_insert'   => $dataDriver->user_id
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
                'name'          => $dataDriver->name,
                'cpf'           => $dataDriver->cpf,
                'rg'            => $dataDriver->rg,
                'cnh'           => $dataDriver->cnh,
                'cnh_exp'       => $dataDriver->cnh_exp,
                'email'         => $dataDriver->email,
                'phone'         => $dataDriver->phone,
                'observation'   => $dataDriver->observation,
                'user_update'   => $dataDriver->user_id
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

        $obj->company_id    = $request->user()->company_id;
        $obj->user_id       = $request->user()->id;
        $obj->name          = filter_var($request->input('name'));
        $obj->email         = $request->input('email') ? (filter_var($request->input('email'), FILTER_VALIDATE_EMAIL) ? $request->input('email') : null) : null;
        $obj->phone         = $request->input('phone') ? filter_var(onlyNumbers($request->input('phone')), FILTER_SANITIZE_NUMBER_INT) : null;
        $obj->cpf           = $request->input('cpf') ? filter_var(onlyNumbers($request->input('cpf')), FILTER_SANITIZE_NUMBER_INT) : null;
        $obj->rg            = $request->input('rg') ? filter_var(onlyNumbers($request->input('rg')), FILTER_SANITIZE_NUMBER_INT) : null;
        $obj->cnh           = $request->input('cnh') ? filter_var(onlyNumbers($request->input('cnh')), FILTER_SANITIZE_NUMBER_INT) : null;
        $obj->cnh_exp       = $request->input('cnh_exp');
        $obj->observation   = $request->input('observation') ? filter_var($request->input('observation')) : null;
        $obj->driver_id     = $request->input('driver_id') ? (int)$request->input('driver_id') : null;

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
