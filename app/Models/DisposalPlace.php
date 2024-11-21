<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisposalPlace extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'name',
        'fantasy',
        'type_person',
        'cpf_cnpj',
        'rg_ie',
        'email',
        'phone_1',
        'phone_2',
        'contact',
        'address_zipcode',
        'address_name',
        'address_number',
        'address_complement',
        'address_reference',
        'address_neigh',
        'address_city',
        'address_state',
        'observation',
        'active',
        'user_insert',
        'user_update',
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

    public function remove($id, $company_id)
    {
        return $this->getByid($id, $company_id)->delete();
    }

    public function edit($data, $id)
    {
        return $this->where('id', $id)->first()->fill($data)->save();
    }

    public function getByid($id, $company_id)
    {
        return $this->where(['id' => $id, 'company_id' => $company_id])->first();
    }

    public function getAllActives($company_id)
    {
        return $this->where(['company_id' => $company_id, 'active' => true])->orderBy('name', 'asc')->get();
    }
}
