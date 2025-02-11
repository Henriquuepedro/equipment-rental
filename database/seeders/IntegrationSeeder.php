<?php

namespace Database\Seeders;

use App\Models\Integration;
use Illuminate\Database\Seeder;

class IntegrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $integrations = [
            ["name" => 'whatsapp', "description" => "WhatsApp", 'active' => true],
        ];

        if (empty(Integration::all()->toArray())) {
            foreach ($integrations as $integration) {
                Integration::create($integration);
            }
        }
    }
}
