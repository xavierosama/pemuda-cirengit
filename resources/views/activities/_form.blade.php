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
    $attendanceDefaults = $attendanceDefaults ?? [
        'radius' => 100,
        'open_minutes_before' => 30,
        'close_minutes_after' => 30,
        'location_accuracy_tolerance' => 50,
    ];
    $attendanceRadiusValue = old('attendance_radius', $activity->attendance_radius ?? $attendanceDefaults['radius']);
    $agendaScheduleDefaults = $agendaScheduleDefaults ?? [];
    $departmentChairPics = $departmentChairPics ?? [];
    $inputClass = 'mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600';
    $labelClass = 'block text-sm font-semibold text-slate-700';
    $helperClass = 'mt-1 text-xs text-slate-500';
    $errorClass = 'mt-2 text-sm text-red-600';
@endphp

<div
    x-data="{
        defaults: @js($attendanceDefaults),
        agendaSchedules: @js($agendaScheduleDefaults),
        departmentChairPics: @js($departmentChairPics),
        selectedAgendaScheduleId: @js((string) old('agenda_schedule_id', $activity->agenda_schedule_id ?? '')),
        departmentId: @js((string) old('department_id', $activity->department_id ?? '')),
        picId: @js((string) old('pic_id', $activity->pic_id ?? '')),
        description: @js(old('description', $activity->description ?? '')),
        activityDate: @js(old('activity_date', isset($activity) && $activity->activity_date ? $activity->activity_date->format('Y-m-d') : '')),
        startTime: @js(old('start_time', isset($activity) && $activity->start_time ? substr($activity->start_time, 0, 5) : '')),
        endTime: @js(old('end_time', isset($activity) && $activity->end_time ? substr($activity->end_time, 0, 5) : '')),
        location: @js(old('location', $activity->location ?? '')),
        latitude: @js((string) old('latitude', $activity->latitude ?? '')),
        longitude: @js((string) old('longitude', $activity->longitude ?? '')),
        attendanceRadius: @js((string) $attendanceRadiusValue),
        picHelper: '',
        applyingAgenda: false,
        setDepartmentChairPic(departmentId) {
            const chairPic = this.departmentChairPics[String(departmentId)] || '';
            if (chairPic) {
                this.picId = String(chairPic);
                this.picHelper = '';
                return;
            }

            this.picId = '';
            this.picHelper = departmentId ? 'Ketua bidang belum tersedia untuk bidang ini.' : '';
        },
        applyAgendaDefaults(agendaScheduleId) {
            const agenda = this.agendaSchedules[String(agendaScheduleId)];
            if (! agenda) return;

            this.applyingAgenda = true;
            this.departmentId = agenda.department_id || '';
            this.description = agenda.description || '';
            this.startTime = agenda.start_time || '';
            this.endTime = agenda.end_time || '';
            this.location = agenda.default_location || '';
            this.latitude = agenda.default_latitude || '';
            this.longitude = agenda.default_longitude || '';
            this.attendanceRadius = agenda.default_radius || String(this.defaults.radius);

            if (agenda.pic_id) {
                this.picId = String(agenda.pic_id);
                this.picHelper = '';
            } else {
                this.setDepartmentChairPic(this.departmentId);
            }

            this.applyingAgenda = false;
        },
    }"
    x-init="$watch('selectedAgendaScheduleId', value => applyAgendaDefaults(value)); $watch('departmentId', value => { if (! applyingAgenda) setDepartmentChairPic(value) }); if (! attendanceRadius) attendanceRadius = String(defaults.radius);"
    class="space-y-5"
