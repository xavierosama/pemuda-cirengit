@extends('layouts.admin')

@section('title', 'Tambah Data Jabatan - Pemuda Cirengit')
@section('section', 'Master Data')
@section('page-title', 'Tambah Data Jabatan')

@section('content')
    <div class="max-w-3xl">
        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <form method="POST" action="{{ route('positions.store') }}">
                @include('positions._form')
            </form>
        </div>
    </div>
@endsection
