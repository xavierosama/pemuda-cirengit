@extends('layouts.admin')

@section('title', 'Lokasi Kegiatan - Pemuda Cirengit')
@section('section', 'Master Data')
@section('page-title', 'Lokasi Kegiatan')
@section('breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Lokasi Kegiatan'],
    ]" />
@endsection

@section('content')
    @php
        $summaryCards = [
            ['label' => 'Total Lokasi', 'value' => $locationStats['total'], 'class' => 'bg-slate-50 text-slate-700 ring-slate-200'],
            ['label' => 'Lokasi Aktif', 'value' => $locationStats['active'], 'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-100'],
            ['label' => 'Lokasi Nonaktif', 'value' => $locationStats['inactive'], 'class' => 'bg-slate-50 text-slate-700 ring-slate-200'],
        ];
    @endphp

    <div class="space-y-6">
        <x-ui.page-header
            eyebrow="Master Data"
            title="Lokasi Kegiatan"
            description="Kelola lokasi default, koordinat, dan radius presensi agar Jadwal Agenda lebih cepat dibuat."
        >
            <x-slot:action>
                <x-ui.button :href="route('activity-locations.create')">Tambah Lokasi</x-ui.button>
            </x-slot:action>
        </x-ui.page-header>

        <div class="grid gap-4 sm:grid-cols-3">
            @foreach ($summaryCards as $card)
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="{{ $card['class'] }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ $card['label'] }}</div>
                    <p class="mt-4 text-3xl font-bold text-slate-950">{{ number_format($card['value']) }}</p>
                </div>
            @endforeach
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4">
                <h3 class="text-base font-bold text-slate-950">Filter Lokasi Kegiatan</h3>
                <p class="mt-1 text-sm text-slate-500">Cari berdasarkan nama/alamat lokasi atau status aktif.</p>
            </div>
            <form method="GET" action="{{ route('activity-locations.index') }}" class="grid gap-4 lg:grid-cols-12">
                <input type="hidden" name="sort" value="{{ $currentSort }}">
                <input type="hidden" name="direction" value="{{ $currentDirection }}">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <div class="lg:col-span-7">
                    <label for="search" class="text-sm font-semibold text-slate-700">Search lokasi</label>
                    <input id="search" name="search" type="search" value="{{ $search }}" placeholder="Cari nama atau alamat lokasi" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                </div>
                <div class="lg:col-span-3">
                    <label for="status" class="text-sm font-semibold text-slate-700">Status</label>
                    <select id="status" name="status" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                        <option value="">Semua status</option>
                        <option value="active" @selected($status === 'active')>Aktif</option>
                        <option value="inactive" @selected($status === 'inactive')>Nonaktif</option>
                    </select>
                </div>
                <div class="flex gap-2 lg:col-span-2 lg:items-end">
                    <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700">Terapkan</button>
                    <a href="{{ route('activity-locations.index') }}" class="inline-flex flex-1 items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Reset</a>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-950">Tabel Lokasi Kegiatan</h3>
                    <p class="mt-1 text-sm text-slate-500">Lokasi aktif akan muncul pada dropdown Jadwal Agenda.</p>
                </div>
                <x-per-page-selector :per-page="$perPage" :options="$perPageOptions" :query="$queryParams" />
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">No</th>
                            <x-sortable-th field="name" label="Nama Lokasi" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Koordinat</th>
                            <x-sortable-th field="radius_meters" label="Radius" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Dipakai</th>
                            <x-sortable-th field="is_active" label="Status" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <x-sortable-th field="created_at" label="Dibuat" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <th class="whitespace-nowrap px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($activityLocations as $location)
                            <tr class="align-top transition hover:bg-slate-50/70">
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-500">{{ $activityLocations->firstItem() + $loop->index }}</td>
                                <td class="max-w-sm px-3 py-4">
                                    <p class="line-clamp-2 break-words text-sm font-semibold text-slate-900">{{ $location->name }}</p>
                                    @if ($location->address)
                                        <p class="mt-1 line-clamp-2 break-words text-xs text-slate-500">{{ $location->address }}</p>
                                    @endif
                                </td>
                                <td class="px-3 py-4 text-sm text-slate-600">
                                    <span class="block whitespace-nowrap">{{ $location->latitude ?: '-' }}</span>
                                    <span class="block whitespace-nowrap">{{ $location->longitude ?: '-' }}</span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm font-semibold text-slate-700">{{ $location->radius_meters }} m</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-600">{{ number_format($location->agenda_schedules_count) }} jadwal</td>
                                <td class="whitespace-nowrap px-3 py-4">
                                    <x-ui.status-badge :status="$location->is_active ? 'active' : 'inactive'" :label="$location->is_active ? 'Aktif' : 'Nonaktif'" />
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-600">{{ \App\Support\DateFormatter::date($location->created_at) }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-right text-sm font-semibold">
                                    <div class="flex justify-end gap-1.5">
                                        <x-action-icon :href="route('activity-locations.show', $location)" label="Detail" icon="eye" variant="blue" />
                                        <x-ui.action-dropdown>
                                            <x-ui.action-dropdown-item :href="route('activity-locations.edit', $location)" label="Edit" icon="pencil" />
                                            @if ($location->is_active)
                                                <x-ui.action-dropdown-item
                                                    :action="route('activity-locations.deactivate', $location)"
                                                    method="PATCH"
                                                    label="Nonaktifkan"
                                                    icon="ban"
                                                    variant="warning"
                                                    confirm="Nonaktifkan lokasi kegiatan ini?"
                                                    confirm-title="Nonaktifkan Lokasi?"
                                                    confirm-description="Lokasi tidak akan muncul pada dropdown Jadwal Agenda baru, tetapi jadwal lama tetap aman."
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
                                <td colspan="8">
                                    <x-ui.empty-state title="Belum ada lokasi kegiatan." description="Tambahkan lokasi pertama agar Jadwal Agenda bisa memilih lokasi dan koordinat otomatis." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $activityLocations->links() }}
    </div>
@endsection
