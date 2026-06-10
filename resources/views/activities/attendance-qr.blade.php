@extends('layouts.admin')

@section('title', 'QR Presensi - Pemuda Cirengit')
@section('section', 'Kegiatan')
@section('page-title', 'QR Presensi')

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <a href="{{ route('activities.show', $activity) }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">Kembali ke Detail Kegiatan</a>
            @if ($attendanceUrl)
                <a href="{{ $attendanceUrl }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">Buka Link Presensi</a>
            @endif
        </div>

        @if (! $activity->attendance_enabled)
            <section class="rounded-lg border border-amber-200 bg-amber-50 p-6 text-center shadow-sm">
                <h2 class="text-lg font-bold text-amber-900">Presensi belum diaktifkan untuk kegiatan ini.</h2>
                <p class="mt-2 text-sm text-amber-800">Aktifkan presensi melalui halaman edit kegiatan sebelum menampilkan QR.</p>
            </section>
        @else
            <section class="rounded-lg border border-slate-200 bg-white p-6 text-center shadow-sm sm:p-8">
                <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">QR Presensi Kegiatan</p>
                <h2 class="mt-2 text-2xl font-bold text-slate-950">{{ $activity->title }}</h2>
                <div class="mt-4 flex flex-wrap justify-center gap-x-4 gap-y-1 text-sm text-slate-600">
                    <span>{{ $activity->activity_date->format('d/m/Y') }}</span>
                    <span>{{ $activity->start_time ? substr($activity->start_time, 0, 5) : '-' }}{{ $activity->end_time ? ' - '.substr($activity->end_time, 0, 5) : '' }}</span>
                    <span>{{ $activity->location ?: 'Lokasi belum diisi' }}</span>
                </div>

                <div class="mx-auto mt-8 flex aspect-square w-full max-w-md items-center justify-center bg-white p-3 ring-1 ring-slate-200">
                    <img src="{{ $qrCode }}" alt="QR presensi {{ $activity->title }}" class="h-full w-full object-contain">
                </div>

                <p class="mt-6 text-base font-semibold text-slate-800">Scan QR ini untuk melakukan presensi. Pastikan lokasi HP aktif.</p>
                <p class="mx-auto mt-4 max-w-2xl break-all rounded-lg bg-slate-50 px-4 py-3 font-mono text-xs leading-5 text-slate-600">{{ $attendanceUrl }}</p>
            </section>
        @endif
    </div>
@endsection
