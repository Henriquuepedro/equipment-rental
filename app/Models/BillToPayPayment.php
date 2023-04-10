<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillToPayPayment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'bill_to_pay_id',
        'parcel',
        'due_day',
        'due_date',
        'due_value',
        'payment_id',
        'payment_name',
        'payday',
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


    public function getBillsToReportWithFilters(int $company_id, array $filters, bool $synthetic = true)
    {
        $rental = $this ->select(
            'bill_to_pays.id',
            'bill_to_pays.code',
            'providers.name as provider_name',
            'form_payments.name as payment_name',
            'bill_to_pay_payments.parcel',
            'bill_to_pay_payments.due_date',
            'bill_to_pay_payments.payday',
            'bill_to_pay_payments.due_value',
            'bill_to_pay_payments.payment_id'
        )
        ->join('bill_to_pays','bill_to_pay_payments.bill_to_pay_id','=','bill_to_pays.id')
        ->join('providers','providers.id','=','bill_to_pays.provider_id')
        ->leftJoin('form_payments','form_payments.id','=','bill_to_pay_payments.payment_id')
        ->where(['bill_to_pays.company_id' => $company_id, 'bill_to_pays.deleted' => false]);

        // Filtrar registros por data.
        switch ($filters['_date_filter']) {
            case 'created':
            default:
                $date_filter = 'bill_to_pays.created_at';
                break;
            case 'due':
                $date_filter = 'bill_to_pay_payments.due_date';
                break;
            case 'pay':
                $date_filter = 'bill_to_pay_payments.payday';
                break;
        }

        $rental->whereBetween($date_filter, ["{$filters['_date_start']} 00:00:00", "{$filters['_date_end']} 23:59:59"]);

        // Faz os filtros conforme o que foi informado.
        foreach ($filters as $filter_key => $filter_value) {
            // chave que comecem com "_", devem se ignoradas.
            if (substr($filter_key, 0, 1) === '_') {
                continue;
            }

            $rental->where($filter_key, $filter_value[0], $filter_value[1]);
        }

        // Ordena os registros.
        $rental->orderBy('bill_to_pays.code', 'DESC');

        // Agrupa os registros por locação.
        if ($synthetic) {
            $rental->groupBy('bill_to_pays.id');
        }

        return $rental->get();
    }
}
