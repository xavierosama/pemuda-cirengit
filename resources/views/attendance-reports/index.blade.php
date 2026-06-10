@extends('layouts.admin')

@section('title', 'Rekap Presensi - Pemuda Cirengit')
@section('section', 'Presensi')
@section('page-title', 'Rekap Presensi')

@section('content')
    @php
        $statusLabels = [
            'present' => 'Hadir',
            'permission' => 'Izin',
            'absent' => 'Tidak Hadir',
            'need_verification' => 'Perlu Verifikasi',
        ];
        $statusBadgeClasses = [
            'present' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'permission' => 'bg-sky-50 text-sky-700 ring-sky-200',
            'absent' => 'bg-slate-100 text-slate-700 ring-slate-200',
            'need_verification' => 'bg-amber-50 text-amber-700 ring-amber-200',
        ];
        $percentageClass = fn ($percentage) => $percentage >= 75
            ? 'bg-emerald-50 text-emerald-700 ring-emerald-200'
            : ($percentage >= 50 ? 'bg-amber-50 text-amber-700 ring-amber-200' : 'bg-red-50 text-red-700 ring-red-200');
        $periodLabel = \Illuminate\Support\Carbon::parse($filters['start_date'])->format('d/m/Y').' - '.\Illuminate\Support\Carbon::parse($filters['end_date'])->format('d/m/Y');
        $hasAttendanceData = array_sum($statusCounts) > 0;
        $hasActivityTrend = $chartData['activityTrend']['data']->sum() > 0;
        $hasDepartmentData = $chartData['departmentAttendance']['data']->sum() > 0;
        $summaryCards = [
            ['label' => 'Total Kegiatan', 'value' => $summary['total_activities'], 'note' => 'Kegiatan dalam periode', 'color' => 'border-l-violet-500'],
            ['label' => 'Total Anggota Aktif', 'value' => $summary['total_active_members'], 'note' => 'Sesuai filter bidang', 'color' => 'border-l-cyan-500'],
            ['label' => 'Total Hadir', 'value' => $summary['present'], 'note' => 'Status hadir', 'color' => 'border-l-emerald-500'],
            ['label' => 'Total Izin', 'value' => $summary['permission'], 'note' => 'Status izin', 'color' => 'border-l-sky-500'],
            ['label' => 'Total Tidak Hadir', 'value' => $summary['absent'], 'note' => 'Status tidak hadir', 'color' => 'border-l-slate-500'],
            ['label' => 'Total Perlu Verifikasi', 'value' => $summary['need_verification'], 'note' => 'Butuh keputusan admin', 'color' => 'border-l-amber-500'],
        ];
    @endphp

    <div class="space-y-6">
        <section class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-slate-950">Rekap Presensi</h2>
                    <p class="mt-2 text-sm text-slate-500">Pantau kehadiran anggota berdasarkan periode, bidang, dan kegiatan.</p>
                </div>
                <div class="rounded-lg bg-emerald-50 px-4 py-3 text-left ring-1 ring-emerald-100 lg:text-right">
                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Periode Aktif</p>
                    <p class="mt-1 text-sm font-bold text-emerald-900">{{ $periodLabel }}</p>
                </div>
            </div>
        </section>

        <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4">
                <h3 class="text-base font-bold text-slate-950">Filter Rekap</h3>
                <p class="mt-1 text-sm text-slate-500">Atur periode dan cakupan data yang ingin dimonitor.</p>
            </div>
            <form method="GET" action="{{ route('attendance-reports.index') }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-[160px_160px_190px_minmax(240px,1fr)_auto]">
                <div>
                    <label for="start_date" class="block text-sm font-semibold text-slate-700">Tanggal Mulai</label>
                    <input id="start_date" name="start_date" type="date" value="{{ $filters['start_date'] }}" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                    @error('start_date') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-semibold text-slate-700">Tanggal Akhir</label>
                    <input id="end_date" name="end_date" type="date" value="{{ $filters['end_date'] }}" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                    @error('end_date') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="department_id" class="block text-sm font-semibold text-slate-700">Bidang</label>
                    <select id="department_id" name="department_id" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                        <option value="">Semua bidang</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected((string) $filters['department_id'] === (string) $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                    @error('department_id') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="activity_id" class="block text-sm font-semibold text-slate-700">Kegiatan</label>
                    <select id="activity_id" name="activity_id" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                        <option value="">Semua kegiatan</option>
                        @foreach ($activityOptions as $activity)
                            <option value="{{ $activity->id }}" @selected((string) $filters['activity_id'] === (string) $activity->id)>{{ $activity->activity_date->format('d/m/Y') }} - {{ $activity->title }}</option>
                        @endforeach
                    </select>
                    @error('activity_id') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="flex gap-2 self-end md:col-span-2 xl:col-span-1">
                    <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-700">Terapkan Filter</button>
                    <a href="{{ route('attendance-reports.index') }}" class="inline-flex flex-1 items-center justify-center rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Reset Filter</a>
                </div>
            </form>
        </section>

        @if (! $hasAttendanceData)
            <section class="rounded-lg border border-amber-200 bg-amber-50 p-5 text-sm font-medium text-amber-800">
                Belum ada data presensi pada periode terpilih. Sinkronkan peserta presensi atau pilih periode lain untuk melihat rekap.
            </section>
        @endif

        <section class="grid gap-4 xl:grid-cols-[1.2fr_0.8fr]">
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($summaryCards as $card)
                    <div class="{{ $card['color'] }} rounded-lg border border-slate-200 border-l-4 bg-white p-5 shadow-sm">
                        <p class="text-sm font-medium text-slate-500">{{ $card['label'] }}</p>
                        <p class="mt-3 text-2xl font-bold text-slate-950">{{ number_format($card['value']) }}</p>
                        <p class="mt-2 text-xs text-slate-500">{{ $card['note'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-6 shadow-sm">
                <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">Persentase Kehadiran</p>
                <div class="mt-4 flex items-end gap-2">
                    <p class="text-4xl font-bold text-emerald-950">{{ rtrim(rtrim(number_format($summary['attendance_percentage'], 2), '0'), '.') }}%</p>
                    <p class="pb-1 text-sm text-emerald-700">Hadir / potensi kehadiran</p>
                </div>
                <div class="mt-5 h-3 overflow-hidden rounded-full bg-white ring-1 ring-emerald-100">
                    <div class="h-full rounded-full bg-emerald-600" style="width: {{ min($summary['attendance_percentage'], 100) }}%"></div>
                </div>
                <p class="mt-3 text-xs text-emerald-700">Potensi kehadiran: {{ number_format($summary['total_potential_attendances']) }}</p>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-3">
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-base font-bold text-slate-950">Komposisi Status Kehadiran</h2>
                <p class="mt-1 text-sm text-slate-500">Perbandingan status hadir, izin, tidak hadir, dan perlu verifikasi.</p>
                @if ($hasAttendanceData)
                    <div class="mt-5 h-72"><canvas id="statusCompositionChart"></canvas></div>
                @else
                    <div class="mt-5 flex h-72 items-center justify-center rounded-lg bg-slate-50 text-center text-sm text-slate-500">Belum ada data untuk grafik komposisi.</div>
                @endif
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-base font-bold text-slate-950">Tren Kehadiran per Kegiatan</h2>
                <p class="mt-1 text-sm text-slate-500">Jumlah hadir pada setiap kegiatan dalam periode.</p>
                @if ($hasActivityTrend)
                    <div class="mt-5 h-72"><canvas id="activityTrendChart"></canvas></div>
                @else
                    <div class="mt-5 flex h-72 items-center justify-center rounded-lg bg-slate-50 text-center text-sm text-slate-500">Belum ada data tren kehadiran.</div>
                @endif
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-base font-bold text-slate-950">Kehadiran per Bidang</h2>
                <p class="mt-1 text-sm text-slate-500">Akumulasi kehadiran anggota berdasarkan bidang.</p>
                @if ($hasDepartmentData)
                    <div class="mt-5 h-72"><canvas id="departmentAttendanceChart"></canvas></div>
                @else
                    <div class="mt-5 flex h-72 items-center justify-center rounded-lg bg-slate-50 text-center text-sm text-slate-500">Belum ada data kehadiran per bidang.</div>
                @endif
            </div>
        </section>

        <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="text-base font-bold text-slate-950">Ringkasan per Kegiatan</h2>
                <p class="mt-1 text-sm text-slate-500">Persentase dihitung dari hadir dibagi jumlah anggota aktif dalam filter.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            @foreach (['Tanggal', 'Nama Kegiatan', 'Bidang', 'Hadir', 'Izin', 'Tidak Hadir', 'Perlu Verifikasi', 'Persentase Kehadiran', 'Aksi'] as $heading)
                                <th class="{{ $heading === 'Aksi' ? 'text-right' : 'text-left' }} px-4 py-3 text-xs font-bold uppercase tracking-wide text-slate-500">{{ $heading }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($activityRows as $row)
                            <tr>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">{{ $row['activity']->activity_date->format('d/m/Y') }}</td>
                                <td class="px-4 py-4 text-sm font-semibold text-slate-900">{{ $row['activity']->title }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">{{ $row['activity']->department?->name ?? '-' }}</td>
                                <td class="px-4 py-4"><span class="{{ $statusBadgeClasses['present'] }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ number_format($row['counts']['present']) }}</span></td>
                                <td class="px-4 py-4"><span class="{{ $statusBadgeClasses['permission'] }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ number_format($row['counts']['permission']) }}</span></td>
                                <td class="px-4 py-4"><span class="{{ $statusBadgeClasses['absent'] }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ number_format($row['counts']['absent']) }}</span></td>
                                <td class="px-4 py-4"><span class="{{ $statusBadgeClasses['need_verification'] }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ number_format($row['counts']['need_verification']) }}</span></td>
                                <td class="whitespace-nowrap px-4 py-4"><span class="{{ $percentageClass($row['attendance_percentage']) }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ number_format($row['attendance_percentage'], 2) }}%</span></td>
                                <td class="whitespace-nowrap px-4 py-4 text-right"><x-action-icon :href="route('activities.attendances.index', $row['activity'])" label="Detail" icon="eye" variant="blue" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="9" class="px-4 py-10 text-center text-sm text-slate-500">Belum ada kegiatan pada periode ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="text-base font-bold text-slate-950">Ringkasan per Anggota</h2>
                <p class="mt-1 text-sm text-slate-500">Persentase dihitung dari hadir dibagi jumlah kegiatan dalam filter.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            @foreach (['NPA', 'Nama Anggota', 'Bidang', 'Hadir', 'Izin', 'Tidak Hadir', 'Perlu Verifikasi', 'Persentase Kehadiran'] as $heading)
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">{{ $heading }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($memberRows as $row)
                            <tr>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">{{ $row['member']->npa ?: '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm font-semibold text-slate-900">{{ $row['member']->full_name }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">{{ $row['member']->department?->name ?? '-' }}</td>
                                <td class="px-4 py-4"><span class="{{ $statusBadgeClasses['present'] }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ number_format($row['counts']['present']) }}</span></td>
                                <td class="px-4 py-4"><span class="{{ $statusBadgeClasses['permission'] }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ number_format($row['counts']['permission']) }}</span></td>
                                <td class="px-4 py-4"><span class="{{ $statusBadgeClasses['absent'] }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ number_format($row['counts']['absent']) }}</span></td>
                                <td class="px-4 py-4"><span class="{{ $statusBadgeClasses['need_verification'] }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ number_format($row['counts']['need_verification']) }}</span></td>
                                <td class="whitespace-nowrap px-4 py-4"><span class="{{ $percentageClass($row['attendance_percentage']) }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ number_format($row['attendance_percentage'], 2) }}%</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-10 text-center text-sm text-slate-500">Belum ada anggota aktif sesuai filter.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const reportCharts = @json($chartData);
        const chartColors = ['#059669', '#0284c7', '#64748b', '#d97706', '#7c3aed', '#0f766e'];

        if (document.getElementById('statusCompositionChart')) {
            new Chart(document.getElementById('statusCompositionChart'), {
                type: 'doughnut',
                data: {
                    labels: reportCharts.statusComposition.labels,
                    datasets: [{ data: reportCharts.statusComposition.data, backgroundColor: chartColors.slice(0, 4) }],
                },
                options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } },
            });
        }

        if (document.getElementById('activityTrendChart')) {
            new Chart(document.getElementById('activityTrendChart'), {
                type: 'bar',
                data: {
                    labels: reportCharts.activityTrend.labels,
                    datasets: [{ label: 'Hadir', data: reportCharts.activityTrend.data, backgroundColor: '#059669' }],
                },
                options: { maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } },
            });
        }

        if (document.getElementById('departmentAttendanceChart')) {
            new Chart(document.getElementById('departmentAttendanceChart'), {
                type: 'bar',
                data: {
                    labels: reportCharts.departmentAttendance.labels,
                    datasets: [{ label: 'Hadir', data: reportCharts.departmentAttendance.data, backgroundColor: '#0284c7' }],
                },
                options: { maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } },
            });
        }
    </script>
@endsection
