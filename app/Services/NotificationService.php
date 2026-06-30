<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class NotificationService
{
    public function sendToUser(int $userId, string $title, string $content, ?int $senderId = null): void
    {
        $notificationId = DB::table('notifications')->insertGetId([
            'title' => $title,
            'content' => $content,
            'sender_id' => $senderId,
            'type' => 'system',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('notification_users')->insert([
            'notification_id' => $notificationId,
            'user_id' => $userId,
            'is_read' => false,
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
