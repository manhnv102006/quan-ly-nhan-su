<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->role) {
            abort(403, 'Tài khoản chưa được gán vai trò. Vui lòng liên hệ quản trị viên.');
        }

        if (in_array($user->role->name, $roles, true)) {
            return $next($request);
        }

        if ($request->is('admin', 'admin/*')) {
            return $this->redirectAwayFromAdmin($user);
        }

        if ($request->is('manager', 'manager/*')) {
            return $this->redirectAwayFromManager($user);
        }

        if ($request->is('accountant', 'accountant/*')) {
            return $this->redirectAwayFromAccountant($user);
        }

        abort(403, 'Bạn không có quyền truy cập trang này.');
    }

    private function redirectAwayFromAdmin($user): Response
    {
        if ($user->isManager()) {
            return redirect()
                ->route('manager.dashboard')
                ->with('error', 'Tài khoản Quản lý không có quyền truy cập khu vực Admin. Hãy dùng menu Manager hoặc đăng nhập bằng tài khoản Admin (ví dụ: admin / datlethanh).');
        }

        if ($user->isAccountant()) {
            return redirect()
                ->route('accountant.dashboard')
                ->with('error', 'Kế toán không có quyền truy cập khu vực Admin.');
        }

        if ($user->isEmployee()) {
            return redirect()
                ->route('employee.dashboard')
                ->with('error', 'Nhân viên không có quyền truy cập khu vực Admin.');
        }

        abort(403, 'Bạn không có quyền truy cập trang này.');
    }

    private function redirectAwayFromManager($user): Response
    {
        if ($user->isAdmin()) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'Admin không truy cập trực tiếp khu vực Manager. Dùng menu Admin để xem toàn hệ thống.');
        }

        if ($user->isEmployee()) {
            return redirect()
                ->route('employee.dashboard')
                ->with('error', 'Nhân viên không có quyền truy cập khu vực Manager.');
        }

        if ($user->isAccountant()) {
            return redirect()
                ->route('accountant.dashboard')
                ->with('error', 'Kế toán không có quyền truy cập khu vực Manager.');
        }

        abort(403, 'Bạn không có quyền truy cập trang này.');
    }

    private function redirectAwayFromAccountant($user): Response
    {
        if ($user->isAdmin()) {
            return redirect()
                ->route('admin.dashboard')
                ->with('error', 'Admin không truy cập trực tiếp khu vực Kế toán.');
        }

        if ($user->isManager()) {
            return redirect()
                ->route('manager.dashboard')
                ->with('error', 'Quản lý không có quyền truy cập khu vực Kế toán.');
        }

        if ($user->isEmployee()) {
            return redirect()
                ->route('employee.dashboard')
                ->with('error', 'Nhân viên không có quyền truy cập khu vực Kế toán.');
        }

        abort(403, 'Bạn không có quyền truy cập trang này.');
    }
}
