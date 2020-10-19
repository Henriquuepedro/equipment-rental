<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id', 'client_id', 'name_address', 'address', 'number', 'cep', 'complement', 'reference', 'neigh', 'city', 'state', 'user_insert', 'user_update'
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

    public function getAddressClient($company_id, $client_id)
    {
        return $this->where(['client_id' => $client_id, 'company_id' => $company_id])->get();
    }

    public function deleteAddressClient($client_id)
    {
        return $this->where('client_id', $client_id)->delete();
    }
}
