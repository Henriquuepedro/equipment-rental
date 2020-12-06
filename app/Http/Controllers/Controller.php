<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function transformMoneyBr_En($value)
    {
        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);
        $value = filter_var($value, FILTER_VALIDATE_FLOAT);

        return (float)$value;
    }

    public function hasPermission($permission)
    {
        $permissions = empty(auth()->user()->permission) ? [] : json_decode(auth()->user()->permission);
        $permission = Permission::query()->where('name', $permission)->first();
        if (!$permission) return false;

        $permission = $permission->id;

        return in_array($permission, $permissions) || $this->hasAdmin();
    }

    public function hasAdmin()
    {
        return auth()->user()->type_user === 1 || auth()->user()->type_user === 2;
    }

    public function hasAdminMaster()
    {
        return auth()->user()->type_user === 2;
    }

    public function isAjax(){
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    }

    public function mask($val, $mask) {
        $maskared = '';
        $k = 0;
        for($i = 0; $i<=strlen($mask)-1; $i++) {
            if($mask[$i] == '#') {
                if(isset($val[$k])) $maskared .= $val[$k++];
            } else {
                if(isset($mask[$i])) $maskared .= $mask[$i];
            }
        }
        return $maskared;
    }
}
