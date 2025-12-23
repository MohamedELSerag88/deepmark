<?php

namespace Database\Seeders;


use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
//
        DB::table('users')->truncate();
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
