<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFaceKioskToken
{
    /**
     * Xác thực yêu cầu từ kiosk chấm công bằng token bí mật dùng chung.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('services.face.kiosk_token');
        $provided = (string) $request->header('X-Face-Token', '');

        if ($expected === '' || ! hash_equals($expected, $provided)) {
            return response()->json([
                'success' => false,
                'message' => 'Token kiosk không hợp lệ.',
            ], 401);
        }

        return $next($request);
    }
}
