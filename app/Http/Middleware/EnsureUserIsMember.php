<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsMember
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->role === 'member', 403, 'Halaman ini hanya untuk akun anggota.');

        return $next($request);
    }
}
