<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Integration extends Model
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
        'active'
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

    public function edit($data, $id)
    {
        return $this->find($id)->fill($data)->save();
    }

    public function insert(array $data)
    {
        return $this->create($data);
    }

    public function getAllActive()
    {
        return $this->where('active', true)->get();
    }

    public function getByName(string $name)
    {
        return $this->where('name', $name)->first();
    }
}
