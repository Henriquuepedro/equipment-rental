<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            CompanySeeder::class,
            PermissionSeeder::class,
            UserSeeder::class,
            ConfigSeeder::class,
            NationalitySeeder::class,
            MaritalStatusSeeder::class,
            FormPaymentSeeder::class
        ]);
    }
}
