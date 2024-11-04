<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentalMtr extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'rental_id',
        'driver_id',
        'disposal_place_id',
        'user_insert',
        'user_update',
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

    public function remove($id, $company_id)
    {
        return $this->getByid($id, $company_id)->delete();
    }

    public function edit($data, $id)
    {
        return $this->where('id', $id)->first()->fill($data)->save();
    }

    public function getByid($id, $company_id)
    {
        return $this->where(['id' => $id, 'company_id' => $company_id])->first();
    }

    public function getByRental($rental_id, $company_id)
    {
        return $this->where(['rental_id' => $rental_id, 'company_id' => $company_id])->first();
    }
}
