<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdatePost;
use App\Http\Requests\UserCreatePost;
use App\Http\Requests\UserDeletePost;
use App\Models\Company;
use App\Models\Permission;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image as ImageUpload;
use Intervention\Image\Image;

class UserController extends Controller
{
    private User $user;
    private Permission $permission;
    private Plan $plan;
    private Company $company;

    public function __construct()
    {
        $this->user = new User();
        $this->permission = new Permission();
        $this->plan = new Plan();
        $this->company = new Company();
    }

    public function profile(): Factory|View|Application
    {
        $user_id    = Auth::user()->__get('id');
        $company_id = Auth::user()->__get('company_id');

        $user = $this->user->getUser($user_id, $company_id);

        return view('profile.index', compact('user'));
    }

    public function expiredPlan(): Factory|View|Application
    {
        $user_id    = Auth::user()->__get('id');
        $company_id = Auth::user()->__get('company_id');

        $user = $this->user->getUser($user_id, $company_id);

        return view('profile.expired_plan', compact('user'));
    }

    public function update(ProfileUpdatePost $request)
    {
        $user_id        = $request->user()->id;
        $company_id     = $request->user()->company_id;
        $isAjax         = isAjax();

        if ($isAjax) {
            $user_id_session = $user_id;
            $user_id = $request->input('user_id');

            if (!hasAdmin()) {
                return response()->json(['success' => false, 'data' => 'Você não tem permissão para fazer essa operação!']);
            }

            if ($user_id == $user_id_session) {
                return response()->json(['success' => false, 'data' => "Não é possível atualizar o cadastro do próprio usuário!"]);
            }


            $user = $this->user->getUser($user_id, $company_id);

            if (!$user) {
                return response()->json(['success' => false, 'data' => "Não foi possível encontrar o usuário para realizar a atualização do cadastro!"]);
            }

            if ($user->type_user == 2) {
                return response()->json(['success' => false, 'data' => "Não é possível atualizar o cadastro de um administrador master!"]);
            }

        }

        // verifica senha atual
        if ($request->input('password')) {
            if (!Hash::check($request->input('password_current'), auth()->user()->__get('password'))) {
                if ($isAjax) {
                    return response()->json(['success' => false, 'data' => 'Senha informada não corresponde com a senha atual!']);
                }

                return redirect()
                    ->route('profile.index')
                    ->withErrors(['Senha informada não corresponde com a senha atual!'])
                    ->withInput();
            }
        }

        $dataUserUpdate = [
            'name'  => filter_var($request->input('name'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL),
            'phone' => filter_var(onlyNumbers($request->input('phone')), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL),
            'style_template'  => $request->input('style_template')
        ];

        if ($request->input('password')) {
            $dataUserUpdate['password'] = Hash::make($request->input('password'));
        }

        if ($isAjax && $user->email != $request->input('email')) {
            $dataUserUpdate['email'] = $request->input('email') ? filter_var($request->input('email'), FILTER_VALIDATE_EMAIL) : null;

            if (!$dataUserUpdate['email']) {
                return response()->json(['success' => false, 'data' => 'Informe um endereço de e-mail válido!']);
            }
        }

        if ($this->user->getUserNotUpdate($dataUserUpdate, $user_id, $company_id)) {
            if ($isAjax) {
                return response()->json(['success' => true, 'data' => 'Usuário atualizado com sucesso!']);
            }

            return redirect()->route('profile.index')
                ->with('success', 'Perfil atualizado com sucesso!');
        }

        $update = $this->user->edit($dataUserUpdate, $user_id, $company_id);

        if ($update) {
            if ($isAjax) {
                return response()->json(['success' => true, 'data' => 'Usuário atualizado com sucesso!']);
            }

            return redirect()->route('profile.index')
                ->with('success', 'Perfil atualizado com sucesso!');
        }

        if ($isAjax) {
            return response()->json(['success' => false, 'data' => 'Não foi possível atualizar o usuário, tente mais tarde!']);
        }

        return redirect()->route('profile.index')
            ->withErrors(['Não foi possível atualizar o perfil. Tente novamente']);
    }

    public function updateImage(Request $request): JsonResponse
    {
        $validator = Validator::make($request->file(),
            [
                'image' => 'required|image|mimes:jpg,png,jpeg|max:4096',
            ], [
                'image.required'    => 'Imagem é obrigatório.',
                'image.image'       => 'O arquivo deve ser uma imagem.',
                'image.mimes'       => 'São aceitos os tipos jpg, png e jpeg.',
                'image.max'         => 'O tamanho máximo é de 4mb.'
            ]
        );

        if ($validator->fails()) {
            return response()->json(array(
                'success' => false,
                'message' => implode('<br>',  $validator->errors()->all())
            ));
        }

        $file = $request->file('image');
        $user_id    = $request->user()->id;
        $company_id = $request->user()->company_id;

        $uploadPath = "assets/images/profile/$user_id"; // Faz o upload para o caminho 'admin/dist/images/autos/{ID}/'
        checkPathExistToCreate($uploadPath);

        $extension = $file->getClientOriginalExtension(); // Recupera extensão da imagem
        $nameOriginal = $file->getClientOriginalName(); // Recupera nome da imagem
        $imageName = base64_encode($nameOriginal); // Gera um novo nome para a imagem.
        $imageName = substr($imageName, 0, 15) . rand(0, 100) . ".$extension"; // Pega apenas o 15 primeiros e adiciona a extensão

        if ($file->move($uploadPath, $imageName)) {
            if ($this->resizeImageProfile($uploadPath, $imageName)) {
                $update = $this->user->edit(['profile' => $imageName], $user_id, $company_id);
                if ($update) {
                    return response()->json(['success' => true, 'path' => asset("$uploadPath/$imageName")]);
                } else {
                    return response()->json(['success' => false, 'message' => 'Não foi possível salvar a imagem. Se o erro persistir tentar com outra imagem.']);
                }
            } else {
                return response()->json(['success' => false, 'message' => 'Não foi possível salvar a imagem. Se o erro persistir tentar com outra imagem.']);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'Não foi possível salvar a imagem. Se o erro persistir tentar com outra imagem.']);
        }
    }

