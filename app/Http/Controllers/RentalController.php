<?php

namespace App\Http\Controllers;

use App\Http\Requests\RentalCreatePost;
use App\Http\Requests\RentalDeletePost;
use App\Models\Driver;
use App\Models\Equipament;
use App\Models\EquipamentWallet;
use App\Models\Rental;
use App\Models\RentalEquipament;
use App\Models\RentalPayment;
use App\Models\RentalResidue;
use App\Models\Residue;
use App\Models\Vehicle;
use http\Env\Response;
use Illuminate\Http\Request;
use App\Models\Client;
use Illuminate\Support\Facades\DB;

class RentalController extends Controller
{
    private $client;
    private $equipament;
    private $driver;
    private $vehicle;
    private $equipament_wallet;
    private $residue;
    private $rental;
    private $rental_equipament;
    private $rental_payment;
    private $rental_residue;

    public function __construct(
        Client $client,
        Equipament $equipament,
        Driver $driver,
        Vehicle $vehicle,
        EquipamentWallet $equipament_wallet,
        Residue $residue,
        Rental $rental,
        RentalEquipament $rental_equipament,
        RentalPayment $rental_payment,
        RentalResidue $rental_residue
    )
    {
        $this->rental = $rental;
        $this->client = $client;
        $this->equipament = $equipament;
        $this->driver = $driver;
        $this->vehicle = $vehicle;
        $this->equipament_wallet = $equipament_wallet;
        $this->residue = $residue;
        $this->rental_equipament = $rental_equipament;
        $this->rental_payment = $rental_payment;
        $this->rental_residue = $rental_residue;
    }

    public function index()
    {
        if (!$this->hasPermission('RentalView')) {
            return redirect()->route('dashboard')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        return view('rental.index');
    }

    public function fetchRentals(Request $request)
    {
        if (!$this->hasPermission('RentalView'))
            return response()->json([]);

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

            $fieldsOrder = array('rentals.code','clients.name','rentals.address_name','rentals.created_at', '');
            $fieldOrder =  $fieldsOrder[$request->order[0]['column']];
            if ($fieldOrder != "") {
                $orderBy['field'] = $fieldOrder;
                $orderBy['order'] = $direction;
            }
        }

        if (!empty($searchUser)) $filtered = $this->rental->getCountRentals($company_id, $searchUser);
        else $filtered = 0;


        $data = $this->rental->getRentals($company_id, $ini, $length, $searchUser, $orderBy);

        // get string query
        // DB::getQueryLog();

        $permissionUpdate = $this->hasPermission('RentalUpdatePost');
        $permissionDelete = $this->hasPermission('RentalDeletePost');

        $i = 0;
        foreach ($data as $key => $value) {
            $i++;
            $buttons = $permissionDelete ? "<button class='btn btn-danger btnRemoveRental btn-sm btn-rounded btn-action ml-md-1' data-toggle='tooltip' title='Excluir' rental-id='{$value['id']}'><i class='fas fa-times'></i></button>" : '';

            $result[$key] = array(
                $value['code'],
                "<strong>{$value['client_name']}</strong><br>{$value['address_name']}, {$value['address_number']} - {$value['address_zipcode']} - {$value['address_neigh']} - {$value['address_city']}/{$value['address_state']}",
                date('d/m/Y H:i', strtotime($value['created_at'])),
                $buttons
            );
        }

        if ($filtered == 0) $filtered = $i;

        $output = array(
            "draw" => $draw,
            "recordsTotal" => $this->rental->getCountRentals($company_id),
            "recordsFiltered" => $filtered,
            "data" => $result
        );

        return response()->json($output);
    }

    public function delete(RentalDeletePost $request)
    {
        $company_id = $request->user()->company_id;
        $rental_id  = $request->rental_id;

        if (!$this->rental->getRental($rental_id, $company_id))
            return response()->json(['success' => false, 'message' => 'Não foi possível localizar a locação!']);

        DB::beginTransaction();// Iniciando transação manual para evitar updates não desejáveis

        $delPayment     = $this->rental_payment->remove($rental_id, $company_id);
        $delResidue     = $this->rental_residue->remove($rental_id, $company_id);
        $delEquipament  = $this->rental_equipament->remove($rental_id, $company_id);
        $delRental      = $this->rental->remove($rental_id, $company_id);

        if ($delEquipament && $delRental) {
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Locação excluída com sucesso!']);
        }

        DB::rollBack();
        return response()->json(['success' => false, 'message' => 'Não foi possível excluir a locação!']);
    }

