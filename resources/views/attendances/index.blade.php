@extends('layouts.admin')

@section('title', 'Daftar Hadir - Pemuda Cirengit')
@section('section', 'Presensi')
@section('page-title', 'Daftar Hadir')
@section('breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Daftar Hadir'],
    ]" />
@endsection

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
        <x-ui.page-header
            title="Daftar Hadir"
            eyebrow="Presensi"
            description="Kelola daftar hadir kegiatan, status presensi, dan verifikasi kehadiran anggota."
        >
            <x-slot name="action">
                <x-ui.button :href="route('activities.index')" variant="secondary">Buka Kegiatan Aktual</x-ui.button>
            </x-slot>
        </x-ui.page-header>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
            @foreach ($summaryCards as $card)
                <x-ui.card padding="sm">
                    <div class="{{ $card['class'] }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ $card['label'] }}</div>
                    <p class="mt-4 text-3xl font-bold text-slate-950">{{ number_format($card['value']) }}</p>
                </x-ui.card>
            @endforeach
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4">
                <h3 class="text-base font-bold text-slate-950">Filter Daftar Hadir</h3>
                <p class="mt-1 text-sm text-slate-500">Saring kegiatan berdasarkan nama, bidang, status kegiatan, status presensi, dan periode.</p>
            </div>
            <form method="GET" action="{{ route('attendances.index') }}" class="grid gap-4 lg:grid-cols-12">
                <input type="hidden" name="sort" value="{{ $currentSort }}">
                <input type="hidden" name="direction" value="{{ $currentDirection }}">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
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
            <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-950">Tabel Daftar Hadir</h3>
                    <p class="mt-1 text-sm text-slate-500">Ringkasan presensi per kegiatan. Gunakan scroll horizontal pada layar kecil.</p>
                </div>
                <x-per-page-selector :per-page="$perPage" :options="$perPageOptions" :query="$queryParams" />
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">No</th>
                            <x-sortable-th field="title" label="Nama Kegiatan" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <x-sortable-th field="activity_date" label="Tanggal" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <x-sortable-th field="start_time" label="Waktu" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Bidang</th>
                            <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Lokasi</th>
                            <x-sortable-th field="status" label="Status Kegiatan" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <x-sortable-th field="attendance_enabled" label="Status Presensi" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Total Hadir</th>
                            <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Total Izin</th>
                            <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Total Tidak Hadir</th>
                            <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Perlu Verifikasi</th>
                            <th class="whitespace-nowrap px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($activities as $activity)
                            @php
                                $time = trim(($activity->start_time ? substr($activity->start_time, 0, 5) : '').($activity->end_time ? ' - '.substr($activity->end_time, 0, 5) : ''));
                            @endphp
                            <tr class="align-top transition hover:bg-slate-50/70">
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-500">{{ $activities->firstItem() + $loop->index }}</td>
                                <td class="max-w-56 px-3 py-4"><p class="line-clamp-2 break-words text-sm font-semibold text-slate-900">{{ $activity->title }}</p><p class="mt-1 line-clamp-1 break-words text-xs text-slate-500">{{ $activity->pic?->full_name ? 'PIC: '.$activity->pic->full_name : 'Tanpa PIC' }}</p></td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-600">{{ $activity->activity_date->format('d/m/Y') }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-600">{{ $time !== '' ? $time : '-' }}</td>
                                <td class="max-w-32 px-3 py-4 text-sm text-slate-600"><span class="line-clamp-2 break-words">{{ $activity->department?->name ?? '-' }}</span></td>
                                <td class="max-w-44 px-3 py-4 text-sm text-slate-600"><span class="line-clamp-2 break-words">{{ $activity->location ?: '-' }}</span></td>
                                <td class="whitespace-nowrap px-3 py-4"><x-ui.status-badge :status="$activity->status" :label="$statusLabels[$activity->status]" /></td>
                                <td class="whitespace-nowrap px-3 py-4"><x-ui.status-badge :status="$activity->attendance_enabled ? 'active' : 'inactive'" :label="$activity->attendance_enabled ? 'Aktif' : 'Tidak Aktif'" /></td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm font-semibold text-emerald-700">{{ number_format($activity->present_count) }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm font-semibold text-sky-700">{{ number_format($activity->permission_count) }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm font-semibold text-slate-700">{{ number_format($activity->absent_count) }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm font-semibold text-amber-700">{{ number_format($activity->need_verification_count) }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-right text-sm font-semibold">
                                    <div class="flex justify-end gap-1.5">
                                        <x-ui.action-icon :href="route('activities.show', $activity)" label="Detail Kegiatan" variant="detail" />
                                        <x-ui.action-dropdown>
                                            <x-ui.action-dropdown-item :href="route('activities.attendances.index', $activity)" label="Buka Daftar Hadir" icon="check" />
                                            @if ($activity->attendance_enabled)
                                                <x-ui.action-dropdown-item :href="route('activities.attendance-qr', $activity)" label="QR Presensi" icon="qr" />
                                            @endif
                                            <x-ui.action-dropdown-item
                                                :action="route('activities.attendances.sync-participants', $activity)"
                                                label="Sinkronkan Peserta"
                                                icon="user-plus"
                                                variant="warning"
                                                confirm="Sinkronkan peserta presensi kegiatan ini?"
                                                confirm-title="Sinkronkan Peserta Presensi?"
                                                confirm-description="Peserta presensi kegiatan ini akan disesuaikan dengan data anggota aktif. Data presensi yang sudah tersimpan tetap mengikuti aturan sistem."
                                                confirm-text="Sinkronkan"
                                                confirm-variant="warning"
                                            />
                                            <x-ui.action-dropdown-item :href="route('activities.attendances.export', $activity)" label="Export Excel" icon="download" />
                                        </x-ui.action-dropdown>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="13">
                                    <x-ui.empty-state title="Belum ada kegiatan presensi." description="Tambahkan kegiatan aktual atau ubah filter pencarian." />
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
