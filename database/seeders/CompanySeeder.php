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
                'id'                    => 1,
                'name'                  => 'Locaí',
                'fantasy'               => 'Locaí',
                'type_person'           => 'pj',
                'cpf_cnpj'              => '00000000000001',
                'email'                 => 'contato@locai.com.br',
                'phone_1'               => '11987654321',
                'contact'               => 'Locaí',
                'plan_expiration_date'  => sumDate(dateNowInternational(), 10)
            ]);
        }
    }
}
