<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Rental extends Model
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
        'type_rental',
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
        'actual_delivery_date',
        'actual_withdrawal_date',
        'not_use_date_withdrawal',
        'gross_value',
        'extra_value',
        'discount_value',
        'net_value',
        'calculate_net_amount_automatic',
        'use_parceled',
        'automatic_parcel_distribution',
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

    public function updateByRentalAndCompany(int $rental_id, int $company_id, array $data)
    {
        return $this->where(array('id' => $rental_id, 'company_id' => $company_id))->update($data);
    }

    public function getLastCode(int $company_id)
    {
        return $this->where('company_id', $company_id)->max('code');
    }

    public function getNextCode(int $company_id)
    {
        $maxCode = $this->where('company_id', $company_id)->max('code');
        if ($maxCode) return ++$maxCode;

        return 1;
    }

    public function getRentals(int $company_id, array $filters, int $init = null, int $length = null, string $searchDriver = null, array $orderBy = array(), string $typeRental = null)
    {
        $rental = $this ->select(
                            'rentals.id',
                            'rentals.code',
                            'clients.name as client_name',
                            'rentals.address_name',
                            'rentals.address_number',
                            'rentals.address_zipcode',
                            'rentals.address_complement',
                            'rentals.address_neigh',
                            'rentals.address_city',
                            'rentals.address_state',
                            'rentals.created_at'
                        )
                        ->join('clients','clients.id','=','rentals.client_id')
                        ->join('rental_equipments','rental_equipments.rental_id','=','rentals.id')
                        ->where('rentals.company_id', $company_id)
                        ->whereBetween('rentals.created_at', ["{$filters['dateStart']} 00:00:00", "{$filters['dateFinish']} 23:59:59"]);
        if ($searchDriver)
            $rental->where(function($query) use ($searchDriver) {
                $query->where('rentals.code', 'like', "%{$searchDriver}%")
                    ->orWhere('clients.name', 'like', "%{$searchDriver}%")
                    ->orWhere('rentals.address_name', 'like', "%{$searchDriver}%")
                    ->orWhere('rentals.created_at', 'like', "%{$searchDriver}%");
            });

        if ($typeRental) {
            switch ($typeRental) {
                case 'deliver':
                    $rental->where('rental_equipments.actual_delivery_date', null);
                    break;
                case 'withdraw':
                    $rental->where([
                        ['rental_equipments.actual_delivery_date', '<>', null],
                        ['rental_equipments.actual_withdrawal_date', '=', null]
                    ]);
                    break;
                case 'finished':
                    $rental->where([
                        ['rentals.actual_delivery_date', '<>', null],
                        ['rentals.actual_withdrawal_date', '<>', null]
                    ]);
                    break;
            }
        }

        if ($filters['client'] !== null) {
            $rental->where('rentals.client_id', $filters['client']);
        }

        if (count($orderBy) !== 0) {
            $rental->orderBy($orderBy['field'], $orderBy['order']);
        } else {
            $rental->orderBy('rentals.code', 'asc');
        }

        $rental->groupBy($typeRental === 'finished' ? 'rentals.id' : 'rental_equipments.rental_id');

        if ($init !== null && $length !== null) {
            $rental->offset($init)->limit($length);
        }

        return $rental->get();
    }

    public function getCountRentals(int $company_id, array $filters, string $searchDriver = null, string $typeRental = null)
    {
        $rental = $this ->join('clients','clients.id','=','rentals.client_id')
            ->join('rental_equipments','rental_equipments.rental_id','=','rentals.id')
            ->where('rentals.company_id', $company_id)
            ->whereBetween('rentals.created_at', ["{$filters['dateStart']} 00:00:00", "{$filters['dateFinish']} 23:59:59"]);

        if ($searchDriver) {
            $rental->where(function ($query) use ($searchDriver) {
                $query->where('rentals.code', 'like', "%{$searchDriver}%")
                    ->orWhere('clients.name', 'like', "%{$searchDriver}%")
                    ->orWhere('rentals.address_name', 'like', "%{$searchDriver}%")
                    ->orWhere('rentals.created_at', 'like', "%{$searchDriver}%");
            });
        }

        if ($typeRental) {
            switch ($typeRental) {
                case 'deliver':
                    $rental->where('rental_equipments.actual_delivery_date', null);
                    break;
                case 'withdraw':
                    $rental->where([
                        ['rental_equipments.actual_delivery_date', '<>', null],
                        ['rental_equipments.actual_withdrawal_date', '=', null]
                    ]);
                    break;
                case 'finished':
                    $rental->where([
                        ['rentals.actual_delivery_date', '<>', null],
                        ['rentals.actual_withdrawal_date', '<>', null]
                    ]);
                    break;
            }
        }

        if ($filters['client'] !== null) {
            $rental->where('rentals.client_id', $filters['client']);
        }

        $rental->groupBy($typeRental === 'finished' ? 'rentals.id' : 'rental_equipments.rental_id');

        return $rental->get()->count();
    }

    public function getRental(int $rental_id, int $company_id)
    {
        return $this->where(['id' => $rental_id, 'company_id' => $company_id])->first();
    }

    public function remove(int $rental_id, int $company_id)
    {
        return $this->where(['id' => $rental_id, 'company_id' => $company_id])->delete();
    }

    public function getCountTypeRentals(int $company_id)
    {
        DB::enableQueryLog();
        $data = array();

        foreach (array(
             'deliver' => array(
                 ['actual_delivery_date', '=', NULL]
             ),
             'withdraw' => array(
                 ['actual_delivery_date', '<>', NULL],
                 ['actual_withdrawal_date', '=', NULL]
             ),
             'finished' => array(
                 ['actual_delivery_date', '<>', NULL],
                 ['actual_withdrawal_date', '<>', NULL]
             )
        ) as $type => $where) {
            $where = array_merge(array(['company_id', '=', $company_id]), $where);

            $data[$type] = $this->from($type === 'finished' ? 'rentals' : 'rental_equipments')
                ->where($where)
                ->groupBy($type === 'finished' ? 'id' : 'rental_id')
                ->get()
                ->count();
        }

        return $data;
    }

    public function checkAllEquipmentsDelivered(int $rental_id, int $company_id): bool
    {
        $rental_dont_delivered = $this->select('rental_id', 'id')
            ->from('rental_equipments')
            ->where([
                'rental_id'             => $rental_id,
                'company_id'            => $company_id,
                'actual_delivery_date'  => null
            ])->first();

        if (!$rental_dont_delivered) {
            $rental_delivered = $this->select(DB::raw('min(actual_delivery_date) as actual_delivery_date'))
                ->from('rental_equipments')
                ->where([
                    ['rental_id',             '=', $rental_id],
                    ['company_id',            '=', $company_id],
                    ['actual_delivery_date',  '!=', null]
                ])->first();

            $this->updateByRentalAndCompany($rental_id, $company_id, array('actual_delivery_date' => $rental_delivered->actual_delivery_date));

            return true;
        }

        return false;
    }

    public function checkAllEquipmentsWithdrawal(int $rental_id, int $company_id): bool
    {
        $rental_dont_withdrawal = $this->select('rental_id', 'id')
            ->from('rental_equipments')
            ->where([
                'rental_id'              => $rental_id,
                'company_id'             => $company_id,
                'actual_withdrawal_date' => null
            ])->first();

        if (!$rental_dont_withdrawal) {
            $rental_withdrawal = $this->select(DB::raw('max(actual_withdrawal_date) as actual_withdrawal_date'))
                ->from('rental_equipments')
                ->where([
                    ['rental_id',              '=', $rental_id],
                    ['company_id',             '=', $company_id],
                    ['actual_withdrawal_date', '!=', null]
                ])->first();

            $this->updateByRentalAndCompany($rental_id, $company_id, array('actual_withdrawal_date' => $rental_withdrawal->actual_withdrawal_date));

            return true;
        }

        return false;
    }
}
