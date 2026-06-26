<?php

namespace Database\Seeders;

use App\Models\JalurPendaftaran;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class JalurPendaftaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['id' => 1, 'name' => 'Beasiswa Parsial'],
            ['id' => 2, 'name' => 'Raport'],
        ];

        foreach ($data as $jalur) {
            $existing = JalurPendaftaran::find($jalur['id']);

            if ($existing) {
                // Update data yang sudah ada
                $existing->update($jalur);
            } else {
                // Buat data baru
                JalurPendaftaran::create($jalur);
            }
        }
    }
}
