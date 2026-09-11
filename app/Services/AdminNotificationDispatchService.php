<?php

namespace App\Services;

use App\Models\AdminNotification;
use App\Models\Member;
use App\Models\Notification;
use App\Models\NotificationPreference;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class AdminNotificationDispatchService
{
    public function __construct(private readonly PushNotificationService $pushNotificationService)
    {
    }

    public function dispatch(AdminNotification $adminNotification): array
    {
        if (! $adminNotification->isDraft()) {
            return [
                'target_count' => $adminNotification->target_count,
                'delivered_count' => $adminNotification->delivered_count,
                'skipped_count' => $adminNotification->skipped_count,
                'push_attempted_count' => $adminNotification->push_attempted_count,
                'push_sent_count' => $adminNotification->push_sent_count,
                'push_failed_count' => $adminNotification->push_failed_count,
            ];
        }

        $members = $this->resolveTargets($adminNotification);

        if ($members->isEmpty()) {
            throw ValidationException::withMessages([
                'target_type' => 'Tidak ada anggota yang sesuai dengan target penerima.',
            ]);
        }

        $result = [
            'target_count' => $members->count(),
            'delivered_count' => 0,
            'skipped_count' => 0,
            'push_attempted_count' => 0,
            'push_sent_count' => 0,
            'push_failed_count' => 0,
        ];

        foreach ($members as $member) {
            $user = $member->user;

            if (! $user) {
                $result['skipped_count']++;

                continue;
            }

            $type = $this->memberNotificationType($adminNotification->type);
            $category = NotificationPreference::typeToCategory($type);

            if (NotificationPreference::allowsInApp($user, $category)) {
                Notification::query()->updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'dedupe_key' => "admin_notification:{$adminNotification->id}:user:{$user->id}",
                    ],
                    [
                        'member_id' => $member->id,
                        'type' => $type,
                        'related_type' => 'admin_notification',
                        'related_id' => $adminNotification->id,
                        'title' => $adminNotification->title,
                        'message' => $adminNotification->message,
                        'url' => $adminNotification->url,
                        'data' => [
                            'source' => 'admin_notification',
                            'admin_notification_id' => $adminNotification->id,
                        ],
                        'read_at' => null,
                    ],
                );

                $result['delivered_count']++;
            }

            if (in_array($adminNotification->channel, ['push', 'both'], true) && NotificationPreference::allowsPush($user, $category)) {
                $pushResult = $this->pushNotificationService->sendToMemberUser(
                    $user,
                    $adminNotification->title,
                    $adminNotification->message,
                    $adminNotification->url ?: route('member.notifications.index', absolute: false),
                    $type,
                );

                $result['push_attempted_count'] += $pushResult['attempted'];
                $result['push_sent_count'] += $pushResult['sent'];
                $result['push_failed_count'] += $pushResult['failed'];
            }
        }

        $adminNotification->forceFill([
            'status' => 'sent',
            'sent_at' => now(),
            ...$result,
        ])->save();

        return $result;
    }

    public function resolveTargets(AdminNotification $adminNotification): Collection
    {
        $payload = $adminNotification->target_payload ?? [];

        return Member::query()
            ->with('user')
            ->when($adminNotification->target_type !== 'all_members', fn ($query) => $query->where('member_status', 'active'))
            ->when($adminNotification->target_type === 'by_bidang', function ($query) use ($payload) {
                $ids = collect($payload['department_ids'] ?? [])->filter()->map(fn ($id) => (int) $id)->all();
                $query->whereIn('department_id', $ids ?: [0]);
            })
            ->when($adminNotification->target_type === 'by_jabatan', function ($query) use ($payload) {
                $ids = collect($payload['position_ids'] ?? [])->filter()->map(fn ($id) => (int) $id)->all();
                $query->whereIn('position_id', $ids ?: [0]);
            })
            ->when($adminNotification->target_type === 'selected_members', function ($query) use ($payload) {
                $ids = collect($payload['member_ids'] ?? [])->filter()->map(fn ($id) => (int) $id)->all();
                $query->whereIn('id', $ids ?: [0]);
            })
            ->orderBy('full_name')
            ->get()
            ->unique('id')
            ->values();
    }

    private function memberNotificationType(string $type): string
    {
        return match ($type) {
            'announcement' => 'admin_announcement',
            'schedule_change' => 'activity_changed',
            'activity_info' => 'activity_info',
            'organization_info' => 'organization_info',
            'urgent' => 'urgent',
            default => $type,
        };
    }
}
