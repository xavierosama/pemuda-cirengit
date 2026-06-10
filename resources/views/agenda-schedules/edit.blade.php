@extends('layouts.admin')

@section('title', 'Edit Jadwal Agenda - Pemuda Cirengit')
@section('section', 'Agenda')
@section('page-title', 'Edit Jadwal Agenda')

@section('content')
    <div class="max-w-5xl">
        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <form method="POST" action="{{ route('agenda-schedules.update', $agendaSchedule) }}">
                @method('PUT')
                @include('agenda-schedules._form')
            </form>
        </div>
    </div>
@endsection
