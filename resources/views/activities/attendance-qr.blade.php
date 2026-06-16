@extends('layouts.admin')

@section('title', 'QR Presensi - Pemuda Cirengit')
@section('section', 'Kegiatan')
@section('page-title', 'QR Presensi')

@section('content')
    @php
        $time = trim(($activity->start_time ? substr($activity->start_time, 0, 5) : '').($activity->end_time ? ' - '.substr($activity->end_time, 0, 5) : ''));
        $shortUrl = $attendanceUrl ? preg_replace('#^https?://#', '', $attendanceUrl) : null;
        $attendanceAvailability = $activity->attendanceAvailability();
    @endphp

    <div class="mx-auto max-w-5xl space-y-5" x-data="{ copied: false }">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">QR Presensi Kegiatan</p>
                <h2 class="mt-1 text-2xl font-bold text-slate-950">{{ $activity->title }}</h2>
                <p class="mt-2 text-sm text-slate-500">Tampilkan QR ini saat kegiatan berlangsung agar anggota bisa melakukan check-in.</p>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row">
                <a href="{{ route('activities.show', $activity) }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Kembali ke Detail Kegiatan
                </a>
                @if ($attendanceUrl)
                    <a href="{{ $attendanceUrl }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800">
                        Buka Halaman Check-in
                    </a>
                @endif
            </div>
        </div>

        @if ($attendanceAvailability === 'not_available')
            <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h3 class="text-base font-bold text-amber-900">Presensi tidak tersedia.</h3>
                        <p class="mt-1 text-sm leading-6 text-amber-800">Status kegiatan saat ini tidak membuka akses presensi untuk anggota.</p>
                    </div>
                    <x-ui.status-badge :status="$attendanceAvailability" :label="$activity->attendanceAvailabilityLabel()" />
                </div>
            </div>
        @endif

        <div class="grid gap-5 lg:grid-cols-[1.1fr_0.9fr]">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <div class="flex flex-col gap-3 border-b border-slate-100 pb-4 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-950">Informasi Kegiatan</h3>
                        <p class="mt-1 text-sm text-slate-500">Detail kegiatan yang terhubung dengan QR presensi.</p>
                    </div>
                    <x-ui.status-badge :status="$attendanceAvailability" :label="$activity->attendanceAvailabilityLabel()" />
                </div>

                <dl class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Nama Kegiatan</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $activity->title }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Tanggal</dt>
                        <dd class="mt-1 text-sm text-slate-700">{{ $activity->activity_date->format('d/m/Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Waktu</dt>
                        <dd class="mt-1 text-sm text-slate-700">{{ $time !== '' ? $time : '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Bidang</dt>
                        <dd class="mt-1 text-sm text-slate-700">{{ $activity->department?->name ?? '-' }}</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Lokasi</dt>
                        <dd class="mt-1 text-sm text-slate-700">{{ $activity->location ?: '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">PIC</dt>
                        <dd class="mt-1 text-sm text-slate-700">{{ $activity->pic?->full_name ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Radius Presensi</dt>
                        <dd class="mt-1 text-sm text-slate-700">{{ number_format((float) $activity->attendance_radius) }} meter</dd>
                    </div>
                </dl>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                <h3 class="text-base font-bold text-slate-950">Status Presensi</h3>
                <p class="mt-1 text-sm text-slate-500">Gunakan informasi ini untuk memastikan waktu presensi sudah sesuai.</p>

                <dl class="mt-5 space-y-4">
                    <div class="flex items-start justify-between gap-4 rounded-xl bg-slate-50 p-4">
                        <dt class="text-sm font-semibold text-slate-700">Status</dt>
                        <dd>
                            <x-ui.status-badge :status="$attendanceAvailability" :label="$activity->attendanceAvailabilityLabel()" />
                        </dd>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-4">
                        <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Waktu Buka Presensi Otomatis</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-800">{{ $activity->attendance_open_at?->format('d/m/Y H:i') ?? '-' }}</dd>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-4">
                        <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Waktu Tutup Presensi Otomatis</dt>
                        <dd class="mt-1 text-sm font-semibold text-slate-800">{{ $activity->attendance_close_at?->format('d/m/Y H:i') ?? '-' }}</dd>
                    </div>
                </dl>
            </section>
        </div>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 text-center shadow-sm sm:p-8">
            @if ($attendanceUrl && $qrCode)
                <div class="mx-auto flex aspect-square w-full max-w-sm items-center justify-center rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:max-w-md">
                    <img src="{{ $qrCode }}" alt="QR presensi {{ $activity->title }}" class="h-full w-full object-contain">
                </div>

                <p class="mt-6 text-base font-bold text-slate-900">Scan QR menggunakan kamera HP.</p>
                <p class="mt-2 text-sm text-slate-500">Pastikan anggota login dan mengizinkan akses lokasi saat melakukan presensi.</p>

                <div class="mx-auto mt-5 max-w-2xl rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Link Presensi</p>
                    <p class="mt-2 break-all font-mono text-xs leading-5 text-slate-700">{{ $shortUrl }}</p>
                    <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:justify-center">
                        <button type="button" @click="navigator.clipboard.writeText(@js($attendanceUrl)).then(() => { copied = true; setTimeout(() => copied = false, 2000) })" class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800">
                            Salin Link
                        </button>
                        <a href="{{ $attendanceUrl }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                            Buka Halaman Check-in
                        </a>
                    </div>
                    <p x-cloak x-show="copied" x-transition class="mt-3 text-sm font-semibold text-emerald-700">Link disalin</p>
                </div>
            @else
                <div class="mx-auto max-w-xl rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-5 py-10">
                    <p class="text-base font-bold text-slate-900">QR belum tersedia.</p>
                    <p class="mt-2 text-sm leading-6 text-slate-600">Presensi tidak tersedia untuk status kegiatan saat ini, sehingga QR tidak ditampilkan.</p>
                </div>
            @endif
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <h3 class="text-base font-bold text-slate-950">Instruksi Singkat</h3>
            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                @foreach (['Scan QR menggunakan kamera HP.', 'Login menggunakan akun anggota.', 'Izinkan akses lokasi.', 'Klik Saya Hadir.'] as $instruction)
                    <div class="flex gap-3 rounded-xl bg-slate-50 p-4 text-left text-sm text-slate-700">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-50 text-xs font-bold text-emerald-700 ring-1 ring-inset ring-emerald-200">{{ $loop->iteration }}</span>
                        <span>{{ $instruction }}</span>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
@endsection
