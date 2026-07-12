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
        $monthOptions = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
        $summaryCards = [
            ['label' => 'Total Jadwal Aktif', 'value' => $agendaStats['active'], 'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-100'],
            ['label' => 'Total Jadwal Nonaktif', 'value' => $agendaStats['inactive'], 'class' => 'bg-slate-50 text-slate-700 ring-slate-200'],
            ['label' => 'Total Agenda Mingguan', 'value' => $agendaStats['weekly'], 'class' => 'bg-sky-50 text-sky-700 ring-sky-100'],
            ['label' => 'Total Agenda Bulanan', 'value' => $agendaStats['monthly'], 'class' => 'bg-violet-50 text-violet-700 ring-violet-100'],
        ];
        $filterCount = collect([$departmentId, $scheduleType, $activeStatus])->filter(fn ($value) => filled($value))->count();
    @endphp

    <div
        class="space-y-6"
        x-data="generateMonthlyModal()"
        x-on:open-generate-monthly.window="openModal($event.detail)"
        x-on:keydown.escape.window="closeModal()"
    >
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

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="grid gap-4 border-b border-slate-200 px-5 py-4 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-center">
                <div>
                    <h3 class="text-base font-bold text-slate-950">Tabel Jadwal Agenda</h3>
                    <p class="mt-1 text-sm text-slate-500">Daftar jadwal sesuai filter aktif. Gunakan scroll horizontal pada layar kecil.</p>
                </div>
                <x-ui.table-toolbar
                    :action="route('agenda-schedules.index')"
                    search-placeholder="Cari nama agenda"
                    :search-value="$search"
                    :search-hidden="[
                        'sort' => $currentSort,
                        'direction' => $currentDirection,
                        'per_page' => $perPage,
                        'department_id' => $departmentId,
                        'schedule_type' => $scheduleType,
                        'is_active' => $activeStatus,
                    ]"
                    :filter-hidden="[
                        'sort' => $currentSort,
                        'direction' => $currentDirection,
                        'per_page' => $perPage,
                    ]"
                    :filter-count="$filterCount"
                    :reset-href="route('agenda-schedules.index')"
                    show-filter
                >
                    <x-slot:filters>
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label for="department_id_filter" class="text-sm font-semibold text-slate-700">Bidang</label>
                                <select id="department_id_filter" name="department_id" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                    <option value="">Semua bidang</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}" @selected((string) $departmentId === (string) $department->id)>{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="schedule_type_filter" class="text-sm font-semibold text-slate-700">Tipe jadwal</label>
                                <select id="schedule_type_filter" name="schedule_type" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                    <option value="">Semua tipe</option>
                                    @foreach ($typeLabels as $value => $label)
                                        <option value="{{ $value }}" @selected($scheduleType === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="is_active_filter" class="text-sm font-semibold text-slate-700">Status</label>
                                <select id="is_active_filter" name="is_active" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                    <option value="">Semua status</option>
                                    <option value="1" @selected($activeStatus === '1')>Aktif</option>
                                    <option value="0" @selected($activeStatus === '0')>Nonaktif</option>
                                </select>
                            </div>
                        </div>
                    </x-slot:filters>

                    <x-per-page-selector :per-page="$perPage" :options="$perPageOptions" :query="$queryParams" />
                </x-ui.table-toolbar>
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
                            <th class="sticky right-0 z-20 whitespace-nowrap border-l border-slate-200 bg-slate-50 px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500 shadow-[-8px_0_12px_-12px_rgba(15,23,42,0.35)]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($agendaSchedules as $agendaSchedule)
                            @php
                                $generatePayload = [
                                    'id' => $agendaSchedule->id,
                                    'title' => $agendaSchedule->title,
                                    'schedule_type' => $agendaSchedule->schedule_type,
                                    'day_of_week' => $agendaSchedule->day_of_week,
                                    'day_label' => $dayLabels[$agendaSchedule->day_of_week] ?? '-',
                                    'start_time' => $agendaSchedule->start_time ? substr($agendaSchedule->start_time, 0, 5) : '-',
                                    'end_time' => $agendaSchedule->end_time ? substr($agendaSchedule->end_time, 0, 5) : '-',
                                    'action' => route('agenda-schedules.generate-monthly.store', $agendaSchedule),
                                    'weekly_topics' => $agendaSchedule->weeklyTopics
                                        ->mapWithKeys(fn ($topic) => [
                                            $topic->week_number => [
                                                'topic' => $topic->topic,
                                                'is_active' => $topic->is_active,
                                            ],
                                        ])
                                        ->all(),
                                ];
                                $pattern = match ($agendaSchedule->schedule_type) {
                                    'incidental' => \App\Support\DateFormatter::date($agendaSchedule->specific_date),
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
                                <td class="whitespace-nowrap px-3 py-4"><x-ui.status-badge :status="$agendaSchedule->is_active ? 'active' : 'inactive'" :label="$agendaSchedule->is_active ? 'Aktif' : 'Nonaktif'" /></td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-600">{{ \App\Support\DateFormatter::date($agendaSchedule->created_at) }}</td>
                                <td class="sticky right-0 z-10 whitespace-nowrap border-l border-slate-100 bg-white px-3 py-4 text-right text-sm font-semibold shadow-[-8px_0_12px_-12px_rgba(15,23,42,0.35)]">
                                    <div class="flex justify-end gap-1.5">
                                        <x-action-icon :href="route('agenda-schedules.show', $agendaSchedule)" label="Detail" icon="eye" variant="blue" />
                                        <x-ui.action-dropdown>
                                            <x-ui.action-dropdown-item :href="route('agenda-schedules.edit', $agendaSchedule)" label="Edit" icon="pencil" />
                                            @if ($agendaSchedule->schedule_type === 'weekly')
                                                <x-ui.action-dropdown-item
                                                    label="Generate Kegiatan Bulanan"
                                                    icon="calendar"
                                                    :click="'$dispatch(\'open-generate-monthly\', '.\Illuminate\Support\Js::from($generatePayload).')'"
                                                />
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
                                <td colspan="11">
                                    <x-ui.empty-state title="Belum ada jadwal agenda." description="Buat jadwal agenda pertama untuk mulai menyusun kegiatan rutin." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $agendaSchedules->links() }}

        <template x-teleport="body">
            <div
                x-cloak
                x-show="open"
                class="fixed inset-0 z-[80] flex min-h-screen items-center justify-center px-4 py-6"
                role="dialog"
                aria-modal="true"
                style="display: none;"
            >
                <div x-show="open" x-transition.opacity class="fixed inset-0 bg-slate-950/60 backdrop-blur-sm" x-on:click="closeModal()" aria-hidden="true"></div>

                <section
                    x-show="open"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="translate-y-3 scale-95 opacity-0"
                    x-transition:enter-end="translate-y-0 scale-100 opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="translate-y-0 scale-100 opacity-100"
                    x-transition:leave-end="translate-y-3 scale-95 opacity-0"
                    class="relative flex max-h-[92vh] w-full max-w-4xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl shadow-slate-950/20"
                >
                    <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">Generate Kegiatan Bulanan</p>
                            <h2 class="mt-1 text-xl font-bold text-slate-950" x-text="schedule?.title || 'Jadwal Agenda'"></h2>
                            <p class="mt-1 text-sm text-slate-500">
                                <span x-text="schedule?.day_label || '-'"></span>
                                <span> &middot; </span>
                                <span x-text="`${schedule?.start_time || '-'} - ${schedule?.end_time || '-'}`"></span>
                            </p>
                        </div>
                        <button type="button" x-on:click="closeModal()" class="rounded-xl p-2 text-slate-500 transition hover:bg-slate-100 hover:text-slate-800" aria-label="Tutup modal">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 0 1 1.414 0L10 8.586l4.293-4.293a1 1 0 1 1 1.414 1.414L11.414 10l4.293 4.293a1 1 0 0 1-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 0 1-1.414-1.414L8.586 10 4.293 5.707a1 1 0 0 1 0-1.414Z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>

                    <form
                        x-data="{ open: false }"
                        x-ref="generateMonthlyForm"
                        method="POST"
                        x-bind:action="schedule?.action"
                        x-on:submit.prevent="open = true"
                        x-on:confirmed="submitting = true; $refs.generateMonthlyForm.submit()"
                        class="flex min-h-0 flex-1 flex-col"
                    >
                        @csrf
                        <div class="space-y-5 overflow-y-auto px-5 py-5">
                            <div class="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <label for="generate_month" class="block text-sm font-semibold text-slate-700">Bulan</label>
                                    <select id="generate_month" name="month" x-model.number="month" x-on:change="buildOccurrences()" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600" required>
                                        @foreach ($monthOptions as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="generate_year" class="block text-sm font-semibold text-slate-700">Tahun</label>
                                    <input id="generate_year" name="year" type="number" min="2000" max="2100" x-model.number="year" x-on:input.debounce.250ms="buildOccurrences()" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600" required>
                                </div>
                            </div>

                            <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                                Hanya minggu yang dicentang aktif yang akan dibuat menjadi Kegiatan Aktual.
                            </div>

                            <div class="overflow-hidden rounded-xl border border-slate-200">
                                <div class="grid grid-cols-[96px_minmax(120px,1fr)_88px_minmax(180px,1.7fr)] gap-3 bg-slate-50 px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-500 max-sm:hidden">
                                    <span>Minggu</span>
                                    <span>Tanggal</span>
                                    <span>Aktif</span>
                                    <span>Topik Agenda</span>
                                </div>

                                <template x-if="occurrences.length === 0">
                                    <div class="px-4 py-8 text-center text-sm text-slate-500">Tidak ada tanggal yang sesuai pola jadwal pada bulan ini.</div>
                                </template>

                                <template x-for="(occurrence, index) in occurrences" :key="occurrence.date">
                                    <div class="grid gap-3 border-t border-slate-100 px-4 py-3 sm:grid-cols-[96px_minmax(120px,1fr)_88px_minmax(180px,1.7fr)] sm:items-center">
                                        <input type="hidden" x-bind:name="`occurrences[${index}][date]`" x-bind:value="occurrence.date">
                                        <input type="hidden" x-bind:name="`occurrences[${index}][week]`" x-bind:value="occurrence.week">
                                        <input type="hidden" x-bind:name="`occurrences[${index}][active]`" value="0">

                                        <div>
                                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 sm:hidden">Minggu</p>
                                            <p class="text-sm font-semibold text-slate-800" x-text="`Minggu ke-${occurrence.week}`"></p>
                                        </div>
                                        <div>
                                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 sm:hidden">Tanggal</p>
                                            <p class="text-sm text-slate-700" x-text="occurrence.label"></p>
                                        </div>
                                        <label class="inline-flex items-center gap-2">
                                            <input type="checkbox" x-bind:name="`occurrences[${index}][active]`" value="1" x-model="occurrence.active" class="rounded border-slate-300 text-emerald-700 shadow-sm focus:ring-emerald-600">
                                            <span class="text-sm font-semibold text-slate-700">Aktif</span>
                                        </label>
                                        <div>
                                            <label class="sr-only" x-bind:for="`topic_${index}`">Topik Agenda</label>
                                            <input x-bind:id="`topic_${index}`" type="text" x-bind:name="`occurrences[${index}][topic]`" x-model="occurrence.topic" placeholder="Contoh: Adab Bermedia Sosial" class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="flex flex-col-reverse gap-3 border-t border-slate-200 bg-white px-5 py-4 sm:flex-row sm:justify-end">
                            <button type="button" x-on:click="closeModal()" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Batal</button>
                            <button type="submit" x-bind:disabled="submitting || occurrences.length === 0" class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800 disabled:cursor-not-allowed disabled:opacity-70">
                                <svg x-cloak x-show="submitting" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"></path>
                                </svg>
                                <span x-show="! submitting">Generate</span>
                                <span x-cloak x-show="submitting">Mengenerate...</span>
                            </button>
                        </div>

                        <x-ui.confirm-modal
                            title="Generate Kegiatan Bulanan?"
                            description="Sistem akan membuat Kegiatan Aktual dari minggu yang dicentang aktif. Kegiatan yang sudah ada pada tanggal yang sama akan dilewati."
                            confirm-text="Generate"
                            loading-text="Menggenerate..."
                            variant="primary"
                        />
                    </form>
                </section>
            </div>
        </template>
    </div>

    <script>
        function generateMonthlyModal() {
            return {
                open: false,
                submitting: false,
                schedule: null,
                month: new Date().getMonth() + 1,
                year: new Date().getFullYear(),
                occurrences: [],
                openModal(schedule) {
                    this.schedule = schedule;
                    this.submitting = false;
                    this.month = new Date().getMonth() + 1;
                    this.year = new Date().getFullYear();
                    this.buildOccurrences();
                    this.open = true;
                },
                closeModal() {
                    this.open = false;
                    this.submitting = false;
                },
                buildOccurrences() {
                    if (! this.schedule || this.schedule.schedule_type !== 'weekly') {
                        this.occurrences = [];
                        return;
                    }

                    const month = Number(this.month);
                    const year = Number(this.year);
                    const targetDay = Number(this.schedule.day_of_week);
                    const lastDate = new Date(year, month, 0).getDate();
                    const templates = this.schedule.weekly_topics || {};
                    const rows = [];

                    for (let day = 1; day <= lastDate; day++) {
                        const date = new Date(year, month - 1, day);

                        if (date.getDay() !== targetDay) {
                            continue;
                        }

                        const isoDate = `${year}-${String(month).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                        const weekNumber = rows.length + 1;
                        const template = templates[weekNumber] || {};
                        rows.push({
                            week: weekNumber,
                            date: isoDate,
                            label: `${String(day).padStart(2, '0')}/${String(month).padStart(2, '0')}/${year}`,
                            active: Object.prototype.hasOwnProperty.call(template, 'is_active') ? Boolean(template.is_active) : true,
                            topic: template.topic || '',
                        });
                    }

                    this.occurrences = rows;
                },
            };
        }
    </script>
@endsection