    private function resizeImageProfile($uploadPath, $imageName): Image
    {
        return ImageUpload::make("$uploadPath/$imageName")
            ->resize(100, 100)
            ->save("$uploadPath/$imageName");
    }

    public function newUser(UserCreatePost $request): JsonResponse
    {
        $company_id     = Auth::user()->__get('company_id');
        $name           = filter_var($request->input('name_modal'));
        $phone          = $request->input('phone_modal') ? filter_var(onlyNumbers($request->input('phone_modal')), FILTER_SANITIZE_NUMBER_INT) : null;
        $email          = filter_var($request->input('email_modal'), FILTER_VALIDATE_EMAIL);
        $password       = Hash::make($request->input('password_modal'));
        $allowed_users  = 1;

        $data_company = $this->company->getCompany($company_id);
        if ($data_company->plan_id) {
            $data_plan = $this->plan->getById($data_company->plan_id);
            $allowed_users = $data_plan->allowed_users;
        }

        // quando $allowed_users é null, usuário pode cadastrar usuários ilimitados.
        if (!is_null($allowed_users) && count($this->user->getUsersCompany($company_id)) >= $allowed_users) {
            return response()->json(['success' => false, 'message' => "Limite de usuários atingidos. Seu plano permite cadastrar somente $allowed_users usuários."]);
        }

        $permissions = array_map(function($permission) {
            return (int)$permission;
        }, $request->input('permission') ?? []);

        if (!$email) {
            return response()->json(['success' => false, 'message' => 'Endereço de e-mail inválido.']);
        }

        $create = $this->user->insert([
            'name'          => $name,
            'email'         => $email,
            'phone'         => $phone,
            'password'      => $password,
            'permission'    => json_encode($permissions),
            'company_id'    => $company_id
        ]);

        if (!$create) {
            return response()->json(['success' => false, 'message' => 'Não foi possível criar o usuário.']);
        }

        return response()->json(['success' => true, 'message' => 'Usuário criado com sucesso.']);
    }

