<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Attendance;
use App\Models\Member;
use App\Models\Notification;
use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

class NotificationRuleService
{
    public function __construct(private readonly PushNotificationService $pushNotificationService)
    {
    }

    public function notifyActivityCreated(Activity $activity): array
    {
        return $this->notifyMembers(
            $this->activeMembersWithUser(),
            'activity_created',
            'activity',
            $activity->id,
            'Kegiatan baru dijadwalkan',
            "Kegiatan {$activity->title} telah dijadwalkan.",
            route('member.home', absolute: false).'#kegiatan-mendatang',
            shouldPush: false,
        );
    }

    public function notifyActivityUpdated(Activity $activity, array $changes = []): array
    {
        return $this->notifyMembers(
            $this->activityParticipantMembers($activity),
            'activity_updated',
            'activity',
            $activity->id,
            'Perubahan kegiatan',
            "Ada perubahan jadwal/lokasi kegiatan {$activity->title}.",
            route('member.home', absolute: false).'#kegiatan-mendatang',
            ['changes' => array_values($changes)],
            shouldPush: true,
        );
    }

    public function notifyAttendanceOpened(Activity $activity): array
    {
        return $this->notifyMembers(
            $this->activityParticipantMembers($activity),
            'attendance_opened',
            'activity',
            $activity->id,
            'Presensi dibuka',
            "Presensi untuk {$activity->title} sudah dibuka. Silakan isi presensi sekarang.",
            route('member.home', absolute: false).'#kegiatan-sekarang',
            shouldPush: true,
        );
    }

    public function notifyAttendanceClosingSoon(Activity $activity): array
    {
        return $this->notifyMembers(
            $this->absentParticipantMembers($activity),
            'attendance_closing_soon',
            'activity',
            $activity->id,
            'Presensi hampir ditutup',
            "Presensi {$activity->title} akan segera ditutup.",
            route('member.home', absolute: false).'#kegiatan-sekarang',
            shouldPush: true,
        );
    }

    public function notifyAttendanceNotSubmitted(Activity $activity): array
    {
        return $this->notifyMembers(
            $this->absentParticipantMembers($activity),
            'attendance_not_submitted',
            'activity',
            $activity->id,
            'Anda belum melakukan presensi',
            "Anda belum melakukan presensi untuk {$activity->title}.",
            route('member.home', absolute: false).'#kegiatan-sekarang',
            shouldPush: true,
        );
    }

    public function notifyAttendancePendingVerification(Attendance $attendance): ?Notification
    {
        $attendance->loadMissing(['activity', 'member.user']);

        return $this->notifyMember(
            $attendance->member,
            'attendance_pending_verification',
            'attendance',
            $attendance->id,
            'Presensi menunggu verifikasi',
            'Presensi Anda untuk '.$this->activityTitle($attendance).' sedang menunggu verifikasi.',
            route('member.home', absolute: false).'#riwayat-presensi',
            shouldPush: false,
        );
    }

    public function notifyAttendanceVerified(Attendance $attendance): ?Notification
    {
        $attendance->loadMissing(['activity', 'member.user']);

        return $this->notifyMember(
            $attendance->member,
            'attendance_verified',
            'attendance',
            $attendance->id,
            'Presensi telah diverifikasi',
            'Presensi Anda untuk '.$this->activityTitle($attendance).' telah diverifikasi.',
            route('member.home', absolute: false).'#riwayat-presensi',
            shouldPush: false,
        );
    }

    public function notifyAttendanceRejected(Attendance $attendance): ?Notification
    {
        $attendance->loadMissing(['activity', 'member.user']);

        return $this->notifyMember(
            $attendance->member,
            'attendance_rejected',
            'attendance',
            $attendance->id,
            'Presensi ditolak',
            'Presensi Anda untuk '.$this->activityTitle($attendance).' ditolak. Silakan hubungi pengurus.',
            route('member.home', absolute: false).'#riwayat-presensi',
            shouldPush: false,
        );
    }

    public function notifyProfileIncomplete(Member $member): ?Notification
    {
        $member->loadMissing('user');

        $missing = collect([
            'No HP' => blank($member->phone),
            'Alamat' => blank($member->address),
            'Tanggal lahir' => blank($member->birth_date),
        ])->filter()->keys()->values();

        if ($missing->isEmpty()) {
            Notification::query()
                ->where('member_id', $member->id)
                ->where('type', 'profile_incomplete')
                ->whereNull('read_at')
                ->update(['read_at' => now(), 'updated_at' => now()]);

            return null;
        }

        return $this->notifyMember(
            $member,
            'profile_incomplete',
            'member',
            $member->id,
            'Lengkapi profil anggota',
            'Lengkapi '.$missing->join(', ', ' dan ').' agar data anggota lebih tertata.',
            route('member.profile.edit', absolute: false),
            ['missing' => $missing->all()],
            shouldPush: false,
        );
    }

