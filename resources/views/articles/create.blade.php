@extends('layouts.admin')

@section('title', 'Tambah Artikel Kajian - Pemuda Cirengit')
@section('breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Artikel Kajian', 'url' => route('articles.index')],
        ['label' => 'Tambah Artikel'],
    ]" />
@endsection

@section('content')
    <div class="space-y-4 sm:space-y-6">
        <x-ui.page-header title="Tambah Artikel Kajian" eyebrow="Publikasi" description="Tulis hasil kajian dan siapkan publikasinya untuk halaman publik." />
        <form method="POST" action="{{ route('articles.store') }}" enctype="multipart/form-data" x-data="{ submitting: false }" x-on:submit="submitting = true">
            @include('articles._form')
        </form>
    </div>
@endsection
