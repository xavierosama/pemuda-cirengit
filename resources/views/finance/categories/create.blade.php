@extends('layouts.admin')

@section('title', 'Tambah Kategori Keuangan')
@section('page-title', 'Tambah Kategori Keuangan')
@section('breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard Bendahara', 'url' => route('finance.dashboard')],
        ['label' => 'Kategori Keuangan', 'url' => route('finance.categories.index')],
        ['label' => 'Tambah'],
    ]" />
@endsection

@section('content')
    <form method="POST" action="{{ route('finance.categories.store') }}">
        @include('finance.categories._form')
    </form>
@endsection
