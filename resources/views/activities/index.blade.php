@extends('layouts.admin')

@section('title', 'Kegiatan Aktual - Pemuda Cirengit')
@section('section', 'Kegiatan')
@section('page-title', 'Kegiatan Aktual')

@section('content')
    @php
        $statusLabels = ['scheduled' => 'Terjadwal', 'completed' => 'Selesai', 'holiday' => 'Libur', 'postponed' => 'Ditunda', 'relocated' => 'Pindah Lokasi', 'cancelled' => 'Dibatalkan'];
        $statusClasses = [
            'scheduled' => 'bg-sky-50 text-sky-700 ring-sky-200',
            'completed' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'holiday' => 'bg-slate-100 text-slate-600 ring-slate-200',
            'postponed' => 'bg-amber-50 text-amber-700 ring-amber-200',
            'relocated' => 'bg-violet-50 text-violet-700 ring-violet-200',
            'cancelled' => 'bg-red-50 text-red-700 ring-red-200',
        ];
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div><h2 class="text-xl font-bold text-slate-950">Kegiatan Aktual</h2><p class="mt-1 text-sm text-slate-500">Kelola pelaksanaan kegiatan dan pengaturan presensi.</p></div>
            <a href="{{ route('activities.create') }}" class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Tambah Kegiatan</a>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
        @endif

        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <form method="GET" action="{{ route('activities.index') }}" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-[minmax(200px,1fr)_160px_180px_170px_200px_auto]">
                <input name="search" type="search" value="{{ $search }}" placeholder="Cari nama kegiatan" aria-label="Cari kegiatan" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                <input name="activity_date" type="date" value="{{ $activityDate }}" aria-label="Filter tanggal" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                <select name="department_id" aria-label="Filter bidang" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                    <option value="">Semua bidang</option>
                    @foreach ($departments as $department)<option value="{{ $department->id }}" @selected((string) $departmentId === (string) $department->id)>{{ $department->name }}</option>@endforeach
                </select>
                <select name="status" aria-label="Filter status" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                    <option value="">Semua status</option>
                    @foreach ($statuses as $statusValue)<option value="{{ $statusValue }}" @selected($status === $statusValue)>{{ $statusLabels[$statusValue] }}</option>@endforeach
                </select>
                <select name="agenda_schedule_id" aria-label="Filter jadwal agenda" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                    <option value="">Semua jadwal agenda</option>
                    @foreach ($agendaSchedules as $agendaSchedule)<option value="{{ $agendaSchedule->id }}" @selected((string) $agendaScheduleId === (string) $agendaSchedule->id)>{{ $agendaSchedule->title }}</option>@endforeach
                </select>
                <div class="flex gap-2 sm:col-span-2 xl:col-span-1">
                    <button type="submit" class="flex-1 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Filter</button>
                    <a href="{{ route('activities.index') }}" class="flex flex-1 items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Reset</a>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50"><tr>
                        @foreach (['Kegiatan', 'Tanggal', 'Waktu', 'Lokasi', 'Bidang', 'Status', 'Presensi'] as $heading)<th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">{{ $heading }}</th>@endforeach
                        <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">Aksi</th>
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($activities as $activity)
                            <tr>
                                <td class="px-4 py-4"><p class="text-sm font-semibold text-slate-900">{{ $activity->title }}</p><p class="mt-1 text-xs text-slate-500">{{ $activity->agendaSchedule?->title ?? 'Kegiatan mandiri' }}</p></td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">{{ $activity->activity_date->format('d/m/Y') }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">{{ $activity->start_time ? substr($activity->start_time, 0, 5) : '-' }}{{ $activity->end_time ? ' - '.substr($activity->end_time, 0, 5) : '' }}</td>
                                <td class="max-w-48 px-4 py-4 text-sm text-slate-600">{{ str($activity->location ?: '-')->limit(45) }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">{{ $activity->department?->name ?? '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-4"><span class="{{ $statusClasses[$activity->status] }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ $statusLabels[$activity->status] }}</span></td>
                                <td class="whitespace-nowrap px-4 py-4"><span class="{{ $activity->attendance_enabled ? 'text-emerald-700' : 'text-slate-500' }} text-sm font-semibold">{{ $activity->attendance_enabled ? 'Aktif' : 'Tidak aktif' }}</span></td>
                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-semibold"><div class="flex justify-end gap-2">
                                    <x-action-icon :href="route('activities.show', $activity)" label="Detail" icon="eye" variant="blue" />
                                    <x-action-icon :href="route('activities.edit', $activity)" label="Edit" icon="pencil" variant="amber" />
                                    <x-action-icon :action="route('activities.destroy', $activity)" method="DELETE" label="Hapus" icon="trash" variant="red" confirm="Yakin ingin menghapus data ini?" />
                                </div></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-10 text-center text-sm text-slate-500">Belum ada kegiatan aktual.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $activities->links() }}
    </div>
@endsection
