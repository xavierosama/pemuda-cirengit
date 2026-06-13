@switch($icon)
    @case('dashboard')
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.75 11.25 12 5l7.25 6.25v7a1.5 1.5 0 0 1-1.5 1.5H6.25a1.5 1.5 0 0 1-1.5-1.5v-7Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 19.75v-5h4.5v5" />
        </svg>
        @break

    @case('users')
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.25a5.75 5.75 0 0 0-11.5 0M10 11.25a3.25 3.25 0 1 0 0-6.5 3.25 3.25 0 0 0 0 6.5ZM19.75 18.75a4.75 4.75 0 0 0-4-4.69M15.5 4.95a3 3 0 0 1 0 5.85" />
        </svg>
        @break

    @case('folder')
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 7.75A2 2 0 0 1 5.75 5.75h4.1l1.75 2h6.65a2 2 0 0 1 2 2v7.5a2 2 0 0 1-2 2H5.75a2 2 0 0 1-2-2v-9.5Z" />
        </svg>
        @break

    @case('badge')
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7.75 4.75h8.5a2 2 0 0 1 2 2v12.5L12 16.5l-6.25 2.75V6.75a2 2 0 0 1 2-2Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 9h6M9 12h4" />
        </svg>
        @break

    @case('calendar')
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7.75 4.75v2.5M16.25 4.75v2.5M4.75 9.25h14.5M6.75 6.25h10.5a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H6.75a2 2 0 0 1-2-2v-9a2 2 0 0 1 2-2Z" />
        </svg>
        @break

    @case('sparkles')
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m12 4.75 1.45 4.05 4.05 1.45-4.05 1.45L12 15.75l-1.45-4.05-4.05-1.45 4.05-1.45L12 4.75ZM17.75 15.25l.65 1.85 1.85.65-1.85.65-.65 1.85-.65-1.85-1.85-.65 1.85-.65.65-1.85Z" />
        </svg>
        @break

    @case('clipboard')
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.25 5.75h5.5a1.5 1.5 0 0 1 1.5 1.5v.5h1a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H6.75a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h1v-.5a1.5 1.5 0 0 1 1.5-1.5Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 11.25h7.5M8.25 14.25h7.5" />
        </svg>
        @break

    @case('chart')
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 19.25h13.5M7.25 16.25v-4.5M12 16.25v-8.5M16.75 16.25v-6.5" />
        </svg>
        @break

    @case('cog')
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 4.95 10.45 3.5h3.1l.7 1.45 1.55.65 1.55-.5 2.2 2.2-.5 1.55.65 1.55 1.45.7v3.1l-1.45.7-.65 1.55.5 1.55-2.2 2.2-1.55-.5-1.55.65-.7 1.45h-3.1l-.7-1.45-1.55-.65-1.55.5-2.2-2.2.5-1.55-.65-1.55-1.45-.7v-3.1l1.45-.7.65-1.55-.5-1.55 2.2-2.2 1.55.5 1.55-.65Z" />
            <circle cx="12" cy="12" r="3.25" />
        </svg>
        @break

    @default
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <circle cx="12" cy="12" r="7.25" />
        </svg>
@endswitch
