<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        User::firstOrCreate(
            ['email' => 'chkl1411@gmail.com'],
            [
                'name' => '管理者',
                'password' => Hash::make('123456789'),
                'role' => 'admin',
            ]
        );
    }
}
