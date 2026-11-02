<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            [
                'email' => 'superadmin@gmail.com',
            ],
            [
                'name' => 'Super Admin',
                'company_id' => null,
                'role_id' => 1,
                'password' => Hash::make('12345678'),
            ]
        );
    }
}
