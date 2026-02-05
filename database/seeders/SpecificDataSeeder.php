<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SpecificDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Créer le Super Admin
        $superAdminId = DB::table('users')->insertGetId([
            'name' => 'super',
            'last_name' => 'admin',
            'email_adresse' => 'superadmin@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'super_admin',
            'is_online' => false,
            'company_id' => null,
            'is_active' => true,
            'is_blocked' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Créer l'Admin Orange (sans entreprise pour le moment)
        $adminOrangeId = DB::table('users')->insertGetId([
            'name' => 'admin',
            'last_name' => 'orrange',
            'email_adresse' => 'adminorange@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'admin',
            'is_online' => false,
            'company_id' => null,
            'is_active' => true,
            'is_blocked' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 3. Créer l'entreprise "comptabilité orange" liée à l'Admin Orange
        $companyId = DB::table('companies')->insertGetId([
            'company_name' => 'comptabilité orange',
            'activity' => 'Comptabilité',
            'juridique_form' => 'SARL',
            'social_capital' => 1000000,
            'adresse' => 'Abidjan',
            'code_postal' => '00225',
            'city' => 'Abidjan',
            'country' => 'Côte d\'Ivoire',
            'phone_number' => '0123456789',
            'email_adresse' => 'adminorange@gmail.com',
            'is_blocked' => false,
            'user_id' => $adminOrangeId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4. Mettre à jour l'Admin Orange avec l'ID de l'entreprise
        DB::table('users')->where('id', $adminOrangeId)->update(['company_id' => $companyId]);

        // 5. Créer l'utilisateur Orange lié à l'entreprise et créé par l'Admin Orange
        DB::table('users')->insert([
            'name' => 'user',
            'last_name' => 'orange',
            'email_adresse' => 'userorange@gmail.com',
            'password' => Hash::make('12345678'),
            'role' => 'comptable',
            'is_online' => false,
            'company_id' => $companyId,
            'created_by_id' => $adminOrangeId,
            'is_active' => true,
            'is_blocked' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
