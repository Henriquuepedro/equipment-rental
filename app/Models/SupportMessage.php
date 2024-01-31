<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportMessage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'support_id',
        'company_id',
        'user_created',
        'description',
        'sent_by'
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

    public function getByCompany(int $support_id)
    {
        return $this->where('support_id', $support_id)->get();
    }

    public function getBySupportId(int $company_id, int $support_id)
    {
        return $this->where(['company_id' => $company_id, 'support_id' => $support_id])->get();
    }

}
