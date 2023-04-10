<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Residue extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id', 'name', 'user_insert', 'user_update'
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

    public function getResidues(int $company_id)
    {
        return $this->where('company_id', $company_id)->get();
    }

    public function getResidue(int $company_id, int $residue_id)
    {
        return $this->where(['company_id' => $company_id, 'id' => $residue_id])->get();
    }

    public function insert(array $data)
    {
        return $this->create($data);
    }

    public function remove(int $company_id, int $residue_id)
    {
        return $this->where(['id' => $residue_id, 'company_id' => $company_id])->delete();
    }

    public function edit($data, $equipment_id)
    {
        return $this->where('id', $equipment_id)->update($data);
    }

    public function getResidues_In(int $company_id, array $resideus)
    {
        return $this->where('company_id', $company_id)->whereIn('id', $resideus)->get();
    }

    public function getFetchResidues(int $company_id, int $init = null, int $length = null, string $searchDriver = null, array $orderBy = array())
    {
        $residue = $this ->select('id', 'name', 'created_at')
            ->where('company_id', $company_id);
        if ($searchDriver)
            $residue->where('name', 'like', "%{$searchDriver}%");

        if (count($orderBy) !== 0) $residue->orderBy($orderBy['field'], $orderBy['order']);
        else $residue->orderBy('name', 'asc');

        if ($init !== null && $length !== null) $residue->offset($init)->limit($length);

        return $residue->get();
    }

    public function getCountFetchResidues(int $company_id, string $searchDriver = null)
    {
        $residue = $this->where('company_id', $company_id);
        if ($searchDriver)
            $residue->where('name', 'like', "%{$searchDriver}%");

        return $residue->count();
    }
}
