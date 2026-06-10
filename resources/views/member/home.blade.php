<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Halaman Anggota - Pemuda Cirengit</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-100 font-sans antialiased text-slate-900">
        <main class="mx-auto flex min-h-screen max-w-2xl items-center px-4 py-10 sm:px-6">
            <section class="w-full rounded-xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">Pemuda Cirengit</p>
                        <h1 class="mt-2 text-2xl font-bold text-slate-950">Halaman Anggota</h1>
                    </div>
                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-200">Member</span>
                </div>

                @if (session('warning'))
                    <div class="mt-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800">
                        {{ session('warning') }}
                    </div>
                @endif

                <div class="mt-6 rounded-lg bg-slate-50 p-5">
                    <p class="text-sm font-medium text-slate-500">Masuk sebagai</p>
                    <p class="mt-2 text-lg font-bold text-slate-950">{{ $user->member?->full_name ?? $user->name }}</p>
                    <p class="mt-1 text-sm text-slate-600">{{ $user->email }}</p>
                </div>

                <p class="mt-6 text-sm leading-6 text-slate-600">
                    Silakan akses presensi melalui link atau QR kegiatan yang diberikan pengurus.
                </p>

                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('profile.edit') }}" class="inline-flex items-center justify-center rounded-lg border border-emerald-700 px-4 py-3 text-sm font-semibold text-emerald-700 hover:bg-emerald-50">
                        Profile
                    </a>

                    <form method="POST" action="{{ route('logout') }}" class="sm:ml-auto">
                        @csrf
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg bg-slate-900 px-4 py-3 text-sm font-semibold text-white hover:bg-slate-700 sm:w-auto">
                            Logout
                        </button>
                    </form>
                </div>
            </section>
        </main>
    </body>
</html>
