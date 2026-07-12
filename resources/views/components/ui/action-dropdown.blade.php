@props([
    'label' => 'Aksi',
    'align' => 'right',
])

@php
    $horizontalOrigin = $align === 'left' ? 'left' : 'right';
@endphp

<div
    x-data="{
        dropdownOpen: false,
        dropdownTop: 0,
        dropdownLeft: 0,
        dropdownMaxHeight: 9999,
        openUpward: false,
        margin: 8,
        toggleDropdown() {
            if (this.dropdownOpen) {
                this.dropdownOpen = false;
                return;
            }

            const rect = this.$refs.trigger.getBoundingClientRect();
            this.dropdownTop = rect.bottom + this.margin;
            this.dropdownLeft = @js($align) === 'left' ? rect.left : rect.right - 224;
            this.dropdownOpen = true;
            setTimeout(() => this.positionDropdown(), 0);
            setTimeout(() => this.positionDropdown(), 50);
        },
        positionDropdown() {
            if (! this.dropdownOpen) return;

            const rect = this.$refs.trigger.getBoundingClientRect();
            const menu = this.$refs.menu;
            const menuHeight = menu?.offsetHeight || 0;
            const menuWidth = menu?.offsetWidth || 224;
            const spaceBelow = window.innerHeight - rect.bottom;
            const spaceAbove = rect.top;

            this.dropdownMaxHeight = Math.max(160, window.innerHeight - (this.margin * 2));
            this.openUpward = spaceBelow < menuHeight + this.margin && spaceAbove > spaceBelow;
            this.dropdownTop = this.openUpward
                ? Math.max(this.margin, rect.top - menuHeight - this.margin)
                : Math.min(rect.bottom + this.margin, window.innerHeight - menuHeight - this.margin);

            const preferredLeft = @js($align) === 'left'
                ? rect.left
                : rect.right - menuWidth;

            this.dropdownLeft = Math.min(
                Math.max(this.margin, preferredLeft),
                window.innerWidth - menuWidth - this.margin
            );
        }
    }"
    x-on:keydown.escape.window="dropdownOpen = false"
    x-on:resize.window="positionDropdown()"
    x-on:scroll.window.passive="positionDropdown()"
    class="relative inline-flex text-left"
>
    <button
        x-ref="trigger"
        type="button"
        class="inline-flex h-8 w-8 items-center justify-center rounded-xl text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white dark:focus:ring-offset-slate-950"
        x-on:click="toggleDropdown()"
        x-bind:aria-expanded="dropdownOpen.toString()"
        aria-haspopup="true"
        title="{{ $label }}"
        aria-label="{{ $label }}"
    >
        <span class="sr-only">{{ $label }}</span>
        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75h.01M12 12h.01M12 17.25h.01" />
        </svg>
    </button>

    <template x-teleport="body">
        <div
            x-ref="menu"
            x-cloak
            x-show="dropdownOpen"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="scale-95 opacity-0"
            x-transition:enter-end="scale-100 opacity-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="scale-100 opacity-100"
            x-transition:leave-end="scale-95 opacity-0"
            x-on:click.outside="dropdownOpen = false"
            x-bind:style="`top: ${dropdownTop}px; left: ${dropdownLeft}px; max-height: ${dropdownMaxHeight}px;`"
            x-bind:class="openUpward ? 'origin-bottom-{{ $horizontalOrigin }}' : 'origin-top-{{ $horizontalOrigin }}'"
            class="fixed z-[70] min-w-48 w-56 overflow-y-auto rounded-xl border border-slate-200 bg-white p-1.5 shadow-xl shadow-slate-900/10 dark:border-slate-700 dark:bg-slate-900 dark:shadow-black/30"
            style="display: none;"
        >
            {{ $slot }}
        </div>
    </template>
</div>
