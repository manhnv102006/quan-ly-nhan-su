<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationUserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('notification_users')->insert([
            [
                'notification_id' => 1,
                'user_id' => 1,
                'is_read' => false,
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'notification_id' => 1,
                'user_id' => 2,
                'is_read' => true,
                'read_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'notification_id' => 2,
                'user_id' => 1,
                'is_read' => true,
                'read_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}