<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EquipamentWallet extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id', 'equipament_id', 'day_start', 'day_end', 'value', 'active', 'user_insert', 'user_update'
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

    public function remove($equipament_wallet_id)
    {
        return $this->where(['id' => $equipament_wallet_id])->delete();
    }

    public function edit($data, $client_id)
    {
        return $this->where('id', $client_id)->update($data);
    }

    public function removeAllEquipament($equipament_id, $company_id)
    {
        return $this->where(['equipament_id' => $equipament_id, 'company_id' => $company_id])->delete();
    }

    public function getWalletsEquipament($company_id, $equipament_id)
    {
        return $this->where(['equipament_id' => $equipament_id, 'company_id' => $company_id])->get();
    }

    public function getValueWalletsEquipament($company_id, $equipament_id, $day)
    {
        return $this->from(DB::raw('equipament_wallets force index(equipament_company_day_start_end)'))
            ->where(['equipament_id' => $equipament_id, 'company_id' => $company_id])
            ->where('day_start', '<=', $day)
            ->where('day_end', '>=', $day)
            ->first();
    }

}
