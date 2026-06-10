@extends('layouts.admin')

@section('title', 'Buat Kegiatan dari Jadwal - Pemuda Cirengit')
@section('section', 'Kegiatan')
@section('page-title', 'Buat Kegiatan dari Jadwal')

@section('content')
    <div class="max-w-3xl space-y-6">
        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">Jadwal Agenda</p>
            <h2 class="mt-2 text-2xl font-bold text-slate-950">{{ $agendaSchedule->title }}</h2>
            <dl class="mt-6 grid gap-4 border-t border-slate-200 pt-6 sm:grid-cols-2">
                <div><dt class="text-xs font-semibold uppercase text-slate-500">Bidang</dt><dd class="mt-1 text-sm text-slate-700">{{ $agendaSchedule->department?->name ?? '-' }}</dd></div>
                <div><dt class="text-xs font-semibold uppercase text-slate-500">PIC</dt><dd class="mt-1 text-sm text-slate-700">{{ $agendaSchedule->pic?->full_name ?? '-' }}</dd></div>
                <div><dt class="text-xs font-semibold uppercase text-slate-500">Waktu</dt><dd class="mt-1 text-sm text-slate-700">{{ $agendaSchedule->start_time ? substr($agendaSchedule->start_time, 0, 5) : '-' }}{{ $agendaSchedule->end_time ? ' - '.substr($agendaSchedule->end_time, 0, 5) : '' }}</dd></div>
                <div><dt class="text-xs font-semibold uppercase text-slate-500">Lokasi</dt><dd class="mt-1 text-sm text-slate-700">{{ $agendaSchedule->default_location ?: '-' }}</dd></div>
            </dl>

            <form method="POST" action="{{ route('agenda-schedules.activities.store', $agendaSchedule) }}" class="mt-6 border-t border-slate-200 pt-6">
                @csrf
                <label for="activity_date" class="block text-sm font-semibold text-slate-700">Tanggal Pelaksanaan</label>
                <input id="activity_date" name="activity_date" type="date" value="{{ old('activity_date', $agendaSchedule->specific_date?->format('Y-m-d')) }}" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600" required>
                @error('activity_date') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror

                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <a href="{{ route('agenda-schedules.show', $agendaSchedule) }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</a>
                    <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Buat Kegiatan</button>
                </div>
            </form>
        </div>
    </div>
@endsection
