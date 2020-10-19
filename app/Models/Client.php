<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id', 'type', 'name', 'fantasy', 'email', 'phone_1', 'phone_2', 'cpf_cnpj', 'rg_ie', 'user_insert', 'user_update'
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

    public function remove($client_id, $company_id)
    {
        return $this->where(['id' => $client_id, 'company_id' => $company_id])->delete();
    }

    public function edit($data, $client_id)
    {
        return $this->where('id', $client_id)->update($data);
    }

    public function getClients($company_id, $init = null, $length = null, $searchUser = null, $orderBy = array())
    {
        $client = $this->where('company_id', $company_id);
        if ($searchUser)
            $client->where(function($query) use ($searchUser) {
                $query->where('name', 'like', "%{$searchUser}%")
                    ->orWhere('email', 'like', "%{$searchUser}%")
                    ->orWhere('phone_1', 'like', "%{$searchUser}%");
            });

        if (count($orderBy) !== 0) $client->orderBy($orderBy['field'], $orderBy['order']);
        else $client->orderBy('id', 'desc');

        if ($init !== null && $length !== null) $client->offset($init)->limit($length);

        return $client->get();
    }

    public function getCountClients($company_id, $searchUser = null)
    {
        $client = $this->where('company_id', $company_id);
        if ($searchUser)
                $client->where(function($query) use ($searchUser) {
                    $query->where('name', 'like', "%{$searchUser}%")
                        ->orWhere('email', 'like', "%{$searchUser}%")
                        ->orWhere('phone_1', 'like', "%{$searchUser}%");
                });

        return $client->count();
    }

    public function getClient($client_id, $company_id)
    {
        return $this->where(['id' => $client_id, 'company_id' => $company_id])->first();
    }
}
