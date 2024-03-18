<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\UserCreatePost;
use App\Http\Requests\Master\UserUpdatePost;
use App\Models\Company;
use App\Models\Permission;
use App\Models\User;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    private User $user;
    private Permission $permission;
    private Company $company;

    public function __construct()
    {
        $this->user = new User();
        $this->permission = new Permission();
        $this->company = new Company();
    }

    public function index(): Factory|View|Application
    {
        return view('master.user.index');
    }

    public function edit(int $id): View|Factory|RedirectResponse|Application
    {
        $user            = $this->user->getUserById($id);
        $htmlPermissions = getFormPermission($this->permission->getAllPermissions(), json_decode($user->permission ?? ''));

        if (!$user) {
            return redirect()->route('master.user.index');
        }

        $companies = $this->company->getAllCompanies();

        return view('master.user.update', compact('user', 'htmlPermissions', 'companies'));
    }

    public function create(): View|Factory|RedirectResponse|Application
    {
        $htmlPermissions = getFormPermission($this->permission->getAllPermissions());
        $companies = $this->company->getAllCompanies();

        return view('master.user.update', compact('htmlPermissions', 'companies'));
    }

    public function fetch(Request $request): JsonResponse
    {
        $result     = array();
        $draw       = $request->input('draw');

        try {
            // Filtro status
            $active = $request->input('active');

            $filters        = array();
            $filter_default = array();
            $fields_order   = array('name', 'email', 'phone', 'active', 'type_user', 'last_access_at', 'created_at', '');

            if (!is_null($active) && $active !== 'all') {
                $filters[]['where']['active'] = $active;
            }

            $query = array(
                'from' => 'users'
            );

            $data = fetchDataTable(
                $query,
                array('created_at', 'desc'),
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
            $buttons = "<a href='".route('master.user.edit', ['id' => $value->id])."' class='dropdown-item' data-rental-id='$value->id'><i class='fas fa-edit'></i> Atualizar Usuário</a>";
            $buttons = dropdownButtonsDataList($buttons, $value->id);

            $result[] = array(
                $value->name,
                $value->email,
                formatPhone($value->phone),
                $value->active ? '<div class="badge badge-pill badge-lg badge-success">Ativo</div>' : '<div class="badge badge-pill badge-lg badge-danger">Inativo</div>',
                $value->type_user == 0 ? 'Usuário' : ($value->type_user == 1 ? 'Administrador' : 'Administrador Master'),
                dateInternationalToDateBrazil($value->last_access_at),
                dateInternationalToDateBrazil($value->created_at, DATETIME_BRAZIL_NO_SECONDS),
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

    public function update(UserUpdatePost $request, int $id): RedirectResponse
    {
        $arrPermissions = array_map(function($permission) {
            return (int)$permission;
        }, $request->input('permission', []));

        $update = $this->user->updateById([
            'name'          => $request->input('name'),
            'email'         => $request->input('email'),
            'phone'         => onlyNumbers($request->input('phone')),
            'permission'    => json_encode($arrPermissions),
            'type_user'     => $request->input('type_user'),
            'active'        => $request->input('active') ? 1 : 0
        ], $id);

        if (!$update) {
            return redirect()->back()
                ->withErrors(['Não foi possível atualizar o usuário, tente novamente!'])
                ->withInput();
        }

        return redirect()->route('master.user.index')
            ->with('success', "Usuário atualizado com sucesso!");
    }

    public function insert(UserCreatePost $request): RedirectResponse
    {
        $arrPermissions = array_map(function($permission) {
            return (int)$permission;
        }, $request->input('permission'));

        $create = $this->user->insert([
            'name'          => $request->input('name'),
            'email'         => $request->input('email'),
            'phone'         => onlyNumbers($request->input('phone')),
            'permission'    => json_encode($arrPermissions),
            'password'      => Hash::make($request->input('password')),
            'company_id'    => $request->input('company'),
            'type_user'     => $request->input('type_user'),
            'active'        => $request->input('active') ? 1 : 0
        ]);

        if (!$create) {
            return redirect()->back()
                ->withErrors(['Não foi possível cadastrar o usuário, tente novamente!'])
                ->withInput();
        }

        return redirect()->route('master.user.index')
            ->with('success', "Usuário cadastrar com sucesso!");
    }
}
