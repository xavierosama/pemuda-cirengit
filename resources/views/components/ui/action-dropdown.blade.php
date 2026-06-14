@props([
    'label' => 'Aksi',
    'align' => 'right',
])

@php
    $originClass = $align === 'left' ? 'origin-top-left' : 'origin-top-right';
@endphp

<div
    x-data="{
        dropdownOpen: false,
        dropdownTop: 0,
        dropdownLeft: 0,
        dropdownRight: 0,
        toggleDropdown() {
            if (this.dropdownOpen) {
                this.dropdownOpen = false;
                return;
            }

            const rect = this.$refs.trigger.getBoundingClientRect();
            this.dropdownTop = rect.bottom + 8;
            this.dropdownLeft = rect.left;
            this.dropdownRight = window.innerWidth - rect.right;
            this.dropdownOpen = true;
        }
    }"
    x-on:keydown.escape.window="dropdownOpen = false"
    class="relative inline-flex text-left"
>
    <button
        x-ref="trigger"
        type="button"
        class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-slate-600 transition hover:bg-slate-100 hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2"
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
            x-cloak
            x-show="dropdownOpen"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="scale-95 opacity-0"
            x-transition:enter-end="scale-100 opacity-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="scale-100 opacity-100"
            x-transition:leave-end="scale-95 opacity-0"
            x-on:click.outside="dropdownOpen = false"
            x-bind:style="@js($align) === 'left'
                ? `top: ${dropdownTop}px; left: ${dropdownLeft}px;`
                : `top: ${dropdownTop}px; right: ${dropdownRight}px;`"
            class="{{ $originClass }} fixed z-[70] w-56 rounded-xl border border-slate-200 bg-white p-1.5 shadow-xl shadow-slate-900/10"
            style="display: none;"
        >
            {{ $slot }}
        </div>
    </template>
</div>
