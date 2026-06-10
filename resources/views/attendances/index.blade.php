@extends('layouts.admin')

@section('title', 'Daftar Hadir - Pemuda Cirengit')
@section('section', 'Presensi')
@section('page-title', 'Daftar Hadir')

@section('content')
    @php
        $statusLabels = ['present' => 'Hadir', 'permission' => 'Izin', 'absent' => 'Tidak Hadir', 'need_verification' => 'Perlu Verifikasi'];
        $statusClasses = ['present' => 'bg-emerald-50 text-emerald-700 ring-emerald-200', 'permission' => 'bg-sky-50 text-sky-700 ring-sky-200', 'absent' => 'bg-red-50 text-red-700 ring-red-200', 'need_verification' => 'bg-amber-50 text-amber-700 ring-amber-200'];
        $verificationLabels = ['valid' => 'Valid', 'need_verification' => 'Perlu Verifikasi', 'rejected' => 'Ditolak'];
    @endphp

    <div class="space-y-6">
        <div><h2 class="text-xl font-bold text-slate-950">Semua Presensi</h2><p class="mt-1 text-sm text-slate-500">Rekap daftar hadir manual dan melalui link seluruh kegiatan.</p></div>

        <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
            <form method="GET" action="{{ route('attendances.index') }}" class="grid gap-3 sm:grid-cols-2 xl:grid-cols-[minmax(220px,1fr)_180px_200px_190px_auto]">
                <select name="activity_id" aria-label="Filter kegiatan" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600"><option value="">Semua kegiatan</option>@foreach ($activities as $activity)<option value="{{ $activity->id }}" @selected((string) $activityId === (string) $activity->id)>{{ $activity->activity_date->format('d M Y') }} - {{ $activity->title }}</option>@endforeach</select>
                <select name="status" aria-label="Filter status" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600"><option value="">Semua status</option>@foreach ($statuses as $statusValue)<option value="{{ $statusValue }}" @selected($status === $statusValue)>{{ $statusLabels[$statusValue] }}</option>@endforeach</select>
                <select name="member_id" aria-label="Filter anggota" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600"><option value="">Semua anggota</option>@foreach ($members as $member)<option value="{{ $member->id }}" @selected((string) $memberId === (string) $member->id)>{{ $member->full_name }}</option>@endforeach</select>
                <select name="department_id" aria-label="Filter bidang anggota" class="block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600"><option value="">Semua bidang</option>@foreach ($departments as $department)<option value="{{ $department->id }}" @selected((string) $departmentId === (string) $department->id)>{{ $department->name }}</option>@endforeach</select>
                <div class="flex gap-2 sm:col-span-2 xl:col-span-1"><button type="submit" class="flex-1 rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-700">Filter</button><a href="{{ route('attendances.index') }}" class="flex flex-1 items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Reset</a></div>
            </form>
        </div>

        <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"><div class="overflow-x-auto"><table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50"><tr>@foreach (['Kegiatan', 'Tanggal', 'Nama Anggota', 'Bidang', 'Status', 'Metode', 'Jarak', 'Accuracy', 'Verifikasi', 'Catatan'] as $heading)<th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide text-slate-500">{{ $heading }}</th>@endforeach<th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wide text-slate-500">Aksi</th></tr></thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($attendances as $attendance)
                    <tr>
                        <td class="px-4 py-4 text-sm font-semibold text-slate-900"><a href="{{ route('activities.attendances.index', $attendance->activity) }}" class="hover:text-emerald-700">{{ $attendance->activity->title }}</a></td>
                        <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">{{ $attendance->activity->activity_date->format('d M Y') }}</td>
                        <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-700">{{ $attendance->member->full_name }}</td>
                        <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">{{ $attendance->member->department?->name ?? '-' }}</td>
                        <td class="whitespace-nowrap px-4 py-4"><span class="{{ $statusClasses[$attendance->status] }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ $statusLabels[$attendance->status] }}</span></td>
                        <td class="whitespace-nowrap px-4 py-4 text-sm capitalize text-slate-600">{{ $attendance->attendance_method }}</td>
                        <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">{{ $attendance->distance_from_activity !== null ? number_format((float) $attendance->distance_from_activity, 2).' m' : '-' }}</td>
                        <td class="whitespace-nowrap px-4 py-4 text-sm text-slate-600">{{ $attendance->location_accuracy !== null ? number_format((float) $attendance->location_accuracy, 2).' m' : '-' }}</td>
                        <td class="whitespace-nowrap px-4 py-4 text-sm font-semibold {{ $attendance->verification_status === 'valid' ? 'text-emerald-700' : ($attendance->verification_status === 'rejected' ? 'text-red-700' : 'text-amber-700') }}">{{ $verificationLabels[$attendance->verification_status] }}</td>
                        <td class="max-w-56 px-4 py-4 text-sm text-slate-600">{{ str($attendance->notes ?: '-')->limit(60) }}</td>
                        <td class="whitespace-nowrap px-4 py-4 text-right"><div class="flex justify-end gap-2">@if ($attendance->verification_status === 'need_verification')<form method="POST" action="{{ route('attendances.verify', $attendance) }}">@csrf @method('PATCH')<button class="rounded-md px-2.5 py-1.5 text-sm font-semibold text-emerald-700 hover:bg-emerald-50">Valid</button></form><form method="POST" action="{{ route('attendances.reject', $attendance) }}">@csrf @method('PATCH')<button class="rounded-md px-2.5 py-1.5 text-sm font-semibold text-red-700 hover:bg-red-50">Reject</button></form>@endif<a href="{{ route('attendances.edit', $attendance) }}" class="rounded-md px-2.5 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-100">Edit</a></div></td>
                    </tr>
                @empty
                    <tr><td colspan="11" class="px-4 py-10 text-center text-sm text-slate-500">Belum ada data presensi.</td></tr>
                @endforelse
            </tbody>
        </table></div></div>

        {{ $attendances->links() }}
    </div>
@endsection
