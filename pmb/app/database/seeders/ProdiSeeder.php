<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProdiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'name' => 'Bisnis Digital',
                'code' => 'BD',
                'kuota' => 50,
            ],
            [
                'name' => 'Kewirausahaan',
                'code' => 'KW',
                'kuota' => 50,
            ],
            [
                'name' => 'Teknologi Pangan',
                'code' => 'TP',
                'kuota' => 50,
            ],
        ];

        foreach ($data as $prodi) {
            \App\Models\Prodi::create($prodi);
        }
    }
}
