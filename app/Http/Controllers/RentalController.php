<?php

namespace App\Http\Controllers;

use App\Http\Requests\BudgetCreatePost;
use App\Http\Requests\RentalCreatePost;
use App\Http\Requests\RentalDeletePost;
use App\Models\Address;
use App\Models\Driver;
use App\Models\Equipment;
use App\Models\EquipmentWallet;
use App\Models\Rental;
use App\Models\RentalEquipment;
use App\Models\RentalPayment;
use App\Models\RentalResidue;
use App\Models\Residue;
use App\Models\Vehicle;
use DateTime;
use http\Env\Response;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use StdClass;

class RentalController extends Controller
{
    private $client;
    private $address;
    private $equipment;
    private $driver;
    private $vehicle;
    private $equipment_wallet;
    private $residue;
    private $rental;
    private $rental_equipment;
    private $rental_payment;
    private $rental_residue;

    public function __construct(
        Client $client,
        Address $address,
        Equipment $equipment,
        Driver $driver,
        Vehicle $vehicle,
        EquipmentWallet $equipment_wallet,
        Residue $residue,
        Rental $rental,
        RentalEquipment $rental_equipment,
        RentalPayment $rental_payment,
        RentalResidue $rental_residue
    )
    {
        $this->rental = $rental;
        $this->client = $client;
        $this->address = $address;
        $this->equipment = $equipment;
        $this->driver = $driver;
        $this->vehicle = $vehicle;
        $this->equipment_wallet = $equipment_wallet;
        $this->residue = $residue;
        $this->rental_equipment = $rental_equipment;
        $this->rental_payment = $rental_payment;
        $this->rental_residue = $rental_residue;
    }

