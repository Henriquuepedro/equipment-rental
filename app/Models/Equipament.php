<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipament extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id', 'name', 'reference', 'stock', 'value', 'manufacturer', 'volume', 'user_insert', 'user_update'
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

    public function remove($equipament_id, $company_id)
    {
        return $this->where(['id' => $equipament_id, 'company_id' => $company_id])->delete();
    }

    public function edit($data, $equipament_id)
    {
        return $this->where('id', $equipament_id)->update($data);
    }

    public function getEquipaments($company_id, $init = null, $length = null, $searchUser = null, $orderBy = array())
    {
        $client = $this->where('company_id', $company_id);
        if ($searchUser)
            $client->where(function($query) use ($searchUser) {
                $query->where('name', 'like', "%{$searchUser}%")
                    ->orWhere('reference', 'like', "%{$searchUser}%")
                    ->orWhere('stock', 'like', "%{$searchUser}%");
            });

        if (count($orderBy) !== 0) $client->orderBy($orderBy['field'], $orderBy['order']);
        else $client->orderBy('id', 'desc');

        if ($init !== null && $length !== null) $client->offset($init)->limit($length);

        return $client->get();
    }

    public function getCountEquipaments($company_id, $searchUser = null)
    {
        $client = $this->where('company_id', $company_id);
        if ($searchUser)
            $client->where(function($query) use ($searchUser) {
                $query->where('name', 'like', "%{$searchUser}%")
                    ->orWhere('reference', 'like', "%{$searchUser}%")
                    ->orWhere('stock', 'like', "%{$searchUser}%");
            });

        return $client->count();
    }

    public function getEquipament($equipament_id, $company_id)
    {
        return $this->where(['id' => $equipament_id, 'company_id' => $company_id])->first();
    }
}
