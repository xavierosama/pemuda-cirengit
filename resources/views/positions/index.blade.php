@extends('layouts.admin')

@section('title', 'Data Jabatan - Pemuda Cirengit')
@section('section', 'Master Data')
@section('page-title', 'Data Jabatan')

@section('content')
    @php
        $summaryCards = [
            ['label' => 'Total Jabatan', 'value' => $positionStats['total'], 'class' => 'bg-slate-50 text-slate-700 ring-slate-200'],
            ['label' => 'Jabatan Aktif', 'value' => $positionStats['active'], 'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-100'],
            ['label' => 'Jabatan Nonaktif', 'value' => $positionStats['inactive'], 'class' => 'bg-slate-50 text-slate-700 ring-slate-200'],
        ];
    @endphp

    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">Master Data</p>
                    <h2 class="mt-2 text-2xl font-bold text-slate-950">Data Jabatan</h2>
                    <p class="mt-2 max-w-2xl text-sm text-slate-500">Kelola jabatan dan posisi anggota dalam organisasi.</p>
                </div>
                <a href="{{ route('positions.create') }}" class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">Tambah Jabatan</a>
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
                <h3 class="text-base font-bold text-slate-950">Filter Data Jabatan</h3>
                <p class="mt-1 text-sm text-slate-500">Cari jabatan berdasarkan nama atau status aktif.</p>
            </div>
            <form method="GET" action="{{ route('positions.index') }}" class="grid gap-4 lg:grid-cols-12">
                <input type="hidden" name="sort" value="{{ $currentSort }}">
                <input type="hidden" name="direction" value="{{ $currentDirection }}">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <div class="lg:col-span-7">
                    <label for="search" class="text-sm font-semibold text-slate-700">Search nama jabatan</label>
                    <input id="search" name="search" type="search" value="{{ $search }}" placeholder="Cari nama jabatan" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
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
                    <a href="{{ route('positions.index') }}" class="inline-flex flex-1 items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Reset</a>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-950">Tabel Data Jabatan</h3>
                    <p class="mt-1 text-sm text-slate-500">Daftar jabatan organisasi dan jumlah anggota yang terhubung.</p>
                </div>
                <x-per-page-selector :per-page="$perPage" :options="$perPageOptions" :query="$queryParams" />
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">No</th>
                            <x-sortable-th field="name" label="Nama Jabatan" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Deskripsi</th>
                            <x-sortable-th field="status" label="Status" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Jumlah Anggota</th>
                            <x-sortable-th field="created_at" label="Dibuat" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <th class="whitespace-nowrap px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($positions as $position)
                            <tr class="transition hover:bg-slate-50/70">
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-500">{{ $positions->firstItem() + $loop->index }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm font-semibold text-slate-900">{{ $position->name }}</td>
                                <td class="max-w-md px-4 py-4 text-sm text-slate-600">{{ str($position->description ?: '-')->limit(90) }}</td>
                                <td class="whitespace-nowrap px-4 py-4">
                                    <span class="{{ $position->status === 'active' ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-slate-100 text-slate-600 ring-slate-200' }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">
                                        {{ $position->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm font-semibold text-slate-700">{{ number_format($position->members_count) }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">{{ $position->created_at?->format('d/m/Y') ?? '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-semibold">
                                    <div class="flex justify-end gap-1.5">
                                        <x-action-icon :href="route('positions.show', $position)" label="Detail" icon="eye" variant="blue" />
                                        <x-action-icon :href="route('positions.edit', $position)" label="Edit" icon="pencil" variant="amber" />
                                        <x-action-icon :action="route('positions.destroy', $position)" method="DELETE" label="Hapus" icon="trash" variant="red" confirm="Yakin ingin menghapus data ini?" />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-14 text-center">
                                    <p class="text-base font-semibold text-slate-800">Belum ada data jabatan.</p>
                                    <p class="mt-1 text-sm text-slate-500">Tambahkan jabatan baru atau ubah filter pencarian.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $positions->links() }}
    </div>
@endsection
