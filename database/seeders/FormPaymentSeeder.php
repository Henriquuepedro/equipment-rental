<?php

namespace Database\Seeders;

use App\Models\FormPayment;
use Illuminate\Database\Seeder;

class FormPaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $form_payments = [
            ["id" => 1, "name" => "Banco"],
            ["id" => 2, "name" => "Boleto"],
            ["id" => 3, "name" => "Cartão de Crédito"],
            ["id" => 4, "name" => "Cartão de Débito"],
            ["id" => 5, "name" => "Cheque"],
            ["id" => 6, "name" => "Depósito"],
            ["id" => 7, "name" => "Dinheiro"],
            ["id" => 8, "name" => "Permuta"],
            ["id" => 9, "name" => "Pix"],
            ["id" => 10, "name" => "Transferência"],
        ];

        foreach ($form_payments as $form_payment) {
            if (!FormPayment::where('name', $form_payment['name'])->first()) {
                FormPayment::create($form_payment);
            }
        }
    }
}
