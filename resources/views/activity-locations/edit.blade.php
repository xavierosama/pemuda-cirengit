@extends('layouts.admin')

@section('title', 'Edit Lokasi Kegiatan - Pemuda Cirengit')
@section('section', 'Master Data')
@section('page-title', 'Edit Lokasi Kegiatan')
@section('breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Lokasi Kegiatan', 'url' => route('activity-locations.index')],
        ['label' => 'Edit Lokasi'],
    ]" />
@endsection

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        <x-ui.page-header
            eyebrow="Master Data"
            title="Edit Lokasi Kegiatan"
            description="Perbarui data lokasi, koordinat, radius default, dan status aktif."
        >
            <x-slot:action>
                <x-ui.button :href="route('activity-locations.index')" variant="secondary">Kembali</x-ui.button>
            </x-slot:action>
        </x-ui.page-header>

        <form method="POST" action="{{ route('activity-locations.update', $activityLocation) }}" x-data="{ submitting: false }" x-on:submit="submitting = true">
            @method('PUT')
            @include('activity-locations._form', ['activityLocation' => $activityLocation])
        </form>
    </div>
@endsection
