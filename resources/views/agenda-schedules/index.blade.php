@extends('layouts.admin')

@section('title', 'Jadwal Agenda - Pemuda Cirengit')
@section('section', 'Agenda')
@section('page-title', 'Jadwal Agenda')

@section('content')
    @php
        $typeLabels = ['once' => 'Satu Kali', 'daily' => 'Harian', 'weekly' => 'Mingguan', 'monthly' => 'Bulanan'];
        $dayLabels = [0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'];
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-950">Jadwal Agenda</h2>
                <p class="mt-1 text-sm text-slate-500">Kelola pola jadwal kegiatan rutin dan satu kali.</p>
            </div>
            <a href="{{ route('agenda-schedules.create') }}" class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-800">Tambah Jadwal</a>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
        @endif

        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <form method="GET" action="{{ route('agenda-schedules.index') }}" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-[minmax(220px,1fr)_200px_180px_170px_auto]">
                <input name="search" type="search" value="{{ $search }}" placeholder="Cari nama agenda" aria-label="Cari agenda" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                <select name="department_id" aria-label="Filter bidang" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                    <option value="">Semua bidang</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected((string) $departmentId === (string) $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
                <select name="schedule_type" aria-label="Filter tipe jadwal" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                    <option value="">Semua tipe</option>
                    @foreach ($typeLabels as $value => $label)
                        <option value="{{ $value }}" @selected($scheduleType === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="is_active" aria-label="Filter status" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                    <option value="">Semua status</option>
                    <option value="1" @selected($activeStatus === '1')>Aktif</option>
                    <option value="0" @selected($activeStatus === '0')>Nonaktif</option>
                </select>
                <div class="flex gap-2 sm:col-span-2 xl:col-span-1">
                    <button type="submit" class="flex-1 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Filter</button>
                    <a href="{{ route('agenda-schedules.index') }}" class="flex flex-1 items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Reset</a>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50"><tr>
                        @foreach (['Agenda', 'Bidang', 'PIC', 'Pola Jadwal', 'Waktu', 'Lokasi', 'Status'] as $heading)
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">{{ $heading }}</th>
                        @endforeach
                        <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">Aksi</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($agendaSchedules as $agendaSchedule)
                            @php
                                $pattern = match ($agendaSchedule->schedule_type) {
                                    'once' => $agendaSchedule->specific_date?->format('d/m/Y') ?? '-',
                                    'daily' => 'Setiap hari',
                                    'weekly' => isset($dayLabels[$agendaSchedule->day_of_week]) ? 'Setiap '.$dayLabels[$agendaSchedule->day_of_week] : '-',
                                    'monthly' => 'Setiap tanggal '.$agendaSchedule->day_of_month,
                                };
                            @endphp
                            <tr>
                                <td class="px-4 py-4 text-sm font-semibold text-slate-900">{{ $agendaSchedule->title }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">{{ $agendaSchedule->department?->name ?? '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">{{ $agendaSchedule->pic?->full_name ?? '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-4"><p class="text-sm font-medium text-slate-700">{{ $typeLabels[$agendaSchedule->schedule_type] }}</p><p class="text-xs text-slate-500">{{ $pattern }}</p></td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">{{ $agendaSchedule->start_time ? substr($agendaSchedule->start_time, 0, 5) : '-' }}{{ $agendaSchedule->end_time ? ' - '.substr($agendaSchedule->end_time, 0, 5) : '' }}</td>
                                <td class="max-w-48 px-4 py-4 text-sm text-slate-600">{{ str($agendaSchedule->default_location ?: '-')->limit(45) }}</td>
                                <td class="whitespace-nowrap px-4 py-4"><span class="{{ $agendaSchedule->is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-slate-100 text-slate-600 ring-slate-200' }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ $agendaSchedule->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-semibold">
                                    <div class="flex justify-end gap-2">
                                        <x-action-icon :href="route('agenda-schedules.show', $agendaSchedule)" label="Detail" icon="eye" variant="blue" />
                                        <x-action-icon :href="route('agenda-schedules.edit', $agendaSchedule)" label="Edit" icon="pencil" variant="amber" />
                                        @if ($agendaSchedule->is_active)
                                            <x-action-icon :action="route('agenda-schedules.deactivate', $agendaSchedule)" method="PATCH" label="Nonaktifkan" icon="ban" variant="slate" confirm="Nonaktifkan jadwal agenda ini?" />
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-10 text-center text-sm text-slate-500">Belum ada jadwal agenda.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $agendaSchedules->links() }}
    </div>
@endsection
