<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentRentalMtr extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'rental_mtr_id',
        'rental_equipment_id',
        'residue_id',
        'quantity',
        'classification',
        'date'
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

    public function remove(int $id, int $company_id)
    {
        return $this->getByid($id, $company_id)->delete();
    }

    public function edit(array $data, int $id)
    {
        return $this->where('id', $id)->first()->fill($data)->save();
    }

    public function getByid(int $id, int $company_id)
    {
        return $this->where(['id' => $id, 'company_id' => $company_id])->first();
    }

    public function getByRentalMtr(int $rental_mtr_id, int $company_id)
    {
        return $this->where(['rental_mtr_id' => $rental_mtr_id, 'company_id' => $company_id])->get();
    }
}
