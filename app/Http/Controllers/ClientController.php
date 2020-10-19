<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientCreatePost;
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
        $company_id = Auth::user()->company_id;
        $getClients = $this->client->getClients($company_id);

        $dataClients = $getClients;

        return view('client.index', compact('dataClients'));
    }

    public function create()
    {
        return view('client.create');
    }

    public function insert(ClientCreatePost $request)
    {
        // data client
        $company_id = $request->user()->company_id;
        $user_id = $request->user()->id;
        $name = $request->name_client;
        $type = $request->type_person;
        $fantasy = $request->fantasy_client;
        $email = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? $request->email : null;
        $phone_1 = filter_var(preg_replace('/[^0-9]/', '', $request->phone_1), FILTER_SANITIZE_NUMBER_INT);
        $phone_2 = filter_var(preg_replace('/[^0-9]/', '', $request->phone_2), FILTER_SANITIZE_NUMBER_INT);
        $cpf_cnpj = filter_var(preg_replace('/[^0-9]/', '', $request->cpf_cnpj), FILTER_SANITIZE_NUMBER_INT);
        $rg_ie = filter_var(preg_replace('/[^0-9]/', '', $request->rg_ie), FILTER_SANITIZE_NUMBER_INT);

        // data address
        $name_address = $request->name_address;
        $cep = filter_var(preg_replace('/[^0-9]/', '', $request->cep), FILTER_SANITIZE_NUMBER_INT);
        $address = $request->address;
        $number = $request->number;
        $complement = $request->complement;
        $reference = $request->reference;
        $neigh = $request->neigh;
        $city = $request->city;
        $state = $request->state;
        $addressComplet = $name_address || $cep || $address || $number || $complement || $reference || $neigh || $city || $state;

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
            'user_insert'   => $user_id
        ));

        $createAddress = true;
        $clientId = $createClient->id;
        if ($addressComplet) {
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
                'user_insert'   => $user_id
            ));
        }
        if($createClient && $createAddress) {
            DB::commit();
            return redirect()->route('client.index')
                ->with('success', "Cliente com o código {$clientId}, cadastrado com sucesso!");
        }

        DB::rollBack();
        return redirect()->back()
            ->withErrors(['Não foi possível cadastrar o cliente, tente novamente!']);

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
                ->withErrors(['Não foi possível localizar o cliente para atualizar!']);


        $user_id = $request->user()->id;
        $name = $request->name_client;
        $type = $request->type_person;
        $fantasy = $request->fantasy_client;
        $email = filter_var($request->email, FILTER_VALIDATE_EMAIL) ? $request->email : null;
        $phone_1 = filter_var(preg_replace('/[^0-9]/', '', $request->phone_1), FILTER_SANITIZE_NUMBER_INT);
        $phone_2 = filter_var(preg_replace('/[^0-9]/', '', $request->phone_2), FILTER_SANITIZE_NUMBER_INT);
        $cpf_cnpj = filter_var(preg_replace('/[^0-9]/', '', $request->cpf_cnpj), FILTER_SANITIZE_NUMBER_INT);
        $rg_ie = filter_var(preg_replace('/[^0-9]/', '', $request->rg_ie), FILTER_SANITIZE_NUMBER_INT);

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
            'user_update'   => $user_id
        ), $client_id);


        // data address
        $createAddressStatus = true;
        $qtyAddress = isset($request->name_address) ? count($request->name_address) : 0;
        // remover todos os endereços desse cliente
        $this->address->deleteAddressClient($client_id);
        for ($adr = 0; $adr < $qtyAddress; $adr++) {
            $name_address = $request->name_address[$adr];
            $cep = filter_var(preg_replace('/[^0-9]/', '', $request->cep[$adr]), FILTER_SANITIZE_NUMBER_INT);
            $address = $request->address[$adr];
            $number = $request->number[$adr];
            $complement = $request->complement[$adr];
            $reference = $request->reference[$adr];
            $neigh = $request->neigh[$adr];
            $city = $request->city[$adr];
            $state = $request->state[$adr];
            $addressComplet = $name_address || $cep || $address || $number || $complement || $reference || $neigh || $city || $state;

            if ($addressComplet) {
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
                    'user_insert'   => $user_id
                ));
                if (!$createAddress) $createAddressStatus = false;
            }

        }

        if($createClient && $createAddressStatus) {
            DB::commit();
            return redirect()->route('client.index')
                ->with('success', "Cliente com o código {$client_id}, atualizado com sucesso!");
        }

        DB::rollBack();
        return redirect()->back()
            ->withErrors(['Não foi possível atualizar o cliente, tente novamente!']);
    }

    public function delete(Request $request)
    {
        $company_id = $request->user()->company_id;
        $client_id = $request->client_id;

        if (!$this->client->getClient($client_id, $company_id)) {
            echo json_encode(['success' => false, 'message' => 'Não foi possível localizar o cliente!']);
            die;
        }

        if (!$this->client->remove($client_id, $company_id)) {
            echo json_encode(['success' => false, 'message' => 'Não foi possível excluir o cliente!']);
            die;
        }

        echo json_encode(['success' => true, 'message' => 'Cliente excluído com sucesso!']);
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

        $i = 0;
        foreach ($data as $key => $value) {
            $i++;
            $buttons = "<a href='".route('client.edit', ['id' => $value['id']])."' class='btn btn-primary btn-sm btn-rounded btn-action' data-toggle='tooltip' title='Editar' ><i class='fas fa-edit'></i></a>
                        <button class='btn btn-danger btnRemoveClient btn-sm btn-rounded btn-action ml-md-1' data-toggle='tooltip' title='Excluir' client-id='{$value['id']}'><i class='fas fa-times'></i></button>";

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

        echo json_encode($output);
    }
}
