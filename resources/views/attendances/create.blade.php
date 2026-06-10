@extends('layouts.admin')

@section('title', 'Input Kehadiran Manual - Pemuda Cirengit')
@section('section', 'Presensi')
@section('page-title', 'Input Kehadiran Manual')

@section('content')
    @php $statusLabels = ['present' => 'Hadir', 'permission' => 'Izin', 'absent' => 'Tidak Hadir', 'need_verification' => 'Perlu Verifikasi']; @endphp
    <div class="max-w-3xl space-y-6">
        <div><a href="{{ route('activities.attendances.index', $activity) }}" class="text-sm font-semibold text-slate-600 hover:text-slate-900">Kembali ke Daftar Hadir</a><h2 class="mt-3 text-xl font-bold text-slate-950">{{ $activity->title }}</h2><p class="mt-1 text-sm text-slate-500">{{ $activity->activity_date->format('d M Y') }}</p></div>
        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <form method="POST" action="{{ route('activities.attendances.store', $activity) }}">
                @csrf
                <input type="hidden" name="activity_id" value="{{ $activity->id }}">
                <div class="space-y-5">
                    <div><label for="member_id" class="block text-sm font-semibold text-slate-700">Anggota</label><select id="member_id" name="member_id" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600" required><option value="">Pilih anggota aktif</option>@foreach ($members as $member)<option value="{{ $member->id }}" @selected((string) old('member_id') === (string) $member->id)>{{ $member->full_name }}</option>@endforeach</select>@error('member_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror</div>
                    <div><label for="status" class="block text-sm font-semibold text-slate-700">Status Kehadiran</label><select id="status" name="status" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600" required>@foreach ($statuses as $statusValue)<option value="{{ $statusValue }}" @selected(old('status', 'present') === $statusValue)>{{ $statusLabels[$statusValue] }}</option>@endforeach</select>@error('status')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror</div>
                    <div><label for="notes" class="block text-sm font-semibold text-slate-700">Catatan</label><textarea id="notes" name="notes" rows="4" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">{{ old('notes') }}</textarea>@error('notes')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror</div>
                </div>
                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end"><a href="{{ route('activities.attendances.index', $activity) }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</a><button type="submit" class="rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Simpan Kehadiran</button></div>
            </form>
        </div>
    </div>
@endsection
