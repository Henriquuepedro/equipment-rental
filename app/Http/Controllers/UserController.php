<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdatePost;
use App\Http\Requests\UserCreatePost;
use App\Http\Requests\UserDeletePost;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\Facades\Image as ImageUpload;

class UserController extends Controller
{
    private $user;
    private $permission;

    public function __construct(User $user, Permission $permission)
    {
        $this->user = $user;
        $this->permission = $permission;
    }

    public function profile()
    {
        $user_id    = Auth::user()->id;
        $company_id = Auth::user()->company_id;

        $user = $this->user->getUser($user_id, $company_id);

        return view('profile.index', compact('user'));
    }

    public function update(ProfileUpdatePost $request)
    {
        $user_id        = $request->user()->id;
        $company_id     = $request->user()->company_id;
        $isAjax         = isAjax();

        if ($isAjax) {

            $user_id_session = $user_id;
            $user_id = $request->user_id;

            if (!hasAdmin())
                return response()->json(['success' => false, 'data' => 'Você não tem permissão para fazer essa operação!']);

            if ($user_id == $user_id_session)
                return response()->json(['success' => false, 'data' => "Não é possível atualizar o cadastro do próprio usuário!"]);


            $user = $this->user->getUser($user_id, $company_id);

            if (!$user)
                return response()->json(['success' => false, 'data' => "Não foi possível encontrar o usuário para realizar a atualização do cadastro!"]);

            if ($user->type_user == 2)
                return response()->json(['success' => false, 'data' => "Não é possível atualizar o cadastro de um administrador master!"]);

        }

        // verifica senha atual
        if($request->password) {
            if(!Hash::check($request->password_current, auth()->user()->password)) {
                if ($isAjax)
                    return response()->json(['success' => false, 'data' => 'Senha informada não corresponde com a senha atual!']);

                return redirect()
                    ->route('profile.index')
                    ->withErrors(['Senha informada não corresponde com a senha atual!'])
                    ->withInput();
            }
        }

        $dataUserUpdate = [
            'name'  => $request->name ? filter_var($request->name, FILTER_SANITIZE_STRING) : null,
            'phone' => $request->phone ? filter_var(preg_replace('/[^0-9]/', '', $request->phone), FILTER_SANITIZE_NUMBER_INT) : null
        ];

        if($request->password)
            $dataUserUpdate['password'] = Hash::make($request->password);

        if ($isAjax && $user->email != $request->email) {
            $dataUserUpdate['email'] = $request->email ? filter_var($request->email, FILTER_VALIDATE_EMAIL) : null;

            if (!$dataUserUpdate['email'])
                return response()->json(['success' => false, 'data' => 'Informe um endereço de e-mail válido!']);
        }

        if ($this->user->getUserNotUpdate($dataUserUpdate, $user_id, $company_id)) {
            if ($isAjax)
                return response()->json(['success' => true, 'data' => 'Usuário atualizado com sucesso!']);

            return redirect()->route('profile.index')
                ->with('success', 'Perfil atualizado com sucesso!');
        }

        $update = $this->user->edit($dataUserUpdate, $user_id, $company_id);

        if($update) {
            if ($isAjax)
                return response()->json(['success' => true, 'data' => 'Usuário atualizado com sucesso!']);

            return redirect()->route('profile.index')
                ->with('success', 'Perfil atualizado com sucesso!');
        }

        if ($isAjax)
            return response()->json(['success' => false, 'data' => 'Não foi possível atualizar o usuário, tente mais tarde!']);

        return redirect()->route('profile.index')
            ->withErrors(['Não foi possível atualizar o perfil. Tente novamente']);
    }

