@extends('layouts.admin')

@section('title', 'Data Bidang - Pemuda Cirengit')
@section('section', 'Master Data')
@section('page-title', 'Data Bidang')

@section('content')
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-950">Data Bidang</h2>
                <p class="mt-1 text-sm text-slate-500">Kelola daftar bidang organisasi Pemuda Persis Cirengit.</p>
            </div>
            <a href="{{ route('departments.create') }}" class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
                Tambah Bidang
            </a>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <form method="GET" action="{{ route('departments.index') }}" class="grid gap-3 md:grid-cols-[1fr_220px_auto]">
                <div>
                    <label for="search" class="sr-only">Cari nama bidang</label>
                    <input
                        id="search"
                        name="search"
                        type="search"
                        value="{{ $search }}"
                        placeholder="Cari nama bidang"
                        class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600"
                    >
                </div>
                <div>
                    <label for="status" class="sr-only">Filter status</label>
                    <select id="status" name="status" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                        <option value="">Semua status</option>
                        <option value="active" @selected($status === 'active')>Active</option>
                        <option value="inactive" @selected($status === 'inactive')>Inactive</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700 md:flex-none">
                        Filter
                    </button>
                    <a href="{{ route('departments.index') }}" class="inline-flex flex-1 items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 md:flex-none">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Nama Bidang</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Deskripsi Singkat</th>
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($departments as $department)
                            <tr>
                                <td class="whitespace-nowrap px-4 py-4 text-sm font-semibold text-slate-900">{{ $department->name }}</td>
                                <td class="px-4 py-4 text-sm text-slate-600">
                                    {{ str($department->description ?: '-')->limit(90) }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-4">
                                    <span class="{{ $department->status === 'active' ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-slate-100 text-slate-600 ring-slate-200' }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">
                                        {{ ucfirst($department->status) }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-semibold">
                                    <div class="flex justify-end gap-2">
                                        <x-action-icon :href="route('departments.show', $department)" label="Detail" icon="eye" variant="blue" />
                                        <x-action-icon :href="route('departments.edit', $department)" label="Edit" icon="pencil" variant="amber" />
                                        <x-action-icon :action="route('departments.destroy', $department)" method="DELETE" label="Hapus" icon="trash" variant="red" confirm="Yakin ingin menghapus data ini?" />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-10 text-center text-sm text-slate-500">Belum ada data bidang.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $departments->links() }}
    </div>
@endsection
