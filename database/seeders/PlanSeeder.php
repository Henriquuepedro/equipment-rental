<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /*$plans = [
            ['name' => 'starter_monthly', 'description' => 'Plano básico mensal', 'value' => 49.90, 'quantity_equipment' => 30, 'plan_type' => 'monthly'],
            ['name' => 'standard_monthly', 'description' => 'Plano intermediário mensal', 'value' => 54.90, 'quantity_equipment' => 50, 'plan_type' => 'monthly'],
            ['name' => 'plus_monthly', 'description' => 'Plano avançado mensal', 'value' => 64.90, 'quantity_equipment' => 100, 'plan_type' => 'monthly'],
            ['name' => 'enterprise_monthly', 'description' => 'Plano empresarial mensal', 'value' => 99.90, 'quantity_equipment' => 1000, 'plan_type' => 'monthly'],
        ];

        foreach ($plans as $plan) {
            if (Plan::where(array('name' => $plan['name'], 'plan_type' => $plan['plan_type']))->count() === 0) {
                Plan::create($plan);
            }
        }*/
    }
}