    public function inactivateUser(Request $request): JsonResponse
    {
        $user_id            = $request->user()->id;
        $company_id         = $request->user()->company_id;
        $user_inactive      = $request->input('user_id');
        $dataUserInactive   = $this->user->getUser($user_inactive, $company_id);

        $nameStatusError    = $dataUserInactive->active == 1 ? 'inativar' : 'ativar';
        $nameStatusSuccess  = $dataUserInactive->active == 1 ? 'inativado' : 'ativado';

        if (!hasAdmin()) {
            return response()->json(['success' => false, 'message' => "Você não tem permissão para $nameStatusError um usuário"]);
        }

        if (!$dataUserInactive) {
            return response()->json(['success' => false, 'message' => "Não foi possível encontrar o usuário para $nameStatusError o usuário"]);
        }

        if ($dataUserInactive['type_user'] == 2) {
            return response()->json(['success' => false, 'message' => "Não é possível $nameStatusError o usuário Admin-Master"]);
        }

        if ($user_id == $user_inactive) {
            return response()->json(['success' => false, 'message' => "Não é possível $nameStatusError o próprio usuário"]);
        }

        if ($dataUserInactive->company_id != $company_id) {
            return response()->json(['success' => false, 'message' => "Não foi possível encontrar o usuário"]);
        }

        // verificar permissão para inativar
        $update = $this->user->edit([
            'active' => !$dataUserInactive->active
        ], $user_inactive, $company_id);

        if (!$update) {
            return response()->json(['success' => false, 'message' => "Não foi possível $nameStatusError o usuário"]);
        }

        return response()->json(['success' => true, 'message' => "Usuário $nameStatusSuccess com sucesso!"]);
    }

    public function getUsers(): JsonResponse
    {
        if (!hasAdmin()) {
            return response()->json();
        }

        $dataUsers  = [];
        $company_id = Auth::user()->__get('company_id');
        $user_id    = Auth::user()->__get('id');
        $users      = $this->user->getUsersCompany($company_id);

        foreach ($users as $user) {
            $dataUsers [] = [
                'type_user'     => $user['type_user'],
                'id'            => $user['id'],
                'name'          => $user['name'],
                'email'         => $user['email'],
                'phone'         => $user['phone'],
                'active'        => $user['active'],
                'image'         => asset($user['profile'] ? "assets/images/profile/{$user['id']}/{$user['profile']}" : "assets/images/system/profile.png"),
                'last_access'   => $user['last_access_at'] ? date(DATETIME_BRAZIL_NO_SECONDS, strtotime($user['last_access_at'])) : 'Sem registro',
                'updated_at'    => $user['updated_at'] ? date(DATETIME_BRAZIL_NO_SECONDS, strtotime($user['updated_at'])) : 'Sem registro',
                'last_login'    => $user['last_login_at'] ? date(DATETIME_BRAZIL_NO_SECONDS, strtotime($user['last_login_at'])) : 'Sem registro',
                'user_id_session'   => $user_id
            ];
        }

        return response()->json($dataUsers);
    }

    public function getPermissionsUsers(Request $request): JsonResponse
    {
        if (!hasAdmin()) {
            return response()->json(['success' => false, 'data' => '<h4>Você não tem permissão para fazer essa operação!</h4>']);
        }

        $user_id    = $request->input('user_id');
        $company_id = $request->user()->company_id;

        $user = $this->user->getPermissionUser($user_id, $company_id);

        if (!$user || $user->type_user !== 0) { // não encontrou o usuário ou não é type_user=0
            return response()->json(['success' => false, 'data' => '<h4>Não foi possível localizar as permissões do usuário, tente mais tarde!</h4>']);
        }

        $permissionUser  = empty($user->permission)  ? [] : json_decode($user->permission);
        $htmlPermissions = getFormPermission($this->permission->getAllPermissions(), $permissionUser);

        return response()->json(['success' => true, 'data' => $htmlPermissions]);
    }

