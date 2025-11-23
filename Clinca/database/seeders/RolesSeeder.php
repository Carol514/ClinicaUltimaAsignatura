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
            ['code' => 'administrador', 'name' => 'Administrador', 'description' => 'Administrador del sistema'],
            ['code' => 'medico', 'name' => 'Médico', 'description' => 'Médico clínico'],
            ['code' => 'enfermera', 'name' => 'Enfermera', 'description' => 'Enfermera'],
            ['code' => 'recepcionista', 'name' => 'Recepcionista', 'description' => 'Recepcionista'],
            ['code' => 'paciente', 'name' => 'Paciente', 'description' => 'Paciente'],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['code' => $roleData['code']],
                [
                    'name' => $roleData['name'],
                    'description' => $roleData['description']
                ]
            );
        }

        $this->command->info('✅ Roles created successfully!');
    }
}
