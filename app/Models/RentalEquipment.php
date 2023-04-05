<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function remove($rental_id, $company_id)
    {
        return $this->where(['rental_id' => $rental_id, 'company_id' => $company_id])->delete();
    }

    public function getEquipments($company_id, $rental_id)
    {
        return $this->where(['rental_id' => $rental_id, 'company_id' => $company_id])->get();
    }

    public function getEquipmentsForDeliver($company_id, $rental_id)
    {
        return $this->where([
            'rental_id' => $rental_id,
            'company_id' => $company_id,
            'actual_delivery_date' => null,
            'actual_withdrawal_date' => null
        ])->get();
    }

    public function getEquipmentsForWithdraw($company_id, $rental_id)
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
}
