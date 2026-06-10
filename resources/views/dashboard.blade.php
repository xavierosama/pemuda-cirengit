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
            'relocated' => 'bg-violet-50 text-violet-700 ring-violet-200',
            'cancelled' => 'bg-red-50 text-red-700 ring-red-200',
        ];
        $typeLabels = ['once' => 'Satu Kali', 'daily' => 'Harian', 'weekly' => 'Mingguan', 'monthly' => 'Bulanan'];
        $dayLabels = [0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'];
        $cards = [
            ['label' => 'Anggota Aktif', 'value' => $statistics['active_members'], 'note' => 'Anggota berstatus aktif', 'color' => 'border-l-emerald-600'],
            ['label' => 'Jadwal Aktif', 'value' => $statistics['active_agenda_schedules'], 'note' => 'Pola agenda yang berjalan', 'color' => 'border-l-sky-600'],
            ['label' => 'Kegiatan Bulan Ini', 'value' => $statistics['monthly_activities'], 'note' => 'Periode '.now()->format('m/Y'), 'color' => 'border-l-violet-600'],
            ['label' => 'Kegiatan Terjadwal', 'value' => $statistics['scheduled_activities'], 'note' => 'Menunggu pelaksanaan', 'color' => 'border-l-cyan-600'],
            ['label' => 'Kegiatan Diliburkan', 'value' => $statistics['holiday_activities'], 'note' => 'Berstatus libur', 'color' => 'border-l-amber-500'],
            ['label' => 'Presensi Bulan Ini', 'value' => $statistics['monthly_attendances'], 'note' => 'Catatan kehadiran', 'color' => 'border-l-rose-500'],
        ];
    @endphp

    <div class="space-y-6">
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-6">
            @foreach ($cards as $card)
                <div class="{{ $card['color'] }} min-w-0 border-l-4 bg-white p-5 shadow-sm ring-1 ring-slate-200">
                    <p class="text-sm font-medium text-slate-500">{{ $card['label'] }}</p>
                    <p class="mt-3 text-3xl font-bold text-slate-950">{{ number_format($card['value']) }}</p>
                    <p class="mt-2 text-xs font-medium text-slate-500">{{ $card['note'] }}</p>
                </div>
            @endforeach
        </section>

        <section class="overflow-hidden border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-950">Agenda Terdekat</h2>
                    <p class="mt-1 text-sm text-slate-500">Kegiatan hari ini dan yang akan datang.</p>
                </div>
                <a href="{{ route('activities.index') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">Lihat semua kegiatan</a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50"><tr>
                        @foreach (['Kegiatan', 'Tanggal', 'Waktu', 'Lokasi', 'Status'] as $heading)
                            <th class="px-5 py-3 text-left text-xs font-bold uppercase text-slate-500">{{ $heading }}</th>
                        @endforeach
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($upcomingActivities as $activity)
                            <tr>
                                <td class="px-5 py-4 text-sm font-semibold text-slate-900"><a href="{{ route('activities.show', $activity) }}" class="hover:text-emerald-700">{{ $activity->title }}</a></td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">{{ $activity->activity_date->format('d/m/Y') }}</td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">{{ $activity->start_time ? substr($activity->start_time, 0, 5) : '-' }}{{ $activity->end_time ? ' - '.substr($activity->end_time, 0, 5) : '' }}</td>
                                <td class="max-w-64 px-5 py-4 text-sm text-slate-600">{{ str($activity->location ?: '-')->limit(55) }}</td>
                                <td class="whitespace-nowrap px-5 py-4"><span class="{{ $statusClasses[$activity->status] }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ $statusLabels[$activity->status] }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-10 text-center text-sm text-slate-500">Belum ada kegiatan yang akan datang.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-5">
            <div class="overflow-hidden border border-slate-200 bg-white shadow-sm xl:col-span-3">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <div><h2 class="text-base font-bold text-slate-950">Rekap Presensi Terbaru</h2><p class="mt-1 text-sm text-slate-500">Lima kegiatan terbaru yang memiliki presensi.</p></div>
                    <a href="{{ route('attendances.index') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">Lihat rekap</a>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse ($recentAttendanceActivities as $activity)
                        <a href="{{ route('activities.attendances.index', $activity) }}" class="block px-5 py-4 hover:bg-slate-50">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0"><p class="truncate text-sm font-semibold text-slate-900">{{ $activity->title }}</p><p class="mt-1 text-xs text-slate-500">{{ $activity->activity_date->format('d/m/Y') }}</p></div>
                                <div class="grid grid-cols-4 gap-4 text-center">
                                    <div><p class="text-base font-bold text-emerald-700">{{ $activity->present_count }}</p><p class="text-xs text-slate-500">Hadir</p></div>
                                    <div><p class="text-base font-bold text-sky-700">{{ $activity->permission_count }}</p><p class="text-xs text-slate-500">Izin</p></div>
                                    <div><p class="text-base font-bold text-red-700">{{ $activity->absent_count }}</p><p class="text-xs text-slate-500">Tidak hadir</p></div>
                                    <div><p class="text-base font-bold text-amber-700">{{ $activity->need_verification_count }}</p><p class="text-xs text-slate-500">Verifikasi</p></div>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="px-5 py-10 text-center text-sm text-slate-500">Belum ada kegiatan dengan data presensi.</div>
                    @endforelse
                </div>
            </div>

            <div class="overflow-hidden border border-slate-200 bg-white shadow-sm xl:col-span-2">
                <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4">
                    <div><h2 class="text-base font-bold text-slate-950">Jadwal Agenda Aktif</h2><p class="mt-1 text-sm text-slate-500">Pola agenda yang sedang digunakan.</p></div>
                    <a href="{{ route('agenda-schedules.index') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">Lihat semua</a>
                </div>
                <div class="divide-y divide-slate-100">
                    @forelse ($activeAgendaSchedules as $agendaSchedule)
                        @php
                            $pattern = match ($agendaSchedule->schedule_type) {
                                'once' => $agendaSchedule->specific_date?->format('d/m/Y') ?? '-',
                                'daily' => 'Setiap hari',
                                'weekly' => isset($dayLabels[$agendaSchedule->day_of_week]) ? 'Setiap '.$dayLabels[$agendaSchedule->day_of_week] : 'Mingguan',
                                'monthly' => $agendaSchedule->day_of_month ? 'Setiap tanggal '.$agendaSchedule->day_of_month : 'Bulanan',
                            };
                        @endphp
                        <a href="{{ route('agenda-schedules.show', $agendaSchedule) }}" class="block px-5 py-4 hover:bg-slate-50">
                            <div class="flex items-start justify-between gap-4"><p class="text-sm font-semibold text-slate-900">{{ $agendaSchedule->title }}</p><span class="whitespace-nowrap text-xs font-semibold text-emerald-700">{{ $typeLabels[$agendaSchedule->schedule_type] }}</span></div>
                            <p class="mt-2 text-xs text-slate-500">{{ $pattern }}{{ $agendaSchedule->start_time ? ' - '.substr($agendaSchedule->start_time, 0, 5) : '' }}</p>
                            <p class="mt-1 truncate text-xs text-slate-500">{{ $agendaSchedule->department?->name ?? 'Tanpa bidang' }}{{ $agendaSchedule->pic ? ' - PIC: '.$agendaSchedule->pic->full_name : '' }}</p>
                        </a>
                    @empty
                        <div class="px-5 py-10 text-center text-sm text-slate-500">Belum ada jadwal agenda aktif.</div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
@endsection
