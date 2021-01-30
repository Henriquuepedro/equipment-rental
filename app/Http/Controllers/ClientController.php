<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientCreatePost;
use App\Http\Requests\ClientDeletePost;
use App\Http\Requests\ClientUpdatePost;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Address;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    private $client;
    private $address;
    private $cpf_cnpj;

    public function __construct(Client $client, Address $address)
    {
        $this->client = $client;
        $this->address = $address;
    }

    public function index()
    {
        if (!$this->hasPermission('ClientView')) {
            return redirect()->route('dashboard')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        return view('client.index');
    }

    public function create()
    {
        if (!$this->hasPermission('ClientCreatePost')) {
            return redirect()->route('client.index')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        return view('client.create');
    }

    public function insert(ClientCreatePost $request)
    {
        // data client
        $company_id = $request->user()->company_id;
        $user_id    = $request->user()->id;
        $name       = $request->name_client     ? filter_var($request->name_client, FILTER_SANITIZE_STRING) : null;
        $type       = $request->type_person     ? filter_var($request->type_person, FILTER_SANITIZE_STRING) : 'pf';
        $fantasy    = $request->fantasy_client  ? filter_var($request->fantasy_client, FILTER_SANITIZE_STRING) : null;
        $email      = $request->email           ? filter_var($request->email, FILTER_VALIDATE_EMAIL) : null;
        $phone_1    = $request->phone_1         ? filter_var(preg_replace('/[^0-9]/', '', $request->phone_1), FILTER_SANITIZE_NUMBER_INT) : null;
        $phone_2    = $request->phone_2         ? filter_var(preg_replace('/[^0-9]/', '', $request->phone_2), FILTER_SANITIZE_NUMBER_INT) : null;
        $cpf_cnpj   = $request->cpf_cnpj        ? filter_var(preg_replace('/[^0-9]/', '', $request->cpf_cnpj), FILTER_SANITIZE_NUMBER_INT) : null;
        $rg_ie      = $request->rg_ie           ? filter_var(preg_replace('/[^0-9]/', '', $request->rg_ie), FILTER_SANITIZE_NUMBER_INT) : null;
        $contact    = $request->contact         ? filter_var($request->contact, FILTER_SANITIZE_STRING) : null;
        $observation= $request->observation     ? filter_var($request->observation, FILTER_SANITIZE_STRING) : null;
        $isAjax     = $this->isAjax();

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
            'observation'   => $observation,
            'user_insert'   => $user_id
        ));

        $createAddress = true;
        $clientId = $createClient->id;

        // data address

        $qtyAddress = isset($request->name_address) ? count($request->name_address) : 0;
        for ($adr = 0; $adr < $qtyAddress; $adr++) {
            $name_address   = $request->name_address[$adr] ? filter_var($request->name_address[$adr], FILTER_SANITIZE_STRING) : null;
            $cep            = $request->cep[$adr] ? filter_var(preg_replace('/[^0-9]/', '', $request->cep[$adr]), FILTER_SANITIZE_NUMBER_INT) : null;
            $address        = $request->address[$adr] ? filter_var($request->address[$adr], FILTER_SANITIZE_STRING) : null;
            $number         = $request->number[$adr] ? filter_var($request->number[$adr], FILTER_SANITIZE_STRING) : null;
            $complement     = $request->complement[$adr] ? filter_var($request->complement[$adr], FILTER_SANITIZE_STRING) : null;
            $reference      = $request->reference[$adr] ? filter_var($request->reference[$adr], FILTER_SANITIZE_STRING) : null;
            $neigh          = $request->neigh[$adr] ? filter_var($request->neigh[$adr], FILTER_SANITIZE_STRING) : null;
            $city           = $request->city[$adr] ? filter_var($request->city[$adr], FILTER_SANITIZE_STRING) : null;
            $state          = $request->state[$adr] ? filter_var($request->state[$adr], FILTER_SANITIZE_STRING) : null;
            $lat            = $request->lat[$adr] ? filter_var($request->lat[$adr], FILTER_SANITIZE_STRING) : null;
            $lng            = $request->lng[$adr] ? filter_var($request->lng[$adr], FILTER_SANITIZE_STRING) : null;

            $verifyAddressStep_1 = $name_address || $cep || $address || $number || $complement || $reference || $neigh || $city || $state;
            $verifyAddressStep_2 = $address && $number && $neigh && $city && $state;

            // verifica se foi digitado algo no endereço para validar
            if ($verifyAddressStep_1 && !$verifyAddressStep_2) {

                if ($isAjax)
                    return response()->json(['success' => false, 'message' => 'É necessário informar os campos de endereço obrigatório. Identificação do Endereço, Endereço, Número, Bairro, Cidade e Estado.']);

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

            if ($isAjax)
                return response()->json(['success' => true, 'message' => 'Cliente cadastrado com sucesso!', 'client_id' => $clientId]);

            return redirect()->route('client.index')
                ->with('success', "Cliente com o código {$clientId}, cadastrado com sucesso!");
        }

        DB::rollBack();

        if ($isAjax)
            return response()->json(['success' => false, 'message' => 'Não foi possível cadastrar o cliente, tente novamente!']);

        return redirect()->back()
            ->withErrors(['Não foi possível cadastrar o cliente, tente novamente!'])
            ->withInput();

    }

    public function edit($id)
    {
        $company_id = Auth::user()->company_id;

        $client = $this->client->getClient($id, $company_id);
        if (!$client)
            return redirect()->route('client.index');

        $addresses = $this->address->getAddressClient($company_id, $id);

        return view('client.update', compact('client', 'addresses'));

    }

    public function update(ClientUpdatePost $request)
    {
        // data client
        $client_id = $request->client_id;
        $company_id = $request->user()->company_id;

        if (!$this->client->getClient($client_id, $company_id))
            return redirect()->back()
                ->withErrors(['Não foi possível localizar o cliente para atualizar!'])
                ->withInput();


        $user_id = $request->user()->id;
        $name       = $request->name_client     ? filter_var($request->name_client, FILTER_SANITIZE_STRING) : null;
        $type       = $request->type_person     ? filter_var($request->type_person, FILTER_SANITIZE_STRING) : 'pf';
        $fantasy    = $request->fantasy_client  ? filter_var($request->fantasy_client, FILTER_SANITIZE_STRING) : null;
        $email      = $request->email           ? (filter_var($request->email, FILTER_VALIDATE_EMAIL) ? $request->email : null) : null;
        $phone_1    = $request->phone_1         ? filter_var(preg_replace('/[^0-9]/', '', $request->phone_1), FILTER_SANITIZE_NUMBER_INT) : null;
        $phone_2    = $request->phone_2         ? filter_var(preg_replace('/[^0-9]/', '', $request->phone_2), FILTER_SANITIZE_NUMBER_INT) : null;
        $cpf_cnpj   = $request->cpf_cnpj        ? filter_var(preg_replace('/[^0-9]/', '', $request->cpf_cnpj), FILTER_SANITIZE_NUMBER_INT) : null;
        $rg_ie      = $request->rg_ie           ? filter_var(preg_replace('/[^0-9]/', '', $request->rg_ie), FILTER_SANITIZE_NUMBER_INT) : null;
        $contact    = $request->contact         ? filter_var($request->contact, FILTER_SANITIZE_STRING) : null;
        $observation= $request->observation     ? filter_var($request->observation, FILTER_SANITIZE_STRING) : null;

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
            'observation'   => $observation,
            'user_update'   => $user_id
        ), $client_id);


        // data address
        $createAddressStatus = true;
        $qtyAddress = isset($request->name_address) ? count($request->name_address) : 0;
        // remover todos os endereços desse cliente
        $this->address->deleteAddressClient($client_id);
        for ($adr = 0; $adr < $qtyAddress; $adr++) {
            $name_address   = $request->name_address[$adr] ? filter_var($request->name_address[$adr], FILTER_SANITIZE_STRING) : null;
            $cep            = $request->cep[$adr] ? filter_var(preg_replace('/[^0-9]/', '', $request->cep[$adr]), FILTER_SANITIZE_NUMBER_INT) : null;
            $address        = $request->address[$adr] ? filter_var($request->address[$adr], FILTER_SANITIZE_STRING) : null;
            $number         = $request->number[$adr] ? filter_var($request->number[$adr], FILTER_SANITIZE_STRING) : null;
            $complement     = $request->complement[$adr] ? filter_var($request->complement[$adr], FILTER_SANITIZE_STRING) : null;
            $reference      = $request->reference[$adr] ? filter_var($request->reference[$adr], FILTER_SANITIZE_STRING) : null;
            $neigh          = $request->neigh[$adr] ? filter_var($request->neigh[$adr], FILTER_SANITIZE_STRING) : null;
            $city           = $request->city[$adr] ? filter_var($request->city[$adr], FILTER_SANITIZE_STRING) : null;
            $state          = $request->state[$adr] ? filter_var($request->state[$adr], FILTER_SANITIZE_STRING) : null;
            $lat            = $request->lat[$adr] ? filter_var($request->lat[$adr], FILTER_SANITIZE_STRING) : null;
            $lng            = $request->lng[$adr] ? filter_var($request->lng[$adr], FILTER_SANITIZE_STRING) : null;

            $verifyAddressStep_1 = $name_address || $cep || $address || $number || $complement || $reference || $neigh || $city || $state;
            $verifyAddressStep_2 = $address && $number && $neigh && $city && $state;

            // verifica se foi digitado algo no endereço para validar
            if ($verifyAddressStep_1 && !$verifyAddressStep_2)
                return redirect()->back()
                    ->withErrors(['É necessário informar os campos de endereço obrigatório. Endereço, Número, Bairro, Cidade e Estado.'])
                    ->withInput();

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
            if (!$createAddress) $createAddressStatus = false;

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

    public function delete(ClientDeletePost $request)
    {
        $company_id = $request->user()->company_id;
        $client_id = $request->client_id;

        if (!$this->client->getClient($client_id, $company_id))
            return response()->json(['success' => false, 'message' => 'Não foi possível localizar o cliente!']);

        if (!$this->client->remove($client_id, $company_id))
            return response()->json(['success' => false, 'message' => 'Não foi possível excluir o cliente!']);

        return response()->json(['success' => true, 'message' => 'Cliente excluído com sucesso!']);
    }

    public function fetchClients(Request $request)
    {
        // DB::enableQueryLog();

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

            $fieldsOrder = array('id','name','email','phone_1', '');
            $fieldOrder =  $fieldsOrder[$request->order[0]['column']];
            if ($fieldOrder != "") {
                $orderBy['field'] = $fieldOrder;
                $orderBy['order'] = $direction;
            }
        }


        if (!empty($searchUser)) {
            $filtered = $this->client->getCountClients($company_id, $searchUser);
        } else {
            $filtered = 0;
        }

        $data = $this->client->getClients($company_id, $ini, $length, $searchUser, $orderBy);

        // get string query
        // DB::getQueryLog();

        $permissionUpdate = $this->hasPermission('ClientUpdatePost');
        $permissionDelete = $this->hasPermission('ClientDeletePost');

        $i = 0;
        foreach ($data as $key => $value) {
            $i++;
            $buttons = "<a href='".route('client.edit', ['id' => $value['id']])."' class='btn btn-primary btn-sm btn-rounded btn-action' data-toggle='tooltip' ";
            $buttons .= $permissionUpdate ? "title='Editar' ><i class='fas fa-edit'></i></a>" : "title='Visualizar' ><i class='fas fa-eye'></i></a>";
            $buttons .= $permissionDelete ? "<button class='btn btn-danger btnRemoveClient btn-sm btn-rounded btn-action ml-md-1' data-toggle='tooltip' title='Excluir' client-id='{$value['id']}'><i class='fas fa-times'></i></button>" : '';

            $result[$key] = array(
                $value['id'],
                $value['name'],
                $value['email'],
                $value['phone_1'],
                $buttons
            );
        }

        if ($filtered == 0) $filtered = $i;

        $output = array(
            "draw" => $draw,
            "recordsTotal" => $this->client->getCountClients($company_id),
            "recordsFiltered" => $filtered,
            "data" => $result
        );

        return response()->json($output);
    }

    public function getClients(Request $request)
    {
        $company_id = $request->user()->company_id;
        $clientData = [];
        $lastId = 0;

        $clients = $this->client->getClients($company_id);

        foreach ($clients as $client) {
            array_push($clientData, ['id' => $client->id, 'name' => $client->name]);
            if ($client->id > $lastId) $lastId = $client->id;
        }

        return response()->json(['data' => $clientData, 'lastId' => $lastId]);
    }

    public function getClient(Request $request)
    {
        $company_id = $request->user()->company_id;
        $client_id  = $request->client_id;

        $client = $this->client->getClient($client_id, $company_id);

        return response()->json([
            'observation' => $client->observation
        ]);
    }
}