    public function index()
    {
        if (!hasPermission('RentalView')) {
            return redirect()->route('dashboard')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        $company_id = Auth::user()->__get('company_id');

        $clients = $this->client->getClients($company_id);

        return view('rental.index', compact('clients'));
    }

    public function fetchRentals(Request $request): JsonResponse
    {
        if (!hasPermission('RentalView')) {
            return response()->json();
        }

        $orderBy    = array();
        $result     = array();
        $searchUser = null;

        $filters        = [];
        $ini            = $request->input('start');
        $draw           = $request->input('draw');
        $length         = $request->input('length');
        $company_id     = $request->user()->company_id;
        $typeRental     = $request->input('type');
        // Filtro datas
        $filters['dateStart']   = $request->input('start_date');
        $filters['dateFinish']  = $request->input('end_date');
        // Filtro cliente
        $client = $request->input('client');
        if (empty($client)) $client = null;
        $filters['client'] = $client;

        $search = $request->input('search');
        if ($search['value']) $searchUser = $search['value'];

        if ($request->input('order')) {
            if ($request->input('order')[0]['dir'] == "asc") $direction = "asc";
            else $direction = "desc";

            $fieldsOrder = array('rentals.code','clients.name','rentals.created_at', '');
            $fieldOrder =  $fieldsOrder[$request->input('order')[0]['column']];
            if ($fieldOrder != "") {
                $orderBy['field'] = $fieldOrder;
                $orderBy['order'] = $direction;
            }
        }

        $data = $this->rental->getRentals($company_id, $filters, $ini, $length, $searchUser, $orderBy, $typeRental);

        $permissionUpdate = hasPermission('RentalUpdatePost');
        $permissionDelete = hasPermission('RentalDeletePost');

        foreach ($data as $key => $value) {
            $buttons = '';

            if ($permissionUpdate && in_array($typeRental, array('deliver', 'withdraw'))) {
                $btn_class = $btn_text = '';
                if ($typeRental === 'deliver') {
                    $btn_class = 'btnDeliver';
                    $btn_text = 'Entrega';
                }
                else if ($typeRental === 'withdraw') {
                    $btn_class = 'btnWithdraw';
                    $btn_text = 'Retirada'; // ou coleta?
                }
                $buttons .="<button class='dropdown-item $btn_class' data-rental-id='{$value['id']}'><i class='fas fa-check'></i> Confirmar $btn_text</button>";
                $buttons .="<a href='".route('rental.update', ['id' => $value['id']])."' class='dropdown-item'><i class='fas fa-edit'></i> Alterar Locação</a>";
            }

            $buttons .= $permissionDelete ? "<button class='dropdown-item btnRemoveRental' data-rental-id='{$value['id']}'><i class='fas fa-trash'></i> Excluir Locação</button>" : '';
            $buttons .= "<a href='".route('print.rental', ['rental' => $value['id']])."' target='_blank' class='dropdown-item'><i class='fas fa-print'></i> Imprimir Recibo</a>";

            $buttons = "<div class='row'><div class='col-12'><div class='dropdown dropleft'>
                            <button class='btn btn-outline-primary icon-btn dropdown-toggle' type='button' id='dropActionsRental-{$value['id']}' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                              <i class='fa fa-cog'></i>
                            </button>
                            <div class='dropdown-menu' aria-labelledby='dropActionsRental-{$value['id']}'>$buttons</div</div>
                        </div>";

            $expectedDeliveryDate   = null;
            $expectedWithdrawalDate = null;

            $actualDeliveryDate     = null;
            $actualWithdrawalDate   = null;

            $allDelivered = true;
            $allWithdrawn = true;

            $dataEquipment = $this->rental_equipment->getEquipments($company_id, $value->id);
            foreach ($dataEquipment as $equipment) {
                // tenta definir as variáveis iniciais, se existir dados
                if ($expectedDeliveryDate === null)   {
                    $expectedDeliveryDate = $equipment->actual_delivery_date   === null ? $equipment->expected_delivery_date   : null;
                }
                if ($expectedWithdrawalDate === null)   {
                    $expectedWithdrawalDate = $equipment->actual_withdrawal_date === null ? $equipment->expected_withdrawal_date : null;
                }

                if ($actualDeliveryDate === null)   {
                    $actualDeliveryDate = $equipment->actual_delivery_date ?? null;
                }
                if ($actualWithdrawalDate === null)   {
                    $actualWithdrawalDate = $equipment->actual_withdrawal_date ?? null;
                }

                // encontrou um equipamento que não foi entregue, a locação não ficará com data de entrega realizada
                if ($allDelivered && $equipment->actual_delivery_date === null) {
                    $allDelivered = false;
                }
                // encontrou um equipamento que não foi retirado, a locação não ficará com data de retirada realizada
                if ($allWithdrawn && $equipment->actual_withdrawal_date === null) {
                    $allWithdrawn = false;
                }

                // data prevista
                // se não foi entregue e existe data de entrega prevista, faz a comparação da data para pegar sempre a data mais antiga
                if ($equipment->actual_delivery_date === null && $expectedDeliveryDate !== null && strtotime($expectedDeliveryDate) > strtotime($equipment->expected_delivery_date)) {
                    $expectedDeliveryDate = $equipment->expected_delivery_date;
                }
                // se não foi retirado e existe data de retirada prevista, faz a comparação da data para pegar sempre a data mais antiga
                if ($equipment->actual_withdrawal_date === null && $expectedWithdrawalDate !== null && $equipment->expected_withdrawal_date !== null && strtotime($expectedWithdrawalDate) > strtotime($equipment->expected_withdrawal_date)) {
                    $expectedWithdrawalDate = $equipment->expected_withdrawal_date;
                }

                // data real
                // se ainda não encontrou um equipamento que não foi entregue e existe data de entrega, faz a comparação para pegar sempre a data mais recente
                if ($allDelivered && $actualDeliveryDate !== null && $equipment->actual_delivery_date !== null && strtotime($actualDeliveryDate) < strtotime($equipment->actual_delivery_date)) {
                    $actualDeliveryDate = $equipment->actual_delivery_date;
                }
                // se ainda não encontrou um equipamento que não foi retirado e existe data de retirada, faz a comparação para pegar sempre a data mais recente
                if ($allWithdrawn && $actualWithdrawalDate !== null && $equipment->actual_withdrawal_date !== null && strtotime($actualWithdrawalDate) > strtotime($equipment->actual_withdrawal_date)) {
                    $actualWithdrawalDate = $equipment->actual_withdrawal_date;
                }
            }

            $colorBadgeDeliveryDate     = $allDelivered ? 'success' : (strtotime($expectedDeliveryDate) < time() ? 'danger' : 'warning');
            $colorBadgeWithdrawalDate   = $allWithdrawn ? 'success' : ($expectedWithdrawalDate !== null && strtotime($expectedWithdrawalDate) < time() ? 'danger' : 'warning');

            $labelBadgeDeliveryDate     = $allDelivered ? 'Entregue em' : 'Entrega prevista em';
            $labelBadgeWithdrawalDate   = $allWithdrawn ? 'Retirada em' : 'Retirada prevista para';

            $strDateDelivery            = date('d/m/Y H:i', strtotime($allDelivered ? $actualDeliveryDate : $expectedDeliveryDate));
            $strDateWithdraw            = $expectedWithdrawalDate === null && !$allWithdrawn ? 'Não informado' : date('d/m/Y H:i', strtotime($allWithdrawn ? $actualWithdrawalDate : $expectedWithdrawalDate));

            $strDeliveryDate    = "<div class='badge badge-pill badge-lg badge-$colorBadgeDeliveryDate'>$labelBadgeDeliveryDate: $strDateDelivery</div>";
            $strWithdrawalDate  = "<div class='badge badge-pill badge-lg badge-$colorBadgeWithdrawalDate'>$labelBadgeWithdrawalDate: $strDateWithdraw</div>";

            // Se for para listar somente os "para entregar", não precisa mostrar os dados de retirada.
            if ($typeRental === 'deliver') {
                $strWithdrawalDate = '';
            }
            // Se for para listar somente os "para retirar", não precisa mostrar os dados de entrega.
            if ($typeRental === 'withdraw') {
                $strDeliveryDate = '';
            }

            $result[$key] = array(
                formatCodeRental($value['code']),
                "<div class='d-flex flex-wrap'>
                    <div class='w-100 mb-2'>
                        $strDeliveryDate
                        $strWithdrawalDate
                    </div>
                    <span class='font-weight-bold w-100'>{$value['client_name']}</span>
                    <span class='mt-1 w-100'>{$value['address_name']}, {$value['address_number']} - {$value['address_zipcode']} - {$value['address_neigh']} - {$value['address_city']}/{$value['address_state']}</span>
                </div>",
                date('d/m/Y H:i', strtotime($value['created_at'])),
                $buttons
            );
        }

        $output = array(
            "draw" => $draw,
            "recordsTotal" => $this->rental->getCountRentals($company_id, $filters, null, $typeRental),
            "recordsFiltered" => $this->rental->getCountRentals($company_id, $filters, $searchUser, $typeRental),
            "data" => $result
        );

        return response()->json($output);
    }

    public function delete(RentalDeletePost $request): JsonResponse
    {
        $company_id = $request->user()->company_id;
        $rental_id  = $request->input('rental_id');

        if (!$this->rental->getRental($company_id, $rental_id)) {
            return response()->json(['success' => false, 'message' => 'Não foi possível localizar a locação!']);
        }

        DB::beginTransaction();// Iniciando transação manual para evitar updates não desejáveis.

        $delPayment     = $this->rental_payment->remove($rental_id, $company_id);
        $delResidue     = $this->rental_residue->remove($rental_id, $company_id);
        $delEquipment   = $this->rental_equipment->remove($rental_id, $company_id);
        $delRental      = $this->rental->remove($rental_id, $company_id);

        if ($delEquipment && $delRental) {
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Locação excluída com sucesso!']);
        }

        DB::rollBack();
        return response()->json(['success' => false, 'message' => 'Não foi possível excluir a locação!']);
    }

    public function create(): Factory|View|RedirectResponse|Application
    {
        if (!hasPermission('RentalCreatePost')) {
            return redirect()->route('rental.index')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }
        $budget = false;

        return view('rental.create', compact('budget'));
    }

    public function insert(RentalCreatePost $request): JsonResponse
    {
        if (!hasPermission('RentalCreatePost')) {
            return response()->json(['success' => false, 'message' => "Você não tem permissão para criar locações."]);
        }

        $company_id  = $request->user()->company_id;
        $noCharged = $request->input('type_rental'); // 0 = Com cobrança, 1 = Sem cobrança

        $clientId   = (int)$request->input('client');
        $zipcode    = onlyNumbers($request->input('cep'));
        $address    = filter_var($request->input('address'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $number     = filter_var($request->input('number'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $complement = filter_var($request->input('complement'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $reference  = filter_var($request->input('reference'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $neigh      = filter_var($request->input('neigh'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $city       = filter_var($request->input('city'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $state      = filter_var($request->input('state'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $lat        = filter_var($request->input('lat'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $lng        = filter_var($request->input('lng'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);

        if (empty($clientId) || !$this->client->getClient($clientId, $company_id)) {
            return response()->json(['success' => false, 'message' => "Cliente não foi encontrado. Revise a aba de Cliente e Endereço."]);
        }

        if ($address == '') {
            return response()->json(['success' => false, 'message' => 'Informe um endereço. Revise a aba de Cliente e Endereço.']);
        }
        if ($number == '') {
            return response()->json(['success' => false, 'message' => 'Informe um número para o endereço. Revise a aba de Cliente e Endereço.']);
        }
        if ($neigh == '') {
            return response()->json(['success' => false, 'message' => 'Informe um bairro. Revise a aba de Cliente e Endereço.']);
        }
        if ($city == '') {
            return response()->json(['success' => false, 'message' => 'Informe uma cidade. Revise a aba de Cliente e Endereço.']);
        }
        if ($state == '') {
            return response()->json(['success' => false, 'message' => 'Informe um estado. Revise a aba de Cliente e Endereço.']);
        }
        if ($lat == '' || $lng == '') {
            return response()->json(['success' => false, 'message' => 'Confirme o endereço no mapa. Revise a aba de Cliente e Endereço.']);
        }

        // datas da locação
        $dateDelivery = $request->input('date_delivery') ? DateTime::createFromFormat('d/m/Y H:i', $request->input('date_delivery')) : null;
        $dateWithdrawal = $request->input('date_withdrawal') ? DateTime::createFromFormat('d/m/Y H:i', $request->input('date_withdrawal')) : null;
        $notUseDateWithdrawal = (bool)$request->input('not_use_date_withdrawal');

        if (!$dateDelivery) { // não reconheceu a data de entrega
            return response()->json(['success' => false, 'message' => "Data prevista de entrega precisa ser informada corretamente dd/mm/yyyy hh:mm."]);
        }

        if (!$notUseDateWithdrawal) { // usará data de retirada

            if (!$dateWithdrawal) { // não reconheceu a data de retirada
                return response()->json(['success' => false, 'message' => "Data prevista de retirada precisa ser informada corretamente dd/mm/yyyy hh:mm."]);
            }

            if ($dateDelivery->getTimestamp() >= $dateWithdrawal->getTimestamp()) { // data de entrega é maior ou igual a data de retirada
                return response()->json(['success' => false, 'message' => "Data prevista de entrega não pode ser maior ou igual que a data prevista de retirada."]);
            }
        }

        // Equipamentos
        $responseEquipment = $this->setEquipmentRental($request);
        if (isset($responseEquipment->error)) {
            return response()->json(['success' => false, 'message' => $responseEquipment->error]);
        }
        $arrEquipment = $responseEquipment->arrEquipment;

        // Pagamento
        $arrPayment = array();
        if (!$noCharged) {
            $responsePayment = $this->setPaymentRental($request, $responseEquipment->grossValue);
            if (isset($responsePayment->error)) {
                return response()->json(['success' => false, 'message' => $responsePayment->error]);
            }

            $arrPayment = $responsePayment->arrPayment;
        }

        // Resíduo
        $arrResidue = $this->setResidueRental($request);
        if (isset($arrResidue['error'])) {
            return response()->json(['success' => false, 'message' => $arrResidue['error']]);
        }

        // Locacão
        $arrRental = array(
            'code'                          => $this->rental->getNextCode($company_id), // get last code
            'company_id'                    => $company_id,
            'type_rental'                   => $noCharged,
            'client_id'                     => $clientId,
            'address_zipcode'               => $zipcode,
            'address_name'                  => $address,
            'address_number'                => $number,
            'address_complement'            => $complement,
            'address_reference'             => $reference,
            'address_neigh'                 => $neigh,
            'address_city'                  => $city,
            'address_state'                 => $state,
            'address_lat'                   => $lat,
            'address_lng'                   => $lng,
            'expected_delivery_date'        => $dateDelivery->format(DATETIME_INTERNATIONAL),
            'expected_withdrawal_date'      => $dateWithdrawal?->format(DATETIME_INTERNATIONAL),
            'not_use_date_withdrawal'       => $notUseDateWithdrawal,
            'gross_value'                   => !$noCharged ? $responseEquipment->grossValue : null,
            'extra_value'                   => !$noCharged ? $responsePayment->extraValue : null,
            'discount_value'                => !$noCharged ? $responsePayment->discountValue : null,
            'net_value'                     => !$noCharged ? $responsePayment->netValue : null,
            'calculate_net_amount_automatic'=> (bool)$request->input('calculate_net_amount_automatic'),
            'use_parceled'                  => (bool)$request->input('is_parceled'),
            'automatic_parcel_distribution' => (bool)$request->input('automatic_parcel_distribution'),
            'observation'                   => strip_tags($request->input('observation'), $this->allowableTags),
            'user_insert'                   => $request->user()->id
        );

        DB::beginTransaction();// Iniciando transação manual para evitar updates não desejáveis

        $this->updateLatLngAddressSelected($request);

        $insertRental   = $this->rental->insert($arrRental);

        $arrEquipment   = $this->addRentalIdArray($arrEquipment, $insertRental->id);
        $arrResidue     = $this->addRentalIdArray($arrResidue, $insertRental->id);
        $arrPayment     = $this->addRentalIdArray($arrPayment, $insertRental->id);

        $this->rental_equipment->inserts($arrEquipment);
        $this->rental_residue->inserts($arrResidue);
        if (count($arrPayment)) {
            $this->rental_payment->inserts($arrPayment);
        }

        if ($insertRental) {
            DB::commit();
            return response()->json(['success' => true, 'urlPrint' => route('print.rental', ['rental' => $insertRental->id]), 'code' => $insertRental->code]);
        }

        DB::rollBack();

        return response()->json(['success' => false, 'message' => 'Não foi possível gravar a locação, recarregue a página e tente novamente.']);
    }

    public function setEquipmentRental($request, bool $budget = false): string|StdClass
    {
        $nameFieldID = $budget ? 'budget_id' : 'rental_id';
        $response = new StdClass();
        $response->arrEquipment = array();
        $response->grossValue = 0;

        $company_id = $request->user()->company_id;

        $dateDelivery = $request->date_delivery ? DateTime::createFromFormat('d/m/Y H:i', $request->date_delivery) : null;
        $dateWithdrawal = $request->date_withdrawal ? DateTime::createFromFormat('d/m/Y H:i', $request->date_withdrawal) : null;
        $notUseDateWithdrawal = (bool)$request->not_use_date_withdrawal;
        $noCharged = $request->type_rental; // 0 = Com cobrança, 1 = Sem cobrança

        $equipments = $this->equipment->getEquipments_In($company_id, $request->equipment_id);

        if (count($request->equipment_id) != count($equipments)) {
            return $response->error = 'Não foram encontrados os equipamentos listado, reveja os equipamentos.';
        }

        foreach ($equipments as $equipmentId) {

            $stockRequest               = (int)$request->{"stock_equipment_$equipmentId->id"};
            $stockDb                    = (int)$equipmentId->stock;
            $reference                  = $request->{"reference_equipment_$equipmentId->id"};
            $useDateDiff                = !$budget && $request->{"use_date_diff_equip_$equipmentId->id"};
            $notUseDateWithdrawalEquip  = !$budget && $request->{"not_use_date_withdrawal_equip_$equipmentId->id"};
            $dateDeliveryEquip          = $request->{"date_delivery_equipment_$equipmentId->id"};
            $dateWithdrawalEquip        = $request->{"date_withdrawal_equipment_$equipmentId->id"};
            $driverEquip                = (int)$request->{"driver_$equipmentId->id"};
            $vehicleEquip               = (int)$request->{"vehicle_$equipmentId->id"};
            $priceTotalEquip            = !$noCharged ? transformMoneyBr_En($request->{"priceTotalEquipment_$equipmentId->id"}) : 0;
            $unitaryValue               = !$noCharged ? $equipmentId->value : 0;
            $response->grossValue       += $priceTotalEquip;

            $dateDeliveryEquip = $dateDeliveryEquip ? DateTime::createFromFormat('d/m/Y H:i', $dateDeliveryEquip) : null;
            $dateWithdrawalEquip = $dateWithdrawalEquip ? DateTime::createFromFormat('d/m/Y H:i', $dateWithdrawalEquip) : null;

            if ($stockRequest > $stockDb && !$budget) {
                return $response->error = "O equipamento ( <strong>$reference</strong> ) não tem estoque suficiente. <strong>Disponível: $stockDb un</strong>";
            }

            if ($useDateDiff && !$budget) { // será utilizada uma diferente que a data da locação

                if (!$dateDeliveryEquip) {
                    return $response->error = "Data prevista de entrega precisa ser informada corretamente dd/mm/yyyy hh:mm, no equipamento ( <strong>$reference</strong> ).";
                }

                if (!$notUseDateWithdrawalEquip) {

                    if (!$dateWithdrawalEquip) {
                        return $response->error = "Data prevista de retirada precisa ser informada corretamente dd/mm/yyyy hh:mm, no equipamento ( <strong>$reference</strong> ).";
                    }

                    if ($dateDeliveryEquip->getTimestamp() >= $dateWithdrawalEquip->getTimestamp()) {
                        return $response->error = "Data prevista de entrega não pode ser maior ou igual que a data prevista de retirada no equipamento ( <strong>$reference</strong> ).";
                    }

                    // diferença entre as datas
                    $dateDiff = $dateDeliveryEquip->diff($dateWithdrawalEquip);
                    // recupera valores configurados para valor unitário
                    $unitaryValue = !$noCharged ? $this->equipment_wallet->getValueWalletsEquipment($company_id, $equipmentId->id, $dateDiff->days) : 0;
                }
            } else { // será utilizada a data da locação

                $dateDeliveryEquip = $dateDelivery;
                $dateWithdrawalEquip = $dateWithdrawal;

                if (!$notUseDateWithdrawal && !$budget) {
                    // diferença entre as datas
                    $dateDiff = $dateDeliveryEquip->diff($dateWithdrawalEquip);
                    // recupera valores configurados para valor unitário
                    $walletsEquipment = $this->equipment_wallet->getValueWalletsEquipment($company_id, $equipmentId->id, $dateDiff->days);
                    if ($walletsEquipment) {
                        $unitaryValue = !$noCharged ? (float)$walletsEquipment->value : 0;
                    }

                }
            }

            if (!$unitaryValue) {
                $unitaryValue = 0;
            }

            if ($driverEquip) {
                if (!$this->driver->getDriver($driverEquip, $company_id)) {
                    return $response->error = "Motorista não foi encontrado no equipamento ( <strong>$reference</strong> ).";
                }
            }

            if ($vehicleEquip) {
                if (!$this->vehicle->getVehicle($vehicleEquip, $company_id)) {
                    return $response->error = "Veículo não foi encontrado no equipamento ( <strong>$reference</strong> ).";
                }
            }

            $arrEquipment = array(
                'company_id'            => $company_id,
                $nameFieldID            => 0,
                'equipment_id'          => $equipmentId->id,
                'reference'             => $equipmentId->reference,
                'name'                  => $equipmentId->name,
                'volume'                => $equipmentId->volume,
                'quantity'              => $stockRequest,
                'unitary_value'         => $unitaryValue,
                'total_value'           => $priceTotalEquip,
                'vehicle_suggestion'    => empty($vehicleEquip) ? null : $vehicleEquip,
                'driver_suggestion'     => empty($driverEquip) ? null : $driverEquip,
                'user_insert'           => $request->user()->id
            );

            $arrEquipment = array_merge($arrEquipment, array(
                'use_date_diff_equip'       => $useDateDiff,
                'expected_delivery_date'    => $dateDeliveryEquip->format(DATETIME_INTERNATIONAL),
                'expected_withdrawal_date'  => $dateWithdrawalEquip?->format(DATETIME_INTERNATIONAL),
                'not_use_date_withdrawal'   => $notUseDateWithdrawalEquip
            ));

            $response->arrEquipment[] = $arrEquipment;
        }

        return $response;
    }

    public function setPaymentRental(RentalCreatePost | BudgetCreatePost $request, $grossValue, bool $budget = false): StdClass
    {
        $nameFieldID = $budget ? 'budget_id' : 'rental_id';
        $company_id = $request->user()->company_id;
        $response = new StdClass();
        $response->arrPayment = array();

        $extraValue = 0;
        $discountValue = 0;
        $calculateNetAmountAutomatic = (bool)$request->input('calculate_net_amount_automatic');
        if ($calculateNetAmountAutomatic) { // valor liquido sera calculado automatico
            $extraValue = transformMoneyBr_En($request->input('extra_value'));
            $discountValue = transformMoneyBr_En($request->input('discount_value'));
            $netValue = $grossValue - $discountValue + $extraValue;
        } else { // valor liquido será definido pelo usuario
            $netValue = transformMoneyBr_En($request->input('net_value'));
            // devo comparar o valor liquido com o bruto para definir desconto e acrescimo
            if ($netValue > $grossValue) {
                $extraValue = $netValue - $grossValue;
            } elseif ($netValue < $grossValue) {
                $discountValue = $grossValue - $netValue;
            }
        }

        // valores divergente
        if ($netValue != ($grossValue - $discountValue + $extraValue)) {
            $response->error = 'Soma de valores divergente, recalcule os valores.';
            return $response;
        }

        $is_parceled = (bool)$request->input('is_parceled');
        $automaticParcelDistribution = (bool)$request->input('automatic_parcel_distribution');

        // existe parcelamento
        if ($is_parceled) {
            $daysTemp = null;
            $priceTemp = 0;

            $valueSumParcel = 0;
            $qtyParcel = count($request->input('due_date'));
            $valueParcel = (float)number_format($netValue / $qtyParcel, 2,'.','');

            foreach ($request->input('due_date') as $parcel => $_) {
                if ($automaticParcelDistribution) {
                    if (($parcel + 1) === $qtyParcel) {
                        $valueParcel = (float)number_format($netValue - $valueSumParcel,2,'.','');
                    }
                    $valueSumParcel += $valueParcel;

                } else {
                    $valueParcel = transformMoneyBr_En($request->input('value_parcel')[$parcel]);
                }


                if ($daysTemp === null) {
                    $daysTemp = $request->input('due_day')[$parcel];
                } elseif ($daysTemp >= $request->input('due_day')[$parcel]) {
                    $response->error = 'A ordem dos vencimentos devem ser informados em ordem crescente.';
                    return $response;
                } else {
                    $daysTemp = $request->input('due_day')[$parcel];
                }

                $priceTemp += $valueParcel;

                $response->arrPayment[] = array(
                    'company_id'    => $company_id,
                    $nameFieldID    => 0,
                    'parcel'        => $parcel + 1,
                    'due_day'       => $request->input('due_day')[$parcel],
                    'due_date'      => $request->input('due_date')[$parcel],
                    'due_value'     => $valueParcel,
                    'user_insert'   => $request->user()->id
                );
            }

            if (number_format($priceTemp,2, '.','') != number_format($netValue,2, '.','')) { // os valores das parcelas não corresponde ao valor líquido
                $response->error = 'A soma das parcelas deve corresponder ao valor líquido.';
                return $response;
            }

        } else {
            // 1x o pagamento, vencimento para hoje
            $response->arrPayment[] = array(
                'company_id'    => $company_id,
                $nameFieldID    => 0,
                'parcel'        => 1,
                'due_day'       => 0,
                'due_date'      => date(DATE_INTERNATIONAL),
                'due_value'     => $netValue,
                'user_insert'   => $request->user()->id
            );
        }
        $response->netValue      = $netValue;
        $response->discountValue = $discountValue;
        $response->extraValue    = $extraValue;

        return $response;
    }

    public function setResidueRental(object $request, bool $budget = false): array
    {
        $nameFieldID = $budget ? 'budget_id' : 'rental_id';
        if (empty($request->residues)) return [];

        $company_id = $request->user()->company_id;

        $arrResidue = array();
        $residues = $this->residue->getResidues_In($company_id, $request->residues);

        if (count($request->residues) != count($residues))
            return ['error' => 'Não foram encontrados os resíduos selecionados, reveja os resíduos.'];

        foreach ($residues as $residue) {
            $arrResidue[] = array(
                'company_id'    => $company_id,
                $nameFieldID    => 0,
                'residue_id'    => $residue->id,
                'name_residue'  => $residue->name,
                'user_insert'   => $request->user()->id
            );
        }

        return $arrResidue;
    }

    public function addRentalIdArray(array $array, int $rentalId, bool $budget = false): array
    {
        $nameFieldID = $budget ? 'budget_id' : 'rental_id';
        foreach ($array as $key => $value) {
            if (isset($value[$nameFieldID])) {
                $array[$key][$nameFieldID] = $rentalId;
            }
        }

        return $array;
    }

    public function updateLatLngAddressSelected($request): bool
    {
        if ((int)$request->name_address == 0) return false;

        $company_id = $request->user()->company_id;

        $dataUpdate = [
            'lat' => $request->lat,
            'lng' => $request->lng
        ];

        return (bool)$this->address->updateLanLngAddressClient($company_id, $request->client, $request->name_address, $dataUpdate);

    }

    public function getQtyTypeRentals(Request $request): JsonResponse
    {
        $company_id = $request->user()->company_id;
        $client     = $request->input('client');
        $start_date = $request->input('start_date');
        $end_date   = $request->input('end_date');

        $typesQuery = $this->rental->getCountTypeRentals($company_id, $client, $start_date, $end_date);

        $arrTypes = array(
            'deliver'   => $typesQuery['deliver'],
            'withdraw'  => $typesQuery['withdraw'],
            'finished'  => $typesQuery['finished']
        );

        return response()->json($arrTypes);
    }

    public function edit(int $id): Factory|View|RedirectResponse|Application
    {
        $company_id = Auth::user()->__get('company_id');
        $budget     = false;

        if (!hasPermission('RentalUpdatePost')) {
            return redirect()->route('rental.index')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        $rental             = $this->rental->getRental($company_id, $id);
        $rental_equipment   = $this->rental_equipment->getEquipments($company_id, $id);
        $rental_payment     = $this->rental_payment->getPayments($company_id, $id);
        $rental_residue     = $this->rental_residue->getResidues($company_id, $id);

        return view('rental.update', compact('budget', 'rental', 'rental_equipment', 'rental_payment', 'rental_residue'));
    }
    public function update(int $id, Request $request): Factory|View|RedirectResponse|Application
    {
        dd('Em andamento...', $request->all());
        if (!hasPermission('RentalCreatePost')) {
            return response()->json(['success' => false, 'message' => "Você não tem permissão para criar locações."]);
        }

        $company_id  = $request->user()->company_id;
        $noCharged = $request->input('type_rental'); // 0 = Com cobrança, 1 = Sem cobrança

        $clientId   = (int)$request->input('client');
        $zipcode    = onlyNumbers($request->input('cep'));
        $address    = filter_var($request->input('address'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $number     = filter_var($request->input('number'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $complement = filter_var($request->input('complement'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $reference  = filter_var($request->input('reference'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $neigh      = filter_var($request->input('neigh'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $city       = filter_var($request->input('city'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $state      = filter_var($request->input('state'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $lat        = filter_var($request->input('lat'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);
        $lng        = filter_var($request->input('lng'), FILTER_DEFAULT, FILTER_FLAG_EMPTY_STRING_NULL);

        if (empty($clientId) || !$this->client->getClient($clientId, $company_id)) {
            return response()->json(['success' => false, 'message' => "Cliente não foi encontrado. Revise a aba de Cliente e Endereço."]);
        }

        if ($address == '') {
            return response()->json(['success' => false, 'message' => 'Informe um endereço. Revise a aba de Cliente e Endereço.']);
        }
        if ($number == '') {
            return response()->json(['success' => false, 'message' => 'Informe um número para o endereço. Revise a aba de Cliente e Endereço.']);
        }
        if ($neigh == '') {
            return response()->json(['success' => false, 'message' => 'Informe um bairro. Revise a aba de Cliente e Endereço.']);
        }
        if ($city == '') {
            return response()->json(['success' => false, 'message' => 'Informe uma cidade. Revise a aba de Cliente e Endereço.']);
        }
        if ($state == '') {
            return response()->json(['success' => false, 'message' => 'Informe um estado. Revise a aba de Cliente e Endereço.']);
        }
        if ($lat == '' || $lng == '') {
            return response()->json(['success' => false, 'message' => 'Confirme o endereço no mapa. Revise a aba de Cliente e Endereço.']);
        }

        // datas da locação
        $dateDelivery = $request->input('date_delivery') ? DateTime::createFromFormat('d/m/Y H:i', $request->input('date_delivery')) : null;
        $dateWithdrawal = $request->input('date_withdrawal') ? DateTime::createFromFormat('d/m/Y H:i', $request->input('date_withdrawal')) : null;
        $notUseDateWithdrawal = (bool)$request->input('not_use_date_withdrawal');

        if (!$dateDelivery) { // não reconheceu a data de entrega
            return response()->json(['success' => false, 'message' => "Data prevista de entrega precisa ser informada corretamente dd/mm/yyyy hh:mm."]);
        }

        if (!$notUseDateWithdrawal) { // usará data de retirada

            if (!$dateWithdrawal) { // não reconheceu a data de retirada
                return response()->json(['success' => false, 'message' => "Data prevista de retirada precisa ser informada corretamente dd/mm/yyyy hh:mm."]);
            }

            if ($dateDelivery->getTimestamp() >= $dateWithdrawal->getTimestamp()) { // data de entrega é maior ou igual a data de retirada
                return response()->json(['success' => false, 'message' => "Data prevista de entrega não pode ser maior ou igual que a data prevista de retirada."]);
            }
        }

        // Equipamentos
        $responseEquipment = $this->setEquipmentRental($request);
        if (isset($responseEquipment->error)) {
            return response()->json(['success' => false, 'message' => $responseEquipment->error]);
        }
        $arrEquipment = $responseEquipment->arrEquipment;

        // Pagamento
        $arrPayment = array();
        if (!$noCharged) {
            $responsePayment = $this->setPaymentRental($request, $responseEquipment->grossValue);
            if (isset($responsePayment->error)) {
                return response()->json(['success' => false, 'message' => $responsePayment->error]);
            }

            $arrPayment = $responsePayment->arrPayment;
        }

        // Resíduo
        $arrResidue = $this->setResidueRental($request);
        if (isset($arrResidue['error'])) {
            return response()->json(['success' => false, 'message' => $arrResidue['error']]);
        }

        // Locacão
        $arrRental = array(
            'code'                          => $this->rental->getNextCode($company_id), // get last code
            'company_id'                    => $company_id,
            'type_rental'                   => $noCharged,
            'client_id'                     => $clientId,
            'address_zipcode'               => $zipcode,
            'address_name'                  => $address,
            'address_number'                => $number,
            'address_complement'            => $complement,
            'address_reference'             => $reference,
            'address_neigh'                 => $neigh,
            'address_city'                  => $city,
            'address_state'                 => $state,
            'address_lat'                   => $lat,
            'address_lng'                   => $lng,
            'expected_delivery_date'        => $dateDelivery->format(DATETIME_INTERNATIONAL),
            'expected_withdrawal_date'      => $dateWithdrawal?->format(DATETIME_INTERNATIONAL),
            'not_use_date_withdrawal'       => $notUseDateWithdrawal,
            'gross_value'                   => !$noCharged ? $responseEquipment->grossValue : null,
            'extra_value'                   => !$noCharged ? $responsePayment->extraValue : null,
            'discount_value'                => !$noCharged ? $responsePayment->discountValue : null,
            'net_value'                     => !$noCharged ? $responsePayment->netValue : null,
            'calculate_net_amount_automatic'=> (bool)$request->input('calculate_net_amount_automatic'),
            'use_parceled'                  => (bool)$request->input('is_parceled'),
            'automatic_parcel_distribution' => (bool)$request->input('automatic_parcel_distribution'),
            'observation'                   => strip_tags($request->input('observation'), $this->allowableTags),
            'user_insert'                   => $request->user()->id
        );

        DB::beginTransaction();// Iniciando transação manual para evitar updates não desejáveis

        $this->updateLatLngAddressSelected($request);

        $insertRental   = $this->rental->insert($arrRental);

        $arrEquipment   = $this->addRentalIdArray($arrEquipment, $insertRental->id);
        $arrResidue     = $this->addRentalIdArray($arrResidue, $insertRental->id);
        $arrPayment     = $this->addRentalIdArray($arrPayment, $insertRental->id);

        $this->rental_equipment->inserts($arrEquipment);
        $this->rental_residue->inserts($arrResidue);
        if (count($arrPayment)) {
            $this->rental_payment->inserts($arrPayment);
        }

        if ($insertRental) {
            DB::commit();
            return response()->json(['success' => true, 'urlPrint' => route('print.rental', ['rental' => $insertRental->id]), 'code' => $insertRental->code]);
        }

        DB::rollBack();

        return response()->json(['success' => false, 'message' => 'Não foi possível gravar a locação, recarregue a página e tente novamente.']);
    }
}
