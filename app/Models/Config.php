<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Config extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'view_observation_client_rental', 'user_update'
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

    public function getConfigColumnAndValue($company_id): array
    {
        return [
            'column' => $this->getConnection()->getSchemaBuilder()->getColumnListing($this->getTable()),
            'value'  => $this->where('company_id', $company_id)->first()
        ];
    }

    public function edit($data, $company_id)
    {
        return $this->where('company_id', $company_id)->update($data);
    }

    public function getConfigCompany($company_id, $config): bool
    {
        $db = $this->where('company_id', $company_id)->first();
        return (bool)$db->$config;
    }
}
