<?php

namespace Database\Seeders;

use App\Models\Bagian;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use League\Csv\Reader;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call(BagianSeeder::class);
        $this->call(UserSeeder::class);
    }
}
