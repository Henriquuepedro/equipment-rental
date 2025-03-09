<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyUpdatePost;
use App\Models\Company;
use App\Models\Integration;
use App\Models\IntegrationToStore;
use App\Models\Permission;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class CompanyController extends Controller
{
    private Company $company;
    private Permission $permission;
    private IntegrationToStore $integration_to_store;
    private Integration $integration;

    public function __construct()
    {
        $this->company              = new Company();
        $this->permission           = new Permission();
        $this->integration_to_store = new IntegrationToStore();
        $this->integration          = new Integration();
    }

    public function index(): View|Factory|RedirectResponse|Application
    {
        if (!hasAdmin()) {
            return redirect()->route('dashboard');
        }

        $company_id = Auth::user()->__get('company_id');

        $company = $this->company->getCompany($company_id);
        $logo_company_no_logotipo = auth()->user()->__get('style_template') == 1 ? 'assets/images/system/logotipo-horizontal-black.png' : 'assets/images/system/logotipo-horizontal-white.png';
        $company->logo = asset($company->logo ? "assets/images/company/{$company_id}/{$company->logo}" : $logo_company_no_logotipo);

        $htmlPermissions     = getFormPermission($this->permission->getAllPermissions());

        $integrations = $this->integration->getAllActive();
        $integration_to_stores = $this->integration_to_store->getByCompany($company_id);

        return view('config.index', compact('company', 'htmlPermissions', 'integrations', 'integration_to_stores'));
    }

    public function updateCompany(CompanyUpdatePost $request): RedirectResponse
    {
        $user_id    = $request->user()->id;
        $company_id = $request->user()->company_id;

        $name       = filter_var($request->input('name'));
        $fantasy    = filter_var($request->input('fantasy'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $email      = filter_var($request->input('email'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $phone_1    = filter_var(onlyNumbers($request->input('phone_1')), FILTER_SANITIZE_NUMBER_INT, FILTER_FLAG_EMPTY_STRING_NULL);
        $phone_2    = filter_var(onlyNumbers($request->input('phone_2')), FILTER_SANITIZE_NUMBER_INT, FILTER_FLAG_EMPTY_STRING_NULL);
        $contact    = filter_var($request->input('contact'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);

        $cep        = filter_var(onlyNumbers($request->input('cep')), FILTER_SANITIZE_NUMBER_INT, FILTER_FLAG_EMPTY_STRING_NULL);
        $address    = filter_var($request->input('address'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $number     = filter_var($request->input('number'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $complement = filter_var($request->input('complement'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $reference  = filter_var($request->input('reference'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $neigh      = filter_var($request->input('neigh'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $city       = filter_var($request->input('city'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $state      = filter_var($request->input('state'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);

        $arrDataCompany = array(
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
            'user_update'   => $user_id
        );

        if ($request->has('profile_logo')) {
            $uploadLogo = $this->uploadLogoCompany($company_id, $request->file('profile_logo'));
            if ($uploadLogo === false) {
                return redirect()->back()
                    ->withErrors(['Não foi possível salvar a logo enviar. Tente novamente!'])
                    ->withInput();
            }

            $arrDataCompany['logo'] = $uploadLogo;
        }

        $updateCompany = $this->company->edit($arrDataCompany, $company_id);

        if ($updateCompany) {
            return redirect()->route('config.index')
                ->with('success', "Empresa atualizada com sucesso!");
        }

        return redirect()->back()
            ->withErrors(['Não foi possível atualizar a empresa, tente novamente!'])
            ->withInput();
    }

    private function uploadLogoCompany($company_id, $file): bool|string
    {
        $uploadPath = "assets/images/company/$company_id";
        checkPathExistToCreate($uploadPath);

        $extension = $file->getClientOriginalExtension(); // Recupera extensão da imagem
        $nameOriginal = $file->getClientOriginalName(); // Recupera nome da imagem
        $imageName = base64_encode($nameOriginal); // Gera um novo nome para a imagem.
        $imageName = substr($imageName, 0, 15) . rand(0, 100) . ".{$extension}"; // Pega apenas o 15 primeiros e adiciona a extensão

        return $file->move($uploadPath, $imageName) ? $imageName : false;
    }

    public function getMyCompany(): JsonResponse
    {
        $company_id = Auth::user()->__get('company_id');

        if (!$company_id) {
            return response()->json(array('message' => 'Empresa não localizada.'), 400);
        }

        $commpany = $this->company->getCompany($company_id);

        if (!$commpany) {
            return response()->json(array('message' => 'Empresa não localizada.'), 400);
        }

        return response()->json($commpany);
    }

    public function getLatLngMyCompany(): JsonResponse
    {
        $company_id = Auth::user()->__get('company_id');

        if (!$company_id) {
            return response()->json(array('message' => 'Empresa não localizada.'), 400);
        }

        $commpany = $this->company->getCompany($company_id);

        if (!$commpany) {
            return response()->json(array('message' => 'Empresa não localizada.'), 400);
        }

        try {
            $client = new Client();
            $address = "$commpany->address - $commpany->cep - $commpany->neigh - $commpany->city/$commpany->state";
            $request = $client->get("https://dev.virtualearth.net/REST/v1/Locations?query=$address&key=" . env('VIRTUALEARTH_KEY'));
            $response = json_decode($request->getBody()->getContents());

            $coordinates = $response->resourceSets[0]->resources[0]->geocodePoints[0]->coordinates;

            $address_lat = $coordinates[0];
            $address_lng = $coordinates[1];
        } catch (Exception | ClientException | ConnectException $exception) {
            $address_lat = 0;
            $address_lng = 0;
        }

        return response()->json([
            'lat' => $address_lat,
            'lng' => $address_lng
        ]);
    }
}
