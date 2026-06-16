@extends('layouts.admin')

@section('title', 'Generate Kegiatan Bulanan - Pemuda Cirengit')
@section('section', 'Agenda')
@section('page-title', 'Generate Kegiatan Bulanan')
@section('breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Jadwal Agenda', 'url' => route('agenda-schedules.index')],
        ['label' => 'Detail Jadwal', 'url' => route('agenda-schedules.show', $agendaSchedule)],
        ['label' => 'Generate Kegiatan Bulanan'],
    ]" />
@endsection

@section('content')
    @php
        $dayLabels = [0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'];
        $monthOptions = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
        $currentYear = (int) now()->year;
    @endphp

    <div class="mx-auto max-w-3xl space-y-6">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">Jadwal Agenda Mingguan</p>
            <h2 class="mt-2 text-2xl font-bold text-slate-950">{{ $agendaSchedule->title }}</h2>
            <dl class="mt-5 grid gap-4 border-t border-slate-100 pt-5 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Hari</dt>
                    <dd class="mt-1 text-sm font-semibold text-slate-800">{{ $dayLabels[$agendaSchedule->day_of_week] ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Waktu</dt>
                    <dd class="mt-1 text-sm text-slate-700">{{ $agendaSchedule->start_time ? substr($agendaSchedule->start_time, 0, 5) : '-' }}{{ $agendaSchedule->end_time ? ' - '.substr($agendaSchedule->end_time, 0, 5) : '' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Bidang</dt>
                    <dd class="mt-1 text-sm text-slate-700">{{ $agendaSchedule->department?->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">PIC</dt>
                    <dd class="mt-1 text-sm text-slate-700">{{ $agendaSchedule->pic?->full_name ?? 'Fallback ke Ketua Bidang jika tersedia' }}</dd>
                </div>
            </dl>
        </section>

        <form method="POST" action="{{ route('agenda-schedules.generate-monthly.store', $agendaSchedule) }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm" x-data="{ submitting: false }" x-on:submit="submitting = true">
            @csrf
            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-base font-bold text-slate-950">Pilih Bulan Generate</h3>
                <p class="mt-1 text-sm text-slate-500">Sistem akan membuat Kegiatan Aktual pada setiap tanggal di bulan terpilih yang sesuai hari jadwal mingguan.</p>
            </div>

            <div class="mt-5 grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="month" class="block text-sm font-semibold text-slate-700">Bulan</label>
                    <select id="month" name="month" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600" required>
                        @foreach ($monthOptions as $value => $label)
                            <option value="{{ $value }}" @selected((int) old('month', now()->month) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('month') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="year" class="block text-sm font-semibold text-slate-700">Tahun</label>
                    <input id="year" name="year" type="number" min="2000" max="2100" value="{{ old('year', $currentYear) }}" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600" required>
                    @error('year') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
                <a href="{{ route('agenda-schedules.show', $agendaSchedule) }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Batal/Kembali</a>
                <x-ui.submit-button loading-text="Mengenerate...">Generate Kegiatan</x-ui.submit-button>
            </div>
        </form>
    </div>
@endsection
