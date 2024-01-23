<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EquipmentWallet extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id', 'equipment_id', 'day_start', 'day_end', 'value', 'active', 'user_insert', 'user_update'
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

    public function remove($equipment_wallet_id)
    {
        return $this->getById($equipment_wallet_id)->delete();
    }

    public function edit($data, $client_id)
    {
        return $this->where('id', $client_id)->first()->fill($data)->save();
    }

    public function getById($equipment_wallet_id)
    {
        return $this->find($equipment_wallet_id);
    }

    public function removeAllEquipment($equipment_id, $company_id)
    {
        return $this->getWalletsEquipment($company_id, $equipment_id)->each(fn ($register) => $register->delete());
    }

    public function getWalletsEquipment($company_id, $equipment_id)
    {
        return $this->where(['equipment_id' => $equipment_id, 'company_id' => $company_id])->get();
    }

    public function getValueWalletsEquipment($company_id, $equipment_id, $day)
    {
        return $this->from(DB::raw('equipment_wallets force index(equipment_company_day_start_end)'))
            ->where(['equipment_id' => $equipment_id, 'company_id' => $company_id])
            ->where('day_start', '<=', $day)
            ->where('day_end', '>=', $day)
            ->first();
    }

}
