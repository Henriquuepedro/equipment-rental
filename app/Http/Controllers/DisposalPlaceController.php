<?php

namespace App\Http\Controllers;

use App\Http\Requests\DisposalPlaceCreatePost;
use App\Http\Requests\DisposalPlaceDeletePost;
use App\Http\Requests\DisposalPlaceUpdatePost;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\DisposalPlace;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DisposalPlaceController extends Controller
{
    private DisposalPlace $disposal_place;

    public function __construct()
    {
        $this->disposal_place = new DisposalPlace();
    }

    public function index(): Factory|View|RedirectResponse|Application
    {
        if (!hasPermission('DisposalPlaceView')) {
            return redirect()->route('dashboard')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        return view('disposal_place.index');
    }

    public function create(): Factory|View|RedirectResponse|Application
    {
        if (!hasPermission('DisposalPlaceCreatePost')) {
            return redirect()->route('disposal_place.index')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        return view('disposal_place.update');
    }

    public function insert(DisposalPlaceCreatePost $request): JsonResponse|RedirectResponse
    {
        $isAjax = isAjax();

        if (!hasPermission('DisposalPlaceCreatePost')) {
            if ($isAjax) {
                return response()->json(['success' => false, 'message' => "Você não tem permissão para acessar essa página!"]);
            }

            return redirect()->route('disposal_place.index')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        $data_update    = $this->formatDataToSave($request);
        $create         = $this->disposal_place->insert($data_update);
        $disposal_place_id       = $create->id;

        if($create) {
            if ($isAjax) {
                return response()->json(['success' => true, 'message' => 'Local de descarte cadastrado com sucesso!', 'disposal_place_id' => $disposal_place_id]);
            }

            return redirect()->route('disposal_place.index')
                ->with('success', "Local de descarte com o código $disposal_place_id, cadastrado com sucesso!");
        }

        if ($isAjax) {
            return response()->json(['success' => false, 'message' => 'Não foi possível cadastrar o local de descarte, tente novamente!']);
        }

        return redirect()->back()
            ->withErrors(['Não foi possível cadastrar o local de descarte, tente novamente!'])
            ->withInput();

    }

    public function edit($id): View|Factory|RedirectResponse|Application
    {
        if (!hasPermission('DisposalPlaceUpdatePost')) {
            return redirect()->route('disposal_place.index')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        $company_id = Auth::user()->__get('company_id');

        $disposal_place = $this->disposal_place->getByid($id, $company_id);
        if (!$disposal_place) {
            return redirect()->route('disposal_place.index');
        }

        return view('disposal_place.update', compact('disposal_place'));

    }

    public function update(DisposalPlaceUpdatePost $request): RedirectResponse
    {
        // data disposal_places
        $disposal_place_id  = $request->input('disposal_place_id');
        $company_id = $request->user()->company_id;

        if (!$this->disposal_place->getByid($disposal_place_id, $company_id)) {
            return redirect()->back()
                ->withErrors(['Não foi possível localizar o local de descarte para atualizar!'])
                ->withInput();
        }

        $data_update = $this->formatDataToSave($request, $disposal_place_id);
        $create = $this->disposal_place->edit($data_update, $disposal_place_id);

        if($create) {
            DB::commit();
            return redirect()->route('disposal_place.index')
                ->with('success', "Local de descarte com o código $disposal_place_id, atualizado com sucesso!");
        }

        DB::rollBack();
        return redirect()->back()
            ->withErrors(['Não foi possível atualizar o local de descarte, tente novamente!'])
            ->withInput();
    }

    private function formatDataToSave($request, int $id = null): array
    {
        $company_id         = $request->user()->company_id;
        $user_id            = $request->user()->id;
        $name               = filter_var($request->input('name'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $fantasy            = filter_var($request->input('fantasy'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $type_person        = filter_var($request->input('type_person'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $cpf_cnpj           = filter_var(onlyNumbers($request->input('cpf_cnpj')), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $rg_ie              = filter_var(onlyNumbers($request->input('rg_ie')), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $email              = filter_var($request->input('email'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $phone_1            = filter_var(onlyNumbers($request->input('phone_1')), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $phone_2            = filter_var(onlyNumbers($request->input('phone_2')), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $contact            = filter_var($request->input('contact'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $address_zipcode    = filter_var(onlyNumbers($request->input('address_zipcode')), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $address_name       = filter_var($request->input('address_name'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $address_number     = filter_var($request->input('address_number'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $address_complement = filter_var($request->input('address_complement'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $address_reference  = filter_var($request->input('address_reference'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $address_neigh      = filter_var($request->input('address_neigh'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $address_city       = filter_var($request->input('address_city'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $address_state      = filter_var($request->input('address_state'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $observation        = filter_var($request->input('observation'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $active             = $request->input('active') ? 1 : 0;

        $response = array(
            'company_id'            => $company_id,
            'user_id'               => $user_id,
            'name'                  => $name,
            'fantasy'               => $fantasy,
            'type_person'           => $type_person,
            'cpf_cnpj'              => $cpf_cnpj,
            'rg_ie'                 => $rg_ie,
            'email'                 => $email,
            'phone_1'               => $phone_1,
            'phone_2'               => $phone_2,
            'contact'               => $contact,
            'address_zipcode'       => $address_zipcode,
            'address_name'          => $address_name,
            'address_number'        => $address_number,
            'address_complement'    => $address_complement,
            'address_reference'     => $address_reference,
            'address_neigh'         => $address_neigh,
            'address_city'          => $address_city,
            'address_state'         => $address_state,
            'observation'           => $observation,
            'active'                => $active,
            'user_insert'           => $user_id
        );

        if ($id) {
            $response['user_update'] = $response['user_insert'];
            unset($response['company_id']);
            unset($response['user_insert']);
        }

        return $response;
    }

    public function delete(DisposalPlaceDeletePost $request): JsonResponse
    {
        $company_id = $request->user()->company_id;
        $disposal_place_id = $request->input('disposal_place_id');

        if (!$this->disposal_place->getByid($disposal_place_id, $company_id)) {
            return response()->json(['success' => false, 'message' => 'Não foi possível localizar o local de descarte!']);
        }

        // Verificar se não tem registros relacionados.

        if (!$this->disposal_place->remove($disposal_place_id, $company_id)) {
            return response()->json(['success' => false, 'message' => 'Não foi possível excluir o local de descarte!']);
        }

        return response()->json(['success' => true, 'message' => 'Local de descarte excluído com sucesso!']);
    }

    public function fetch(Request $request): JsonResponse
    {
        $result     = array();
        $draw       = $request->input('draw');
        $company_id = $request->user()->company_id;

        try {
            // Filtro status
            $active = $request->input('active');

            $filters        = array();
            $filter_default = array();
            $fields_order   = array('id','name','email','phone_1','active', '');

            $filter_default[]['where']['company_id'] = $company_id;

            if (!is_null($active) && $active !== 'all') {
                $filters[]['where']['active'] = $active;
            }

            $query = array(
                'from' => 'disposal_places'
            );

            $data = fetchDataTable(
                $query,
                array('name', 'asc'),
                null,
                ['DisposalPlaceView'],
                $filters,
                $fields_order,
                $filter_default
            );

        } catch (Exception $exception) {
            return response()->json(getErrorDataTables($exception->getMessage(), $draw));
        }

        $permissionUpdate   = hasPermission('DisposalPlaceUpdatePost');
        $permissionDelete   = hasPermission('DisposalPlaceDeletePost');

        foreach ($data['data'] as $value) {
            $buttons = "<a href='".route('disposal_place.edit', ['id' => $value->id])."' class='dropdown-item'>";
            $buttons .= $permissionUpdate ? "<i class='fas fa-edit'></i> Atualizar Cadastro</a>" : "<i class='fas fa-eye'></i> Visualizar Cadastro</a>";
            $buttons .= $permissionDelete ? "<button class='dropdown-item btnRemoveDisposalPlace' data-disposal-place-id='$value->id'><i class='fas fa-solid fa-xmark pr-1'></i> Excluir Cadastro</button>" : '';

            $result[] = array(
                $value->id,
                $value->name,
                $value->email ?: 'Não Informado',
                formatPhone($value->phone_1),
                getHtmlStatusList($value->active),
                dropdownButtonsDataList($buttons, $value->id)
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

    public function getDisposalPlaces(Request $request): JsonResponse
    {
        if (!hasPermission('DisposalPlaceView')) {
            return response()->json(['data' => [], 'lastId' => 0]);
        }

        $company_id = $request->user()->company_id;
        $disposalPlaceData = [];
        $lastId = 0;

        $disposal_places = $this->disposal_place->getAllActives($company_id);

        foreach ($disposal_places as $disposal_place) {
            $disposalPlaceData[] = ['id' => $disposal_place->id, 'name' => $disposal_place->name];
            if ($disposal_place->id > $lastId) {
                $lastId = $disposal_place->id;
            }
        }

        return response()->json(['data' => $disposalPlaceData, 'lastId' => $lastId]);
    }
}
