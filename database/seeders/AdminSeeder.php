<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        if (!Admin::where('email', 'admin@deepmark.local')->exists()) {
            Admin::create([
                'name' => 'Super Admin',
                'email' => 'admin@deepmark.com',
                'password' => Hash::make('password'),
            ]);
        }
    }
}

