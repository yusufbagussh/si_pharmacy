<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ReceiptStandardTimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('receipt_standard_times')->insert([
            'jenis_resep' => 'RACIKAN',
            'miliseconds' => 3600000,
            'created_at' => now(),
        ]);
        DB::table('receipt_standard_times')->insert([
            'jenis_resep' => 'NON RACIKAN',
            'miliseconds' => 1800000,
            'created_at' => now(),
        ]);
    }
}
