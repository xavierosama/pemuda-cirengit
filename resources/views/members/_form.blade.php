@csrf

<div class="grid gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label for="full_name" class="block text-sm font-semibold text-slate-700">Nama Lengkap</label>
        <input id="full_name" name="full_name" type="text" value="{{ old('full_name', $member->full_name ?? '') }}" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600" required autofocus>
        @error('full_name') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="member_number" class="block text-sm font-semibold text-slate-700">Nomor Anggota</label>
        <input id="member_number" name="member_number" type="text" value="{{ old('member_number', $member->member_number ?? '') }}" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
        @error('member_number') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="joined_at" class="block text-sm font-semibold text-slate-700">Tanggal Bergabung</label>
        <input id="joined_at" name="joined_at" type="date" value="{{ old('joined_at', isset($member) && $member->joined_at ? $member->joined_at->format('Y-m-d') : '') }}" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
        @error('joined_at') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="phone" class="block text-sm font-semibold text-slate-700">Nomor HP</label>
        <input id="phone" name="phone" type="text" value="{{ old('phone', $member->phone ?? '') }}" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
        @error('phone') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="email" class="block text-sm font-semibold text-slate-700">Email</label>
        <input id="email" name="email" type="email" value="{{ old('email', $member->email ?? '') }}" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
        @error('email') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="department_id" class="block text-sm font-semibold text-slate-700">Bidang</label>
        <select id="department_id" name="department_id" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
            <option value="">Tanpa bidang</option>
            @foreach ($departments as $department)
                <option value="{{ $department->id }}" @selected((string) old('department_id', $member->department_id ?? '') === (string) $department->id)>{{ $department->name }}</option>
            @endforeach
        </select>
        @error('department_id') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="position_id" class="block text-sm font-semibold text-slate-700">Jabatan</label>
        <select id="position_id" name="position_id" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
            <option value="">Tanpa jabatan</option>
            @foreach ($positions as $position)
                <option value="{{ $position->id }}" @selected((string) old('position_id', $member->position_id ?? '') === (string) $position->id)>{{ $position->name }}</option>
            @endforeach
        </select>
        @error('position_id') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="member_status" class="block text-sm font-semibold text-slate-700">Status Anggota</label>
        <select id="member_status" name="member_status" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600" required>
            @foreach (['active' => 'Aktif', 'inactive' => 'Tidak Aktif', 'alumni' => 'Alumni', 'moved' => 'Pindah'] as $value => $label)
                <option value="{{ $value }}" @selected(old('member_status', $member->member_status ?? 'active') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('member_status') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="address" class="block text-sm font-semibold text-slate-700">Alamat</label>
        <textarea id="address" name="address" rows="4" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">{{ old('address', $member->address ?? '') }}</textarea>
        @error('address') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="notes" class="block text-sm font-semibold text-slate-700">Catatan</label>
        <textarea id="notes" name="notes" rows="4" class="mt-2 block w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">{{ old('notes', $member->notes ?? '') }}</textarea>
        @error('notes') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
    </div>
</div>

<div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
    <a href="{{ route('members.index') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Batal</a>
    <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">Simpan</button>
</div>
