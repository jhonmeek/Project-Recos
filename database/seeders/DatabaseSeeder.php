<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::query()->create([
            'name' => 'Admin Controle Interne',
            'email' => 'controle@anbg.local',
            'role' => User::ROLE_CONTROLE_INTERNE,
            'direction' => null,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        User::query()->create([
            'name' => 'Directeur Demo',
            'email' => 'directeur@anbg.local',
            'role' => User::ROLE_DIRECTEUR,
            'direction' => 'DSIC',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        User::query()->create([
            'name' => 'DG Demo',
            'email' => 'dg@anbg.local',
            'role' => User::ROLE_DG,
            'direction' => null,
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        $this->call([
            RecommendationSeeder::class,
        ]);
    }
}
