<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        $this->call(UsersSeeder::class);
        $this->call(QuestionSeeder::class);
        $this->call(AiPromptSeeder::class);
        $this->call(PlanSeeder::class);
        $this->call(AdminSeeder::class);
        $this->call(MarketingSeeder::class);
        Schema::enableForeignKeyConstraints();
    }
}
