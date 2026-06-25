@php
    $systemSettings = app(\App\Support\SystemSettings::class);
    $appName = $systemSettings->get('app_name');
    $organizationName = $systemSettings->get('organization_name');
    $appLogoUrl = $systemSettings->assetUrl('app_logo');
    $faviconUrl = $systemSettings->assetUrl('favicon');
    $themeMode = $systemSettings->themeMode();
    $member = $user->member;
    $displayName = $member?->full_name ?? $user->name;
    $initial = strtoupper(substr($displayName, 0, 1));
    $profilePhotoUrl = $member?->profile_photo ? asset('storage/'.$member->profile_photo) : null;
    $memberStatusLabels = ['active' => 'Aktif', 'inactive' => 'Tidak Aktif', 'alumni' => 'Alumni', 'moved' => 'Pindah'];
    $dateValue = old('birth_date') !== null
        ? \App\Support\DateFormatter::normalizeInputDate(old('birth_date'))
        : \App\Support\DateFormatter::normalizeInputDate($member?->birth_date);
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ $themeMode === 'dark' ? 'dark' : '' }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Edit Profil Anggota - {{ $appName }}</title>

        @if ($faviconUrl)
            <link rel="icon" href="{{ $faviconUrl }}">
        @endif

        <script>
            (() => {
                const themeMode = @json($themeMode);
                if (themeMode === 'dark' || (themeMode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                }
            })();
        </script>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-slate-100 font-sans antialiased text-slate-900 dark:bg-slate-950 dark:text-slate-100">
        <div class="min-h-screen">
            <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/90 shadow-sm shadow-slate-200/50 backdrop-blur-xl dark:border-slate-800 dark:bg-slate-900/90 dark:shadow-black/20">
                <div class="mx-auto flex max-w-5xl items-center justify-between gap-3 px-4 py-2.5 sm:px-6 lg:px-8">
                    <a href="{{ route('member.home') }}" class="flex items-center gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-emerald-700 text-xs font-bold text-white">
                            @if ($appLogoUrl)
                                <img src="{{ $appLogoUrl }}" alt="{{ $appName }}" class="h-full w-full object-contain p-1.5">
                            @else
                                {{ str($appName)->substr(0, 2)->upper() }}
                            @endif
                        </span>
                        <span>
                            <span class="block max-w-40 truncate text-sm font-bold text-slate-950 dark:text-white sm:max-w-none">{{ $appName }}</span>
                            <span class="block text-xs text-slate-500 dark:text-slate-400">Profil Anggota</span>
                        </span>
                    </a>

                    <x-member.account-menu :user="$user" :member="$member" />
                </div>
            </header>

            <main class="px-4 py-5 sm:px-6 sm:py-6 lg:px-8">
                <div class="mx-auto max-w-5xl space-y-5">
                    <x-ui.breadcrumb :items="[
                        ['label' => 'Dashboard Anggota', 'url' => route('member.home')],
                        ['label' => 'Edit Profil'],
                    ]" />

                    @if (session('status') === 'member-profile-updated')
                        <x-ui.card class="border-emerald-200 bg-emerald-50">
                            <p class="text-sm font-semibold text-emerald-800">Profil anggota berhasil diperbarui.</p>
                        </x-ui.card>
                    @endif

                    @if (session('status') === 'password-updated')
                        <x-ui.card class="border-emerald-200 bg-emerald-50">
                            <p class="text-sm font-semibold text-emerald-800">Password berhasil diperbarui.</p>
                        </x-ui.card>
                    @endif

                    @if (session('error'))
                        <x-ui.card class="border-red-200 bg-red-50">
                            <p class="text-sm font-semibold text-red-800">{{ session('error') }}</p>
                        </x-ui.card>
                    @endif

                    <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-5">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                            <div class="flex min-w-0 gap-4">
                                <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-emerald-700 text-xl font-bold text-white ring-1 ring-inset ring-emerald-800">
                                    @if ($profilePhotoUrl)
                                        <img src="{{ $profilePhotoUrl }}" alt="Foto profil {{ $displayName }}" class="h-full w-full object-cover">
                                    @else
                                        {{ $initial }}
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">{{ $organizationName }}</p>
                                    <h1 class="mt-1 text-2xl font-bold text-slate-950 dark:text-white">Edit Profil Anggota</h1>
                                    <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-600 dark:text-slate-300">Perbarui data pribadi yang boleh dikelola anggota. Data administratif tetap dikelola pengurus.</p>
                                </div>
                            </div>
                            <x-ui.button :href="route('member.home')" variant="secondary" size="sm">Kembali</x-ui.button>
                        </div>
                    </section>

                    <div class="grid gap-5 lg:grid-cols-[1.35fr_0.65fr]">
                        <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-5">
                            <div class="border-b border-slate-100 pb-4 dark:border-slate-800">
                                <h2 class="text-base font-bold text-slate-950 dark:text-white">Data Pribadi</h2>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Foto, no HP, alamat, dan tanggal lahir dapat diperbarui oleh anggota.</p>
                            </div>

                            <form method="POST" action="{{ route('member.profile.update') }}" enctype="multipart/form-data" class="mt-5 space-y-5" x-data="{ submitting: false }" x-on:submit="submitting = true">
                                @csrf
                                @method('PATCH')

                                <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                                    <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-emerald-700 text-2xl font-bold text-white ring-1 ring-inset ring-emerald-800">
                                        @if ($profilePhotoUrl)
                                            <img src="{{ $profilePhotoUrl }}" alt="Foto profil {{ $displayName }}" class="h-full w-full object-cover">
                                        @else
                                            {{ $initial }}
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <label for="profile_photo" class="block text-sm font-semibold text-slate-800 dark:text-slate-100">Foto Profil</label>
                                        <input id="profile_photo" name="profile_photo" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white text-sm text-slate-700 file:mr-4 file:border-0 file:bg-emerald-50 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-emerald-700 hover:file:bg-emerald-100 focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-200">
                                        <p class="mt-1 text-xs text-slate-500">Format jpg, jpeg, png, atau webp. Maksimal 2MB.</p>
                                        <x-input-error :messages="$errors->get('profile_photo')" class="mt-2" />
                                    </div>
                                </div>

                                <div class="grid gap-5 sm:grid-cols-2">
                                    <div>
                                        <label for="phone" class="block text-sm font-semibold text-slate-800 dark:text-slate-100">No HP</label>
                                        <input id="phone" name="phone" type="text" value="{{ old('phone', $member?->phone) }}" class="mt-2 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" placeholder="Contoh: 081234567890">
                                        <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                                    </div>

                                    <div>
                                        <label for="birth_date" class="block text-sm font-semibold text-slate-800 dark:text-slate-100">Tanggal Lahir</label>
                                        <input id="birth_date" name="birth_date" type="text" value="{{ $dateValue }}" placeholder="dd/mm/yyyy" class="js-date-picker mt-2 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100">
                                        <p class="mt-1 text-xs text-slate-500">Klik untuk memilih tanggal. Tampilan menggunakan format dd/mm/yyyy.</p>
                                        <x-input-error :messages="$errors->get('birth_date')" class="mt-2" />
                                    </div>
                                </div>

                                <div>
                                    <label for="address" class="block text-sm font-semibold text-slate-800 dark:text-slate-100">Alamat</label>
                                    <textarea id="address" name="address" rows="3" class="mt-2 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100" placeholder="Masukkan alamat">{{ old('address', $member?->address) }}</textarea>
                                    <x-input-error :messages="$errors->get('address')" class="mt-2" />
                                </div>

                                <div class="flex justify-end">
                                    <x-ui.submit-button class="rounded-xl font-bold" size="lg" loading-text="Menyimpan...">Simpan Profil</x-ui.submit-button>
                                </div>
                            </form>
                        </section>

                        <aside class="space-y-5">
                            <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-5">
                                <h2 class="text-base font-bold text-slate-950 dark:text-white">Data Administratif</h2>
                                <p class="mt-1 text-sm text-slate-500">Data berikut hanya dapat diubah oleh pengurus.</p>
                                <dl class="mt-4 space-y-3">
                                    <div>
                                        <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Nama Anggota</dt>
                                        <dd class="mt-1 text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $member?->full_name ?? '-' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">NPA</dt>
                                        <dd class="mt-1 text-sm text-slate-700 dark:text-slate-300">{{ $member?->npa ?: '-' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Bidang</dt>
                                        <dd class="mt-1 text-sm text-slate-700 dark:text-slate-300">{{ $member?->department?->name ?? '-' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Jabatan</dt>
                                        <dd class="mt-1 text-sm text-slate-700 dark:text-slate-300">{{ $member?->position?->name ?? '-' }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Status Anggota</dt>
                                        <dd class="mt-1">
                                            @if ($member)
                                                <x-ui.status-badge :status="$member->member_status" :label="$memberStatusLabels[$member->member_status] ?? $member->member_status" />
                                            @else
                                                <x-ui.status-badge status="inactive" label="Belum terhubung" />
                                            @endif
                                        </dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Email Login</dt>
                                        <dd class="mt-1 break-all text-sm text-slate-700 dark:text-slate-300">{{ $user->email }}</dd>
                                    </div>
                                    <div>
                                        <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Role Akun</dt>
                                        <dd class="mt-1 text-sm text-slate-700 dark:text-slate-300">{{ $user->role }}</dd>
                                    </div>
                                </dl>
                            </section>

                            <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900 sm:p-5">
                                @include('profile.partials.update-password-form')
                            </section>
                        </aside>
                    </div>
                </div>
            </main>
        </div>
        <x-ui.toast />
    </body>
</html>
