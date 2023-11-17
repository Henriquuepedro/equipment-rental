<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RentalEquipment extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rental_equipments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'rental_id',
        'equipment_id',
        'reference',
        'name',
        'volume',
        'quantity',
        'unitary_value',
        'total_value',
        'vehicle_suggestion',
        'driver_suggestion',
        'use_date_diff_equip',
        'expected_delivery_date',
        'expected_withdrawal_date',
        'not_use_date_withdrawal',
        'exchange_rental_equipment_id',
        'exchanged',
        'user_insert',
        'user_update'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    public function insert(array $data)
    {
        return $this->create($data);
    }

    public function inserts(array $datas)
    {
        foreach ($datas as $data)
            if (!$this->create($data)) return false;

        return true;
    }

    public function remove(int $company_id, int $rental_id)
    {
        foreach ($this->where(['rental_id' => $rental_id, 'company_id' => $company_id])->orderBy('id', 'DESC')->get() as $equipment) {
            $this->where('id', $equipment->id)->delete();
        }
        return true;
    }

    public function getEquipments(int $company_id, int $rental_id)
    {
        return $this->where(['rental_id' => $rental_id, 'company_id' => $company_id])->get();
    }

    public function getEquipmentsForDeliver(int $company_id, int $rental_id)
    {
        return $this->where([
            'rental_id' => $rental_id,
            'company_id' => $company_id,
            'actual_delivery_date' => null,
            'actual_withdrawal_date' => null
        ])->get();
    }

    public function getEquipmentsForWithdraw(int $company_id, int $rental_id)
    {
        return $this->where([
            ['rental_id', '=', $rental_id],
            ['company_id', '=', $company_id],
            ['actual_delivery_date', '!=', null],
            ['actual_withdrawal_date', '=', null]
        ])->get();
    }

    public function updateByRentalAndRentalEquipmentId(int $rental_id, int $rental_equipment_id, array $data): bool
    {
        return (bool)$this->where(array('id' => $rental_equipment_id, 'rental_id' => $rental_id))->update($data);
    }

    public function getEquipmentsInUse(int $company_id, int $equipment_id)
    {
        $equipment = $this->where([
            ['equipment_id', '=', $equipment_id],
            ['company_id', '=', $company_id]
        ]);

        $equipment->where(function($query) {
            $query->where('actual_delivery_date', '=', null)
                ->orWhere('actual_withdrawal_date', '=', null);
        });


        return $equipment->get()->count();
    }

    public function getMatchingEquipmentToValidateLeaseUpdate(
        int $company_id,
        int $rental_id,
        int $equipment_id,
        int $quantity,
        ?int $vehicle_suggestion,
        ?int $driver_suggestion,
        bool $use_date_diff_equip,
        ?string $expected_delivery_date,
        ?string $expected_withdrawal_date,
        bool $not_use_date_withdrawal,
        float $total_value
    )
    {
        return $this->where(array(
            'company_id'                => $company_id,
            'rental_id'                 => $rental_id,
            'equipment_id'              => $equipment_id,
            'quantity'                  => $quantity,
            'vehicle_suggestion'        => $vehicle_suggestion,
            'driver_suggestion'         => $driver_suggestion,
            'use_date_diff_equip'       => $use_date_diff_equip,
            'expected_delivery_date'    => $expected_delivery_date,
            'expected_withdrawal_date'  => $expected_withdrawal_date,
            'not_use_date_withdrawal'   => $not_use_date_withdrawal,
            'total_value'               => $total_value,
        ))->first();
    }

    public function getEquipmentInProgressByRental(int $company_id, int $rental_id)
    {
        $equipments = $this->where(array(
            'company_id'    => $company_id,
            'rental_id'     => $rental_id
        ));

        $equipments->where(function($query) {
            $query->where('actual_delivery_date', '!=', null)
                ->orWhere('actual_withdrawal_date', '!=', null);
        });

        return $equipments->get();
    }

    public function getEquipmentToExchange(int $company_id, int $rental_id)
    {
        return $this->where([
            'rental_id' => $rental_id,
            'company_id' => $company_id,
            ['actual_delivery_date', '!=', null],
            'actual_withdrawal_date' => null,
            'exchanged' => false
        ])->get();
    }

    public function getRentalClientByDate(int $company_id, string $date, string $type)
    {
        $query = $this->select(DB::raw('SUM(rental_equipments.quantity) as total, COUNT(DISTINCT rental_equipments.rental_id) as rentals, rentals.client_id, clients.name'))
            ->join('rentals', 'rentals.id', '=', 'rental_equipments.rental_id')
            ->join('clients', 'rentals.client_id', '=', 'clients.id')
            ->where('rentals.company_id', $company_id);

        if ($type === 'deliver') {
            $query->where('rental_equipments.actual_delivery_date', null)->whereDate('rental_equipments.expected_delivery_date', $date);
        } elseif ($type === 'withdraw') {
            $query->where([
                ['rental_equipments.actual_delivery_date', '!=', null],
                ['rental_equipments.actual_withdrawal_date', '=', null],
            ])
            ->whereDate('rental_equipments.expected_withdrawal_date', $date);
        } else {
            $query->whereDate('rentals.created_at', $date);
        }

        return $query->groupBy('rentals.client_id')->get();
    }
}
