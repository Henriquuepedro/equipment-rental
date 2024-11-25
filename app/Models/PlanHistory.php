<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanHistory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'payment_id',
        'status_detail',
        'status',
        'observation',
        'status_date'
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


    public function getHistoryPayment(int $payment)
    {
        return $this->where('payment_id', $payment)->get();
    }

    public function getStatusByPayment(int $payment_id, ?string $observation, $status)
    {
        if (!is_array($status)) {
            $status = array($status);
        }

        return $this->where(array('payment_id' => $payment_id, 'observation' => $observation))->whereIn('status', $status)->first();
    }

    public function getPenultimatePlanConfirmedCompany(int $company, int $planIgnore)
    {
        $plansIgnore = array($planIgnore);
        while (true) {
            $data = $this->select('plans.id as plan_config_id', 'plan_histories.payment_id as payment_id')
                ->where('plan_payments.company_id', $company)
                ->join('plan_payments', 'plan_histories.payment_id', '=', 'plan_payments.id')
                ->join('plans', 'plan_payments.plan_id', '=', 'plans.id')
                ->whereNotIn('plan_histories.payment_id', $plansIgnore)
                ->whereIn('plan_histories.status', array('approved', 'authorized'))
                ->orderBy('plan_histories.id', 'DESC')
                ->first();

            // não existe mais registro, então nunca teve um plano aprovado.
            if (!$data) {
                return null;
            }

            // verifica se o pagamento não teve cancelamento.
            if (
                $this->where('payment_id', $data->payment_id)
                    ->whereIn('status', array('rejected', 'cancelled', 'refunded', 'charged_back'))
                    ->count() === 0
            ) {
                return $data->plan_config_id;
            }

            $plansIgnore[] = $data->payment_id;
        }
    }

    public function getHistoryByStatusAndStatusDetail(int $planId, string $status, ?string $statusDetail, ?string $observation)
    {
        return $this->where([
            'payment_id' => $planId,
            'status' => $status,
            'status_detail' => $statusDetail,
            'observation' => $observation
        ])->first();
    }
}
