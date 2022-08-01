<?php

namespace Database\Seeders;

use App\Http\User\Models\User;
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
                'first_name' => 'Julien',
                'last_name' => 'MONDHARD',
                'email' => 'julien@smice.com',
                'password' => Hash::make('password'),
            ],
            [
                'first_name' => 'Marc',
                'last_name' => 'DUREISSEIX',
                'email' => 'marc@smice.com',
                'password' => Hash::make('password'),
            ],
            [
                'first_name' => 'Franck',
                'last_name' => 'LÉCUVIER',
                'email' => 'franck.l@smice.com',
                'password' => Hash::make('password'),
            ],
        ];

        User::factory()->createMany($admins);
    }
}