    public function updateImage(Request $request)
    {
        $file = $request->file('image');
        $user_id    = $request->user()->id;
        $company_id = $request->user()->company_id;

        $uploadPath = "assets/images/profile/{$user_id}"; // Faz o upload para o caminho 'admin/dist/images/autos/{ID}/'
        $extension = $file->getClientOriginalExtension(); // Recupera extensão da imagem
        $nameOriginal = $file->getClientOriginalName(); // Recupera nome da imagem
        $imageName = base64_encode($nameOriginal); // Gera um novo nome para a imagem.
        $imageName = substr($imageName, 0, 15) . rand(0, 100) . ".$extension"; // Pega apenas o 15 primeiros e adiciona a extensão

        if ($file->move($uploadPath, $imageName)) {
            if ($this->resizeImageProfile($uploadPath, $imageName)) {
                $update = $this->user->edit(['profile' => $imageName], $user_id, $company_id);
                if ($update)
                    return response()->json(['success' => true, 'path' => asset("{$uploadPath}/{$imageName}")]);
                else
                    return response()->json(['success' => false, 'message' => 'Não foi possível salvar a imagem. Se o erro persistir tentar com outra imagem.']);
            } else {
                return response()->json(['success' => false, 'message' => 'Não foi possível salvar a imagem. Se o erro persistir tentar com outra imagem.']);
            }
        } else {
            return response()->json(['success' => false, 'message' => 'Não foi possível salvar a imagem. Se o erro persistir tentar com outra imagem.']);
        }
    }

    private function resizeImageProfile($uploadPath, $imageName)
    {
        return ImageUpload::make("{$uploadPath}/{$imageName}")
            ->resize(100, 100)
            ->save("{$uploadPath}/{$imageName}");
    }

    public function newUser(UserCreatePost $request)
    {
        $company_id = Auth::user()->company_id;
        $name       = filter_var($request->name_modal, FILTER_SANITIZE_STRING);
        $phone      = $request->phone_modal ? filter_var(preg_replace('/[^0-9]/', '', $request->phone_modal), FILTER_SANITIZE_NUMBER_INT) : null;
        $email      = filter_var($request->email_modal, FILTER_VALIDATE_EMAIL);
        $password   = Hash::make($request->password_modal);

        $permissions = [];
        // percorre os dados enviados
        foreach ($request->all() as $namePer => $value) {
            if (!preg_match('/newuser_/', $namePer)) continue;

            $getPermission = $this->permission->getPermissionByName(str_replace('newuser_', '', $namePer)); // recupera o ID da permissão

            if($getPermission) array_push($permissions, $getPermission->id);
        }

        if (!$email)
            return response()->json(['success' => false, 'message' => 'Endereço de e-mail inválido.']);

        $create = $this->user->insert([
            'name'          => $name,
            'email'         => $email,
            'phone'         => $phone,
            'password'      => $password,
            'permission'    => json_encode($permissions),
            'company_id'    => $company_id
        ]);

        if (!$create)
            return response()->json(['success' => false, 'message' => 'Não foi possível criar o usuário.']);

        return response()->json(['success' => true, 'message' => 'Usuário criado com sucesso.']);
    }

    public function inactivateUser(Request $request)
    {
        $user_id            = $request->user()->id;
        $company_id         = $request->user()->company_id;
        $user_inactive      = $request->user_id;
        $dataUserInactive   = $this->user->getUser($user_inactive, $company_id);

        $nameStatusError    = $dataUserInactive->active == 1 ? 'inativar' : 'ativar';
        $nameStatusSuccess  = $dataUserInactive->active == 1 ? 'inativado' : 'ativado';

        if (!hasAdmin())
            return response()->json(['success' => false, 'message' => "Você não tem permissão para {$nameStatusError} um usuário"]);

        if (!$dataUserInactive)
            return response()->json(['success' => false, 'message' => "Não foi possível encontrar o usuário para {$nameStatusError} o usuário"]);

        if ($dataUserInactive['type_user'] == 2)
            return response()->json(['success' => false, 'message' => "Não é possível {$nameStatusError} o usuário Admin-Master"]);

        if ($user_id == $user_inactive)
            return response()->json(['success' => false, 'message' => "Não é possível {$nameStatusError} o próprio usuário"]);

        if ($dataUserInactive->company_id != $company_id)
            return response()->json(['success' => false, 'message' => "Não foi possível encontrar o usuário"]);

        // verificar permissão para inativar

        $update = $this->user->edit([
            'active' => !$dataUserInactive->active
        ], $user_inactive, $company_id);

        if (!$update)
            return response()->json(['success' => false, 'message' => "Não foi possível {$nameStatusError} o usuário"]);

        return response()->json(['success' => true, 'message' => "Usuário {$nameStatusSuccess} com sucesso!"]);
    }

