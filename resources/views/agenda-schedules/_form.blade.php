@csrf

@php
    $selectedType = old('schedule_type', $agendaSchedule->schedule_type ?? 'incidental');
    $inputClass = 'mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600';
    $labelClass = 'block text-sm font-semibold text-slate-700';
    $helperClass = 'mt-1 text-xs text-slate-500';
    $errorClass = 'mt-2 text-sm text-red-600';
@endphp

<div x-data="{ scheduleType: @js($selectedType) }" class="space-y-5">
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-5 border-b border-slate-100 pb-4">
            <h3 class="text-base font-bold text-slate-950">Informasi Agenda</h3>
            <p class="mt-1 text-sm text-slate-500">Data dasar agenda rutin atau agenda satu kali yang menjadi acuan kegiatan.</p>
        </div>
        <div class="grid gap-5 md:grid-cols-2">
            <div class="md:col-span-2">
                <label for="title" class="{{ $labelClass }}">Nama/Judul Agenda</label>
                <input id="title" name="title" type="text" value="{{ old('title', $agendaSchedule->title ?? '') }}" class="{{ $inputClass }}" required autofocus>
                @error('title') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label for="description" class="{{ $labelClass }}">Deskripsi</label>
                <textarea id="description" name="description" rows="4" class="{{ $inputClass }}">{{ old('description', $agendaSchedule->description ?? '') }}</textarea>
                @error('description') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="department_id" class="{{ $labelClass }}">Bidang</label>
                <select id="department_id" name="department_id" class="{{ $inputClass }}">
                    <option value="">Tanpa bidang</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected((string) old('department_id', $agendaSchedule->department_id ?? '') === (string) $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
                @error('department_id') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="pic_id" class="{{ $labelClass }}">PIC</label>
                <select id="pic_id" name="pic_id" class="{{ $inputClass }}">
                    <option value="">Tanpa PIC</option>
                    @foreach ($members as $member)
                        <option value="{{ $member->id }}" @selected((string) old('pic_id', $agendaSchedule->pic_id ?? '') === (string) $member->id)>{{ $member->full_name }}</option>
                    @endforeach
                </select>
                @error('pic_id') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="is_active" class="{{ $labelClass }}">Status Aktif</label>
                <select id="is_active" name="is_active" class="{{ $inputClass }}" required>
                    <option value="1" @selected((string) old('is_active', isset($agendaSchedule) ? (int) $agendaSchedule->is_active : 1) === '1')>Aktif</option>
                    <option value="0" @selected((string) old('is_active', isset($agendaSchedule) ? (int) $agendaSchedule->is_active : 1) === '0')>Nonaktif</option>
                </select>
                @error('is_active') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-5 border-b border-slate-100 pb-4">
            <h3 class="text-base font-bold text-slate-950">Pola Jadwal</h3>
            <p class="mt-1 text-sm text-slate-500">Pilih pola agar field tanggal/hari yang relevan saja yang perlu diisi.</p>
        </div>
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="schedule_type" class="{{ $labelClass }}">Tipe jadwal</label>
                <select id="schedule_type" name="schedule_type" x-model="scheduleType" class="{{ $inputClass }}" required>
                    <option value="incidental">Insidental</option>
                    <option value="weekly">Mingguan</option>
                    <option value="monthly">Bulanan</option>
                    <option value="yearly">Tahunan</option>
                </select>
                @error('schedule_type') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div x-show="scheduleType === 'incidental'" x-cloak>
                <label for="specific_date" class="{{ $labelClass }}">Tanggal spesifik</label>
                <input id="specific_date" name="specific_date" type="date" value="{{ old('specific_date', isset($agendaSchedule) && $agendaSchedule->specific_date ? $agendaSchedule->specific_date->format('Y-m-d') : '') }}" class="{{ $inputClass }}">
                @error('specific_date') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div x-show="scheduleType === 'weekly'" x-cloak>
                <label for="day_of_week" class="{{ $labelClass }}">Hari dalam pekan</label>
                <select id="day_of_week" name="day_of_week" class="{{ $inputClass }}">
                    @foreach ([0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'] as $value => $label)
                        <option value="{{ $value }}" @selected((string) old('day_of_week', $agendaSchedule->day_of_week ?? '') === (string) $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('day_of_week') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div x-show="scheduleType === 'monthly'" x-cloak>
                <label for="day_of_month" class="{{ $labelClass }}">Tanggal dalam bulan</label>
                <input id="day_of_month" name="day_of_month" type="number" min="1" max="31" value="{{ old('day_of_month', $agendaSchedule->day_of_month ?? '') }}" class="{{ $inputClass }}">
                @error('day_of_month') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-5 border-b border-slate-100 pb-4">
            <h3 class="text-base font-bold text-slate-950">Waktu & Lokasi Default</h3>
            <p class="mt-1 text-sm text-slate-500">Nilai ini akan menjadi rujukan saat kegiatan dibuat dari jadwal agenda.</p>
        </div>
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="start_time" class="{{ $labelClass }}">Waktu mulai</label>
                <input id="start_time" name="start_time" type="text" inputmode="numeric" pattern="^([01][0-9]|2[0-3]):[0-5][0-9]$" placeholder="Contoh: 20:00" value="{{ old('start_time', isset($agendaSchedule) && $agendaSchedule->start_time ? substr($agendaSchedule->start_time, 0, 5) : '') }}" class="{{ $inputClass }}">
                <p class="{{ $helperClass }}">Gunakan format 24 jam, contoh 20:00.</p>
                @error('start_time') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="end_time" class="{{ $labelClass }}">Waktu selesai</label>
                <input id="end_time" name="end_time" type="text" inputmode="numeric" pattern="^([01][0-9]|2[0-3]):[0-5][0-9]$" placeholder="Contoh: 20:00" value="{{ old('end_time', isset($agendaSchedule) && $agendaSchedule->end_time ? substr($agendaSchedule->end_time, 0, 5) : '') }}" class="{{ $inputClass }}">
                <p class="{{ $helperClass }}">Gunakan format 24 jam, contoh 20:00.</p>
                @error('end_time') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label for="default_location" class="{{ $labelClass }}">Lokasi default</label>
                <input id="default_location" name="default_location" type="text" value="{{ old('default_location', $agendaSchedule->default_location ?? '') }}" class="{{ $inputClass }}">
                @error('default_location') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="default_latitude" class="{{ $labelClass }}">Latitude default</label>
                <input id="default_latitude" name="default_latitude" type="number" step="0.0000001" value="{{ old('default_latitude', $agendaSchedule->default_latitude ?? '') }}" class="{{ $inputClass }}">
                <p class="{{ $helperClass }}">Isi sesuai titik lokasi kegiatan.</p>
                @error('default_latitude') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="default_longitude" class="{{ $labelClass }}">Longitude default</label>
                <input id="default_longitude" name="default_longitude" type="number" step="0.0000001" value="{{ old('default_longitude', $agendaSchedule->default_longitude ?? '') }}" class="{{ $inputClass }}">
                <p class="{{ $helperClass }}">Isi sesuai titik lokasi kegiatan.</p>
                @error('default_longitude') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="default_radius" class="{{ $labelClass }}">Radius default</label>
                <input id="default_radius" name="default_radius" type="number" min="1" value="{{ old('default_radius', $agendaSchedule->default_radius ?? 100) }}" class="{{ $inputClass }}" required>
                <p class="{{ $helperClass }}">Dalam meter, contoh: 100.</p>
                @error('default_radius') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <div class="flex flex-col-reverse gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:justify-end">
        <a href="{{ route('agenda-schedules.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">Batal/Kembali</a>
        <x-ui.submit-button loading-text="Menyimpan...">Simpan</x-ui.submit-button>
    </div>
</div>
