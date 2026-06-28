<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureLeaveApprovalManager
{
    /**
     * Chỉ cho phép tài khoản role Manager truy cập route duyệt nghỉ phép.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isManager()) {
            abort(403, 'Chỉ quản lý mới được truy cập chức năng duyệt nghỉ phép.');
        }

        return $next($request);
    }
}