    public function updatePermissionsUsers(Request $request): JsonResponse
    {
        if (!hasAdmin()) {
            return response()->json(['success' => false, 'message' => 'Você não tem permissão para fazer essa operação!']);
        }

        $arrPermissions = array_map(function($permission) {
            return (int)$permission;
        }, $request->input('permission') ?? []);

        $company_id = $request->user()->company_id;
        $user_id    = $request->input('user_id');
        $getUser    = $this->user->getUser($user_id, $company_id);
        if (!$getUser || $getUser->type_user !== 0) {
            return response()->json(['success' => false, 'message' => 'Usuário não encontrado, tente mais tarde!']);
        }

        $update = $this->user->edit(['permission' => json_encode($arrPermissions)], $user_id, $company_id);

        if (!$update) {
            return response()->json(['success' => false, 'message' => 'Não foi possível realizar a alteração de permissão, tente mais tarde!']);
        }

        return response()->json(['success' => true, 'message' => 'Permissões de usuário atualizada com sucesso!']);
    }

    public function changeTypeUser(Request $request): JsonResponse
    {
        $company_id = $request->user()->company_id;
        $user_id    = $request->input('user_id');

        if (!hasAdmin()) {
            return response()->json(['success' => false, 'message' => 'Você não tem permissão para fazer essa operação!']);
        }

        $user = $this->user->getUser($user_id, $company_id);

        if (!$user) {
            return response()->json(['success' => false, 'message' => "Não foi possível encontrar o usuário para realizar a operação!"]);
        }

        if ($user->type_user != 0 && $user->type_user != 1) {
            return response()->json(['success' => false, 'message' => "Não foi possível alterar o tipo de usuário, tente mais tarde!"]);
        }

        $newTypeUser = $user->type_user === 0 ? 1 : 0;

        $update = $this->user->edit(['type_user' => $newTypeUser, 'permission' => '[]'], $user_id, $company_id);

        if (!$update) {
            return response()->json(['success' => false, 'message' => 'Não foi possível realizar a alteração do usuário, tente mais tarde!']);
        }

        return response()->json(['success' => true, 'message' => 'Tipo de usuário alterado com sucesso!']);
    }

    public function deleteUser(UserDeletePost $request): JsonResponse
    {
        return response()->json(['success' => false, 'message' => 'Você não tem permissão para fazer essa operação!']);
        $company_id     = $request->user()->company_id;
        $user_id_session= $request->user()->id;
        $user_id        = $request->input('user_id');

        if (!hasAdmin()) {
            return response()->json(['success' => false, 'message' => 'Você não tem permissão para fazer essa operação!']);
        }

        if ($user_id == $user_id_session) {
            return response()->json(['success' => false, 'message' => "Não é possível excluir o próprio usuário!"]);
        }


        $user = $this->user->getUser($user_id, $company_id);

        if (!$user) {
            return response()->json(['success' => false, 'message' => "Não foi possível encontrar o usuário para realizar a operação!"]);
        }

        if ($user->type_user == 2) {
            return response()->json(['success' => false, 'message' => "Não é possível excluir um administrador master!"]);
        }

        $delete = $this->user->remove($user_id, $company_id);

        if (!$delete) {
            return response()->json(['success' => false, 'message' => 'Não foi possível excluir o usuário, tente mais tarde!']);
        }

        return response()->json(['success' => true, 'message' => 'Usuário excluído com sucesso!']);
    }

    public function getUser(Request $request): JsonResponse
    {
        $company_id     = $request->user()->company_id;
        $user_id_session= $request->user()->id;
        $user_id        = $request->input('user_id');

        if (!hasAdmin()) {
            return response()->json(['success' => false, 'data' => 'Você não tem permissão para fazer essa operação!']);
        }

        if ($user_id == $user_id_session) {
            return response()->json(['success' => false, 'data' => "Não é possível atualizar o cadastro do próprio usuário!"]);
        }

        $user   = $this->user->getUser($user_id, $company_id);

        if (!$user) {
            return response()->json(['success' => false, 'data' => "Não foi possível encontrar o usuário para realizar a atualização do cadastro!"]);
        }

        if ($user->type_user == 2) {
            return response()->json(['success' => false, 'data' => "Não é possível atualizar o cadastro de um administrador master!"]);
        }

        $dataUsers = [
            'name'  => $user->name,
            'email' => $user->email,
            'phone' => $user->phone
        ];

        return response()->json(['success' => true, 'data' => $dataUsers]);
    }
}