    public function create()
    {
        if (!$this->hasPermission('RentalCreatePost')) {
            return redirect()->route('rental.index')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }

        return view('rental.create');
    }

    public function insert(RentalCreatePost $request)
    {
        if (!$this->hasPermission('RentalCreatePost')) {
            return redirect()->route('rental.index')
                ->with('warning', "Você não tem permissão para acessar essa página!");
        }
        $company_id = $request->user()->company_id;

        $haveCharged = $request->type_rental ? false : true; // true = com cobrança

        $clientId   = (int)$request->client;
        $zipcode    = $request->cep ? filter_var(preg_replace('/[^0-9]/', '', $request->cep), FILTER_SANITIZE_NUMBER_INT) : null;
        $address    = $request->address ? filter_var($request->address, FILTER_SANITIZE_STRING) : null;
        $number     = $request->number ? filter_var($request->number, FILTER_SANITIZE_STRING) : null;
        $complement = $request->complement ? filter_var($request->complement, FILTER_SANITIZE_STRING) : null;
        $reference  = $request->reference ? filter_var($request->reference, FILTER_SANITIZE_STRING) : null;
        $neigh      = $request->neigh ? filter_var($request->neigh, FILTER_SANITIZE_STRING) : null;
        $city       = $request->city ? filter_var($request->city, FILTER_SANITIZE_STRING) : null;
        $state      = $request->state ? filter_var($request->state, FILTER_SANITIZE_STRING) : null;
        $lat        = $request->lat ? filter_var($request->lat, FILTER_SANITIZE_STRING) : null;
        $lng        = $request->lng ? filter_var($request->lng, FILTER_SANITIZE_STRING) : null;

        // datas da locação
        $dateDelivery = $request->date_delivery ? \DateTime::createFromFormat('d/m/Y H:i', $request->date_delivery) : null;
        $dateWithdrawal = $request->date_withdrawal ? \DateTime::createFromFormat('d/m/Y H:i', $request->date_withdrawal) : null;
        $notUseDateWithdrawal = $request->not_use_date_withdrawal ? true : false;

        if (!$dateDelivery) // não reconheceu a data de entrega
            return response()->json(['success' => false, 'message' => "Data prevista de entrega precisa ser informada corretamente dd/mm/yyyy hh:mm."]);

        if (!$notUseDateWithdrawal) { // usará data de retirada

            if (!$dateWithdrawal) // não reconheceu a data de retirada
                return response()->json(['success' => false, 'message' => "Data prevista de retirada precisa ser informada corretamente dd/mm/yyyy hh:mm."]);

            if ($dateDelivery->getTimestamp() >= $dateWithdrawal->getTimestamp()) // data de entrega é maior ou igual a data de retirada
                return response()->json(['success' => false, 'message' => "Data prevista de entrega não pode ser maior ou igual que a data prevista de retirada."]);
        }

        // Equipamentos
        $responseEquipament = $this->setEquipamentRental($request);
        if (isset($responseEquipament->error))
            return response()->json(['success' => false, 'message' => $responseEquipament->error]);
        $arrEquipament = $responseEquipament->arrEquipament;

        // Pagamento
        $arrPayment = array();
        if ($haveCharged) {
            $responsePayment = $this->setPaymentRental($request, $responseEquipament->grossValue);
            if (isset($arrPayment->error))
                return response()->json(['success' => false, 'message' => $arrPayment->error]);

            $arrPayment = $responsePayment->arrPayment;
        }

        // Resíduo
        $arrResidue = $this->setResidueRental($request);
        if (isset($arrResidue['error']))
            return response()->json(['success' => false, 'message' => $arrResidue['error']]);

        // Locacão
        $arrRental = array(
            'code' => $this->rental->getNextCode($company_id), // get last code
            'company_id' => $company_id,
            'type_rental' => $haveCharged,
            'client_id' => $clientId,
            'address_zipcode' => $zipcode,
            'address_name' => $address,
            'address_number' => $number,
            'address_complement' => $complement,
            'address_reference' => $reference,
            'address_neigh' => $neigh,
            'address_city' => $city,
            'address_state' => $state,
            'address_lat' => $lat,
            'address_lng' => $lng,
            'expected_delivery_date' => $dateDelivery->format('Y-m-d H:i:s'),
            'expected_withdrawal_date' => $dateWithdrawal ? $dateWithdrawal->format('Y-m-d H:i:s') : null,
            'not_use_date_withdrawal' => $notUseDateWithdrawal,
            'gross_value' => $haveCharged ? $responseEquipament->grossValue : null,
            'extra_value'   => $haveCharged ? $responsePayment->extraValue : null,
            'discount_value' => $haveCharged ? $responsePayment->discountValue : null,
            'net_value' => $haveCharged ? $responsePayment->netValue : null,
            'calculate_net_amount_automatic' => $request->calculate_net_amount_automatic ? true : false,
            'use_parceled' => $request->is_parceled ? true : false,
            'automatic_parcel_distribution' => $request->automatic_parcel_distribution ? true : false,
            'observation' => strip_tags($request->observation, $this->allowableTags),
            'user_insert' => $request->user()->id
        );

        DB::beginTransaction();// Iniciando transação manual para evitar updates não desejáveis

        $insertRental = $this->rental->insert($arrRental);

        $arrEquipament = $this->addRentalIdArray($arrEquipament, $insertRental->id);
        $arrResidue = $this->addRentalIdArray($arrResidue, $insertRental->id);
        $arrPayment = $this->addRentalIdArray($arrPayment, $insertRental->id);

        $this->rental_equipament->inserts($arrEquipament);
        $this->rental_residue->inserts($arrResidue);
        if (count($arrPayment)) $this->rental_payment->inserts($arrPayment);

        if ($insertRental) {
            DB::commit();
            return response()->json(['success' => true]);
        }

        DB::rollBack();

        return response()->json(['success' => false, 'message' => 'Não foi possível gravar a locação, recarregue a página e tente novamente.']);

    }

