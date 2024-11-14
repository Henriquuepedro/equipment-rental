<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'text', 'group_name', 'group_text', 'active'
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

    public function getAllPermissions()
    {
        return $this->get();
    }

    public function getPermissionByName(string $name)
    {
        return $this->select('id')->where('name', $name)->first();
    }

    public function getGroupPermissions()
    {
        return $this->select(['group_name', 'group_text'])->groupBy('group_text')->get();
    }

    public function getPermissionByGroup($group)
    {
        return $this->where('group_name', $group)->get();
    }

    public function getById(string $id)
    {
        return $this->find($id);
    }
}
