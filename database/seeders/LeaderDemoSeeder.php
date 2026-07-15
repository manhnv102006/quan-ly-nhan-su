<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class LeaderDemoSeeder extends Seeder
{
    /**
     * Tạo/cập nhật tài khoản leader demo trên DB đã có sẵn (chạy lại được).
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $leaderRoleId = Role::query()->where('name', Role::LEADER)->value('id');

        if (! $leaderRoleId) {
            $this->command?->error('Không tìm thấy role leader. Chạy RoleSeeder trước.');

            return;
        }

        $leaderUser = User::query()->updateOrCreate(
            ['username' => 'leader'],
            [
                'name' => 'Lê Văn Cường',
                'email' => 'leader@example.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'role_id' => $leaderRoleId,
                'status' => 'active',
            ]
        );

        $emp003Id = DB::table('employees')->where('employee_code', 'EMP003')->value('id');

        if (! $emp003Id) {
            $this->command?->warn('Chưa có nhân viên EMP003 — bỏ qua liên kết user/team.');

            return;
        }

        DB::table('employees')->where('employee_code', 'EMP003')->update([
            'user_id' => $leaderUser->id,
            'email' => $leaderUser->email,
        ]);

        DB::table('employees')->whereIn('employee_code', ['EMP004', 'EMP005'])->update([
            'manager_id' => $emp003Id,
        ]);

        $this->command?->info('Leader demo: username=leader / password=password → /leader/dashboard');
    }
}
