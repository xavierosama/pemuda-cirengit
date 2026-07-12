@php
    $systemSettings = app(\App\Support\SystemSettings::class);
    $appName = $systemSettings->get('app_name') ?: 'Pemuda Cirengit';
    $organizationName = $systemSettings->get('organization_name') ?: 'Pemuda Persis Cirengit';
    $logoUrl = $systemSettings->assetUrl('login_logo') ?: $systemSettings->assetUrl('app_logo');
    $initials = collect(explode(' ', $appName))
        ->filter()
        ->take(2)
        ->map(fn ($word) => substr($word, 0, 1))
        ->implode('');
@endphp

<x-guest-layout variant="login">
    <main class="min-h-screen overflow-hidden bg-slate-50 text-slate-900">
        <div class="grid min-h-screen lg:grid-cols-[minmax(0,1.08fr)_minmax(420px,0.92fr)]">
            <section class="relative isolate hidden overflow-hidden bg-gradient-to-br from-emerald-950 via-emerald-900 to-teal-950 px-6 py-8 text-white sm:px-10 lg:flex lg:min-h-screen lg:items-stretch lg:px-14 lg:py-12">
                <div class="absolute inset-0 -z-10 opacity-80">
                    <div class="absolute left-[-12rem] top-[-12rem] h-96 w-96 rounded-full bg-emerald-500/20 blur-3xl"></div>
                    <div class="absolute bottom-[-10rem] right-[-8rem] h-80 w-80 rounded-full bg-teal-300/15 blur-3xl"></div>
                    <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-white/30 to-transparent"></div>
                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_20%_20%,rgba(255,255,255,0.12),transparent_28%),radial-gradient(circle_at_78%_68%,rgba(16,185,129,0.16),transparent_32%)]"></div>
                </div>

                <div class="mx-auto flex w-full max-w-2xl flex-col justify-between gap-10 lg:mx-0">
                    <div>
                        <div class="flex items-center gap-4">
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-white/15 bg-white/10 text-lg font-bold text-white shadow-sm ring-1 ring-inset ring-white/10 sm:h-16 sm:w-16">
                                @if ($logoUrl)
                                    <img src="{{ $logoUrl }}" alt="{{ $appName }}" class="h-full w-full object-contain p-2">
                                @else
                                    {{ $initials ?: 'PC' }}
                                @endif
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-100">Pemuda Persis Cirengit</p>
                                <h1 class="mt-1 truncate text-2xl font-bold tracking-tight sm:text-3xl">{{ $appName }}</h1>
                                <p class="mt-1 truncate text-sm font-medium text-emerald-100/80">{{ $organizationName }}</p>
                            </div>
                        </div>

                        <div class="mt-10 max-w-2xl sm:mt-14 lg:mt-24">
                            <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-bold uppercase tracking-[0.18em] text-emerald-100 shadow-sm">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-300"></span>
                                Portal Internal
                            </div>
                            <h2 class="mt-5 text-3xl font-bold leading-tight tracking-tight sm:text-4xl lg:text-5xl">
                                Satu tempat untuk administrasi organisasi yang lebih tertata.
                            </h2>
                            <p class="mt-5 max-w-xl text-base leading-7 text-emerald-50/85 sm:text-lg">
                                Sistem administrasi, agenda, dan presensi anggota Pemuda Persis Cirengit.
                            </p>
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-3 lg:max-w-3xl">
                        <div class="rounded-2xl border border-white/10 bg-white/[0.08] p-4 shadow-sm backdrop-blur">
                            <p class="text-xs font-bold uppercase tracking-wide text-emerald-100">Administrasi</p>
                            <p class="mt-2 text-sm leading-5 text-emerald-50/85">Data anggota dan akun tertata.</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/[0.08] p-4 shadow-sm backdrop-blur">
                            <p class="text-xs font-bold uppercase tracking-wide text-emerald-100">Agenda</p>
                            <p class="mt-2 text-sm leading-5 text-emerald-50/85">Jadwal dan kegiatan terpantau.</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/[0.08] p-4 shadow-sm backdrop-blur">
                            <p class="text-xs font-bold uppercase tracking-wide text-emerald-100">Presensi</p>
                            <p class="mt-2 text-sm leading-5 text-emerald-50/85">Kehadiran anggota lebih praktis.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="relative flex min-h-screen items-start justify-center bg-gradient-to-br from-white via-emerald-50/35 to-slate-100 px-5 py-8 sm:px-10 sm:py-10 lg:min-h-screen lg:items-center lg:px-14">
                <div class="pointer-events-none absolute inset-0 overflow-hidden">
                    <div class="absolute right-[-8rem] top-[-8rem] h-72 w-72 rounded-full bg-emerald-100/70 blur-3xl"></div>
                    <div class="absolute bottom-[-12rem] left-[-10rem] h-80 w-80 rounded-full bg-sky-100/60 blur-3xl"></div>
                </div>

                <div class="relative w-full max-w-md">
                    <div class="mb-7 lg:mb-8">
                        <div class="mb-6 flex items-center gap-3 lg:block">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-emerald-100 bg-white text-base font-bold text-emerald-800 shadow-sm ring-1 ring-inset ring-white lg:mb-6 lg:h-14 lg:w-14">
                                @if ($logoUrl)
                                    <img src="{{ $logoUrl }}" alt="{{ $appName }}" class="h-full w-full object-contain p-2">
                                @else
                                    {{ $initials ?: 'PC' }}
                                @endif
                            </div>
                            <div class="min-w-0 lg:hidden">
                                <h1 class="truncate text-lg font-bold tracking-tight text-slate-950">{{ $appName }}</h1>
                                <p class="mt-0.5 truncate text-xs font-semibold uppercase tracking-wide text-emerald-700">{{ $organizationName }}</p>
                            </div>
                        </div>
                        <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-700">Login Pengguna</p>
                        <h2 class="mt-3 text-3xl font-bold tracking-tight text-slate-950">Masuk ke Akun</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Gunakan akun yang telah dibuat oleh pengurus.</p>
                    </div>

                    <x-auth-session-status class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800" :status="session('status')" />

                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf

                        <div>
                            <label for="email" class="block text-sm font-semibold text-slate-700">Username, Email, atau NPA</label>
                            <input
                                id="email"
                                class="mt-2 block h-12 w-full rounded-xl border-slate-200 bg-white/90 px-4 text-sm font-medium text-slate-900 shadow-sm shadow-slate-200/60 transition placeholder:text-slate-400 focus:border-emerald-600 focus:bg-white focus:ring-emerald-600"
                                type="text"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="Masukkan username, email, atau NPA"
                                required
                                autofocus
                                autocomplete="username"
                            >
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-semibold text-slate-700">Password</label>
                            <input
                                id="password"
                                class="mt-2 block h-12 w-full rounded-xl border-slate-200 bg-white/90 px-4 text-sm font-medium text-slate-900 shadow-sm shadow-slate-200/60 transition placeholder:text-slate-400 focus:border-emerald-600 focus:bg-white focus:ring-emerald-600"
                                type="password"
                                name="password"
                                placeholder="Masukkan password"
                                required
                                autocomplete="current-password"
                            >
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <label for="remember_me" class="inline-flex items-center">
                                <input id="remember_me" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-emerald-700 shadow-sm focus:ring-emerald-600" name="remember">
                                <span class="ms-2 text-sm font-medium text-slate-600">Remember me</span>
                            </label>

                            @if (Route::has('password.request'))
                                <a class="text-sm font-semibold text-emerald-700 transition hover:text-emerald-900 hover:underline focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2" href="{{ route('password.request') }}">
                                    Forgot password?
                                </a>
                            @endif
                        </div>

                        <button type="submit" class="inline-flex h-12 w-full items-center justify-center rounded-xl bg-emerald-700 px-5 text-sm font-bold text-white shadow-sm shadow-emerald-700/20 transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
                            Login
                        </button>
                    </form>
                </div>
            </section>
        </div>
    </main>
</x-guest-layout>
