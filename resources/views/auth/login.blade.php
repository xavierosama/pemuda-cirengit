@php
    $systemSettings = app(\App\Support\SystemSettings::class);
    $appName = $systemSettings->get('app_name') ?: 'Pemuda Cirengit';
    $organizationName = \Illuminate\Support\Facades\Schema::hasTable('settings')
        ? (\App\Models\Setting::query()->where('key', 'organization_name')->value('value') ?: 'PJ Pemuda Persis Cirengit')
        : 'PJ Pemuda Persis Cirengit';
    $logoUrl = $systemSettings->assetUrl('login_logo') ?: $systemSettings->assetUrl('app_logo');
    $initials = collect(explode(' ', $appName))
        ->filter()
        ->take(2)
        ->map(fn ($word) => substr($word, 0, 1))
        ->implode('');
@endphp

<x-guest-layout variant="login">
    <main class="min-h-screen bg-gradient-to-br from-emerald-50 via-white to-slate-100 px-4 py-6 sm:px-6 lg:px-8">
        <div class="mx-auto grid min-h-[calc(100vh-3rem)] max-w-6xl items-center gap-8 lg:grid-cols-[1.05fr_0.95fr]">
            <section class="hidden overflow-hidden rounded-2xl border border-emerald-100 bg-emerald-900 p-10 text-white shadow-xl lg:block">
                <div class="flex min-h-[560px] flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-4">
                            <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-white/20 bg-white/10 text-xl font-bold shadow-sm">
                                @if ($logoUrl)
                                    <img src="{{ $logoUrl }}" alt="{{ $appName }}" class="h-full w-full object-contain p-2">
                                @else
                                    {{ $initials ?: 'PC' }}
                                @endif
                            </div>
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-wide text-emerald-100">{{ $organizationName }}</p>
                                <h1 class="mt-1 text-3xl font-bold tracking-tight">{{ $appName }}</h1>
                            </div>
                        </div>

                        <div class="mt-16 max-w-xl">
                            <p class="text-sm font-semibold uppercase tracking-wide text-emerald-200">Portal Internal</p>
                            <h2 class="mt-4 text-4xl font-bold leading-tight">Satu tempat untuk administrasi organisasi yang lebih tertata.</h2>
                            <p class="mt-5 text-base leading-7 text-emerald-50">Sistem administrasi, agenda, dan presensi anggota Pemuda Persis Cirengit.</p>
                        </div>
                    </div>

                    <div class="grid gap-3 sm:grid-cols-3">
                        <div class="rounded-2xl border border-white/10 bg-white/10 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-100">Administrasi</p>
                            <p class="mt-2 text-sm text-emerald-50">Data anggota dan akun tertata.</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/10 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-100">Agenda</p>
                            <p class="mt-2 text-sm text-emerald-50">Jadwal dan kegiatan terpantau.</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/10 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-100">Presensi</p>
                            <p class="mt-2 text-sm text-emerald-50">Kehadiran anggota lebih praktis.</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="mx-auto w-full max-w-md">
                <div class="mb-6 text-center lg:hidden">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center overflow-hidden rounded-2xl border border-emerald-100 bg-white text-lg font-bold text-emerald-800 shadow-sm">
                        @if ($logoUrl)
                            <img src="{{ $logoUrl }}" alt="{{ $appName }}" class="h-full w-full object-contain p-2">
                        @else
                            {{ $initials ?: 'PC' }}
                        @endif
                    </div>
                    <h1 class="mt-4 text-2xl font-bold text-slate-950">{{ $appName }}</h1>
                    <p class="mt-1 text-sm font-medium text-slate-500">{{ $organizationName }}</p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-xl shadow-slate-200/70 sm:p-8">
                    <div class="mb-7">
                        <div class="mb-5 hidden h-12 w-12 items-center justify-center overflow-hidden rounded-2xl border border-emerald-100 bg-emerald-50 text-sm font-bold text-emerald-800 lg:flex">
                            @if ($logoUrl)
                                <img src="{{ $logoUrl }}" alt="{{ $appName }}" class="h-full w-full object-contain p-2">
                            @else
                                {{ $initials ?: 'PC' }}
                            @endif
                        </div>
                        <h2 class="text-2xl font-bold text-slate-950">Masuk ke Akun</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-500">Gunakan akun yang telah dibuat oleh pengurus.</p>
                    </div>

                    <x-auth-session-status class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800" :status="session('status')" />

                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf

                        <div>
                            <label for="email" class="block text-sm font-semibold text-slate-700">Email atau NPA</label>
                            <input id="email" class="mt-2 block w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-emerald-600 focus:ring-emerald-600" type="text" name="email" value="{{ old('email') }}" placeholder="Masukkan email atau NPA" required autofocus autocomplete="username">
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-semibold text-slate-700">Password</label>
                            <input id="password" class="mt-2 block w-full rounded-xl border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 shadow-sm transition placeholder:text-slate-400 focus:border-emerald-600 focus:ring-emerald-600" type="password" name="password" placeholder="Masukkan password" required autocomplete="current-password">
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-between gap-4">
                            <label for="remember_me" class="inline-flex items-center">
                                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-emerald-700 shadow-sm focus:ring-emerald-600" name="remember">
                                <span class="ms-2 text-sm font-medium text-slate-600">Remember me</span>
                            </label>

                            @if (Route::has('password.request'))
                                <a class="text-sm font-semibold text-emerald-700 transition hover:text-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2" href="{{ route('password.request') }}">
                                    Forgot password?
                                </a>
                            @endif
                        </div>

                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-700 px-5 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
                            Login
                        </button>
                    </form>
                </div>
            </section>
        </div>
    </main>
</x-guest-layout>
