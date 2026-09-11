<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberNotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user()->load(['member.department', 'member.position']);
        $filter = $request->string('filter')->toString();

        $notifications = Notification::query()
            ->where('user_id', $user->id)
            ->when($filter === 'unread', fn ($query) => $query->unread())
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $unreadCount = Notification::query()
            ->where('user_id', $user->id)
            ->unread()
            ->count();

        return view('member.notifications.index', compact('user', 'notifications', 'unreadCount', 'filter'));
    }

    public function read(Request $request, Notification $notification): RedirectResponse
    {
        abort_unless((int) $notification->user_id === (int) $request->user()->id, 403);

        $notification->markAsRead();

        $redirectTo = $this->safeRedirect($request->string('redirect_to')->toString());

        return $redirectTo
            ? redirect($redirectTo)
            : back();
    }

    public function readAll(Request $request): RedirectResponse
    {
        Notification::query()
            ->where('user_id', $request->user()->id)
            ->unread()
            ->update(['read_at' => now(), 'updated_at' => now()]);

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }

    private function safeRedirect(?string $url): ?string
    {
        if (! $url || ! str_starts_with($url, '/')) {
            return null;
        }

        if (str_starts_with($url, '//')) {
            return null;
        }

        return $url;
    }
}