    public function generateReminders(): array
    {
        $summary = [
            'attendance_opened' => 0,
            'attendance_closing_soon' => 0,
            'attendance_not_submitted' => 0,
        ];

        Activity::query()
            ->whereIn('status', ['scheduled', 'relocated'])
            ->whereDate('activity_date', now()->toDateString())
            ->get()
            ->each(function (Activity $activity) use (&$summary) {
                if ($activity->isAttendanceOpen()) {
                    $summary['attendance_opened'] += $this->notifyAttendanceOpened($activity)['created'];
                    $summary['attendance_not_submitted'] += $this->notifyAttendanceNotSubmitted($activity)['created'];
                }

                $closeAt = $activity->effectiveAttendanceCloseAt();

                if ($closeAt && now()->lessThanOrEqualTo($closeAt) && now()->diffInMinutes($closeAt, false) <= 30) {
                    $summary['attendance_closing_soon'] += $this->notifyAttendanceClosingSoon($activity)['created'];
                }
            });

        return $summary;
    }

    private function notifyMembers(Collection $members, string $type, string $relatedType, int $relatedId, string $title, string $message, ?string $url, array $data = [], bool $shouldPush = false): array
    {
        $result = ['targeted' => $members->count(), 'created' => 0, 'existing' => 0, 'skipped' => 0, 'push_sent' => 0, 'push_failed' => 0];

        foreach ($members as $member) {
            $notification = $this->notifyMember($member, $type, $relatedType, $relatedId, $title, $message, $url, $data, $shouldPush);

            if (! $member->user) {
                $result['skipped']++;
            } elseif ($notification?->wasRecentlyCreated) {
                $result['created']++;
            } else {
                $result['existing']++;
            }
        }

        return $result;
    }

    private function notifyMember(?Member $member, string $type, string $relatedType, int $relatedId, string $title, string $message, ?string $url, array $data = [], bool $shouldPush = false): ?Notification
    {
        if (! $member) {
            return null;
        }

        $member->loadMissing('user');
        $user = $member->user;

        if (! $user) {
            return null;
        }

        $dedupeKey = "{$type}:{$relatedType}:{$relatedId}";
        $category = NotificationPreference::typeToCategory($type);

        if (! NotificationPreference::allowsInApp($user, $category)) {
            return null;
        }

        $notification = Notification::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'dedupe_key' => $dedupeKey,
            ],
            [
                'member_id' => $member->id,
                'type' => $type,
                'related_type' => $relatedType,
                'related_id' => $relatedId,
                'title' => $title,
                'message' => $message,
                'url' => $url,
                'data' => [
                    'source' => 'automatic_rule',
                    'related_type' => $relatedType,
                    'related_id' => $relatedId,
                    ...$data,
                ],
                'read_at' => null,
            ],
        );

        if ($notification->wasRecentlyCreated && $shouldPush && $this->allowsPush($user, $category, $relatedType, $relatedId, $notification->id)) {
            $this->sendPush($user, $title, $message, $url, $type);
        }

        return $notification;
    }

    private function activeMembersWithUser(): Collection
    {
        return Member::query()
            ->with('user')
            ->where('member_status', 'active')
            ->whereHas('user')
            ->orderBy('full_name')
            ->get();
    }

    private function activityParticipantMembers(Activity $activity): Collection
    {
        $members = $activity->attendances()
            ->with('member.user')
            ->whereHas('member.user')
            ->get()
            ->pluck('member')
            ->filter()
            ->unique('id')
            ->values();

        return $members->isNotEmpty() ? $members : $this->activeMembersWithUser();
    }

    private function absentParticipantMembers(Activity $activity): Collection
    {
        return $activity->attendances()
            ->with('member.user')
            ->where('status', 'absent')
            ->whereHas('member.user')
            ->get()
            ->pluck('member')
            ->filter()
            ->unique('id')
            ->values();
    }

    private function sendPush(User $user, string $title, string $message, ?string $url, string $type): void
    {
        try {
            $this->pushNotificationService->sendToMemberUser($user, $title, $message, $url ?: route('member.home', absolute: false), $type);
        } catch (Throwable $exception) {
            Log::warning('Automatic notification push failed.', [
                'user_id' => $user->id,
                'type' => $type,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function allowsPush(User $user, string $category, string $relatedType, int $relatedId, int $notificationId): bool
    {
        if (! NotificationPreference::allowsPush($user, $category)) {
            return false;
        }

        $throttleMinutes = $this->pushThrottleMinutes($category);

        if ($throttleMinutes <= 0) {
            return true;
        }

        return ! Notification::query()
            ->where('user_id', $user->id)
            ->where('related_type', $relatedType)
            ->where('related_id', $relatedId)
            ->whereIn('type', NotificationPreference::typesForCategory($category))
            ->where('id', '!=', $notificationId)
            ->where('created_at', '>=', now()->subMinutes($throttleMinutes))
            ->exists();
    }

    private function pushThrottleMinutes(string $category): int
    {
        return match ($category) {
            'attendance_not_submitted',
            'attendance_closing_soon' => 30,
            default => 0,
        };
    }

    private function activityTitle(Attendance $attendance): string
    {
        return $attendance->activity?->title ?? 'kegiatan';
    }
}
