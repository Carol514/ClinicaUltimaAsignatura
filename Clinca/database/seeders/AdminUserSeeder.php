<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@clinica.com'],
            [
                'name' => 'Administrador Sistema',
                'password' => Hash::make('123456'),
            ]
        );

        // Get admin role
        $adminRole = Role::where('code', 'administrador')->first();
        
        if ($adminRole) {
            // Assign admin role to user if not already assigned
            $hasRole = DB::table('users_roles')
                ->where('user_id', $admin->id)
                ->where('role_id', $adminRole->id)
                ->exists();
            
            if (!$hasRole) {
                DB::table('users_roles')->insert([
                    'user_id' => $admin->id,
                    'role_id' => $adminRole->id,
                ]);
            }
            
            $this->command->info('✅ Admin user created successfully!');
            $this->command->info('   Email: admin@clinica.com');
            $this->command->info('   Password: 123456');
        } else {
            $this->command->error('❌ Admin role not found. Please run RolesSeeder first.');
        }
    }
}
