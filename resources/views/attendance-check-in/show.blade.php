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
            $verificationLabels = ['valid' => 'Valid', 'need_verification' => 'Perlu Verifikasi', 'rejected' => 'Ditolak'];
            $availabilityMessages = [
                'disabled' => 'Presensi belum diaktifkan untuk kegiatan ini.',
                'not_configured' => 'Waktu presensi belum dikonfigurasi oleh admin.',
                'not_open' => 'Presensi belum dibuka.',
                'closed' => 'Presensi sudah ditutup.',
            ];
            $canSubmit = $member && $availability === 'open' && $canRetry && $activity->latitude !== null && $activity->longitude !== null;
        @endphp

        <div class="min-h-screen">
            <header class="border-b border-slate-200 bg-white">
                <div class="mx-auto flex h-16 max-w-3xl items-center justify-between px-4 sm:px-6">
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-700 text-xs font-bold text-white">PC</span>
                        <span class="text-sm font-bold text-slate-900">Pemuda Cirengit</span>
                    </a>
                    <span class="text-sm font-semibold text-slate-600">{{ Auth::user()->name }}</span>
                </div>
            </header>

            <main class="mx-auto max-w-3xl space-y-5 px-4 py-6 sm:px-6 sm:py-10">
                @foreach ([
                    'success' => 'border-emerald-200 bg-emerald-50 text-emerald-800',
                    'warning' => 'border-amber-200 bg-amber-50 text-amber-800',
                    'error' => 'border-red-200 bg-red-50 text-red-800',
                    'info' => 'border-sky-200 bg-sky-50 text-sky-800',
                ] as $flash => $classes)
                    @if (session($flash))
                        <div class="{{ $classes }} rounded-lg border px-4 py-3 text-sm font-medium">{{ session($flash) }}</div>
                    @endif
                @endforeach

                <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                    <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">Presensi Kegiatan</p>
                    <h1 class="mt-2 text-2xl font-bold text-slate-950">{{ $activity->title }}</h1>
                    <dl class="mt-6 grid gap-4 border-t border-slate-200 pt-6 sm:grid-cols-2">
                        <div><dt class="text-xs font-semibold uppercase text-slate-500">Tanggal</dt><dd class="mt-1 text-sm text-slate-700">{{ $activity->activity_date->format('d/m/Y') }}</dd></div>
                        <div><dt class="text-xs font-semibold uppercase text-slate-500">Waktu</dt><dd class="mt-1 text-sm text-slate-700">{{ $activity->start_time ? substr($activity->start_time, 0, 5) : '-' }}{{ $activity->end_time ? ' - '.substr($activity->end_time, 0, 5) : '' }}</dd></div>
                        <div class="sm:col-span-2"><dt class="text-xs font-semibold uppercase text-slate-500">Lokasi</dt><dd class="mt-1 text-sm text-slate-700">{{ $activity->location ?: '-' }}</dd></div>
                    </dl>
                </section>

                @if (! $member)
                    <div class="rounded-lg border border-red-200 bg-red-50 p-5 text-sm text-red-800">Akun Anda belum terhubung dengan data anggota. Hubungi admin untuk mengatur member_id akun.</div>
                @elseif ($attendance && ! $canRetry)
                    <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                        <h2 class="text-base font-bold text-slate-950">Presensi Sudah Tercatat</h2>
                        <dl class="mt-5 grid gap-4 sm:grid-cols-2">
                            <div><dt class="text-xs font-semibold uppercase text-slate-500">Status Kehadiran</dt><dd class="mt-1 text-sm font-semibold text-slate-800">{{ $attendanceLabels[$attendance->status] }}</dd></div>
                            <div><dt class="text-xs font-semibold uppercase text-slate-500">Verifikasi</dt><dd class="mt-1 text-sm font-semibold text-slate-800">{{ $verificationLabels[$attendance->verification_status] }}</dd></div>
                            <div><dt class="text-xs font-semibold uppercase text-slate-500">Waktu Check-in</dt><dd class="mt-1 text-sm text-slate-700">{{ $attendance->checked_in_at?->format('d/m/Y H:i:s') ?? '-' }}</dd></div>
                            <div><dt class="text-xs font-semibold uppercase text-slate-500">Jarak</dt><dd class="mt-1 text-sm text-slate-700">{{ $attendance->distance_from_activity !== null ? number_format((float) $attendance->distance_from_activity, 2).' meter' : '-' }}</dd></div>
                        </dl>
                    </section>
                @else
                    @if ($availability !== 'open')
                        <div class="rounded-lg border border-amber-200 bg-amber-50 p-5 text-sm font-medium text-amber-800">{{ $availabilityMessages[$availability] }}</div>
                    @elseif ($activity->latitude === null || $activity->longitude === null)
                        <div class="rounded-lg border border-red-200 bg-red-50 p-5 text-sm font-medium text-red-800">Titik lokasi kegiatan belum dikonfigurasi oleh admin.</div>
                    @endif

                    @if ($attendance && $canRetry)
                        <div class="rounded-lg border border-amber-200 bg-amber-50 p-5 text-sm text-amber-800">Presensi sebelumnya masih perlu verifikasi dan belum diproses admin. Anda boleh mengambil ulang lokasi.</div>
                    @endif

                    <section
                        x-data="{
                            latitude: '', longitude: '', accuracy: '', locating: false,
                            locationReady: false, message: '',
                            getLocation() {
                                if (!navigator.geolocation) {
                                    this.message = 'Browser Anda tidak mendukung geolocation.';
                                    return;
                                }
                                this.locating = true;
                                this.message = 'Mengambil lokasi...';
                                navigator.geolocation.getCurrentPosition(
                                    position => {
                                        this.latitude = position.coords.latitude;
                                        this.longitude = position.coords.longitude;
                                        this.accuracy = position.coords.accuracy;
                                        this.locationReady = true;
                                        this.locating = false;
                                        this.message = 'Lokasi berhasil diperoleh.';
                                    },
                                    error => {
                                        this.locating = false;
                                        this.locationReady = false;
                                        this.message = error.code === 1
                                            ? 'Izin lokasi ditolak. Aktifkan izin lokasi pada browser.'
                                            : 'Lokasi tidak dapat diperoleh. Silakan coba lagi.';
                                    },
                                    { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
                                );
                            }
                        }"
                        class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm"
                    >
                        <h2 class="text-base font-bold text-slate-950">Lokasi Presensi</h2>
                        <p class="mt-2 text-sm leading-6 text-slate-600">Gunakan lokasi perangkat sebelum mengirim kehadiran. Jarak akan dihitung oleh server.</p>

                        <form method="POST" action="{{ route('attendance.check-in.store', $activity->attendance_token) }}" class="mt-6 space-y-4">
                            @csrf
                            <input type="hidden" name="latitude" x-model="latitude">
                            <input type="hidden" name="longitude" x-model="longitude">
                            <input type="hidden" name="location_accuracy" x-model="accuracy">

                            <div class="rounded-lg bg-slate-50 p-4 text-sm text-slate-600">
                                <p x-text="message || 'Lokasi belum diambil.'"></p>
                                <template x-if="locationReady"><p class="mt-2 text-xs" x-text="'Akurasi: ' + Number(accuracy).toFixed(2) + ' meter'"></p></template>
                            </div>

                            @if ($errors->any())
                                <div class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">Lokasi tidak valid. Silakan ambil ulang lokasi perangkat.</div>
                            @endif

                            <div class="grid gap-3 sm:grid-cols-2">
                                <button type="button" @click="getLocation" :disabled="locating || !@js($canSubmit)" class="inline-flex items-center justify-center rounded-lg border border-emerald-700 px-4 py-3 text-sm font-semibold text-emerald-700 hover:bg-emerald-50 disabled:cursor-not-allowed disabled:opacity-50">
                                    <span x-text="locating ? 'Mengambil Lokasi...' : 'Gunakan Lokasi Saya'"></span>
                                </button>
                                <button type="submit" :disabled="!locationReady || !@js($canSubmit)" class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-800 disabled:cursor-not-allowed disabled:opacity-50">Saya Hadir</button>
                            </div>
                        </form>
                    </section>
                @endif
            </main>
        </div>
    </body>
</html>
