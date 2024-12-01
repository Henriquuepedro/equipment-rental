<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'company_id',
        'client_id',
        'address_zipcode',
        'address_name',
        'address_number',
        'address_complement',
        'address_reference',
        'address_neigh',
        'address_city',
        'address_state',
        'address_lat',
        'address_lng',
        'expected_delivery_date',
        'expected_withdrawal_date',
        'not_use_date_withdrawal',
        'gross_value',
        'extra_value',
        'discount_value',
        'net_value',
        'calculate_net_amount_automatic',
        'automatic_parcel_distribution',
        'multiply_quantity_of_equipment_per_amount',
        'multiply_quantity_of_equipment_per_day',
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

    public function insert(array $data)
    {
        return $this->create($data);
    }

    public function updateByBudgetAndCompany(int $budget_id, int $company_id, array $data)
    {
        return $this->where(array('id' => $budget_id, 'company_id' => $company_id))->first()->fill($data)->save();
    }

    public function getNextCode(int $company_id)
    {
        $maxCode = $this->where('company_id', $company_id)->max('code');
        if ($maxCode) {
            return ++$maxCode;
        }

        return 1;
    }

    public function getBudgets($company_id, $init = null, $length = null, $searchDriver = null, $orderBy = array())
    {
        $budget = $this ->select(
            'budgets.id',
            'budgets.code',
            'clients.name as client_name',
            'budgets.address_name',
            'budgets.address_number',
            'budgets.address_zipcode',
            'budgets.address_complement',
            'budgets.address_neigh',
            'budgets.address_city',
            'budgets.address_state',
            'budgets.created_at'
        )->join('clients','clients.id','=','budgets.client_id')
        ->where('budgets.company_id', $company_id);

        if ($searchDriver) {
            $budget->where(function ($query) use ($searchDriver) {
                $query->where('budgets.code', 'like', "%$searchDriver%")
                    ->orWhere('clients.name', 'like', "%$searchDriver%")
                    ->orWhere('budgets.address_name', 'like', "%$searchDriver%")
                    ->orWhere('budgets.created_at', 'like', "%$searchDriver%");
            });
        }

        if (count($orderBy) !== 0) $budget->orderBy($orderBy['field'], $orderBy['order']);
        else $budget->orderBy('budgets.code', 'asc');

        if ($init !== null && $length !== null) $budget->offset($init)->limit($length);

        return $budget->get();
    }

    public function getCountBudgets($company_id, $searchDriver = null)
    {
        $budget = $this ->join('clients','clients.id','=','budgets.client_id')
            ->where('budgets.company_id', $company_id);
        if ($searchDriver) {
            $budget->where(function ($query) use ($searchDriver) {
                $query->where('budgets.code', 'like', "%$searchDriver%")
                    ->orWhere('clients.name', 'like', "%$searchDriver%")
                    ->orWhere('budgets.address_name', 'like', "%$searchDriver%")
                    ->orWhere('budgets.created_at', 'like', "%$searchDriver%");
            });
        }

        return $budget->count();
    }

    public function getBudget($budget_id, $company_id)
    {
        return $this->where(['id' => $budget_id, 'company_id' => $company_id])->first();
    }

    public function remove($budget_id, $company_id)
    {
        return $this->getBudget($budget_id, $company_id)->delete();
    }
}
