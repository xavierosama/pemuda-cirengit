@php
    $member = $user->member;
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
                        <h3 class="text-base font-bold text-slate-950">Informasi Anggota</h3>
                        <p class="mt-1 text-sm text-slate-500">Data anggota ini bersifat read-only dari halaman profil.</p>
                    </div>

                    <dl class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                        <div>
                            <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">Nama Anggota</dt>
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
                            <dt class="text-xs font-bold uppercase tracking-wide text-slate-500">No HP</dt>
                            <dd class="mt-1 text-sm text-slate-700">{{ $member->phone ?: '-' }}</dd>
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
