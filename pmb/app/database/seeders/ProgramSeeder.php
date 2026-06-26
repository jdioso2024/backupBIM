<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'Regular Class',
            'International Class',
            'Executive Class'
        ];

        foreach ($data as $name) {
            \App\Models\Program::firstOrCreate([
                'name' => $name,
            ]);
        }
    }
}
