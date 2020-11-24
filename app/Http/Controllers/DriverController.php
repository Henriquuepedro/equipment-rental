<?php

namespace App\Http\Controllers;

use App\Http\Requests\DriverCreatePost;
use App\Http\Requests\DriverDeletePost;
use App\Http\Requests\DriverUpdatePost;
use Illuminate\Http\Request;
use App\Models\Driver;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DriverController extends Controller
{
    public $driver;

    public function __construct(Driver $driver)
    {
        $this->driver = $driver;
    }

    public function index()
    {
        if (!$this->hasPermission('DriverView')) {
            return redirect()->route('dashboard')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        return view('driver.index');
    }

    public function fetchDrivers(Request $request)
    {
        if (!$this->hasPermission('DriverView'))
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

            $fieldsOrder = array('id','name','cpf','phone', '');
            $fieldOrder =  $fieldsOrder[$request->order[0]['column']];
            if ($fieldOrder != "") {
                $orderBy['field'] = $fieldOrder;
                $orderBy['order'] = $direction;
            }
        }

        if (!empty($searchUser)) $filtered = $this->driver->getCountDrivers($company_id, $searchUser);
        else $filtered = 0;


        $data = $this->driver->getDrivers($company_id, $ini, $length, $searchUser, $orderBy);

        // get string query
        // DB::getQueryLog();

        $permissionUpdate = $this->hasPermission('DriverUpdatePost');
        $permissionDelete = $this->hasPermission('DriverDeletePost');

        $i = 0;
        foreach ($data as $key => $value) {
            $i++;
            $buttons = "<a href='".route('driver.edit', ['id' => $value['id']])."' class='btn btn-primary btn-sm btn-rounded btn-action' data-toggle='tooltip'";
            $buttons .= $permissionUpdate ? "title='Editar' ><i class='fas fa-edit'></i></a>" : "title='Visualizar' ><i class='fas fa-eye'></i></a>";
            $buttons .= $permissionDelete ? "<button class='btn btn-danger btnRemoveDriver btn-sm btn-rounded btn-action ml-md-1' data-toggle='tooltip' title='Excluir' driver-id='{$value['id']}'><i class='fas fa-times'></i></button>" : '';

            $result[$key] = array(
                $value['id'],
                $value['name'],
                $value['cpf'] ? $this->mask($value['cpf'], '###.###.###-##') : '',
                $value['phone'] ? $this->mask($value['phone'], strlen($value['phone']) === 10 ? '(##) ####-####' : '(##) #####-####') : '',
                $buttons
            );
        }

        if ($filtered == 0) $filtered = $i;

        $output = array(
            "draw" => $draw,
            "recordsTotal" => $this->driver->getCountDrivers($company_id),
            "recordsFiltered" => $filtered,
            "data" => $result
        );

        return response()->json($output);
    }

    public function create()
    {
        if (!$this->hasPermission('DriverCreatePost')) {
            return redirect()->route('driver.index')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        return view('driver.create');
    }

    public function insert(DriverCreatePost $request)
    {
        // data driver
        $dataDriver = $this->formatDataDriver($request);
        $isAjax = $this->isAjax();

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

            if ($isAjax)
                return response()->json(['success' => true, 'message' => 'Motorista cadastrado com sucesso.', 'driver_id' => $driverId]);

            return redirect()->route('driver.index')
                ->with('success', "Motorista com o código {$driverId}, cadastrado com sucesso!");
        }

        if ($isAjax)
            return response()->json(['success' => false, 'message' => 'Não foi possível cadastrar o motorista, tente novamente!']);

        return redirect()->back()
            ->withErrors(['Não foi possível cadastrar o motorista, tente novamente!'])
            ->withInput();
    }

    public function delete(DriverDeletePost $request)
    {
        $company_id = $request->user()->company_id;
        $driver_id = $request->driver_id;

        if (!$this->driver->getDriver($driver_id, $company_id))
            return response()->json(['success' => false, 'message' => 'Não foi possível localizar o motorista!']);

        if (!$this->driver->remove($driver_id, $company_id))
            return response()->json(['success' => false, 'message' => 'Não foi possível excluir o motorista!']);

        return response()->json(['success' => true, 'message' => 'Motorista excluído com sucesso!']);
    }

    public function edit($id)
    {
        $company_id = Auth::user()->company_id;

        $driver = $this->driver->getDriver($id, $company_id);
        if (!$driver)
            return redirect()->route('driver.index');

        return view('driver.update', compact('driver'));
    }

    public function update(DriverUpdatePost $request)
    {
        // data driver
        $dataDriver = $this->formatDataDriver($request);

        if (!$this->driver->getDriver($dataDriver->driver_id, $dataDriver->company_id))
            return redirect()->back()
                ->withErrors(['Não foi possível localizar o motorista para atualizar!'])
                ->withInput();

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

    private function formatDataDriver($request)
    {
        $obj = new \stdClass;

        $obj->company_id    = $request->user()->company_id;
        $obj->user_id       = $request->user()->id;
        $obj->name          = filter_var($request->name, FILTER_SANITIZE_STRING);
        $obj->email         = $request->email ? (filter_var($request->email, FILTER_VALIDATE_EMAIL) ? $request->email : null) : null;
        $obj->phone         = $request->phone ? filter_var(preg_replace('/[^0-9]/', '', $request->phone), FILTER_SANITIZE_NUMBER_INT) : null;
        $obj->cpf           = $request->cpf ? filter_var(preg_replace('/[^0-9]/', '', $request->cpf), FILTER_SANITIZE_NUMBER_INT) : null;
        $obj->rg            = $request->rg ? filter_var(preg_replace('/[^0-9]/', '', $request->rg), FILTER_SANITIZE_NUMBER_INT) : null;
        $obj->cnh           = $request->cnh ? filter_var(preg_replace('/[^0-9]/', '', $request->cnh), FILTER_SANITIZE_NUMBER_INT) : null;
        $obj->cnh_exp       = $request->cnh_exp;
        $obj->observation   = $request->observation ? filter_var($request->observation, FILTER_SANITIZE_STRING) : null;
        $obj->driver_id     = isset($request->driver_id) ? (int)$request->driver_id : null;

        return $obj;
    }

    public function getDrivers(Request $request)
    {
        $company_id = $request->user()->company_id;
        $driverData = [];
        $lastId = 0;

        $drivers = $this->driver->getDrivers($company_id);

        foreach ($drivers as $driver) {
            array_push($driverData, ['id' => $driver->id, 'name' => $driver->name]);
            if ($driver->id > $lastId) $lastId = $driver->id;
        }

        return response()->json(['data' => $driverData, 'lastId' => $lastId]);
    }
}
