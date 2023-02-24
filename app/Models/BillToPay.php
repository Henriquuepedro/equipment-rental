<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillToPay extends Model
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
        'provider_id',
        'gross_value',
        'extra_value',
        'discount_value',
        'net_value',
        'calculate_net_amount_automatic',
        'use_parceled',
        'automatic_parcel_distribution',
        'form_payment',
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

    public function getLastCode(int $company_id)
    {
        return $this->where('company_id', $company_id)->max('code');
    }

    public function getNextCode(int $company_id): int
    {
        $max_code = $this->where('company_id', $company_id)->max('code');
        if ($max_code) {
            return ++$max_code;
        }

        return 1;
    }

    public function insert(array $data)
    {
        return $this->create($data);
    }

    public function getCountTypePayments(int $company_id, int $provider, string $start_date, string $end_date): array
    {
        $data = array();

        foreach (array(
             'late' => array(
                 ['due_date', '<', date('Y-m-d')],
                 ['payday', '=', NULL]
             ),
             'without_pay' => array(
                 ['due_date', '>=', date('Y-m-d')],
                 ['payday', '=', NULL]
             ),
             'paid' => array(
                 ['payday', '<>', NULL]
             )
        ) as $type => $where) {
            $where = array_merge(array(['bill_to_pay_payments.company_id', '=', $company_id]), $where);

            if ($provider) {
                $where = array_merge(array(['bill_to_pays.provider_id', '=', $provider]), $where);
            }

            $data[$type] = $this
                ->join('bill_to_pay_payments','bill_to_pay_payments.bill_to_pay_id','=','bill_to_pays.id')
                ->whereBetween('bill_to_pay_payments.due_date', [$start_date, $end_date])
                ->where($where)
                ->get()
                ->count();
        }

        return $data;
    }

    public function getBills(int $company_id, array $filters, int $init = null, int $length = null, string $search_provider = null, array $order_by = array(), string $type_bill = null, bool $return_count = false)
    {
        $bill = $this ->select(
            'bill_to_pays.id',
            'bill_to_pays.code',
            'providers.name as provider_name',
            'bill_to_pays.created_at',
            'bill_to_pay_payments.due_date',
            'bill_to_pay_payments.due_value',
            'bill_to_pay_payments.id as bill_payment_id',
            'bill_to_pay_payments.payment_id',
            'bill_to_pay_payments.payday',
        )->join('bill_to_pay_payments','bill_to_pay_payments.bill_to_pay_id','=','bill_to_pays.id')
        ->join('providers','providers.id','=','bill_to_pays.provider_id')
        ->where(['bill_to_pays.company_id' => $company_id]);

        if ($search_provider) {
            $bill->where(function ($query) use ($search_provider) {
                $query->where('bill_to_pays.code', 'like', "%".(int)onlyNumbers($search_provider)."%")
                    ->orWhere('providers.name', 'like', "%$search_provider%")
                    ->orWhere('bill_to_pays.address_name', 'like', "%$search_provider%")
                    ->orWhere('bill_to_pay_payments.due_date', 'like', "%$search_provider%");
            });
        }

        if ($type_bill) {
            switch ($type_bill) {
                case 'late':
                    $bill->where(array(
                        ['bill_to_pay_payments.due_date', '<', date('Y-m-d')],
                        ['bill_to_pay_payments.payday', '=', NULL]
                    ));
                    break;
                case 'without_pay':
                    $bill->where(array(
                        ['bill_to_pay_payments.due_date', '>=', date('Y-m-d')],
                        ['bill_to_pay_payments.payday', '=', NULL]
                    ));
                    break;
                case 'paid':
                    $bill->where(array(
                        ['bill_to_pay_payments.payday', '<>', NULL]
                    ));
                    break;
            }
        }

        if ($filters['provider'] !== null) {
            $bill->where('bill_to_pays.provider_id', $filters['provider']);
        }

        if ($filters['end_date'] !== null && $filters['start_date'] !== null) {
            $bill->whereBetween('bill_to_pay_payments.due_date', [$filters['start_date'], $filters['end_date']]);
        }

        if (count($order_by) !== 0) {
            $bill->orderBy($order_by['field'], $order_by['order']);
        } else {
            $bill->orderBy('bill_to_pays.code', 'asc');
        }

        if ($init !== null && $length !== null) {
            $bill->offset($init)->limit($length);
        }

        if ($return_count) {
            return $bill->get()->count();
        }

        return $bill->get();
    }

    public function getBill($company_id, $bill_to_pay_id)
    {
        return $this->from('bill_to_pay_payments')->where(['id' => $bill_to_pay_id, 'company_id' => $company_id])->first();
    }

    public function updateById(array $data, int $id)
    {
        return $this->from('bill_to_pay_payments')->where('id', $id)->update($data);
    }
}
