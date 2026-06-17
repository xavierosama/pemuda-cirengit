@csrf

@php
    $inputClass = 'mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600';
    $labelClass = 'block text-sm font-semibold text-slate-700';
    $helperClass = 'mt-1 text-xs text-slate-500';
    $errorClass = 'mt-2 text-sm text-red-600';
    $inactiveReasons = \App\Models\Member::INACTIVE_REASONS;
    $currentStatus = old('member_status', isset($member) ? $member->displayStatusKey() : 'active');
@endphp

<div class="space-y-5">
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-5 border-b border-slate-100 pb-4">
            <h3 class="text-base font-bold text-slate-950">Identitas Anggota</h3>
            <p class="mt-1 text-sm text-slate-500">Lengkapi identitas utama anggota agar data mudah dicari dan tidak tertukar.</p>
        </div>
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="npa" class="{{ $labelClass }}">Nomor Pokok Anggota (NPA)</label>
                <input id="npa" name="npa" type="text" value="{{ old('npa', $member->npa ?? '') }}" placeholder="Masukkan NPA" class="{{ $inputClass }}">
                <p class="{{ $helperClass }}">Contoh: 20.0001</p>
                @error('npa') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="full_name" class="{{ $labelClass }}">Nama Lengkap</label>
                <input id="full_name" name="full_name" type="text" value="{{ old('full_name', $member->full_name ?? '') }}" class="{{ $inputClass }}" required autofocus>
                @error('full_name') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="joined_at" class="{{ $labelClass }}">Tanggal Bergabung</label>
                <input id="joined_at" name="joined_at" type="date" value="{{ old('joined_at', isset($member) && $member->joined_at ? $member->joined_at->format('Y-m-d') : '') }}" class="{{ $inputClass }}">
                <p class="{{ $helperClass }}">Format tampilan: dd/mm/yyyy.</p>
                @error('joined_at') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="birth_date" class="{{ $labelClass }}">Tanggal Lahir</label>
                <input id="birth_date" name="birth_date" type="date" value="{{ old('birth_date', isset($member) && $member->birth_date ? $member->birth_date->format('Y-m-d') : '') }}" class="{{ $inputClass }}">
                <p class="{{ $helperClass }}">Dipakai untuk memantau batas usia anggota Pemuda.</p>
                @error('birth_date') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <section
        class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
        x-data="{ status: @js($currentStatus) }"
    >
        <div class="mb-5 border-b border-slate-100 pb-4">
            <h3 class="text-base font-bold text-slate-950">Status Keanggotaan</h3>
            <p class="mt-1 text-sm text-slate-500">Status utama dibuat sederhana. Jika tidak aktif, lengkapi alasan agar riwayat administrasi tetap jelas.</p>
        </div>
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="member_status" class="{{ $labelClass }}">Status Anggota</label>
                <select id="member_status" name="member_status" class="{{ $inputClass }}" required x-model="status">
                    @foreach (['active' => 'Aktif', 'inactive' => 'Tidak Aktif'] as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                <p class="{{ $helperClass }}">Status utama anggota hanya Aktif atau Tidak Aktif.</p>
                @error('member_status') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div x-cloak x-show="status === 'inactive'" x-transition>
                <label for="inactive_reason" class="{{ $labelClass }}">Alasan Tidak Aktif</label>
                <select id="inactive_reason" name="inactive_reason" class="{{ $inputClass }}">
                    <option value="">Pilih alasan</option>
                    @foreach ($inactiveReasons as $value => $label)
                        <option value="{{ $value }}" @selected(old('inactive_reason', $member->inactive_reason ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('inactive_reason') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div x-cloak x-show="status === 'inactive'" x-transition>
                <label for="inactive_at" class="{{ $labelClass }}">Tanggal Tidak Aktif</label>
                <input id="inactive_at" name="inactive_at" type="date" value="{{ old('inactive_at', isset($member) && $member->inactive_at ? $member->inactive_at->format('Y-m-d') : '') }}" class="{{ $inputClass }}">
                @error('inactive_at') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2" x-cloak x-show="status === 'inactive'" x-transition>
                <label for="status_notes" class="{{ $labelClass }}">Catatan Status</label>
                <textarea id="status_notes" name="status_notes" rows="3" class="{{ $inputClass }}" placeholder="Tambahkan keterangan status tidak aktif bila diperlukan.">{{ old('status_notes', $member->status_notes ?? '') }}</textarea>
                @error('status_notes') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-5 border-b border-slate-100 pb-4">
            <h3 class="text-base font-bold text-slate-950">Kontak</h3>
            <p class="mt-1 text-sm text-slate-500">Simpan kontak yang bisa digunakan pengurus untuk komunikasi dan akun login.</p>
        </div>
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="phone" class="{{ $labelClass }}">No HP</label>
                <input id="phone" name="phone" type="text" value="{{ old('phone', $member->phone ?? '') }}" class="{{ $inputClass }}">
                @error('phone') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="email" class="{{ $labelClass }}">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $member->email ?? '') }}" class="{{ $inputClass }}">
                <p class="{{ $helperClass }}">Digunakan untuk akun login anggota.</p>
                @error('email') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label for="address" class="{{ $labelClass }}">Alamat</label>
                <textarea id="address" name="address" rows="4" class="{{ $inputClass }}">{{ old('address', $member->address ?? '') }}</textarea>
                @error('address') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-5 border-b border-slate-100 pb-4">
            <h3 class="text-base font-bold text-slate-950">Struktur Organisasi</h3>
            <p class="mt-1 text-sm text-slate-500">Hubungkan anggota dengan bidang dan jabatan yang sesuai di data master.</p>
        </div>
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="department_id" class="{{ $labelClass }}">Bidang</label>
                <select id="department_id" name="department_id" class="{{ $inputClass }}">
                    <option value="">Tanpa bidang</option>
                    @foreach ($departments as $department)
                        <option value="{{ $department->id }}" @selected((string) old('department_id', $member->department_id ?? '') === (string) $department->id)>{{ $department->name }}</option>
                    @endforeach
                </select>
                @error('department_id') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="position_id" class="{{ $labelClass }}">Jabatan</label>
                <select id="position_id" name="position_id" class="{{ $inputClass }}">
                    <option value="">Tanpa jabatan</option>
                    @foreach ($positions as $position)
                        <option value="{{ $position->id }}" @selected((string) old('position_id', $member->position_id ?? '') === (string) $position->id)>{{ $position->name }}</option>
                    @endforeach
                </select>
                @error('position_id') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
        <div class="mb-5 border-b border-slate-100 pb-4">
            <h3 class="text-base font-bold text-slate-950">Catatan</h3>
            <p class="mt-1 text-sm text-slate-500">Tambahkan informasi khusus bila ada, misalnya riwayat amanah atau keterangan administrasi.</p>
        </div>
        <div>
            <label for="notes" class="{{ $labelClass }}">Catatan</label>
            <textarea id="notes" name="notes" rows="4" class="{{ $inputClass }}">{{ old('notes', $member->notes ?? '') }}</textarea>
            @error('notes') <p class="{{ $errorClass }}">{{ $message }}</p> @enderror
        </div>
    </section>
</div>

<div class="mt-6 flex flex-col-reverse gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:justify-end">
    <a href="{{ route('members.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">Batal/Kembali</a>
    <x-ui.submit-button loading-text="Menyimpan...">Simpan</x-ui.submit-button>
</div>
