@props([
    'activity',
])

@php
    $template = app(\App\Support\SystemSettings::class)->whatsappGroupReminderTemplate();
    $attendanceUrl = $activity->attendance_token
        ? route('attendance.check-in.show', $activity->attendance_token, true)
        : 'Link presensi belum tersedia. Buka QR Presensi untuk membuat token.';
    $dayLabels = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $dayName = $activity->activity_date ? $dayLabels[$activity->activity_date->dayOfWeek] : '-';
    $activityDate = \App\Support\DateFormatter::date($activity->activity_date);
    $startTime = \App\Support\DateFormatter::time($activity->start_time);
    $endTime = \App\Support\DateFormatter::time($activity->end_time);
    $location = $activity->location ?: 'Menyesuaikan';
    $topic = trim((string) $activity->topic);
    $templateLines = preg_split("/\r\n|\n|\r/", $template);

    if ($topic === '') {
        $templateLines = array_values(array_filter(
            $templateLines,
            fn ($line) => ! str_contains($line, '{topic}')
        ));
    }

    $reminderMessage = str_replace(
        [
            '{nama_kegiatan}',
            '{topic}',
            '{hari_tanggal}',
            '{jam_mulai}',
            '{jam_selesai}',
            '{lokasi}',
            '{link_presensi}',
        ],
        [
            $activity->title,
            $topic,
            $dayName.', '.$activityDate,
            $startTime,
            $endTime,
            $location,
            $attendanceUrl,
        ],
        implode("\n", $templateLines)
    );
@endphp

<x-ui.card
    class="space-y-5"
    x-data="{
        copied: false,
        copying: false,
        copyReminder() {
            if (this.copying) return;
            this.copying = true;
            const text = this.$refs.reminderMessage.value;
            const markCopied = () => {
                this.copied = true;
                this.copying = false;
                window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'success', message: 'Pesan reminder berhasil disalin.' } }));
                setTimeout(() => this.copied = false, 2500);
            };
            const markFailed = () => {
                this.copying = false;
                window.dispatchEvent(new CustomEvent('toast', { detail: { type: 'error', message: 'Pesan gagal disalin. Silakan salin manual dari textarea.' } }));
            };

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(markCopied).catch(markFailed);
                return;
            }

            this.$refs.reminderMessage.select();
            document.execCommand('copy');
            markCopied();
        },
    }"
>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Reminder WhatsApp Grup</p>
            <h3 class="mt-2 text-lg font-bold text-slate-950">Pesan pengingat kegiatan</h3>
            <p class="mt-1 text-sm leading-6 text-slate-500">Pesan ini belum dikirim otomatis. Silakan edit jika diperlukan, salin pesan, lalu kirim manual ke grup WhatsApp Pemuda.</p>
        </div>
        <p x-cloak x-show="copied" x-transition class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-100">Pesan disalin</p>
    </div>

    <textarea
        x-ref="reminderMessage"
        rows="14"
        class="block w-full resize-y rounded-xl border border-slate-200 bg-white px-4 py-3 font-mono text-sm leading-6 text-slate-800 shadow-inner focus:border-emerald-600 focus:ring-emerald-600"
    >{{ $reminderMessage }}</textarea>

    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
        <button
            type="button"
            x-on:click="copyReminder()"
            x-bind:disabled="copying"
            class="inline-flex items-center justify-center gap-2 rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-80"
        >
            <svg x-cloak x-show="copying" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle class="opacity-25" cx="12" cy="12" r="9" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-90" fill="currentColor" d="M21 12a9 9 0 0 0-9-9v4a5 5 0 0 1 5 5h4Z"></path>
            </svg>
            <span x-show="! copying">Salin Pesan</span>
            <span x-cloak x-show="copying">Menyalin...</span>
        </button>
        <x-ui.button href="https://web.whatsapp.com/" target="_blank" rel="noopener noreferrer" variant="secondary">Buka WhatsApp Web</x-ui.button>
    </div>
</x-ui.card>
