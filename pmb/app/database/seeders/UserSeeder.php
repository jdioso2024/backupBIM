<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Student;
use App\Models\Prodi;
use App\Models\Program;
use App\Models\RegisterPeriod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get necessary data
        $prodis = Prodi::all();
        $program = Program::first();
        $registerPeriod = RegisterPeriod::first();

        // Check if required data exists
        if ($prodis->isEmpty()) {
            $this->command->error('Prodi data not found. Please run ProdiSeeder first.');
            return;
        }

        if (!$program) {
            $this->command->error('Program data not found. Please run ProgramSeeder first.');
            return;
        }

        // Create Superadmin User
        $superadmin = User::firstOrCreate(
            ['email' => 'superadmin@bim.ac.id'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'original_password' => 'password',
            ]
        );
        $superadmin->assignRole('superadmin');

        // Create Admrektorat User
        $admrektorat = User::firstOrCreate(
            ['email' => 'admin@bim.ac.id'],
            [
                'name' => 'Admin Rektorat',
                'password' => Hash::make('password'),
                'original_password' => 'password',
            ]
        );
        $admrektorat->assignRole('admrektorat');

        // Create Student Users with complete Student data
        $students = [
            [
                'name' => 'John Doe',
                'email' => 'john@student.bim.ac.id',
                'password' => Hash::make('password'),
                'original_password' => 'password',
                'phone_number' => '081234567890',
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@student.bim.ac.id',
                'password' => Hash::make('password'),
                'original_password' => 'password',
                'phone_number' => '081234567891',
            ],
            [
                'name' => 'Ahmad Rizki',
                'email' => 'ahmad@student.bim.ac.id',
                'password' => Hash::make('password'),
                'original_password' => 'password',
                'phone_number' => '081234567892',
            ],
        ];

        foreach ($students as $index => $studentData) {
            $user = User::firstOrCreate(
                ['email' => $studentData['email']],
                [
                    'name' => $studentData['name'],
                    'password' => $studentData['password'],
                    'original_password' => $studentData['original_password'],
                ]
            );
            $user->assignRole('student');
            $user->givePermissionTo('prestasi-access');

            // Create Student record for this user
            Student::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $studentData['name'],
                    'phone_number' => $studentData['phone_number'],
                    'referensi' => 'Website',
                    'prodi1_id' => $prodis[0]->id ?? 1,
                    'prodi2_id' => $prodis[1]->id ?? 2,
                    'program_id' => $program->id,
                    'jalur_pendaftaran_id' => 2, // Raport
                    'register_period_id' => $registerPeriod?->id,
                    'register_at' => now(),
                    'status' => 0, // Pending
                ]
            );
        }
    }
}
