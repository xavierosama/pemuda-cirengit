@php
    $systemSettings = app(\App\Support\SystemSettings::class);
    $appName = $systemSettings->get('app_name');
    $organizationName = $systemSettings->get('organization_name');
    $appLogoUrl = $systemSettings->assetUrl('app_logo');
    $faviconUrl = $systemSettings->assetUrl('favicon');
    $themeMode = $systemSettings->themeMode();
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ $themeMode === 'dark' ? 'dark' : '' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Halaman Anggota - {{ $appName }}</title>

        @if ($faviconUrl)
            <link rel="icon" href="{{ $faviconUrl }}">
        @endif

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
        @php
            $member = $user->member;
            $displayName = $member?->full_name ?? $user->name;
            $initial = strtoupper(substr($displayName, 0, 1));
            $profilePhotoUrl = $member?->profile_photo ? asset('storage/'.$member->profile_photo) : null;
            $memberStatusLabels = ['active' => 'Aktif', 'inactive' => 'Tidak Aktif', 'alumni' => 'Alumni', 'moved' => 'Pindah'];
            $memberStatusClasses = [
                'active' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                'inactive' => 'bg-slate-100 text-slate-600 ring-slate-200',
                'alumni' => 'bg-sky-50 text-sky-700 ring-sky-200',
                'moved' => 'bg-amber-50 text-amber-700 ring-amber-200',
            ];
            $attendanceLabels = ['present' => 'Hadir', 'permission' => 'Izin', 'absent' => 'Tidak Hadir', 'need_verification' => 'Perlu Verifikasi'];
            $attendanceClasses = [
                'present' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                'permission' => 'bg-sky-50 text-sky-700 ring-sky-200',
                'absent' => 'bg-slate-100 text-slate-600 ring-slate-200',
                'need_verification' => 'bg-amber-50 text-amber-700 ring-amber-200',
            ];
            $verificationLabels = ['valid' => 'Valid', 'need_verification' => 'Perlu Verifikasi', 'rejected' => 'Ditolak'];
            $verificationClasses = [
                'valid' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                'need_verification' => 'bg-amber-50 text-amber-700 ring-amber-200',
                'rejected' => 'bg-red-50 text-red-700 ring-red-200',
            ];
            $activityStatusLabels = ['scheduled' => 'Terjadwal', 'completed' => 'Selesai', 'holiday' => 'Libur', 'postponed' => 'Ditunda', 'relocated' => 'Dipindah', 'cancelled' => 'Dibatalkan'];
            $activityStatusClasses = [
                'scheduled' => 'bg-slate-100 text-slate-700 ring-slate-200',
                'completed' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                'holiday' => 'bg-slate-100 text-slate-600 ring-slate-200',
                'postponed' => 'bg-amber-50 text-amber-700 ring-amber-200',
                'relocated' => 'bg-cyan-50 text-cyan-700 ring-cyan-200',
                'cancelled' => 'bg-red-50 text-red-700 ring-red-200',
            ];
            $scheduleTypeLabels = ['once' => 'Sekali', 'daily' => 'Harian', 'weekly' => 'Mingguan', 'monthly' => 'Bulanan'];
        @endphp

        <div class="min-h-screen">
            <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/90 shadow-sm shadow-slate-200/50 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/20">
                <div class="mx-auto flex max-w-5xl items-center justify-between gap-3 px-4 py-2.5 sm:px-6 lg:px-8">
                    <div>
                        <div class="flex items-center gap-3">
                            <div class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-emerald-700 text-xs font-bold text-white">
                                @if ($appLogoUrl)
                                    <img src="{{ $appLogoUrl }}" alt="{{ $appName }}" class="h-full w-full object-contain p-1.5">
                                @else
                                    {{ str($appName)->substr(0, 2)->upper() }}
                                @endif
                            </div>
                            <div>
                                <p class="max-w-40 truncate text-sm font-bold text-slate-950 dark:text-white sm:max-w-none">{{ $appName }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Dashboard Anggota</p>
                            </div>
                        </div>
                    </div>

                    <div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false" @click.outside="open = false">
                        <button type="button" class="flex h-10 w-10 items-center justify-center overflow-hidden rounded-full bg-emerald-700 text-sm font-bold text-white shadow-sm ring-1 ring-inset ring-emerald-800 transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2" @click="open = ! open" aria-label="Menu anggota" :aria-expanded="open.toString()">
                            @if ($profilePhotoUrl)
                                <img src="{{ $profilePhotoUrl }}" alt="Foto profil {{ $displayName }}" class="h-full w-full object-cover">
                            @else
                                {{ $initial }}
                            @endif
                        </button>

                        <div x-cloak x-show="open" x-transition.origin.top.right class="absolute right-0 mt-2 w-56 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 shadow-xl dark:border-slate-800 dark:bg-slate-900">
                            <div class="border-b border-slate-100 px-4 py-3 dark:border-slate-800">
                                <p class="truncate text-sm font-bold text-slate-950 dark:text-white">{{ $displayName }}</p>
                                <p class="truncate text-xs text-slate-500 dark:text-slate-400">{{ $user->email }}</p>
                            </div>
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800">Edit Profil</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full px-4 py-2 text-left text-sm font-medium text-red-600 transition hover:bg-red-50 dark:text-red-300 dark:hover:bg-red-500/10">Logout</button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <main class="px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
                <div class="mx-auto max-w-5xl space-y-4">
                    @foreach (['warning' => 'amber', 'success' => 'emerald', 'error' => 'red', 'info' => 'sky'] as $sessionKey => $color)
                        @if (session($sessionKey))
                            <div @class([
                                'rounded-xl border px-4 py-3 text-sm font-medium',
                                'border-amber-200 bg-amber-50 text-amber-800' => $color === 'amber',
                                'border-emerald-200 bg-emerald-50 text-emerald-800' => $color === 'emerald',
                                'border-red-200 bg-red-50 text-red-800' => $color === 'red',
                                'border-sky-200 bg-sky-50 text-sky-800' => $color === 'sky',
                            ])>{{ session($sessionKey) }}</div>
                        @endif
                    @endforeach

                    <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-5">
                        <h2 class="sr-only">Profil Anggota</h2>
                        <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                            <div class="flex min-w-0 gap-4">
                                <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-emerald-700 text-lg font-bold text-white ring-1 ring-inset ring-emerald-800">
                                    @if ($profilePhotoUrl)
                                        <img src="{{ $profilePhotoUrl }}" alt="Foto profil {{ $displayName }}" class="h-full w-full object-cover">
                                    @else
                                        {{ $initial }}
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">{{ $organizationName }}</p>
                                    <h1 class="mt-1 text-xl font-bold text-slate-950 sm:text-2xl">Assalamu'alaikum, {{ $displayName }}</h1>
                                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600">Silakan akses presensi melalui QR atau link kegiatan yang diberikan pengurus.</p>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-200">NPA: {{ $member?->npa ?: '-' }}</span>
                                <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700 ring-1 ring-inset ring-slate-200">{{ $member?->department?->name ?? 'Bidang belum diisi' }}</span>
                                <span class="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700 ring-1 ring-inset ring-slate-200">{{ $member?->position?->name ?? 'Jabatan belum diisi' }}</span>
                            </div>
                        </div>

                        <div class="mt-4 grid gap-3 border-t border-slate-100 pt-4 dark:border-slate-800 sm:grid-cols-2 lg:grid-cols-5">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Nama</p>
                                <p class="mt-1 truncate text-sm font-semibold text-slate-900">{{ $member?->full_name ?? $user->name }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Status</p>
                                @if ($member)
                                    <span class="{{ $memberStatusClasses[$member->member_status] ?? $memberStatusClasses['inactive'] }} mt-1 inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ $memberStatusLabels[$member->member_status] ?? $member->member_status }}</span>
                                @else
                                    <span class="mt-1 inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-600 ring-1 ring-inset ring-slate-200">Belum terhubung</span>
                                @endif
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Email</p>
                                <p class="mt-1 truncate text-sm text-slate-700">{{ $member?->email ?: $user->email }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">No HP</p>
                                <p class="mt-1 text-sm text-slate-700">{{ $member?->phone ?: '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Akun</p>
                                <p class="mt-1 text-sm text-slate-700">Aktif sebagai anggota</p>
                            </div>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-emerald-100 bg-white p-4 shadow-sm ring-1 ring-emerald-50 dark:border-emerald-900/60 dark:bg-slate-900 dark:ring-emerald-400/10 sm:p-5">
                        <div class="flex flex-col gap-1 border-b border-slate-100 pb-3 dark:border-slate-800 sm:flex-row sm:items-end sm:justify-between">
                            <div>
                                <h2 class="text-base font-bold text-slate-950">Kegiatan Sekarang</h2>
                                <p class="mt-1 text-sm text-slate-500">Prioritas presensi yang sedang dibuka saat ini.</p>
                            </div>
                        </div>

                        @if ($currentActivities->isNotEmpty())
                            <div class="mt-4 space-y-3">
                                @foreach ($currentActivities as $activity)
                                    @php
                                        $attendance = $activity->attendances->first();
                                        $time = trim(($activity->start_time ? substr($activity->start_time, 0, 5) : '').($activity->end_time ? ' - '.substr($activity->end_time, 0, 5) : ''));
                                        $canCheckIn = ! $attendance || $attendance->status === 'absent';
                                    @endphp
                                    <article class="rounded-xl border border-emerald-100 bg-emerald-50/40 p-3 dark:border-emerald-900/60 dark:bg-emerald-500/5 sm:p-4">
                                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                            <div class="min-w-0">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <h3 class="text-base font-bold text-slate-950">{{ $activity->title }}</h3>
                                                    <span class="{{ $activityStatusClasses[$activity->status] ?? $activityStatusClasses['scheduled'] }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ $activityStatusLabels[$activity->status] ?? $activity->status }}</span>
                                                </div>
                                                <div class="mt-3 grid gap-x-4 gap-y-1.5 text-sm text-slate-700 dark:text-slate-300 sm:grid-cols-2 lg:grid-cols-4">
                                                    <p><span class="font-semibold text-slate-900">Tanggal:</span> {{ $activity->activity_date->format('d/m/Y') }}</p>
                                                    <p><span class="font-semibold text-slate-900">Waktu:</span> {{ $time !== '' ? $time : '-' }}</p>
                                                    <p class="sm:col-span-2"><span class="font-semibold text-slate-900">Lokasi:</span> {{ $activity->location ?: '-' }}</p>
                                                    <p><span class="font-semibold text-slate-900">Bidang:</span> {{ $activity->department?->name ?? '-' }}</p>
                                                    <p><span class="font-semibold text-slate-900">PIC:</span> {{ $activity->pic?->full_name ?? '-' }}</p>
                                                    <p class="sm:col-span-2"><span class="font-semibold text-slate-900">Presensi:</span> {{ $activity->attendance_open_at?->format('d/m/Y H:i') }} - {{ $activity->attendance_close_at?->format('d/m/Y H:i') }}</p>
                                                </div>
                                            </div>

                                            <div class="w-full shrink-0 lg:w-52">
                                                @if ($canCheckIn)
                                                    <form method="POST" action="{{ route('member.activities.check-in', $activity) }}" class="member-check-in-form space-y-2">
                                                        @csrf
                                                        <input type="hidden" name="latitude">
                                                        <input type="hidden" name="longitude">
                                                        <input type="hidden" name="location_accuracy">
                                                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">Saya Hadir</button>
                                                        <p class="member-check-in-message text-xs text-slate-500">Akses lokasi akan diminta.</p>
                                                    </form>
                                                @else
                                                    <div class="rounded-xl border border-slate-200 bg-white p-3">
                                                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Presensi Anda</p>
                                                        <div class="mt-2 flex flex-wrap gap-2">
                                                            <span class="{{ $attendanceClasses[$attendance->status] ?? $attendanceClasses['absent'] }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ $attendanceLabels[$attendance->status] ?? $attendance->status }}</span>
                                                            <span class="{{ $verificationClasses[$attendance->verification_status] ?? $verificationClasses['need_verification'] }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ $verificationLabels[$attendance->verification_status] ?? $attendance->verification_status }}</span>
                                                        </div>
                                                        <p class="mt-2 text-xs text-slate-500">Waktu presensi</p>
                                                        <p class="text-sm font-semibold text-slate-800">{{ $attendance->checked_in_at?->format('d/m/Y H:i') ?? '-' }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
                        @else
                            <div class="mt-4 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center">
                                <p class="text-sm font-semibold text-slate-800">Belum ada kegiatan yang membuka presensi saat ini.</p>
                            </div>
                        @endif
                    </section>

                    <div class="grid gap-4 lg:grid-cols-[1.35fr_0.65fr]">
                        <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-5">
                            <div class="border-b border-slate-100 pb-3 dark:border-slate-800">
                                <h2 class="text-base font-bold text-slate-950">Kegiatan Mendatang</h2>
                                <p class="mt-1 text-sm text-slate-500">Maksimal 5 agenda terdekat untuk anggota.</p>
                            </div>

                            @if ($upcomingActivities->isNotEmpty())
                                <div class="mt-4 divide-y divide-slate-100">
                                    @foreach ($upcomingActivities as $activity)
                                        @php
                                            $dateLabel = null;
                                            if ($activity->activity_date->isToday()) {
                                                $dateLabel = 'Hari ini';
                                            } elseif ($activity->activity_date->isTomorrow()) {
                                                $dateLabel = 'Besok';
                                            } elseif ($activity->activity_date->betweenIncluded(now()->startOfWeek(), now()->endOfWeek())) {
                                                $dateLabel = 'Minggu ini';
                                            }
                                            $time = trim(($activity->start_time ? substr($activity->start_time, 0, 5) : '').($activity->end_time ? ' - '.substr($activity->end_time, 0, 5) : ''));
                                        @endphp
                                        <article class="flex gap-3 py-2.5 first:pt-0 last:pb-0">
                                            <div class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-emerald-500 ring-4 ring-emerald-50"></div>
                                            <div class="min-w-0 flex-1">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <h3 class="truncate text-sm font-bold text-slate-950">{{ $activity->title }}</h3>
                                                    <span class="{{ $activityStatusClasses[$activity->status] ?? $activityStatusClasses['scheduled'] }} inline-flex rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1 ring-inset">{{ $activityStatusLabels[$activity->status] ?? $activity->status }}</span>
                                                    @if ($dateLabel)
                                                        <span class="inline-flex rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-200">{{ $dateLabel }}</span>
                                                    @endif
                                                </div>
                                                <p class="mt-1 text-sm text-slate-600">{{ $activity->activity_date->format('d/m/Y') }} &middot; {{ $time !== '' ? $time : '-' }}</p>
                                                <p class="mt-1 truncate text-xs text-slate-500">{{ $activity->location ?: '-' }} &middot; {{ $activity->department?->name ?? '-' }}</p>
                                                @if ($activity->pic)
                                                    <p class="mt-1 text-xs text-slate-500">PIC: {{ $activity->pic->full_name }}</p>
                                                @endif
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            @elseif ($activeAgendaSchedules->isNotEmpty())
                                <div class="mt-4 divide-y divide-slate-100">
                                    @foreach ($activeAgendaSchedules as $agendaSchedule)
                                        @php
                                            $time = trim(($agendaSchedule->start_time ? substr($agendaSchedule->start_time, 0, 5) : '').($agendaSchedule->end_time ? ' - '.substr($agendaSchedule->end_time, 0, 5) : ''));
                                        @endphp
                                        <article class="flex gap-3 py-2.5 first:pt-0 last:pb-0">
                                            <div class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-sky-500 ring-4 ring-sky-50"></div>
                                            <div class="min-w-0 flex-1">
                                                <div class="flex flex-wrap items-center gap-2">
                                                    <h3 class="truncate text-sm font-bold text-slate-950">{{ $agendaSchedule->title }}</h3>
                                                    <span class="inline-flex rounded-full bg-sky-50 px-2 py-0.5 text-[11px] font-semibold text-sky-700 ring-1 ring-inset ring-sky-200">Jadwal Rutin</span>
                                                </div>
                                                <p class="mt-1 text-sm text-slate-600">{{ $scheduleTypeLabels[$agendaSchedule->schedule_type] ?? $agendaSchedule->schedule_type }} &middot; {{ $time !== '' ? $time : '-' }}</p>
                                                <p class="mt-1 truncate text-xs text-slate-500">{{ $agendaSchedule->default_location ?: '-' }} &middot; {{ $agendaSchedule->department?->name ?? '-' }}</p>
                                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            @else
                                <div class="mt-4 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-center">
                                    <p class="text-sm font-semibold text-slate-800">Belum ada kegiatan mendatang.</p>
                                </div>
                            @endif
                        </section>

                        <section class="rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900" x-data="{ open: false }">
                            <button type="button" class="flex w-full items-center justify-between gap-3 p-4 text-left" @click="open = ! open" :aria-expanded="open.toString()">
                                <span>
                                    <span class="block text-base font-bold text-slate-950">Panduan Presensi</span>
                                    <span class="mt-1 block text-sm text-slate-500">Buka saat perlu mengingat langkah presensi.</span>
                                </span>
                                <svg class="h-5 w-5 shrink-0 text-slate-500 transition" :class="{ 'rotate-180': open }" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                </svg>
                            </button>
                            <div x-cloak x-show="open" x-transition class="border-t border-slate-100 px-4 pb-4 dark:border-slate-800">
                                <ol class="mt-3 space-y-2.5">
                                    @foreach (['Scan QR atau buka link kegiatan', 'Login dengan akun anggota', 'Izinkan akses lokasi', 'Klik Saya Hadir', 'Pastikan berada dalam radius lokasi kegiatan'] as $step)
                                        <li class="flex gap-3 text-sm text-slate-700">
                                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-xs font-bold text-emerald-700 ring-1 ring-inset ring-emerald-200">{{ $loop->iteration }}</span>
                                            <span>{{ $step }}</span>
                                        </li>
                                    @endforeach
                                </ol>
                            </div>
                        </section>
                    </div>

                    <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                        <div class="border-b border-slate-200 px-4 py-3 dark:border-slate-800 sm:px-5">
                            <h2 class="text-base font-bold text-slate-950">Riwayat Presensi Pribadi</h2>
                            <p class="mt-1 text-sm text-slate-500">Maksimal 10 presensi terbaru yang tercatat.</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-slate-50">
                                    <tr>
                                        @foreach (['Kegiatan', 'Tanggal', 'Waktu Presensi', 'Kehadiran', 'Verifikasi'] as $heading)
                                            <th class="whitespace-nowrap px-4 py-2.5 text-left text-xs font-bold uppercase tracking-wide text-slate-500">{{ $heading }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 bg-white">
                                    @forelse ($attendanceHistory as $attendance)
                                        <tr class="transition hover:bg-slate-50/70">
                                            <td class="px-4 py-2.5"><p class="whitespace-nowrap text-sm font-semibold text-slate-900">{{ $attendance->activity?->title ?? '-' }}</p></td>
                                            <td class="whitespace-nowrap px-4 py-2.5 text-sm text-slate-600">{{ $attendance->activity?->activity_date?->format('d/m/Y') ?? '-' }}</td>
                                            <td class="whitespace-nowrap px-4 py-2.5 text-sm text-slate-600">{{ $attendance->checked_in_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                            <td class="whitespace-nowrap px-4 py-2.5">
                                                <span class="{{ $attendanceClasses[$attendance->status] ?? $attendanceClasses['absent'] }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ $attendanceLabels[$attendance->status] ?? $attendance->status }}</span>
                                            </td>
                                            <td class="whitespace-nowrap px-4 py-2.5">
                                                <span class="{{ $verificationClasses[$attendance->verification_status] ?? $verificationClasses['need_verification'] }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ $verificationLabels[$attendance->verification_status] ?? $attendance->verification_status }}</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-4 py-8 text-center">
                                                <p class="text-sm font-semibold text-slate-800">Belum ada riwayat presensi.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                </div>
            </main>
        </div>

        <script>
            document.querySelectorAll('.member-check-in-form').forEach((form) => {
                form.addEventListener('submit', (event) => {
                    const message = form.querySelector('.member-check-in-message');

                    if (! navigator.geolocation) {
                        event.preventDefault();
                        message.textContent = 'Browser Anda belum mendukung akses lokasi.';
                        message.className = 'member-check-in-message text-xs font-semibold text-red-600';
                        return;
                    }

                    if (form.dataset.locationReady === '1') {
                        return;
                    }

                    event.preventDefault();
                    message.textContent = 'Meminta lokasi Anda...';
                    message.className = 'member-check-in-message text-xs font-semibold text-slate-600';

                    navigator.geolocation.getCurrentPosition((position) => {
                        form.querySelector('[name="latitude"]').value = position.coords.latitude;
                        form.querySelector('[name="longitude"]').value = position.coords.longitude;
                        form.querySelector('[name="location_accuracy"]').value = position.coords.accuracy ?? 0;
                        form.dataset.locationReady = '1';
                        form.requestSubmit();
                    }, () => {
                        message.textContent = 'Lokasi tidak diizinkan. Aktifkan izin lokasi untuk presensi.';
                        message.className = 'member-check-in-message text-xs font-semibold text-red-600';
                    }, {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0,
                    });
                });
            });
        </script>
    </body>
</html>
