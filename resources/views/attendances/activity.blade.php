@extends('layouts.admin')

@section('title', 'Daftar Hadir Kegiatan - Pemuda Cirengit')
@section('section', 'Presensi')
@section('page-title', 'Daftar Hadir Kegiatan')

@section('content')
    @php
        $statusLabels = ['present' => 'Hadir', 'permission' => 'Izin', 'absent' => 'Tidak Hadir', 'need_verification' => 'Perlu Verifikasi'];
        $statusClasses = ['present' => 'bg-emerald-50 text-emerald-700 ring-emerald-200', 'permission' => 'bg-sky-50 text-sky-700 ring-sky-200', 'absent' => 'bg-red-50 text-red-700 ring-red-200', 'need_verification' => 'bg-amber-50 text-amber-700 ring-amber-200'];
        $verificationLabels = ['valid' => 'Valid', 'need_verification' => 'Perlu Verifikasi', 'rejected' => 'Ditolak'];
    @endphp

    <div class="space-y-6">
        @if (session('success'))<div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>@endif

        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div><a href="{{ route('activities.show', $activity) }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">Kembali ke Detail Kegiatan</a><h2 class="mt-3 text-2xl font-bold text-slate-950">{{ $activity->title }}</h2><p class="mt-2 text-sm text-slate-500">{{ $activity->activity_date->format('d/m/Y') }} - {{ $activity->location ?: 'Lokasi belum diisi' }}</p></div>
            <div class="flex flex-col gap-2 sm:flex-row">
                <form method="POST" action="{{ route('activities.attendances.sync-participants', $activity) }}">
                    @csrf
                    <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Sinkronkan Peserta Presensi</button>
                </form>
                <a href="{{ route('activities.attendances.create', $activity) }}" class="inline-flex items-center justify-center rounded-lg border border-emerald-700 px-4 py-2 text-sm font-semibold text-emerald-700 hover:bg-emerald-50">Input Satu Anggota</a><a href="{{ route('activities.attendances.bulk.create', $activity) }}" class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Input Massal</a>
            </div>
        </div>

        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($statusLabels as $value => $label)<div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm"><p class="text-sm font-medium text-slate-500">{{ $label }}</p><p class="mt-3 text-3xl font-bold text-slate-950">{{ $summary[$value] }}</p></div>@endforeach
        </section>

        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50"><tr>@foreach (['Anggota', 'Bidang', 'Status', 'Metode', 'Check-in', 'Verifikasi Pada', 'Jarak', 'Accuracy', 'Verifikasi', 'Catatan'] as $heading)<th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">{{ $heading }}</th>@endforeach<th class="px-4 py-3 text-right text-xs font-bold uppercase text-slate-500">Aksi</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($attendances as $attendance)
                            <tr>
                                <td class="whitespace-nowrap px-4 py-4 text-sm font-semibold text-slate-900">{{ $attendance->member->full_name }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">{{ $attendance->member->department?->name ?? '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-4"><span class="{{ $statusClasses[$attendance->status] }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ $statusLabels[$attendance->status] }}</span></td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm capitalize text-slate-600">{{ $attendance->attendance_method }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">{{ $attendance->checked_in_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">{{ $attendance->verified_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">{{ $attendance->distance_from_activity !== null ? number_format((float) $attendance->distance_from_activity, 2).' m' : '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">{{ $attendance->location_accuracy !== null ? number_format((float) $attendance->location_accuracy, 2).' m' : '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-sm font-semibold {{ $attendance->verification_status === 'valid' ? 'text-emerald-700' : ($attendance->verification_status === 'rejected' ? 'text-red-700' : 'text-amber-700') }}">{{ $verificationLabels[$attendance->verification_status] }}</td>
                                <td class="max-w-64 px-4 py-4 text-sm text-slate-600">{{ $attendance->notes ?: '-' }}</td>
                                <td class="whitespace-nowrap px-4 py-4 text-right"><div class="flex justify-end gap-2">
                                    @if ($attendance->verification_status === 'need_verification')
                                        <form method="POST" action="{{ route('attendances.verify', $attendance) }}">@csrf @method('PATCH')<button class="rounded-md px-2.5 py-1.5 text-sm font-semibold text-emerald-700 hover:bg-emerald-50">Valid</button></form>
                                        <form method="POST" action="{{ route('attendances.reject', $attendance) }}">@csrf @method('PATCH')<button class="rounded-md px-2.5 py-1.5 text-sm font-semibold text-red-700 hover:bg-red-50">Reject</button></form>
                                    @endif
                                    <a href="{{ route('attendances.edit', $attendance) }}" class="rounded-md px-2.5 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-100">Edit</a>
                                    <form method="POST" action="{{ route('attendances.destroy', $attendance) }}" onsubmit="return confirm('Hapus data kehadiran ini?')">@csrf @method('DELETE')<button class="rounded-md px-2.5 py-1.5 text-sm font-semibold text-red-700 hover:bg-red-50">Hapus</button></form>
                                </div></td>
                            </tr>
                        @empty
                            <tr><td colspan="11" class="px-4 py-10 text-center text-sm text-slate-500">Belum ada anggota yang tercatat.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
