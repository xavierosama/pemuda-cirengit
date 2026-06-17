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
            ['label' => 'Total Kegiatan dengan Presensi Terjadwal', 'value' => $attendanceStats['active_activities'], 'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-100'],
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
                <p class="mt-1 text-sm text-slate-500">Saring kegiatan berdasarkan nama, bidang, status kegiatan, ketersediaan presensi, dan periode.</p>
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
                        <option value="1" @selected($attendanceStatus === '1')>Terjadwal</option>
                        <option value="0" @selected($attendanceStatus === '0')>Tidak tersedia</option>
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
                    <p class="mt-1 text-sm text-slate-500">Ringkasan presensi per kegiatan dalam tampilan compact.</p>
                </div>
                <x-per-page-selector :per-page="$perPage" :options="$perPageOptions" :query="$queryParams" />
            </div>
            <div class="divide-y divide-slate-100 md:hidden">
                @forelse ($activities as $activity)
                    @php
                        $attendanceAvailability = $activity->attendanceAvailability();
                        $summaryBadges = [
                            ['short' => 'H', 'label' => 'Hadir', 'value' => $activity->present_count, 'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-200'],
                            ['short' => 'TH', 'label' => 'Tidak Hadir', 'value' => $activity->absent_count, 'class' => 'bg-slate-100 text-slate-700 ring-slate-200'],
                            ['short' => 'I', 'label' => 'Izin', 'value' => $activity->permission_count, 'class' => 'bg-sky-50 text-sky-700 ring-sky-200'],
                            ['short' => 'V', 'label' => 'Perlu Verifikasi', 'value' => $activity->need_verification_count, 'class' => 'bg-amber-50 text-amber-700 ring-amber-200'],
                        ];
                    @endphp
                    <article class="px-4 py-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-100 text-sm font-bold text-slate-700">{{ $activities->firstItem() + $loop->index }}</div>
                            <x-activity-summary :activity="$activity" class="flex-1" />
                        </div>
                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <x-ui.status-badge :status="$attendanceAvailability" :label="$activity->attendanceAvailabilityLabel()" />
                            @foreach ($summaryBadges as $badge)
                                <span title="{{ $badge['label'] }}" class="{{ $badge['class'] }} inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-bold ring-1 ring-inset">
                                    <span>{{ $badge['short'] }}</span>
                                    <span>{{ number_format($badge['value']) }}</span>
                                </span>
                            @endforeach
                        </div>
                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <a href="{{ route('activities.show', $activity) }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Detail</a>
                            <a href="{{ route('activities.attendances.index', $activity) }}" class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Daftar Hadir</a>
                        </div>
                    </article>
                @empty
                    <x-ui.empty-state title="Belum ada daftar hadir." description="Buat atau generate kegiatan terlebih dahulu, lalu daftar hadir akan tersinkron otomatis." />
                @endforelse
            </div>
            <div class="hidden overflow-x-auto md:block">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="w-14 px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">No</th>
                            <x-sortable-th field="title" label="Kegiatan" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <x-sortable-th field="activity_date" label="Tanggal/Jam" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <x-sortable-th field="attendance_enabled" label="Status Presensi" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Rekap H/TH/I/V</th>
                            <th class="w-24 px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($activities as $activity)
                            @php
                                $time = trim(($activity->start_time ? substr($activity->start_time, 0, 5) : '').($activity->end_time ? ' - '.substr($activity->end_time, 0, 5) : ''));
                                $attendanceAvailability = $activity->attendanceAvailability();
                                $summaryBadges = [
                                    ['short' => 'H', 'label' => 'Hadir', 'value' => $activity->present_count, 'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-200'],
                                    ['short' => 'TH', 'label' => 'Tidak Hadir', 'value' => $activity->absent_count, 'class' => 'bg-slate-100 text-slate-700 ring-slate-200'],
                                    ['short' => 'I', 'label' => 'Izin', 'value' => $activity->permission_count, 'class' => 'bg-sky-50 text-sky-700 ring-sky-200'],
                                    ['short' => 'V', 'label' => 'Perlu Verifikasi', 'value' => $activity->need_verification_count, 'class' => 'bg-amber-50 text-amber-700 ring-amber-200'],
                                ];
                            @endphp
                            <tr class="align-top transition hover:bg-slate-50/70">
                                <td class="px-3 py-4 text-sm text-slate-500">{{ $activities->firstItem() + $loop->index }}</td>
                                <td class="max-w-md px-3 py-4">
                                    <x-activity-summary :activity="$activity" :show-meta="false" />
                                </td>
                                <td class="px-3 py-4 text-sm text-slate-600">
                                    <span class="block font-medium text-slate-800">{{ $activity->activity_date->format('d/m/Y') }}</span>
                                    <span class="mt-1 block text-xs text-slate-500">{{ $time !== '' ? $time : '-' }}</span>
                                </td>
                                <td class="px-3 py-4"><x-ui.status-badge :status="$attendanceAvailability" :label="$activity->attendanceAvailabilityLabel()" /></td>
                                <td class="px-3 py-4">
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach ($summaryBadges as $badge)
                                            <span title="{{ $badge['label'] }}" class="{{ $badge['class'] }} inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-bold ring-1 ring-inset">
                                                <span>{{ $badge['short'] }}</span>
                                                <span>{{ number_format($badge['value']) }}</span>
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-3 py-4 text-right text-sm font-semibold">
                                    <div class="flex justify-end gap-1.5">
                                        <x-ui.action-icon :href="route('activities.show', $activity)" label="Detail Kegiatan" variant="detail" />
                                        <x-ui.action-dropdown>
                                            <x-ui.action-dropdown-item :href="route('activities.attendances.index', $activity)" label="Buka Daftar Hadir" icon="check" />
                                            @if ($attendanceAvailability !== 'not_available')
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
                                                loading-text="Menyinkronkan..."
                                                confirm-variant="warning"
                                            />
                                            <x-ui.action-dropdown-item :href="route('activities.attendances.export', $activity)" label="Export Excel" icon="download" />
                                        </x-ui.action-dropdown>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <x-ui.empty-state title="Belum ada daftar hadir." description="Buat atau generate kegiatan terlebih dahulu, lalu daftar hadir akan tersinkron otomatis." />
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
