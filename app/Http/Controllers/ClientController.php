<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientPost;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Address;
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
        $getClients = $this->client->getClients();

        $dataClients = $getClients;

        return view('client.index', compact('dataClients'));
    }

    public function create()
    {
        return view('client.create');
    }

    public function insert(ClientPost $request)
    {
        // data client
        $company_id = $request->user()->company_id;
        $user_id = $request->user()->id;
        $name = $request->name_client;
        $type = $request->type_person;
        $fantasy = $request->fantasy_client;
        $email = filter_var($request->email, FILTER_VALIDATE_EMAIL);
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
        return 'editar cliente: ' . $id;
    }

    public function update(Request $request)
    {
        return 'update client';
    }

    public function delete(Request $request)
    {
        return 'delete client';
    }
}
