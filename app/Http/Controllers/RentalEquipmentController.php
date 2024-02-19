<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use DateTime;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\RentalEquipment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RentalEquipmentController extends Controller
{
    private RentalEquipment$rental_equipment;
    private Rental $rental;

    public function __construct()
    {
        $this->rental_equipment = new RentalEquipment();
        $this->rental = new Rental();
    }

    public function getEquipmentsRental(int $rental_id): JsonResponse
    {
        if (!hasPermission('RentalUpdatePost')) {
            return response()->json();
        }

        $company_id = Auth::user()->__get('company_id');

        $equipments = $this->rental_equipment->getEquipments($company_id, $rental_id);

        return response()->json($equipments);
    }

    public function getEquipmentsRentalToDeliver(Request $request): JsonResponse
    {
        $company_id = $request->user()->company_id;
        $rental_id  = $request->input('rental_id');

        $equipments = $this->rental_equipment->getEquipmentsForDeliver($company_id, $rental_id);

        if (count($equipments) === 0) {
            return response()->json(['success' => false, 'data' => 'Não foi possível localizar os equipamentos para entrega.']);
        }

        return response()->json(['success' => true, 'data' => $equipments]);
    }

    public function getEquipmentsRentalToWithdraw(Request $request): JsonResponse
    {
        $company_id = $request->user()->company_id;
        $rental_id  = $request->input('rental_id');

        $equipments = $this->rental_equipment->getEquipmentsForWithdraw($company_id, $rental_id);

        if (count($equipments) === 0) {
            return response()->json(['success' => false, 'data' => 'Não foi possível localizar os equipamentos para entrega.']);
        }

        return response()->json(['success' => true, 'data' => $equipments]);
    }

    public function deliverEquipment(Request $request): JsonResponse
    {
        $company_id             = $request->user()->company_id;
        $checked                = $request->input('checked');
        $date_deliver           = $request->input('date');
        $drivers                = $request->input('drivers');
        $vechicles              = $request->input('vechicles');
        $rental_equipments_id   = $request->input('rental_equipment_id');
        $rental_id              = $request->input('rental_id')[0] ?? 0;

        if (!$checked) {
            return response()->json(array(
                'success' => false,
                'message' => "Nenhum equipamento selecionado."
            ));
        }

        if (!$this->rental->getRental($company_id, $rental_id)) {
            return response()->json(array(
                'success' => false,
                'message' => "Não foi possível localizar a locação."
            ));
        }

        if (count($date_deliver) !== count($drivers) || count($date_deliver) !== count($vechicles)) {
            return response()->json(array(
                'success' => false,
                'message' => "Não foi possível realizar a leitura."
            ));
        }

        $datas_update = array();
        for ($count = 0; $count < count($date_deliver); $count++) {
            if (!in_array((string)$count, $checked)) {
                continue;
            }

            if (empty($drivers[$count]) || empty($vechicles[$count])) {
                return response()->json(array(
                    'success' => false,
                    'message' => "Informe o veículo e motorista de todos os equipamentos para realizar a entrega."
                ));
            }

            $date_deliver_equipment = DateTime::createFromFormat(DATETIME_BRAZIL_NO_SECONDS, $date_deliver[$count]);

            $datas_update[] = array(
                'actual_delivery_date'      => $date_deliver_equipment,
                'actual_driver_delivery'    => $drivers[$count],
                'actual_vehicle_delivery'   => $vechicles[$count],
                'rental_equipment_id'       => $rental_equipments_id[$count]
            );
        }

        if (!count($datas_update)) {
            return response()->json(array(
                'success' => false,
                'message' => "Não foi selecionado nenhum equipamento."
            ));
        }

        DB::beginTransaction();

        foreach ($datas_update as $data_update) {
            $rental_equipment_id = $data_update['rental_equipment_id'];
            unset($data_update['rental_equipment_id']);

            if (!$this->rental_equipment->updateByRentalAndRentalEquipmentId($rental_id, $rental_equipment_id, $data_update)) {
                DB::rollBack();
                return response()->json(array(
                    'success' => false,
                    'message' => "Não foi atualizar o equipamento $rental_equipment_id."
                ));
            }
        }

        $rental_updated = $this->rental->checkAllEquipmentsDelivered($company_id, $rental_id);

        DB::commit();

        return response()->json(array(
            'success'        => true,
            'message'        => "Equipamento atualizado.",
            'rental_updated' => $rental_updated
        ));
    }

    public function withdrawEquipment(Request $request): JsonResponse
    {
        $company_id             = $request->user()->company_id;
        $checked                = $request->input('checked');
        $date_withdraw          = $request->input('date');
        $drivers                = $request->input('drivers');
        $vechicles              = $request->input('vechicles');
        $rental_equipments_id   = $request->input('rental_equipment_id');
        $rental_id              = $request->input('rental_id')[0] ?? 0;

        if (!$checked) {
            return response()->json(array(
                'success' => false,
                'message' => "Nenhum equipamento selecionado."
            ));
        }

        if (!$this->rental->getRental($company_id, $rental_id)) {
            return response()->json(array(
                'success' => false,
                'message' => "Não foi possível localizar a locação."
            ));
        }

        if (count($date_withdraw) !== count($drivers) || count($date_withdraw) !== count($vechicles)) {
            return response()->json(array(
                'success' => false,
                'message' => "Não foi possível realizar a leitura."
            ));
        }

        $datas_update = array();
        for ($count = 0; $count < count($date_withdraw); $count++) {
            if (!in_array((string)$count, $checked)) {
                continue;
            }

            if (empty($drivers[$count]) || empty($vechicles[$count])) {
                return response()->json(array(
                    'success' => false,
                    'message' => "Informe o veículo e motorista de todos os equipamentos para realizar a retirada."
                ));
            }

            $date_withdraw_equipment = dateBrazilToDateInternational($date_withdraw[$count].':00');

            $datas_update[] = array(
                'actual_withdrawal_date'    => $date_withdraw_equipment,
                'actual_driver_withdrawal'  => $drivers[$count],
                'actual_vehicle_withdrawal' => $vechicles[$count],
                'rental_equipment_id'       => $rental_equipments_id[$count]
            );
        }

        if (!count($datas_update)) {
            return response()->json(array(
                'success' => false,
                'message' => "Não foi selecionado nenhum equipamento."
            ));
        }

        DB::beginTransaction();

        foreach ($datas_update as $data_update) {

            $rental_equipment_id = $data_update['rental_equipment_id'];
            unset($data_update['rental_equipment_id']);

            if (!$this->rental_equipment->updateByRentalAndRentalEquipmentId($rental_id, $rental_equipment_id, $data_update)) {
                DB::rollBack();
                return response()->json(array(
                    'success' => false,
                    'message' => "Não foi atualizar o equipamento $rental_equipment_id."
                ));
            }
        }

        $rental_updated = $this->rental->checkAllEquipmentsWithdrawal($company_id, $rental_id);

        DB::commit();

        return response()->json(array(
            'success'        => true,
            'message'        => "Equipamento atualizado.",
            'rental_updated' => $rental_updated
        ));
    }

    public function getEquipmentsLateByRentalAndType(): JsonResponse
    {
        if (!hasPermission('RentalView')) {
            return response()->json();
        }

        $date = dateNowInternational();
        $date_time = strtotime(dateNowInternational());
        $company_id = Auth::user()->__get('company_id');

        $rentals = $this->rental_equipment->getRentalsLateByType($company_id, $date);
        $to_delivery = 0;
        $to_withdraw = 0;
        $no_date_to_withdraw = 0;
        $rental_already = array(
            'to_delivery' => array(),
            'to_withdraw' => array(),
            'no_date_to_withdraw' => array(),
        );
        $y_ = [];

        foreach ($rentals as $rental) {
            if (is_null($rental['actual_delivery_date']) && !is_null($rental['expected_delivery_date']) && !is_null($rental['expected_withdrawal_date']) && strtotime($rental['expected_delivery_date']) < $date_time) {
                if (in_array($rental['rental_id'], $rental_already['to_delivery'])) {
                    continue;
                }
                $rental_already['to_delivery'][] = $rental['rental_id'];
                $to_delivery++;
            }
            else if (!is_null($rental['actual_delivery_date']) && is_null($rental['actual_withdrawal_date']) && !is_null($rental['expected_withdrawal_date']) && strtotime($rental['expected_withdrawal_date']) < $date_time) {
                if (in_array($rental['rental_id'], $rental_already['to_withdraw'])) {
                    continue;
                }
                $rental_already['to_withdraw'][] = $rental['rental_id'];
                $to_withdraw++;
            }
            else if (is_null($rental['expected_withdrawal_date']) && !is_null($rental['actual_delivery_date']) && is_null($rental['actual_withdrawal_date'])) {
                if (in_array($rental['rental_id'], $rental_already['no_date_to_withdraw'])) {
                    continue;
                }
                $rental_already['no_date_to_withdraw'][] = $rental['rental_id'];
                $no_date_to_withdraw++;
            }
        }

        return response()->json(array(
            'to_delivery' => $to_delivery,
            'to_withdraw' => $to_withdraw,
            'no_date_to_withdraw' => $no_date_to_withdraw,
        ));
    }
}
