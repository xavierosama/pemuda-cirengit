@extends('layouts.admin')

@section('title', 'Detail Kegiatan Aktual - Pemuda Cirengit')
@section('section', 'Kegiatan')
@section('page-title', 'Detail Kegiatan Aktual')
@section('breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Kegiatan Aktual', 'url' => route('activities.index')],
        ['label' => 'Detail Kegiatan'],
    ]" />
@endsection

@section('content')
    @php
        $statusLabels = ['scheduled' => 'Terjadwal', 'completed' => 'Selesai', 'holiday' => 'Libur', 'postponed' => 'Ditunda', 'relocated' => 'Pindah Lokasi', 'cancelled' => 'Dibatalkan'];
        $statusClasses = ['scheduled' => 'bg-sky-50 text-sky-700 ring-sky-200', 'completed' => 'bg-emerald-50 text-emerald-700 ring-emerald-200', 'holiday' => 'bg-slate-100 text-slate-600 ring-slate-200', 'postponed' => 'bg-amber-50 text-amber-700 ring-amber-200', 'relocated' => 'bg-cyan-50 text-cyan-700 ring-cyan-200', 'cancelled' => 'bg-red-50 text-red-700 ring-red-200'];
        $attendanceUrl = $activity->attendance_token ? route('attendance.check-in.show', $activity->attendance_token, true) : null;
        $activityTime = trim(($activity->start_time ? substr($activity->start_time, 0, 5) : '').($activity->end_time ? ' - '.substr($activity->end_time, 0, 5) : ''));
        $summaryCards = [
            ['label' => 'Total Hadir', 'value' => $attendanceSummary['present'], 'class' => 'border-l-emerald-500'],
            ['label' => 'Total Izin', 'value' => $attendanceSummary['permission'], 'class' => 'border-l-sky-500'],
            ['label' => 'Total Tidak Hadir', 'value' => $attendanceSummary['absent'], 'class' => 'border-l-slate-500'],
            ['label' => 'Total Perlu Verifikasi', 'value' => $attendanceSummary['need_verification'], 'class' => 'border-l-amber-500'],
        ];
    @endphp

    <div class="max-w-7xl space-y-6">
        @if (session('success'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
        @endif
        @if (session('info'))
            <div class="rounded-lg border border-sky-200 bg-sky-50 px-4 py-3 text-sm font-medium text-sky-800">{{ session('info') }}</div>
        @endif

        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <a href="{{ route('activities.index') }}" class="inline-flex items-center text-sm font-semibold text-slate-600 hover:text-slate-900">Kembali ke daftar kegiatan</a>
                <h2 class="mt-3 text-2xl font-bold text-slate-950">{{ $activity->title }}</h2>
                <p class="mt-2 text-sm text-slate-500">{{ $activity->agendaSchedule?->title ?? 'Kegiatan mandiri' }}</p>
            </div>
            <span class="{{ $statusClasses[$activity->status] }} inline-flex w-fit rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset">{{ $statusLabels[$activity->status] }}</span>
        </div>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.15fr)_minmax(360px,0.85fr)]">
            <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Informasi Kegiatan</p>
                        <h3 class="mt-2 text-lg font-bold text-slate-950">{{ $activity->title }}</h3>
                    </div>
                    <span class="{{ $statusClasses[$activity->status] }} inline-flex shrink-0 rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset">{{ $statusLabels[$activity->status] }}</span>
                </div>

                <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tanggal Kegiatan</dt><dd class="mt-1 text-sm font-medium text-slate-800">{{ $activity->activity_date->format('d/m/Y') }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Waktu Kegiatan</dt><dd class="mt-1 text-sm font-medium text-slate-800">{{ $activityTime ?: '-' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Lokasi</dt><dd class="mt-1 text-sm font-medium text-slate-800">{{ $activity->location ?: '-' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Bidang</dt><dd class="mt-1 text-sm font-medium text-slate-800">{{ $activity->department?->name ?? '-' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">PIC</dt><dd class="mt-1 text-sm font-medium text-slate-800">{{ $activity->pic?->full_name ?? '-' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Dibuat Oleh</dt><dd class="mt-1 text-sm font-medium text-slate-800">{{ $activity->creator?->name ?? '-' }}</dd></div>
                </dl>

                <div class="mt-6 rounded-lg border border-slate-200 bg-slate-50 p-4">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Alasan Perubahan</p>
                    <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-700">{{ $activity->change_reason ?: 'Tidak ada alasan perubahan.' }}</p>
                </div>
            </section>

            <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Pengaturan Presensi</p>
                        <h3 class="mt-2 text-lg font-bold text-slate-950">Link, radius, dan koordinat</h3>
                    </div>
                    <span class="{{ $activity->attendance_enabled ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-slate-100 text-slate-600 ring-slate-200' }} inline-flex shrink-0 rounded-full px-3 py-1 text-xs font-semibold ring-1 ring-inset">{{ $activity->attendance_enabled ? 'Aktif' : 'Tidak Aktif' }}</span>
                </div>

                @if (! $activity->attendance_enabled)
                    <div class="mt-5 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800">Presensi belum diaktifkan untuk kegiatan ini.</div>
                @elseif (! $attendanceUrl)
                    <div class="mt-5 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-800">Token presensi belum tersedia. Buka halaman QR untuk membuat token otomatis.</div>
                @endif

                <dl class="mt-6 grid gap-4 sm:grid-cols-2">
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Waktu Buka</dt><dd class="mt-1 text-sm font-medium text-slate-800">{{ $activity->attendance_open_at?->format('d/m/Y H:i') ?? '-' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Waktu Tutup</dt><dd class="mt-1 text-sm font-medium text-slate-800">{{ $activity->attendance_close_at?->format('d/m/Y H:i') ?? '-' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Radius Presensi</dt><dd class="mt-1 text-sm font-medium text-slate-800">{{ $activity->attendance_radius }} meter</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Latitude</dt><dd class="mt-1 text-sm font-medium text-slate-800">{{ $activity->latitude ?: '-' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Longitude</dt><dd class="mt-1 text-sm font-medium text-slate-800">{{ $activity->longitude ?: '-' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Attendance Token</dt><dd class="mt-1 break-all font-mono text-xs text-slate-700">{{ $activity->attendance_token ? str($activity->attendance_token)->limit(18, '...') : '-' }}</dd></div>
                </dl>
            </section>
        </div>

        <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm" x-data="{ copied: false }">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Aksi Cepat</p>
                    <h3 class="mt-2 text-lg font-bold text-slate-950">Kontrol kegiatan</h3>
                </div>
                <p x-show="copied" x-transition class="text-sm font-semibold text-emerald-700">Link disalin</p>
            </div>

            <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                <a href="{{ route('activities.edit', $activity) }}" class="inline-flex items-center justify-center rounded-lg border border-amber-300 px-4 py-2.5 text-sm font-semibold text-amber-700 hover:bg-amber-50">Edit Kegiatan</a>
                <a href="{{ route('activities.attendances.index', $activity) }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Buka Daftar Hadir</a>
                <div x-data="{ open: false }" x-on:confirmed="$refs.syncParticipantsForm.submit()">
                    <form x-ref="syncParticipantsForm" method="POST" action="{{ route('activities.attendances.sync-participants', $activity) }}" x-on:submit.prevent="open = true">
                        @csrf
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg border border-emerald-300 px-4 py-2.5 text-sm font-semibold text-emerald-700 hover:bg-emerald-50">Sinkronkan Peserta</button>
                    </form>

                    <x-ui.confirm-modal
                        title="Sinkronkan Peserta Presensi?"
                        description="Peserta presensi kegiatan ini akan disesuaikan dengan data anggota aktif. Data presensi yang sudah tersimpan tetap mengikuti aturan sistem."
                        confirm-text="Sinkronkan"
                        variant="warning"
                    />
                </div>
                @if ($activity->attendance_enabled)
                    <a href="{{ route('activities.attendance-qr', $activity) }}" class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-emerald-800">Lihat QR Presensi</a>
                @else
                    <button type="button" disabled class="inline-flex cursor-not-allowed items-center justify-center rounded-lg bg-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-500">QR Belum Aktif</button>
                @endif
                @if ($attendanceUrl)
                    <button type="button" @click="navigator.clipboard.writeText(@js($attendanceUrl)).then(() => { copied = true; setTimeout(() => copied = false, 2000) })" class="inline-flex items-center justify-center rounded-lg border border-cyan-300 px-4 py-2.5 text-sm font-semibold text-cyan-700 hover:bg-cyan-50">Salin Link Presensi</button>
                @else
                    <button type="button" disabled class="inline-flex cursor-not-allowed items-center justify-center rounded-lg border border-slate-200 px-4 py-2.5 text-sm font-semibold text-slate-400">Link Belum Tersedia</button>
                @endif
                <a href="{{ route('activities.attendances.export', $activity) }}" class="inline-flex items-center justify-center rounded-lg border border-emerald-600 px-4 py-2.5 text-sm font-semibold text-emerald-700 hover:bg-emerald-50">Export Rekap Excel</a>
            </div>
        </section>

        <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Ringkasan Presensi</p>
                    <h3 class="mt-2 text-lg font-bold text-slate-950">Status daftar hadir kegiatan</h3>
                </div>
                <div class="rounded-lg bg-emerald-50 px-4 py-2 text-right">
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Persentase Kehadiran</p>
                    <p class="text-xl font-bold text-emerald-800">{{ number_format($attendanceSummary['attendance_percentage'], 2) }}%</p>
                </div>
            </div>

            <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                @foreach ($summaryCards as $card)
                    <div class="{{ $card['class'] }} rounded-lg border border-slate-200 border-l-4 bg-white p-4 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">{{ $card['label'] }}</p>
                        <p class="mt-2 text-2xl font-bold text-slate-950">{{ number_format($card['value']) }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-base font-bold text-slate-950">Status Perubahan</h3>
            <p class="mt-3 text-sm text-slate-600">Status saat ini: <span class="font-semibold text-slate-800">{{ $statusLabels[$activity->status] }}</span></p>

            <form method="POST" action="{{ route('activities.status.update', $activity) }}" class="mt-6 grid gap-4 border-t border-slate-200 pt-6 sm:grid-cols-[220px_1fr_auto]">
                @csrf @method('PATCH')
                <select name="status" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600" required>
                    @foreach ($statusLabels as $value => $label)
                        <option value="{{ $value }}" @selected($activity->status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <input name="change_reason" type="text" value="{{ $activity->change_reason }}" placeholder="Alasan perubahan status" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Perbarui Status</button>
            </form>
        </section>
    </div>
@endsection
