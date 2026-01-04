<?php

namespace Database\Seeders;


use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Use DELETE instead of TRUNCATE to respect FK constraints and CASCADE rules
        User::query()->delete();
        // Reset auto-increment for MySQL
        DB::statement('ALTER TABLE users AUTO_INCREMENT = 1');
        User::create([
            'fname'=>'Test',
            'lname'=>'User',
            'email'=>'test@test.com',
            'password'=>bcrypt('123456'),
            'phone' => '0123456789',
        ]);
        User::factory()->count(20)->create();
    }
}