    public function getUsers()
    {
        if (!hasAdmin())
            return response()->json([]);

        $dataUsers = [];
        $company_id = Auth::user()->company_id;
        $user_id = Auth::user()->id;

        $users   = $this->user->getUsersCompany($company_id);

        foreach ($users as $user) {
            array_push($dataUsers, [
                'type_user'     => $user['type_user'],
                'id'            => $user['id'],
                'name'          => $user['name'],
                'email'         => $user['email'],
                'phone'         => $user['phone'],
                'active'        => $user['active'],
                'image'         => asset($user['profile'] ? "assets/images/profile/{$user['id']}/{$user['profile']}" : "assets/images/profile/profile.png"),
                'last_access'   => $user['last_access_at'] ? date('d/m/Y H:i', strtotime($user['last_access_at'])) : 'Sem registro',
                'updated_at'    => $user['updated_at'] ? date('d/m/Y H:i', strtotime($user['updated_at'])) : 'Sem registro',
                'last_login'    => $user['last_login_at'] ? date('d/m/Y H:i', strtotime($user['last_login_at'])) : 'Sem registro',
                'user_id_session'   => $user_id
            ]);
        }

        return response()->json($dataUsers);
    }

    public function getPermissionsUsers(Request $request)
    {
        if (!hasAdmin())
            return response()->json(['success' => false, 'data' => '<h4>Você não tem permissão para fazer essa operação!</h4>']);

        $user_id    = $request->user_id;
        $company_id = $request->user()->company_id;

        $user = $this->user->getPermissionUser($user_id, $company_id);

        if (!$user || $user->type_user !== 0) // não encontrou o usuário ou não é type_user=0
            return response()->json(['success' => false, 'data' => '<h4>Não foi possível localizar as permissões do usuário, tente mais tarde!</h4>']);

        $permissionUser     = empty($user->permission)  ? [] : json_decode($user->permission);
        $groupPermissions   = $this->permission->getGroupPermissions();

        $htmlPermissions = '';
        foreach ($groupPermissions as $group) {
            $permissions = $this->permission->getPermissionByGroup($group->group_name);

            $htmlPermissions .= '
            <div class="col-md-4 grid-margin stretch-card permissions">
                <div class="card">
                  <div class="card-body">
                    <h4 class="card-title text-uppercase">'.$group->group_text.'</h4>
                    <div class="template-demo table-responsive">
                      <table class="table mb-0">
                        <tbody>';

            foreach ($permissions as $permission) {
                $checked = in_array($permission->id, $permissionUser) ? 'checked' : '';

                $htmlPermissions .= '
                          <tr>
                            <td class="pr-0 pl-0 pt-3 d-flex align-items-center">
                              <div class="switch">
                                <input type="checkbox" class="update-permission switch-input" name="'.$permission->name.'" id="user_'.$permission->name.'" permission-id="'.$permission->id.'" auto-check="'.$permission->auto_check.'" '.$checked.'>
                                <label for="user_'.$permission->name.'" class="switch-label"></label>
                              </div>
                              '.$permission->text.'
                            </td>
                          </tr>';
            }

            $htmlPermissions .= '
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>
              </div>';
        }

        return response()->json(['success' => true, 'data' => $htmlPermissions]);
    }

