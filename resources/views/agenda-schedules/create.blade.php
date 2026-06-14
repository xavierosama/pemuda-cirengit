@extends('layouts.admin')

@section('title', 'Tambah Jadwal Agenda - Pemuda Cirengit')
@section('section', 'Agenda')
@section('page-title', 'Tambah Jadwal Agenda')
@section('breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Jadwal Agenda', 'url' => route('agenda-schedules.index')],
        ['label' => 'Tambah Jadwal Agenda'],
    ]" />
@endsection

@section('content')
    <div class="mx-auto max-w-5xl space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">Agenda & Kegiatan</p>
                    <h2 class="mt-2 text-2xl font-bold text-slate-950">Tambah Jadwal Agenda</h2>
                    <p class="mt-2 max-w-2xl text-sm text-slate-500">Atur pola agenda, waktu, lokasi default, dan penanggung jawab kegiatan.</p>
                </div>
                <a href="{{ route('agenda-schedules.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">Kembali</a>
            </div>
        </div>

        <form method="POST" action="{{ route('agenda-schedules.store') }}">
            @include('agenda-schedules._form')
        </form>
    </div>
@endsection
