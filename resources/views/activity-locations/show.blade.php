@extends('layouts.admin')

@section('title', 'Detail Lokasi Kegiatan - Pemuda Cirengit')
@section('section', 'Master Data')
@section('page-title', 'Detail Lokasi Kegiatan')
@section('breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Lokasi Kegiatan', 'url' => route('activity-locations.index')],
        ['label' => 'Detail Lokasi'],
    ]" />
@endsection

@section('content')
    <div class="max-w-4xl space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <a href="{{ route('activity-locations.index') }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">Kembali ke Lokasi Kegiatan</a>
            <x-ui.button :href="route('activity-locations.edit', $activityLocation)">Edit Lokasi</x-ui.button>
        </div>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">Lokasi Kegiatan</p>
                    <h2 class="mt-2 text-2xl font-bold text-slate-950">{{ $activityLocation->name }}</h2>
                    <p class="mt-3 max-w-3xl whitespace-pre-line text-sm leading-6 text-slate-600">{{ $activityLocation->address ?: 'Tidak ada keterangan lokasi.' }}</p>
                </div>
                <x-ui.status-badge class="w-fit" :status="$activityLocation->is_active ? 'active' : 'inactive'" :label="$activityLocation->is_active ? 'Aktif' : 'Nonaktif'" />
            </div>

            <dl class="mt-6 grid gap-5 border-t border-slate-200 pt-6 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Latitude</dt>
                    <dd class="mt-1 text-sm text-slate-700">{{ $activityLocation->latitude ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Longitude</dt>
                    <dd class="mt-1 text-sm text-slate-700">{{ $activityLocation->longitude ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Radius Default</dt>
                    <dd class="mt-1 text-sm text-slate-700">{{ $activityLocation->radius_meters }} meter</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Dipakai Jadwal</dt>
                    <dd class="mt-1 text-sm text-slate-700">{{ number_format($activityLocation->agenda_schedules_count) }} jadwal</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Dibuat</dt>
                    <dd class="mt-1 text-sm text-slate-700">{{ \App\Support\DateFormatter::dateTime($activityLocation->created_at) }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">Diperbarui</dt>
                    <dd class="mt-1 text-sm text-slate-700">{{ \App\Support\DateFormatter::dateTime($activityLocation->updated_at) }}</dd>
                </div>
            </dl>
        </section>
    </div>
@endsection
