<?php

namespace Database\Seeders;

use App\Models\Config;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (empty(Config::all()->toArray())) {
            Config::create([
                'id'                            => 1,
                'company_id'                    => 1,
                'view_observation_client_rental'=> 1,
                'user_update'                   => 1
            ]);
        }
    }
}
