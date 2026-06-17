@php
    $toasts = collect(['success', 'error', 'warning', 'info'])
        ->filter(fn ($key) => session()->has($key))
        ->map(fn ($key) => [
            'type' => $key,
            'message' => session($key),
        ])
        ->values();
@endphp

<div
        x-data="{
            toasts: @js($toasts),
            push(type, message) {
                const toast = { type, message, id: `${type}-${Date.now()}-${Math.random()}` };
                this.toasts.push(toast);
                setTimeout(() => this.remove(toast.id), 5000);
            },
            remove(id) {
                this.toasts = this.toasts.filter((toast) => toast.id !== id);
            },
            init() {
                this.toasts.forEach((toast, index) => {
                    toast.id = `${toast.type}-${index}-${Date.now()}`;
                    setTimeout(() => this.remove(toast.id), 5000 + (index * 500));
                });
            }
        }"
        x-cloak
        x-show="toasts.length > 0"
        x-on:toast.window="push($event.detail.type || 'info', $event.detail.message || '')"
        class="fixed right-4 top-20 z-50 w-[calc(100%-2rem)] max-w-sm space-y-3 sm:right-6"
        aria-live="polite"
        aria-atomic="true"
    >
        <template x-for="toast in toasts" :key="toast.id">
            <div
                x-show="true"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="translate-x-4 opacity-0"
                x-transition:enter-end="translate-x-0 opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="translate-x-0 opacity-100"
                x-transition:leave-end="translate-x-4 opacity-0"
                :class="{
                    'border-emerald-200 bg-emerald-50 text-emerald-800': toast.type === 'success',
                    'border-red-200 bg-red-50 text-red-800': toast.type === 'error',
                    'border-amber-200 bg-amber-50 text-amber-800': toast.type === 'warning',
                    'border-sky-200 bg-sky-50 text-sky-800': toast.type === 'info',
                }"
                class="pointer-events-auto flex gap-3 rounded-2xl border p-4 text-sm font-medium shadow-lg shadow-slate-900/10"
            >
                <div class="min-w-0 flex-1">
                    <p class="font-bold" x-text="{ success: 'Berhasil', error: 'Gagal', warning: 'Perhatian', info: 'Informasi' }[toast.type]"></p>
                    <p class="mt-1 leading-5" x-text="toast.message"></p>
                </div>
                <button type="button" class="rounded-lg p-1 opacity-70 transition hover:bg-white/60 hover:opacity-100" @click="remove(toast.id)" aria-label="Tutup notifikasi">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </template>
    </div>
