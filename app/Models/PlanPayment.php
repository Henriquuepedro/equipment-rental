<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanPayment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_transaction',
        'code_payment',
        'link_billet',
        'barcode_billet',
        'date_of_expiration',
        'key_pix',
        'base64_key_pix',
        'payment_method_id',
        'payment_type_id',
        'plan',
        'type_payment',
        'plan_id',
        'status_detail',
        'installments',
        'status',
        'gross_amount',
        'net_amount',
        'client_amount',
        'company_id',
        'user_created',
        'user_updated'
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

    public function edit($data, $company_id, $id)
    {
        return $this->where(['company_id' => $company_id, 'id', $id])->update($data);
    }

    public function getPaymentByTransaction(int $id_transaction)
    {
        return $this->where('id_transaction', $id_transaction)->first();
    }

    public function getById(int $company_id, int $id)
    {
        return $this
            ->select('plan_payments.*', 'plans.name')
            ->join('plans', 'plan_payments.plan_id', '=', 'plans.id')
            ->where(['plan_payments.company_id' => $company_id, 'plan_payments.id' => $id])
            ->first();
    }

    public function getPaymentOpen()
    {
        return $this->whereIn('status', array('pending','inprocess','inmediation'))->get();
    }
}
