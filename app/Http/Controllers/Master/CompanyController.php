<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompanyUpdatePost;
use App\Http\Requests\Master\CompanyCreatePost;
use App\Models\Company;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    private Company $company;
    public function __construct()
    {
        $this->company = new Company();
    }

    public function index(): Factory|View|Application
    {
        return view('master.company.index');
    }

    public function edit(int $id): View|Factory|RedirectResponse|Application
    {
        $company = $this->company->getCompany($id);

        if (!$company) {
            return redirect()->route('master.company.index');
        }
        $company->logo = asset($company->logo ? "assets/images/company/$id/$company->logo" : "assets/images/system/company.png");

        return view('master.company.update', compact('company'));
    }

    public function create(): Factory|View|Application
    {
        return view('master.company.update');
    }

    public function fetch(Request $request): JsonResponse
    {
        $result     = array();
        $draw       = $request->input('draw');

        try {
            // Filtro status
            $status = $request->input('status');

            $filters        = array();
            $filter_default = array();
            $fields_order   = array(['name', 'fantasy'], 'cpf_cnpj', 'email', 'phone_1', 'status', 'plan_expiration_date', 'created_at','');

            if (!is_null($status) && $status !== 'all') {
                $filters[]['where']['status'] = $status;
            }

            $query = array(
                'from' => 'companies'
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
            $buttons = "<a href='".route('master.company.edit', ['id' => $value->id])."' class='btn btn-primary btn-sm btn-rounded btn-action' data-toggle='tooltip' title='Atualizar' ><i class='fas fa-edit'></i></a>";
            $buttons .= "<button data-company-id='".$value->id."' class='btn btn-success btn-sm btn-rounded btn-action btn-add-expiration-time ml-1' data-toggle='tooltip' title='Adicionar dias de expiração' ><i class='fa-regular fa-calendar-plus'></i></a>";

            $result[] = array(
                $value->name,
                formatCPF_CNPJ($value->cpf_cnpj),
                $value->email,
                formatPhone($value->phone_1),
                $value->status ? '<div class="badge badge-pill badge-lg badge-success">Ativo</div>' : '<div class="badge badge-pill badge-lg badge-danger">Inativo</div>',
                dateInternationalToDateBrazil($value->plan_expiration_date, DATETIME_BRAZIL_NO_SECONDS),
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

    private function getDataForm($request, ?int $company_id = null): array
    {
        $user_id        = $request->user()->id;
        $name           = filter_var($request->input('name'));
        $fantasy        = $request->input('fantasy') ? filter_var($request->input('fantasy')) : null;
        $email          = $request->input('email')   ? filter_var($request->input('email'), FILTER_VALIDATE_EMAIL) : null;
        $phone_1        = $request->input('phone_1') ? filter_var(onlyNumbers($request->input('phone_1')), FILTER_SANITIZE_NUMBER_INT) : null;
        $phone_2        = $request->input('phone_2') ? filter_var(onlyNumbers($request->input('phone_2')), FILTER_SANITIZE_NUMBER_INT) : null;
        $contact        = $request->input('contact') ? filter_var($request->input('contact')) : null;
        $cep            = $request->input('cep') ? filter_var(onlyNumbers($request->input('cep')), FILTER_SANITIZE_NUMBER_INT) : null;
        $address        = $request->input('address') ? filter_var($request->input('address')) : null;
        $number         = $request->input('number') ? filter_var($request->input('number')) : null;
        $complement     = $request->input('complement') ? filter_var($request->input('complement')) : null;
        $reference      = $request->input('reference') ? filter_var($request->input('reference')) : null;
        $neigh          = $request->input('neigh') ? filter_var($request->input('neigh')) : null;
        $city           = $request->input('city') ? filter_var($request->input('city')) : null;
        $state          = $request->input('state') ? filter_var($request->input('state')) : null;
        $status         = $request->input('status') ? 1 : 0;
        $plan_expiration_date = dateBrazilToDateInternational($request->input('plan_expiration_date'));

        return array(
            'name'          => $name,
            'fantasy'       => $fantasy,
            'email'         => $email,
            'phone_1'       => $phone_1,
            'phone_2'       => $phone_2,
            'contact'       => $contact,
            'address'       => $address,
            'number'        => $number,
            'cep'           => $cep,
            'complement'    => $complement,
            'reference'     => $reference,
            'neigh'         => $neigh,
            'city'          => $city,
            'state'         => $state,
            'status'        => $status,
            'plan_expiration_date' => $plan_expiration_date,
            $company_id ? 'user_update' : 'user_create'   => $user_id
        );
    }

    private function uploadLogo($request, $company_id): ?string
    {
        if ($request->file('profile_logo')) {
            try {
                return uploadFile("assets/images/company/$company_id", $request->file('profile_logo'), null, ['jpg', 'jpeg', 'png'], 2048);
            } catch (Exception $exception) {
                return redirect()->back()
                    ->withErrors([$exception->getMessage()])
                    ->withInput();
            }
        }

        return null;
    }

    public function update(CompanyUpdatePost $request, int $id): RedirectResponse
    {
        $arrDataCompany = $this->getDataForm($request, $id);
        $uploadLogo     = $this->uploadLogo($request, $id);

        if ($request->file('profile_logo') && !empty($uploadLogo)) {
            $arrDataCompany['logo'] = $uploadLogo;
        }

        $updateCompany = $this->company->edit($arrDataCompany, $id);

        if ($updateCompany) {
            return redirect()->route('master.company.index')
                ->with('success', "Empresa atualizada com sucesso!");
        }

        return redirect()->back()
            ->withErrors(['Não foi possível atualizar a empresa, tente novamente!'])
            ->withInput();

    }

    public function insert(CompanyCreatePost $request): RedirectResponse
    {
        $arrDataCompany = $this->getDataForm($request);
        $updateCompany  = $this->company->insert($arrDataCompany);
        $uploadLogo     = $this->uploadLogo($request, $updateCompany->id);

        $update_after_create = array(
            'type_person' => $request->input('type_person'),
            'cpf_cnpj' => onlyNumbers($request->input('cpf_cnpj'))
        );

        if ($request->file('profile_logo') && !empty($uploadLogo)) {
            $update_after_create['logo'] = $uploadLogo;
        }

        $updateCompany = $this->company->edit($update_after_create, $updateCompany->id);

        if ($updateCompany) {
            return redirect()->route('master.company.index')
                ->with('success', "Empresa cadastrada com sucesso!");
        }

        return redirect()->back()
            ->withErrors(['Não foi possível cadastrar a empresa, tente novamente!'])
            ->withInput();
    }

    public function addExpirationTime(Request $request): JsonResponse
    {
        $company_id = $request->input('company_id');
        $type       = $request->input('type');
        $time       = $request->input('time');
        $year       = $type === 'year' ? $time : null;
        $month      = $type === 'month' ? $time : null;
        $day        = $type === 'day' ? $time : null;

        $data_company = $this->company->getCompany($company_id);

        $actual_expiration_date = $data_company->plan_expiration_date;

        $new_expiration_date = sumDate($actual_expiration_date, $year, $month, $day);

        $updateCompany = $this->company->edit(array(
            'plan_expiration_date' => $new_expiration_date
        ), $company_id);

        if ($updateCompany) {
            return response()->json(array(
                'success' => true,
                'message' => "Data de expiração atualizada com sucesso!",
                'company_id' => $company_id,
                'new_expiration_date' => dateInternationalToDateBrazil($new_expiration_date, DATETIME_INTERNATIONAL_NO_SECONDS)
            ));
        }

        return response()->json(array(
            'success' => false,
            'message' => "Não foi possível atualizar a data de expiração, tente novamente!"
        ));
    }
}
