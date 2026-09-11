@extends('layouts.admin')

@section('title', 'Notifikasi - Pemuda Cirengit')
@section('section', 'Komunikasi')
@section('page-title', 'Notifikasi')
@section('breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Notifikasi'],
    ]" />
@endsection

@section('content')
    @php
        $filterCount = collect([$status, $type, $channel])->filter(fn ($value) => filled($value))->count();
        $statusClasses = [
            'draft' => 'bg-amber-50 text-amber-700 ring-amber-200',
            'sent' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
            'cancelled' => 'bg-slate-100 text-slate-600 ring-slate-200',
        ];
    @endphp

    <div class="space-y-4 sm:space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">Komunikasi Anggota</p>
                    <h2 class="mt-2 text-2xl font-bold text-slate-950">Notifikasi</h2>
                    <p class="mt-2 max-w-2xl text-sm text-slate-500">Buat pengumuman dan kirim notifikasi penting kepada anggota.</p>
                </div>
                <a href="{{ route('admin-notifications.create') }}" class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">Tambah Notifikasi</a>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3 sm:gap-4 sm:grid-cols-3">
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="inline-flex rounded-full bg-slate-50 px-2 py-1 text-[11px] font-semibold text-slate-700 ring-1 ring-inset ring-slate-200 sm:px-2.5 sm:text-xs">Total</div>
                <p class="mt-3 text-2xl font-bold text-slate-950 sm:mt-4 sm:text-3xl">{{ number_format($stats['total']) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="inline-flex rounded-full bg-amber-50 px-2 py-1 text-[11px] font-semibold text-amber-700 ring-1 ring-inset ring-amber-200 sm:px-2.5 sm:text-xs">Draft</div>
                <p class="mt-3 text-2xl font-bold text-slate-950 sm:mt-4 sm:text-3xl">{{ number_format($stats['draft']) }}</p>
            </div>
            <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="inline-flex rounded-full bg-emerald-50 px-2 py-1 text-[11px] font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-200 sm:px-2.5 sm:text-xs">Terkirim</div>
                <p class="mt-3 text-2xl font-bold text-slate-950 sm:mt-4 sm:text-3xl">{{ number_format($stats['sent']) }}</p>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="grid gap-4 border-b border-slate-200 px-5 py-4 xl:grid-cols-[minmax(0,1fr)_auto] xl:items-center">
                <div>
                    <h3 class="text-base font-bold text-slate-950">Riwayat Notifikasi</h3>
                    <p class="mt-1 text-sm text-slate-500">Daftar draft dan pengiriman notifikasi anggota.</p>
                </div>
                <x-ui.table-toolbar
                    :action="route('admin-notifications.index')"
                    search-placeholder="Cari judul atau pesan"
                    :search-value="$search"
                    :search-hidden="[
                        'sort' => $currentSort,
                        'direction' => $currentDirection,
                        'per_page' => $perPage,
                        'status' => $status,
                        'type' => $type,
                        'channel' => $channel,
                    ]"
                    :filter-hidden="[
                        'sort' => $currentSort,
                        'direction' => $currentDirection,
                        'per_page' => $perPage,
                    ]"
                    :filter-count="$filterCount"
                    :reset-href="route('admin-notifications.index')"
                    show-filter
                >
                    <x-slot:filters>
                        <div class="grid gap-4 sm:grid-cols-3">
                            <div>
                                <label for="status_filter" class="text-sm font-semibold text-slate-700">Status</label>
                                <select id="status_filter" name="status" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                    <option value="">Semua status</option>
                                    @foreach (\App\Models\AdminNotification::STATUSES as $value => $label)
                                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="type_filter" class="text-sm font-semibold text-slate-700">Tipe</label>
                                <select id="type_filter" name="type" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                    <option value="">Semua tipe</option>
                                    @foreach (\App\Models\AdminNotification::TYPES as $value => $label)
                                        <option value="{{ $value }}" @selected($type === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="channel_filter" class="text-sm font-semibold text-slate-700">Channel</label>
                                <select id="channel_filter" name="channel" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                                    <option value="">Semua channel</option>
                                    @foreach (\App\Models\AdminNotification::CHANNELS as $value => $label)
                                        <option value="{{ $value }}" @selected($channel === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </x-slot:filters>

                    <x-per-page-selector :per-page="$perPage" :options="$perPageOptions" :query="$queryParams" />
                </x-ui.table-toolbar>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">No</th>
                            <x-sortable-th field="title" label="Notifikasi" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <x-sortable-th field="type" label="Tipe" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <x-sortable-th field="channel" label="Channel" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <x-sortable-th field="status" label="Status" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <th class="whitespace-nowrap px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">Terkirim</th>
                            <x-sortable-th field="created_at" label="Dibuat" :current-sort="$currentSort" :current-direction="$currentDirection" :query="$queryParams" />
                            <th class="sticky right-0 z-20 whitespace-nowrap border-l border-slate-200 bg-slate-50 px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500 shadow-[-8px_0_12px_-12px_rgba(15,23,42,0.35)]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse ($adminNotifications as $notification)
                            <tr class="align-top transition hover:bg-slate-50/70">
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-500">{{ $adminNotifications->firstItem() + $loop->index }}</td>
                                <td class="max-w-sm px-3 py-4">
                                    <p class="line-clamp-1 text-sm font-bold text-slate-950">{{ $notification->title }}</p>
                                    <p class="mt-1 line-clamp-2 text-xs leading-5 text-slate-500">{{ $notification->message }}</p>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-700">{{ $notification->typeLabel() }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-700">{{ $notification->channelLabel() }}</td>
                                <td class="whitespace-nowrap px-3 py-4">
                                    <span class="{{ $statusClasses[$notification->status] ?? 'bg-slate-100 text-slate-600 ring-slate-200' }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ $notification->statusLabel() }}</span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-700">{{ number_format($notification->delivered_count) }} anggota</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-slate-600">{{ \App\Support\DateFormatter::dateTime($notification->created_at) }}</td>
                                <td class="sticky right-0 z-10 whitespace-nowrap border-l border-slate-100 bg-white px-3 py-4 text-right text-sm font-semibold shadow-[-8px_0_12px_-12px_rgba(15,23,42,0.35)]">
                                    <div class="flex justify-end gap-1.5">
                                        <x-action-icon :href="route('admin-notifications.show', $notification)" label="Detail" icon="eye" variant="blue" />
                                        @if ($notification->isDraft())
                                            <x-ui.action-dropdown>
                                                <x-ui.action-dropdown-item :href="route('admin-notifications.edit', $notification)" label="Edit" icon="pencil" />
                                                <x-ui.action-dropdown-item
                                                    :action="route('admin-notifications.send', $notification)"
                                                    method="POST"
                                                    label="Kirim"
                                                    icon="send"
                                                    variant="success"
                                                    confirm="Kirim notifikasi ini sekarang?"
                                                    confirm-title="Kirim Notifikasi?"
                                                    confirm-description="Notifikasi akan dibuat untuk target anggota dan tidak bisa diedit setelah terkirim."
                                                    confirm-text="Kirim"
                                                    confirm-variant="primary"
                                                />
                                            </x-ui.action-dropdown>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-14 text-center">
                                    <p class="text-base font-semibold text-slate-800">Belum ada notifikasi admin.</p>
                                    <p class="mt-1 text-sm text-slate-500">Buat notifikasi pertama untuk mengirim informasi penting ke anggota.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{ $adminNotifications->links() }}
    </div>
@endsection
