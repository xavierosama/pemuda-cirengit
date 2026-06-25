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
        $statusLabels = ['active' => 'Aktif', 'inactive' => 'Tidak Aktif', 'alumni' => 'Tidak Aktif', 'moved' => 'Tidak Aktif'];
        $summaryCards = [
            ['label' => 'Total Anggota', 'value' => $memberStats['total'], 'class' => 'bg-slate-50 text-slate-700 ring-slate-200'],
            ['label' => 'Total Anggota Aktif', 'value' => $memberStats['active'], 'class' => 'bg-emerald-50 text-emerald-700 ring-emerald-100'],
            ['label' => 'Total Anggota Nonaktif', 'value' => $memberStats['inactive'], 'class' => 'bg-slate-50 text-slate-700 ring-slate-200'],
            ['label' => 'Perlu Diproses Batas Usia', 'value' => $memberStats['age_limit_due'], 'class' => 'bg-amber-50 text-amber-700 ring-amber-100'],
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
                <x-ui.button :href="route('members.import.template')" variant="secondary" loading-text="Menyiapkan file..." x-data="{ submitting: false }" x-on:click="submitting = true" x-bind:class="{ 'pointer-events-none opacity-80': submitting }">Download Template</x-ui.button>
                <x-ui.button :href="route('members.export', request()->only(['search', 'department_id', 'position_id', 'member_status']))" variant="secondary" loading-text="Menyiapkan file..." x-data="{ submitting: false }" x-on:click="submitting = true" x-bind:class="{ 'pointer-events-none opacity-80': submitting }">Export Excel</x-ui.button>
            </x-slot>
        </x-ui.page-header>

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
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
                <div class="lg:col-span-3">
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
                        <option value="active" @selected($memberStatus === 'active')>Aktif</option>
                        <option value="inactive" @selected($memberStatus === 'inactive')>Tidak Aktif</option>
                        <option value="age_limit_due" @selected($memberStatus === 'age_limit_due')>Perlu Diproses Batas Usia</option>
                    </select>
                </div>
                <div class="lg:col-span-2">
                    <label for="inactive_reason" class="text-sm font-semibold text-slate-700">Alasan tidak aktif</label>
                    <select id="inactive_reason" name="inactive_reason" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                        <option value="">Semua alasan</option>
                        @foreach ($inactiveReasons as $value => $label)
                            <option value="{{ $value }}" @selected($inactiveReason === $value)>{{ $label }}</option>
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
                            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Kontak</th>
                            <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Tanggal Lahir / Usia</th>
                            <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Status Usia</th>
                            <x-sortable-th field="member_status" label="Status Anggota" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Status Akun</th>
                            <th class="whitespace-nowrap px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($members as $member)
                            <tr class="align-top transition hover:bg-slate-50/70">
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-500">{{ $members->firstItem() + $loop->index }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm font-semibold text-slate-700">{{ $member->npa ?: '-' }}</td>
                                <td class="max-w-48 px-3 py-4">
                                    <p class="line-clamp-2 break-words text-sm font-semibold text-slate-900">{{ $member->full_name }}</p>
                                </td>
                                <td class="max-w-36 px-3 py-4 text-sm text-slate-600"><span class="line-clamp-2 break-words">{{ $member->position?->name ?? '-' }}</span></td>
                                <td class="max-w-48 px-3 py-4 text-sm text-slate-600">
                                    <span class="line-clamp-1 break-all">{{ $member->email ?: '-' }}</span>
                                    <span class="mt-1 block whitespace-nowrap text-xs text-slate-500">{{ $member->phone ?: '-' }}</span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-600">
                                    <span class="block font-medium text-slate-800">{{ \App\Support\DateFormatter::date($member->birth_date) }}</span>
                                    <span class="mt-1 block text-xs text-slate-500">{{ $member->age() !== null ? $member->age().' tahun' : '-' }}</span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4">
                                    @if ($member->ageStatusKey() === 'needs_processing')
                                        <span class="inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700 ring-1 ring-inset ring-amber-200">Perlu Diproses</span>
                                    @elseif ($member->ageStatusKey() === 'eligible')
                                        <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-200">Memenuhi</span>
                                    @else
                                        <span class="text-sm text-slate-500">-</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-3 py-4">
                                    <div class="space-y-1">
                                        <x-ui.status-badge :status="$member->displayStatusKey()" :label="$member->displayStatusLabel()" />
                                        @if ($member->displayStatusKey() === 'inactive')
                                            <p class="max-w-36 truncate text-xs text-slate-500" title="{{ $member->inactiveReasonLabel() ?: ($member->member_status === 'alumni' ? 'Alumni/data lama' : ($member->member_status === 'moved' ? 'Pindah/data lama' : 'Alasan belum diisi')) }}">
                                                {{ $member->inactiveReasonLabel() ?: ($member->member_status === 'alumni' ? 'Alumni/data lama' : ($member->member_status === 'moved' ? 'Pindah/data lama' : 'Alasan belum diisi')) }}
                                            </p>
                                        @endif
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4">
                                    <x-ui.status-badge :status="$member->user ? 'account_exists' : 'account_missing'" />
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-right text-sm font-semibold">
                                    <div class="flex justify-end gap-1.5">
                                        <x-ui.action-icon :href="route('members.show', $member)" label="Detail" variant="detail" />
                                        <x-ui.action-dropdown>
                                            <x-ui.action-dropdown-item :href="route('members.edit', $member)" label="Edit" icon="pencil" />
                                            @if ($member->user)
                                                <x-ui.action-dropdown-item
                                                    :action="route('members.account.reset-password', $member)"
                                                    method="PATCH"
                                                    label="Reset Password"
                                                    icon="key"
                                                    variant="warning"
                                                    confirm="Reset password akun ini menjadi password?"
                                                    confirm-title="Reset Password?"
                                                    confirm-description="Password akun anggota ini akan direset. Informasikan password baru kepada anggota terkait."
                                                    confirm-text="Reset Password"
                                                    confirm-variant="warning"
                                                />
                                            @else
                                                <x-ui.action-dropdown-item :action="route('members.account.store', $member)" label="Buat Akun" icon="user-plus" />
                                            @endif
                                            @if ($member->needsAgeLimitProcessing())
                                                <x-ui.action-dropdown-item
                                                    :action="route('members.mark-age-limit-inactive', $member)"
                                                    method="PATCH"
                                                    label="Tandai Tidak Aktif karena Batas Usia"
                                                    icon="x"
                                                    variant="warning"
                                                    confirm="Tandai anggota ini tidak aktif karena batas usia?"
                                                    confirm-title="Tandai Tidak Aktif?"
                                                    confirm-description="Status anggota akan menjadi Tidak Aktif dengan alasan Melebihi batas usia Pemuda. Tidak ada surat yang dibuat pada tahap ini."
                                                    confirm-text="Tandai Tidak Aktif"
                                                    loading-text="Memproses..."
                                                    confirm-variant="warning"
                                                />
                                            @endif
                                            <x-ui.action-dropdown-item
                                                :action="route('members.destroy', $member)"
                                                method="DELETE"
                                                label="Hapus"
                                                icon="trash"
                                                variant="danger"
                                                confirm="Yakin ingin menghapus data ini?"
                                                confirm-title="Hapus Data?"
                                                confirm-description="Data anggota akan dihapus dari sistem. Pastikan data ini tidak lagi diperlukan."
                                                confirm-text="Hapus"
                                                confirm-variant="danger"
                                            />
                                        </x-ui.action-dropdown>
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
