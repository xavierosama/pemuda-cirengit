@extends('layouts.admin')

@section('title', 'Tambah Lokasi Kegiatan - Pemuda Cirengit')
@section('section', 'Master Data')
@section('page-title', 'Tambah Lokasi Kegiatan')
@section('breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Lokasi Kegiatan', 'url' => route('activity-locations.index')],
        ['label' => 'Tambah Lokasi'],
    ]" />
@endsection

@section('content')
    <div class="w-full space-y-6">
        <x-ui.page-header
            eyebrow="Master Data"
            title="Tambah Lokasi Kegiatan"
            description="Tambahkan lokasi default agar Jadwal Agenda tidak perlu mengisi koordinat manual."
        >
            <x-slot:action>
                <x-ui.button :href="route('activity-locations.index')" variant="secondary">Kembali</x-ui.button>
            </x-slot:action>
        </x-ui.page-header>

        <form method="POST" action="{{ route('activity-locations.store') }}" x-data="{ submitting: false }" x-on:submit="submitting = true">
            @include('activity-locations._form')
        </form>
    </div>
@endsection
