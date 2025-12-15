<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
      if(DB::table("users")->where('email_adresse','superadmin@gmail.com')->doesntExist()){
        DB::table('users')->insert([
                'name' => 'Super',
                'last_name' => 'Admin',
                'email_adresse' => 'superadmin@gmail.com',
                'password' => Hash::make('12345678'),
                'role' => 'super_admin',
                'is_online' => false,
                'company_id' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                 ]);
      }
    }
}
