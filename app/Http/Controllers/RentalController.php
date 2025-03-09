<?php

namespace App\Http\Controllers;

use App\Http\Requests\BudgetCreatePost;
use App\Http\Requests\RentalCreatePost;
use App\Http\Requests\RentalDeletePost;
use App\Http\Requests\RentalMtrCreatePost;
use App\Models\Address;
use App\Models\Budget;
use App\Models\Driver;
use App\Models\Equipment;
use App\Models\EquipmentWallet;
use App\Models\Rental;
use App\Models\RentalEquipment;
use App\Models\RentalMtr;
use App\Models\RentalPayment;
use App\Models\RentalResidue;
use App\Models\Residue;
use App\Models\Vehicle;
use App\Traits\Validation\RentalTrait;
use DateTime;
use Exception;
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
use Twilio\Rest\Client as TwilioRestClient;

class RentalController extends Controller
{
    use RentalTrait;

    private Client $client;
    private Address $address;
    private Equipment $equipment;
    private Driver $driver;
    private Vehicle $vehicle;
    private EquipmentWallet $equipment_wallet;
    private Residue $residue;
    private Rental $rental;
    private Budget $budget;
    private RentalEquipment $rental_equipment;
    private RentalPayment $rental_payment;
    private RentalResidue $rental_residue;
    private RentalMtr $rental_mtr;

    public function __construct()
    {
        $this->client = new Client();
        $this->address = new Address();
        $this->equipment = new Equipment();
        $this->driver = new Driver();
        $this->vehicle = new Vehicle();
        $this->equipment_wallet = new EquipmentWallet();
        $this->residue = new Residue();
        $this->rental = new Rental();
        $this->budget = new Budget();
        $this->rental_equipment = new RentalEquipment();
        $this->rental_payment = new RentalPayment();
        $this->rental_residue = new RentalResidue();
        $this->rental_mtr = new RentalMtr();

        /*try {
            $sid = env('TWILIO_SID');
            $token = env('TWILIO_TOKEN');
            $client = new TwilioRestClient($sid, $token);

            // Use the Client to make requests to the Twilio REST API
            $sent = $client->messages->create(
            // The number you'd like to send the message to
                'whatsapp:+554896677961',
                [
                    // A Twilio phone number you purchased at https://console.twilio.com
                    'from' => 'whatsapp:+14155238886',
                    // The body of the text message you'd like to send
                    'body' => "Message sent by Locaí"
                ]
            );
            dd($sent);
        } catch (Exception $exception) {
            dd($exception->getMessage());
        }*/
    }

