<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id', 'name', 'cpf', 'rg', 'cnh', 'cnh_exp', 'email', 'phone', 'observation', 'user_insert', 'user_update'
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

    public function remove($driver_id, $company_id)
    {
        return $this->where(['id' => $driver_id, 'company_id' => $company_id])->delete();
    }

    public function edit($data, $driver_id)
    {
        return $this->where('id', $driver_id)->update($data);
    }

    public function getDrivers($company_id, $init = null, $length = null, $searchDriver = null, $orderBy = array())
    {
        $driver = $this->where('company_id', $company_id);
        if ($searchDriver) {
            $driver->where(function ($query) use ($searchDriver) {
                $query->where('name', 'like', "%{$searchDriver}%")
                    ->orWhere('cpf', 'like', "%{$searchDriver}%")
                    ->orWhere('phone', 'like', "%{$searchDriver}%");
            });
        }

        if (count($orderBy) !== 0) {
            $driver->orderBy($orderBy['field'], $orderBy['order']);
        } else {
            $driver->orderBy('name', 'asc');
        }

        if ($init !== null && $length !== null) {
            $driver->offset($init)->limit($length);
        }

        return $driver->get();
    }

    public function getCountDrivers($company_id, $searchDriver = null)
    {
        $driver = $this->where('company_id', $company_id);
        if ($searchDriver) {
            $driver->where(function ($query) use ($searchDriver) {
                $query->where('name', 'like', "%{$searchDriver}%")
                    ->orWhere('cpf', 'like', "%{$searchDriver}%")
                    ->orWhere('phone', 'like', "%{$searchDriver}%");
            });
        }

        return $driver->count();
    }

    public function getDriver($driver_id, $company_id)
    {
        return $this->where(['id' => $driver_id, 'company_id' => $company_id])->first();
    }
}
