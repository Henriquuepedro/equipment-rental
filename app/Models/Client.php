<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Client extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'type',
        'name',
        'fantasy',
        'email',
        'phone_1',
        'phone_2',
        'cpf_cnpj',
        'rg_ie',
        'contact',
        'sex',
        'birth_date',
        'nationality',
        'marital_status',
        'active',
        'observation',
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

    public function insert($data)
    {
        return $this->create($data);
    }

    public function remove($client_id, $company_id)
    {
        return $this->getClient($client_id, $company_id)->delete();
    }

    public function edit($data, $client_id)
    {
        return $this->where('id', $client_id)->first()->fill($data)->save();
    }

    public function getClients($company_id, $init = null, $length = null, $searchUser = null, $orderBy = array(), $select = '*')
    {
        $client = $this->select($select)->where(array(
            'company_id'    => $company_id,
            'active'        => true
        ));
        if ($searchUser) {
            $client->where(function ($query) use ($searchUser) {
                $query->where('name', 'like', "%{$searchUser}%")
                    ->orWhere('email', 'like', "%{$searchUser}%")
                    ->orWhere('phone_1', 'like', "%{$searchUser}%");
            });
        }

        if (count($orderBy) !== 0) {
            $client->orderBy($orderBy['field'], $orderBy['order']);
        } else {
            $client->orderBy('id', 'desc');
        }

        if ($init !== null && $length !== null) {
            $client->offset($init)->limit($length);
        }

        return $client->get();
    }

    public function getClient($client_id, $company_id)
    {
        return $this->where(['id' => $client_id, 'company_id' => $company_id])->first();
    }

    public function getNewClientForMonth(int $company_id, $year, $month)
    {
        return $this->where('company_id', $company_id)->whereYear('created_at', $year)->whereMonth('created_at', $month)->count();
    }

    public function getClientTopRentals(int $company_id, int $count)
    {
        return $this->select(DB::raw('rentals.client_id, clients.name, clients.email, COUNT(*) as total'))
            ->join('rentals', 'rentals.client_id', '=', 'clients.id')
            ->where('rentals.company_id', $company_id)
            ->groupBy('rentals.client_id')
            ->having(DB::raw('total'), '>', 0)
            ->orderBy('total', 'Desc')
            ->limit($count)
            ->get();
    }

    public function getCountClientsActive($company_id)
    {
        return $this->where(['company_id' => $company_id, 'active' => true])->count();
    }
}
