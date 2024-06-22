<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class BagianSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        //Import CSV
        // $csv = Reader::createFromPath(storage_path('users.csv'), 'r');
        // $csv->setHeaderOffset(0);
        // $records = $csv->getRecords();

        // foreach ($records as $record) {
        //     DB::table('bagians')->insert([
        //         'kode_bagian' => $record['kode_bagian'],
        //         'nama_bagian' => $record['nama_bagian'],
        //         'golongan' => $record['golongan'],
        //         'created_at' => now(),
        //     ]);
        // }

        //Import Excel
        $filePath = storage_path('filedata/bagians.csv');
        $data = Excel::toArray([], $filePath);
        // Assuming the first sheet and the first row as header
        $bagians = $data[0];
        $headers = array_shift($bagians);

        $batchSize = 1000;
        $data = [];

        foreach ($bagians as $bagian) {

            $data[] = [
                'kode_bagian' => $bagian[0],
                'nama_bagian' => $bagian[1],
                'golongan' => $bagian[2],
                'created_at' => now(),
            ];

            if (count($data) >= $batchSize) {
                // Insert batch ke database
                DB::table('bagians')->insert($data);
                // Reset data array untuk batch berikutnya
                $data = [];
            }
        }

        if (!empty($data)) {
            DB::table('bagians')->insert($data);
        }
    }
}
