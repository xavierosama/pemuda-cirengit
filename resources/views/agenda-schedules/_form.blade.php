@csrf

@php
    $selectedType = old('schedule_type', $agendaSchedule->schedule_type ?? 'once');
@endphp

<div x-data="{ scheduleType: @js($selectedType) }" class="space-y-6">
    <div class="grid gap-5 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <label for="title" class="block text-sm font-semibold text-slate-700">Nama Agenda</label>
            <input id="title" name="title" type="text" value="{{ old('title', $agendaSchedule->title ?? '') }}" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600" required autofocus>
            @error('title') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="sm:col-span-2">
            <label for="description" class="block text-sm font-semibold text-slate-700">Deskripsi</label>
            <textarea id="description" name="description" rows="4" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">{{ old('description', $agendaSchedule->description ?? '') }}</textarea>
            @error('description') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="department_id" class="block text-sm font-semibold text-slate-700">Bidang</label>
            <select id="department_id" name="department_id" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                <option value="">Tanpa bidang</option>
                @foreach ($departments as $department)
                    <option value="{{ $department->id }}" @selected((string) old('department_id', $agendaSchedule->department_id ?? '') === (string) $department->id)>{{ $department->name }}</option>
                @endforeach
            </select>
            @error('department_id') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="pic_id" class="block text-sm font-semibold text-slate-700">PIC</label>
            <select id="pic_id" name="pic_id" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                <option value="">Tanpa PIC</option>
                @foreach ($members as $member)
                    <option value="{{ $member->id }}" @selected((string) old('pic_id', $agendaSchedule->pic_id ?? '') === (string) $member->id)>{{ $member->full_name }}</option>
                @endforeach
            </select>
            @error('pic_id') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="schedule_type" class="block text-sm font-semibold text-slate-700">Tipe Jadwal</label>
            <select id="schedule_type" name="schedule_type" x-model="scheduleType" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600" required>
                <option value="once">Satu kali</option>
                <option value="daily">Harian</option>
                <option value="weekly">Mingguan</option>
                <option value="monthly">Bulanan</option>
            </select>
            @error('schedule_type') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div x-show="scheduleType === 'once'" x-cloak>
            <label for="specific_date" class="block text-sm font-semibold text-slate-700">Tanggal Agenda</label>
            <input id="specific_date" name="specific_date" type="date" value="{{ old('specific_date', isset($agendaSchedule) && $agendaSchedule->specific_date ? $agendaSchedule->specific_date->format('Y-m-d') : '') }}" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
            @error('specific_date') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div x-show="scheduleType === 'weekly'" x-cloak>
            <label for="day_of_week" class="block text-sm font-semibold text-slate-700">Hari</label>
            <select id="day_of_week" name="day_of_week" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                @foreach ([0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'] as $value => $label)
                    <option value="{{ $value }}" @selected((string) old('day_of_week', $agendaSchedule->day_of_week ?? '') === (string) $value)>{{ $label }}</option>
                @endforeach
            </select>
            @error('day_of_week') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div x-show="scheduleType === 'monthly'" x-cloak>
            <label for="day_of_month" class="block text-sm font-semibold text-slate-700">Tanggal Setiap Bulan</label>
            <input id="day_of_month" name="day_of_month" type="number" min="1" max="31" value="{{ old('day_of_month', $agendaSchedule->day_of_month ?? '') }}" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
            @error('day_of_month') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="start_time" class="block text-sm font-semibold text-slate-700">Waktu Mulai</label>
            <input id="start_time" name="start_time" type="text" inputmode="numeric" pattern="^([01][0-9]|2[0-3]):[0-5][0-9]$" placeholder="Contoh: 20:00" value="{{ old('start_time', isset($agendaSchedule) && $agendaSchedule->start_time ? substr($agendaSchedule->start_time, 0, 5) : '') }}" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
            <p class="mt-2 text-xs text-slate-500">Gunakan format 24 jam, contoh 20:00.</p>
            @error('start_time') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="end_time" class="block text-sm font-semibold text-slate-700">Waktu Selesai</label>
            <input id="end_time" name="end_time" type="text" inputmode="numeric" pattern="^([01][0-9]|2[0-3]):[0-5][0-9]$" placeholder="Contoh: 20:00" value="{{ old('end_time', isset($agendaSchedule) && $agendaSchedule->end_time ? substr($agendaSchedule->end_time, 0, 5) : '') }}" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
            <p class="mt-2 text-xs text-slate-500">Gunakan format 24 jam, contoh 20:00.</p>
            @error('end_time') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div class="sm:col-span-2">
            <label for="default_location" class="block text-sm font-semibold text-slate-700">Lokasi Default</label>
            <input id="default_location" name="default_location" type="text" value="{{ old('default_location', $agendaSchedule->default_location ?? '') }}" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
            @error('default_location') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="default_latitude" class="block text-sm font-semibold text-slate-700">Latitude</label>
            <input id="default_latitude" name="default_latitude" type="number" step="0.0000001" value="{{ old('default_latitude', $agendaSchedule->default_latitude ?? '') }}" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
            @error('default_latitude') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="default_longitude" class="block text-sm font-semibold text-slate-700">Longitude</label>
            <input id="default_longitude" name="default_longitude" type="number" step="0.0000001" value="{{ old('default_longitude', $agendaSchedule->default_longitude ?? '') }}" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
            @error('default_longitude') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="default_radius" class="block text-sm font-semibold text-slate-700">Radius Presensi Default (meter)</label>
            <input id="default_radius" name="default_radius" type="number" min="1" value="{{ old('default_radius', $agendaSchedule->default_radius ?? 100) }}" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600" required>
            @error('default_radius') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="is_active" class="block text-sm font-semibold text-slate-700">Status</label>
            <select id="is_active" name="is_active" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600" required>
                <option value="1" @selected((string) old('is_active', isset($agendaSchedule) ? (int) $agendaSchedule->is_active : 1) === '1')>Aktif</option>
                <option value="0" @selected((string) old('is_active', isset($agendaSchedule) ? (int) $agendaSchedule->is_active : 1) === '0')>Nonaktif</option>
            </select>
            @error('is_active') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:justify-end">
        <a href="{{ route('agenda-schedules.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Batal</a>
        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">Simpan</button>
    </div>
</div>
