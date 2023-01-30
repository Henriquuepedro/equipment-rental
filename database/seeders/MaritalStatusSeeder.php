<?php

namespace Database\Seeders;

use App\Models\MaritalStatus;
use Illuminate\Database\Seeder;
use App\Models\Nationality;

class MaritalStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $marital_statuses = [
            ["id" => 1, "name" => "Solteiro(a)"],
            ["id" => 2, "name" => "Casado(a)"],
            ["id" => 3, "name" => "Separado(a)"],
            ["id" => 4, "name" => "Divorciado(a)"],
            ["id" => 5, "name" => "Viúvo(a)"],
            ["id" => 6, "name" => "Outro"]
        ];

        if (empty(MaritalStatus::all()->toArray())) {
            foreach ($marital_statuses as $marital_status) {
                MaritalStatus::create($marital_status);
            }
        }
    }
}
