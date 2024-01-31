<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Support extends Model
{
    use HasFactory;

    /**
     * status
     *
     * open
     * ongoing
     * awaiting_return
     * closed
     */

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'user_created',
        'subject',
        'description',
        'path_files',
        'status',
        'priority',
        'open',
        'closed_at'
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

    public function updateBySupportAndCompany(int $company_id, int $support_id, array $data)
    {
        return $this->getByCompany($company_id, $support_id)->fill($data)->save();
    }

    public function updateBySupport(int $support_id, array $data)
    {
        return $this->getByid($support_id)->fill($data)->save();
    }

    public function getAllByCompany(int $company_id, array $filter = [])
    {
        $support = $this->select('supports.*', 'companies.name as company_name', 'users.name as user_name')
            ->join('companies', 'supports.company_id', '=', 'companies.id')
            ->join('users', 'supports.user_created', '=', 'users.id')
            ->where('company_id', $company_id);

        if (!empty($filter)) {
            $support->where($filter);
        }

        return $support->orderBy('supports.id', 'DESC')->get();
    }

    public function getAll(array $filter = [])
    {
        $support = $this->select('supports.*', 'companies.name as company_name', 'users.name as user_name')
            ->join('companies', 'supports.company_id', '=', 'companies.id')
            ->join('users', 'supports.user_created', '=', 'users.id');

        if (!empty($filter)) {
            $support->where($filter);
        }

        return $support->orderBy('supports.id', 'DESC')->get();
    }

    public function getByCompany(int $company_id, int $support_id)
    {
        return $this->where(['company_id' => $company_id, 'id' => $support_id])->first();
    }

    public function getByid(int $support_id)
    {
        return $this->find($support_id);
    }
}
