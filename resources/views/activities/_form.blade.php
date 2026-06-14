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
    $isCreateForm = ! isset($activity);
    $attendanceDefaults = $attendanceDefaults ?? [
        'radius' => 100,
        'open_minutes_before' => 30,
        'close_minutes_after' => 30,
        'location_accuracy_tolerance' => 50,
    ];
    $attendanceEnabled = (string) old('attendance_enabled', isset($activity) ? (int) $activity->attendance_enabled : 0);
    $attendanceRadiusValue = old('attendance_radius', $activity->attendance_radius ?? $attendanceDefaults['radius']);
    $attendanceOpenAtValue = old('attendance_open_at', isset($activity) && $activity->attendance_open_at ? $activity->attendance_open_at->format('Y-m-d\TH:i') : '');
    $attendanceCloseAtValue = old('attendance_close_at', isset($activity) && $activity->attendance_close_at ? $activity->attendance_close_at->format('Y-m-d\TH:i') : '');
    $inputClass = 'mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600';
    $labelClass = 'block text-sm font-semibold text-slate-700';
    $helperClass = 'mt-1 text-xs text-slate-500';
    $errorClass = 'mt-2 text-sm text-red-600';
@endphp

<div
    x-data="{
        attendanceEnabled: @js($attendanceEnabled),
        isCreateForm: @js($isCreateForm),
        defaults: @js($attendanceDefaults),
        activityDate: @js(old('activity_date', isset($activity) && $activity->activity_date ? $activity->activity_date->format('Y-m-d') : '')),
        startTime: @js(old('start_time', isset($activity) && $activity->start_time ? substr($activity->start_time, 0, 5) : '')),
        endTime: @js(old('end_time', isset($activity) && $activity->end_time ? substr($activity->end_time, 0, 5) : '')),
        attendanceRadius: @js((string) $attendanceRadiusValue),
        attendanceOpenAt: @js($attendanceOpenAtValue),
        attendanceCloseAt: @js($attendanceCloseAtValue),
        generatedOpenAt: '',
        generatedCloseAt: '',
        toDatetimeLocal(date, time, minutes) {
            if (! date || ! time) return '';
            const value = new Date(`${date}T${time}`);
            if (Number.isNaN(value.getTime())) return '';
            value.setMinutes(value.getMinutes() + minutes);
            const offset = value.getTimezoneOffset();
            return new Date(value.getTime() - offset * 60000).toISOString().slice(0, 16);
        },
        applyDefaults() {
            if (! this.isCreateForm || this.attendanceEnabled !== '1') return;
            if (! this.attendanceRadius) this.attendanceRadius = String(this.defaults.radius);
            const nextOpenAt = this.toDatetimeLocal(this.activityDate, this.startTime, -Number(this.defaults.open_minutes_before));
            const nextCloseAt = this.toDatetimeLocal(this.activityDate, this.endTime, Number(this.defaults.close_minutes_after));
            if (nextOpenAt && (! this.attendanceOpenAt || this.attendanceOpenAt === this.generatedOpenAt)) {
                this.attendanceOpenAt = nextOpenAt;
                this.generatedOpenAt = nextOpenAt;
            }
            if (nextCloseAt && (! this.attendanceCloseAt || this.attendanceCloseAt === this.generatedCloseAt)) {
                this.attendanceCloseAt = nextCloseAt;
                this.generatedCloseAt = nextCloseAt;
            }
        },
    }"
    x-init="$watch('attendanceEnabled', () => applyDefaults()); $watch('activityDate', () => applyDefaults()); $watch('startTime', () => applyDefaults()); $watch('endTime', () => applyDefaults()); applyDefaults();"
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
                <p class="{{ $helperClass }}">Kolom deskripsi kegiatan belum tersedia di database saat ini.</p>
                @error('title') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="agenda_schedule_id" class="{{ $labelClass }}">Jadwal Agenda asal</label>
                <select id="agenda_schedule_id" name="agenda_schedule_id" class="{{ $inputClass }}">
                    <option value="">Tanpa jadwal agenda</option>
                    @foreach ($agendaSchedules as $agendaSchedule)
                        <option value="{{ $agendaSchedule->id }}" @selected((string) old('agenda_schedule_id', $activity->agenda_schedule_id ?? '') === (string) $agendaSchedule->id)>{{ $agendaSchedule->title }}</option>
                    @endforeach
                </select>
                @error('agenda_schedule_id') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="department_id" class="{{ $labelClass }}">Bidang</label>
                <select id="department_id" name="department_id" class="{{ $inputClass }}">
                    <option value="">Tanpa bidang</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected((string) old('department_id', $activity->department_id ?? '') === (string) $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
                @error('department_id') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="pic_id" class="{{ $labelClass }}">PIC</label>
                <select id="pic_id" name="pic_id" class="{{ $inputClass }}">
                    <option value="">Tanpa PIC</option>
                    @foreach ($members as $member)
                        <option value="{{ $member->id }}" @selected((string) old('pic_id', $activity->pic_id ?? '') === (string) $member->id)>{{ $member->full_name }}</option>
                    @endforeach
                </select>
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
                <input id="location" name="location" type="text" value="{{ old('location', $activity->location ?? '') }}" class="{{ $inputClass }}">
                @error('location') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="latitude" class="{{ $labelClass }}">Latitude</label>
                <input id="latitude" name="latitude" type="number" step="0.0000001" value="{{ old('latitude', $activity->latitude ?? '') }}" class="{{ $inputClass }}">
                <p class="{{ $helperClass }}">Isi sesuai titik lokasi kegiatan.</p>
                @error('latitude') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="longitude" class="{{ $labelClass }}">Longitude</label>
                <input id="longitude" name="longitude" type="number" step="0.0000001" value="{{ old('longitude', $activity->longitude ?? '') }}" class="{{ $inputClass }}">
                <p class="{{ $helperClass }}">Isi sesuai titik lokasi kegiatan.</p>
                @error('longitude') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-5 border-b border-slate-100 pb-4">
            <h3 class="text-base font-bold text-slate-950">Pengaturan Presensi</h3>
            <p class="mt-1 text-sm text-slate-500">Atur apakah kegiatan memakai QR/link presensi dan kapan presensi dapat digunakan.</p>
        </div>
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="attendance_enabled" class="{{ $labelClass }}">Presensi aktif / tidak aktif</label>
                <select id="attendance_enabled" name="attendance_enabled" x-model="attendanceEnabled" class="{{ $inputClass }}" required>
                    <option value="0">Tidak aktif</option>
                    <option value="1">Aktif</option>
                </select>
                <p class="{{ $helperClass }}">Aktifkan jika kegiatan menggunakan QR/link presensi.</p>
                @error('attendance_enabled') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            @if (isset($activity) && $activity->attendance_token)
                <div>
                    <label for="attendance_token_display" class="{{ $labelClass }}">Attendance token</label>
                    <input id="attendance_token_display" type="text" value="{{ $activity->attendance_token }}" class="{{ $inputClass }} bg-slate-50 font-mono text-xs text-slate-600" readonly>
                    <p class="{{ $helperClass }}">Token otomatis untuk link/QR presensi.</p>
                </div>
            @endif

            <div x-show="attendanceEnabled === '1'" x-cloak>
                <label for="attendance_open_at" class="{{ $labelClass }}">Waktu buka presensi</label>
                <input id="attendance_open_at" name="attendance_open_at" type="datetime-local" x-model="attendanceOpenAt" class="{{ $inputClass }}">
                <p class="{{ $helperClass }}">Gunakan format waktu 24 jam. Nilai default presensi dapat diubah di Pengaturan Sistem.</p>
                @error('attendance_open_at') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div x-show="attendanceEnabled === '1'" x-cloak>
                <label for="attendance_close_at" class="{{ $labelClass }}">Waktu tutup presensi</label>
                <input id="attendance_close_at" name="attendance_close_at" type="datetime-local" x-model="attendanceCloseAt" class="{{ $inputClass }}">
                <p class="{{ $helperClass }}">Gunakan format waktu 24 jam. Nilai default presensi dapat diubah di Pengaturan Sistem.</p>
                @error('attendance_close_at') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <div class="flex flex-col-reverse gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:justify-end">
        <a href="{{ route('activities.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">Batal/Kembali</a>
        <x-ui.submit-button loading-text="Menyimpan...">Simpan</x-ui.submit-button>
    </div>
</div>
