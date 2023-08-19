<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientCreatePost;
use App\Http\Requests\ClientDeletePost;
use App\Http\Requests\ClientUpdatePost;
use App\Models\Config;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Address;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    private Client $client;
    private Address $address;
    private Config $config;

    public function __construct()
    {
        $this->client   = new Client();
        $this->address  = new Address();
        $this->config   = new Config();
    }

    public function index(): Factory|View|RedirectResponse|Application
    {
        if (!hasPermission('ClientView')) {
            return redirect()->route('dashboard')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        return view('client.index');
    }

    public function create(): Factory|View|RedirectResponse|Application
    {
        if (!hasPermission('ClientCreatePost')) {
            return redirect()->route('client.index')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        return view('client.create');
    }

    public function insert(ClientCreatePost $request): JsonResponse|RedirectResponse
    {
        if (!hasPermission('ClientCreatePost')) {
            return redirect()->route('client.index')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        // data client
        $company_id     = $request->user()->company_id;
        $user_id        = $request->user()->id;
        $name           = filter_var($request->input('name_client'));
        $type           = filter_var($request->input('type_person'));
        $fantasy        = filter_var($request->input('fantasy_client'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $email          = filter_var($request->input('email'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $phone_1        = filter_var(onlyNumbers($request->input('phone_1')), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $phone_2        = filter_var(onlyNumbers($request->input('phone_2')), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $cpf_cnpj       = filter_var(onlyNumbers($request->input('cpf_cnpj')), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $rg_ie          = filter_var(onlyNumbers($request->input('rg_ie')), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $contact        = filter_var($request->input('contact'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $sex            = filter_var($request->input('sex'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $birth_date     = filter_var($request->input('birth_date'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $nationality    = filter_var($request->input('nationality'));
        $marital_status = filter_var($request->input('marital_status'));
        $observation    = filter_var($request->input('observation'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $isAjax         = isAjax();

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

        DB::beginTransaction();// Iniciando transação manual para evitar updates não desejáveis

        $createClient = $this->client->insert(array(
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
            'user_insert'   => $user_id
        ));

        $createAddress = true;
        $clientId = $createClient->id;

        $qtyAddress = is_array($request->input('name_address')) ? count($request->input('name_address')) : 0;
        for ($adr = 0; $adr < $qtyAddress; $adr++) {
            $name_address   = $request->input('name_address')[$adr] ? filter_var($request->input('name_address')[$adr]) : null;
            $cep            = $request->input('cep')[$adr]          ? filter_var(onlyNumbers($request->input('cep')[$adr]), FILTER_SANITIZE_NUMBER_INT) : null;
            $address        = $request->input('address')[$adr]      ? filter_var($request->input('address')[$adr]) : null;
            $number         = $request->input('number')[$adr]       ? filter_var($request->input('number')[$adr]) : null;
            $complement     = $request->input('complement')[$adr]   ? filter_var($request->input('complement')[$adr]) : null;
            $reference      = $request->input('reference')[$adr]    ? filter_var($request->input('reference')[$adr]) : null;
            $neigh          = $request->input('neigh')[$adr]        ? filter_var($request->input('neigh')[$adr]) : null;
            $city           = $request->input('city')[$adr]         ? filter_var($request->input('city')[$adr]) : null;
            $state          = $request->input('state')[$adr]        ? filter_var($request->input('state')[$adr]) : null;
            $lat            = $request->input('lat')[$adr]          ? filter_var($request->input('lat')[$adr]) : null;
            $lng            = $request->input('lng')[$adr]          ? filter_var($request->input('lng')[$adr]) : null;

            $verifyAddressStep_1 = $name_address || $cep || $address || $number || $complement || $reference || $neigh || $city || $state;
            $verifyAddressStep_2 = $address && $number && $neigh && $city && $state;

            // verifica se foi digitado algo no endereço para validar
            if ($verifyAddressStep_1 && !$verifyAddressStep_2) {
                if ($isAjax) {
                    return response()->json(['success' => false, 'message' => 'É necessário informar os campos de endereço obrigatório. Identificação do Endereço, Endereço, Número, Bairro, Cidade e Estado.']);
                }

                return redirect()->back()
                    ->withErrors(['É necessário informar os campos de endereço obrigatório. Identificação do Endereço, Endereço, Número, Bairro, Cidade e Estado.'])
                    ->withInput();
            }

            $createAddress = $this->address->insert(array(
                'company_id'    => $company_id,
                'client_id'     => $clientId,
                'name_address'  => $name_address,
                'address'       => $address,
                'number'        => $number,
                'cep'           => $cep,
                'complement'    => $complement,
                'reference'     => $reference,
                'neigh'         => $neigh,
                'city'          => $city,
                'state'         => $state,
                'lat'           => $lat,
                'lng'           => $lng,
                'user_insert'   => $user_id
            ));
        }

        if($createClient && $createAddress) {
            DB::commit();

            if ($isAjax) {
                return response()->json(['success' => true, 'message' => 'Cliente cadastrado com sucesso!', 'client_id' => $clientId]);
            }

            return redirect()->route('client.index')
                ->with('success', "Cliente com o código {$clientId}, cadastrado com sucesso!");
        }

        DB::rollBack();

        if ($isAjax) {
            return response()->json(['success' => false, 'message' => 'Não foi possível cadastrar o cliente, tente novamente!']);
        }

        return redirect()->back()
            ->withErrors(['Não foi possível cadastrar o cliente, tente novamente!'])
            ->withInput();

    }

    public function edit($id): View|Factory|RedirectResponse|Application
    {
        if (!hasPermission('ClientUpdatePost')) {
            return redirect()->route('client.index')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        $company_id = Auth::user()->__get('company_id');

        $client = $this->client->getClient($id, $company_id);
        if (!$client) {
            return redirect()->route('client.index');
        }

        $addresses = $this->address->getAddressClient($company_id, $id);

        return view('client.update', compact('client', 'addresses'));

    }

    public function update(ClientUpdatePost $request): RedirectResponse
    {
        // data client
        $client_id  = $request->input('client_id');
        $company_id = $request->user()->company_id;

        if (!$this->client->getClient($client_id, $company_id)) {
            return redirect()->back()
                ->withErrors(['Não foi possível localizar o cliente para atualizar!'])
                ->withInput();
        }

        $user_id        = $request->user()->id;
        $name           = filter_var($request->input('name_client'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $type           = filter_var($request->input('type_person'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $fantasy        = filter_var($request->input('fantasy_client'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $email          = filter_var($request->input('email'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $phone_1        = filter_var(onlyNumbers($request->input('phone_1')), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $phone_2        = filter_var(onlyNumbers($request->input('phone_2')), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $cpf_cnpj       = filter_var(onlyNumbers($request->input('cpf_cnpj')), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $rg_ie          = filter_var(onlyNumbers($request->input('rg_ie')), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $contact        = filter_var($request->input('contact'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $observation    = filter_var($request->input('observation'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $sex            = filter_var($request->input('sex'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $birth_date     = filter_var($request->input('birth_date'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $nationality    = filter_var($request->input('nationality'));
        $marital_status = filter_var($request->input('marital_status'));

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

        DB::beginTransaction();// Iniciando transação manual para evitar updates não desejáveis

        $createClient = $this->client->edit(array(
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
            'user_update'   => $user_id
        ), $client_id);


        // data address
        $createAddressStatus = true;
        $qtyAddress = isset($request->name_address) ? count($request->name_address) : 0;
        // remover todos os endereços desse cliente
        $this->address->deleteAddressClient($client_id);
        for ($adr = 0; $adr < $qtyAddress; $adr++) {
            $name_address   = $request->input('name_address')[$adr] ? filter_var($request->input('name_address')[$adr]) : null;
            $cep            = $request->input('cep')[$adr]          ? filter_var(onlyNumbers($request->input('cep')[$adr]), FILTER_SANITIZE_NUMBER_INT) : null;
            $address        = $request->input('address')[$adr]      ? filter_var($request->input('address')[$adr]) : null;
            $number         = $request->input('number')[$adr]       ? filter_var($request->input('number')[$adr]) : null;
            $complement     = $request->input('complement')[$adr]   ? filter_var($request->input('complement')[$adr]) : null;
            $reference      = $request->input('reference')[$adr]    ? filter_var($request->input('reference')[$adr]) : null;
            $neigh          = $request->input('neigh')[$adr]        ? filter_var($request->input('neigh')[$adr]) : null;
            $city           = $request->input('city')[$adr]         ? filter_var($request->input('city')[$adr]) : null;
            $state          = $request->input('state')[$adr]        ? filter_var($request->input('state')[$adr]) : null;
            $lat            = $request->input('lat')[$adr]          ? filter_var($request->input('lat')[$adr]) : null;
            $lng            = $request->input('lng')[$adr]          ? filter_var($request->input('lng')[$adr]) : null;

            $verifyAddressStep_1 = $name_address || $cep || $address || $number || $complement || $reference || $neigh || $city || $state;
            $verifyAddressStep_2 = $address && $number && $neigh && $city && $state;

            // verifica se foi digitado algo no endereço para validar
            if ($verifyAddressStep_1 && !$verifyAddressStep_2) {
                return redirect()->back()
                    ->withErrors(['É necessário informar os campos de endereço obrigatório. Endereço, Número, Bairro, Cidade e Estado.'])
                    ->withInput();
            }

            $createAddress = $this->address->insert(array(
                'company_id'    => $company_id,
                'client_id'     => $client_id,
                'name_address'  => $name_address,
                'address'       => $address,
                'number'        => $number,
                'cep'           => $cep,
                'complement'    => $complement,
                'reference'     => $reference,
                'neigh'         => $neigh,
                'city'          => $city,
                'state'         => $state,
                'lat'           => $lat,
                'lng'           => $lng,
                'user_insert'   => $user_id
            ));
            if (!$createAddress) {
                $createAddressStatus = false;
            }

        }

        if($createClient && $createAddressStatus) {
            DB::commit();
            return redirect()->route('client.index')
                ->with('success', "Cliente com o código {$client_id}, atualizado com sucesso!");
        }

        DB::rollBack();
        return redirect()->back()
            ->withErrors(['Não foi possível atualizar o cliente, tente novamente!'])
            ->withInput();
    }

    public function delete(ClientDeletePost $request): JsonResponse
    {
        $company_id = $request->user()->company_id;
        $client_id = $request->input('client_id');

        if (!$this->client->getClient($client_id, $company_id)) {
            return response()->json(['success' => false, 'message' => 'Não foi possível localizar o cliente!']);
        }

        if (!$this->client->remove($client_id, $company_id)) {
            return response()->json(['success' => false, 'message' => 'Não foi possível excluir o cliente!']);
        }

        return response()->json(['success' => true, 'message' => 'Cliente excluído com sucesso!']);
    }

    public function fetchClients(Request $request): JsonResponse
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
                'from' => 'clients'
            );

            $data = fetchDataTable(
                $query,
                array('name', 'asc'),
                null,
                ['ClientView'],
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

        $permissionUpdate = hasPermission('ClientUpdatePost');
        $permissionDelete = hasPermission('ClientDeletePost');

        foreach ($data['data'] as $value) {
            $buttons = "<a href='".route('client.edit', ['id' => $value->id])."' class='btn btn-primary btn-sm btn-rounded btn-action' data-toggle='tooltip' ";
            $buttons .= $permissionUpdate ? "title='Editar' ><i class='fas fa-edit'></i></a>" : "title='Visualizar' ><i class='fas fa-eye'></i></a>";
            $buttons .= $permissionDelete ? "<button class='btn btn-danger btnRemoveClient btn-sm btn-rounded btn-action ml-md-1' data-toggle='tooltip' title='Excluir' data-client-id='{$value->id}'><i class='fas fa-times'></i></button>" : '';

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

    public function getClients(Request $request): JsonResponse
    {
        if (!hasPermission('ClientView')) {
            return response()->json(['success' => false, 'message' => "Você não tem permissão para acessar essa página!"]);
        }

        $company_id = $request->user()->company_id;
        $clientData = [];
        $lastId = 0;

        $clients = $this->client->getClients($company_id);

        foreach ($clients as $client) {
            $clientData[] = ['id' => $client->id, 'name' => $client->name];
            if ($client->id > $lastId) {
                $lastId = $client->id;
            }
        }

        return response()->json(['data' => $clientData, 'lastId' => $lastId]);
    }

    public function getClient(int $client_id = null): JsonResponse
    {
        if (!hasPermission('ClientView')) {
            return response()->json(['success' => false, 'message' => "Você não tem permissão para acessar essa página!"]);
        }

        if (is_null($client_id)) {
            return response()->json();
        }

        $company_id = Auth::user()->__get('company_id');

        $client = $this->client->getClient($client_id, $company_id);

        $config_obs = $this->config->getConfigCompany($company_id, 'view_observation_client_rental');

        return response()->json([
            'observation' => $config_obs ? $client->observation : null
        ]);
    }
}
