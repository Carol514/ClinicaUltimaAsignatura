<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['code' => 'administrador', 'name' => 'Administrador'],
            ['code' => 'medico', 'name' => 'Médico'],
            ['code' => 'enfermera', 'name' => 'Enfermera'],
            ['code' => 'recepcionista', 'name' => 'Recepcionista'],
            ['code' => 'paciente', 'name' => 'Paciente'],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['code' => $roleData['code']],
                ['name' => $roleData['name']]
            );
        }

        $this->command->info('✅ Roles created successfully!');
    }
}
