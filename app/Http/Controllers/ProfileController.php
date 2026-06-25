<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Support\DateFormatter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View|RedirectResponse
    {
        if ($request->user()->role === 'member') {
            return Redirect::route('member.profile.edit');
        }

        $request->user()->load(['member.department', 'member.position']);

        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    public function editMember(Request $request): View
    {
        $request->user()->load(['member.department', 'member.position']);

        return view('member.edit-profile', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Update the connected member's personal profile fields.
     */
    public function updateMember(Request $request): RedirectResponse
    {
        $user = $request->user()->load('member');
        $member = $user->member;

        if (! $member) {
            return Redirect::route($user->role === 'member' ? 'member.home' : 'profile.edit')
                ->with('error', 'Akun Anda belum terhubung dengan data anggota.');
        }

        $request->merge([
            'birth_date' => DateFormatter::normalizeInputDateForValidation($request->input('birth_date')),
        ]);

        $validated = $request->validate([
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'birth_date' => ['nullable', 'date'],
        ], [
            'birth_date.date' => 'Tanggal lahir harus menggunakan format dd/mm/yyyy.',
        ]);

        if ($request->hasFile('profile_photo')) {
            if ($member->profile_photo) {
                Storage::disk('public')->delete($member->profile_photo);
            }

            $validated['profile_photo'] = $request->file('profile_photo')->store('member-photos', 'public');
        } else {
            unset($validated['profile_photo']);
        }

        $member->update($validated);

        return Redirect::route($user->role === 'member' ? 'member.profile.edit' : 'profile.edit')
            ->with('status', 'member-profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
