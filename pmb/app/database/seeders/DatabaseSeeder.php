<?php

namespace Database\Seeders;

use App\Models\JalurPendaftaran;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $this->call([
            RoleSeeder::class,
            ProdiSeeder::class,
            ProgramSeeder::class,
            JalurPendaftaranSeeder::class,
            RegisterPeriodSeeder::class,
            UserSeeder::class, // Added UserSeeder here
            StudentSeeder::class,
            StudentDocumentSeeder::class,
        ]);

        $user = User::create([
            'name' => 'Admin BIM (Bali International Management) by ITBM Bali empowered by Hasnur Group',
            'email' => 'pmb@bim-university.ac.id',
            'password' => bcrypt('B1sm1llah'),
            'original_password' => 'apa hayo',
        ]);
        $user->assignRole('admrektorat');
    }
}
