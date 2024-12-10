<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class Company extends Model
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'fantasy',
        'type_person',
        'cpf_cnpj',
        'email',
        'phone_1',
        'phone_2',
        'contact',
        'logo',
        'cep',
        'address',
        'number',
        'complement',
        'reference',
        'neigh',
        'city',
        'state',
        'plan_id',
        'status',
        'plan_expiration_date',
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

    public function getCompany($id)
    {
        return $this->find($id);
    }

    public function getAllCompanies()
    {
        if (hasAdminMaster()) {
            return $this->select('id', 'name', 'fantasy')->get();
        }

        return array();
    }

    public function getAllCompaniesActive()
    {
        if (hasAdminMaster()) {
            return $this->select('id', 'name')->where('status', true)->get();
        }

        return array();
    }

    public function edit($data, $id)
    {
        return $this->where('id', $id)->first()->fill($data)->save();
    }

    public function insert(array $data)
    {
        return $this->create($data);
    }

    public function getPlanCompany(int $id)
    {
        return $this->select('plans.*')->join('plans', 'plans.id', '=', 'companies.plan_id')->where('companies.id', $id)->first();
    }

    public function setDatePlanAndUpdatePlanCompany(int $company_id, int $plan_id, int $months)
    {
        if ($months === 0) {
            return null;
        }

        $company = $this->where('id', $company_id)->first();

        if ($company) {
            $plan_expiration_date = $company->plan_expiration_date;

            if (strtotime($plan_expiration_date) < strtotime(dateNowInternational())) {
                $plan_expiration_date = dateNowInternational();
            }

            if ($months > 0) {
                $plan_expiration_date = sumDate($plan_expiration_date, 0, $months);
            } else {
                $months *= (-1);
                $plan_expiration_date = subDate($plan_expiration_date, 0, $months);
            }

            return $this->where('id', $company_id)->first()->fill(
                array(
                    'plan_id' => $plan_id,
                    'plan_expiration_date' => $plan_expiration_date
                )
            )->save();
        }

        return null;
    }
}
