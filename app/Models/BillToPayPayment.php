<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

    public function remove($company_id, $bill_to_pay_id)
    {
        return $this->where(['bill_to_pay_id' => $bill_to_pay_id, 'company_id' => $company_id])->delete();
    }

    public function getBillsToReportWithFilters(int $company_id, array $filters, bool $synthetic = true, array $order_by = array())
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
        ->where('bill_to_pays.company_id', $company_id);

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
        if (!empty($order_by)) {
            $rental->orderBy($order_by[0], $order_by[1]);
        } else {
            $rental->orderBy('bill_to_pays.code', 'DESC');
        }

        // Agrupa os registros por locação.
        if ($synthetic) {
            $rental->groupBy('bill_to_pays.id');
        }

        return $rental->get();
    }

    public function getPaymentsByBillId($company_id, $payment_id)
    {
        return $this->where(['bill_to_pay_id' => $payment_id, 'company_id' => $company_id])->get();
    }

    public function getPayments($company_id, $payment_id)
    {
        if (is_numeric($payment_id)) {
            return $this->where(['id' => $payment_id, 'company_id' => $company_id])->first();
        } elseif (is_array($payment_id)) {
            return $this->whereIn('id', $payment_id)->where('company_id', $company_id)->get();
        }
        return [];
    }

    public function updateById(array $data, int $id)
    {
        return $this->where('id', $id)->update($data);
    }

    public function getPaymentByRentalAndDueDateAndValue(int $company_id, int $bill_to_pay_id, string $due_date, float $due_value)
    {
        return $this->where(array(
            'company_id'        => $company_id,
            'bill_to_pay_id'    => $bill_to_pay_id,
            'due_date'          => $due_date,
            'due_value'         => $due_value
        ))->first();
    }

    public function getPaymentsPaidByBill(int $company_id, int $bill_to_pay_id)
    {
        return $this->where(array(
            'company_id'     => $company_id,
            'bill_to_pay_id' => $bill_to_pay_id
        ))->where('payment_id', '!=', null)->get();
    }

    public function getBillsForDate(int $company_id, string $date): float|int
    {
        $register = $this->select(DB::raw('SUM(due_value) as total'))
            ->where([
                ['payment_id', '<>', null],
                ['company_id', '=', $company_id]
            ])
            ->whereDate('payday', $date)
            ->first();

        if ($register) {
            if ($register->total) {
                return roundDecimal($register->total);
            }
        }

        return 0;
    }
}
