<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'value',
        'from_value',
        'quantity_equipment',
        'highlight',
        'month_time',
        'allowed_users'
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

    public function getById(int $id)
    {
        return $this->find($id);
    }

    public function updateById(array $data, int $id)
    {
        return $this->where('id', $id)->first()->fill($data)->save();
    }

    public function getByMonthTime(int $month_time)
    {
        return $this->where('month_time', $month_time)->orderBy('value')->get();
    }

    public function getPlanAtLowerPrice()
    {
        return $this->orderBy('value', 'ASC')->first();
    }
}
