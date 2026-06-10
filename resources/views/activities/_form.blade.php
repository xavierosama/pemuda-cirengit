@csrf

@php
    $statusLabels = [
        'scheduled' => 'Terjadwal',
        'completed' => 'Selesai',
        'holiday' => 'Libur',
        'postponed' => 'Ditunda',
        'relocated' => 'Pindah Lokasi',
        'cancelled' => 'Dibatalkan',
    ];
    $attendanceEnabled = (string) old('attendance_enabled', isset($activity) ? (int) $activity->attendance_enabled : 0);
@endphp

<div x-data="{ attendanceEnabled: @js($attendanceEnabled) }" class="space-y-6">
    @if (session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
    @endif

    <div class="grid gap-5 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label for="title" class="block text-sm font-semibold text-slate-700">Nama Kegiatan</label>
            <input id="title" name="title" type="text" value="{{ old('title', $activity->title ?? '') }}" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600" required autofocus>
            @error('title') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="agenda_schedule_id" class="block text-sm font-semibold text-slate-700">Jadwal Agenda</label>
            <select id="agenda_schedule_id" name="agenda_schedule_id" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                <option value="">Tanpa jadwal agenda</option>
                @foreach ($agendaSchedules as $agendaSchedule)
                    <option value="{{ $agendaSchedule->id }}" @selected((string) old('agenda_schedule_id', $activity->agenda_schedule_id ?? '') === (string) $agendaSchedule->id)>{{ $agendaSchedule->title }}</option>
                @endforeach
            </select>
            @error('agenda_schedule_id') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="activity_date" class="block text-sm font-semibold text-slate-700">Tanggal Kegiatan</label>
            <input id="activity_date" name="activity_date" type="date" value="{{ old('activity_date', isset($activity) && $activity->activity_date ? $activity->activity_date->format('Y-m-d') : '') }}" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600" required>
            @error('activity_date') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="department_id" class="block text-sm font-semibold text-slate-700">Bidang</label>
            <select id="department_id" name="department_id" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                <option value="">Tanpa bidang</option>
                @foreach ($departments as $department)
                    <option value="{{ $department->id }}" @selected((string) old('department_id', $activity->department_id ?? '') === (string) $department->id)>{{ $department->name }}</option>
                @endforeach
            </select>
            @error('department_id') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="pic_id" class="block text-sm font-semibold text-slate-700">PIC</label>
            <select id="pic_id" name="pic_id" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                <option value="">Tanpa PIC</option>
                @foreach ($members as $member)
                    <option value="{{ $member->id }}" @selected((string) old('pic_id', $activity->pic_id ?? '') === (string) $member->id)>{{ $member->full_name }}</option>
                @endforeach
            </select>
            @error('pic_id') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="start_time" class="block text-sm font-semibold text-slate-700">Waktu Mulai</label>
            <input id="start_time" name="start_time" type="time" value="{{ old('start_time', isset($activity) && $activity->start_time ? substr($activity->start_time, 0, 5) : '') }}" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
            @error('start_time') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="end_time" class="block text-sm font-semibold text-slate-700">Waktu Selesai</label>
            <input id="end_time" name="end_time" type="time" value="{{ old('end_time', isset($activity) && $activity->end_time ? substr($activity->end_time, 0, 5) : '') }}" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
            @error('end_time') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="sm:col-span-2">
            <label for="location" class="block text-sm font-semibold text-slate-700">Lokasi</label>
            <input id="location" name="location" type="text" value="{{ old('location', $activity->location ?? '') }}" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
            @error('location') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="latitude" class="block text-sm font-semibold text-slate-700">Latitude</label>
            <input id="latitude" name="latitude" type="number" step="0.0000001" value="{{ old('latitude', $activity->latitude ?? '') }}" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
            @error('latitude') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="longitude" class="block text-sm font-semibold text-slate-700">Longitude</label>
            <input id="longitude" name="longitude" type="number" step="0.0000001" value="{{ old('longitude', $activity->longitude ?? '') }}" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
            @error('longitude') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="status" class="block text-sm font-semibold text-slate-700">Status</label>
            <select id="status" name="status" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600" required>
                @foreach ($statuses as $statusValue)
                    <option value="{{ $statusValue }}" @selected(old('status', $activity->status ?? 'scheduled') === $statusValue)>{{ $statusLabels[$statusValue] }}</option>
                @endforeach
            </select>
            @error('status') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="sm:col-span-2">
            <label for="change_reason" class="block text-sm font-semibold text-slate-700">Alasan Perubahan</label>
            <textarea id="change_reason" name="change_reason" rows="3" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">{{ old('change_reason', $activity->change_reason ?? '') }}</textarea>
            @error('change_reason') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <section class="border-t border-slate-200 pt-6">
        <h3 class="text-base font-bold text-slate-950">Pengaturan Presensi</h3>
        <div class="mt-5 grid gap-5 sm:grid-cols-2">
            <div>
                <label for="attendance_enabled" class="block text-sm font-semibold text-slate-700">Status Presensi</label>
                <select id="attendance_enabled" name="attendance_enabled" x-model="attendanceEnabled" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600" required>
                    <option value="0">Tidak aktif</option>
                    <option value="1">Aktif</option>
                </select>
                @error('attendance_enabled') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="attendance_radius" class="block text-sm font-semibold text-slate-700">Radius Presensi (meter)</label>
                <input id="attendance_radius" name="attendance_radius" type="number" min="1" value="{{ old('attendance_radius', $activity->attendance_radius ?? 100) }}" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600" required>
                @error('attendance_radius') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div x-show="attendanceEnabled === '1'" x-cloak>
                <label for="attendance_open_at" class="block text-sm font-semibold text-slate-700">Presensi Dibuka</label>
                <input id="attendance_open_at" name="attendance_open_at" type="datetime-local" value="{{ old('attendance_open_at', isset($activity) && $activity->attendance_open_at ? $activity->attendance_open_at->format('Y-m-d\TH:i') : '') }}" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                @error('attendance_open_at') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div x-show="attendanceEnabled === '1'" x-cloak>
                <label for="attendance_close_at" class="block text-sm font-semibold text-slate-700">Presensi Ditutup</label>
                <input id="attendance_close_at" name="attendance_close_at" type="datetime-local" value="{{ old('attendance_close_at', isset($activity) && $activity->attendance_close_at ? $activity->attendance_close_at->format('Y-m-d\TH:i') : '') }}" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                @error('attendance_close_at') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:justify-end">
        <a href="{{ route('activities.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</a>
        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-800">Simpan</button>
    </div>
</div>
