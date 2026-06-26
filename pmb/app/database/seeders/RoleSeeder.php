<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $datas = [
            'admrektorat',
            'student',
            'superadmin',
            'pimpinan',
        ];

        $permissions = [
            'superadmin-access',
            'admin-access',
            'prestasi-access',
            'reguler-access',
            'beasiswa-access',
            'kip',
        ];

        foreach ($datas as $data) {
            \Spatie\Permission\Models\Role::firstOrCreate([
                'name' => $data
            ]);
        }

        foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::firstOrCreate([
                'name' => $permission
            ]);
        }

        $student = \App\Models\User::role('student')->get();
        foreach ($student as $user) {
            $user->givePermissionTo('prestasi-access');
        }
    }
}