    private function setEquipamentRental($request)
    {
        $response = new \StdClass();
        $response->arrEquipament = array();
        $response->grossValue = 0;

        $company_id = $request->user()->company_id;

        $dateDelivery = $request->date_delivery ? \DateTime::createFromFormat('d/m/Y H:i', $request->date_delivery) : null;
        $dateWithdrawal = $request->date_withdrawal ? \DateTime::createFromFormat('d/m/Y H:i', $request->date_withdrawal) : null;
        $notUseDateWithdrawal = $request->not_use_date_withdrawal ? true : false;
        $haveCharged = $request->type_rental ? false : true; // true = com cobrança

        $equipaments = $this->equipament->getEquipaments_In($company_id, $request->equipament_id);

        if (count($request->equipament_id) != count($equipaments))
            return $response->error = 'Não foram encontrados os equipamentos listado, reveja os equipamentos.';

        foreach ($equipaments as $equipamentId) {

            $stockRequest = (int)$request->{"stock_equipament_{$equipamentId->id}"};
            $stockDb = (int)$equipamentId->stock;
            $reference = $request->{"reference_equipament_{$equipamentId->id}"};
            $useDateDiff = $request->{"use_date_diff_equip_{$equipamentId->id}"} ? true : false;
            $notUseDateWithdrawalEquip = $request->{"not_use_date_withdrawal_equip_{$equipamentId->id}"} ? true : false;
            $dateDeliveryEquip = $request->{"date_delivery_equipament_{$equipamentId->id}"};
            $dateWithdrawalEquip = $request->{"date_withdrawal_equipament_{$equipamentId->id}"};
            $driverEquip = (int)$request->{"driver_{$equipamentId->id}"};
            $vehicleEquip = (int)$request->{"vehicle_{$equipamentId->id}"};
            $priceTotalEquip = $haveCharged ? $this->transformMoneyBr_En($request->{"priceTotalEquipament_{$equipamentId->id}"}) : 0;
            $unitaryValue = $haveCharged ? $equipamentId->value : 0;
            $response->grossValue += $priceTotalEquip;

            $dateDeliveryEquip = $dateDeliveryEquip ? \DateTime::createFromFormat('d/m/Y H:i', $dateDeliveryEquip) : null;
            $dateWithdrawalEquip = $dateWithdrawalEquip ? \DateTime::createFromFormat('d/m/Y H:i', $dateWithdrawalEquip) : null;

            if ($stockRequest > $stockDb)
                return $response->error = "O equipamento ( <strong>{$reference}</strong> ) não tem estoque suficiente. <strong>Disponível: {$stockDb} un</strong>";

            if ($useDateDiff) { // será utilizada uma diferente que a data da locação

                if (!$dateDeliveryEquip)
                    return $response->error = "Data prevista de entrega precisa ser informada corretamente dd/mm/yyyy hh:mm, no equipamento ( <strong>{$reference}</strong> ).";

                if (!$notUseDateWithdrawalEquip) {

                    if (!$dateWithdrawalEquip)
                        return $response->error = "Data prevista de retirada precisa ser informada corretamente dd/mm/yyyy hh:mm, no equipamento ( <strong>{$reference}</strong> ).";

                    if ($dateDeliveryEquip->getTimestamp() >= $dateWithdrawalEquip->getTimestamp())
                        return $response->error = "Data prevista de entrega não pode ser maior ou igual que a data prevista de retirada no equipamento ( <strong>{$reference}</strong> ).";

                    // diferença entre as datas
                    $dateDiff = $dateDeliveryEquip->diff($dateWithdrawalEquip);
                    // recupera valores configurados para valor unitário
                    $unitaryValue = $haveCharged ? $this->equipament_wallet->getValueWalletsEquipament($company_id, $equipamentId->id, $dateDiff->days) : 0;
                }
            } else { // será utilizada a data da locação

                $dateDeliveryEquip = $dateDelivery;
                $dateWithdrawalEquip = $dateWithdrawal;

                if (!$notUseDateWithdrawal) {
                    // diferença entre as datas
                    $dateDiff = $dateDeliveryEquip->diff($dateWithdrawalEquip);
                    // recupera valores configurados para valor unitário
                    $walletsEquipament = $this->equipament_wallet->getValueWalletsEquipament($company_id, $equipamentId->id, $dateDiff->days);
                    if ($walletsEquipament) $unitaryValue = $haveCharged ? (float)$walletsEquipament->value : 0;

                }
            }

            if (!$unitaryValue) $unitaryValue = 0;

            if ($driverEquip)
                if (!$this->driver->getDriver($driverEquip, $company_id))
                    return $response->error = "Motorista não foi encontrado no equipamento ( <strong>{$reference}</strong> ).";

            if ($vehicleEquip)
                if (!$this->vehicle->getVehicle($vehicleEquip, $company_id))
                    return $response->error = "Veículo não foi encontrado no equipamento ( <strong>{$reference}</strong> ).";

            array_push($response->arrEquipament, array(
                'company_id'                => $company_id,
                'rental_id'                 => 0,
                'equipament_id'             => $equipamentId->id,
                'reference'                 => $equipamentId->reference,
                'quantity'                  => $stockRequest,
                'unitary_value'             => $unitaryValue,
                'total_value'               => $priceTotalEquip,
                'vehicle_suggestion'        => $vehicleEquip,
                'driver_suggestion'         => $driverEquip,
                'use_date_diff_equip'       => $useDateDiff,
                'expected_delivery_date'    => $dateDeliveryEquip->format('Y-m-d H:i:s'),
                'expected_withdrawal_date'  => $dateWithdrawalEquip ? $dateWithdrawalEquip->format('Y-m-d H:i:s') : null,
                'not_use_date_withdrawal'   => $notUseDateWithdrawalEquip,
                'user_insert'               => $request->user()->id
            ));
        }

        return $response;
    }

