<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use App\Services\PushNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MemberPushSubscriptionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'string', 'max:500'],
            'keys' => ['required', 'array'],
            'keys.p256dh' => ['required', 'string', 'max:1024'],
            'keys.auth' => ['required', 'string', 'max:512'],
            'content_encoding' => ['nullable', 'string', 'max:32'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = $request->user()->load('member');
        $userAgent = $request->userAgent();

        $subscription = PushSubscription::query()->updateOrCreate(
            ['endpoint' => $validated['endpoint']],
            [
                'user_id' => $user->id,
                'member_id' => $user->member?->id,
                'public_key' => $validated['keys']['p256dh'],
                'auth_token' => $validated['keys']['auth'],
                'content_encoding' => $validated['content_encoding'] ?? 'aes128gcm',
                'user_agent' => $userAgent,
                'device_name' => $validated['device_name'] ?? Str::limit($userAgent ?: 'Perangkat ini', 90, ''),
                'last_used_at' => now(),
                'revoked_at' => null,
            ],
        );

        return response()->json([
            'message' => 'Notifikasi aktif di perangkat ini.',
            'subscription_id' => $subscription->id,
        ]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'string', 'max:500'],
        ]);

        $updated = PushSubscription::query()
            ->where('user_id', $request->user()->id)
            ->where('endpoint', $validated['endpoint'])
            ->active()
            ->update(['revoked_at' => now(), 'updated_at' => now()]);

        return response()->json([
            'message' => $updated > 0
                ? 'Notifikasi perangkat ini dinonaktifkan.'
                : 'Subscription tidak ditemukan atau sudah nonaktif.',
        ]);
    }

    public function test(Request $request, PushNotificationService $pushNotificationService): JsonResponse
    {
        $result = $pushNotificationService->sendToMemberUser(
            $request->user(),
            'Tes Notifikasi Pemuda Cirengit',
            'Jika notifikasi ini muncul, perangkat Anda sudah siap menerima push notification.',
            route('member.home', absolute: false),
            'test',
        );

        return response()->json([
            'message' => $result['configured']
                ? 'Tes notifikasi diproses.'
                : 'VAPID key belum dikonfigurasi di server.',
            'result' => $result,
        ], $result['configured'] ? 200 : 422);
    }
}
