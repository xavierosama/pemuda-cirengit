@php
    $payload = old('target_payload', $adminNotification?->target_payload ?? []);
    $selectedDepartments = old('department_ids', $payload['department_ids'] ?? []);
    $selectedPositions = old('position_ids', $payload['position_ids'] ?? []);
    $selectedMembers = old('member_ids', $payload['member_ids'] ?? []);
@endphp

@csrf

<div
    class="grid gap-6 lg:grid-cols-[minmax(0,1.25fr)_minmax(22rem,0.75fr)]"
    x-data="{
        title: @js(old('title', $adminNotification?->title ?? '')),
        message: @js(old('message', $adminNotification?->message ?? '')),
        type: @js(old('type', $adminNotification?->type ?? 'announcement')),
        targetType: @js(old('target_type', $adminNotification?->target_type ?? 'all_active_members')),
        channel: @js(old('channel', $adminNotification?->channel ?? 'in_app')),
        url: @js(old('url', $adminNotification?->url ?? '')),
    }"
>
    <div class="space-y-6">
        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-base font-bold text-slate-950">Isi Notifikasi</h3>
                <p class="mt-1 text-sm text-slate-500">Pesan ini akan muncul di bell notifikasi member.</p>
            </div>

            <div class="mt-5 grid gap-5">
                <div>
                    <label for="title" class="text-sm font-semibold text-slate-700">Judul</label>
                    <input id="title" name="title" type="text" value="{{ old('title', $adminNotification?->title) }}" x-model="title" class="mt-2 block w-full rounded-xl border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600" required maxlength="180">
                    @error('title')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="message" class="text-sm font-semibold text-slate-700">Pesan</label>
                    <textarea id="message" name="message" rows="6" x-model="message" class="mt-2 block w-full rounded-xl border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600" required maxlength="2000">{{ old('message', $adminNotification?->message) }}</textarea>
                    <p class="mt-2 text-xs text-slate-500">Maksimal 2000 karakter. Gunakan bahasa singkat dan jelas.</p>
                    @error('message')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-5 md:grid-cols-2">
                    <div>
                        <label for="type" class="text-sm font-semibold text-slate-700">Tipe</label>
                        <select id="type" name="type" x-model="type" class="mt-2 block w-full rounded-xl border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                            @foreach ($types as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('type')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="channel" class="text-sm font-semibold text-slate-700">Channel</label>
                        <select id="channel" name="channel" x-model="channel" class="mt-2 block w-full rounded-xl border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                            @foreach ($channels as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-xs text-slate-500">Push akan dikirim hanya jika perangkat member sudah subscribe.</p>
                        @error('channel')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="url" class="text-sm font-semibold text-slate-700">URL Tujuan Opsional</label>
                    <input id="url" name="url" type="text" value="{{ old('url', $adminNotification?->url) }}" x-model="url" placeholder="/member/notifications" class="mt-2 block w-full rounded-xl border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                    <p class="mt-2 text-xs text-slate-500">Gunakan path internal aplikasi, misalnya <span class="font-semibold">/member</span>. Kosongkan jika tidak perlu.</p>
                    @error('url')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-base font-bold text-slate-950">Target Penerima</h3>
                <p class="mt-1 text-sm text-slate-500">Pilih target penerima. Anggota tanpa akun login akan dilewati karena belum bisa menerima notifikasi akun.</p>
            </div>

            <div class="mt-5 space-y-5">
                <div>
                    <label for="target_type" class="text-sm font-semibold text-slate-700">Target</label>
                    <select id="target_type" name="target_type" x-model="targetType" class="mt-2 block w-full rounded-xl border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                        @foreach ($targetTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('target_type')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div x-show="targetType === 'by_bidang'" x-cloak>
                    <label for="department_ids" class="text-sm font-semibold text-slate-700">Pilih Bidang</label>
                    <select id="department_ids" name="department_ids[]" multiple class="mt-2 block min-h-36 w-full rounded-xl border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                        @foreach ($departments as $department)
                            <option value="{{ $department->id }}" @selected(in_array($department->id, array_map('intval', $selectedDepartments), true))>{{ $department->name }}</option>
                        @endforeach
                    </select>
                    <p class="mt-2 text-xs text-slate-500">Tahan Ctrl/Cmd untuk memilih lebih dari satu.</p>
                    @error('department_ids')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div x-show="targetType === 'by_jabatan'" x-cloak>
                    <label for="position_ids" class="text-sm font-semibold text-slate-700">Pilih Jabatan</label>
                    <select id="position_ids" name="position_ids[]" multiple class="mt-2 block min-h-36 w-full rounded-xl border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                        @foreach ($positions as $position)
                            <option value="{{ $position->id }}" @selected(in_array($position->id, array_map('intval', $selectedPositions), true))>{{ $position->name }}</option>
                        @endforeach
                    </select>
                    @error('position_ids')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div x-show="targetType === 'selected_members'" x-cloak>
                    <label for="member_ids" class="text-sm font-semibold text-slate-700">Pilih Member</label>
                    <select id="member_ids" name="member_ids[]" multiple class="mt-2 block min-h-44 w-full rounded-xl border-slate-300 shadow-sm focus:border-emerald-600 focus:ring-emerald-600">
                        @foreach ($members as $member)
                            <option value="{{ $member->id }}" @selected(in_array($member->id, array_map('intval', $selectedMembers), true))>{{ $member->full_name }}{{ $member->npa ? ' - '.$member->npa : '' }}</option>
                        @endforeach
                    </select>
                    @error('member_ids')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        <div class="flex flex-col-reverse gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:justify-end">
            <a href="{{ route('admin-notifications.index') }}" class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Batal</a>
            <button type="submit" name="submit_action" value="draft" class="inline-flex items-center justify-center rounded-xl border border-emerald-200 bg-white px-4 py-2 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-50" x-bind:disabled="submitting">
                Simpan Draft
            </button>
            <button type="submit" name="submit_action" value="send" class="inline-flex items-center justify-center rounded-xl bg-emerald-700 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-800" x-bind:disabled="submitting">
                <span x-text="submitting ? 'Memproses...' : 'Kirim Sekarang'"></span>
            </button>
        </div>
    </div>

    <aside class="space-y-4">
        <section class="sticky top-20 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">Preview</p>
            <div class="mt-4 rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div class="flex gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.85 18.25a2.85 2.85 0 0 1-5.7 0M18.5 9.5a6.5 6.5 0 0 0-13 0c0 6.75-2.5 7.75-2.5 7.75h18s-2.5-1-2.5-7.75Z" />
                        </svg>
                    </span>
                    <div class="min-w-0">
                        <h4 class="line-clamp-2 text-sm font-bold text-slate-950" x-text="title || 'Judul notifikasi'"></h4>
                        <p class="mt-1 line-clamp-4 text-xs leading-5 text-slate-600" x-text="message || 'Isi pesan akan tampil di sini.'"></p>
                        <p class="mt-2 text-[11px] font-semibold text-slate-400">Baru saja</p>
                    </div>
                </div>
            </div>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">Tipe</dt>
                    <dd class="font-semibold text-slate-800" x-text="type"></dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">Channel</dt>
                    <dd class="font-semibold text-slate-800" x-text="channel"></dd>
                </div>
                <div class="flex justify-between gap-3">
                    <dt class="text-slate-500">Target</dt>
                    <dd class="font-semibold text-slate-800" x-text="targetType"></dd>
                </div>
            </dl>
        </section>
    </aside>
</div>
