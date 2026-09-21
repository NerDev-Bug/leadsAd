<?php

namespace Database\Seeders;

use App\Models\AccessRegister;
use App\Support\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RoleAccountsSeeder extends Seeder
{
    /**
     * Seed role-based access accounts.
     *
     * Default password for all seeded accounts: Password123!
     */
    public function run(): void
    {
        $accounts = [
            [
                'username' => 'Administrator',
                'email' => 'admin@lapc.local',
                'role' => Role::ADMINISTRATOR,
            ],
            [
                'username' => 'Marketing',
                'email' => 'marketing@lapc.local',
                'role' => Role::MARKETING,
            ],
            [
                'username' => 'Careers',
                'email' => 'careers@lapc.local',
                'role' => Role::CAREERS,
            ],
            [
                'username' => 'Directory',
                'email' => 'directory@lapc.local',
                'role' => Role::DIRECTORY,
            ],
        ];

        foreach ($accounts as $account) {
            AccessRegister::updateOrCreate(
                ['email' => $account['email']],
                [
                    'username' => $account['username'],
                    'password' => Hash::make('Password123!'),
                    'role' => $account['role'],
                ]
            );
        }
    }
}
