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
        $summaryCards = [
            ['label' => 'Total Anggota Aktif', 'value' => $memberStats['active'], 'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-100'],
            ['label' => 'Total Anggota Nonaktif', 'value' => $memberStats['inactive'], 'class' => 'bg-slate-50 text-slate-700 ring-slate-200'],
            ['label' => 'Total Alumni', 'value' => $memberStats['alumni'], 'class' => 'bg-sky-50 text-sky-700 ring-sky-100'],
            ['label' => 'Total Mutasi/Pindah', 'value' => $memberStats['moved'], 'class' => 'bg-amber-50 text-amber-700 ring-amber-100'],
            ['label' => 'Total Sudah Punya Akun', 'value' => $memberStats['account_exists'], 'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-100'],
            ['label' => 'Total Belum Punya Akun', 'value' => $memberStats['account_missing'], 'class' => 'bg-rose-50 text-rose-700 ring-rose-100'],
        ];
    @endphp

    <div class="space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">Master Data</p>
                    <h2 class="mt-2 text-2xl font-bold text-slate-950">Data Anggota</h2>
                    <p class="mt-2 max-w-2xl text-sm text-slate-500">Kelola data anggota, NPA, bidang, jabatan, dan akun login anggota.</p>
                </div>
                <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap lg:justify-end">
                    <a href="{{ route('members.create') }}" class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">Tambah Anggota</a>
                    <a href="{{ route('members.import') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">Import Excel</a>
                    <a href="{{ route('members.import.template') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">Download Template</a>
                    <a href="{{ route('members.export', request()->only(['search', 'department_id', 'position_id', 'member_status'])) }}" class="inline-flex items-center justify-center rounded-lg border border-emerald-600 px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">Export Excel</a>
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
        @endif

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
            @foreach ($summaryCards as $card)
                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="{{ $card['class'] }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ $card['label'] }}</div>
                    <p class="mt-4 text-3xl font-bold text-slate-950">{{ number_format($card['value']) }}</p>
                </div>
            @endforeach
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="mb-4">
                <h3 class="text-base font-bold text-slate-950">Filter Data Anggota</h3>
                <p class="mt-1 text-sm text-slate-500">Cari dan saring data berdasarkan identitas, bidang, jabatan, status anggota, atau akun login.</p>
            </div>
            <form method="GET" action="{{ route('members.index') }}" class="grid gap-4 lg:grid-cols-12">
                <input type="hidden" name="sort" value="{{ $currentSort }}">
                <input type="hidden" name="direction" value="{{ $currentDirection }}">
                <input type="hidden" name="per_page" value="{{ $perPage }}">
                <div class="lg:col-span-4">
                    <label for="search" class="text-sm font-semibold text-slate-700">Search nama / NPA / email / no HP</label>
                    <input id="search" name="search" type="search" value="{{ $search }}" placeholder="Contoh: Ahmad, 20.0001, email, no HP" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                </div>
                <div class="lg:col-span-2">
                    <label for="department_id" class="text-sm font-semibold text-slate-700">Bidang</label>
                    <select id="department_id" name="department_id" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                        <option value="">Semua bidang</option>
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected((string) $departmentId === (string) $department->id)>{{ $department->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="lg:col-span-2">
                    <label for="position_id" class="text-sm font-semibold text-slate-700">Jabatan</label>
                    <select id="position_id" name="position_id" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                        <option value="">Semua jabatan</option>
                        @foreach ($positions as $position)
                            <option value="{{ $position->id }}" @selected((string) $positionId === (string) $position->id)>{{ $position->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="lg:col-span-2">
                    <label for="member_status" class="text-sm font-semibold text-slate-700">Status anggota</label>
                    <select id="member_status" name="member_status" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                        <option value="">Semua status</option>
                        @foreach ($statusLabels as $value => $label)
                            <option value="{{ $value }}" @selected($memberStatus === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="lg:col-span-2">
                    <label for="account_status" class="text-sm font-semibold text-slate-700">Status akun</label>
                    <select id="account_status" name="account_status" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                        <option value="">Semua akun</option>
                        <option value="exists" @selected($accountStatus === 'exists')>Sudah Ada</option>
                        <option value="missing" @selected($accountStatus === 'missing')>Belum Ada</option>
                    </select>
                </div>
                <div class="flex gap-2 lg:col-span-12 lg:justify-end">
                    <button type="submit" class="inline-flex flex-1 items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-700 sm:flex-none">Terapkan Filter</button>
                    <a href="{{ route('members.index') }}" class="inline-flex flex-1 items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 sm:flex-none">Reset</a>
                </div>
            </form>
        </div>

        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-200 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-base font-bold text-slate-950">Tabel Data Anggota</h3>
                    <p class="mt-1 text-sm text-slate-500">Daftar anggota sesuai filter aktif. Gunakan scroll horizontal pada layar kecil.</p>
                </div>
                <x-per-page-selector :per-page="$perPage" :options="$perPageOptions" :query="$queryParams" />
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">No</th>
                            <x-sortable-th field="npa" label="NPA" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <x-sortable-th field="full_name" label="Nama Anggota" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Jabatan</th>
                            <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">No HP</th>
                            <x-sortable-th field="email" label="Email" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <x-sortable-th field="member_status" label="Status Anggota" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Status Akun</th>
                            <th class="whitespace-nowrap px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($members as $member)
                            <tr class="transition hover:bg-slate-50/70">
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-500">{{ $members->firstItem() + $loop->index }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm font-semibold text-slate-700">{{ $member->npa ?: '-' }}</td>
                                <td class="px-4 py-4">
                                    <p class="whitespace-nowrap text-sm font-semibold text-slate-900">{{ $member->full_name }}</p>
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">{{ $member->position?->name ?? '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">{{ $member->phone ?: '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">{{ $member->email ?: '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-4">
                                    <span class="{{ $statusClasses[$member->member_status] ?? $statusClasses['inactive'] }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ $statusLabels[$member->member_status] ?? $member->member_status }}</span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-4">
                                    <span class="{{ $member->user ? 'bg-emerald-50 text-emerald-700 ring-emerald-200' : 'bg-amber-50 text-amber-700 ring-amber-200' }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ $member->user ? 'Sudah Ada' : 'Belum Ada' }}</span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-semibold">
                                    <div class="flex justify-end gap-1.5">
                                        <x-action-icon :href="route('members.show', $member)" label="Detail" icon="eye" variant="blue" />
                                        <x-action-icon :href="route('members.edit', $member)" label="Edit" icon="pencil" variant="amber" />
                                        @if ($member->user)
                                            <x-action-icon :action="route('members.account.reset-password', $member)" method="PATCH" label="Reset Password" icon="key" variant="violet" confirm="Reset password akun ini menjadi password?" />
                                        @else
                                            <x-action-icon :action="route('members.account.store', $member)" label="Buat Akun" icon="user-plus" variant="emerald" />
                                        @endif
                                        <x-action-icon :action="route('members.destroy', $member)" method="DELETE" label="Hapus" icon="trash" variant="red" confirm="Yakin ingin menghapus data ini?" />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-14 text-center">
                                    <div class="mx-auto max-w-sm">
                                        <p class="text-base font-semibold text-slate-800">Belum ada data anggota.</p>
                                        <p class="mt-1 text-sm text-slate-500">Tambahkan anggota baru atau ubah filter pencarian untuk melihat data lain.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $members->links() }}
    </div>
@endsection
