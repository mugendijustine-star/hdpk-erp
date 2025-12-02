<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ProductDemoSeeder::class,
        ]);
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //
    }
}
