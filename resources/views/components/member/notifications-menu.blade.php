@php
    $user = Auth::user();
    $hasNotificationsTable = \Illuminate\Support\Facades\Schema::hasTable('notifications');
    $notifications = collect();
    $unreadCount = 0;

    if ($user && $hasNotificationsTable) {
        $notifications = \App\Models\Notification::query()
            ->where('user_id', $user->id)
            ->latest()
            ->limit(5)
            ->get();

        $unreadCount = \App\Models\Notification::query()
            ->where('user_id', $user->id)
            ->unread()
            ->count();
    }

    $typeClasses = [
        'activity_created' => 'bg-sky-50 text-sky-700 ring-sky-100',
        'activity_updated' => 'bg-sky-50 text-sky-700 ring-sky-100',
        'activity_reminder' => 'bg-sky-50 text-sky-700 ring-sky-100',
        'attendance_opened' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        'attendance_open' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        'attendance_not_submitted' => 'bg-amber-50 text-amber-700 ring-amber-100',
        'attendance_pending' => 'bg-amber-50 text-amber-700 ring-amber-100',
        'attendance_closing_soon' => 'bg-orange-50 text-orange-700 ring-orange-100',
        'attendance_closing' => 'bg-orange-50 text-orange-700 ring-orange-100',
        'attendance_pending_verification' => 'bg-amber-50 text-amber-700 ring-amber-100',
        'attendance_verification_pending' => 'bg-amber-50 text-amber-700 ring-amber-100',
        'attendance_verified' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        'attendance_rejected' => 'bg-red-50 text-red-700 ring-red-100',
        'activity_changed' => 'bg-sky-50 text-sky-700 ring-sky-100',
        'attendance_instruction' => 'bg-emerald-50 text-emerald-700 ring-emerald-100',
        'attendance_warning' => 'bg-orange-50 text-orange-700 ring-orange-100',
        'admin_announcement' => 'bg-violet-50 text-violet-700 ring-violet-100',
        'profile_update' => 'bg-violet-50 text-violet-700 ring-violet-100',
        'general' => 'bg-slate-100 text-slate-700 ring-slate-200',
        'profile_incomplete' => 'bg-violet-50 text-violet-700 ring-violet-100',
    ];
@endphp

<div class="relative" x-data="{ open: false }" @keydown.escape.window="open = false" @click.outside="open = false">
    <button
        type="button"
        class="relative inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-white/85 text-slate-600 shadow-sm transition hover:border-emerald-200 hover:bg-emerald-50 hover:text-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2 dark:border-slate-800 dark:bg-slate-900 dark:text-slate-300 dark:hover:border-emerald-500/40 dark:hover:bg-emerald-500/10 dark:hover:text-emerald-200 dark:focus:ring-offset-slate-950"
        @click="open = ! open"
        aria-label="Buka notifikasi"
        title="Notifikasi"
        :aria-expanded="open.toString()"
    >
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.85 18.25a2.85 2.85 0 0 1-5.7 0M18.5 9.5a6.5 6.5 0 0 0-13 0c0 6.75-2.5 7.75-2.5 7.75h18s-2.5-1-2.5-7.75Z" />
        </svg>
        @if ($unreadCount > 0)
            <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-600 px-1.5 text-[11px] font-bold text-white ring-2 ring-white dark:ring-slate-900">
                {{ $unreadCount > 99 ? '99+' : $unreadCount }}
            </span>
        @endif
    </button>

    <div
        x-cloak
        x-show="open"
        x-transition.origin.top.right
        class="absolute right-0 z-50 mt-2 w-[min(calc(100vw-2rem),24rem)] overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl shadow-slate-200/70 dark:border-slate-800 dark:bg-slate-900 dark:shadow-black/30"
    >
        <div class="flex items-start justify-between gap-3 border-b border-slate-100 px-4 py-3 dark:border-slate-800">
            <div>
                <h2 class="text-sm font-bold text-slate-950 dark:text-white">Notifikasi</h2>
                <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                    {{ $unreadCount > 0 ? $unreadCount.' belum dibaca' : 'Semua sudah dibaca' }}
                </p>
            </div>
            @if ($unreadCount > 0)
                <form method="POST" action="{{ route('member.notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="text-xs font-bold text-emerald-700 hover:text-emerald-900 hover:underline">Tandai semua</button>
                </form>
            @endif
        </div>

        <div class="border-b border-slate-100 p-3 dark:border-slate-800">
            <x-member.push-notification-toggle />
        </div>

        <div class="max-h-[70vh] divide-y divide-slate-100 overflow-y-auto dark:divide-slate-800">
            @forelse ($notifications as $notification)
                @php
                    $toneClass = $typeClasses[$notification->type] ?? 'bg-slate-100 text-slate-700 ring-slate-200';
                    $redirectTo = $notification->url ?: route('member.notifications.index', absolute: false);
                @endphp
                <form method="POST" action="{{ route('member.notifications.read', $notification) }}">
                    @csrf
                    <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
                    <button type="submit" class="block w-full px-4 py-3 text-left transition hover:bg-slate-50 dark:hover:bg-slate-800/70">
                        <div class="flex gap-3">
                            <span class="{{ $toneClass }} mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full ring-1 ring-inset">
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path d="M10 2a6 6 0 0 0-6 6v2.764l-.894 1.789A1 1 0 0 0 4 14h12a1 1 0 0 0 .894-1.447L16 10.764V8a6 6 0 0 0-6-6ZM8.25 15a1.75 1.75 0 0 0 3.5 0h-3.5Z" />
                                </svg>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="flex items-start justify-between gap-2">
                                    <span class="line-clamp-1 text-sm font-bold text-slate-950 dark:text-white">{{ $notification->title }}</span>
                                    @if (! $notification->read_at)
                                        <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-emerald-500"></span>
                                    @endif
                                </span>
                                <span class="mt-1 line-clamp-2 text-xs leading-5 text-slate-600 dark:text-slate-400">{{ $notification->message }}</span>
                                <span class="mt-1 block text-[11px] font-medium text-slate-400">{{ $notification->created_at->diffForHumans() }}</span>
                            </span>
                        </div>
                    </button>
                </form>
            @empty
                <div class="px-4 py-8 text-center">
                    <div class="mx-auto flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.85 18.25a2.85 2.85 0 0 1-5.7 0M18.5 9.5a6.5 6.5 0 0 0-13 0c0 6.75-2.5 7.75-2.5 7.75h18s-2.5-1-2.5-7.75Z" />
                        </svg>
                    </div>
                    <p class="mt-3 text-sm font-bold text-slate-900 dark:text-white">Belum ada notifikasi</p>
                    <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Informasi penting akan muncul di sini.</p>
                </div>
            @endforelse
        </div>

        <div class="border-t border-slate-100 p-3 dark:border-slate-800">
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">
                <a href="{{ route('member.notification-settings.edit') }}" class="inline-flex w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-700 transition hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200" @click="open = false">
                    Pengaturan
                </a>
                <a href="{{ route('member.notifications.index') }}" class="inline-flex w-full items-center justify-center rounded-xl bg-slate-900 px-3 py-2 text-sm font-bold text-white transition hover:bg-slate-700 dark:bg-slate-100 dark:text-slate-950 dark:hover:bg-white" @click="open = false">
                Lihat semua notifikasi
                </a>
            </div>
        </div>
    </div>
</div>
