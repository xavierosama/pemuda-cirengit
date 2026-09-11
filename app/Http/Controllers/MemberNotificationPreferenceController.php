<?php

namespace App\Http\Controllers;

use App\Models\NotificationPreference;
use App\Models\PushSubscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MemberNotificationPreferenceController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user()->load(['member.department', 'member.position']);
        $preferences = NotificationPreference::resolvedForUser($user);
        $activePushSubscriptions = PushSubscription::query()
            ->where('user_id', $user->id)
            ->active()
            ->count();

        return view('member.notification-settings.edit', compact('user', 'preferences', 'activePushSubscriptions'));
    }

    public function update(Request $request): RedirectResponse
    {
        $categories = NotificationPreference::categoryKeys();

        $validated = $request->validate([
            'preferences' => ['required', 'array'],
            'preferences.*.category' => ['required', Rule::in($categories)],
            'preferences.*.in_app_enabled' => ['nullable', 'boolean'],
            'preferences.*.push_enabled' => ['nullable', 'boolean'],
        ]);

        $preferences = collect($validated['preferences'])
            ->keyBy('category')
            ->map(fn (array $preference) => [
                'in_app_enabled' => (bool) ($preference['in_app_enabled'] ?? true),
                'push_enabled' => (bool) ($preference['push_enabled'] ?? false),
            ])
            ->all();

        NotificationPreference::updateForUser($request->user()->load('member'), $preferences);

        return back()->with('success', 'Pengaturan notifikasi berhasil disimpan.');
    }
}
