<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Presensi {{ $activity->title }} - Pemuda Cirengit</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-100 font-sans antialiased text-slate-900">
        @php
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
            $availabilityMessages = [
                'disabled' => 'Presensi belum diaktifkan untuk kegiatan ini.',
                'not_configured' => 'Waktu presensi belum dikonfigurasi oleh admin.',
                'not_open' => 'Presensi belum dibuka.',
                'closed' => 'Presensi sudah ditutup.',
            ];
            $availabilityLabels = [
                'open' => 'Dibuka',
                'closed' => 'Ditutup',
                'not_open' => 'Belum Dibuka',
                'disabled' => 'Ditutup',
                'not_configured' => 'Ditutup',
            ];
            $availabilityClasses = [
                'open' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                'closed' => 'bg-red-50 text-red-700 ring-red-200',
                'not_open' => 'bg-amber-50 text-amber-700 ring-amber-200',
                'disabled' => 'bg-slate-100 text-slate-600 ring-slate-200',
                'not_configured' => 'bg-slate-100 text-slate-600 ring-slate-200',
            ];
            $hasRecordedAttendance = $attendance && $attendance->status !== 'absent';
            $canSubmit = $member && $availability === 'open' && ! $hasRecordedAttendance && $activity->latitude !== null && $activity->longitude !== null;
            $time = trim(($activity->start_time ? substr($activity->start_time, 0, 5) : '').($activity->end_time ? ' - '.substr($activity->end_time, 0, 5) : ''));
        @endphp

        <div class="min-h-screen">
            <header class="border-b border-slate-200 bg-white">
                <div class="mx-auto flex h-14 max-w-2xl items-center justify-between px-4 sm:px-6">
                    <a href="{{ Auth::user()->role === 'member' ? route('member.home') : route('dashboard') }}" class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-700 text-xs font-bold text-white">PC</span>
                        <span class="text-sm font-bold text-slate-900">Pemuda Cirengit</span>
                    </a>
                    <span class="max-w-36 truncate text-xs font-semibold text-slate-500 sm:max-w-none sm:text-sm">{{ Auth::user()->name }}</span>
                </div>
            </header>

            <main class="mx-auto max-w-2xl space-y-4 px-4 py-5 sm:px-6 sm:py-8">
                @foreach ([
                    'success' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
                    'warning' => 'border-amber-200 bg-amber-50 text-amber-800',
                    'error' => 'border-red-200 bg-red-50 text-red-800',
                    'info' => 'border-sky-200 bg-sky-50 text-sky-800',
                ] as $flash => $classes)
                    @if (session($flash))
                        <div class="{{ $classes }} rounded-xl border px-4 py-3 text-sm font-medium">{{ session($flash) }}</div>
                    @endif
                @endforeach

                <section class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                    <div class="border-b border-slate-100 p-5">
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">Presensi Kegiatan</p>
                                <h1 class="mt-1 text-xl font-bold text-slate-950 sm:text-2xl">{{ $activity->title }}</h1>
                            </div>
                            <span class="{{ $availabilityClasses[$availability] ?? $availabilityClasses['disabled'] }} inline-flex w-fit rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset">
                                {{ $availabilityLabels[$availability] ?? 'Ditutup' }}
                            </span>
                        </div>
                    </div>

                    <dl class="grid gap-4 p-5 sm:grid-cols-2">
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Tanggal</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-800">{{ $activity->activity_date->format('d/m/Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Waktu</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-800">{{ $time !== '' ? $time : '-' }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Lokasi</dt>
                            <dd class="mt-1 text-sm text-slate-700">{{ $activity->location ?: '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Bidang</dt>
                            <dd class="mt-1 text-sm text-slate-700">{{ $activity->department?->name ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">PIC</dt>
                            <dd class="mt-1 text-sm text-slate-700">{{ $activity->pic?->full_name ?? '-' }}</dd>
                        </div>
                    </dl>
                </section>

                @if (! $member)
                    <section class="rounded-2xl border border-red-200 bg-red-50 p-5 shadow-sm">
                        <h2 class="text-base font-bold text-red-900">Akun belum terhubung</h2>
                        <p class="mt-2 text-sm leading-6 text-red-800">Akun Anda belum terhubung dengan data anggota.</p>
                        <p class="mt-1 text-sm leading-6 text-red-700">Silakan hubungi pengurus untuk menghubungkan akun dengan data anggota.</p>
                    </section>
                @elseif ($hasRecordedAttendance)
                    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <h2 class="text-base font-bold text-slate-950">Presensi Sudah Tercatat</h2>
                                <p class="mt-1 text-sm text-slate-500">Terima kasih, data presensi Anda sudah masuk ke sistem.</p>
                            </div>
                            <span class="{{ $attendanceClasses[$attendance->status] ?? $attendanceClasses['absent'] }} inline-flex w-fit rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset">{{ $attendanceLabels[$attendance->status] ?? $attendance->status }}</span>
                        </div>
                        <dl class="mt-5 grid gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Status Kehadiran</dt>
                                <dd class="mt-1">
                                    <span class="{{ $attendanceClasses[$attendance->status] ?? $attendanceClasses['absent'] }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ $attendanceLabels[$attendance->status] ?? $attendance->status }}</span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Status Verifikasi</dt>
                                <dd class="mt-1">
                                    <span class="{{ $verificationClasses[$attendance->verification_status] ?? $verificationClasses['need_verification'] }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ $verificationLabels[$attendance->verification_status] ?? $attendance->verification_status }}</span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Waktu Presensi</dt>
                                <dd class="mt-1 text-sm font-semibold text-slate-800">{{ $attendance->checked_in_at?->format('d/m/Y H:i') ?? '-' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Jarak dari Lokasi</dt>
                                <dd class="mt-1 text-sm font-semibold text-slate-800">{{ $attendance->distance_from_activity !== null ? number_format((float) $attendance->distance_from_activity, 2).' meter' : '-' }}</dd>
                            </div>
                        </dl>
                    </section>
                @else
                    @if ($availability !== 'open')
                        <section class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
                            <h2 class="text-base font-bold text-amber-900">{{ $availabilityMessages[$availability] ?? 'Presensi tidak tersedia.' }}</h2>
                            <p class="mt-2 text-sm leading-6 text-amber-800">Silakan kembali ke halaman ini sesuai waktu presensi yang ditentukan pengurus.</p>
                        </section>
                    @elseif ($activity->latitude === null || $activity->longitude === null)
                        <section class="rounded-2xl border border-red-200 bg-red-50 p-5 shadow-sm">
                            <h2 class="text-base font-bold text-red-900">Lokasi kegiatan belum siap</h2>
                            <p class="mt-2 text-sm leading-6 text-red-800">Titik lokasi kegiatan belum dikonfigurasi oleh admin.</p>
                        </section>
                    @endif

                    <section
                        x-data="{
                            latitude: '',
                            longitude: '',
                            accuracy: '',
                            locating: false,
                            processing: false,
                            message: 'Tekan tombol Saya Hadir untuk mengambil lokasi dan mengirim presensi.',
                            submitAttendance(form) {
                                if (!@js($canSubmit)) {
                                    return;
                                }

                                if (!navigator.geolocation) {
                                    this.message = 'Browser Anda belum mendukung akses lokasi.';
                                    return;
                                }

                                this.locating = true;
                                this.processing = false;
                                this.message = 'Mengambil lokasi...';

                                navigator.geolocation.getCurrentPosition(
                                    position => {
                                        this.latitude = position.coords.latitude;
                                        this.longitude = position.coords.longitude;
                                        this.accuracy = position.coords.accuracy ?? 0;
                                        this.locating = false;
                                        this.processing = true;
                                        this.message = 'Memproses presensi...';

                                        this.$nextTick(() => form.submit());
                                    },
                                    error => {
                                        this.locating = false;
                                        this.processing = false;
                                        this.message = error.code === 1
                                            ? 'Izin lokasi ditolak. Aktifkan izin lokasi pada browser, lalu coba lagi.'
                                            : 'Lokasi tidak dapat diperoleh. Pastikan GPS aktif lalu coba lagi.';
                                    },
                                    { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
                                );
                            }
                        }"
                        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                    >
                        <div class="text-center">
                            <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">Status Akses Presensi</p>
                            @if ($availability === 'open' && $activity->latitude !== null && $activity->longitude !== null)
                                <h2 class="mt-2 text-lg font-bold text-slate-950">Presensi sedang dibuka.</h2>
                                <p class="mt-2 text-sm leading-6 text-slate-600">Sistem akan meminta lokasi perangkat untuk memverifikasi jarak dari titik kegiatan.</p>
                            @elseif ($availability === 'not_open')
                                <h2 class="mt-2 text-lg font-bold text-slate-950">Presensi belum dibuka.</h2>
                            @elseif ($availability === 'closed')
                                <h2 class="mt-2 text-lg font-bold text-slate-950">Presensi sudah ditutup.</h2>
                            @else
                                <h2 class="mt-2 text-lg font-bold text-slate-950">Presensi tidak tersedia.</h2>
                            @endif
                        </div>

                        <form method="POST" action="{{ route('attendance.check-in.store', $activity->attendance_token) }}" class="mt-5 space-y-4" @submit.prevent="submitAttendance($el)">
                            @csrf
                            <input type="hidden" name="latitude" x-model="latitude">
                            <input type="hidden" name="longitude" x-model="longitude">
                            <input type="hidden" name="location_accuracy" x-model="accuracy">

                            <div @class([
                                'rounded-xl border px-4 py-3 text-sm font-medium',
                                'border-slate-200 bg-slate-50 text-slate-700' => ! $errors->any(),
                                'border-red-200 bg-red-50 text-red-700' => $errors->any(),
                            ])>
                                @if ($errors->any())
                                    Lokasi tidak valid. Silakan izinkan akses lokasi dan coba lagi.
                                @else
                                    <span x-text="message"></span>
                                @endif
                            </div>

                            <button type="submit" :disabled="locating || processing || !@js($canSubmit)" class="inline-flex w-full items-center justify-center rounded-xl bg-emerald-700 px-5 py-3.5 text-base font-bold text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
                                <span x-text="locating ? 'Mengambil lokasi...' : (processing ? 'Memproses presensi...' : 'Saya Hadir')"></span>
                            </button>
                        </form>
                    </section>
                @endif

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-base font-bold text-slate-950">Catatan Presensi</h2>
                    <ul class="mt-4 space-y-3 text-sm text-slate-700">
                        @foreach (['Pastikan berada di lokasi kegiatan.', 'Izinkan akses lokasi pada browser.', 'Jarak dari lokasi akan diverifikasi oleh sistem.'] as $note)
                            <li class="flex gap-3">
                                <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>
                                <span>{{ $note }}</span>
                            </li>
                        @endforeach
                    </ul>
                </section>
            </main>
        </div>
    </body>
</html>
