<section class="space-y-5">
    <header class="border-b border-red-100 pb-4">
        <h3 class="text-base font-bold text-red-900">Zona Berbahaya</h3>
        <p class="mt-1 text-sm leading-6 text-slate-500">Hapus akun hanya jika benar-benar diperlukan. Aksi ini tidak dapat dibatalkan.</p>
    </header>

    <button
        type="button"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="inline-flex items-center justify-center rounded-lg border border-red-200 px-4 py-2.5 text-sm font-semibold text-red-700 transition hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2"
    >Hapus Akun</button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-bold text-slate-950">Yakin ingin menghapus akun?</h2>

            <p class="mt-2 text-sm leading-6 text-slate-600">
                Setelah akun dihapus, sesi Anda akan keluar dan data akun tidak dapat dipulihkan. Masukkan password untuk konfirmasi.
            </p>

            <div class="mt-6">
                <label for="password" class="sr-only">Password</label>

                <input
                    id="password"
                    name="password"
                    type="password"
                    class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-red-500 focus:ring-red-500"
                    placeholder="Password"
                >

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button type="button" x-on:click="$dispatch('close')" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                    Batal
                </button>

                <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    Hapus Akun
                </button>
            </div>
        </form>
    </x-modal>
</section>
