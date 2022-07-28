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
        $this->createSmiceClient();
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

    /**
     * Create the Smice client, which will be used by the API
     *
     * @return void
     */
    private function createSmiceClient()
    {
        $clientRepository = app('Laravel\Passport\ClientRepository');
        $client = $clientRepository->create(
            1,
            'Smice',
            'https://0.0.0.0:8080/auth/callback',
            null,
            false,
            true
        );
        $client->secret = 'g1bnSaztvbs3RyIhNK6NAxUGT39TEldtF81xiWQb';
        $client->save();
    }
}