>
    @if (session('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
    @endif

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-5 border-b border-slate-100 pb-4">
            <h3 class="text-base font-bold text-slate-950">Informasi Kegiatan</h3>
            <p class="mt-1 text-sm text-slate-500">Data utama kegiatan aktual, termasuk sumber jadwal, penanggung jawab, dan status pelaksanaan.</p>
        </div>
        <div class="grid gap-5 md:grid-cols-2">
            <div class="md:col-span-2">
                <label for="title" class="{{ $labelClass }}">Nama/Judul Kegiatan</label>
                <input id="title" name="title" type="text" value="{{ old('title', $activity->title ?? '') }}" class="{{ $inputClass }}" required autofocus>
                @error('title') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label for="description" class="{{ $labelClass }}">Deskripsi</label>
                <textarea id="description" name="description" rows="3" x-model="description" class="{{ $inputClass }}"></textarea>
                @error('description') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="agenda_schedule_id" class="{{ $labelClass }}">Jadwal Agenda asal</label>
                <select id="agenda_schedule_id" name="agenda_schedule_id" x-model="selectedAgendaScheduleId" class="{{ $inputClass }}">
                    <option value="">Tanpa jadwal agenda</option>
                    @foreach ($agendaSchedules as $agendaSchedule)
                        <option value="{{ $agendaSchedule->id }}" @selected((string) old('agenda_schedule_id', $activity->agenda_schedule_id ?? '') === (string) $agendaSchedule->id)>{{ $agendaSchedule->title }}</option>
                    @endforeach
                </select>
                @error('agenda_schedule_id') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="department_id" class="{{ $labelClass }}">Bidang</label>
                <select id="department_id" name="department_id" x-model="departmentId" class="{{ $inputClass }}">
                    <option value="">Tanpa bidang</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected((string) old('department_id', $activity->department_id ?? '') === (string) $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
                @error('department_id') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="pic_id" class="{{ $labelClass }}">PIC</label>
                <select id="pic_id" name="pic_id" x-model="picId" class="{{ $inputClass }}">
                    <option value="">Tanpa PIC</option>
                    @foreach ($members as $member)
                        <option value="{{ $member->id }}" @selected((string) old('pic_id', $activity->pic_id ?? '') === (string) $member->id)>{{ $member->full_name }}</option>
                    @endforeach
                </select>
                <p x-show="picHelper" x-cloak x-text="picHelper" class="{{ $helperClass }}"></p>
                @error('pic_id') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="status" class="{{ $labelClass }}">Status Kegiatan</label>
                <select id="status" name="status" class="{{ $inputClass }}" required>
                    @foreach ($statuses as $statusValue)
                        <option value="{{ $statusValue }}" @selected(old('status', $activity->status ?? 'scheduled') === $statusValue)>{{ $statusLabels[$statusValue] }}</option>
                    @endforeach
                </select>
                @error('status') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label for="change_reason" class="{{ $labelClass }}">Alasan perubahan</label>
                <textarea id="change_reason" name="change_reason" rows="3" class="{{ $inputClass }}">{{ old('change_reason', $activity->change_reason ?? '') }}</textarea>
                <p class="{{ $helperClass }}">Isi jika kegiatan libur, pindah lokasi, ditunda, atau dibatalkan.</p>
                @error('change_reason') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-5 border-b border-slate-100 pb-4">
            <h3 class="text-base font-bold text-slate-950">Tanggal, Waktu, dan Lokasi</h3>
            <p class="mt-1 text-sm text-slate-500">Pastikan tanggal, jam, dan titik lokasi sesuai dengan pelaksanaan kegiatan.</p>
        </div>
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="activity_date" class="{{ $labelClass }}">Tanggal kegiatan</label>
                <input id="activity_date" name="activity_date" type="date" x-model="activityDate" class="{{ $inputClass }}" required>
                <p class="{{ $helperClass }}">Format tampilan setelah tersimpan: dd/mm/yyyy.</p>
                @error('activity_date') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="attendance_radius" class="{{ $labelClass }}">Radius presensi</label>
                <input id="attendance_radius" name="attendance_radius" type="number" min="1" x-model="attendanceRadius" class="{{ $inputClass }}" required>
                <p class="{{ $helperClass }}">Dalam meter, contoh: {{ $attendanceDefaults['radius'] }}. Nilai default presensi dapat diubah di Pengaturan Sistem.</p>
                @error('attendance_radius') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="start_time" class="{{ $labelClass }}">Waktu mulai</label>
                <input id="start_time" name="start_time" type="text" inputmode="numeric" pattern="^([01][0-9]|2[0-3]):[0-5][0-9]$" placeholder="Contoh: 20:00" x-model="startTime" class="{{ $inputClass }}">
                <p class="{{ $helperClass }}">Gunakan format 24 jam, contoh 20:00.</p>
                @error('start_time') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="end_time" class="{{ $labelClass }}">Waktu selesai</label>
                <input id="end_time" name="end_time" type="text" inputmode="numeric" pattern="^([01][0-9]|2[0-3]):[0-5][0-9]$" placeholder="Contoh: 20:00" x-model="endTime" class="{{ $inputClass }}">
                <p class="{{ $helperClass }}">Gunakan format 24 jam, contoh 20:00.</p>
                @error('end_time') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label for="location" class="{{ $labelClass }}">Lokasi</label>
                <input id="location" name="location" type="text" x-model="location" class="{{ $inputClass }}">
                @error('location') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="latitude" class="{{ $labelClass }}">Latitude</label>
                <input id="latitude" name="latitude" type="number" step="0.0000001" x-model="latitude" class="{{ $inputClass }}">
                <p class="{{ $helperClass }}">Isi sesuai titik lokasi kegiatan.</p>
                @error('latitude') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="longitude" class="{{ $labelClass }}">Longitude</label>
                <input id="longitude" name="longitude" type="number" step="0.0000001" x-model="longitude" class="{{ $inputClass }}">
                <p class="{{ $helperClass }}">Isi sesuai titik lokasi kegiatan.</p>
                @error('longitude') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-5 border-b border-slate-100 pb-4">
            <h3 class="text-base font-bold text-slate-950">Jadwal Presensi Otomatis</h3>
            <p class="mt-1 text-sm text-slate-500">Presensi dihitung otomatis dari tanggal, waktu mulai, dan waktu selesai kegiatan.</p>
        </div>
        <div class="grid gap-5 md:grid-cols-2">
            <div class="rounded-xl border border-emerald-100 bg-emerald-50 p-4 text-sm text-emerald-800 md:col-span-2">
                <p class="font-semibold">Presensi tersedia otomatis untuk kegiatan berstatus Terjadwal atau Pindah Lokasi.</p>
                <p class="mt-1">Waktu buka memakai default {{ $attendanceDefaults['open_minutes_before'] }} menit sebelum kegiatan, dan waktu tutup mengikuti jam selesai kegiatan.</p>
            </div>
            @if (isset($activity) && $activity->attendance_token)
                <div>
                    <label for="attendance_token_display" class="{{ $labelClass }}">Attendance token</label>
                    <input id="attendance_token_display" type="text" value="{{ $activity->attendance_token }}" class="{{ $inputClass }} bg-slate-50 font-mono text-xs text-slate-600" readonly>
                    <p class="{{ $helperClass }}">Token otomatis untuk link/QR presensi.</p>
                </div>
            @endif
        </div>
    </section>

    <div class="flex flex-col-reverse gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:justify-end">
        <a href="{{ route('activities.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">Batal/Kembali</a>
        <x-ui.submit-button loading-text="Menyimpan...">Simpan</x-ui.submit-button>
    </div>
</div>
