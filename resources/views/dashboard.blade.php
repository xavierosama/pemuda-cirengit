@extends('layouts.admin')

@section('title', 'Dashboard - Pemuda Cirengit')
@section('section', 'Ringkasan')
@section('page-title', 'Dashboard')
@section('breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard'],
    ]" />
@endsection

@section('content')
    @php
        $statusLabels = ['scheduled' => 'Terjadwal', 'completed' => 'Selesai', 'holiday' => 'Libur', 'postponed' => 'Ditunda', 'relocated' => 'Pindah Lokasi', 'cancelled' => 'Dibatalkan'];
        $summaryCards = [
            ['label' => 'Total Anggota Aktif', 'value' => $statistics['active_members'], 'note' => 'Anggota berstatus aktif', 'color' => 'border-l-emerald-600'],
            ['label' => 'Anggota Belum Punya Akun', 'value' => $statistics['members_without_account'], 'note' => 'Anggota aktif tanpa akun login', 'color' => 'border-l-sky-600'],
            ['label' => 'Agenda Aktif', 'value' => $statistics['active_agenda_schedules'], 'note' => 'Jadwal agenda berjalan', 'color' => 'border-l-cyan-600'],
            ['label' => 'Kegiatan Bulan Ini', 'value' => $statistics['monthly_activities'], 'note' => 'Periode '.now()->format('m/Y'), 'color' => 'border-l-violet-600'],
            ['label' => 'Presensi Perlu Verifikasi', 'value' => $statistics['need_verification_attendances'], 'note' => 'Menunggu keputusan admin', 'color' => 'border-l-amber-500'],
        ];
        $attendanceCards = [
            ['label' => 'Total Hadir', 'value' => $monthlyAttendanceSummary['present'], 'color' => 'text-emerald-700'],
            ['label' => 'Total Izin', 'value' => $monthlyAttendanceSummary['permission'], 'color' => 'text-sky-700'],
            ['label' => 'Total Tidak Hadir', 'value' => $monthlyAttendanceSummary['absent'], 'color' => 'text-slate-700'],
            ['label' => 'Total Perlu Verifikasi', 'value' => $monthlyAttendanceSummary['need_verification'], 'color' => 'text-amber-700'],
        ];
    @endphp

    <div class="space-y-6">
        <section>
            <div class="mb-4">
                <h2 class="text-xl font-bold text-slate-950">Ringkasan Utama</h2>
                <p class="mt-1 text-sm text-slate-500">Gambaran cepat kondisi administrasi Pemuda Cirengit.</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                @foreach ($summaryCards as $card)
                    <x-ui.card class="{{ $card['color'] }} border-l-4" padding="md">
                        <p class="text-sm font-medium text-slate-500">{{ $card['label'] }}</p>
                        <p class="mt-3 text-3xl font-bold text-slate-950">{{ number_format($card['value']) }}</p>
                        <p class="mt-2 text-xs font-medium text-slate-500">{{ $card['note'] }}</p>
                    </x-ui.card>
                @endforeach
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[minmax(0,1.4fr)_minmax(340px,0.6fr)]">
            <x-ui.card padding="none" class="overflow-hidden">
                <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-950">Kegiatan Terdekat</h2>
                        <p class="mt-1 text-sm text-slate-500">Maksimal lima kegiatan dari hari ini.</p>
                    </div>
                    <x-ui.button :href="route('activities.index')" variant="secondary" size="sm">Lihat semua kegiatan</x-ui.button>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                @foreach (['Nama Kegiatan', 'Tanggal', 'Waktu', 'Lokasi', 'Status', 'Aksi'] as $heading)
                                    <th class="{{ $heading === 'Aksi' ? 'text-right' : 'text-left' }} px-5 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">{{ $heading }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($upcomingActivities as $activity)
                                <tr>
                                    <td class="px-5 py-4 text-sm font-semibold text-slate-900">{{ $activity->title }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">{{ $activity->activity_date->format('d/m/Y') }}</td>
                                    <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">{{ $activity->start_time ? substr($activity->start_time, 0, 5) : '-' }}{{ $activity->end_time ? ' - '.substr($activity->end_time, 0, 5) : '' }}</td>
                                    <td class="max-w-64 px-5 py-4 text-sm text-slate-600">{{ str($activity->location ?: '-')->limit(55) }}</td>
                                    <td class="whitespace-nowrap px-5 py-4"><x-ui.status-badge :status="$activity->status" :label="$statusLabels[$activity->status]" /></td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right"><x-ui.action-icon :href="route('activities.show', $activity)" label="Detail" variant="detail" /></td>
                                </tr>
                            @empty
                                <tr><td colspan="6"><x-ui.empty-state title="Belum ada kegiatan terdekat." description="Kegiatan terdekat akan muncul setelah dibuat." /></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-ui.card>

            <x-ui.card>
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-950">Rekap Presensi Bulan Ini</h2>
                        <p class="mt-1 text-sm text-slate-500">Ringkasan status presensi periode {{ now()->format('m/Y') }}.</p>
                    </div>
                    <div class="rounded-lg bg-emerald-50 px-3 py-2 text-right">
                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Kehadiran</p>
                        <p class="text-xl font-bold text-emerald-800">{{ number_format($monthlyAttendanceSummary['attendance_percentage'], 2) }}%</p>
                    </div>
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-1 2xl:grid-cols-2">
                    @foreach ($attendanceCards as $card)
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                            <p class="text-sm font-medium text-slate-500">{{ $card['label'] }}</p>
                            <p class="{{ $card['color'] }} mt-2 text-2xl font-bold">{{ number_format($card['value']) }}</p>
                        </div>
                    @endforeach
                </div>

                <x-ui.button :href="route('attendance-reports.index')" class="mt-5 w-full">Buka Rekap Presensi</x-ui.button>
            </x-ui.card>
        </section>

        <section class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
            <x-ui.card padding="none" class="overflow-hidden">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-950">Presensi Perlu Verifikasi</h2>
                        <p class="mt-1 text-sm text-slate-500">Maksimal lima presensi terbaru yang menunggu keputusan admin.</p>
                    </div>
                    <x-ui.button :href="route('attendances.index')" variant="secondary" size="sm">Buka daftar hadir</x-ui.button>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse ($needVerificationAttendances as $attendance)
                        <a href="{{ $attendance->activity ? route('activities.attendances.index', $attendance->activity) : route('attendances.index') }}" class="block px-5 py-4 hover:bg-slate-50">
                            <div class="grid gap-3 lg:grid-cols-[1fr_180px_160px_120px] lg:items-center">
                                <div>
                                    <p class="text-sm font-semibold text-slate-900">{{ $attendance->member?->full_name ?? 'Anggota tidak tersedia' }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $attendance->activity?->title ?? 'Kegiatan tidak tersedia' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase text-slate-500">Kegiatan</p>
                                    <p class="mt-1 text-sm text-slate-700">{{ $attendance->activity?->activity_date?->format('d/m/Y') ?? '-' }}{{ $attendance->activity?->start_time ? ' '.substr($attendance->activity->start_time, 0, 5) : '' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase text-slate-500">Bidang</p>
                                    <p class="mt-1 text-sm text-slate-700">{{ $attendance->activity?->department?->name ?? '-' }}</p>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase text-slate-500">Jarak</p>
                                    <p class="mt-1 text-sm text-slate-700">{{ $attendance->distance_from_activity !== null ? number_format((float) $attendance->distance_from_activity, 2).' m' : '-' }}</p>
                                </div>
                            </div>
                        </a>
                    @empty
                        <x-ui.empty-state title="Tidak ada presensi yang perlu diverifikasi." description="Presensi yang butuh keputusan admin akan muncul di sini." />
                    @endforelse
                </div>
            </x-ui.card>

            <x-ui.card>
                <h2 class="text-base font-bold text-slate-950">Aksi Cepat</h2>
                <p class="mt-1 text-sm text-slate-500">Shortcut untuk pekerjaan administrasi harian.</p>
                <div class="mt-5 grid gap-3">
                    <x-ui.button :href="route('members.create')">Tambah Anggota</x-ui.button>
                    <x-ui.button :href="route('members.import')" variant="secondary">Import Anggota</x-ui.button>
                    <x-ui.button :href="route('agenda-schedules.create')" variant="secondary">Tambah Jadwal Agenda</x-ui.button>
                    <x-ui.button :href="route('activities.create')" variant="warning">Tambah Kegiatan</x-ui.button>
                    <x-ui.button :href="route('attendance-reports.index')" variant="secondary">Buka Rekap Presensi</x-ui.button>
                </div>
            </x-ui.card>
        </section>
    </div>
@endsection
