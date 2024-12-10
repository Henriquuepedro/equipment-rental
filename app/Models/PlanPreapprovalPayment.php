<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanPreapprovalPayment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'plan_payment_id',
        'preapproval_id',
        'status_detail',
        'status',
        'transaction_amount',
        'gateway_payment_id',
        'gateway_debit_date',
        'gateway_date_created',
        'gateway_last_modified'
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

    public function editById(array $data, int $id)
    {
        return $this->find($id)->fill($data)->save();
    }

    public function edit(array $data, string $gateway_payment_id, string $preapproval_id)
    {
        return $this->where([
            'gateway_payment_id' => $gateway_payment_id,
            'preapproval_id' => $preapproval_id
        ])->first()->fill($data)->save();
    }

    public function getByGatewayPaymentId(int $gateway_payment_id)
    {
        return $this->where('gateway_payment_id', $gateway_payment_id)->first();
    }
}
