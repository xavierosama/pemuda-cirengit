@props([
    'user',
    'member' => null,
])

@php
    $displayName = $member?->full_name ?? $user->name;
    $initial = strtoupper(substr($displayName, 0, 1));
    $profilePhotoUrl = $member?->profile_photo ? asset('storage/'.$member->profile_photo) : null;
@endphp

<div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false" @click.outside="open = false">
    <button
        type="button"
        class="group inline-flex cursor-pointer items-center gap-2 rounded-full border border-slate-200 bg-white/80 p-1 pr-2 text-slate-600 shadow-sm transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-emerald-500/40 dark:hover:bg-emerald-500/10 dark:hover:text-emerald-200 dark:focus:ring-offset-slate-950"
        @click="open = ! open"
        aria-label="Buka menu akun"
        title="Buka menu akun"
        :aria-expanded="open.toString()"
    >
        <span class="flex h-9 w-9 shrink-0 items-center justify-center overflow-hidden rounded-full bg-emerald-700 text-sm font-bold uppercase text-white ring-1 ring-inset ring-emerald-800 transition group-hover:ring-emerald-600">
            @if ($profilePhotoUrl)
                <img src="{{ $profilePhotoUrl }}" alt="Foto profil {{ $displayName }}" class="h-full w-full object-cover">
            @else
                {{ $initial }}
            @endif
        </span>
        <svg class="h-4 w-4 shrink-0 transition group-hover:translate-y-0.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.168l3.71-3.938a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
        </svg>
    </button>

    <div
        x-cloak
        x-show="open"
        x-transition.origin.top.right
        class="absolute right-0 z-50 mt-2 w-64 overflow-hidden rounded-2xl border border-slate-200 bg-white py-1 shadow-xl shadow-slate-200/70 dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/30"
    >
        <div class="border-b border-slate-100 px-4 py-3 dark:border-slate-800">
            <p class="truncate text-sm font-bold text-slate-950 dark:text-white">{{ $displayName }}</p>
            <p class="mt-0.5 truncate text-xs text-slate-500 dark:text-slate-400">NPA: {{ $member?->npa ?: '-' }}</p>
        </div>

        <div class="py-1">
            <a href="{{ route('member.home') }}#profil-anggota" class="block px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 hover:text-slate-950 dark:text-slate-200 dark:hover:bg-slate-800 dark:hover:text-white" @click="open = false">
                Lihat Profile
            </a>
            <a href="{{ route('member.profile.edit') }}" class="block px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 hover:text-slate-950 dark:text-slate-200 dark:hover:bg-slate-800 dark:hover:text-white" @click="open = false">
                Edit Profile
            </a>
        </div>

        <div class="border-t border-slate-100 py-1 dark:border-slate-800">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="block w-full px-4 py-2 text-left text-sm font-semibold text-red-600 transition hover:bg-red-50 dark:text-red-300 dark:hover:bg-red-500/10">
                    Logout
                </button>
            </form>
        </div>
    </div>
</div>
