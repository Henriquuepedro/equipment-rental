<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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

    /**
     * Get the phone associated with the rental_payment.
     */
    public function rental_payment(): HasMany
    {
        return $this->hasMany(RentalPayment::class);
    }

    /**
     * Get the phone associated with the rental_equipment.
     */
    public function rental_equipment(): HasMany
    {
        return $this->hasMany(RentalEquipment::class);
    }

    /**
     * Get the phone associated with the client.
     */
    public function client(): HasOne
    {
        return $this->HasOne(Client::class, 'id', 'client_id');
    }

    /**
     * Get the phone associated with the rental_residue.
     */
    public function rental_residue(): HasMany
    {
        return $this->HasMany(RentalResidue::class);
    }

    public function insert(array $data)
    {
        return $this->create($data);
    }

    public function updateByRentalAndCompany(int $company_id, int $rental_id, array $data)
    {
        return $this->where(array('id' => $rental_id, 'company_id' => $company_id))->first()->fill($data)->save();
    }

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

    /**
     * @deprecated Não está mais em uso.
     *
     * @param int $company_id
     * @param array $filters
     * @param int|null $init
     * @param int|null $length
     * @param string|null $search_rental
     * @param array $order_by
     * @param string|null $type_rental
     * @return mixed
     */
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
                        ->where('rentals.company_id', $company_id)
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
                        ['rental_equipments.actual_delivery_date', '<>', null],
                        ['rental_equipments.actual_withdrawal_date', '<>', null]
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

    /**
     * @param int $company_id
     * @return int
     */
    public function getCountRentals(int $company_id): int
    {
        return $this->where('company_id', $company_id)->count();
    }

    public function getRental(int $company_id, int $rental_id)
    {
        return $this->where(['id' => $rental_id, 'company_id' => $company_id])->first();
    }

    public function remove(int $company_id, int $rental_id)
    {
        return $this->getRental($company_id, $rental_id)->delete();
    }

    public function getCountTypeRentals(int $company_id, int $client, string $start_date, string $end_date, string $date_filter_by, bool $no_date_to_withdraw): array
    {
        $data = array();

        $where_date_filter = match ($date_filter_by) {
            'created_at'        => 'rentals.created_at',
            'delivery'          => 'rental_equipments.actual_delivery_date',
            'withdraw'          => 'rental_equipments.actual_withdrawal_date',
            'expected_delivery' => 'rental_equipments.expected_delivery_date',
            'expected_withdraw' => 'rental_equipments.expected_withdrawal_date',
            default             => null,
        };

        if (is_null($where_date_filter)) {
            return array(
                'deliver'   => 0,
                'withdraw'  => 0,
                'finished'  => 0
            );
        }

        foreach (array(
             'deliver' => array(
                 ['rental_equipments.actual_delivery_date', '=', NULL]
             ),
             'withdraw' => array(
                 ['rental_equipments.actual_delivery_date', '<>', NULL],
                 ['rental_equipments.actual_withdrawal_date', '=', NULL]
             ),
             'finished' => array(
                 ['rental_equipments.actual_delivery_date', '<>', NULL],
                 ['rental_equipments.actual_withdrawal_date', '<>', NULL]
             )
        ) as $type => $where) {
            $where = array_merge(array(['rentals.company_id', '=', $company_id]), $where);

            if ($client) {
                $where = array_merge(array(['rentals.client_id', '=', $client]), $where);
            }

            $query = $this->from('rental_equipments')
                ->join('rentals', 'rental_equipments.rental_id', '=', 'rentals.id')
                ->where($where);

            if ($no_date_to_withdraw) {
                $query->where($where_date_filter, null);
            } else {
                $query->whereBetween($where_date_filter, ["$start_date 00:00:00", "$end_date 23:59:59"]);
            }

            $data[$type] = $query
                ->groupBy('rental_equipments.rental_id')
                ->get()
                ->count();
        }

        return $data;
    }

    public function checkAllEquipmentsDelivered(int $company_id, int $rental_id): bool
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

            $this->updateByRentalAndCompany($company_id, $rental_id, array('actual_delivery_date' => $rental_delivered->actual_delivery_date));

            return true;
        }

        return false;
    }

    public function checkAllEquipmentsWithdrawal(int $company_id, int $rental_id): bool
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

            $this->updateByRentalAndCompany($company_id, $rental_id, array('actual_withdrawal_date' => $rental_withdrawal->actual_withdrawal_date));

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
        ->where('rentals.company_id', $company_id);

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

    public function getRentalFull(int $company_id, int $rental_id)
    {
        return $this->where(['id' => $rental_id, 'company_id' => $company_id])
            ->with([
                'rental_payment',
                'rental_equipment',
                'client',
                'rental_residue'
            ])
            ->first();
    }

    public function getRentalsForMonth(int $company_id, $year, $month)
    {
        return $this->where('company_id', $company_id)->whereYear('created_at', $year)->whereMonth('created_at', $month)->count();
    }

    public function getRentalsOpen(int $company_id)
    {
        return $this->where([
            'company_id'             => $company_id,
            'actual_withdrawal_date' => null
        ])->with([
            'client'
        ])->get();
    }
}
