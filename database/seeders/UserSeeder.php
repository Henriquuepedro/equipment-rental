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
            User::create([
                'id'                => 1,
                'name'              => 'Locaí',
                'username'          => 'locai',
                'email'             => 'contato@locai.com.br',
                'email_verified_at' => dateNowInternational(),
                'phone'             => '11987654321',
                'password'          => Hash::make('123'),
                'company_id'        => 1,
                'permission'        => json_encode([]),
                'type_user'         => User::$TYPE_USER['master'],
                'style_template'    => User::$STYLE_TEMPLATE['black'],
            ]);
        }
    }
}
