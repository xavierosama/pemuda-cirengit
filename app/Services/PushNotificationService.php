<?php

namespace App\Services;

use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use Throwable;

class PushNotificationService
{
    public function sendToUser(User $user, array $payload): array
    {
        if (! $this->isConfigured()) {
            return [
                'configured' => false,
                'attempted' => 0,
                'sent' => 0,
                'failed' => 0,
                'revoked' => 0,
            ];
        }

        $subscriptions = PushSubscription::query()
            ->where('user_id', $user->id)
            ->active()
            ->get();

        $result = [
            'configured' => true,
            'attempted' => $subscriptions->count(),
            'sent' => 0,
            'failed' => 0,
            'revoked' => 0,
        ];

        if ($subscriptions->isEmpty()) {
            return $result;
        }

        $webPush = new WebPush([
            'VAPID' => [
                'subject' => config('services.webpush.vapid.subject'),
                'publicKey' => config('services.webpush.vapid.public_key'),
                'privateKey' => config('services.webpush.vapid.private_key'),
            ],
        ], [
            'TTL' => 3600,
            'urgency' => $payload['urgency'] ?? 'normal',
        ]);

        $body = json_encode([
            'title' => $payload['title'] ?? config('app.name', 'Pemuda Cirengit'),
            'body' => $payload['body'] ?? $payload['message'] ?? '',
            'url' => $payload['url'] ?? route('member.home', absolute: false),
            'icon' => $payload['icon'] ?? asset('icons/icon-192.png'),
            'badge' => $payload['badge'] ?? asset('icons/apple-touch-icon.png'),
            'type' => $payload['type'] ?? 'info',
        ], JSON_THROW_ON_ERROR);

        foreach ($subscriptions as $pushSubscription) {
            try {
                $report = $webPush->sendOneNotification(
                    Subscription::create([
                        'endpoint' => $pushSubscription->endpoint,
                        'publicKey' => $pushSubscription->public_key,
                        'authToken' => $pushSubscription->auth_token,
                        'contentEncoding' => $pushSubscription->content_encoding ?: 'aes128gcm',
                    ]),
                    $body,
                );

                if ($report->isSuccess()) {
                    $pushSubscription->forceFill(['last_used_at' => now()])->save();
                    $result['sent']++;

                    continue;
                }

                $result['failed']++;

                if ($report->isSubscriptionExpired()) {
                    $pushSubscription->revoke();
                    $result['revoked']++;
                }

                Log::warning('Web push notification failed.', [
                    'endpoint' => $pushSubscription->endpoint,
                    'reason' => $report->getReason(),
                ]);
            } catch (Throwable $exception) {
                $result['failed']++;

                Log::warning('Web push notification exception.', [
                    'endpoint' => $pushSubscription->endpoint,
                    'message' => $exception->getMessage(),
                ]);
            }
        }

        return $result;
    }

    public function sendToMemberUser(User $user, string $title, string $message, ?string $url = null, string $type = 'info'): array
    {
        return $this->sendToUser($user, [
            'title' => $title,
            'body' => $message,
            'url' => $url ?: route('member.home', absolute: false),
            'type' => $type,
        ]);
    }

    public function isConfigured(): bool
    {
        return filled(config('services.webpush.vapid.public_key'))
            && filled(config('services.webpush.vapid.private_key'))
            && filled(config('services.webpush.vapid.subject'));
    }
}
