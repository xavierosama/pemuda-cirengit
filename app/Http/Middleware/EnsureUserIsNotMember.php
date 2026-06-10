<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsNotMember
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_if($request->user()?->role === 'member', 403, 'Akun anggota hanya dapat mengakses halaman presensi.');

        return $next($request);
    }
}
