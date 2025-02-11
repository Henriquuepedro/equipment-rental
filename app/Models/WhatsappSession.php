<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappSession extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'session_id'
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

    public function getByCompany(int $company_id)
    {
        return $this->where('company_id', $company_id)->first();
    }
}
