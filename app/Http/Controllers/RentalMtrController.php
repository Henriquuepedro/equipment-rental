<?php

namespace App\Http\Controllers;

use App\Http\Requests\RentalMtrCreatePost;
use App\Models\DisposalPlace;
use App\Models\EquipmentRentalMtr;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Rental;
use App\Models\Driver;
use App\Models\RentalMtr;

class RentalMtrController extends Controller
{
    private Rental $rental;
    private Driver $driver;
    private RentalMtr $rental_mtr;
    private DisposalPlace $disposal_place;
    private EquipmentRentalMtr $equipment_rental_mtr;

    public function __construct()
    {
        $this->rental = new Rental();
        $this->driver = new Driver();
        $this->rental_mtr = new RentalMtr();
        $this->disposal_place = new DisposalPlace();
        $this->equipment_rental_mtr = new EquipmentRentalMtr();
    }

    public function createMtr(RentalMtrCreatePost $request): JsonResponse
    {
        if (!hasPermission('RentalMtrCreatePost')) {
            return response()->json();
        }

        $equipments         = $request->input('equipment');
        $residues           = $request->input('residue');
        $quantities         = $request->input('quantity');
        $classifications    = $request->input('classification');
        $dates              = $request->input('date');

        $company_id = $request->user()->company_id;
        $rental_id  = $request->input('rental_id');
        $driver_id  = $request->input('rental_mtr_drivers');
        $disposal_place_id  = $request->input('rental_mtr_disposal_places');

        if (!$equipments) {
            return response()->json(['success' => false, 'message' => 'Nenhum equipamento encontrado!']);
        }

        foreach ($equipments as $key => $equipment) {
            $rental_equipment_id   = $equipment;
            $residue_id            = $residues[$key];
            $quantity              = $quantities[$key];
            $date                  = $dates[$key];

            if (!$rental_equipment_id) {
                return response()->json(['success' => false, 'message' => 'Todos os equipamentos devem ser informados!']);
            }
            if (!$residue_id) {
                return response()->json(['success' => false, 'message' => 'Todos os resíduos devem ser informados!']);
            }
            if (!$quantity) {
                return response()->json(['success' => false, 'message' => 'Todas as quantidades devem ser informados!']);
            }
            if (!$date) {
                return response()->json(['success' => false, 'message' => 'Todas as datas devem ser informados!']);
            }
        }

        $rental = $this->rental->getRental($company_id, $rental_id);
        $driver = $this->driver->getDriver($driver_id, $company_id);
        $disposal_place = $this->disposal_place->getByid($disposal_place_id, $company_id);

        if (!$rental) {
            return response()->json(['success' => false, 'message' => 'Locação não localizada!']);
        }

        if (!$driver) {
            return response()->json(['success' => false, 'message' => 'Motorista não localizada!']);
        }

        if (!$disposal_place) {
            return response()->json(['success' => false, 'message' => 'Local de descarte não localizado!']);
        }

        $mtr_exists = $this->rental_mtr->getByRental($rental_id, $company_id);

        if ($mtr_exists) {
            return response()->json([
                'success' => true,
                'message' => 'MTR já gerado, visualize!',
                'rental_mtr_id' => $mtr_exists->id,
                'print_mtr' => route('print.generate-mtr', ['rental_mtr_id' => $mtr_exists->id])
            ]);
        }

        $create_rental_mtr = $this->rental_mtr->insert(array(
            'company_id'        => $company_id,
            'rental_id'         => $rental_id,
            'driver_id'         => $driver_id,
            'disposal_place_id' => $disposal_place_id,
            'user_insert'       => $request->user()->id
        ));

        if (!$create_rental_mtr) {
            return response()->json(['success' => false, 'message' => 'Não foi possível gerar o MTR, tente novamente!']);
        }

        $rental_mtr_id = $create_rental_mtr->id;

        foreach ($equipments as $key => $equipment) {
            $this->equipment_rental_mtr->insert(array(
                'company_id'            => $company_id,
                'rental_mtr_id'         => $rental_mtr_id,
                'rental_equipment_id'   => $equipment,
                'residue_id'            => $residues[$key],
                'quantity'              => $quantities[$key],
                'classification'        => $classifications[$key] ?: null,
                'date'                  => dateBrazilToDateInternational($dates[$key].':00'),
            ));
        }

        return response()->json([
            'success' => true,
            'message' => 'MTR gerado com sucesso!',
            'rental_mtr_id' => $rental_mtr_id,
            'print_mtr' => route('print.generate-mtr', ['rental_mtr_id' => $rental_mtr_id])
        ]);
    }
}
