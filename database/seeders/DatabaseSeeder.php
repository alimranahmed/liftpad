<?php

namespace Database\Seeders;

use App\Models\Server;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

//        Server::query()->create([
//            'uuid' => Str::uuid7(),
//            'name' => 'Local',
//            'user' => 'al_imran',
//            'host' => 'localhost',
//            'port' => 22,
//        ]);

        Server::query()->create([
            'uuid' => Str::uuid7(),
            'name' => 'Home Server 1',
            'user' => 'al_imran',
            'host' => '192.168.178.2',
            'port' => 22,
            'password' => 'secret',
            'private_key_path' => '/Users/al_imran/.ssh/id_rsa',
        ]);
    }
}
