<?php

namespace Database\Seeders;

use App\Models\Advisor;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::firstOrCreate(
            ['email' => 'martin@ofgrd.cz'],
            [
                'name'     => 'Martin Šetek',
                'password' => Hash::make('changeme'),
                'role'     => 'admin',
            ]
        );

        // 14 PFS brokers
        $advisors = [
            ['initials' => 'GA', 'name' => 'Gabriela Adamcová'],
            ['initials' => 'LD', 'name' => 'Lenka Dolejš Michálková'],
            ['initials' => 'MM', 'name' => 'Martin Micka'],
            ['initials' => 'KD', 'name' => 'Kristýna Dočkalová'],
            ['initials' => 'VJ', 'name' => 'Veronika Janikovičová'],
            ['initials' => 'KK', 'name' => 'Karel Kučera'],
            ['initials' => 'RD', 'name' => 'Romana Danilevičová'],
            ['initials' => 'LR', 'name' => 'Lenka Rebrošová'],
            ['initials' => 'RK', 'name' => 'Rostislav Kubíček'],
            ['initials' => 'AV', 'name' => 'Adam Vaškeба'],
            ['initials' => 'RF', 'name' => 'Radek Fiala'],
            ['initials' => 'AM', 'name' => 'Albert Matějka'],
            ['initials' => 'EM', 'name' => 'Eva Marková'],
            ['initials' => 'PČ', 'name' => 'Petr Čajan'],
        ];

        foreach ($advisors as $data) {
            Advisor::firstOrCreate(
                ['initials' => $data['initials']],
                array_merge($data, ['active' => true])
            );
        }
    }
}
