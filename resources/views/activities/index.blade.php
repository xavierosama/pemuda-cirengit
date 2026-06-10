@extends('layouts.admin')

@section('title', 'Kegiatan Aktual - Pemuda Cirengit')
@section('section', 'Kegiatan')
@section('page-title', 'Kegiatan Aktual')

@section('content')
    @php
        $statusLabels = ['scheduled' => 'Terjadwal', 'completed' => 'Selesai', 'holiday' => 'Libur', 'postponed' => 'Ditunda', 'relocated' => 'Pindah Lokasi', 'cancelled' => 'Dibatalkan'];
        $statusClasses = [
            'scheduled' => 'bg-slate-100 text-slate-700 ring-slate-200',
            'completed' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'holiday' => 'bg-slate-100 text-slate-600 ring-slate-200',
            'postponed' => 'bg-amber-50 text-amber-700 ring-amber-200',
            'relocated' => 'bg-cyan-50 text-cyan-700 ring-cyan-200',
            'cancelled' => 'bg-red-50 text-red-700 ring-red-200',
        ];
        $summaryCards = [
            ['label' => 'Kegiatan Bulan Ini', 'value' => $activityStats['current_month'], 'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-100'],
            ['label' => 'Kegiatan Terjadwal', 'value' => $activityStats['scheduled'], 'class' => 'bg-slate-50 text-slate-700 ring-slate-200'],
            ['label' => 'Kegiatan Selesai', 'value' => $activityStats['completed'], 'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-100'],
            ['label' => 'Kegiatan Ditunda/Dibatalkan', 'value' => $activityStats['postponed_cancelled'], 'class' => 'bg-amber-50 text-amber-700 ring-amber-100'],
            ['label' => 'Presensi Aktif', 'value' => $activityStats['attendance_enabled'], 'class' => 'bg-cyan-50 text-cyan-700 ring-cyan-100'],
        ];
    @endphp

    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">Agenda & Kegiatan</p>
                    <h2 class="mt-2 text-2xl font-bold text-slate-950">Kegiatan Aktual</h2>
                    <p class="mt-2 max-w-2xl text-sm text-slate-500">Kelola kegiatan berjalan, perubahan jadwal, dan pengaturan presensi.</p>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap lg:justify-end">
                    <a href="{{ route('activities.create') }}" class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">Tambah Kegiatan</a>
                    <a href="{{ route('agenda-schedules.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">Generate dari Jadwal Agenda</a>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
        @endif

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            @foreach ($summaryCards as $card)
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="{{ $card['class'] }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ $card['label'] }}</div>
                    <p class="mt-4 text-3xl font-bold text-slate-950">{{ number_format($card['value']) }}</p>
                </div>
            @endforeach
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4">
                <h3 class="text-base font-bold text-slate-950">Filter Kegiatan Aktual</h3>
                <p class="mt-1 text-sm text-slate-500">Saring kegiatan berdasarkan nama, bidang, status, periode, dan status presensi.</p>
            </div>
            <form method="GET" action="{{ route('activities.index') }}" class="grid gap-4 lg:grid-cols-12">
                <div class="lg:col-span-3">
                    <label for="search" class="text-sm font-semibold text-slate-700">Search nama kegiatan</label>
                    <input id="search" name="search" type="search" value="{{ $search }}" placeholder="Cari nama kegiatan" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                </div>
                <div class="lg:col-span-2">
                    <label for="department_id" class="text-sm font-semibold text-slate-700">Bidang</label>
                    <select id="department_id" name="department_id" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                        <option value="">Semua bidang</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected((string) $departmentId === (string) $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="lg:col-span-2">
                    <label for="status" class="text-sm font-semibold text-slate-700">Status kegiatan</label>
                    <select id="status" name="status" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                        <option value="">Semua status</option>
                        @foreach ($statuses as $statusValue)
                            <option value="{{ $statusValue }}" @selected($status === $statusValue)>{{ $statusLabels[$statusValue] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="lg:col-span-2">
                    <label for="start_date" class="text-sm font-semibold text-slate-700">Tanggal mulai</label>
                    <input id="start_date" name="start_date" type="date" value="{{ $startDate ?: $activityDate }}" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                </div>
                <div class="lg:col-span-2">
                    <label for="end_date" class="text-sm font-semibold text-slate-700">Tanggal akhir</label>
                    <input id="end_date" name="end_date" type="date" value="{{ $endDate ?: $activityDate }}" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                </div>
                <div class="lg:col-span-1">
                    <label for="attendance_enabled" class="text-sm font-semibold text-slate-700">Presensi</label>
                    <select id="attendance_enabled" name="attendance_enabled" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                        <option value="">Semua</option>
                        <option value="1" @selected($attendanceStatus === '1')>Aktif</option>
                        <option value="0" @selected($attendanceStatus === '0')>Tidak Aktif</option>
                    </select>
                </div>
                <div class="flex gap-2 lg:col-span-12 lg:justify-end">
                    <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700 sm:flex-none">Terapkan Filter</button>
                    <a href="{{ route('activities.index') }}" class="inline-flex flex-1 items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 sm:flex-none">Reset</a>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h3 class="text-base font-bold text-slate-950">Tabel Kegiatan Aktual</h3>
                <p class="mt-1 text-sm text-slate-500">Daftar kegiatan sesuai filter aktif. Gunakan scroll horizontal pada layar kecil.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            @foreach (['No', 'Nama Kegiatan', 'Tanggal', 'Waktu', 'Bidang', 'PIC', 'Lokasi', 'Status Kegiatan', 'Status Presensi'] as $heading)
                                <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">{{ $heading }}</th>
                            @endforeach
                            <th class="whitespace-nowrap px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($activities as $activity)
                            @php
                                $time = trim(($activity->start_time ? substr($activity->start_time, 0, 5) : '').($activity->end_time ? ' - '.substr($activity->end_time, 0, 5) : ''));
                            @endphp
                            <tr class="transition hover:bg-slate-50/70">
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-500">{{ $activities->firstItem() + $loop->index }}</td>
                                <td class="px-4 py-4"><p class="whitespace-nowrap text-sm font-semibold text-slate-900">{{ $activity->title }}</p><p class="mt-1 whitespace-nowrap text-xs text-slate-500">{{ $activity->agendaSchedule?->title ?? 'Kegiatan mandiri' }}</p></td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">{{ $activity->activity_date->format('d/m/Y') }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">{{ $time !== '' ? $time : '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">{{ $activity->department?->name ?? '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">{{ $activity->pic?->full_name ?? '-' }}</td>
                                <td class="max-w-56 px-4 py-4 text-sm text-slate-600">{{ str($activity->location ?: '-')->limit(45) }}</td>
                                <td class="whitespace-nowrap px-4 py-4"><span class="{{ $statusClasses[$activity->status] }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ $statusLabels[$activity->status] }}</span></td>
                                <td class="whitespace-nowrap px-4 py-4"><span class="{{ $activity->attendance_enabled ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-slate-100 text-slate-600 ring-slate-200' }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ $activity->attendance_enabled ? 'Aktif' : 'Tidak Aktif' }}</span></td>
                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-semibold">
                                    <div class="flex justify-end gap-1.5">
                                        <x-action-icon :href="route('activities.show', $activity)" label="Detail" icon="eye" variant="blue" />
                                        <x-action-icon :href="route('activities.edit', $activity)" label="Edit" icon="pencil" variant="amber" />
                                        <x-action-icon :href="route('activities.attendances.index', $activity)" label="Daftar Hadir" icon="check" variant="emerald" />
                                        @if ($activity->attendance_enabled)
                                            <x-action-icon :href="route('activities.attendance-qr', $activity)" label="QR Presensi" icon="qr" variant="cyan" />
                                        @endif
                                        <x-action-icon :action="route('activities.destroy', $activity)" method="DELETE" label="Hapus" icon="trash" variant="red" confirm="Yakin ingin menghapus data ini?" />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-14 text-center">
                                    <p class="text-base font-semibold text-slate-800">Belum ada kegiatan aktual.</p>
                                    <p class="mt-1 text-sm text-slate-500">Tambahkan kegiatan baru atau ubah filter pencarian.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $activities->links() }}
    </div>
@endsection