    public function updatePermissionsUsers(Request $request)
    {
        if (!hasAdmin())
            return response()->json(['success' => false, 'message' => 'Você não tem permissão para fazer essa operação!']);

        $arrPermissions = [];
        $company_id     = $request->user()->company_id;
        $user_id        = 0;

        // percorre os dados enviados
        foreach ($request->all() as $name => $value) {
            if ($name === 'user_id') { // recupera o usuário
                $user_id = $value;
                $getUser = $this->user->getUser($user_id, $company_id);
                if (!$getUser || $getUser->type_user !== 0)
                    return response()->json(['success' => false, 'message' => 'Usuário não encontrado, tente mais tarde!']);

                continue;
            }
            $getPermission = $this->permission->getPermissionByName($name); // recupera o ID da permissão

            if($getPermission) array_push($arrPermissions, $getPermission->id);
        }

        $update = $this->user->edit(['permission' => json_encode($arrPermissions)], $user_id, $company_id);

        if (!$update)
            return response()->json(['success' => false, 'message' => 'Não foi possível realizar a alteração de permissão, tente mais tarde!']);

        return response()->json(['success' => true, 'message' => 'Permissões de usuário atualizada com sucesso!']);
    }

    public function changeTypeUser(Request $request)
    {
        $company_id = $request->user()->company_id;
        $user_id    = $request->user_id;

        if (!hasAdmin()) {
            return response()->json(['success' => false, 'message' => 'Você não tem permissão para fazer essa operação!']);
        }

        $user = $this->user->getUser($user_id, $company_id);

        if (!$user)
            return response()->json(['success' => false, 'message' => "Não foi possível encontrar o usuário para realizar a operação!"]);

        if ($user->type_user != 0 && $user->type_user != 1)
            return response()->json(['success' => false, 'message' => "Não foi possível alterar o tipo de usuário, tente mais tarde!"]);

        $newTypeUser = $user->type_user === 0 ? 1 : 0;

        $update = $this->user->edit(['type_user' => $newTypeUser, 'permission' => '[]'], $user_id, $company_id);

        if (!$update)
            return response()->json(['success' => false, 'message' => 'Não foi possível realizar a alteração do usuário, tente mais tarde!']);

        return response()->json(['success' => true, 'message' => 'Tipo de usuário alterado com sucesso!']);
    }

    public function deleteUser(UserDeletePost $request)
    {
        $company_id     = $request->user()->company_id;
        $user_id_session= $request->user()->id;
        $user_id        = $request->user_id;

        if (!hasAdmin())
            return response()->json(['success' => false, 'message' => 'Você não tem permissão para fazer essa operação!']);

        if ($user_id == $user_id_session)
            return response()->json(['success' => false, 'message' => "Não é possível excluir o próprio usuário!"]);


        $user = $this->user->getUser($user_id, $company_id);

        if (!$user)
            return response()->json(['success' => false, 'message' => "Não foi possível encontrar o usuário para realizar a operação!"]);

        if ($user->type_user == 2)
            return response()->json(['success' => false, 'message' => "Não é possível excluir um administrador master!"]);

        $delete = $this->user->remove($user_id, $company_id);

        if (!$delete)
            return response()->json(['success' => false, 'message' => 'Não foi possível excluir o usuário, tente mais tarde!']);

        return response()->json(['success' => true, 'message' => 'Usuário excluído com sucesso!']);
    }

    public function getUser(Request $request)
    {
        $company_id     = $request->user()->company_id;
        $user_id_session= $request->user()->id;
        $user_id        = $request->user_id;

        if (!hasAdmin())
            return response()->json(['success' => false, 'data' => 'Você não tem permissão para fazer essa operação!']);

        if ($user_id == $user_id_session)
            return response()->json(['success' => false, 'data' => "Não é possível atualizar o cadastro do próprio usuário!"]);

        $user   = $this->user->getUser($user_id, $company_id);

        if (!$user)
            return response()->json(['success' => false, 'data' => "Não foi possível encontrar o usuário para realizar a atualização do cadastro!"]);

        if ($user->type_user == 2)
            return response()->json(['success' => false, 'data' => "Não é possível atualizar o cadastro de um administrador master!"]);

        $dataUsers = [
            'name'  => $user->name,
            'email' => $user->email,
            'phone' => $user->phone
        ];

        return response()->json(['success' => true, 'data' => $dataUsers]);
    }
}
