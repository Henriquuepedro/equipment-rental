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

    public function getRentals($company_id, $filters, $init = null, $length = null, $searchDriver = null, $orderBy = array(), $typeRental = null)
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
                    $rental->where('rentals.actual_delivery_date', null);
                    break;
                case 'withdraw':
                    $rental->where([
                        ['rentals.actual_delivery_date', '<>', null],
                        ['rentals.actual_withdrawal_date', '=', null]
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

        if ($filters['client'] !== null)
            $rental->where('rentals.client_id', $filters['client']);

        if (count($orderBy) !== 0) $rental->orderBy($orderBy['field'], $orderBy['order']);
        else $rental->orderBy('rentals.code', 'asc');

        if ($init !== null && $length !== null) $rental->offset($init)->limit($length);

        return $rental->get();
    }

    public function getCountRentals($company_id, $filters, $searchDriver = null, $typeRental = null)
    {
        $rental = $this ->join('clients','clients.id','=','rentals.client_id')
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
                    $rental->where('rentals.actual_delivery_date', null);
                    break;
                case 'withdraw':
                    $rental->where([
                        ['rentals.actual_delivery_date', '<>', null],
                        ['rentals.actual_withdrawal_date' => null]
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

        if ($filters['client'] !== null)
            $rental->where('rentals.client_id', $filters['client']);

        return $rental->count();
    }

    public function getRental($rental_id, $company_id)
    {
        return $this->where(['id' => $rental_id, 'company_id' => $company_id])->first();
    }

    public function remove($rental_id, $company_id)
    {
        return $this->where(['id' => $rental_id, 'company_id' => $company_id])->delete();
    }

    public function getCountTypeRentals($company_id)
    {
        return DB::select("
            SELECT * FROM(
                SELECT COUNT(*) AS qty_rental
                FROM rentals
                WHERE actual_delivery_date IS NULL
                AND company_id = {$company_id}

                UNION ALL

                SELECT COUNT(*) AS qty_rental
                FROM rentals
                WHERE actual_delivery_date IS NOT NULL
                AND actual_withdrawal_date IS NULL
                AND company_id = {$company_id}

                UNION ALL

                SELECT COUNT(*) AS qty_rental
                FROM rentals
                WHERE actual_delivery_date IS NOT NULL
                AND actual_withdrawal_date IS NOT NULL
                AND company_id = {$company_id}
            ) AS qty_rentals
        ");
    }
}
