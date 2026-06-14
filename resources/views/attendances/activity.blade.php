@extends('layouts.admin')

@section('title', 'Daftar Hadir Kegiatan - Pemuda Cirengit')
@section('section', 'Presensi')
@section('page-title', 'Daftar Hadir Kegiatan')
@section('breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Kegiatan Aktual', 'url' => route('activities.index')],
        ['label' => 'Daftar Hadir', 'url' => route('attendances.index')],
        ['label' => 'Daftar Hadir per Kegiatan'],
    ]" />
@endsection

@section('content')
    @php
        $statusLabels = ['present' => 'Hadir', 'permission' => 'Izin', 'absent' => 'Tidak Hadir', 'need_verification' => 'Perlu Verifikasi'];
        $statusClasses = ['present' => 'bg-emerald-50 text-emerald-700 ring-emerald-200', 'permission' => 'bg-sky-50 text-sky-700 ring-sky-200', 'absent' => 'bg-slate-100 text-slate-700 ring-slate-200', 'need_verification' => 'bg-amber-50 text-amber-700 ring-amber-200'];
        $verificationLabels = ['valid' => 'Valid', 'need_verification' => 'Perlu Verifikasi', 'rejected' => 'Ditolak'];
        $verificationClasses = ['valid' => 'bg-emerald-50 text-emerald-700 ring-emerald-200', 'need_verification' => 'bg-amber-50 text-amber-700 ring-amber-200', 'rejected' => 'bg-red-50 text-red-700 ring-red-200'];
        $activityStatusLabels = ['scheduled' => 'Terjadwal', 'completed' => 'Selesai', 'holiday' => 'Libur', 'postponed' => 'Ditunda', 'relocated' => 'Pindah Lokasi', 'cancelled' => 'Dibatalkan'];
        $activityStatusClasses = ['scheduled' => 'bg-sky-50 text-sky-700 ring-sky-200', 'completed' => 'bg-emerald-50 text-emerald-700 ring-emerald-200', 'holiday' => 'bg-slate-100 text-slate-600 ring-slate-200', 'postponed' => 'bg-amber-50 text-amber-700 ring-amber-200', 'relocated' => 'bg-cyan-50 text-cyan-700 ring-cyan-200', 'cancelled' => 'bg-red-50 text-red-700 ring-red-200'];
        $attendanceUrl = $activity->attendance_token ? route('attendance.check-in.show', $activity->attendance_token, true) : null;
        $activityTime = trim(($activity->start_time ? substr($activity->start_time, 0, 5) : '').($activity->end_time ? ' - '.substr($activity->end_time, 0, 5) : ''));
        $summaryCards = [
            ['label' => 'Total Hadir', 'value' => $summary['present'], 'class' => 'border-l-emerald-500'],
            ['label' => 'Total Izin', 'value' => $summary['permission'], 'class' => 'border-l-sky-500'],
            ['label' => 'Total Tidak Hadir', 'value' => $summary['absent'], 'class' => 'border-l-slate-500'],
            ['label' => 'Total Perlu Verifikasi', 'value' => $summary['need_verification'], 'class' => 'border-l-amber-500'],
        ];
    @endphp

    <div class="space-y-6" x-data="{ copied: false }">
        @if (session('success'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
        @endif

        <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <a href="{{ route('activities.show', $activity) }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">Kembali ke Detail Kegiatan</a>
                    <h2 class="mt-3 text-2xl font-bold text-slate-950">{{ $activity->title }}</h2>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <span class="{{ $activityStatusClasses[$activity->status] }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ $activityStatusLabels[$activity->status] }}</span>
                        <span class="{{ $activity->attendance_enabled ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-slate-100 text-slate-600 ring-slate-200' }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">Presensi {{ $activity->attendance_enabled ? 'Aktif' : 'Tidak Aktif' }}</span>
                    </div>
                </div>
                <dl class="grid gap-3 text-sm sm:grid-cols-2 lg:min-w-[520px]">
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tanggal</dt><dd class="mt-1 font-medium text-slate-800">{{ $activity->activity_date->format('d/m/Y') }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Waktu</dt><dd class="mt-1 font-medium text-slate-800">{{ $activityTime ?: '-' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Lokasi</dt><dd class="mt-1 font-medium text-slate-800">{{ $activity->location ?: '-' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Bidang</dt><dd class="mt-1 font-medium text-slate-800">{{ $activity->department?->name ?? '-' }}</dd></div>
                </dl>
            </div>
        </section>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
            @foreach ($summaryCards as $card)
                <div class="{{ $card['class'] }} rounded-lg border border-slate-200 border-l-4 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">{{ $card['label'] }}</p>
                    <p class="mt-3 text-2xl font-bold text-slate-950">{{ number_format($card['value']) }}</p>
                </div>
            @endforeach
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-5 shadow-sm">
                <p class="text-sm font-medium text-emerald-700">Persentase Kehadiran</p>
                <p class="mt-3 text-2xl font-bold text-emerald-900">{{ number_format($attendancePercentage, 2) }}%</p>
            </div>
        </section>

        <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap">
                <a href="{{ route('activities.show', $activity) }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Kembali ke Detail Kegiatan</a>
                <div x-data="{ open: false, submitting: false }" x-on:confirmed="submitting = true; $refs.syncParticipantsForm.submit()">
                    <form x-ref="syncParticipantsForm" method="POST" action="{{ route('activities.attendances.sync-participants', $activity) }}" x-on:submit.prevent="open = true">
                        @csrf
                        <x-ui.submit-button class="w-full" variant="primary" loading-text="Menyinkronkan...">Sinkronkan Peserta Presensi</x-ui.submit-button>
                    </form>

                    <x-ui.confirm-modal
                        title="Sinkronkan Peserta Presensi?"
                        description="Peserta presensi kegiatan ini akan disesuaikan dengan data anggota aktif. Data presensi yang sudah tersimpan tetap mengikuti aturan sistem."
                        confirm-text="Sinkronkan"
                        variant="warning"
                    />
                </div>
                @if ($activity->attendance_enabled)
                    <a href="{{ route('activities.attendance-qr', $activity) }}" class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Lihat QR Presensi</a>
                @else
                    <button type="button" disabled class="inline-flex cursor-not-allowed items-center justify-center rounded-lg bg-slate-200 px-4 py-2 text-sm font-semibold text-slate-500">QR Belum Aktif</button>
                @endif
                @if ($attendanceUrl)
                    <button type="button" @click="navigator.clipboard.writeText(@js($attendanceUrl)).then(() => { copied = true; setTimeout(() => copied = false, 2000) })" class="inline-flex items-center justify-center rounded-lg border border-cyan-300 px-4 py-2 text-sm font-semibold text-cyan-700 hover:bg-cyan-50">Salin Link Presensi</button>
                @else
                    <button type="button" disabled class="inline-flex cursor-not-allowed items-center justify-center rounded-lg border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-400">Link Belum Tersedia</button>
                @endif
                <a
                    href="{{ route('activities.attendances.export', $activity) }}"
                    x-data="{ submitting: false }"
                    x-on:click="submitting = true"
                    x-bind:class="{ 'pointer-events-none opacity-80': submitting }"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-emerald-600 px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-50"
                >
                    <svg x-cloak x-show="submitting" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    <span x-show="! submitting">Export Excel</span>
                    <span x-cloak x-show="submitting">Menyiapkan file...</span>
                </a>
                <a href="{{ route('activities.attendances.create', $activity) }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Input Satu Anggota</a>
                <a href="{{ route('activities.attendances.bulk.create', $activity) }}" class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Input Massal</a>
            </div>
            <p x-show="copied" x-transition class="mt-3 text-sm font-semibold text-emerald-700">Link disalin</p>
        </section>

        <section class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <form method="GET" action="{{ route('activities.attendances.index', $activity) }}" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-[minmax(220px,1fr)_180px_200px_auto]">
                <input type="hidden" name="sort" value="{{ $currentSort }}">
                <input type="hidden" name="direction" value="{{ $currentDirection }}">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <input name="search" type="search" value="{{ $search }}" placeholder="Cari nama anggota atau NPA" aria-label="Cari nama anggota atau NPA" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                <select name="status" aria-label="Filter status kehadiran" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                    <option value="">Semua status</option>
                    @foreach ($statusLabels as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="department_id" aria-label="Filter bidang" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                    <option value="">Semua bidang</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected((string) $departmentId === (string) $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
                <div class="flex gap-2 sm:col-span-2 xl:col-span-1">
                    <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Filter</button>
                    <a href="{{ route('activities.attendances.index', $activity) }}" class="inline-flex flex-1 items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Reset</a>
                </div>
            </form>
        </section>

        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="flex justify-end border-b border-slate-200 px-4 py-3">
                <x-per-page-selector :per-page="$perPage" :options="$perPageOptions" :query="$queryParams" />
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">No</th>
                            <x-sortable-th field="npa" label="NPA" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <x-sortable-th field="full_name" label="Nama Anggota" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Jabatan</th>
                            <x-sortable-th field="status" label="Status Kehadiran" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Metode Presensi</th>
                            <x-sortable-th field="checked_in_at" label="Waktu Presensi" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Jarak</th>
                            <x-sortable-th field="verification_status" label="Status Verifikasi" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Catatan</th>
                            <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($attendances as $attendance)
                            <tr class="align-top">
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-600">{{ $attendances->firstItem() + $loop->index }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-600">{{ $attendance->member->npa ?: '-' }}</td>
                                <td class="max-w-48 px-3 py-4 text-sm font-semibold text-slate-900"><span class="line-clamp-2 break-words">{{ $attendance->member->full_name }}</span></td>
                                <td class="max-w-36 px-3 py-4 text-sm text-slate-600"><span class="line-clamp-2 break-words">{{ $attendance->member->position?->name ?? '-' }}</span></td>
                                <td class="whitespace-nowrap px-3 py-4"><span class="{{ $statusClasses[$attendance->status] }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ $statusLabels[$attendance->status] }}</span></td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm capitalize text-slate-600">{{ $attendance->attendance_method }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-600">{{ $attendance->checked_in_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-600">{{ $attendance->distance_from_activity !== null ? number_format((float) $attendance->distance_from_activity, 2).' m' : '-' }}</td>
                                <td class="whitespace-nowrap px-3 py-4"><span class="{{ $verificationClasses[$attendance->verification_status] }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ $verificationLabels[$attendance->verification_status] }}</span></td>
                                <td class="max-w-48 px-3 py-4 text-sm text-slate-600"><span class="line-clamp-2 break-words">{{ $attendance->notes ?: '-' }}</span></td>
                                <td class="whitespace-nowrap px-3 py-4 text-right"><div class="flex justify-end gap-1.5">
                                    <x-action-icon :href="route('attendances.edit', $attendance)" label="Ubah Status" icon="pencil" variant="amber" />
                                    @if ($attendance->verification_status === 'need_verification')
                                        <x-ui.action-dropdown>
                                            <x-ui.action-dropdown-item :action="route('attendances.verify', $attendance)" method="PATCH" label="Verifikasi" icon="check" />
                                            <x-ui.action-dropdown-item :action="route('attendances.reject', $attendance)" method="PATCH" label="Tolak" icon="x" variant="danger" />
                                        </x-ui.action-dropdown>
                                    @endif
                                </div></td>
                            </tr>
                        @empty
                            <tr><td colspan="11" class="px-4 py-12 text-center text-sm text-slate-500">Belum ada peserta presensi. Klik Sinkronkan Peserta Presensi untuk membuat daftar hadir.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $attendances->links() }}
    </div>
@endsection
