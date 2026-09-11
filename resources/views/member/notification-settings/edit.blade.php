@php
    $systemSettings = app(\App\Support\SystemSettings::class);
    $appName = $systemSettings->get('app_name');
    $appLogoUrl = $systemSettings->assetUrl('app_logo');
    $faviconUrl = $systemSettings->assetUrl('favicon');
    $themeMode = $systemSettings->themeMode();
    $member = $user->member;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ $themeMode === 'dark' ? 'dark' : '' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Pengaturan Notifikasi - {{ $appName }}</title>

        @if ($faviconUrl)
            <link rel="icon" href="{{ $faviconUrl }}">
        @endif
        <x-pwa.meta :app-name="$appName" />

        <script>
            (() => {
                const themeMode = @json($themeMode);
                if (themeMode === 'dark' || (themeMode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                }
            })();
        </script>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-100 font-sans antialiased text-slate-900 dark:bg-slate-950 dark:text-slate-100">
        <div class="min-h-screen bg-gradient-to-br from-slate-50 via-emerald-50/35 to-slate-100 dark:from-slate-950 dark:via-slate-950 dark:to-slate-900">
            <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/90 shadow-sm shadow-slate-200/50 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/20">
                <div class="mx-auto flex max-w-5xl items-center justify-between gap-3 px-4 py-2 sm:px-6 sm:py-2.5 lg:px-8">
                    <a href="{{ route('member.home') }}" class="flex min-w-0 items-center gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-emerald-700 text-xs font-bold text-white">
                            @if ($appLogoUrl)
                                <img src="{{ $appLogoUrl }}" alt="{{ $appName }}" class="h-full w-full object-contain p-1.5">
                            @else
                                {{ str($appName)->substr(0, 2)->upper() }}
                            @endif
                        </span>
                        <span class="min-w-0">
                            <span class="block max-w-40 truncate text-sm font-bold text-slate-950 dark:text-white sm:max-w-none">{{ $appName }}</span>
                            <span class="hidden text-xs text-slate-500 dark:text-slate-400 sm:block">Pengaturan Notifikasi</span>
                        </span>
                    </a>

                    <div class="flex items-center gap-2">
                        <x-member.notifications-menu />
                        <x-member.account-menu :user="$user" :member="$member" />
                    </div>
                </div>
            </header>

            <main class="px-4 py-4 sm:px-6 sm:py-6 lg:px-8">
                <div class="mx-auto max-w-5xl space-y-4">
                    <x-ui.breadcrumb :items="[
                        ['label' => 'Dashboard Anggota', 'url' => route('member.home')],
                        ['label' => 'Notifikasi', 'url' => route('member.notifications.index')],
                        ['label' => 'Pengaturan'],
                    ]" />

                    @if (session('success'))
                        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800 shadow-sm">
                            {{ session('success') }}
                        </div>
                    @endif

                    <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-5">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">Preferensi Member</p>
                                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-950 dark:text-white">Pengaturan Notifikasi</h1>
                                <p class="mt-1 max-w-2xl text-sm leading-6 text-slate-500 dark:text-slate-400">
                                    Atur jenis notifikasi yang ingin Anda terima di aplikasi dan perangkat HP. Notifikasi di aplikasi tetap menjadi pusat informasi utama.
                                </p>
                            </div>
                            <a href="{{ route('member.notifications.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                                Kembali ke Notifikasi
                            </a>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-5">
                        <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <h2 class="text-base font-bold text-slate-950 dark:text-white">Status Perangkat</h2>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                    {{ $activePushSubscriptions > 0 ? $activePushSubscriptions.' perangkat aktif menerima push notification.' : 'Belum ada perangkat yang aktif menerima push notification.' }}
                                </p>
                            </div>
                            <span class="{{ $activePushSubscriptions > 0 ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-slate-100 text-slate-600 ring-slate-200' }} inline-flex w-fit rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset">
                                {{ $activePushSubscriptions > 0 ? 'Push aktif' : 'Push belum aktif' }}
                            </span>
                        </div>
                        <x-member.push-notification-toggle />
                    </section>

                    <form method="POST" action="{{ route('member.notification-settings.update') }}" class="space-y-4">
                        @csrf
                        @method('PUT')

                        <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                            <div class="border-b border-slate-100 px-4 py-4 dark:border-slate-800 sm:px-5">
                                <h2 class="text-base font-bold text-slate-950 dark:text-white">Kategori Notifikasi</h2>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Matikan push HP untuk kategori yang tidak ingin mengganggu. In-app tetap aktif agar informasi masih tersimpan di aplikasi.</p>
                            </div>

                            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach ($preferences as $category => $preference)
                                    <div class="px-4 py-4 sm:px-5" x-data="{ pushEnabled: {{ $preference['push_enabled'] ? 'true' : 'false' }} }">
                                        <input type="hidden" name="preferences[{{ $category }}][category]" value="{{ $category }}">
                                        <input type="hidden" name="preferences[{{ $category }}][in_app_enabled]" value="1">
                                        <input type="hidden" name="preferences[{{ $category }}][push_enabled]" :value="pushEnabled ? '1' : '0'">

                                        <div class="flex items-start justify-between gap-4">
                                            <div class="min-w-0">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <h3 class="text-sm font-bold text-slate-950 dark:text-white">{{ $preference['label'] }}</h3>
                                                    <span class="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-bold text-emerald-700 ring-1 ring-inset ring-emerald-200">In-app aktif</span>
                                                </div>
                                                <p class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400">{{ $preference['description'] }}</p>
                                            </div>

                                            <button
                                                type="button"
                                                class="relative inline-flex h-7 w-12 shrink-0 items-center rounded-full transition focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2 dark:focus:ring-offset-slate-900"
                                                :class="pushEnabled ? 'bg-emerald-600' : 'bg-slate-300 dark:bg-slate-700'"
                                                @click="pushEnabled = ! pushEnabled"
                                                :aria-pressed="pushEnabled.toString()"
                                                aria-label="Toggle push notification {{ $preference['label'] }}"
                                            >
                                                <span class="inline-block h-5 w-5 rounded-full bg-white shadow transition" :class="pushEnabled ? 'translate-x-6' : 'translate-x-1'"></span>
                                            </button>
                                        </div>

                                        <p class="mt-2 text-xs font-medium" :class="pushEnabled ? 'text-emerald-700' : 'text-slate-500'" x-text="pushEnabled ? 'Push HP aktif untuk kategori ini.' : 'Push HP dimatikan, tetapi notifikasi tetap muncul di aplikasi.'"></p>
                                    </div>
                                @endforeach
                            </div>
                        </section>

                        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:items-center sm:justify-end">
                            <a href="{{ route('member.notifications.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                                Batal
                            </a>
                            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
                                Simpan Pengaturan
                            </button>
                        </div>
                    </form>
                </div>
            </main>
        </div>
        <x-pwa.register />
        <x-ui.toast />
    </body>
</html>