    public function index(string $filter_start_date = null, string $filter_end_date = null, string $date_filter_by = null, int $client_id = null): Factory|View|RedirectResponse|Application
    {
        if (!hasPermission('RentalView')) {
            return redirect()->route('dashboard')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        $company_id = Auth::user()->__get('company_id');

        $clients = $this->client->getClients($company_id);

        return view('rental.index', compact('clients', 'filter_start_date', 'filter_end_date', 'date_filter_by', 'client_id'));
    }

    public function fetchRentals(Request $request): JsonResponse
    {
        $draw                   = $request->input('draw');
        $company_id             = $request->user()->company_id;
        $type_rental            = $request->input('type');
        $type_to_today          = $request->input('type_to_today'); // Se '$type_rental' for 'deliver' retornar para entregar hoje, se for 'withdraw' retornar para retirar hoje.
        $response_simplified    = $request->input('response_simplified'); // Retornar somente código e cliente com endereço.
        $date_filter_by         = $request->input('date_filter_by'); // created_at, delivery, withdraw
        $no_date_to_withdraw    = $request->input('no_date_to_withdraw'); // Filtro sem data de retirada. Data nula.
        $result                 = array();

        try {
            // Filtro datas
            $filters_date['dateStart']   = $request->input('start_date');
            $filters_date['dateFinish']  = $request->input('end_date');

            // Filtro cliente
            $client = $request->input('client');

            $filters        = array();
            $filter_default = array();

            $filter_default[]['where']['rentals.company_id'] = $company_id;

            if ($type_to_today && in_array($type_rental, array('deliver', 'withdraw'))) {
                if ($type_rental == 'deliver') {
                    $filter_default[]['whereBetween']['rental_equipments.expected_delivery_date'] = ["{$filters_date['dateStart']} 00:00:00", "{$filters_date['dateFinish']} 23:59:59"];
                }
                // withdraw
                else {
                    $filter_default[][$no_date_to_withdraw ? 'where' : 'whereBetween']['rental_equipments.expected_withdrawal_date'] = $no_date_to_withdraw ? null : ["{$filters_date['dateStart']} 00:00:00", "{$filters_date['dateFinish']} 23:59:59"];
                }
            } else {
                $where_date_filter = match ($date_filter_by) {
                    'created_at'        => 'rentals.created_at',
                    'delivery'          => 'rental_equipments.actual_delivery_date',
                    'withdraw'          => 'rental_equipments.actual_withdrawal_date',
                    'expected_delivery' => 'rental_equipments.expected_delivery_date',
                    'expected_withdraw' => 'rental_equipments.expected_withdrawal_date',
                    default             => throw new Exception('Filtro de data não localizada.'),
                };

                $filter_default[][$no_date_to_withdraw ? 'where' : 'whereBetween'][$where_date_filter] = $no_date_to_withdraw ? null : ["{$filters_date['dateStart']} 00:00:00", "{$filters_date['dateFinish']} 23:59:59"];
            }

            switch ($type_rental) {
                case 'deliver':
                    $filter_default[]['where']['rental_equipments.actual_delivery_date'] = null;
                    break;
                case 'withdraw':
                    $filter_default[]['where']['rental_equipments.actual_delivery_date <>'] = null;
                    $filter_default[]['where']['rental_equipments.actual_withdrawal_date'] = null;
                    break;
                case 'finished':
                    $filter_default[]['where']['rental_equipments.actual_delivery_date <>'] = null;
                    $filter_default[]['where']['rental_equipments.actual_withdrawal_date <>'] = null;
                    break;
                default:
                    return response()->json(getErrorDataTables('Tipo de locação não localizada.', $draw));
            }

            if (!empty($client)) {
                $filters[]['where']['rentals.client_id'] = $client;
            }

            $fields_order = array(
                'rentals.code',
                [
                    'clients.name',
                    'rentals.address_name',
                    'rentals.address_name',
                    'rentals.address_number',
                    'rentals.address_zipcode',
                    'rentals.address_neigh',
                    'rentals.address_city',
                    'rentals.address_state'
                ],
                'rentals.created_at',
                ''
            );

            $query = array();
            $query['select'] = [
                'rentals.id',
                'rentals.code',
                'clients.name as client_name',
                'rentals.address_name',
                'rentals.address_number',
                'rentals.address_zipcode',
                'rentals.address_complement',
                'rentals.address_neigh',
                'rentals.address_city',
                'rentals.address_state',
                'rentals.created_at',
                'SUM(rental_equipments.quantity) as quantity_equipment'
            ];
            $query['from'] = 'rentals';
            $query['join'][] = ['clients','clients.id','=','rentals.client_id'];
            $query['join'][] = ['rental_equipments','rental_equipments.rental_id','=','rentals.id'];

            $data = fetchDataTable(
                $query,
                array('rentals.code', 'asc'),
                $type_rental === 'finished' ? 'rentals.id' : 'rental_equipments.rental_id',
                ['RentalView'],
                $filters,
                $fields_order,
                $filter_default
            );

        } catch (Exception $exception) {
            return response()->json(getErrorDataTables($exception->getMessage(), $draw));
        }

        $permissionUpdate = hasPermission('RentalUpdatePost');
        $permissionDelete = hasPermission('RentalDeletePost');
        $permissionViewMtr = hasPermission('RentalMtrView');
        $permissionCreateMtr = hasPermission('RentalMtrCreatePost');

        foreach ($data['data'] as $value) {
            if ($response_simplified) {
                $result[] = array(
                    formatCodeIndex($value->code),
                    "<div class='d-flex flex-wrap'>
                        <span class='font-weight-bold w-100'>$value->client_name</span>
                        <span class='mt-1 w-100'>$value->address_name, $value->address_number - $value->address_zipcode - $value->address_neigh - $value->address_city/$value->address_state</span>
                    </div>",
                    $value->quantity_equipment
                );
                continue;
            }

            $buttons = "<button class='dropdown-item btnViewRental' data-rental-id='$value->id'><i class='fas fa-eye'></i> Visualizar Locação</button>";

            if ($permissionUpdate && in_array($type_rental, array('deliver', 'withdraw'))) {
                $btn_class = $btn_text = '';
                if ($type_rental === 'deliver') {
                    $btn_class = 'btnDeliver';
                    $btn_text = 'Entrega';
                }
                else if ($type_rental === 'withdraw') {
                    $btn_class = 'btnWithdraw';
                    $btn_text = 'Retirada'; // ou coleta?
                }

                $buttons .= "<button class='dropdown-item $btn_class' data-rental-id='$value->id'><i class='fas fa-check'></i> Confirmar $btn_text</button>";

                $exist_equipment_exchanged = false;
                foreach ($this->rental_equipment->getEquipments($company_id, $value->id) as $equipment) {
                    if ($equipment['exchanged']) {
                        $exist_equipment_exchanged = true;
                    }
                }

                if (!$exist_equipment_exchanged) {
                    $buttons .="<a href='".route('rental.update', ['id' => $value->id])."' class='dropdown-item'><i class='fas fa-edit'></i> Alterar Locação</a>";
                }

                if ($type_rental === 'withdraw' && count($this->rental_equipment->getEquipmentToExchange($company_id, $value->id))) {
                    $buttons .="<a href='".route('rental.exchange', ['id' => $value->id])."' class='dropdown-item'><i class='fa fa fa-arrow-right-arrow-left'></i> Trocar Equipamento</a>";
                }
            }

            $buttons .= $permissionDelete ? "<button class='dropdown-item btnRemoveRental' data-rental-id='$value->id'><i class='fas fa-trash-can'></i> Excluir Locação</button>" : '';
            $buttons .= "<a href='".route('print.rental', ['rental' => $value->id])."' target='_blank' class='dropdown-item'><i class='fas fa-print'></i> Imprimir Recibo</a>";

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
                if ($equipment->actual_delivery_date !== null && $equipment->actual_withdrawal_date === null && $expectedWithdrawalDate !== null && $equipment->expected_withdrawal_date !== null && strtotime($expectedWithdrawalDate) > strtotime($equipment->expected_withdrawal_date)) {
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

            if ($allWithdrawn && $type_rental === 'finished' && ($permissionCreateMtr || $permissionViewMtr)) {
                $rental_mtr = $this->rental_mtr->getByRental($value->id, $company_id);
                if ($rental_mtr && $permissionViewMtr) {
                    $buttons .= "<a href='".route('print.generate-mtr', ['rental_mtr_id' => $rental_mtr->id])."' target='_blank' class='dropdown-item'><i class='fa fa-file-invoice'></i> Gerar MTR</a>";
                } else if (!$rental_mtr && $permissionCreateMtr) {
                    $buttons .= "<button class='dropdown-item btnShowMtr' data-rental-id='$value->id'><i class='fa fa-file-invoice'></i> Gerar MTR</button>";
                }
            }

            $buttons .= "<button class='dropdown-item btnSendOnWhatsapp' data-rental-id='$value->id'><i class='fab fa-whatsapp'></i> Enviar por Whatsapp</button>";

            $buttons = dropdownButtonsDataList($buttons, $value->id);

            $colorBadgeDeliveryDate     = $allDelivered ? 'success' : (strtotime($expectedDeliveryDate) < time() ? 'danger' : 'warning');
            $colorBadgeWithdrawalDate   = $allWithdrawn ? 'success' : ($expectedWithdrawalDate !== null && strtotime($expectedWithdrawalDate) < time() ? 'danger' : 'warning');

            $labelBadgeDeliveryDate     = $allDelivered ? 'Entregue em' : 'Entrega prevista em';
            $labelBadgeWithdrawalDate   = $allWithdrawn ? 'Retirada em' : 'Retirada prevista para';

            $strDateDelivery            = date(DATETIME_BRAZIL_NO_SECONDS, strtotime($allDelivered ? $actualDeliveryDate : $expectedDeliveryDate));
            $strDateWithdraw            = $expectedWithdrawalDate === null && !$allWithdrawn ? 'Não informado' : date(DATETIME_BRAZIL_NO_SECONDS, strtotime($allWithdrawn ? $actualWithdrawalDate : $expectedWithdrawalDate));

            $strDeliveryDate    = "<div class='badge badge-pill badge-lg badge-$colorBadgeDeliveryDate'>$labelBadgeDeliveryDate: $strDateDelivery</div>";
            $strWithdrawalDate  = "<div class='badge badge-pill badge-lg badge-$colorBadgeWithdrawalDate'>$labelBadgeWithdrawalDate: $strDateWithdraw</div>";

            // Se for para listar somente os "para entregar", não precisa mostrar os dados de retirada.
            if ($type_rental === 'deliver') {
                $strWithdrawalDate = '';
            }
            // Se for para listar somente os "para retirar", não precisa mostrar os dados de entrega.
            if ($type_rental === 'withdraw') {
                $strDeliveryDate = '';
            }

            $result[] = array(
                formatCodeIndex($value->code),
                "<div class='d-flex flex-wrap'>
                    <div class='w-100 mb-2'>
                        $strDeliveryDate
                        $strWithdrawalDate
                    </div>
                    <span class='font-weight-bold w-100'>$value->client_name</span>
                    <span class='mt-1 w-100'>$value->address_name, $value->address_number - $value->address_zipcode - $value->address_neigh - $value->address_city/$value->address_state</span>
                </div>",
                date(DATETIME_BRAZIL_NO_SECONDS, strtotime($value->created_at)),
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
        $delEquipment   = $this->rental_equipment->remove($company_id, $rental_id);
        $delRental      = $this->rental->remove($company_id, $rental_id);

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

        try {
            $data_validation = $this->makeValidationRental($request, false);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }

        // Locacão.
        $arrRental      = $data_validation['rental'];
        $arrEquipment   = $data_validation['arrEquipment'];
        $arrResidue     = $data_validation['arrResidue'];
        $arrPayment     = $data_validation['arrPayment'];

        DB::beginTransaction();// Iniciando transação manual para evitar updates não desejáveis.

        $this->updateLatLngAddressSelected($request);

        $insertRental   = $this->rental->insert($arrRental);

        $arrEquipment   = $this->addRentalIdArray($arrEquipment, $insertRental->id);
        $arrResidue     = $this->addRentalIdArray($arrResidue, $insertRental->id);
        $arrPayment     = $this->addRentalIdArray($arrPayment, $insertRental->id);
        $payment_today  = null;

        $this->rental_equipment->inserts($arrEquipment);
        $this->rental_residue->inserts($arrResidue);
        if (count($arrPayment)) {
            $this->rental_payment->inserts($arrPayment);
        }

        if ($insertRental) {
            DB::commit();

            foreach ($arrPayment as $payment) {
                if (strtotime($payment['due_date']) === strtotime(dateNowInternational(null, DATE_INTERNATIONAL))) {
                    $payment_today = $this->rental_payment->getPaymentByRentalAndDate($request->user()->company_id, $insertRental->id, $payment['due_date']);
                    break;
                }
            }

            return response()->json(['success' => true, 'urlPrint' => route('print.rental', ['rental' => $insertRental->id]), 'code' => $insertRental->code, 'payment_today' => $payment_today]);
        }

        DB::rollBack();

        return response()->json(['success' => false, 'message' => 'Não foi possível gravar a locação, recarregue a página e tente novamente.']);
    }

    public function setEquipmentRental($request, bool $budget = false, ?int $rental_id = null, bool $exchange_equipment_id = false): string|StdClass
    {
        $nameFieldID = $budget ? 'budget_id' : 'rental_id';
        $response = new StdClass();
        $response->arrEquipment = array();

        $company_id = $request->user()->company_id;

        $dateDelivery = $request->date_delivery ? DateTime::createFromFormat(DATETIME_BRAZIL_NO_SECONDS, $request->date_delivery) : null;
        $dateWithdrawal = $request->date_withdrawal ? DateTime::createFromFormat(DATETIME_BRAZIL_NO_SECONDS, $request->date_withdrawal) : null;
        $notUseDateWithdrawal = (bool)$request->not_use_date_withdrawal;
        $noCharged = $request->type_rental; // 0 = Com cobrança, 1 = Sem cobrança
        $total_rental_paid = transformMoneyBr_En($request->input('total_rental_paid'));
        $total_rental_no_paid = transformMoneyBr_En($request->input('total_rental_no_paid'));
        $extraValue = transformMoneyBr_En($request->input('extra_value'));
        $discountValue = transformMoneyBr_En($request->input('discount_value'));
        $response->grossValue = 0;
        if (!$noCharged) {
            if ($request->input('is_exchange')) {
                $response->grossValue += $total_rental_no_paid + $total_rental_paid + $discountValue - $extraValue;
            }
        }

        $equipments = $this->equipment->getEquipments_In($company_id, $request->equipment_id);

        if (count($request->equipment_id) != count($equipments)) {
            return $response->error = 'Não foram encontrados os equipamentos listado, reveja os equipamentos.';
        }

        foreach ($equipments as $equipmentId) {
            $stockRequest                       = (int)$request->{"stock_equipment_$equipmentId->id"};
            $stockDb                            = (int)$equipmentId->stock;
            $reference                          = $request->{"reference_equipment_$equipmentId->id"};
            $useDateDiff                        = !$budget && $request->{"use_date_diff_equip_$equipmentId->id"};
            $notUseDateWithdrawalEquip          = !$budget && $request->{"not_use_date_withdrawal_equip_$equipmentId->id"};
            $dateDeliveryEquip                  = $request->{"date_delivery_equipment_$equipmentId->id"};
            $dateWithdrawalEquip                = $request->{"date_withdrawal_equipment_$equipmentId->id"};
            $driverEquip                        = (int)$request->{"driver_$equipmentId->id"};
            $vehicleEquip                       = (int)$request->{"vehicle_$equipmentId->id"};
            $priceTotalEquip                    = !$noCharged ? transformMoneyBr_En($request->{"priceTotalEquipment_$equipmentId->id"}) : 0;
            $unitaryValue                       = !$noCharged ? (float)$equipmentId->value : 0;
            $response->grossValue               += $priceTotalEquip;
            $dateWithdrawalEquipmentActual      = $request->{"date_withdrawal_equipment_actual_$equipmentId->id"} ?? null;
            $withdrawalEquipmentActual          = $request->{"withdrawal_equipment_actual_$equipmentId->id"} ?? null;
            $withdrawalEquipmentActualVehicle   = $request->{"withdrawal_equipment_actual_vehicle_$equipmentId->id"} ?? null;
            $withdrawalEquipmentActualDriver    = $request->{"withdrawal_equipment_actual_driver_$equipmentId->id"} ?? null;

            $dateDeliveryEquip = $dateDeliveryEquip ? DateTime::createFromFormat(DATETIME_BRAZIL_NO_SECONDS, $dateDeliveryEquip) : null;
            $dateWithdrawalEquip = $dateWithdrawalEquip ? DateTime::createFromFormat(DATETIME_BRAZIL_NO_SECONDS, $dateWithdrawalEquip) : null;

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
                    $unitaryValue = 0;

                    if (!$noCharged) {
                        $valueWalletsEquipment = $this->equipment_wallet->getValueWalletsEquipment($company_id, $equipmentId->id, $dateDiff->days);
                        if ($valueWalletsEquipment) {
                            $unitaryValue = (float)$valueWalletsEquipment->value;
                        }
                    }
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
                'company_id'                        => $company_id,
                $nameFieldID                        => $rental_id ?: 0,
                'equipment_id'                      => $equipmentId->id,
                'reference'                         => $equipmentId->reference,
                'name'                              => $equipmentId->name,
                'volume'                            => $equipmentId->volume,
                'quantity'                          => $stockRequest,
                'unitary_value'                     => $unitaryValue,
                'total_value'                       => $priceTotalEquip,
                'vehicle_suggestion'                => empty($vehicleEquip) ? null : $vehicleEquip,
                'driver_suggestion'                 => empty($driverEquip) ? null : $driverEquip,
                'withdrawalEquipmentActual'         => $withdrawalEquipmentActual,
                'dateWithdrawalEquipmentActual'     => $dateWithdrawalEquipmentActual,
                'withdrawalEquipmentActualVehicle'  => $withdrawalEquipmentActualVehicle,
                'withdrawalEquipmentActualDriver'   => $withdrawalEquipmentActualDriver,
                'user_insert'                       => $request->user()->id
            );

            if ($exchange_equipment_id) {
                $arrEquipment['exchange_rental_equipment_id'] = (int)$request->{"rental_equipment_id_$equipmentId->id"};
            }

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

    public function setPaymentRental($request, $grossValue, bool $budget = false, ?int $rental_id = null): StdClass
    {
        $nameFieldID = $budget ? 'budget_id' : 'rental_id';
        $company_id = $request->user()->company_id;
        $response = new StdClass();
        $response->arrPayment = array();

        $extraValue = 0;
        $discountValue = 0;
        $total_rental_paid = 0;

        $calculateNetAmountAutomatic = (bool)$request->input('calculate_net_amount_automatic');
        if ($calculateNetAmountAutomatic) { // valor liquido sera calculado automático
            $total_rental_paid = transformMoneyBr_En($request->input('total_rental_paid'));
            $extraValue = transformMoneyBr_En($request->input('extra_value'));
            $discountValue = transformMoneyBr_En($request->input('discount_value'));
            $netValue = $grossValue - $discountValue + $extraValue;
        } else { // valor líquido será definido pelo usuário
            $netValue = transformMoneyBr_En($request->input('net_value'));
            // devo comparar o valor liquido com o bruto para definir desconto e acré scimo
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

        $automaticParcelDistribution = (bool)$request->input('automatic_parcel_distribution');

        $priceTemp = $total_rental_paid;

        $valueSumParcel = 0;
        $qtyParcel = count($request->input('due_date'));
        $valueParcel = (float)number_format($netValue / $qtyParcel, 2,'.','');

        foreach ($request->input('due_date') as $parcel => $_) {
            if ($automaticParcelDistribution) {
                if (($parcel + 1) === $qtyParcel) {
                    $valueParcel = (float)number_format(($netValue - $total_rental_paid) - $valueSumParcel,2,'.','');
                }
                $valueSumParcel += $valueParcel;
            } else {
                $valueParcel = transformMoneyBr_En($request->input('value_parcel')[$parcel]);
            }

            $priceTemp += $valueParcel;

            $response->arrPayment[] = array(
                'company_id'    => $company_id,
                $nameFieldID    => $rental_id ?: 0,
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

        // Pagamento não encontrado, cria o pagamento para o dia de hoje.
        if (!count($response->arrPayment)) {
            $response->arrPayment[] = array(
                'company_id'    => $company_id,
                $nameFieldID    => $rental_id ?: 0,
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

    public function setResidueRental(object $request, bool $budget = false, ?int $rental_id = null): array
    {
        $nameFieldID = $budget ? 'budget_id' : 'rental_id';
        if (empty($request->residues)) {
            return [];
        }

        $company_id = $request->user()->company_id;

        $arrResidue = array();
        $residues = $this->residue->getResidues_In($company_id, $request->residues);

        if (count($request->residues) != count($residues)) {
            return ['error' => 'Não foram encontrados os resíduos selecionados, reveja os resíduos.'];
        }

        foreach ($residues as $residue) {
            $arrResidue[] = array(
                'company_id'    => $company_id,
                $nameFieldID    => $rental_id ?: 0,
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
        if ((int)$request->name_address == 0) {
            return false;
        }

        $company_id = $request->user()->company_id;

        $dataUpdate = [
            'lat' => $request->lat,
            'lng' => $request->lng
        ];

        return (bool)$this->address->updateLanLngAddressClient($company_id, $request->client, $request->name_address, $dataUpdate);

    }

    public function getQtyTypeRentals(Request $request): JsonResponse
    {
        $company_id             = $request->user()->company_id;
        $client                 = $request->input('client');
        $start_date             = $request->input('start_date');
        $end_date               = $request->input('end_date');
        $date_filter_by         = $request->input('date_filter_by');
        $no_date_to_withdraw    = (bool)$request->input('no_date_to_withdraw'); // Filtro sem data de retirada. Data nula.

        $typesQuery = $this->rental->getCountTypeRentals($company_id, $client, $start_date, $end_date, $date_filter_by, $no_date_to_withdraw);

        $arrTypes = array(
            'deliver'   => $typesQuery['deliver'],
            'withdraw'  => $typesQuery['withdraw'],
            'finished'  => $typesQuery['finished']
        );

        return response()->json($arrTypes);
    }

    public function edit(int $id): Factory|View|RedirectResponse|Application
    {
        if (!hasPermission('RentalUpdatePost')) {
            return redirect()->route('rental.index')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        $company_id     = Auth::user()->__get('company_id');
        $budget         = false;
        $rental         = $this->rental->getRental($company_id, $id);
        $rental_residue = $this->rental_residue->getResidues($company_id, $id);

        if (!$rental) {
            return redirect()->route('rental.index')
                ->with('warning', "Locação não encontrada!");
        }

        foreach ($this->rental_equipment->getEquipments($company_id, $id) as $equipment) {
            if ($equipment['exchanged']) {
                return redirect()->route('rental.index')
                    ->with('warning', "Locação contém equipamentos trocados, não é mais permitido realizar alterações.");
            }
        }

        return view('rental.update', compact('budget', 'rental', 'rental_residue'));
    }

    public function update(int $id, RentalCreatePost $request): JsonResponse
    {
        if (!hasPermission('RentalUpdatePost')) {
            return response()->json(['success' => false, 'message' => "Você não tem permissão para atualizar locações."]);
        }

        $company_id = $request->user()->company_id;
        // Define os dados para ser usado na Trait.
        $this->setDataRental($this->rental->getRental($company_id, $id));
        $this->setDataRentalEquipment($this->rental_equipment->getEquipments($company_id, $id));
        $this->setDataRentalPayment($this->rental_payment->getPayments($company_id, $id));

        if (!$this->getDataRental()) {
            return response()->json(['success' => false, 'message' => "Locação não encontrada."]);
        }

        DB::beginTransaction();// Iniciando transação manual para evitar updates não desejáveis.

        try {
            // Faz as validações iniciais padrões para poder seguir com a atualização.
            $data_validation = $this->makeValidationRental($request, $id);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }

        // Locacão.
        $arrRental      = $data_validation['rental'];
        $arrEquipment   = $data_validation['arrEquipment'];
        $arrResidue     = $data_validation['arrResidue'];
        $arrPayment     = $data_validation['arrPayment'];

        // remove o campo 'code' da atualização.
        unset($arrRental['code']);

        try {
            // Faz as validações para atualizar a locação.
            $validate_payment_equipment = $this->makeValidationToUpdate($request, $arrRental, $arrEquipment, $arrPayment);
            $create_payment     = !$validate_payment_equipment['payment'];
            $create_equipment   = !$validate_payment_equipment['equipment'];
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }

        if ($create_equipment || $create_payment) {
            if ($request->has('confirm_update_equipment_or_payment') && !$request->input('confirm_update_equipment_or_payment')) {
                $show_alert_equipment   = count($this->rental_equipment->getEquipmentInProgressByRental($company_id, $id)) > 0;
                $show_alert_payment     = count($this->rental_payment->getPaymentsPaidByRental($company_id, $id)) > 0;

                // Se já tinha algum equipamento entregue/retirado ou pagamento pago, precisa mostrar o alerta.
                if ($show_alert_equipment || $show_alert_payment) {
                    DB::rollBack();
                    return response()->json(['success' => true, 'message' => null, 'show_alert_update_equipment_or_payment' => array(
                        'equipment' => $create_equipment && $show_alert_equipment,
                        'payment'   => $create_payment && $show_alert_payment
                    )]);
                }
            }
        }

        // Se deve criar os equipamentos novamente, significa os valores de entrega e retirada devem voltar a ser nulos.
        if ($create_equipment) {
            $arrRental['actual_delivery_date'] = null;
            $arrRental['actual_withdrawal_date'] = null;
        }

        $updateRental = $this->rental->updateByRentalAndCompany($company_id, $id, $arrRental);

        // Remove os equipamentos e cria novamente.
        if ($create_equipment) {
            $this->rental_equipment->remove($company_id, $id);
            $this->rental_equipment->inserts($arrEquipment);
        }
        // Remove os pagamento e cria novamente.
        if ($create_payment && count($arrPayment)) {
            $this->rental_payment->remove($id, $company_id);
            $this->rental_payment->inserts($arrPayment);
        }

        // Remove os resíduos para serem criados novamente.
        $this->rental_residue->remove($id, $company_id);
        $this->rental_residue->inserts($arrResidue);

        if ($updateRental) {
            DB::commit();
            return response()->json(['success' => true, 'urlPrint' => route('print.rental', ['rental' => $this->dataRental->id]), 'code' => $this->dataRental->code]);
        }

        DB::rollBack();

        return response()->json(['success' => false, 'message' => 'Não foi possível gravar a locação, recarregue a página e tente novamente.']);
    }

    public function exchange(int $id): Factory|View|RedirectResponse|Application
    {
        if (!hasPermission('RentalUpdatePost')) {
            return redirect()->route('rental.index')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }
        $company_id = Auth::user()->__get('company_id');

        $exist_equipment_to_exchange = false;
        foreach ($this->rental_equipment->getEquipments($company_id, $id) as $equipment) {
            if ($equipment['actual_delivery_date'] != null && $equipment['actual_withdrawal_date'] == null && !$equipment['exchanged']) {
                $exist_equipment_to_exchange = true;
            }
        }

        if (!$exist_equipment_to_exchange) {
            return redirect()->route('rental.index')
                ->with('warning', "Não existem equipamentos para trocar!");
        }

        $rental = $this->rental->getRental($company_id, $id);

        return view('rental.exchange', compact('rental'));
    }

    public function exchangePost(int $id, Request $request): JsonResponse
    {
        if (!hasPermission('RentalUpdatePost')) {
            return response()->json(['success' => false, 'message' => "Você não tem permissão para atualizar locações."]);
        }

        $company_id = $request->user()->company_id;
        // Define os dados para ser usado na Trait.
        $this->setDataRental($this->rental->getRental($company_id, $id));
        $this->setDataRentalEquipment($this->rental_equipment->getEquipments($company_id, $id));
        $this->setDataRentalPayment($this->rental_payment->getPayments($company_id, $id));

        if (!$this->getDataRental()) {
            return response()->json(['success' => false, 'message' => "Locação não encontrada."]);
        }

        DB::beginTransaction();// Iniciando transação manual para evitar updates não desejáveis.

        try {
            // Faz as validações iniciais padrões para poder seguir com a atualização.
            $data_validation = $this->makeValidationRentalToExchange($request, $id);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }

        // Locacão.
        $arrEquipment    = $data_validation['arrEquipment'];
        $arrPayment      = $data_validation['arrPayment'];

        $arrRental = [
            'actual_delivery_date'   => null,
            'actual_withdrawal_date' => null
        ];

        // Com cobrança.
        if ($this->getDataRental('type_rental') == 0) {
            $total_new_value = array_sum(array_column($arrPayment,'due_value')) + transformMoneyBr_En($request->input('total_rental_paid'));

            $extra_value = transformMoneyBr_En($request->input('extra_value'));
            $discount_value = transformMoneyBr_En($request->input('discount_value'));
            $arrRental['automatic_parcel_distribution'] = false;
            $arrRental['net_value'] = $total_new_value;
            $arrRental['gross_value'] = $total_new_value - $extra_value + $discount_value;
            $arrRental['extra_value'] = $extra_value;
            $arrRental['discount_value'] = $discount_value;
        }

        // O valor de entrega deve voltar a ser nulo.
        $updateRental = $this->rental->updateByRentalAndCompany($company_id, $id, $arrRental);

        // Cria os novos equipamento e pagamentos.
        $this->rental_equipment->inserts($arrEquipment);
        $this->rental_payment->removeByPaid($company_id, $id);
        $this->rental_payment->inserts($arrPayment);

        // Coloca todos os equipamentos como já trocados.
        foreach ($arrEquipment as $equipment) {
            $withdrawalEquipmentActual = $equipment['withdrawalEquipmentActual'] ?? null;
            $dateWithdrawalEquipmentActual = $equipment['dateWithdrawalEquipmentActual'] ?? null;
            $withdrawalEquipmentActualVehicle = $equipment['withdrawalEquipmentActualVehicle'] ?? null;
            $withdrawalEquipmentActualDriver = $equipment['withdrawalEquipmentActualDriver'] ?? null;

            // remove os campos que não podem ir para a criação.
            unset($equipment['withdrawalEquipmentActual']);
            unset($equipment['dateWithdrawalEquipmentActual']);
            unset($equipment['withdrawalEquipmentActualVehicle']);
            unset($equipment['withdrawalEquipmentActualDriver']);

            if ($withdrawalEquipmentActual && $dateWithdrawalEquipmentActual && ($equipment['exchange_rental_equipment_id'] ?? false)) {
                $equipment_actual = $equipment['exchange_rental_equipment_id'];

                $updateWithdrawal = array(
                    'actual_withdrawal_date'    => dateBrazilToDateInternational($dateWithdrawalEquipmentActual),
                    'actual_driver_withdrawal'  => $withdrawalEquipmentActualDriver,
                    'actual_vehicle_withdrawal' => $withdrawalEquipmentActualVehicle
                );

                if (!$this->rental_equipment->updateByRentalAndRentalEquipmentId($id, $equipment_actual, $updateWithdrawal)) {
                    DB::rollBack();
                    return response()->json(array(
                        'success' => false,
                        'message' => "Não foi atualizar o equipamento atual para retirado."
                    ));
                }
            }

            $this->rental_equipment->updateByRentalAndRentalEquipmentId($id, $equipment['exchange_rental_equipment_id'], array('exchanged' => true));
        }

        if ($updateRental) {
            DB::commit();
            return response()->json(['success' => true, 'urlPrint' => route('print.rental', ['rental' => $this->dataRental->id]), 'code' => $this->dataRental->code]);
        }

        DB::rollBack();

        return response()->json(['success' => false, 'message' => 'Não foi possível gravar a locação, recarregue a página e tente novamente.']);
    }

    public function getFull(int $rental_id): JsonResponse
    {
        $company_id = Auth::user()->__get('company_id');

        return response()->json($this->rental->getRentalFull($company_id, $rental_id));
    }

    public function getRentalsForMonths(int $months): JsonResponse
    {
        if (!hasPermission('RentalView')) {
            return response()->json();
        }

        $response_months = array();
        $company_id = Auth::user()->__get('company_id');

        for ($month = $months; $month > 0; $month--) {
            $year_month = date('Y-m', strtotime(subDate(dateNowInternational(), null, ($month - 1))));
            $exp_year_month = explode('-', $year_month);

            $response_months[SHORT_MONTH_NAME_PT[$exp_year_month[1]] . '/' . substr($exp_year_month[0], 2, 4)] = $this->rental->getRentalsForMonth($company_id, $exp_year_month[0], $exp_year_month[1]);
        }

        return response()->json($response_months);
    }

    public function getRentalsForDateAndClient(string $date, string $type = null): JsonResponse
    {
        if (!hasPermission('RentalView')) {
            return response()->json();
        }

        $company_id = Auth::user()->__get('company_id');

        return response()->json(
            array_map(function($payment) {
                $payment['total'] = roundDecimal($payment['total']);
                return $payment;
            },
                $this->rental_equipment->getRentalClientByDate($company_id, $date, $type)->toArray()
            )
        );
    }

    public function getRentalsOpen(): JsonResponse
    {
        if (!hasPermission('RentalView')) {
            return response()->json();
        }

        $company_id = Auth::user()->__get('company_id');

        $rentals = $this->rental->getRentalsOpen($company_id);
        return response()->json($rentals);
    }
}
