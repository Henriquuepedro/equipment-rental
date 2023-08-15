<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (empty(User::all()->toArray())) {
            $permissions = array_map(function($permission) {
                return $permission['id'];
            }, Permission::all()->toArray());

            User::create([
                'id'            => 1,
                'name'          => 'Administrador',
                'username'      => 'admin',
                'email'         => 'admin@admin.com',
                'phone'         => Str::random(10),
                'password'      => Hash::make('123'),
                'company_id'    => 1,
                'active'        => 1,
                'permission'    => json_encode($permissions),
                'type_user'     => 2,
                'logout'        => 0
            ]);
        }
    }
}
