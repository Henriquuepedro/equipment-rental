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

    public function getNextCode(int $company_id): int
    {
        $max_code = $this->where(array('company_id' => $company_id, 'deleted' => false))->max('code');
        if ($max_code) {
            return ++$max_code;
        }

        return 1;
    }

    public function getRentals(int $company_id, array $filters, int $init = null, int $length = null, string $search_rental = null, array $order_by = array(), string $type_rental = null)
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
                        ->where(['rentals.company_id' => $company_id, 'rentals.deleted' => false])
                        ->whereBetween('rentals.created_at', ["{$filters['dateStart']} 00:00:00", "{$filters['dateFinish']} 23:59:59"]);
        if ($search_rental)
            $rental->where(function($query) use ($search_rental) {
                $query->where('rentals.code', 'like', "%".(int)onlyNumbers($search_rental)."%")
                    ->orWhere('clients.name', 'like', "%$search_rental%")
                    ->orWhere('rentals.address_name', 'like', "%$search_rental%")
                    ->orWhere('rentals.created_at', 'like', "%$search_rental%");
            });

        if ($type_rental) {
            switch ($type_rental) {
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

        if (count($order_by) !== 0) {
            $rental->orderBy($order_by['field'], $order_by['order']);
        } else {
            $rental->orderBy('rentals.code', 'asc');
        }

        $rental->groupBy($type_rental === 'finished' ? 'rentals.id' : 'rental_equipments.rental_id');

        if ($init !== null && $length !== null) {
            $rental->offset($init)->limit($length);
        }

        return $rental->get();
    }

    public function getCountRentals(int $company_id, array $filters, string $search_rental = null, string $type_rental = null)
    {
        $rental = $this ->join('clients','clients.id','=','rentals.client_id')
            ->join('rental_equipments','rental_equipments.rental_id','=','rentals.id')
            ->where('rentals.company_id', $company_id)
            ->whereBetween('rentals.created_at', ["{$filters['dateStart']} 00:00:00", "{$filters['dateFinish']} 23:59:59"]);

        if ($search_rental) {
            $rental->where(function ($query) use ($search_rental) {
                $query->where('rentals.code', 'like', "%".(int)onlyNumbers($search_rental)."%")
                    ->orWhere('clients.name', 'like', "%$search_rental%")
                    ->orWhere('rentals.address_name', 'like', "%$search_rental%")
                    ->orWhere('rentals.created_at', 'like', "%$search_rental%");
            });
        }

        if ($type_rental) {
            switch ($type_rental) {
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

        $rental->groupBy($type_rental === 'finished' ? 'rentals.id' : 'rental_equipments.rental_id');

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

    public function getCountTypeRentals(int $company_id, int $client, string $start_date, string $end_date): array
    {
        $data = array();

        foreach (array(
             'deliver' => array(
                 ['rental_equipments.actual_delivery_date', '=', NULL]
             ),
             'withdraw' => array(
                 ['rental_equipments.actual_delivery_date', '<>', NULL],
                 ['rental_equipments.actual_withdrawal_date', '=', NULL]
             ),
             'finished' => array(
                 ['rentals.actual_delivery_date', '<>', NULL],
                 ['rentals.actual_withdrawal_date', '<>', NULL]
             )
        ) as $type => $where) {
            $where = array_merge(array(['rentals.company_id', '=', $company_id]), $where);

            if ($client) {
                $where = array_merge(array(['rentals.client_id', '=', $client]), $where);
            }

            $query = $this->from($type === 'finished' ? 'rentals' : 'rental_equipments')
                ->where($where)
                ->whereBetween('rentals.created_at', [$start_date, $end_date]);

            if ($type !== 'finished') {
                $query->join('rentals', 'rental_equipments.rental_id', '=', 'rentals.id');
            }

            $data[$type] = $query
                ->groupBy($type === 'finished' ? 'rentals.id' : 'rental_equipments.rental_id')
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

    public function getRentalsToReportWithFilters(int $company_id, array $filters, bool $synthetic = true)
    {
        $rental = $this ->select(
            'rentals.id',
            'rentals.code',
            'clients.name as client_name',
            'rentals.net_value',
            'rentals.address_name',
            'rentals.address_number',
            'rentals.address_zipcode',
            'rentals.address_complement',
            'rentals.address_neigh',
            'rentals.address_city',
            'rentals.address_state',
            'rentals.created_at',
            'rental_equipments.actual_delivery_date',
            'rental_equipments.actual_withdrawal_date',
            'equipments.name',
            'equipments.volume'
        )
        ->join('clients','clients.id','=','rentals.client_id')
        ->join('rental_equipments','rental_equipments.rental_id','=','rentals.id')
        ->join('equipments','equipments.id','=','rental_equipments.equipment_id')
        ->where(['rentals.company_id' => $company_id, 'rentals.deleted' => false]);

        // Filtrar registros por data.
        switch ($filters['_date_filter']) {
            case 'created':
            default:
                $date_filter = 'rentals.created_at';
                break;
            case 'delivered':
                $date_filter = 'rental_equipments.actual_delivery_date';
                break;
            case 'withdrawn':
                $date_filter = 'rental_equipments.actual_withdrawal_date';
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

        // Filtrou o motorista.
        if (!empty($filters['_driver'])) {
            $rental->where(function ($query) use ($filters) {
                $query->where('rental_equipments.actual_driver_delivery', $filters['_driver'])
                    ->orWhere('rental_equipments.actual_driver_withdrawal', $filters['_driver']);
            });
        }

        // Filtrou o veículo.
        if (!empty($filters['_vehicle'])) {
            $rental->where(function ($query) use ($filters) {
                $query->where('rental_equipments.actual_vehicle_delivery', $filters['_vehicle'])
                    ->orWhere('rental_equipments.actual_vehicle_withdrawal', $filters['_vehicle']);
            });
        }

        // Filtrar registro por situação, caso foi informado.
        if (!empty($filters['_status'])) {
            switch ($filters['_status']) {
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
                        ['rental_equipments.actual_delivery_date', '<>', null],
                        ['rental_equipments.actual_withdrawal_date', '<>', null]
                    ]);
                    break;
            }
        }

        // Ordena os registros.
        $rental->orderBy('rentals.code', 'DESC');

        // Agrupa os registros por locação.
        if ($synthetic) {
            $rental->groupBy('rentals.id');
        }

        return $rental->get();
    }
}
