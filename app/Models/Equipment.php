<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Equipment extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'equipments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id', 'name', 'reference', 'stock', 'value', 'manufacturer', 'volume', 'user_insert', 'user_update'
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

    public function remove($equipment_id, $company_id)
    {
        return $this->getEquipment($equipment_id, $company_id)->delete();
    }

    public function edit($data, $equipment_id)
    {
        return $this->where('id', $equipment_id)->first()->fill($data)->save();
    }

    public function getEquipments($company_id, $init = null, $length = null, $searchEquipment = null, $orderBy = array(), $getCacamba = false, $select = '*')
    {
        $equipment = $this->select($select)->where('company_id', $company_id);
        if ($searchEquipment) {
            $equipment->where(function ($query) use ($searchEquipment, $getCacamba) {
                $query->where('name', 'like', "%{$searchEquipment}%")
                    ->orWhere('reference', 'like', "%{$searchEquipment}%")
                    ->orWhere('stock', 'like', "%{$searchEquipment}%");

                if ($getCacamba) {
                    $query->orWhere('name', null);
                }
            });
        }

        if (count($orderBy) !== 0) {
            $equipment->orderBy($orderBy['field'], $orderBy['order']);
        } else {
            $equipment->orderBy('id', 'desc');
        }

        if ($init !== null && $length !== null) {
            $equipment->offset($init)->limit($length);
        }

        return $equipment->get();
    }

    public function getCountEquipments($company_id, $searchEquipment = null, $getCacamba = false)
    {
        $equipment = $this->where('company_id', $company_id);
        if ($searchEquipment) {
            $equipment->where(function ($query) use ($searchEquipment, $getCacamba) {
                $query->where('name', 'like', "%{$searchEquipment}%")
                    ->orWhere('reference', 'like', "%{$searchEquipment}%")
                    ->orWhere('stock', 'like', "%{$searchEquipment}%");

                if ($getCacamba) {
                    $query->orWhere('name', null);
                }
            });
        }

        return $equipment->count();
    }

    public function getEquipment($equipment_id, $company_id)
    {
        return $this->from(DB::raw('equipments force index(id_company)'))->where(['id' => $equipment_id, 'company_id' => $company_id])->first();
    }

    public function getEquipmentRental($company_id, $searchEquipment, $getCacamba, $equipmentInUse)
    {
        // faço essa engembra pra pegar o volume da caçamba
        // não salvo no banco o nome caçamba, então faço o
        // explode e tento pegar o volume digitado
        $_searchEquipment = explode(' ', str_replace(['m³', 'm3', 'm'],'',$searchEquipment));

        $equipments = $this->from(DB::raw('equipments force index(company_id_name_reference_volume)'))
                    ->where('company_id', $company_id)
                    ->where(function($query) use ($searchEquipment, $_searchEquipment, $getCacamba) {
                        $query->where('id', 'like', "%{$searchEquipment}%")
                            ->orWhere('name', 'like', "%{$searchEquipment}%")
                            ->orWhere('reference', 'like', "%{$searchEquipment}%")
                            ->orWhereIn('volume', $_searchEquipment);

                        if ($getCacamba) $query->orWhere('name', null);
                    });
        if ($equipmentInUse && count($equipmentInUse)) {
            $equipments = $equipments->whereNotIn('id', $equipmentInUse);
        }

        return $equipments->get();
    }

    public function getMultipleEquipments(array $equipments_id, $company_id)
    {
        return $this->from(DB::raw('equipments force index(id_company)'))->where('company_id', $company_id)->whereIn('id', $equipments_id)->get();
    }

    public function getEquipments_In($company_id, $equipments_id)
    {
        return $this->from(DB::raw('equipments force index(id_company)'))
                    ->where('company_id', $company_id)
                    ->whereIn('id', $equipments_id)
                    ->get();
    }

    public function getAllStockEquipment(int $company_id, int $ignore_id = null): ?int
    {
        $quantity_equipment = 0;
        $company = (new Company())->getPlanCompany($company_id);
        if ($company) {
            if (is_null($company->quantity_equipment)) {
                return null;
            }
            $quantity_equipment = $company->quantity_equipment;
        }

        $total_stock = $this->select(DB::raw('SUM(stock) as total'))->where(['company_id' => $company_id]);

        if (!is_null($ignore_id)) {
            $total_stock->where('id', '!=', $ignore_id);
        }

        $total_stock = $total_stock->first();

        if (empty($total_stock)) {
            return 0;
        }

        $stock_available = $quantity_equipment - (int)$total_stock->total;

        if ($stock_available < 0) {
            return 0;
        }

        return $stock_available;
    }
}
