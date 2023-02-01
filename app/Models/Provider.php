<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id', 'type', 'name', 'fantasy', 'email', 'phone_1', 'phone_2', 'cpf_cnpj', 'rg_ie', 'contact', 'sex', 'birth_date', 'nationality', 'marital_status', 'observation', 'address', 'number', 'cep', 'complement', 'reference', 'neigh', 'city', 'state', 'user_insert', 'user_update'
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

    public function remove($provider_id, $company_id)
    {
        return $this->where(['id' => $provider_id, 'company_id' => $company_id])->delete();
    }

    public function edit($data, $provider_id)
    {
        return $this->where('id', $provider_id)->update($data);
    }

    public function getProviders($company_id, $init = null, $length = null, $searchUser = null, $orderBy = array())
    {
        $provider = $this->where('company_id', $company_id);
        if ($searchUser)
            $provider->where(function($query) use ($searchUser) {
                $query->where('name', 'like', "%{$searchUser}%")
                    ->orWhere('email', 'like', "%{$searchUser}%")
                    ->orWhere('phone_1', 'like', "%{$searchUser}%");
            });

        if (count($orderBy) !== 0) $provider->orderBy($orderBy['field'], $orderBy['order']);
        else $provider->orderBy('id', 'desc');

        if ($init !== null && $length !== null) $provider->offset($init)->limit($length);

        return $provider->get();
    }

    public function getCountProviders($company_id, $searchUser = null)
    {
        $provider = $this->where('company_id', $company_id);
        if ($searchUser)
            $provider->where(function($query) use ($searchUser) {
                $query->where('name', 'like', "%{$searchUser}%")
                    ->orWhere('email', 'like', "%{$searchUser}%")
                    ->orWhere('phone_1', 'like', "%{$searchUser}%");
            });

        return $provider->count();
    }

    public function getProvider($provider_id, $company_id)
    {
        return $this->where(['id' => $provider_id, 'company_id' => $company_id])->first();
    }
}
