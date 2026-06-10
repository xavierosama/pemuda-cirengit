@extends('layouts.admin')

@section('title', 'Tambah Kegiatan Aktual - Pemuda Cirengit')
@section('section', 'Kegiatan')
@section('page-title', 'Tambah Kegiatan Aktual')

@section('content')
    <div class="max-w-5xl">
        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <form method="POST" action="{{ route('activities.store') }}">
                @include('activities._form')
            </form>
        </div>
    </div>
@endsection
