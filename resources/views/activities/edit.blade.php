@extends('layouts.admin')

@section('title', 'Edit Kegiatan Aktual - Pemuda Cirengit')
@section('section', 'Kegiatan')
@section('page-title', 'Edit Kegiatan Aktual')

@section('content')
    <div class="mx-auto max-w-5xl space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">Agenda & Kegiatan</p>
                    <h2 class="mt-2 text-2xl font-bold text-slate-950">Edit Kegiatan Aktual</h2>
                    <p class="mt-2 max-w-2xl text-sm text-slate-500">Perbarui detail kegiatan, lokasi, dan pengaturan presensi tanpa mengubah logic QR atau radius.</p>
                    @if ($activity->updated_at)
                        <p class="mt-3 text-xs font-medium text-slate-500">Terakhir diperbarui: {{ $activity->updated_at->format('d/m/Y H:i') }}</p>
                    @endif
                </div>
                <a href="{{ route('activities.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">Kembali</a>
            </div>
        </div>

        <form method="POST" action="{{ route('activities.update', $activity) }}">
            @method('PUT')
            @include('activities._form')
        </form>
    </div>
@endsection
