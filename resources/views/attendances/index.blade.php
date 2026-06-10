@extends('layouts.admin')

@section('title', 'Daftar Hadir - Pemuda Cirengit')
@section('section', 'Presensi')
@section('page-title', 'Daftar Hadir')

@section('content')
    @php
        $statusLabels = [
            'scheduled' => 'Terjadwal',
            'completed' => 'Selesai',
            'holiday' => 'Libur',
            'postponed' => 'Ditunda',
            'relocated' => 'Pindah Lokasi',
            'cancelled' => 'Dibatalkan',
        ];
        $statusClasses = [
            'scheduled' => 'bg-slate-100 text-slate-700 ring-slate-200',
            'completed' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'holiday' => 'bg-slate-100 text-slate-600 ring-slate-200',
            'postponed' => 'bg-amber-50 text-amber-700 ring-amber-200',
            'relocated' => 'bg-cyan-50 text-cyan-700 ring-cyan-200',
            'cancelled' => 'bg-red-50 text-red-700 ring-red-200',
        ];
        $summaryCards = [
            ['label' => 'Total Kegiatan dengan Presensi Aktif', 'value' => $attendanceStats['active_activities'], 'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-100'],
            ['label' => 'Total Presensi Bulan Ini', 'value' => $attendanceStats['monthly_total'], 'class' => 'bg-slate-50 text-slate-700 ring-slate-200'],
            ['label' => 'Total Hadir Bulan Ini', 'value' => $attendanceStats['monthly_present'], 'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-100'],
            ['label' => 'Total Izin Bulan Ini', 'value' => $attendanceStats['monthly_permission'], 'class' => 'bg-sky-50 text-sky-700 ring-sky-100'],
            ['label' => 'Total Tidak Hadir Bulan Ini', 'value' => $attendanceStats['monthly_absent'], 'class' => 'bg-slate-50 text-slate-700 ring-slate-200'],
            ['label' => 'Total Perlu Verifikasi', 'value' => $attendanceStats['need_verification'], 'class' => 'bg-amber-50 text-amber-700 ring-amber-100'],
        ];
    @endphp

    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">Presensi</p>
                    <h2 class="mt-2 text-2xl font-bold text-slate-950">Daftar Hadir</h2>
                    <p class="mt-2 max-w-2xl text-sm text-slate-500">Kelola daftar hadir kegiatan, status presensi, dan verifikasi kehadiran anggota.</p>
                </div>
                <a href="{{ route('activities.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">Buka Kegiatan Aktual</a>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
        @endif

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
            @foreach ($summaryCards as $card)
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="{{ $card['class'] }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ $card['label'] }}</div>
                    <p class="mt-4 text-3xl font-bold text-slate-950">{{ number_format($card['value']) }}</p>
                </div>
            @endforeach
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4">
                <h3 class="text-base font-bold text-slate-950">Filter Daftar Hadir</h3>
                <p class="mt-1 text-sm text-slate-500">Saring kegiatan berdasarkan nama, bidang, status kegiatan, status presensi, dan periode.</p>
            </div>
            <form method="GET" action="{{ route('attendances.index') }}" class="grid gap-4 lg:grid-cols-12">
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
                    <label for="activity_status" class="text-sm font-semibold text-slate-700">Status kegiatan</label>
                    <select id="activity_status" name="activity_status" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                        <option value="">Semua status</option>
                        @foreach ($activityStatuses as $statusValue)
                            <option value="{{ $statusValue }}" @selected($activityStatus === $statusValue)>{{ $statusLabels[$statusValue] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="lg:col-span-2">
                    <label for="attendance_enabled" class="text-sm font-semibold text-slate-700">Status presensi</label>
                    <select id="attendance_enabled" name="attendance_enabled" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                        <option value="">Semua</option>
                        <option value="1" @selected($attendanceStatus === '1')>Aktif</option>
                        <option value="0" @selected($attendanceStatus === '0')>Tidak Aktif</option>
                    </select>
                </div>
                <div class="lg:col-span-1">
                    <label for="start_date" class="text-sm font-semibold text-slate-700">Tanggal mulai</label>
                    <input id="start_date" name="start_date" type="date" value="{{ $startDate }}" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                </div>
                <div class="lg:col-span-1">
                    <label for="end_date" class="text-sm font-semibold text-slate-700">Tanggal akhir</label>
                    <input id="end_date" name="end_date" type="date" value="{{ $endDate }}" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                </div>
                <div class="flex gap-2 lg:col-span-12 lg:justify-end">
                    <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700 sm:flex-none">Terapkan Filter</button>
                    <a href="{{ route('attendances.index') }}" class="inline-flex flex-1 items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 sm:flex-none">Reset</a>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h3 class="text-base font-bold text-slate-950">Tabel Daftar Hadir</h3>
                <p class="mt-1 text-sm text-slate-500">Ringkasan presensi per kegiatan. Gunakan scroll horizontal pada layar kecil.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            @foreach (['No', 'Nama Kegiatan', 'Tanggal', 'Waktu', 'Bidang', 'Lokasi', 'Status Kegiatan', 'Status Presensi', 'Total Hadir', 'Total Izin', 'Total Tidak Hadir', 'Perlu Verifikasi'] as $heading)
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
                                <td class="px-4 py-4"><p class="whitespace-nowrap text-sm font-semibold text-slate-900">{{ $activity->title }}</p><p class="mt-1 whitespace-nowrap text-xs text-slate-500">{{ $activity->pic?->full_name ? 'PIC: '.$activity->pic->full_name : 'Tanpa PIC' }}</p></td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">{{ $activity->activity_date->format('d/m/Y') }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">{{ $time !== '' ? $time : '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">{{ $activity->department?->name ?? '-' }}</td>
                                <td class="max-w-56 px-4 py-4 text-sm text-slate-600">{{ str($activity->location ?: '-')->limit(45) }}</td>
                                <td class="whitespace-nowrap px-4 py-4"><span class="{{ $statusClasses[$activity->status] }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ $statusLabels[$activity->status] }}</span></td>
                                <td class="whitespace-nowrap px-4 py-4"><span class="{{ $activity->attendance_enabled ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-slate-100 text-slate-600 ring-slate-200' }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ $activity->attendance_enabled ? 'Aktif' : 'Tidak Aktif' }}</span></td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm font-semibold text-emerald-700">{{ number_format($activity->present_count) }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm font-semibold text-sky-700">{{ number_format($activity->permission_count) }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm font-semibold text-slate-700">{{ number_format($activity->absent_count) }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm font-semibold text-amber-700">{{ number_format($activity->need_verification_count) }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-semibold">
                                    <div class="flex justify-end gap-1.5">
                                        <x-action-icon :href="route('activities.show', $activity)" label="Detail Kegiatan" icon="eye" variant="blue" />
                                        <x-action-icon :href="route('activities.attendances.index', $activity)" label="Buka Daftar Hadir" icon="check" variant="emerald" />
                                        @if ($activity->attendance_enabled)
                                            <x-action-icon :href="route('activities.attendance-qr', $activity)" label="QR Presensi" icon="qr" variant="cyan" />
                                        @endif
                                        <x-action-icon :action="route('activities.attendances.sync-participants', $activity)" label="Sinkronkan Peserta" icon="user-plus" variant="indigo" confirm="Sinkronkan peserta presensi kegiatan ini?" />
                                        <x-action-icon :href="route('activities.attendances.export', $activity)" label="Export Excel" icon="download" variant="slate" />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13" class="px-4 py-14 text-center">
                                    <p class="text-base font-semibold text-slate-800">Belum ada kegiatan presensi.</p>
                                    <p class="mt-1 text-sm text-slate-500">Tambahkan kegiatan aktual atau ubah filter pencarian.</p>
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
