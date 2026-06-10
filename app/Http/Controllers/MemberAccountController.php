<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class MemberAccountController extends Controller
{
    public function store(Member $member): RedirectResponse
    {
        $validator = Validator::make(
            ['email' => $member->email],
            ['email' => ['required', 'email']]
        );

        if ($validator->fails()) {
            return back()->withErrors([
                'email' => 'Email anggota perlu diisi dengan format yang valid sebelum membuat akun login.',
            ]);
        }

        if ($member->user) {
            return back()->with('info', 'Anggota ini sudah memiliki akun login.');
        }

        if (User::where('member_id', $member->id)->exists()) {
            return back()->with('error', 'Data anggota ini sudah terhubung dengan akun user lain.');
        }

        $user = User::where('email', $member->email)->first();

        if ($user) {
            if ($user->member_id && $user->member_id !== $member->id) {
                return back()->with('error', 'Email tersebut sudah digunakan oleh akun anggota lain.');
            }

            try {
                $user->update([
                    'member_id' => $member->id,
                    'role' => $user->role ?? 'member',
                ]);
            } catch (QueryException) {
                return back()->with('error', 'Data anggota ini sudah terhubung dengan akun user lain.');
            }

            return back()->with('success', 'Akun dengan email tersebut sudah ada dan berhasil dihubungkan ke anggota.');
        }

        try {
            User::create([
                'name' => $member->full_name,
                'email' => $member->email,
                'password' => Hash::make('password'),
                'role' => 'member',
                'member_id' => $member->id,
            ]);
        } catch (QueryException) {
            return back()->with('error', 'Data anggota ini sudah terhubung dengan akun user lain.');
        }

        return back()->with('success', 'Akun login berhasil dibuat. Password awal: password');
    }

    public function resetPassword(Member $member): RedirectResponse
    {
        $user = $member->user;

        if (! $user) {
            return back()->with('error', 'Anggota ini belum memiliki akun login.');
        }

        $user->update([
            'password' => Hash::make('password'),
        ]);

        return back()->with('success', 'Password akun berhasil direset menjadi: password');
    }
}
