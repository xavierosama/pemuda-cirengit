@php
    $systemSettings = app(\App\Support\SystemSettings::class);
    $appName = $systemSettings->get('app_name');
    $appLogoUrl = $systemSettings->assetUrl('app_logo');
    $faviconUrl = $systemSettings->assetUrl('favicon');
    $themeMode = $systemSettings->themeMode();
    $member = $user->member;

    $typeLabels = [
        'activity_created' => 'Kegiatan Baru',
        'activity_updated' => 'Perubahan Kegiatan',
        'activity_reminder' => 'Reminder Kegiatan',
        'attendance_opened' => 'Presensi Dibuka',
        'attendance_open' => 'Presensi Dibuka',
        'attendance_not_submitted' => 'Belum Presensi',
        'attendance_pending' => 'Belum Presensi',
        'attendance_closing_soon' => 'Hampir Ditutup',
        'attendance_closing' => 'Hampir Ditutup',
        'attendance_pending_verification' => 'Perlu Verifikasi',
        'attendance_verification_pending' => 'Perlu Verifikasi',
        'attendance_verified' => 'Terverifikasi',
        'attendance_rejected' => 'Ditolak',
        'activity_changed' => 'Perubahan Kegiatan',
        'attendance_instruction' => 'Instruksi Presensi',
        'attendance_warning' => 'Peringatan Presensi',
        'admin_announcement' => 'Pengumuman',
        'profile_update' => 'Update Profil',
        'general' => 'Umum',
        'profile_incomplete' => 'Profil',
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ $themeMode === 'dark' ? 'dark' : '' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Notifikasi - {{ $appName }}</title>

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
                            <span class="hidden text-xs text-slate-500 dark:text-slate-400 sm:block">Notifikasi Anggota</span>
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
                        ['label' => 'Notifikasi'],
                    ]" />

                    <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-5">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-700">Pusat Informasi</p>
                                <h1 class="mt-1 text-2xl font-bold tracking-tight text-slate-950 dark:text-white">Notifikasi</h1>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Informasi penting terkait kegiatan, presensi, dan profil anggota.</p>
                            </div>

                            @if ($unreadCount > 0)
                                <form method="POST" action="{{ route('member.notifications.read-all') }}">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-700 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-800">
                                        Tandai semua dibaca
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('member.notification-settings.edit') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200">
                                Pengaturan Notifikasi
                            </a>
                        </div>

                        <div class="mt-4 flex gap-2">
                            <a href="{{ route('member.notifications.index') }}" class="{{ $filter !== 'unread' ? 'bg-slate-900 text-white' : 'border border-slate-200 bg-white text-slate-700' }} inline-flex rounded-full px-3 py-1.5 text-sm font-bold transition">
                                Semua
                            </a>
                            <a href="{{ route('member.notifications.index', ['filter' => 'unread']) }}" class="{{ $filter === 'unread' ? 'bg-emerald-700 text-white' : 'border border-slate-200 bg-white text-slate-700' }} inline-flex rounded-full px-3 py-1.5 text-sm font-bold transition">
                                Belum dibaca
                                @if ($unreadCount > 0)
                                    <span class="ml-2 rounded-full bg-white/20 px-1.5">{{ $unreadCount }}</span>
                                @endif
                            </a>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-5">
                        <div class="mb-4">
                            <h2 class="text-base font-bold text-slate-950 dark:text-white">Pengaturan Push Notification</h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Aktifkan notifikasi HP/browser untuk menerima info penting tanpa membuka aplikasi.</p>
                        </div>
                        <x-member.push-notification-toggle />
                    </section>

                    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse ($notifications as $notification)
                                @php
                                    $redirectTo = $notification->url ?: route('member.notifications.index', absolute: false);
                                @endphp
                                <article class="{{ $notification->read_at ? 'bg-white dark:bg-slate-900' : 'bg-emerald-50/45 dark:bg-emerald-500/5' }} px-4 py-4 sm:px-5">
                                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700 ring-1 ring-inset ring-slate-200">
                                                    {{ $typeLabels[$notification->type] ?? str($notification->type)->headline() }}
                                                </span>
                                                @if (! $notification->read_at)
                                                    <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-bold text-emerald-700 ring-1 ring-inset ring-emerald-200">Belum dibaca</span>
                                                @endif
                                            </div>
                                            <h2 class="mt-2 text-base font-bold text-slate-950 dark:text-white">{{ $notification->title }}</h2>
                                            <p class="mt-1 text-sm leading-6 text-slate-600 dark:text-slate-400">{{ $notification->message }}</p>
                                            <p class="mt-2 text-xs font-medium text-slate-400">{{ $notification->created_at->diffForHumans() }}</p>
                                        </div>

                                        <div class="flex shrink-0 flex-wrap gap-2 sm:justify-end">
                                            <form method="POST" action="{{ route('member.notifications.read', $notification) }}">
                                                @csrf
                                                <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
                                                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-700 px-3 py-2 text-sm font-bold text-white transition hover:bg-emerald-800">
                                                    {{ $notification->url ? 'Buka' : 'Tandai dibaca' }}
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </article>
                            @empty
                                <x-ui.empty-state title="Belum ada notifikasi" description="Informasi penting terkait kegiatan dan presensi akan muncul di sini." />
                            @endforelse
                        </div>

                        @if ($notifications->hasPages())
                            <div class="border-t border-slate-100 px-4 py-3 dark:border-slate-800">
                                {{ $notifications->links() }}
                            </div>
                        @endif
                    </section>
                </div>
            </main>
        </div>
        <x-pwa.register />
        <x-ui.toast />
    </body>
</html>
