@extends('layouts.admin')

@section('title', 'Data Anggota - Pemuda Cirengit')
@section('section', 'Master Data')
@section('page-title', 'Data Anggota')

@section('content')
    @php
        $statusLabels = ['active' => 'Aktif', 'inactive' => 'Tidak Aktif', 'alumni' => 'Alumni', 'moved' => 'Pindah'];
        $statusClasses = [
            'active' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'inactive' => 'bg-slate-100 text-slate-600 ring-slate-200',
            'alumni' => 'bg-sky-50 text-sky-700 ring-sky-200',
            'moved' => 'bg-amber-50 text-amber-700 ring-amber-200',
        ];
    @endphp

    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-950">Data Anggota</h2>
                <p class="mt-1 text-sm text-slate-500">Kelola profil dan penempatan anggota Pemuda Persis Cirengit.</p>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row">
                <a href="{{ route('members.import') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">Import Data Anggota</a>
                <a href="{{ route('members.create') }}" class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">Tambah Anggota</a>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
        @endif

        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <form method="GET" action="{{ route('members.index') }}" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-[minmax(220px,1fr)_190px_190px_170px_auto]">
                <input name="search" type="search" value="{{ $search }}" placeholder="Cari nama, HP, atau email" aria-label="Cari anggota" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                <select name="department_id" aria-label="Filter bidang" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                    <option value="">Semua bidang</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected((string) $departmentId === (string) $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
                <select name="position_id" aria-label="Filter jabatan" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                    <option value="">Semua jabatan</option>
                    @foreach ($positions as $position)
                        <option value="{{ $position->id }}" @selected((string) $positionId === (string) $position->id)>{{ $position->name }}</option>
                    @endforeach
                </select>
                <select name="member_status" aria-label="Filter status anggota" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                    <option value="">Semua status</option>
                    @foreach ($statusLabels as $value => $label)
                        <option value="{{ $value }}" @selected($memberStatus === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <div class="flex gap-2 sm:col-span-2 xl:col-span-1">
                    <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700">Filter</button>
                    <a href="{{ route('members.index') }}" class="inline-flex flex-1 items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Reset</a>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            @foreach (['Nama', 'NPA', 'Bidang', 'Jabatan', 'No HP', 'Status', 'Status Akun'] as $heading)
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">{{ $heading }}</th>
                            @endforeach
                            <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($members as $member)
                            <tr>
                                <td class="px-4 py-4">
                                    <p class="whitespace-nowrap text-sm font-semibold text-slate-900">{{ $member->full_name }}</p>
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">{{ $member->npa ?: '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">{{ $member->department?->name ?? '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">{{ $member->position?->name ?? '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">{{ $member->phone ?: '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-4">
                                    <span class="{{ $statusClasses[$member->member_status] }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ $statusLabels[$member->member_status] }}</span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-4">
                                    <span class="{{ $member->user ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-slate-100 text-slate-600 ring-slate-200' }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ $member->user ? 'Sudah Ada' : 'Belum Ada' }}</span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-semibold">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('members.show', $member) }}" class="rounded-md px-2.5 py-1.5 text-slate-700 transition hover:bg-slate-100">Detail</a>
                                        <a href="{{ route('members.edit', $member) }}" class="rounded-md px-2.5 py-1.5 text-emerald-700 transition hover:bg-emerald-50">Edit</a>
                                        <form method="POST" action="{{ route('members.destroy', $member) }}" onsubmit="return confirm('Hapus data anggota ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="rounded-md px-2.5 py-1.5 text-red-700 transition hover:bg-red-50">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="px-4 py-10 text-center text-sm text-slate-500">Belum ada data anggota.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $members->links() }}
    </div>
@endsection
