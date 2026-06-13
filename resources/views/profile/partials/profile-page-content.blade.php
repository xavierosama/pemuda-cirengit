@php
    $member = $user->member;
    $profilePhotoUrl = $member?->profile_photo ? asset('storage/'.$member->profile_photo) : null;
    $initial = strtoupper(substr($member?->full_name ?? $user->name, 0, 1));
    $memberStatusLabels = ['active' => 'Aktif', 'inactive' => 'Tidak Aktif', 'alumni' => 'Alumni', 'moved' => 'Pindah'];
    $memberStatusClasses = [
        'active' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'inactive' => 'bg-slate-100 text-slate-600 ring-slate-200',
        'alumni' => 'bg-sky-50 text-sky-700 ring-sky-200',
        'moved' => 'bg-amber-50 text-amber-700 ring-amber-200',
    ];
@endphp

<div class="mx-auto max-w-5xl space-y-5">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wide text-emerald-700">Pengaturan Akun</p>
            <h2 class="mt-1 text-2xl font-bold text-slate-950">Edit Profil</h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">Kelola informasi akun, password, dan lihat data anggota yang terhubung.</p>
        </div>
        <a href="{{ $backRoute }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
            {{ $backLabel }}
        </a>
    </div>

    <div class="grid gap-5 lg:grid-cols-[1fr_0.95fr]">
        <div class="space-y-5">
            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                @include('profile.partials.update-profile-information-form')
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                @include('profile.partials.update-password-form')
            </section>
        </div>

        <div class="space-y-5">
            @if ($member)
                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <div class="border-b border-slate-100 pb-4">
                        <h3 class="text-base font-bold text-slate-950">Profil Anggota</h3>
                        <p class="mt-1 text-sm text-slate-500">Perbarui foto, no HP, dan alamat. Data organisasi tetap dikunci oleh pengurus.</p>
                    </div>

                    @if (session('status') === 'member-profile-updated')
                        <div class="mt-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
                            Profil anggota berhasil diperbarui.
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mt-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('profile.member.update') }}" enctype="multipart/form-data" class="mt-5 space-y-5">
                        @csrf
                        @method('patch')

                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                            <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-emerald-700 text-2xl font-bold text-white ring-1 ring-inset ring-emerald-800">
                                @if ($profilePhotoUrl)
                                    <img src="{{ $profilePhotoUrl }}" alt="Foto profil {{ $member->full_name }}" class="h-full w-full object-cover">
                                @else
                                    {{ $initial }}
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <label for="profile_photo" class="block text-sm font-semibold text-slate-800">Foto Profil</label>
                                <input id="profile_photo" name="profile_photo" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white text-sm text-slate-700 file:mr-4 file:border-0 file:bg-emerald-50 file:px-4 file:py-2.5 file:text-sm file:font-semibold file:text-emerald-700 hover:file:bg-emerald-100 focus:border-emerald-500 focus:ring-emerald-500">
                                <p class="mt-1 text-xs text-slate-500">Format jpg, jpeg, png, atau webp. Maksimal 2MB.</p>
                                <x-input-error :messages="$errors->get('profile_photo')" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <label for="phone" class="block text-sm font-semibold text-slate-800">No HP</label>
                            <input id="phone" name="phone" type="text" value="{{ old('phone', $member->phone) }}" class="mt-2 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Contoh: 081234567890">
                            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                        </div>

                        <div>
                            <label for="address" class="block text-sm font-semibold text-slate-800">Alamat</label>
                            <textarea id="address" name="address" rows="3" class="mt-2 block w-full rounded-xl border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500" placeholder="Masukkan alamat">{{ old('address', $member->address) }}</textarea>
                            <x-input-error :messages="$errors->get('address')" class="mt-2" />
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-emerald-700 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
                                Simpan Profil Anggota
                            </button>
                        </div>
                    </form>

                    <dl class="mt-6 grid gap-4 border-t border-slate-100 pt-5 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Nama Lengkap Anggota</dt>
                            <dd class="mt-1 text-sm font-semibold text-slate-900">{{ $member->full_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">NPA</dt>
                            <dd class="mt-1 text-sm text-slate-700">{{ $member->npa ?: '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Bidang</dt>
                            <dd class="mt-1 text-sm text-slate-700">{{ $member->department?->name ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Jabatan</dt>
                            <dd class="mt-1 text-sm text-slate-700">{{ $member->position?->name ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Status Anggota</dt>
                            <dd class="mt-1">
                                <span class="{{ $memberStatusClasses[$member->member_status] ?? $memberStatusClasses['inactive'] }} inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset">{{ $memberStatusLabels[$member->member_status] ?? $member->member_status }}</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Tanggal Bergabung</dt>
                            <dd class="mt-1 text-sm text-slate-700">{{ $member->joined_at?->format('d/m/Y') ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Email Login</dt>
                            <dd class="mt-1 text-sm text-slate-700">{{ $user->email }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Role Akun</dt>
                            <dd class="mt-1 text-sm text-slate-700">{{ $user->role ?? '-' }}</dd>
                        </div>
                    </dl>
                </section>
            @endif

            <section class="rounded-2xl border border-red-100 bg-white p-5 shadow-sm sm:p-6">
                @include('profile.partials.delete-user-form')
            </section>
        </div>
    </div>
</div>
