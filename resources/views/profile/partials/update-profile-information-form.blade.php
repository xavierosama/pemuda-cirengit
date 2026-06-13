<section>
    <header class="border-b border-slate-100 pb-4">
        <h3 class="text-base font-bold text-slate-950">Informasi Akun</h3>
        <p class="mt-1 text-sm text-slate-500">Perbarui nama dan email yang digunakan untuk login.</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-5 space-y-5">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="text-sm font-semibold text-slate-700">Nama</label>
            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <label for="email" class="text-sm font-semibold text-slate-700">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    <p>Email Anda belum terverifikasi.</p>
                    <button form="send-verification" class="mt-2 font-semibold underline decoration-amber-500 underline-offset-4 hover:text-amber-900">
                        Kirim ulang email verifikasi
                    </button>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-semibold text-emerald-700">Link verifikasi baru sudah dikirim ke email Anda.</p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">
                Simpan Perubahan
            </button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2500)"
                    class="rounded-lg bg-emerald-50 px-3 py-2 text-sm font-semibold text-emerald-700"
                >Profil berhasil disimpan.</p>
            @endif
        </div>
    </form>
</section>
