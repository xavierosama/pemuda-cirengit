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
        $commandCards = [
            ['label' => 'Kegiatan Hari Ini', 'value' => $todayActivities->count(), 'note' => 'Agenda pada '.now()->format('d/m/Y'), 'color' => 'border-l-emerald-600'],
            ['label' => 'Presensi Dibuka', 'value' => $openAttendanceActivities->count(), 'note' => 'Butuh pemantauan langsung', 'color' => 'border-l-sky-600'],
            ['label' => 'Perlu Finalisasi', 'value' => $needFinalizationActivities->count(), 'note' => 'Kegiatan lewat belum selesai', 'color' => 'border-l-amber-500'],
        ];
        $dayLabels = [0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'];
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

        <section>
            <div class="mb-4">
                <h2 class="text-xl font-bold text-slate-950">Command Center</h2>
                <p class="mt-1 text-sm text-slate-500">Sinyal operasional yang perlu dilihat pengurus hari ini.</p>
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                @foreach ($commandCards as $card)
                    <x-ui.card class="{{ $card['color'] }} border-l-4" padding="md">
                        <p class="text-sm font-medium text-slate-500">{{ $card['label'] }}</p>
                        <p class="mt-3 text-3xl font-bold text-slate-950">{{ number_format($card['value']) }}</p>
                        <p class="mt-2 text-xs font-medium text-slate-500">{{ $card['note'] }}</p>
                    </x-ui.card>
                @endforeach
            </div>

            <div class="mt-4 grid gap-4 xl:grid-cols-3">
                <x-ui.card padding="none" class="overflow-hidden">
                    <div class="border-b border-slate-200 px-5 py-4">
                        <h3 class="text-base font-bold text-slate-950">Hari Ini Ada Apa?</h3>
                        <p class="mt-1 text-sm text-slate-500">Kegiatan pada tanggal {{ now()->format('d/m/Y') }}.</p>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse ($todayActivities as $activity)
                            <article class="px-5 py-4">
                                <div class="flex items-start justify-between gap-3">
                                    <x-activity-summary :activity="$activity" />
                                    <x-ui.status-badge :status="$activity->attendanceAvailability()" :label="$activity->attendanceAvailabilityLabel()" />
                                </div>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <x-ui.button :href="route('activities.show', $activity)" variant="secondary" size="sm">Detail</x-ui.button>
                                    <x-ui.button :href="route('activities.attendances.index', $activity)" size="sm">Daftar Hadir</x-ui.button>
                                </div>
                            </article>
                        @empty
                            <x-ui.empty-state title="Tidak ada kegiatan hari ini." description="Kegiatan hari ini akan muncul di sini saat sudah dibuat." />
                        @endforelse
                    </div>
                </x-ui.card>

                <x-ui.card padding="none" class="overflow-hidden">
                    <div class="border-b border-slate-200 px-5 py-4">
                        <h3 class="text-base font-bold text-slate-950">Presensi Sedang Dibuka</h3>
                        <p class="mt-1 text-sm text-slate-500">Kegiatan yang sedang menerima hadir/izin.</p>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse ($openAttendanceActivities as $activity)
                            <article class="px-5 py-4">
                                <div class="flex items-start justify-between gap-3">
                                    <x-activity-summary :activity="$activity" />
                                    <x-ui.status-badge status="open" label="Dibuka" />
                                </div>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <x-ui.button :href="route('activities.attendance-qr', $activity)" size="sm">QR Presensi</x-ui.button>
                                    <x-ui.button :href="route('activities.attendances.index', $activity)" variant="secondary" size="sm">Daftar Hadir</x-ui.button>
                                </div>
                            </article>
                        @empty
                            <x-ui.empty-state title="Belum ada presensi yang sedang dibuka." description="Saat presensi masuk jam buka, kegiatan akan tampil di sini." />
                        @endforelse
                    </div>
                </x-ui.card>

                <x-ui.card padding="none" class="overflow-hidden">
                    <div class="border-b border-slate-200 px-5 py-4">
                        <h3 class="text-base font-bold text-slate-950">Perlu Finalisasi</h3>
                        <p class="mt-1 text-sm text-slate-500">Kegiatan lewat yang statusnya belum selesai.</p>
                    </div>
                    <div class="divide-y divide-slate-100">
                        @forelse ($needFinalizationActivities as $activity)
                            <article class="px-5 py-4">
                                <x-activity-summary :activity="$activity" />
                                <div class="mt-3 flex flex-wrap gap-2">
                                    <x-ui.button :href="route('activities.attendances.index', $activity)" variant="secondary" size="sm">Cek Daftar Hadir</x-ui.button>
                                    <div x-data="{ open: false, submitting: false }" x-on:confirmed="submitting = true; $refs.finalizeDashboardForm.submit()">
                                        <form x-ref="finalizeDashboardForm" method="POST" action="{{ route('activities.attendances.finalize', $activity) }}" x-on:submit.prevent="open = true">
                                            @csrf
                                            @method('PATCH')
                                            <x-ui.button type="submit" variant="warning" size="sm" loading-text="Memfinalisasi...">Finalisasi</x-ui.button>
                                        </form>
                                        <x-ui.confirm-modal
                                            title="Finalisasi Presensi?"
                                            description="Status kegiatan akan menjadi selesai. Member tidak bisa lagi melakukan hadir atau izin, tetapi admin tetap bisa mengoreksi daftar hadir."
                                            confirm-text="Finalisasi"
                                            loading-text="Memfinalisasi..."
                                            variant="warning"
                                        />
                                    </div>
                                </div>
                            </article>
                        @empty
                            <x-ui.empty-state title="Tidak ada kegiatan yang perlu difinalisasi." description="Kegiatan yang sudah lewat dan belum selesai akan muncul di sini." />
                        @endforelse
                    </div>
                </x-ui.card>
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

                <div class="divide-y divide-slate-100">
                    @forelse ($upcomingActivities as $activity)
                        @php
                            $subInfo = $activity->topic
                                ? 'Topik: '.$activity->topic
                                : ($activity->description ?: ($activity->location ?: $activity->agendaSchedule?->default_location));
                            $time = trim(($activity->start_time ? substr($activity->start_time, 0, 5) : '').($activity->end_time ? ' - '.substr($activity->end_time, 0, 5) : ''));
                            $dateLabel = ($dayLabels[$activity->activity_date->dayOfWeek] ?? '').', '.$activity->activity_date->format('d/m/Y');
                        @endphp
                        <article class="grid gap-4 px-5 py-4 transition hover:bg-slate-50 sm:grid-cols-[auto_minmax(0,1fr)_auto] sm:items-start">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-emerald-50 text-sm font-bold text-emerald-700 ring-1 ring-inset ring-emerald-200">
                                {{ $loop->iteration }}
                            </div>
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="line-clamp-1 break-words text-sm font-bold text-slate-950">{{ $activity->title }}</h3>
                                    <x-ui.status-badge :status="$activity->status" :label="$statusLabels[$activity->status]" />
                                    <x-ui.status-badge :status="$activity->attendanceAvailability()" :label="$activity->attendanceAvailabilityLabel()" />
                                </div>
                                @if ($subInfo)
                                    <p class="mt-2 line-clamp-2 break-words text-sm text-slate-600">{{ $subInfo }}</p>
                                @endif
                                <p class="mt-2 text-sm font-medium text-slate-700">{{ $dateLabel }} <span class="text-slate-400">&bull;</span> {{ $time !== '' ? $time : '-' }}</p>
                                <p class="mt-1 line-clamp-1 text-xs text-slate-500">{{ $activity->location ?: '-' }}{{ $activity->department?->name ? ' - '.$activity->department->name : '' }}</p>
                            </div>
                            <div class="flex flex-wrap gap-2 sm:justify-end">
                                <x-ui.button :href="route('activities.show', $activity)" variant="secondary" size="sm">Detail</x-ui.button>
                                <x-ui.button :href="route('activities.attendances.index', $activity)" size="sm">Daftar Hadir</x-ui.button>
                            </div>
                        </article>
                    @empty
                        <x-ui.empty-state title="Belum ada kegiatan terdekat." description="Kegiatan terdekat akan muncul setelah dibuat." />
                    @endforelse
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
