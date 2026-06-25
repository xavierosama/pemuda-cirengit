@extends('layouts.admin')

@section('title', 'Detail Jadwal Agenda - Pemuda Cirengit')
@section('section', 'Agenda')
@section('page-title', 'Detail Jadwal Agenda')

@section('content')
    @php
        $typeLabels = ['incidental' => 'Insidental', 'weekly' => 'Mingguan', 'monthly' => 'Bulanan', 'yearly' => 'Tahunan'];
        $dayLabels = [0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'];
        $pattern = match ($agendaSchedule->schedule_type) {
            'incidental' => \App\Support\DateFormatter::date($agendaSchedule->specific_date),
            'weekly' => isset($dayLabels[$agendaSchedule->day_of_week]) ? 'Setiap '.$dayLabels[$agendaSchedule->day_of_week] : '-',
            'monthly' => 'Setiap tanggal '.$agendaSchedule->day_of_month,
            'yearly' => $agendaSchedule->specific_date ? 'Tahunan, '.$agendaSchedule->specific_date->format('d/m') : 'Tahunan',
            default => '-',
        };
    @endphp

    <div class="max-w-5xl space-y-6">
        @if (session('info'))
            <div class="rounded-lg border border-sky-200 bg-sky-50 px-4 py-3 text-sm font-medium text-sky-800">{{ session('info') }}</div>
        @endif

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <a href="{{ route('agenda-schedules.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">Kembali ke Jadwal Agenda</a>
            <div class="flex flex-col gap-2 sm:flex-row">
                <a href="{{ route('agenda-schedules.activities.create', $agendaSchedule) }}" class="inline-flex items-center justify-center rounded-lg border border-emerald-700 px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-50">Buat Kegiatan dari Jadwal</a>
                @if ($agendaSchedule->schedule_type === 'weekly')
                    <a href="{{ route('agenda-schedules.generate-monthly.create', $agendaSchedule) }}" class="inline-flex items-center justify-center rounded-lg border border-sky-600 px-4 py-2 text-sm font-semibold text-sky-700 hover:bg-sky-50">Generate Kegiatan Bulanan</a>
                @endif
                <a href="{{ route('agenda-schedules.edit', $agendaSchedule) }}" class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Edit Jadwal</a>
            </div>
        </div>

        <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div><p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">{{ $typeLabels[$agendaSchedule->schedule_type] ?? $agendaSchedule->schedule_type }}</p><h2 class="mt-2 text-2xl font-bold text-slate-950">{{ $agendaSchedule->title }}</h2><p class="mt-3 max-w-3xl whitespace-pre-line text-sm leading-6 text-slate-600">{{ $agendaSchedule->description ?: 'Tidak ada deskripsi.' }}</p></div>
                <x-ui.status-badge class="w-fit" :status="$agendaSchedule->is_active ? 'active' : 'inactive'" :label="$agendaSchedule->is_active ? 'Aktif' : 'Nonaktif'" />
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-2">
            <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-base font-bold text-slate-950">Pola Jadwal</h3>
                <dl class="mt-5 space-y-4">
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Pengulangan</dt><dd class="mt-1 text-sm font-semibold text-slate-800">{{ $pattern }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Waktu</dt><dd class="mt-1 text-sm text-slate-700">{{ $agendaSchedule->start_time ? substr($agendaSchedule->start_time, 0, 5) : '-' }}{{ $agendaSchedule->end_time ? ' - '.substr($agendaSchedule->end_time, 0, 5) : '' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Bidang</dt><dd class="mt-1 text-sm text-slate-700">{{ $agendaSchedule->department?->name ?? '-' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">PIC</dt><dd class="mt-1 text-sm text-slate-700">{{ $agendaSchedule->pic?->full_name ?? '-' }}</dd></div>
                </dl>
            </section>

            <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-base font-bold text-slate-950">Lokasi Default</h3>
                <dl class="mt-5 space-y-4">
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Lokasi</dt><dd class="mt-1 text-sm text-slate-700">{{ $agendaSchedule->default_location ?: '-' }}</dd></div>
                    <div class="grid grid-cols-2 gap-4"><div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Latitude</dt><dd class="mt-1 text-sm text-slate-700">{{ $agendaSchedule->default_latitude ?: '-' }}</dd></div><div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Longitude</dt><dd class="mt-1 text-sm text-slate-700">{{ $agendaSchedule->default_longitude ?: '-' }}</dd></div></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Radius</dt><dd class="mt-1 text-sm text-slate-700">{{ $agendaSchedule->default_radius }} meter</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Dibuat oleh</dt><dd class="mt-1 text-sm text-slate-700">{{ $agendaSchedule->creator?->name ?? '-' }}</dd></div>
                </dl>
            </section>
        </div>
    </div>
@endsection
