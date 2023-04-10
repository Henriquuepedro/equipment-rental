<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'phone',
        'password',
        'company_id',
        'profile',
        'active',
        'permission',
        'last_login_at',
        'last_login_ip',
        'last_access_at',
        'logout',
        'type_user',
        'style_template'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function insert($data)
    {
        return $this->create($data);
    }

    public function edit($data, $user_id, $company_id)
    {
        return $this->where(['id' => $user_id, 'company_id' => $company_id])->update($data);
    }

    public function remove($user_id, $company_id)
    {
        return $this->where(['id' => $user_id, 'company_id' => $company_id])->delete();
    }

    public function getUser($user_id, $company_id)
    {
        return $this->where(['id' => $user_id, 'company_id' => $company_id])->first();
    }

    public function getUsersCompany($company_id)
    {
        return $this->where('company_id', $company_id)->get();
    }

    public function logout()
    {
        auth()->user()->logout();

        return redirect()->route('user.login');
    }

    public function getPermissionUser($user_id, $company_id)
    {
        return $this->where(['id' => $user_id, 'company_id' => $company_id])->first();
    }

    public function getUserNotUpdate($data, $user_id, $company_id)
    {
        $arrCheckUser = [
            'id' => $user_id,
            'company_id' => $company_id
        ];

        $arrCheckUser = array_merge($arrCheckUser, $data);

        return $this->where($arrCheckUser)->count() ? true : false;
    }
}
