<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'user_insert',
        'user_update',
        'expires_in',
        'only_permission',
        'read',
        'read_at',
        'user_read_by',
        'title',
        'description',
        'title_icon',
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

    public function insert($data)
    {
        return $this->create($data);
    }

    public function edit(array $data, int $id)
    {
        return $this->where('id', $id)->first()->fill($data)->save();
    }

    public function remove(int $id)
    {
        return $this->get($id)->delete();
    }

    public function getNotReadLastRows(int $company_id, int $count = null)
    {
        $query = $this->select('id', 'title', 'title_icon', 'created_at')
            ->where([
                'active' => true,
                'read' => false,
            ])->where(function($query) use ($company_id) {
                $query->where('company_id', null)
                    ->orWhere('company_id', $company_id);
            })->where(function($query) {
                $query->where('expires_in', null)
                    ->orWhere('expires_in', '>', dateNowInternational());
            })->where(function($query) {
                $query->where('only_permission', null)
                    ->orWhereIn('only_permission', getAllPermissions());
            })->orderBy('id', 'desc');

        if (is_null($count)) {
            return $query->count();
        }

        return $query->limit($count)->get();
    }

    public function get(int $id)
    {
        return $this->find($id);
    }

    public function getByid(int $company_id, int $id)
    {
        return $this->where(['id' => $id])
            ->where(function($query) use ($company_id) {
                $query->where('company_id', null)
                    ->orWhere('company_id', $company_id);
            })->first();
    }
}
