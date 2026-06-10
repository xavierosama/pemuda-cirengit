<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasInternalRole
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->role === 'member') {
            return redirect()
                ->route('member.home')
                ->with('warning', 'Dashboard admin hanya dapat diakses oleh pengurus.');
        }

        abort_unless(
            in_array($request->user()?->role, ['admin', 'secretary'], true),
            403,
            'Akun Anda belum memiliki hak akses internal.'
        );

        return $next($request);
    }
}
