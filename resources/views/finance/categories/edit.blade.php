@extends('layouts.admin')

@section('title', 'Edit Kategori Keuangan')
@section('page-title', 'Edit Kategori Keuangan')
@section('breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard Bendahara', 'url' => route('finance.dashboard')],
        ['label' => 'Kategori Keuangan', 'url' => route('finance.categories.index')],
        ['label' => 'Edit'],
    ]" />
@endsection

@section('content')
    <form method="POST" action="{{ route('finance.categories.update', $category) }}">
        @method('PUT')
        @include('finance.categories._form')
    </form>
@endsection
