@extends('layouts.admin')

@section('title', 'Tambah Jadwal Agenda - Pemuda Cirengit')
@section('section', 'Agenda')
@section('page-title', 'Tambah Jadwal Agenda')

@section('content')
    <div class="max-w-5xl">
        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <form method="POST" action="{{ route('agenda-schedules.store') }}">
                @include('agenda-schedules._form')
            </form>
        </div>
    </div>
@endsection
