<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class PimpinanSeeder extends Seeder
{
    /**
     * Buat role + user untuk Pimpinan (dashboard eksekutif).
     */
    public function run(): void
    {
        Role::firstOrCreate(['name' => 'pimpinan']);

        $pimpinan = User::firstOrCreate(
            ['email' => 'pimpinan@bim.ac.id'],
            [
                'name' => 'Pimpinan BIM University',
                'password' => Hash::make('password'),
                'original_password' => 'password',
            ]
        );
        $pimpinan->assignRole('pimpinan');
    }
}
