<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (empty(Company::all()->toArray())) {
            Company::create([
                'id'            => 1,
                'name'          => Str::random(10),
                'fantasy'       => Str::random(10),
                'type_person'   => 'pj',
                'cpf_cnpj'      => '00000000000099',
                'email'         => Str::random(10) . '@gmail.com',
                'phone_1'       => '11987654321',
                'contact'       => 'Teste'
            ]);
        }
    }
}
