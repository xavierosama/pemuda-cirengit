@extends('layouts.admin')

@section('title', 'Dashboard - Pemuda Cirengit')
@section('section', 'Ringkasan')
@section('page-title', 'Dashboard')

@section('content')
    @php
        $statusLabels = ['scheduled' => 'Terjadwal', 'completed' => 'Selesai', 'holiday' => 'Libur', 'postponed' => 'Ditunda', 'relocated' => 'Pindah Lokasi', 'cancelled' => 'Dibatalkan'];
        $statusClasses = [
            'scheduled' => 'bg-sky-50 text-sky-700 ring-sky-200',
            'completed' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'holiday' => 'bg-slate-100 text-slate-600 ring-slate-200',
            'postponed' => 'bg-amber-50 text-amber-700 ring-amber-200',
            'relocated' => 'bg-cyan-50 text-cyan-700 ring-cyan-200',
            'cancelled' => 'bg-red-50 text-red-700 ring-red-200',
        ];
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
                    <div class="{{ $card['color'] }} rounded-lg border border-slate-200 border-l-4 bg-white p-5 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">{{ $card['label'] }}</p>
                        <p class="mt-3 text-3xl font-bold text-slate-950">{{ number_format($card['value']) }}</p>
                        <p class="mt-2 text-xs font-medium text-slate-500">{{ $card['note'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[minmax(0,1.4fr)_minmax(340px,0.6fr)]">
            <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-base font-bold text-slate-950">Kegiatan Terdekat</h2>
                        <p class="mt-1 text-sm text-slate-500">Maksimal lima kegiatan dari hari ini.</p>
                    </div>
                    <a href="{{ route('activities.index') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">Lihat semua kegiatan</a>
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
                                    <td class="whitespace-nowrap px-5 py-4"><span class="{{ $statusClasses[$activity->status] }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ $statusLabels[$activity->status] }}</span></td>
                                    <td class="whitespace-nowrap px-5 py-4 text-right"><x-action-icon :href="route('activities.show', $activity)" label="Detail" icon="eye" variant="blue" /></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">Belum ada kegiatan terdekat.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
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

                <a href="{{ route('attendance-reports.index') }}" class="mt-5 inline-flex w-full items-center justify-center rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Buka Rekap Presensi</a>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
            <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-950">Presensi Perlu Verifikasi</h2>
                        <p class="mt-1 text-sm text-slate-500">Maksimal lima presensi terbaru yang menunggu keputusan admin.</p>
                    </div>
                    <a href="{{ route('attendances.index') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">Buka daftar hadir</a>
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
                        <div class="px-5 py-10 text-center text-sm text-slate-500">Tidak ada presensi yang perlu diverifikasi.</div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-base font-bold text-slate-950">Aksi Cepat</h2>
                <p class="mt-1 text-sm text-slate-500">Shortcut untuk pekerjaan administrasi harian.</p>
                <div class="mt-5 grid gap-3">
                    <a href="{{ route('members.create') }}" class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800">Tambah Anggota</a>
                    <a href="{{ route('members.import') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Import Anggota</a>
                    <a href="{{ route('agenda-schedules.create') }}" class="inline-flex items-center justify-center rounded-lg border border-cyan-300 px-4 py-2.5 text-sm font-semibold text-cyan-700 hover:bg-cyan-50">Tambah Jadwal Agenda</a>
                    <a href="{{ route('activities.create') }}" class="inline-flex items-center justify-center rounded-lg border border-amber-300 px-4 py-2.5 text-sm font-semibold text-amber-700 hover:bg-amber-50">Tambah Kegiatan</a>
                    <a href="{{ route('attendance-reports.index') }}" class="inline-flex items-center justify-center rounded-lg border border-emerald-600 px-4 py-2.5 text-sm font-semibold text-emerald-700 hover:bg-emerald-50">Buka Rekap Presensi</a>
                </div>
            </div>
        </section>
    </div>
@endsection
