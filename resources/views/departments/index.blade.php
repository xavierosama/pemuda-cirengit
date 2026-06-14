@extends('layouts.admin')

@section('title', 'Data Bidang - Pemuda Cirengit')
@section('section', 'Master Data')
@section('page-title', 'Data Bidang')

@section('content')
    @php
        $summaryCards = [
            ['label' => 'Total Bidang', 'value' => $departmentStats['total'], 'class' => 'bg-slate-50 text-slate-700 ring-slate-200'],
            ['label' => 'Bidang Aktif', 'value' => $departmentStats['active'], 'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-100'],
            ['label' => 'Bidang Nonaktif', 'value' => $departmentStats['inactive'], 'class' => 'bg-slate-50 text-slate-700 ring-slate-200'],
        ];
    @endphp

    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">Master Data</p>
                    <h2 class="mt-2 text-2xl font-bold text-slate-950">Data Bidang</h2>
                    <p class="mt-2 max-w-2xl text-sm text-slate-500">Kelola bidang organisasi Pemuda Persis Cirengit.</p>
                </div>
                <a href="{{ route('departments.create') }}" class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">Tambah Bidang</a>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
        @endif

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
                <h3 class="text-base font-bold text-slate-950">Filter Data Bidang</h3>
                <p class="mt-1 text-sm text-slate-500">Cari bidang berdasarkan nama atau status aktif.</p>
            </div>
            <form method="GET" action="{{ route('departments.index') }}" class="grid gap-4 lg:grid-cols-12">
                <input type="hidden" name="sort" value="{{ $currentSort }}">
                <input type="hidden" name="direction" value="{{ $currentDirection }}">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <div class="lg:col-span-7">
                    <label for="search" class="text-sm font-semibold text-slate-700">Search nama bidang</label>
                    <input id="search" name="search" type="search" value="{{ $search }}" placeholder="Cari nama bidang" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
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
                    <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700">Terapkan Filter</button>
                    <a href="{{ route('departments.index') }}" class="inline-flex flex-1 items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Reset</a>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-950">Tabel Data Bidang</h3>
                    <p class="mt-1 text-sm text-slate-500">Daftar bidang organisasi dan jumlah anggota yang terhubung.</p>
                </div>
                <x-per-page-selector :per-page="$perPage" :options="$perPageOptions" :query="$queryParams" />
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">No</th>
                            <x-sortable-th field="name" label="Nama Bidang" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Deskripsi</th>
                            <x-sortable-th field="status" label="Status" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Jumlah Anggota</th>
                            <x-sortable-th field="created_at" label="Dibuat" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <th class="whitespace-nowrap px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($departments as $department)
                            <tr class="transition hover:bg-slate-50/70">
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-500">{{ $departments->firstItem() + $loop->index }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm font-semibold text-slate-900">{{ $department->name }}</td>
                                <td class="max-w-md px-4 py-4 text-sm text-slate-600">{{ str($department->description ?: '-')->limit(90) }}</td>
                                <td class="whitespace-nowrap px-4 py-4">
                                    <span class="{{ $department->status === 'active' ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-slate-100 text-slate-600 ring-slate-200' }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">
                                        {{ $department->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm font-semibold text-slate-700">{{ number_format($department->members_count) }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">{{ $department->created_at?->format('d/m/Y') ?? '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-semibold">
                                    <div class="flex justify-end gap-1.5">
                                        <x-action-icon :href="route('departments.show', $department)" label="Detail" icon="eye" variant="blue" />
                                        <x-action-icon :href="route('departments.edit', $department)" label="Edit" icon="pencil" variant="amber" />
                                        <x-action-icon
                                            :action="route('departments.destroy', $department)"
                                            method="DELETE"
                                            label="Hapus"
                                            icon="trash"
                                            variant="red"
                                            confirm="Yakin ingin menghapus data ini?"
                                            confirm-title="Hapus Data?"
                                            confirm-description="Data bidang akan dihapus dari sistem. Pastikan bidang ini tidak lagi digunakan."
                                            confirm-text="Hapus"
                                            confirm-variant="danger"
                                        />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-14 text-center">
                                    <p class="text-base font-semibold text-slate-800">Belum ada data bidang.</p>
                                    <p class="mt-1 text-sm text-slate-500">Tambahkan bidang baru atau ubah filter pencarian.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $departments->links() }}
    </div>
@endsection
