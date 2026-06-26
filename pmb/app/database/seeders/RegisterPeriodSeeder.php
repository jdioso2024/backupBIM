<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RegisterPeriodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $datas = [
            [
                'name' => 'TA 2024/2025',
                'start_date' => '2024-01-01',
                'end_date' => '2024-11-30',
                'is_active' => true,
            ],
            [
                'name' => 'TA 2025/2026',
                'start_date' => '2024-07-01',
                'end_date' => '2024-12-31',
                'is_active' => false,
            ],
        ];

        foreach ($datas as $data) {
            \App\Models\RegisterPeriod::create($data);
        }

        $students = \App\Models\Student::all();
        $registerPeriods = \App\Models\RegisterPeriod::first();

        // Assign register period id 1 to students
        $students->each(function ($student) use ($registerPeriods) {
            $student->registerPeriod()->associate($registerPeriods);
            $student->save();
        });
    }
}
