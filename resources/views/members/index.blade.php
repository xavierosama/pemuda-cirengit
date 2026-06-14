@extends('layouts.admin')

@section('title', 'Data Anggota - Pemuda Cirengit')
@section('section', 'Master Data')
@section('page-title', 'Data Anggota')
@section('breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Data Anggota'],
    ]" />
@endsection

@section('content')
    @php
        $statusLabels = ['active' => 'Aktif', 'inactive' => 'Tidak Aktif', 'alumni' => 'Alumni', 'moved' => 'Pindah'];
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
        <x-ui.page-header
            title="Data Anggota"
            eyebrow="Master Data"
            description="Kelola data anggota, NPA, bidang, jabatan, dan akun login anggota."
        >
            <x-slot name="action">
                <x-ui.button :href="route('members.create')">Tambah Anggota</x-ui.button>
                <x-ui.button :href="route('members.import')" variant="secondary">Import Excel</x-ui.button>
                <x-ui.button :href="route('members.import.template')" variant="secondary">Download Template</x-ui.button>
                <x-ui.button :href="route('members.export', request()->only(['search', 'department_id', 'position_id', 'member_status']))" variant="secondary">Export Excel</x-ui.button>
            </x-slot>
        </x-ui.page-header>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-6">
            @foreach ($summaryCards as $card)
                <x-ui.card padding="sm">
                    <div class="{{ $card['class'] }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ $card['label'] }}</div>
                    <p class="mt-4 text-3xl font-bold text-slate-950">{{ number_format($card['value']) }}</p>
                </x-ui.card>
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
                                    <x-ui.status-badge :status="$member->member_status" :label="$statusLabels[$member->member_status] ?? $member->member_status" />
                                </td>
                                <td class="whitespace-nowrap px-4 py-4">
                                    <x-ui.status-badge :status="$member->user ? 'account_exists' : 'account_missing'" />
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-right text-sm font-semibold">
                                    <div class="flex justify-end gap-1.5">
                                        <x-ui.action-icon :href="route('members.show', $member)" label="Detail" variant="detail" />
                                        <x-ui.action-icon :href="route('members.edit', $member)" label="Edit" variant="edit" />
                                        @if ($member->user)
                                            <x-ui.action-icon
                                                :action="route('members.account.reset-password', $member)"
                                                method="PATCH"
                                                label="Reset Password"
                                                variant="reset"
                                                confirm="Reset password akun ini menjadi password?"
                                                confirm-title="Reset Password?"
                                                confirm-description="Password akun anggota ini akan direset. Informasikan password baru kepada anggota terkait."
                                                confirm-text="Reset Password"
                                                confirm-variant="warning"
                                            />
                                        @else
                                            <x-ui.action-icon :action="route('members.account.store', $member)" label="Buat Akun" variant="account" />
                                        @endif
                                        <x-ui.action-icon
                                            :action="route('members.destroy', $member)"
                                            method="DELETE"
                                            label="Hapus"
                                            variant="delete"
                                            confirm="Yakin ingin menghapus data ini?"
                                            confirm-title="Hapus Data?"
                                            confirm-description="Data anggota akan dihapus dari sistem. Pastikan data ini tidak lagi diperlukan."
                                            confirm-text="Hapus"
                                            confirm-variant="danger"
                                        />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">
                                    <x-ui.empty-state title="Belum ada data anggota." description="Tambahkan anggota baru atau ubah filter pencarian untuk melihat data lain." />
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
