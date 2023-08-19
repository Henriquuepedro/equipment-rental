<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProviderCreatePost;
use App\Http\Requests\ProviderDeletePost;
use App\Http\Requests\ProviderUpdatePost;
use App\Models\Config;
use App\Models\Provider;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProviderController extends Controller
{
    private Provider $provider;
    private Config $config;

    public function __construct()
    {
        $this->provider = new Provider();
        $this->config = new Config();
    }

    public function index(): Factory|View|RedirectResponse|Application
    {
        if (!hasPermission('ProviderView')) {
            return redirect()->route('dashboard')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        return view('provider.index');
    }

    public function create(): Factory|View|RedirectResponse|Application
    {
        if (!hasPermission('ProviderCreatePost')) {
            return redirect()->route('provider.index')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        return view('provider.create');
    }

    private function formatDataToCreateAndUpdate($request, bool $create = true): array
    {
        // data provider
        $company_id     = $request->user()->company_id;
        $user_id        = $request->user()->id;
        $name           = filter_var($request->input('name'));
        $type           = filter_var($request->input('type_person'));
        $fantasy        = filter_var($request->input('fantasy'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $email          = filter_var($request->input('email'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $phone_1        = filter_var(onlyNumbers($request->input('phone_1')), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $phone_2        = filter_var(onlyNumbers($request->input('phone_2')), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $cpf_cnpj       = filter_var(onlyNumbers($request->input('cpf_cnpj')), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $rg_ie          = filter_var(onlyNumbers($request->input('rg_ie')), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $contact        = filter_var($request->input('contact'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $sex            = filter_var($request->input('sex'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $birth_date     = filter_var($request->input('birth_date'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $nationality    = filter_var($request->input('nationality'), FILTER_SANITIZE_NUMBER_INT);
        $marital_status = filter_var($request->input('marital_status'), FILTER_SANITIZE_NUMBER_INT);
        $observation    = filter_var($request->input('observation'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);

        $address        = filter_var($request->input('address'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $number         = filter_var($request->input('number'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $cep            = filter_var(onlyNumbers($request->input('cep')), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $complement     = filter_var($request->input('complement'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $reference      = filter_var($request->input('reference'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $neigh          = filter_var($request->input('neigh'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $city           = filter_var($request->input('city'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $state          = filter_var($request->input('state'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);

        if (empty($nationality)) {
            $nationality = null;
        }
        if (empty($marital_status)) {
            $marital_status = null;
        }

        if ($type === 'pj') {
            $sex            = null;
            $birth_date     = null;
            $nationality    = null;
            $marital_status = null;
        } else {
            $fantasy = null;
        }

        return array(
            'company_id'    => $company_id,
            'type'          => $type,
            'name'          => $name,
            'fantasy'       => $fantasy,
            'email'         => $email,
            'phone_1'       => $phone_1,
            'phone_2'       => $phone_2,
            'cpf_cnpj'      => $cpf_cnpj,
            'rg_ie'         => $rg_ie,
            'contact'       => $contact,
            'sex'           => $sex,
            'birth_date'    => $birth_date,
            'nationality'   => $nationality,
            'marital_status'=> $marital_status,
            'observation'   => $observation,
            'address'       => $address,
            'number'        => $number,
            'cep'           => $cep,
            'complement'    => $complement,
            'reference'     => $reference,
            'neigh'         => $neigh,
            'city'          => $city,
            'state'         => $state,
            $create ? 'user_insert' : 'user_update'   => $user_id
        );
    }

    public function insert(ProviderCreatePost $request): JsonResponse|RedirectResponse
    {
        $isAjax = isAjax();

        $createProvider = $this->provider->insert($this->formatDataToCreateAndUpdate($request));
        $provider_id = $createProvider->id;

        if($createProvider) {
            if ($isAjax) {
                return response()->json(['success' => true, 'message' => 'Fornecedor cadastrado com sucesso!', 'provider_id' => $provider_id]);
            }

            return redirect()->route('provider.index')
                ->with('success', "Fornecedor com o código $provider_id, cadastrado com sucesso!");
        }

        if ($isAjax) {
            return response()->json(['success' => false, 'message' => 'Não foi possível cadastrar o fornecedor, tente novamente!']);
        }

        return redirect()->back()
            ->withErrors(['Não foi possível cadastrar o fornecedor, tente novamente!'])
            ->withInput();

    }

    public function edit($id): View|Factory|RedirectResponse|Application
    {
        $company_id = Auth::user()->__get('company_id');

        $provider = $this->provider->getProvider($id, $company_id);
        if (!$provider) {
            return redirect()->route('provider.index');
        }

        return view('provider.update', compact('provider'));

    }

    public function update(ProviderUpdatePost $request): RedirectResponse
    {
        // data provider
        $provider_id = $request->input('provider_id');
        $company_id = $request->user()->company_id;

        if (!$this->provider->getProvider($provider_id, $company_id)) {
            return redirect()->back()
                ->withErrors(['Não foi possível localizar o fornecedor para atualizar!'])
                ->withInput();
        }

        $createProvider = $this->provider->edit($this->formatDataToCreateAndUpdate($request, false), $provider_id);

        if($createProvider) {
            return redirect()->route('provider.index')
                ->with('success', "Fornecedor com o código $provider_id, atualizado com sucesso!");
        }

        return redirect()->back()
            ->withErrors(['Não foi possível atualizar o fornecedor, tente novamente!'])
            ->withInput();
    }

    public function delete(ProviderDeletePost $request): JsonResponse
    {
        $company_id = $request->user()->company_id;
        $provider_id = $request->input('provider_id');

        if (!$this->provider->getProvider($provider_id, $company_id)) {
            return response()->json(['success' => false, 'message' => 'Não foi possível localizar o fornecedor!']);
        }

        if (!$this->provider->remove($provider_id, $company_id)) {
            return response()->json(['success' => false, 'message' => 'Não foi possível excluir o fornecedor!']);
        }

        return response()->json(['success' => true, 'message' => 'Fornecedor excluído com sucesso!']);
    }

    public function fetchProviders(Request $request): JsonResponse
    {
        $result     = array();
        $draw       = $request->input('draw');
        $company_id = $request->user()->company_id;

        try {
            $filters        = array();
            $filter_default = array();
            $fields_order   = array('id','name','email','phone_1', '');

            $filter_default[]['where']['company_id'] = $company_id;

            $query = array(
                'from' => 'providers'
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

        $permissionUpdate = hasPermission('ProviderUpdatePost');
        $permissionDelete = hasPermission('ProviderDeletePost');

        foreach ($data['data'] as $value) {
            $buttons = "<a href='".route('provider.edit', ['id' => $value->id])."' class='btn btn-primary btn-sm btn-rounded btn-action' data-toggle='tooltip' ";
            $buttons .= $permissionUpdate ? "title='Editar' ><i class='fas fa-edit'></i></a>" : "title='Visualizar' ><i class='fas fa-eye'></i></a>";
            $buttons .= $permissionDelete ? "<button class='btn btn-danger btnRemoveProvider btn-sm btn-rounded btn-action ml-md-1' data-toggle='tooltip' title='Excluir' provider-id='$value->id'><i class='fas fa-times'></i></button>" : '';

            $result[] = array(
                $value->id,
                $value->name,
                $value->email,
                $value->phone_1,
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

    public function getProviders(): JsonResponse
    {
        $company_id = Auth::user()->__get('company_id');
        $providerData = [];
        $lastId = 0;

        $providers = $this->provider->getProviders($company_id);

        foreach ($providers as $provider) {
            $providerData[] = ['id' => $provider->id, 'name' => $provider->name];
            if ($provider->id > $lastId) $lastId = $provider->id;
        }

        return response()->json(['data' => $providerData, 'lastId' => $lastId]);
    }

    public function getProvider(int $provider_id = null): JsonResponse
    {
        if (is_null($provider_id)) {
            return response()->json();
        }

        $company_id = Auth::user()->__get('company_id');

        $provider = $this->provider->getProvider($provider_id, $company_id);

        $configObs = $this->config->getConfigCompany($company_id, 'view_observation_client_rental');

        return response()->json([
            'observation' => $configObs ? $provider->observation : null
        ]);
    }
}
