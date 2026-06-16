@switch($icon)
    @case('eye')
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-6.75 9.75-6.75S21.75 12 21.75 12 18 18.75 12 18.75 2.25 12 2.25 12Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
        </svg>
        @break

    @case('pencil')
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m16.86 4.49 2.65 2.65M18 3.75a1.87 1.87 0 0 1 2.65 2.65L7.5 19.55 3.75 20.25l.7-3.75L18 3.75Z" />
        </svg>
        @break

    @case('trash')
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 6.75h15M9.75 6.75V5.25A1.5 1.5 0 0 1 11.25 3.75h1.5a1.5 1.5 0 0 1 1.5 1.5v1.5m-7.5 0 .75 12A1.5 1.5 0 0 0 9 20.25h6a1.5 1.5 0 0 0 1.5-1.5l.75-12M10.5 10.5v6M13.5 10.5v6" />
        </svg>
        @break

    @case('check')
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" />
        </svg>
        @break

    @case('x')
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" />
        </svg>
        @break

    @case('ban')
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <circle cx="12" cy="12" r="8.25" />
            <path stroke-linecap="round" d="m6.25 6.25 11.5 11.5" />
        </svg>
        @break

    @case('qr')
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.75 4.75h5.5v5.5h-5.5v-5.5ZM13.75 4.75h5.5v5.5h-5.5v-5.5ZM4.75 13.75h5.5v5.5h-5.5v-5.5ZM14 14h2.25v2.25H14V14ZM18 14h1.25v5.25H14V18h4v-4Z" />
        </svg>
        @break

    @case('key')
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 7.5a4.25 4.25 0 1 0 2.25 3.75h2.25v2.25H21v2.25h-3.25l-1.25-1.25h-2.25a4.25 4.25 0 0 0 0-7Z" />
            <path stroke-linecap="round" d="M7.5 12h.01" />
        </svg>
        @break

    @case('user-plus')
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.25a5.75 5.75 0 0 0-11.5 0M10 11.25a3.25 3.25 0 1 0 0-6.5 3.25 3.25 0 0 0 0 6.5ZM18 7.75v6.5M14.75 11h6.5" />
        </svg>
        @break

    @case('download')
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.75v9.5m0 0 3.75-3.75M12 14.25 8.25 10.5M5.25 16.75v1.5a2 2 0 0 0 2 2h9.5a2 2 0 0 0 2-2v-1.5" />
        </svg>
        @break

    @case('calendar')
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M7.25 3.75v3M16.75 3.75v3M4.75 8.25h14.5M6.25 5.25h11.5a1.5 1.5 0 0 1 1.5 1.5v11a1.5 1.5 0 0 1-1.5 1.5H6.25a1.5 1.5 0 0 1-1.5-1.5v-11a1.5 1.5 0 0 1 1.5-1.5Z" />
        </svg>
        @break

    @default
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <circle cx="12" cy="12" r="8" />
        </svg>
@endswitch
