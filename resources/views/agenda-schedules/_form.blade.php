@csrf

@php
    $selectedType = old('schedule_type', $agendaSchedule->schedule_type ?? 'incidental');
    $selectedLocationId = old('activity_location_id', $agendaSchedule->activity_location_id ?? '');
    $legacyLocation = [
        'name' => old('default_location', $agendaSchedule->default_location ?? ''),
        'latitude' => old('default_latitude', $agendaSchedule->default_latitude ?? ''),
        'longitude' => old('default_longitude', $agendaSchedule->default_longitude ?? ''),
        'radius_meters' => old('default_radius', $agendaSchedule->default_radius ?? 100),
    ];
    $locationsPayload = $activityLocations->mapWithKeys(fn ($location) => [
        (string) $location->id => [
            'id' => (string) $location->id,
            'name' => $location->name,
            'address' => $location->address,
            'latitude' => $location->latitude !== null ? (string) $location->latitude : '',
            'longitude' => $location->longitude !== null ? (string) $location->longitude : '',
            'radius_meters' => $location->radius_meters,
            'is_active' => $location->is_active,
        ],
    ])->all();
    $inputClass = 'mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600';
    $labelClass = 'block text-sm font-semibold text-slate-700';
    $helperClass = 'mt-1 text-xs text-slate-500';
    $errorClass = 'mt-2 text-sm text-red-600';
@endphp

<div
    x-data="{
        scheduleType: @js($selectedType),
        selectedLocationId: @js((string) $selectedLocationId),
        locations: @js($locationsPayload),
        legacyLocation: @js($legacyLocation),
        get selectedLocation() {
            return this.locations[this.selectedLocationId] || null;
        },
        locationValue(key) {
            if (this.selectedLocation) {
                return this.selectedLocation[key] ?? '';
            }

            return this.legacyLocation[key] ?? '';
        },
    }"
    class="space-y-5"
>
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
                <input id="start_time" name="start_time" type="text" placeholder="HH:mm" value="{{ old('start_time', isset($agendaSchedule) && $agendaSchedule->start_time ? substr($agendaSchedule->start_time, 0, 5) : '') }}" class="js-time-picker {{ $inputClass }}">
                <p class="{{ $helperClass }}">Klik field untuk memilih jam. Format tersimpan tetap 24 jam, contoh 20:00.</p>
                @error('start_time') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="end_time" class="{{ $labelClass }}">Waktu selesai</label>
                <input id="end_time" name="end_time" type="text" placeholder="HH:mm" value="{{ old('end_time', isset($agendaSchedule) && $agendaSchedule->end_time ? substr($agendaSchedule->end_time, 0, 5) : '') }}" class="js-time-picker {{ $inputClass }}">
                <p class="{{ $helperClass }}">Klik field untuk memilih jam selesai. Waktu selesai harus setelah waktu mulai.</p>
                @error('end_time') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label for="activity_location_id" class="{{ $labelClass }}">Lokasi default</label>
                <select id="activity_location_id" name="activity_location_id" x-model="selectedLocationId" class="{{ $inputClass }}">
                    <option value="">Pilih lokasi kegiatan</option>
                    @foreach ($activityLocations as $location)
                        <option value="{{ $location->id }}" @selected((string) $selectedLocationId === (string) $location->id)>
                            {{ $location->name }}{{ $location->is_active ? '' : ' (Nonaktif)' }}
                        </option>
                    @endforeach
                </select>
                <p class="{{ $helperClass }}">Lokasi aktif diambil dari Master Data &gt; Lokasi Kegiatan.</p>
                @error('activity_location_id') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
                @error('default_location') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <input type="hidden" name="default_location" :value="locationValue('name')">
            <input type="hidden" name="default_latitude" :value="locationValue('latitude')">
            <input type="hidden" name="default_longitude" :value="locationValue('longitude')">
            <input type="hidden" name="default_radius" :value="locationValue('radius_meters') || 100">

            <div>
                <p class="{{ $labelClass }}">Latitude default</p>
                <div class="mt-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700" x-text="locationValue('latitude') || '-'"></div>
                <p class="{{ $helperClass }}">Terisi otomatis dari lokasi yang dipilih.</p>
                @error('default_latitude') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <p class="{{ $labelClass }}">Longitude default</p>
                <div class="mt-2 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700" x-text="locationValue('longitude') || '-'"></div>
                <p class="{{ $helperClass }}">Terisi otomatis dari lokasi yang dipilih.</p>
                @error('default_longitude') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <p class="{{ $labelClass }}">Detail lokasi terpilih</p>
                <div class="mt-2 rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <template x-if="selectedLocation">
                        <div class="grid gap-3 text-sm text-slate-700 md:grid-cols-3">
                            <div class="md:col-span-3">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Alamat/Keterangan</p>
                                <p class="mt-1 whitespace-pre-line" x-text="selectedLocation.address || '-'"></p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Latitude</p>
                                <p class="mt-1 font-semibold" x-text="selectedLocation.latitude || '-'"></p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Longitude</p>
                                <p class="mt-1 font-semibold" x-text="selectedLocation.longitude || '-'"></p>
                            </div>
                            <div>
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Radius</p>
                                <p class="mt-1 font-semibold"><span x-text="selectedLocation.radius_meters"></span> meter</p>
                            </div>
                        </div>
                    </template>

                    <template x-if="! selectedLocation">
                        <div>
                            <p class="text-sm font-semibold text-slate-700">Belum memilih lokasi master.</p>
                            <p class="mt-1 text-sm text-slate-500">Pilih lokasi agar latitude, longitude, dan radius default terisi otomatis.</p>
                            <template x-if="legacyLocation.name">
                                <p class="mt-3 rounded-lg bg-white px-3 py-2 text-xs text-slate-500">
                                    Lokasi lama tetap dipertahankan: <span class="font-semibold text-slate-700" x-text="legacyLocation.name"></span>.
                                </p>
                            </template>
                        </div>
                    </template>
                </div>
                <p class="{{ $helperClass }}">Untuk menambah pilihan, buka Master Data &gt; Lokasi Kegiatan.</p>
                @error('default_radius') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <div class="flex flex-col-reverse gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:justify-end">
        <a href="{{ route('agenda-schedules.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">Batal/Kembali</a>
        <x-ui.submit-button loading-text="Menyimpan...">Simpan</x-ui.submit-button>
    </div>
</div>
