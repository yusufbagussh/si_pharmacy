<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        //Import Excel
        $filePath = storage_path('filedata/users.csv');
        $data = Excel::toArray([], $filePath);
        // Assuming the first sheet and the first row as header
        $users = $data[0];
        $headers = array_shift($users);

        $batchSize = 1000;
        $data = [];

        foreach ($users as $user) {

            $data[] = [
                'kode_bagian' => $user[0],
                'name' => $user[1],
                'username' => $user[2],
                'golongan' => $user[3],
                'is_active' => $user[4],
                'created_at' => now(),
            ];

            if (count($data) >= $batchSize) {
                // Insert batch ke database
                DB::table('users')->insert($data);
                // Reset data array untuk batch berikutnya
                $data = [];
            }
        }

        if (!empty($data)) {
            DB::table('users')->insert($data);
        }
    }
}
