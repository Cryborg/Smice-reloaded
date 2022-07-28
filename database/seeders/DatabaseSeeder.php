<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        $this->createAdmins();
    }

    private function createAdmins()
    {
        $admins = [
            [
                'name' => 'Julien M',
                'email' => 'julien@smice.com',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Marc D',
                'email' => 'marc@smice.com',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'Franck L',
                'email' => 'franck.l@smice.com',
                'password' => Hash::make('password'),
            ],
        ];

        User::factory()->createMany($admins);
    }
}