    private function setPaymentRental($request, $grossValue)
    {
        $company_id = $request->user()->company_id;
        $response = new \StdClass();
        $response->arrPayment = array();

        $extraValue = $this->transformMoneyBr_En($request->extra_value);
        $discountValue = $this->transformMoneyBr_En($request->discount_value);
        $calculateNetAmountAutomatic = $request->calculate_net_amount_automatic ? true : false;
        if ($calculateNetAmountAutomatic) $netValue = $grossValue - $discountValue + $extraValue;
        else $netValue = $this->transformMoneyBr_En($request->net_value);

        // valores divergente
        if ($netValue != ($grossValue - $discountValue + $extraValue))
            return $response->error = 'Soma de valores divergente, recalcule os valores.';

        $is_parceled = $request->is_parceled ? true : false;
        $automaticParcelDistribution = $request->automatic_parcel_distribution ? true : false;

        if ($is_parceled) {

            $daysTemp = null;
            $priceTemp = 0;

            $valueSumParcel = 0;
            $qtyParcel = count($request->due_date);
            $valueParcel = (float)number_format($netValue / $qtyParcel, 2,'.','.');

            foreach ($request->due_date as $parcel => $_) {

                if ($automaticParcelDistribution) {

                    if(($parcel + 1) === $qtyParcel) $valueParcel = (float)number_format($netValue - $valueSumParcel,2,'.','');
                    $valueSumParcel += $valueParcel;

                } else $valueParcel = (float)$this->transformMoneyBr_En($request->value_parcel[$parcel]);


                if ($daysTemp === null) $daysTemp = $request->due_day[$parcel];
                elseif ($daysTemp >= $request->due_day[$parcel])
                    return $response->error = 'A ordem dos vencimentos devem ser informados em ordem crescente.';
                else $daysTemp = $request->due_day[$parcel];

                $priceTemp += $valueParcel;

                array_push($response->arrPayment, array(
                    'company_id'    => $company_id,
                    'rental_id'     => 0,
                    'parcel'        => $parcel + 1,
                    'due_day'       => $request->due_day[$parcel],
                    'due_date'      => $request->due_date[$parcel],
                    'due_value'     => $valueParcel,
                    'payment_id'    => 0,
                    'payment_name'  => '',
                    'user_insert'   => $request->user()->id
                ));
            }

            if (number_format($priceTemp,2, '.','') != number_format($netValue,2, '.','')) // os valores das parcelas não corresponde ao valor líquido
                return $response->error = 'A soma das parcelas deve corresponder ao valor líquido.';

        } else {
            // 1x o pagamento, vencimento para hoje
            array_push($response->arrPayment, array(
                'company_id'    => $company_id,
                'rental_id'     => 0,
                'parcel'        => 1,
                'due_day'       => 0,
                'due_date'      => date('Y-m-d'),
                'due_value'     => $netValue,
                'payment_id'    => 0,
                'payment_name'  => '',
                'user_insert'   => $request->user()->id
            ));
        }
        $response->netValue      = $netValue;
        $response->discountValue = $discountValue;
        $response->extraValue    = $extraValue;

        return $response;
    }

    private function setResidueRental($request)
    {
        if (empty($request->residues)) return [];

        $company_id = $request->user()->company_id;

        $arrResidue = array();
        $residues = $this->residue->getResidues_In($company_id, $request->residues);

        if (count($request->residues) != count($residues))
            return ['error' => 'Não foram encontrados os resíduos selecionados, reveja os resíduos.'];

        foreach ($residues as $residue) {
            array_push($arrResidue, array(
                'company_id'    => $company_id,
                'rental_id'     => 0,
                'residue_id'    => $residue->id,
                'name_residue'  => $residue->name,
                'user_insert'   => $request->user()->id
            ));
        }

        return $arrResidue;
    }

    private function addRentalIdArray(array $array, $rentalId)
    {
        foreach ($array as $key => $value)
             if (isset($value['rental_id'])) $array[$key]['rental_id'] = $rentalId;

        return $array;
    }
}
