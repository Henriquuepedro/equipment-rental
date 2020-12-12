<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipament extends Model
{
    use HasFactory;

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

    public function remove($equipament_id, $company_id)
    {
        return $this->where(['id' => $equipament_id, 'company_id' => $company_id])->delete();
    }

    public function edit($data, $equipament_id)
    {
        return $this->where('id', $equipament_id)->update($data);
    }

    public function getEquipaments($company_id, $init = null, $length = null, $searchEquipament = null, $orderBy = array())
    {
        $equipament = $this->where('company_id', $company_id);
        if ($searchEquipament)
            $equipament->where(function($query) use ($searchEquipament) {
                $query->where('name', 'like', "%{$searchEquipament}%")
                    ->orWhere('reference', 'like', "%{$searchEquipament}%")
                    ->orWhere('stock', 'like', "%{$searchEquipament}%");
            });

        if (count($orderBy) !== 0) $equipament->orderBy($orderBy['field'], $orderBy['order']);
        else $equipament->orderBy('id', 'desc');

        if ($init !== null && $length !== null) $equipament->offset($init)->limit($length);

        return $equipament->get();
    }

    public function getCountEquipaments($company_id, $searchEquipament = null)
    {
        $equipament = $this->where('company_id', $company_id);
        if ($searchEquipament)
            $equipament->where(function($query) use ($searchEquipament) {
                $query->where('name', 'like', "%{$searchEquipament}%")
                    ->orWhere('reference', 'like', "%{$searchEquipament}%")
                    ->orWhere('stock', 'like', "%{$searchEquipament}%");
            });

        return $equipament->count();
    }

    public function getEquipament($equipament_id, $company_id)
    {
        return $this->where(['id' => $equipament_id, 'company_id' => $company_id])->first();
    }

    public function getEquipamentRental($company_id, $searchEquipament, $getCacamba, $equipamentInUse)
    {
        // faço essa engembra pra pegar o volume da caçmba
        // não salvo no banco o nome caçamba, então faço o
        // explode e tento pegar o volume digitado
        $_searchEquipament = explode(' ', str_replace(['m³', 'm3', 'm'],'',$searchEquipament));

        $equipaments = $this->where('company_id', $company_id)
                    ->where(function($query) use ($searchEquipament, $_searchEquipament) {
                        $query->where('id', 'like', "%{$searchEquipament}%")
                            ->orWhere('name', 'like', "%{$searchEquipament}%")
                            ->orWhere('reference', 'like', "%{$searchEquipament}%")
                            ->orWhereIn('volume', $_searchEquipament);
                    });
        if ($equipamentInUse && count($equipamentInUse))
            $equipaments = $equipaments->whereNotIn('id', $equipamentInUse);

        if ($getCacamba)
            $equipaments->orWhere('name', null);

        return $equipaments->get();
    }
}
