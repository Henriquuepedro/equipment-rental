<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehicle extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id', 'name', 'brand', 'model', 'reference', 'board', 'driver_id', 'observation', 'user_insert', 'user_update'
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

    public function insert($data)
    {
        return $this->create($data);
    }

    public function remove($vehicle_id, $company_id)
    {
        return $this->where(['id' => $vehicle_id, 'company_id' => $company_id])->delete();
    }

    public function edit($data, $vehicle_id)
    {
        return $this->where('id', $vehicle_id)->update($data);
    }

    public function getVehicles($company_id, $init = null, $length = null, $searchVehicle = null, $orderBy = array(), $select = '*')
    {
        $vehicle = $this->select($select)->where('company_id', $company_id);
        if ($searchVehicle)
            $vehicle->where(function($query) use ($searchVehicle) {
                $query->where('name', 'like', "%{$searchVehicle}%")
                    ->orWhere('cpf', 'like', "%{$searchVehicle}%")
                    ->orWhere('phone', 'like', "%{$searchVehicle}%");
            });

        if (count($orderBy) !== 0) $vehicle->orderBy($orderBy['field'], $orderBy['order']);
        else $vehicle->orderBy('id', 'desc');

        if ($init !== null && $length !== null) $vehicle->offset($init)->limit($length);

        return $vehicle->get();
    }

    public function getCountVehicles($company_id)
    {
        return $this->where('company_id', $company_id)->count();
    }

    public function getVehicle($vehicle_id, $company_id)
    {
        return $this->where(['id' => $vehicle_id, 'company_id' => $company_id])->first();
    }
}
