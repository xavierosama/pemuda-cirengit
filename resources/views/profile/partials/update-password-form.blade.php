<section>
    <header class="border-b border-slate-100 pb-4">
        <h3 class="text-base font-bold text-slate-950">Ubah Password</h3>
        <p class="mt-1 text-sm text-slate-500">Gunakan password yang kuat dan tidak mudah ditebak.</p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-5 space-y-5">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="text-sm font-semibold text-slate-700">Password saat ini</label>
            <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <label for="update_password_password" class="text-sm font-semibold text-slate-700">Password baru</label>
            <input id="update_password_password" name="password" type="password" autocomplete="new-password" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <label for="update_password_password_confirmation" class="text-sm font-semibold text-slate-700">Konfirmasi password baru</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
                Simpan Password
            </button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2500)"
                    class="rounded-lg bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-700"
                >Password berhasil disimpan.</p>
            @endif
        </div>
    </form>
</section>
