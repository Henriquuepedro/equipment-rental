<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IntegrationToStore extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'integration_id',
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

    public function edit(array $data, int $integration_id, int $company_id)
    {
        return $this->where(array('company_id' => $company_id, 'integration_id' => $integration_id))->first()->fill($data)->save();
    }

    public function insert(array $data)
    {
        return $this->create($data);
    }

    public function getByCompany(int $company_id)
    {
        return $this->where('company_id', $company_id)->get();
    }

    public function getByCompanyAndIntegration(int $company_id, int $integration_id)
    {
        return $this->where(array('company_id' => $company_id, 'integration_id' => $integration_id))->first();
    }
}
