@extends('layouts.admin')

@section('title', 'Edit Kegiatan Aktual - Pemuda Cirengit')
@section('section', 'Kegiatan')
@section('page-title', 'Edit Kegiatan Aktual')

@section('content')
    <div class="max-w-5xl">
        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <form method="POST" action="{{ route('activities.update', $activity) }}">
                @method('PUT')
                @include('activities._form')
            </form>
        </div>
    </div>
@endsection
