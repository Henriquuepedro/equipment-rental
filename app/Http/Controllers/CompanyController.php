<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyUpdatePost;
use App\Models\Company;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    private $user;
    private $company;
    private $permission;

    public function __construct(User $user, Company $company, Permission $permission)
    {
        $this->user         = $user;
        $this->company      = $company;
        $this->permission   = $permission;
    }

    public function index()
    {
        if (!$this->hasAdmin())
            return redirect()->route('dashboard');

        $company_id = Auth::user()->company_id;

        $company = $this->company->getCompany($company_id);
        $company->logo = asset($company->logo ? "assets/images/company/{$company_id}/{$company->logo}" : "assets/images/company/company.png");

        $groupPermissions = $this->permission->getGroupPermissions();

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
                $htmlPermissions .= '
                          <tr>
                            <td class="pr-0 pl-0">
                              <input type="checkbox" name="newuser_'.$permission->name.'" id="newuser_'.$permission->name.'" permission-id="'.$permission->id.'" auto-check="'.$permission->auto_check.'"> <label for="newuser_'.$permission->name.'">'.$permission->text.'</label>
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

        return view('config.index', compact('company', 'htmlPermissions'));
    }

    public function updateCompany(CompanyUpdatePost $request)
    {
        $user_id    = $request->user()->id;
        $company_id = $request->user()->company_id;

        $name       = filter_var($request->name, FILTER_SANITIZE_STRING);
        $fantasy    = $request->fantasy ? filter_var($request->fantasy, FILTER_SANITIZE_STRING) : null;
        $email      = $request->email   ? filter_var($request->email, FILTER_VALIDATE_EMAIL) : null;
        $phone_1    = $request->phone_1 ? filter_var(preg_replace('/[^0-9]/', '', $request->phone_1), FILTER_SANITIZE_NUMBER_INT) : null;
        $phone_2    = $request->phone_2 ? filter_var(preg_replace('/[^0-9]/', '', $request->phone_2), FILTER_SANITIZE_NUMBER_INT) : null;
        $contact    = $request->contact ? filter_var($request->contact, FILTER_SANITIZE_STRING) : null;

        if ($request->profile_logo) {
            $uploadLogo = $this->uploadLogoCompany($company_id, $request->file('profile_logo'));
            if ($uploadLogo === false) {
                return redirect()->back()
                    ->withErrors(['Não foi possível salvar a logo enviar. Tente novamente!'])
                    ->withInput();
            }
        }

        $arrDataCompany = array(
            'name'          => $name,
            'fantasy'       => $fantasy,
            'email'         => $email,
            'phone_1'       => $phone_1,
            'phone_2'       => $phone_2,
            'contact'       => $contact,
            'user_update'   => $user_id
        );

        if ($request->profile_logo) $arrDataCompany['logo'] = $uploadLogo;

        $updateCompany = $this->company->edit($arrDataCompany, $company_id);


        if($updateCompany) {
            return redirect()->route('config.index')
                ->with('success', "Empresa atualizada com sucesso!");
        }

        return redirect()->back()
            ->withErrors(['Não foi possível atualizar a empresa, tente novamente!'])
            ->withInput();

    }

    private function uploadLogoCompany($company_id, $file)
    {
        $uploadPath = "assets/images/company/{$company_id}";
        $extension = $file->getClientOriginalExtension(); // Recupera extensão da imagem
        $nameOriginal = $file->getClientOriginalName(); // Recupera nome da imagem
        $imageName = base64_encode($nameOriginal); // Gera um novo nome para a imagem.
        $imageName = substr($imageName, 0, 15) . rand(0, 100) . ".{$extension}"; // Pega apenas o 15 primeiros e adiciona a extensão

        return $file->move($uploadPath, $imageName) ? $imageName : false;
    }
}
