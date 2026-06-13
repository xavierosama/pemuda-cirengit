@extends('layouts.admin')

@section('title', 'Pengaturan Sistem - '.app(\App\Support\SystemSettings::class)->get('app_name'))
@section('section', 'Pengaturan')
@section('page-title', 'Pengaturan Sistem')

@section('content')
    <div class="mx-auto max-w-5xl space-y-6">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">Identitas & Tampilan</p>
                <h2 class="mt-1 text-2xl font-bold text-slate-950">Pengaturan Sistem</h2>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">Kelola nama aplikasi, nama organisasi, logo, favicon, dan mode tema dasar.</p>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                {{ session('success') }}
            </div>
        @endif

        <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            @csrf
            @method('put')

            <div class="grid gap-5 lg:grid-cols-2">
                <div>
                    <label for="app_name" class="block text-sm font-semibold text-slate-800">Nama Aplikasi</label>
                    <input id="app_name" name="app_name" type="text" value="{{ old('app_name', $settings['app_name']) }}" class="mt-2 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Pemuda Cirengit" required>
                    <x-input-error :messages="$errors->get('app_name')" class="mt-2" />
                </div>

                <div>
                    <label for="organization_name" class="block text-sm font-semibold text-slate-800">Nama Organisasi</label>
                    <input id="organization_name" name="organization_name" type="text" value="{{ old('organization_name', $settings['organization_name']) }}" class="mt-2 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Pemuda Persis Cirengit" required>
                    <x-input-error :messages="$errors->get('organization_name')" class="mt-2" />
                </div>

                <div>
                    <label for="theme_mode" class="block text-sm font-semibold text-slate-800">Theme Mode</label>
                    <select id="theme_mode" name="theme_mode" class="mt-2 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" required>
                        @foreach (['light' => 'Light', 'dark' => 'Dark', 'system' => 'System'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('theme_mode', $settings['theme_mode']) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-slate-500">Mode system mengikuti preferensi dark/light dari browser pengguna.</p>
                    <x-input-error :messages="$errors->get('theme_mode')" class="mt-2" />
                </div>
            </div>

            <div class="mt-6 grid gap-5 lg:grid-cols-3">
                @foreach ([
                    'app_logo' => ['label' => 'Logo Aplikasi', 'hint' => 'Dipakai di sidebar/topbar.'],
                    'login_logo' => ['label' => 'Logo Login', 'hint' => 'Dipakai di halaman login.'],
                    'favicon' => ['label' => 'Favicon', 'hint' => 'Ikon kecil pada tab browser.'],
                ] as $field => $meta)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-400">
                                @if ($logoUrls[$field])
                                    <img src="{{ $logoUrls[$field] }}" alt="{{ $meta['label'] }}" class="h-full w-full object-contain p-2">
                                @else
                                    Logo
                                @endif
                            </div>
                            <div>
                                <label for="{{ $field }}" class="block text-sm font-semibold text-slate-800">{{ $meta['label'] }}</label>
                                <p class="mt-1 text-xs text-slate-500">{{ $meta['hint'] }}</p>
                            </div>
                        </div>
                        <input id="{{ $field }}" name="{{ $field }}" type="file" accept=".jpg,.jpeg,.png,.webp,.svg,.ico,image/jpeg,image/png,image/webp,image/svg+xml,image/x-icon" class="mt-4 block w-full rounded-xl border border-slate-300 bg-white text-sm text-slate-700 file:mr-4 file:border-0 file:bg-emerald-50 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-emerald-700 hover:file:bg-emerald-100 focus:border-emerald-500 focus:ring-emerald-500">
                        <p class="mt-1 text-xs text-slate-500">Format jpg, jpeg, png, webp, svg{{ $field === 'favicon' ? ', atau ico' : '' }}. Maksimal 2MB.</p>
                        <x-input-error :messages="$errors->get($field)" class="mt-2" />
                    </div>
                @endforeach
            </div>

            <div class="mt-8 border-t border-slate-200 pt-6">
                <div class="mb-5">
                    <h3 class="text-base font-bold text-slate-950">Pengaturan Presensi</h3>
                    <p class="mt-1 text-sm text-slate-500">Nilai default untuk membantu pengisian presensi saat membuat Kegiatan Aktual baru.</p>
                </div>

                <div class="grid gap-5 lg:grid-cols-2">
                    <div>
                        <label for="default_attendance_radius" class="block text-sm font-semibold text-slate-800">Default Radius Presensi</label>
                        <div class="mt-2 flex rounded-xl shadow-sm">
                            <input id="default_attendance_radius" name="default_attendance_radius" type="number" min="1" value="{{ old('default_attendance_radius', $settings['default_attendance_radius']) }}" class="block w-full rounded-l-xl border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500" required>
                            <span class="inline-flex items-center rounded-r-xl border border-l-0 border-slate-300 bg-slate-50 px-3 text-sm font-medium text-slate-600">meter</span>
                        </div>
                        <x-input-error :messages="$errors->get('default_attendance_radius')" class="mt-2" />
                    </div>

                    <div>
                        <label for="default_attendance_open_minutes_before" class="block text-sm font-semibold text-slate-800">Buka Presensi Sebelum Kegiatan</label>
                        <div class="mt-2 flex rounded-xl shadow-sm">
                            <input id="default_attendance_open_minutes_before" name="default_attendance_open_minutes_before" type="number" min="0" value="{{ old('default_attendance_open_minutes_before', $settings['default_attendance_open_minutes_before']) }}" class="block w-full rounded-l-xl border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500" required>
                            <span class="inline-flex items-center rounded-r-xl border border-l-0 border-slate-300 bg-slate-50 px-3 text-sm font-medium text-slate-600">menit</span>
                        </div>
                        <x-input-error :messages="$errors->get('default_attendance_open_minutes_before')" class="mt-2" />
                    </div>

                    <div>
                        <label for="default_attendance_close_minutes_after" class="block text-sm font-semibold text-slate-800">Tutup Presensi Setelah Kegiatan</label>
                        <div class="mt-2 flex rounded-xl shadow-sm">
                            <input id="default_attendance_close_minutes_after" name="default_attendance_close_minutes_after" type="number" min="0" value="{{ old('default_attendance_close_minutes_after', $settings['default_attendance_close_minutes_after']) }}" class="block w-full rounded-l-xl border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500" required>
                            <span class="inline-flex items-center rounded-r-xl border border-l-0 border-slate-300 bg-slate-50 px-3 text-sm font-medium text-slate-600">menit</span>
                        </div>
                        <x-input-error :messages="$errors->get('default_attendance_close_minutes_after')" class="mt-2" />
                    </div>

                    <div>
                        <label for="default_location_accuracy_tolerance" class="block text-sm font-semibold text-slate-800">Toleransi Akurasi Lokasi</label>
                        <div class="mt-2 flex rounded-xl shadow-sm">
                            <input id="default_location_accuracy_tolerance" name="default_location_accuracy_tolerance" type="number" min="0" value="{{ old('default_location_accuracy_tolerance', $settings['default_location_accuracy_tolerance']) }}" class="block w-full rounded-l-xl border-slate-300 text-sm focus:border-emerald-500 focus:ring-emerald-500" required>
                            <span class="inline-flex items-center rounded-r-xl border border-l-0 border-slate-300 bg-slate-50 px-3 text-sm font-medium text-slate-600">meter</span>
                        </div>
                        <x-input-error :messages="$errors->get('default_location_accuracy_tolerance')" class="mt-2" />
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-700 px-5 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
@endsection
