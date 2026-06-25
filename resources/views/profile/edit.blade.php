@php
    $systemSettings = app(\App\Support\SystemSettings::class);
    $appName = $systemSettings->get('app_name');
@endphp

@extends('layouts.admin')

@section('title', 'Edit Profil - '.$appName)
@section('section', 'Pengaturan')
@section('page-title', 'Edit Profil')
@section('breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Edit Profil'],
    ]" />
@endsection

@section('content')
    @include('profile.partials.profile-page-content', ['backRoute' => route('dashboard'), 'backLabel' => 'Kembali ke Dashboard Admin'])
@endsection
