@extends('layouts.admin')

@section('title', 'Jadwal Agenda - Pemuda Cirengit')
@section('section', 'Agenda')
@section('page-title', 'Jadwal Agenda')
@section('breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Jadwal Agenda'],
    ]" />
@endsection

@section('content')
    @php
        $typeLabels = ['incidental' => 'Insidental', 'weekly' => 'Mingguan', 'monthly' => 'Bulanan', 'yearly' => 'Tahunan'];
        $typeClasses = [
            'incidental' => 'bg-slate-100 text-slate-700 ring-slate-200',
            'weekly' => 'bg-sky-50 text-sky-700 ring-sky-200',
            'monthly' => 'bg-violet-50 text-violet-700 ring-violet-200',
            'yearly' => 'bg-amber-50 text-amber-700 ring-amber-200',
        ];
        $dayLabels = [0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'];
        $summaryCards = [
            ['label' => 'Total Jadwal Aktif', 'value' => $agendaStats['active'], 'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-100'],
            ['label' => 'Total Jadwal Nonaktif', 'value' => $agendaStats['inactive'], 'class' => 'bg-slate-50 text-slate-700 ring-slate-200'],
            ['label' => 'Total Agenda Mingguan', 'value' => $agendaStats['weekly'], 'class' => 'bg-sky-50 text-sky-700 ring-sky-100'],
            ['label' => 'Total Agenda Bulanan', 'value' => $agendaStats['monthly'], 'class' => 'bg-violet-50 text-violet-700 ring-violet-100'],
        ];
    @endphp

    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">Agenda & Kegiatan</p>
                    <h2 class="mt-2 text-2xl font-bold text-slate-950">Jadwal Agenda</h2>
                    <p class="mt-2 max-w-2xl text-sm text-slate-500">Kelola agenda rutin dan jadwal kegiatan Pemuda Persis Cirengit.</p>
                </div>
                <a href="{{ route('agenda-schedules.create') }}" class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">Tambah Jadwal Agenda</a>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
        @endif

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($summaryCards as $card)
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="{{ $card['class'] }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ $card['label'] }}</div>
                    <p class="mt-4 text-3xl font-bold text-slate-950">{{ number_format($card['value']) }}</p>
                </div>
            @endforeach
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4">
                <h3 class="text-base font-bold text-slate-950">Filter Jadwal Agenda</h3>
                <p class="mt-1 text-sm text-slate-500">Cari agenda berdasarkan nama, bidang, tipe jadwal, atau status aktif.</p>
            </div>
            <form method="GET" action="{{ route('agenda-schedules.index') }}" class="grid gap-4 lg:grid-cols-12">
                <input type="hidden" name="sort" value="{{ $currentSort }}">
                <input type="hidden" name="direction" value="{{ $currentDirection }}">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <div class="lg:col-span-4">
                    <label for="search" class="text-sm font-semibold text-slate-700">Search nama agenda</label>
                    <input id="search" name="search" type="search" value="{{ $search }}" placeholder="Cari nama agenda" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                </div>
                <div class="lg:col-span-3">
                    <label for="department_id" class="text-sm font-semibold text-slate-700">Bidang</label>
                    <select id="department_id" name="department_id" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                        <option value="">Semua bidang</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected((string) $departmentId === (string) $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="lg:col-span-3">
                    <label for="schedule_type" class="text-sm font-semibold text-slate-700">Tipe jadwal</label>
                    <select id="schedule_type" name="schedule_type" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                        <option value="">Semua tipe</option>
                        @foreach ($typeLabels as $value => $label)
                            <option value="{{ $value }}" @selected($scheduleType === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="lg:col-span-2">
                    <label for="is_active" class="text-sm font-semibold text-slate-700">Status</label>
                    <select id="is_active" name="is_active" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                        <option value="">Semua status</option>
                        <option value="1" @selected($activeStatus === '1')>Aktif</option>
                        <option value="0" @selected($activeStatus === '0')>Nonaktif</option>
                    </select>
                </div>
                <div class="flex gap-2 lg:col-span-12 lg:justify-end">
                    <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700 sm:flex-none">Terapkan Filter</button>
                    <a href="{{ route('agenda-schedules.index') }}" class="inline-flex flex-1 items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 sm:flex-none">Reset</a>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-950">Tabel Jadwal Agenda</h3>
                    <p class="mt-1 text-sm text-slate-500">Daftar jadwal sesuai filter aktif. Gunakan scroll horizontal pada layar kecil.</p>
                </div>
                <x-per-page-selector :per-page="$perPage" :options="$perPageOptions" :query="$queryParams" />
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">No</th>
                            <x-sortable-th field="title" label="Nama Agenda" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Bidang</th>
                            <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">PIC</th>
                            <x-sortable-th field="schedule_type" label="Tipe Jadwal" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Pola Jadwal</th>
                            <x-sortable-th field="start_time" label="Waktu" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Lokasi</th>
                            <x-sortable-th field="is_active" label="Status" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <x-sortable-th field="created_at" label="Dibuat" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <th class="whitespace-nowrap px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($agendaSchedules as $agendaSchedule)
                            @php
                                $pattern = match ($agendaSchedule->schedule_type) {
                                    'incidental' => $agendaSchedule->specific_date?->format('d/m/Y') ?? '-',
                                    'weekly' => isset($dayLabels[$agendaSchedule->day_of_week]) ? 'Setiap '.$dayLabels[$agendaSchedule->day_of_week] : '-',
                                    'monthly' => $agendaSchedule->day_of_month ? 'Setiap tanggal '.$agendaSchedule->day_of_month : '-',
                                    'yearly' => $agendaSchedule->specific_date ? 'Tahunan, '.$agendaSchedule->specific_date->format('d/m') : 'Tahunan',
                                    default => '-',
                                };
                                $time = trim(($agendaSchedule->start_time ? substr($agendaSchedule->start_time, 0, 5) : '').($agendaSchedule->end_time ? ' - '.substr($agendaSchedule->end_time, 0, 5) : ''));
                            @endphp
                            <tr class="align-top transition hover:bg-slate-50/70">
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-500">{{ $agendaSchedules->firstItem() + $loop->index }}</td>
                                <td class="max-w-56 px-3 py-4"><p class="line-clamp-2 break-words text-sm font-semibold text-slate-900">{{ $agendaSchedule->title }}</p></td>
                                <td class="max-w-32 px-3 py-4 text-sm text-slate-600"><span class="line-clamp-2 break-words">{{ $agendaSchedule->department?->name ?? '-' }}</span></td>
                                <td class="max-w-36 px-3 py-4 text-sm text-slate-600"><span class="line-clamp-2 break-words">{{ $agendaSchedule->pic?->full_name ?? '-' }}</span></td>
                                <td class="whitespace-nowrap px-3 py-4"><span class="{{ $typeClasses[$agendaSchedule->schedule_type] }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ $typeLabels[$agendaSchedule->schedule_type] }}</span></td>
                                <td class="max-w-36 px-3 py-4 text-sm text-slate-600"><span class="line-clamp-2 break-words">{{ $pattern }}</span></td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-600">{{ $time !== '' ? $time : '-' }}</td>
                                <td class="max-w-44 px-3 py-4 text-sm text-slate-600"><span class="line-clamp-2 break-words">{{ $agendaSchedule->default_location ?: '-' }}</span></td>
                                <td class="whitespace-nowrap px-3 py-4"><span class="{{ $agendaSchedule->is_active ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-slate-100 text-slate-600 ring-slate-200' }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ $agendaSchedule->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-600">{{ $agendaSchedule->created_at?->format('d/m/Y') ?? '-' }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-right text-sm font-semibold">
                                    <div class="flex justify-end gap-1.5">
                                        <x-action-icon :href="route('agenda-schedules.show', $agendaSchedule)" label="Detail" icon="eye" variant="blue" />
                                        <x-ui.action-dropdown>
                                            <x-ui.action-dropdown-item :href="route('agenda-schedules.edit', $agendaSchedule)" label="Edit" icon="pencil" />
                                            @if ($agendaSchedule->schedule_type === 'weekly')
                                                <x-ui.action-dropdown-item :href="route('agenda-schedules.generate-monthly.create', $agendaSchedule)" label="Generate Kegiatan Bulanan" icon="calendar" />
                                            @endif
                                            @if ($agendaSchedule->is_active)
                                                <x-ui.action-dropdown-item
                                                    :action="route('agenda-schedules.deactivate', $agendaSchedule)"
                                                    method="PATCH"
                                                    label="Nonaktifkan"
                                                    icon="ban"
                                                    variant="warning"
                                                    confirm="Nonaktifkan jadwal agenda ini?"
                                                    confirm-title="Nonaktifkan Jadwal Agenda?"
                                                    confirm-description="Jadwal agenda tidak akan aktif untuk pembuatan kegiatan berikutnya sampai diaktifkan kembali melalui edit data."
                                                    confirm-text="Nonaktifkan"
                                                    confirm-variant="warning"
                                                />
                                            @endif
                                        </x-ui.action-dropdown>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-4 py-14 text-center">
                                    <p class="text-base font-semibold text-slate-800">Belum ada jadwal agenda.</p>
                                    <p class="mt-1 text-sm text-slate-500">Tambahkan jadwal agenda baru atau ubah filter pencarian.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $agendaSchedules->links() }}
    </div>
@endsection
