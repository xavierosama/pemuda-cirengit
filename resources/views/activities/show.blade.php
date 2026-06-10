@extends('layouts.admin')

@section('title', 'Detail Kegiatan Aktual - Pemuda Cirengit')
@section('section', 'Kegiatan')
@section('page-title', 'Detail Kegiatan Aktual')

@section('content')
    @php
        $statusLabels = ['scheduled' => 'Terjadwal', 'completed' => 'Selesai', 'holiday' => 'Libur', 'postponed' => 'Ditunda', 'relocated' => 'Pindah Lokasi', 'cancelled' => 'Dibatalkan'];
        $statusClasses = ['scheduled' => 'bg-sky-50 text-sky-700 ring-sky-200', 'completed' => 'bg-emerald-50 text-emerald-700 ring-emerald-200', 'holiday' => 'bg-slate-100 text-slate-600 ring-slate-200', 'postponed' => 'bg-amber-50 text-amber-700 ring-amber-200', 'relocated' => 'bg-violet-50 text-violet-700 ring-violet-200', 'cancelled' => 'bg-red-50 text-red-700 ring-red-200'];
    @endphp

    <div class="max-w-5xl space-y-6">
        @if (session('success'))<div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>@endif
        @if (session('info'))<div class="rounded-lg border border-sky-200 bg-sky-50 px-4 py-3 text-sm font-medium text-sky-800">{{ session('info') }}</div>@endif

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <a href="{{ route('activities.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">Kembali ke Kegiatan Aktual</a>
            <div class="flex flex-col gap-2 sm:flex-row">
                <a href="{{ route('activities.attendances.index', $activity) }}" class="inline-flex items-center justify-center rounded-lg border border-emerald-700 px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-50">Daftar Hadir</a>
                <a href="{{ route('activities.edit', $activity) }}" class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Edit Kegiatan</a>
            </div>
        </div>

        <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div><p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">{{ $activity->activity_date->format('d M Y') }}</p><h2 class="mt-2 text-2xl font-bold text-slate-950">{{ $activity->title }}</h2><p class="mt-2 text-sm text-slate-500">{{ $activity->agendaSchedule?->title ?? 'Kegiatan mandiri' }}</p></div>
                <span class="{{ $statusClasses[$activity->status] }} inline-flex w-fit rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset">{{ $statusLabels[$activity->status] }}</span>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-base font-bold text-slate-950">Detail Pelaksanaan</h3>
                <dl class="mt-5 space-y-4">
                    <div><dt class="text-xs font-semibold uppercase text-slate-500">Waktu</dt><dd class="mt-1 text-sm text-slate-700">{{ $activity->start_time ? substr($activity->start_time, 0, 5) : '-' }}{{ $activity->end_time ? ' - '.substr($activity->end_time, 0, 5) : '' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase text-slate-500">Lokasi</dt><dd class="mt-1 text-sm text-slate-700">{{ $activity->location ?: '-' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase text-slate-500">Bidang</dt><dd class="mt-1 text-sm text-slate-700">{{ $activity->department?->name ?? '-' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase text-slate-500">PIC</dt><dd class="mt-1 text-sm text-slate-700">{{ $activity->pic?->full_name ?? '-' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase text-slate-500">Dibuat oleh</dt><dd class="mt-1 text-sm text-slate-700">{{ $activity->creator?->name ?? '-' }}</dd></div>
                </dl>
            </section>

            <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-base font-bold text-slate-950">Informasi Presensi</h3>
                <dl class="mt-5 space-y-4">
                    <div><dt class="text-xs font-semibold uppercase text-slate-500">Status</dt><dd class="mt-1 text-sm font-semibold {{ $activity->attendance_enabled ? 'text-emerald-700' : 'text-slate-600' }}">{{ $activity->attendance_enabled ? 'Aktif' : 'Tidak aktif' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase text-slate-500">Periode</dt><dd class="mt-1 text-sm text-slate-700">{{ $activity->attendance_open_at?->format('d M Y H:i') ?? '-' }}{{ $activity->attendance_close_at ? ' sampai '.$activity->attendance_close_at->format('d M Y H:i') : '' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase text-slate-500">Radius</dt><dd class="mt-1 text-sm text-slate-700">{{ $activity->attendance_radius }} meter</dd></div>
                    <div><dt class="text-xs font-semibold uppercase text-slate-500">Koordinat</dt><dd class="mt-1 text-sm text-slate-700">{{ $activity->latitude ?: '-' }}, {{ $activity->longitude ?: '-' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase text-slate-500">Token</dt><dd class="mt-1 break-all font-mono text-xs text-slate-600">{{ $activity->attendance_token ?: '-' }}</dd></div>
                    @if ($activity->attendance_token)
                        <div><dt class="text-xs font-semibold uppercase text-slate-500">Link Presensi</dt><dd class="mt-2"><a href="{{ route('attendance.check-in.show', $activity->attendance_token) }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">Buka Link Presensi</a></dd></div>
                    @endif
                </dl>
            </section>
        </div>

        <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-base font-bold text-slate-950">Status Perubahan</h3>
            <p class="mt-3 text-sm text-slate-600">Status saat ini: <span class="font-semibold text-slate-800">{{ $statusLabels[$activity->status] }}</span></p>
            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-600">{{ $activity->change_reason ?: 'Tidak ada alasan perubahan.' }}</p>

            <form method="POST" action="{{ route('activities.status.update', $activity) }}" class="mt-6 grid gap-4 border-t border-slate-200 pt-6 sm:grid-cols-[220px_1fr_auto]">
                @csrf @method('PATCH')
                <select name="status" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600" required>
                    @foreach ($statusLabels as $value => $label)<option value="{{ $value }}" @selected($activity->status === $value)>{{ $label }}</option>@endforeach
                </select>
                <input name="change_reason" type="text" value="{{ $activity->change_reason }}" placeholder="Alasan perubahan status" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Perbarui Status</button>
            </form>
        </section>
    </div>
@endsection
