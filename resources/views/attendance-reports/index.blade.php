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
        $summaryCards = [
            ['label' => 'Total Kegiatan', 'value' => $summary['total_activities'], 'note' => 'Kegiatan dalam periode'],
            ['label' => 'Total Anggota Aktif', 'value' => $summary['total_active_members'], 'note' => 'Sesuai filter bidang'],
            ['label' => 'Total Kehadiran', 'value' => $summary['present'], 'note' => 'Status hadir'],
            ['label' => 'Total Izin', 'value' => $summary['permission'], 'note' => 'Status izin'],
            ['label' => 'Total Tidak Hadir', 'value' => $summary['absent'], 'note' => 'Status tidak hadir'],
            ['label' => 'Total Perlu Verifikasi', 'value' => $summary['need_verification'], 'note' => 'Butuh keputusan admin'],
            ['label' => 'Persentase Kehadiran', 'value' => number_format($summary['attendance_percentage'], 2).'%', 'note' => 'Hadir / potensi kehadiran'],
        ];
    @endphp

    <div class="space-y-6">
        <section class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
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

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4 2xl:grid-cols-7">
            @foreach ($summaryCards as $card)
                <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <p class="text-sm font-medium text-slate-500">{{ $card['label'] }}</p>
                    <p class="mt-3 text-2xl font-bold text-slate-950">{{ is_numeric($card['value']) ? number_format($card['value']) : $card['value'] }}</p>
                    <p class="mt-2 text-xs text-slate-500">{{ $card['note'] }}</p>
                </div>
            @endforeach
        </section>

        <section class="grid gap-6 xl:grid-cols-3">
            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-base font-bold text-slate-950">Komposisi Status Kehadiran</h2>
                <div class="mt-5 h-72"><canvas id="statusCompositionChart"></canvas></div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-base font-bold text-slate-950">Tren Kehadiran per Kegiatan</h2>
                <div class="mt-5 h-72"><canvas id="activityTrendChart"></canvas></div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                <h2 class="text-base font-bold text-slate-950">Kehadiran per Bidang</h2>
                <div class="mt-5 h-72"><canvas id="departmentAttendanceChart"></canvas></div>
            </div>
        </section>

        <section class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="text-base font-bold text-slate-950">Ringkasan per Kegiatan</h2>
                <p class="mt-1 text-sm text-slate-500">Persentase dihitung dari hadir dibagi jumlah anggota aktif dalam filter.</p>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50"><tr>
                        @foreach (['Tanggal', 'Nama Kegiatan', 'Bidang', 'Total Hadir', 'Total Izin', 'Total Tidak Hadir', 'Total Perlu Verifikasi', 'Persentase Kehadiran'] as $heading)
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">{{ $heading }}</th>
                        @endforeach
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($activityRows as $row)
                            <tr>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">{{ $row['activity']->activity_date->format('d/m/Y') }}</td>
                                <td class="px-4 py-4 text-sm font-semibold text-slate-900">{{ $row['activity']->title }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">{{ $row['activity']->department?->name ?? '-' }}</td>
                                <td class="px-4 py-4 text-sm text-slate-600">{{ number_format($row['counts']['present']) }}</td>
                                <td class="px-4 py-4 text-sm text-slate-600">{{ number_format($row['counts']['permission']) }}</td>
                                <td class="px-4 py-4 text-sm text-slate-600">{{ number_format($row['counts']['absent']) }}</td>
                                <td class="px-4 py-4 text-sm text-slate-600">{{ number_format($row['counts']['need_verification']) }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm font-semibold text-emerald-700">{{ number_format($row['attendance_percentage'], 2) }}%</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-10 text-center text-sm text-slate-500">Belum ada kegiatan pada periode ini.</td></tr>
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
                    <thead class="bg-slate-50"><tr>
                        @foreach (['NPA', 'Nama Anggota', 'Bidang', 'Total Hadir', 'Total Izin', 'Total Tidak Hadir', 'Total Perlu Verifikasi', 'Persentase Kehadiran'] as $heading)
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">{{ $heading }}</th>
                        @endforeach
                    </tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($memberRows as $row)
                            <tr>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">{{ $row['member']->npa ?: '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm font-semibold text-slate-900">{{ $row['member']->full_name }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">{{ $row['member']->department?->name ?? '-' }}</td>
                                <td class="px-4 py-4 text-sm text-slate-600">{{ number_format($row['counts']['present']) }}</td>
                                <td class="px-4 py-4 text-sm text-slate-600">{{ number_format($row['counts']['permission']) }}</td>
                                <td class="px-4 py-4 text-sm text-slate-600">{{ number_format($row['counts']['absent']) }}</td>
                                <td class="px-4 py-4 text-sm text-slate-600">{{ number_format($row['counts']['need_verification']) }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm font-semibold text-emerald-700">{{ number_format($row['attendance_percentage'], 2) }}%</td>
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
        const chartColors = ['#059669', '#0284c7', '#dc2626', '#d97706', '#7c3aed', '#0f766e'];

        new Chart(document.getElementById('statusCompositionChart'), {
            type: 'doughnut',
            data: {
                labels: reportCharts.statusComposition.labels,
                datasets: [{ data: reportCharts.statusComposition.data, backgroundColor: chartColors.slice(0, 4) }],
            },
            options: { maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } },
        });

        new Chart(document.getElementById('activityTrendChart'), {
            type: 'bar',
            data: {
                labels: reportCharts.activityTrend.labels,
                datasets: [{ label: 'Hadir', data: reportCharts.activityTrend.data, backgroundColor: '#059669' }],
            },
            options: { maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } },
        });

        new Chart(document.getElementById('departmentAttendanceChart'), {
            type: 'bar',
            data: {
                labels: reportCharts.departmentAttendance.labels,
                datasets: [{ label: 'Hadir', data: reportCharts.departmentAttendance.data, backgroundColor: '#0284c7' }],
            },
            options: { maintainAspectRatio: false, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } },
        });
    </script>
@endsection
