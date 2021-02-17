<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentalEquipament extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'rental_id',
        'equipament_id',
        'reference',
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
}
