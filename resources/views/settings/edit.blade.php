@extends('layouts.admin')

@section('title', 'Pengaturan Sistem - '.app(\App\Support\SystemSettings::class)->get('app_name'))
@section('section', 'Pengaturan')
@section('page-title', 'Pengaturan Sistem')
@section('breadcrumb')
    <x-ui.breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Pengaturan Sistem'],
    ]" />
@endsection

@section('content')
    @php
        $fieldClass = 'mt-2 block w-full rounded-xl border-slate-300 text-sm shadow-sm transition focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:placeholder:text-slate-500';
        $unitInputClass = 'block w-full rounded-l-xl border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100';
        $unitClass = 'inline-flex items-center rounded-r-xl border border-l-0 border-slate-300 bg-slate-50 px-3 text-sm font-medium text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300';
        $labelClass = 'text-sm font-semibold text-slate-800 dark:text-slate-100';
        $helperClass = 'mt-1 text-xs leading-5 text-slate-500 dark:text-slate-400';
        $sectionIconClass = 'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-100 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-400/20';
    @endphp

    <form
        method="POST"
        action="{{ route('settings.update') }}"
        enctype="multipart/form-data"
        class="space-y-6"
        x-data="{ submitting: false, whatsappTemplate: @js(old('whatsapp_group_reminder_template', $settings['whatsapp_group_reminder_template'])), defaultWhatsappTemplate: @js(\App\Support\SystemSettings::DEFAULT_WHATSAPP_GROUP_REMINDER_TEMPLATE) }"
        x-on:submit="submitting = true"
    >
        @csrf
        @method('put')

        <x-ui.page-header
            title="Pengaturan Sistem"
            description="Kelola identitas aplikasi, tampilan, dan pengaturan default presensi."
            eyebrow="Konfigurasi Aplikasi"
        >
            <x-slot name="action">
                <x-ui.submit-button size="lg" loading-text="Menyimpan...">Simpan Pengaturan</x-ui.submit-button>
            </x-slot>
        </x-ui.page-header>

        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(320px,0.72fr)]">
            <div class="space-y-6">
                <x-ui.card padding="lg" class="dark:border-slate-800 dark:bg-slate-900">
                    <div class="mb-6 flex items-start gap-4">
                        <div class="{{ $sectionIconClass }}">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3.75 4.5 7.5 12 11.25l7.5-3.75L12 3.75Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12 12 15.75 19.5 12M4.5 16.5 12 20.25l7.5-3.75" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-950 dark:text-white">Identitas Aplikasi</h3>
                            <p class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400">Nama dan identitas utama yang tampil di layout admin, member, dan halaman login.</p>
                        </div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <x-input-label for="app_name" value="Nama Aplikasi" class="{{ $labelClass }}" />
                            <input id="app_name" name="app_name" type="text" value="{{ old('app_name', $settings['app_name']) }}" class="{{ $fieldClass }}" placeholder="Pemuda Cirengit" required>
                            <p class="{{ $helperClass }}">Ditampilkan pada sidebar, topbar, dan judul halaman.</p>
                            <x-input-error :messages="$errors->get('app_name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="organization_name" value="Nama Organisasi" class="{{ $labelClass }}" />
                            <input id="organization_name" name="organization_name" type="text" value="{{ old('organization_name', $settings['organization_name']) }}" class="{{ $fieldClass }}" placeholder="Pemuda Persis Cirengit" required>
                            <p class="{{ $helperClass }}">Digunakan sebagai informasi organisasi di beberapa tampilan.</p>
                            <x-input-error :messages="$errors->get('organization_name')" class="mt-2" />
                        </div>
                    </div>
                </x-ui.card>

                <x-ui.card padding="lg" class="dark:border-slate-800 dark:bg-slate-900">
                    <div class="mb-6 flex items-start justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <div class="{{ $sectionIconClass }}">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.75 8.25h8.5M7.75 12h5.5M5.75 19.25l2.75-2h9.75a2 2 0 0 0 2-2v-8.5a2 2 0 0 0-2-2H5.75a2 2 0 0 0-2 2v8.5a2 2 0 0 0 2 2v2Z" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-950 dark:text-white">Template Pesan WhatsApp Grup</h3>
                                <p class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400">Atur template default reminder kegiatan yang bisa disalin admin ke grup WhatsApp Pemuda.</p>
                            </div>
                        </div>
                        <button type="button" x-on:click="whatsappTemplate = defaultWhatsappTemplate" class="shrink-0 rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800">Reset ke Template Default</button>
                    </div>

                    <div>
                        <x-input-label for="whatsapp_group_reminder_template" value="Template Reminder Grup" class="{{ $labelClass }}" />
                        <textarea
                            id="whatsapp_group_reminder_template"
                            name="whatsapp_group_reminder_template"
                            rows="14"
                            x-model="whatsappTemplate"
                            class="{{ $fieldClass }} font-mono leading-6"
                            placeholder="Tulis template pesan reminder WhatsApp grup."
                        ></textarea>
                        <div class="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-950/60">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Placeholder tersedia</p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach (['{nama_kegiatan}', '{topic}', '{hari_tanggal}', '{jam_mulai}', '{jam_selesai}', '{lokasi}', '{link_presensi}'] as $placeholder)
                                    <code class="rounded-lg bg-white px-2 py-1 text-xs font-semibold text-slate-700 ring-1 ring-inset ring-slate-200 dark:bg-slate-900 dark:text-slate-200 dark:ring-slate-700">{{ $placeholder }}</code>
                                @endforeach
                            </div>
                        </div>
                        <p class="{{ $helperClass }}">Jika topik kegiatan kosong, baris template yang mengandung {topic} otomatis dihapus dari pesan reminder.</p>
                        <x-input-error :messages="$errors->get('whatsapp_group_reminder_template')" class="mt-2" />
                    </div>
                </x-ui.card>

                <x-ui.card padding="lg" class="dark:border-slate-800 dark:bg-slate-900">
                    <div class="mb-6 flex items-start gap-4">
                        <div class="{{ $sectionIconClass }}">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.75 5.75h14.5v8.5H4.75z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 18.25h8M12 14.25v4" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-950 dark:text-white">Tampilan</h3>
                            <p class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400">Atur mode tema aplikasi agar mengikuti kebutuhan penggunaan harian.</p>
                        </div>
                    </div>

                    <div class="max-w-md">
                        <x-input-label for="theme_mode" value="Mode Tema" class="{{ $labelClass }}" />
                        <select id="theme_mode" name="theme_mode" class="{{ $fieldClass }}" required>
                            @foreach (['light' => 'Light', 'dark' => 'Dark', 'system' => 'System'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('theme_mode', $settings['theme_mode']) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="{{ $helperClass }}">Mode system mengikuti preferensi dark/light dari browser pengguna.</p>
                        <x-input-error :messages="$errors->get('theme_mode')" class="mt-2" />
                    </div>
                </x-ui.card>

                <x-ui.card padding="lg" class="dark:border-slate-800 dark:bg-slate-900">
                    <div class="mb-6 flex items-start gap-4">
                        <div class="{{ $sectionIconClass }}">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7.75 14.25 10 16.5l6.25-7" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a8.25 8.25 0 1 0 0-16.5A8.25 8.25 0 0 0 12 21Z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-950 dark:text-white">Default Presensi</h3>
                            <p class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400">Digunakan otomatis saat membuat Kegiatan Aktual.</p>
                        </div>
                    </div>

                    <div class="grid gap-5 md:grid-cols-2">
                        <div>
                            <x-input-label for="default_attendance_radius" value="Default Radius Presensi" class="{{ $labelClass }}" />
                            <div class="mt-2 flex rounded-xl shadow-sm">
                                <input id="default_attendance_radius" name="default_attendance_radius" type="number" min="1" value="{{ old('default_attendance_radius', $settings['default_attendance_radius']) }}" class="{{ $unitInputClass }}" required>
                                <span class="{{ $unitClass }}">meter</span>
                            </div>
                            <p class="{{ $helperClass }}">Digunakan sebagai radius default lokasi presensi.</p>
                            <x-input-error :messages="$errors->get('default_attendance_radius')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="default_attendance_open_minutes_before" value="Buka Presensi Sebelum Kegiatan" class="{{ $labelClass }}" />
                            <div class="mt-2 flex rounded-xl shadow-sm">
                                <input id="default_attendance_open_minutes_before" name="default_attendance_open_minutes_before" type="number" min="0" value="{{ old('default_attendance_open_minutes_before', $settings['default_attendance_open_minutes_before']) }}" class="{{ $unitInputClass }}" required>
                                <span class="{{ $unitClass }}">menit</span>
                            </div>
                            <p class="{{ $helperClass }}">Contoh: 30 berarti presensi dibuka 30 menit sebelum kegiatan dimulai.</p>
                            <x-input-error :messages="$errors->get('default_attendance_open_minutes_before')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="default_location_accuracy_tolerance" value="Toleransi Akurasi Lokasi" class="{{ $labelClass }}" />
                            <div class="mt-2 flex rounded-xl shadow-sm">
                                <input id="default_location_accuracy_tolerance" name="default_location_accuracy_tolerance" type="number" min="0" value="{{ old('default_location_accuracy_tolerance', $settings['default_location_accuracy_tolerance']) }}" class="{{ $unitInputClass }}" required>
                                <span class="{{ $unitClass }}">meter</span>
                            </div>
                            <p class="{{ $helperClass }}">Batas toleransi akurasi GPS yang masih dianggap aman saat check-in.</p>
                            <x-input-error :messages="$errors->get('default_location_accuracy_tolerance')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="default_attendance_close_minutes_after" value="Tutup Presensi Setelah Kegiatan" class="{{ $labelClass }}" />
                            <div class="mt-2 flex rounded-xl shadow-sm">
                                <input id="default_attendance_close_minutes_after" name="default_attendance_close_minutes_after" type="number" min="0" value="{{ old('default_attendance_close_minutes_after', $settings['default_attendance_close_minutes_after']) }}" class="{{ $unitInputClass }}" required>
                                <span class="{{ $unitClass }}">menit</span>
                            </div>
                            <p class="{{ $helperClass }}">Disimpan untuk kompatibilitas pengaturan lama; jadwal presensi saat ini mengikuti jam selesai kegiatan.</p>
                            <x-input-error :messages="$errors->get('default_attendance_close_minutes_after')" class="mt-2" />
                        </div>
                    </div>
                </x-ui.card>
            </div>

            <div class="space-y-6">
                <x-ui.card padding="lg" class="dark:border-slate-800 dark:bg-slate-900">
                    <div class="mb-6 flex items-start gap-4">
                        <div class="{{ $sectionIconClass }}">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.75 6.75h12.5v10.5H5.75z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="m5.75 15 3.5-3.25 2.5 2.25 2.25-2 4.25 3.75" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9.25h.01" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-950 dark:text-white">Logo & Ikon</h3>
                            <p class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400">Upload aset visual aplikasi. Preview akan tampil jika file sudah tersimpan.</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        @foreach ([
                            'app_logo' => ['label' => 'Logo Aplikasi', 'hint' => 'Dipakai di sidebar/topbar.', 'placeholder' => 'App'],
                            'login_logo' => ['label' => 'Logo Halaman Login', 'hint' => 'Dipakai di halaman login.', 'placeholder' => 'Login'],
                            'favicon' => ['label' => 'Favicon', 'hint' => 'Ikon kecil pada tab browser.', 'placeholder' => 'Icon'],
                        ] as $field => $meta)
                            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-slate-800 dark:bg-slate-950/60">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-400 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-500">
                                        @if ($logoUrls[$field])
                                            <img src="{{ $logoUrls[$field] }}" alt="{{ $meta['label'] }}" class="h-full w-full object-contain p-2">
                                        @else
                                            {{ $meta['placeholder'] }}
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <x-input-label for="{{ $field }}" value="{{ $meta['label'] }}" class="{{ $labelClass }}" />
                                        <p class="{{ $helperClass }}">{{ $meta['hint'] }}</p>
                                    </div>
                                </div>
                                <input id="{{ $field }}" name="{{ $field }}" type="file" accept=".jpg,.jpeg,.png,.webp,.svg,.ico,image/jpeg,image/png,image/webp,image/svg+xml,image/x-icon" class="mt-4 block w-full rounded-xl border border-slate-300 bg-white text-sm text-slate-700 shadow-sm file:mr-4 file:border-0 file:bg-emerald-50 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-emerald-700 hover:file:bg-emerald-100 focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200 dark:file:bg-emerald-500/10 dark:file:text-emerald-300">
                                <p class="{{ $helperClass }}">Rekomendasi: PNG transparan, maksimal 2MB{{ $field === 'favicon' ? '; favicon boleh format ICO.' : '.' }}</p>
                                <x-input-error :messages="$errors->get($field)" class="mt-2" />
                            </div>
                        @endforeach
                    </div>
                </x-ui.card>

                <x-ui.card padding="lg" class="dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-start gap-4">
                        <div class="{{ $sectionIconClass }}">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 11.25v5M12 7.75h.01" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18Z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-950 dark:text-white">Informasi Organisasi</h3>
                            <p class="mt-1 text-sm leading-6 text-slate-500 dark:text-slate-400">Saat ini pengaturan organisasi yang tersedia adalah nama organisasi. Alamat, kontak, dan email organisasi belum memiliki key setting tersendiri.</p>
                        </div>
                    </div>
                </x-ui.card>
            </div>
        </div>

        <x-ui.card padding="md" class="sticky bottom-4 z-10 border-emerald-100 bg-white/95 backdrop-blur dark:border-slate-800 dark:bg-slate-900/95">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-slate-500 dark:text-slate-400">Perubahan akan diterapkan setelah pengaturan berhasil disimpan.</p>
                <x-ui.submit-button size="lg" loading-text="Menyimpan...">Simpan Pengaturan</x-ui.submit-button>
            </div>
        </x-ui.card>
    </form>
@endsection
