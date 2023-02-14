<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            ['id' => 1, 'name' => 'ClientView', 'text' => 'Visualizar', 'group_name' => 'client', 'group_text' => 'Cadastro de Cliente', 'auto_check' => '[]', 'active' => '1'],
            ['id' => 2, 'name' => 'ClientCreatePost', 'text' => 'Cadastrar', 'group_name' => 'client', 'group_text' => 'Cadastro de Cliente', 'auto_check' => '[1]', 'active' => '1'],
            ['id' => 3, 'name' => 'ClientUpdatePost', 'text' => 'Atualizar', 'group_name' => 'client', 'group_text' => 'Cadastro de Cliente', 'auto_check' => '[1]', 'active' => '1'],
            ['id' => 4, 'name' => 'ClientDeletePost', 'text' => 'Excluir', 'group_name' => 'client', 'group_text' => 'Cadastro de Cliente', 'auto_check' => '[1]', 'active' => '1'],
            ['id' => 5, 'name' => 'EquipmentView', 'text' => 'Visualizar', 'group_name' => 'equipament', 'group_text' => 'Cadastro de Equipamento', 'auto_check' => '[]', 'active' => '1'],
            ['id' => 6, 'name' => 'EquipmentCreatePost', 'text' => 'Cadastrar', 'group_name' => 'equipament', 'group_text' => 'Cadastro de Equipamento', 'auto_check' => '[5]', 'active' => '1'],
            ['id' => 7, 'name' => 'EquipmentUpdatePost', 'text' => 'Atualizar', 'group_name' => 'equipament', 'group_text' => 'Cadastro de Equipamento', 'auto_check' => '[5]', 'active' => '1'],
            ['id' => 8, 'name' => 'EquipmentDeletePost', 'text' => 'Excluir', 'group_name' => 'equipament', 'group_text' => 'Cadastro de Equipamento', 'auto_check' => '[5]', 'active' => '1'],
            ['id' => 9, 'name' => 'DriverView', 'text' => 'Visualizar', 'group_name' => 'driver', 'group_text' => 'Cadastro de Motorista', 'auto_check' => '[]', 'active' => '1'],
            ['id' => 10, 'name' => 'DriverCreatePost', 'text' => 'Cadastrar', 'group_name' => 'driver', 'group_text' => 'Cadastro de Motorista', 'auto_check' => '[9]', 'active' => '1'],
            ['id' => 11, 'name' => 'DriverUpdatePost', 'text' => 'Atualizar', 'group_name' => 'driver', 'group_text' => 'Cadastro de Motorista', 'auto_check' => '[9]', 'active' => '1'],
            ['id' => 12, 'name' => 'DriverDeletePost', 'text' => 'Excluir', 'group_name' => 'driver', 'group_text' => 'Cadastro de Motorista', 'auto_check' => '[9]', 'active' => '1'],
            ['id' => 13, 'name' => 'VehicleView', 'text' => 'Visualizar', 'group_name' => 'vehicle', 'group_text' => 'Cadastro de Motorista', 'auto_check' => '[]', 'active' => '1'],
            ['id' => 14, 'name' => 'VehicleCreatePost', 'text' => 'Cadastrar', 'group_name' => 'vehicle', 'group_text' => 'Cadastro de Veículo', 'auto_check' => '[13]', 'active' => '1'],
            ['id' => 15, 'name' => 'VehicleUpdatePost', 'text' => 'Atualizar', 'group_name' => 'vehicle', 'group_text' => 'Cadastro de Veículo', 'auto_check' => '[13]', 'active' => '1'],
            ['id' => 16, 'name' => 'VehicleDeletePost', 'text' => 'Excluir', 'group_name' => 'vehicle', 'group_text' => 'Cadastro de Veículo', 'auto_check' => '[13]', 'active' => '1'],
            ['id' => 17, 'name' => 'BudgetView', 'text' => 'Visualizar', 'group_name' => 'budget', 'group_text' => 'Controle de Orçamento', 'auto_check' => '[]', 'active' => '1'],
            ['id' => 18, 'name' => 'BudgetCreatePost', 'text' => 'Cadastrar', 'group_name' => 'budget', 'group_text' => 'Controle de Orçamento', 'auto_check' => '[17]', 'active' => '1'],
            ['id' => 21, 'name' => 'BudgetDeletePost', 'text' => 'Excluir', 'group_name' => 'budget', 'group_text' => 'Controle de Orçamento', 'auto_check' => '[17]', 'active' => '1'],
            ['id' => 19, 'name' => 'ResidueView', 'text' => 'Visualizar', 'group_name' => 'residue', 'group_text' => 'Controle de Resíduo', 'auto_check' => '[]', 'active' => '1'],
            ['id' => 20, 'name' => 'ResidueCreatePost', 'text' => 'Cadastrar', 'group_name' => 'residue', 'group_text' => 'Controle de Resíduo', 'auto_check' => '[19]', 'active' => '1'],
            ['id' => 22, 'name' => 'ResidueUpdatePost', 'text' => 'Atualizar', 'group_name' => 'residue', 'group_text' => 'Controle de Resíduo', 'auto_check' => '[19]', 'active' => '1'],
            ['id' => 23, 'name' => 'ResidueDeletePost', 'text' => 'Excluir', 'group_name' => 'residue', 'group_text' => 'Controle de Resíduo', 'auto_check' => '[19]', 'active' => '1'],
            ['id' => 24, 'name' => 'RentalView', 'text' => 'Visualizar', 'group_name' => 'rental', 'group_text' => 'Controle de Locação', 'auto_check' => '[]', 'active' => '1'],
            ['id' => 25, 'name' => 'RentalCreatePost', 'text' => 'Cadastrar', 'group_name' => 'rental', 'group_text' => 'Controle de Locação', 'auto_check' => '[24]', 'active' => '1'],
            ['id' => 26, 'name' => 'RentalUpdatePost', 'text' => 'Atualizar', 'group_name' => 'rental', 'group_text' => 'Controle de Locação', 'auto_check' => '[24]', 'active' => '1'],
            ['id' => 27, 'name' => 'RentalDeletePost', 'text' => 'Excluir', 'group_name' => 'rental', 'group_text' => 'Controle de Locação', 'auto_check' => '[24]', 'active' => '1'],
            ['id' => 28, 'name' => 'ProviderView', 'text' => 'Visualizar', 'group_name' => 'provider', 'group_text' => 'Controle de Fornecedor', 'auto_check' => '[]', 'active' => '1'],
            ['id' => 29, 'name' => 'ProviderCreatePost', 'text' => 'Cadastrar', 'group_name' => 'provider', 'group_text' => 'Controle de Fornecedor', 'auto_check' => '[28]', 'active' => '1'],
            ['id' => 30, 'name' => 'ProviderUpdatePost', 'text' => 'Atualizar', 'group_name' => 'provider', 'group_text' => 'Controle de Fornecedor', 'auto_check' => '[28]', 'active' => '1'],
            ['id' => 31, 'name' => 'ProviderDeletePost', 'text' => 'Excluir', 'group_name' => 'provider', 'group_text' => 'Cadastro de Fornecedor', 'auto_check' => '[28]', 'active' => '1'],
            ['id' => 32, 'name' => 'BillsToReceiveView', 'text' => 'Visualizar', 'group_name' => 'bills_to_receive', 'group_text' => 'Contas a Receber', 'auto_check' => '[]', 'active' => '1'],
            ['id' => 33, 'name' => 'BillsToReceiveCreatePost', 'text' => 'Cadastrar', 'group_name' => 'bills_to_receive', 'group_text' => 'Contas a Receber', 'auto_check' => '[32]', 'active' => '1'],
            ['id' => 34, 'name' => 'BillsToReceiveUpdatePost', 'text' => 'Atualizar', 'group_name' => 'bills_to_receive', 'group_text' => 'Contas a Receber', 'auto_check' => '[32]', 'active' => '1'],
            ['id' => 35, 'name' => 'BillsToReceiveDeletePost', 'text' => 'Excluir', 'group_name' => 'bills_to_receive', 'group_text' => 'Contas a Receber', 'auto_check' => '[32]', 'active' => '1'],
            ['id' => 36, 'name' => 'BillsToPayView', 'text' => 'Visualizar', 'group_name' => 'bills_to_pay', 'group_text' => 'Contas a Pagar', 'auto_check' => '[]', 'active' => '1'],
            ['id' => 37, 'name' => 'BillsToPayCreatePost', 'text' => 'Cadastrar', 'group_name' => 'bills_to_pay', 'group_text' => 'Contas a Pagar', 'auto_check' => '[36]', 'active' => '1'],
            ['id' => 38, 'name' => 'BillsToPayUpdatePost', 'text' => 'Atualizar', 'group_name' => 'bills_to_pay', 'group_text' => 'Contas a Pagar', 'auto_check' => '[36]', 'active' => '1'],
            ['id' => 39, 'name' => 'BillsToPayDeletePost', 'text' => 'Excluir', 'group_name' => 'bills_to_pay', 'group_text' => 'Contas a Pagar', 'auto_check' => '[36]', 'active' => '1']
        ];

        foreach ($permissions as $permission) {
            if (Permission::where('name', $permission['name'])->count() === 0) {
                Permission::create($permission);
            }
        }
    }
}
