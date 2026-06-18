<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call(RoleSeeder::class);

        $adminRoleId = Role::query()->where('name', Role::ADMIN)->value('id');

        User::factory()->create([
            'username' => 'admin',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'status' => 'active',
            'role_id' => $adminRoleId,
        ]);
        }
}
