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
            ['name' => 'ClientView', 'text' => 'Visualizar', 'group_name' => 'client', 'group_text' => 'Cadastro de Cliente', 'auto_check' => '[]', 'active' => '1'],
            ['name' => 'ClientCreatePost', 'text' => 'Cadastrar', 'group_name' => 'client', 'group_text' => 'Cadastro de Cliente', 'auto_check' => '[1]', 'active' => '1'],
            ['name' => 'ClientUpdatePost', 'text' => 'Atualizar', 'group_name' => 'client', 'group_text' => 'Cadastro de Cliente', 'auto_check' => '[1]', 'active' => '1'],
            ['name' => 'ClientDeletePost', 'text' => 'Excluir', 'group_name' => 'client', 'group_text' => 'Cadastro de Cliente', 'auto_check' => '[1]', 'active' => '1'],
            ['name' => 'EquipmentView', 'text' => 'Visualizar', 'group_name' => 'equipament', 'group_text' => 'Cadastro de Equipamento', 'auto_check' => '[]', 'active' => '1'],
            ['name' => 'EquipmentCreatePost', 'text' => 'Cadastrar', 'group_name' => 'equipament', 'group_text' => 'Cadastro de Equipamento', 'auto_check' => '[5]', 'active' => '1'],
            ['name' => 'EquipmentUpdatePost', 'text' => 'Atualizar', 'group_name' => 'equipament', 'group_text' => 'Cadastro de Equipamento', 'auto_check' => '[5]', 'active' => '1'],
            ['name' => 'EquipmentDeletePost', 'text' => 'Excluir', 'group_name' => 'equipament', 'group_text' => 'Cadastro de Equipamento', 'auto_check' => '[5]', 'active' => '1'],
            ['name' => 'DriverView', 'text' => 'Visualizar', 'group_name' => 'driver', 'group_text' => 'Cadastro de Motorista', 'auto_check' => '[]', 'active' => '1'],
            ['name' => 'DriverCreatePost', 'text' => 'Cadastrar', 'group_name' => 'driver', 'group_text' => 'Cadastro de Motorista', 'auto_check' => '[9]', 'active' => '1'],
            ['name' => 'DriverUpdatePost', 'text' => 'Atualizar', 'group_name' => 'driver', 'group_text' => 'Cadastro de Motorista', 'auto_check' => '[9]', 'active' => '1'],
            ['name' => 'DriverDeletePost', 'text' => 'Excluir', 'group_name' => 'driver', 'group_text' => 'Cadastro de Motorista', 'auto_check' => '[9]', 'active' => '1'],
            ['name' => 'VehicleView', 'text' => 'Visualizar', 'group_name' => 'vehicle', 'group_text' => 'Cadastro de Motorista', 'auto_check' => '[]', 'active' => '1'],
            ['name' => 'VehicleCreatePost', 'text' => 'Cadastrar', 'group_name' => 'vehicle', 'group_text' => 'Cadastro de Veículo', 'auto_check' => '[13]', 'active' => '1'],
            ['name' => 'VehicleUpdatePost', 'text' => 'Atualizar', 'group_name' => 'vehicle', 'group_text' => 'Cadastro de Veículo', 'auto_check' => '[13]', 'active' => '1'],
            ['name' => 'VehicleDeletePost', 'text' => 'Excluir', 'group_name' => 'vehicle', 'group_text' => 'Cadastro de Veículo', 'auto_check' => '[13]', 'active' => '1'],
            ['name' => 'BudgetView', 'text' => 'Visualizar', 'group_name' => 'budget', 'group_text' => 'Controle de Orçamento', 'auto_check' => '[]', 'active' => '1'],
            ['name' => 'BudgetCreatePost', 'text' => 'Cadastrar', 'group_name' => 'budget', 'group_text' => 'Controle de Orçamento', 'auto_check' => '[24]', 'active' => '1'],
            ['name' => 'ResidueView', 'text' => 'Visualizar', 'group_name' => 'residue', 'group_text' => 'Controle de Resíduo', 'auto_check' => '[]', 'active' => '1'],
            ['name' => 'ResidueCreatePost', 'text' => 'Cadastrar', 'group_name' => 'residue', 'group_text' => 'Controle de Resíduo', 'auto_check' => '[19]', 'active' => '1'],
            ['name' => 'BudgetDeletePost', 'text' => 'Excluir', 'group_name' => 'budget', 'group_text' => 'Cadastro de Orçamento', 'auto_check' => '[17]', 'active' => '1'],
            ['name' => 'ResidueUpdatePost', 'text' => 'Atualizar', 'group_name' => 'residue', 'group_text' => 'Controle de Resíduo', 'auto_check' => '[19]', 'active' => '1'],
            ['name' => 'ResidueDeletePost', 'text' => 'Excluir', 'group_name' => 'residue', 'group_text' => 'Controle de Resíduo', 'auto_check' => '[19]', 'active' => '1'],
            ['name' => 'RentalView', 'text' => 'Visualizar', 'group_name' => 'rental', 'group_text' => 'Controle de Locação', 'auto_check' => '[]', 'active' => '1'],
            ['name' => 'RentalCreatePost', 'text' => 'Cadastrar', 'group_name' => 'rental', 'group_text' => 'Controle de Locação', 'auto_check' => '[24]', 'active' => '1'],
            ['name' => 'RentalUpdatePost', 'text' => 'Atualizar', 'group_name' => 'rental', 'group_text' => 'Controle de Locação', 'auto_check' => '[24]', 'active' => '1'],
            ['name' => 'RentalDeletePost', 'text' => 'Excluir', 'group_name' => 'rental', 'group_text' => 'Cadastro de Locação', 'auto_check' => '[24]', 'active' => '1']
        ];

        foreach ($permissions as $permission) {
            if (Permission::where('name', $permission['name'])->count() === 0) {
                Permission::create($permission);
            }
        }
    }
}
