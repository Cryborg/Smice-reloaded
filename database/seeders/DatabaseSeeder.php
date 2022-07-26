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
    public function run()
    {
         User::factory()->create([
             'name' => 'Franck L',
             'email' => 'franck.l@smice.com',
             'password' => Hash::make('password'),
         ]);

        User::factory(10)->create();
    }
}
