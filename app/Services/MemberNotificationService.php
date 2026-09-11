<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Attendance;
use App\Models\Member;
use App\Models\Notification;
use App\Models\NotificationPreference;
use App\Models\User;
use App\Support\DateFormatter;
use Illuminate\Support\Carbon;

class MemberNotificationService
{
    public function __construct(
        private readonly PushNotificationService $pushNotificationService,
        private readonly NotificationRuleService $notificationRuleService,
    )
    {
    }

    public function syncForUser(User $user): void
    {
        $member = $user->member;

        if (! $member) {
            return;
        }

        $this->profileIncomplete($user, $member);
        $this->upcomingActivities($user, $member);
        $this->openAttendance($user, $member);
        $this->attendanceVerification($user, $member);
    }

    private function profileIncomplete(User $user, Member $member): void
    {
        $this->notificationRuleService->notifyProfileIncomplete($member);
    }

    private function upcomingActivities(User $user, Member $member): void
    {
        Activity::query()
            ->whereIn('status', ['scheduled', 'relocated'])
            ->whereDate('activity_date', '>=', now()->toDateString())
            ->whereDate('activity_date', '<=', now()->copy()->addDay()->toDateString())
            ->orderBy('activity_date')
            ->orderByRaw('start_time is null')
            ->orderBy('start_time')
            ->limit(5)
            ->get()
            ->each(function (Activity $activity) use ($user, $member) {
                $time = DateFormatter::time($activity->start_time, '');
                $date = $activity->activity_date?->isToday()
                    ? 'hari ini'
                    : DateFormatter::date($activity->activity_date);

                $message = $time !== ''
                    ? "Kegiatan {$activity->title} akan dimulai {$date} pukul {$time}."
                    : "Kegiatan {$activity->title} dijadwalkan {$date}.";

                $this->updateOrCreate(
                    $user,
                    $member,
                    'activity_reminder',
                    'Reminder kegiatan terdekat',
                    $message,
                    route('member.home', absolute: false).'#kegiatan-mendatang',
                    ['activity_id' => $activity->id],
                );
            });
    }

    private function openAttendance(User $user, Member $member): void
    {
        Activity::query()
            ->with(['attendances' => fn ($query) => $query->where('member_id', $member->id)])
            ->whereIn('status', ['scheduled', 'relocated'])
            ->whereDate('activity_date', now()->toDateString())
            ->orderBy('activity_date')
            ->orderByRaw('start_time is null')
            ->orderBy('start_time')
            ->get()
            ->filter(fn (Activity $activity) => $activity->isAttendanceOpen())
            ->each(function (Activity $activity) use ($user, $member) {
                $attendance = $activity->attendances->first();
                $url = route('member.home', absolute: false).'#kegiatan-sekarang';

                $this->notificationRuleService->notifyAttendanceOpened($activity);

                if (! $attendance || $attendance->status === 'absent') {
                    $this->notificationRuleService->notifyAttendanceNotSubmitted($activity);
                } else {
                    $this->markExistingRead($user, 'attendance_pending:'.$activity->id);
                    $this->markExistingRead($user, 'attendance_not_submitted:activity:'.$activity->id);
                }

                $closeAt = $activity->effectiveAttendanceCloseAt();

                if ($closeAt instanceof Carbon && now()->lessThanOrEqualTo($closeAt) && now()->diffInMinutes($closeAt, false) <= 30) {
                    $this->notificationRuleService->notifyAttendanceClosingSoon($activity);
                }
            });
    }

    private function attendanceVerification(User $user, Member $member): void
    {
        Attendance::query()
            ->with('activity')
            ->where('member_id', $member->id)
            ->whereIn('verification_status', ['need_verification', 'valid', 'rejected'])
            ->whereIn('status', ['present', 'permission', 'need_verification'])
            ->where('updated_at', '>=', now()->copy()->subDays(14))
            ->latest('updated_at')
            ->limit(10)
            ->get()
            ->each(function (Attendance $attendance) use ($user, $member) {
                $activityTitle = $attendance->activity?->title ?? 'kegiatan';
                $url = route('member.home', absolute: false).'#riwayat-presensi';

                if ($attendance->verification_status === 'need_verification') {
                    $this->notificationRuleService->notifyAttendancePendingVerification($attendance);

                    return;
                }

                if ($attendance->verification_status === 'valid') {
                    $this->markExistingRead($user, 'attendance_verification_pending:'.$attendance->id);

                    $this->notificationRuleService->notifyAttendanceVerified($attendance);

                    return;
                }

                $this->markExistingRead($user, 'attendance_verification_pending:'.$attendance->id);

                $this->notificationRuleService->notifyAttendanceRejected($attendance);
            });
    }

    private function updateOrCreate(User $user, Member $member, string $type, string $title, string $message, ?string $url = null, array $data = []): void
    {
        $dedupeKey = $type.':'.($data['activity_id'] ?? $data['attendance_id'] ?? 'member');
        $category = NotificationPreference::typeToCategory($type);

        if (! NotificationPreference::allowsInApp($user, $category)) {
            return;
        }

        $notification = Notification::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'dedupe_key' => $dedupeKey,
            ],
            [
                'member_id' => $member->id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'url' => $url,
                'data' => $data,
            ],
        );

        if ($notification->wasRecentlyCreated && $this->shouldSendPush($type) && NotificationPreference::allowsPush($user, $category)) {
            $this->pushNotificationService->sendToMemberUser($user, $title, $message, $url, $type);
        }
    }

    private function markExistingRead(User $user, string $dedupeKey): void
    {
        Notification::query()
            ->where('user_id', $user->id)
            ->where('dedupe_key', $dedupeKey)
            ->whereNull('read_at')
            ->update(['read_at' => now(), 'updated_at' => now()]);
    }

    private function shouldSendPush(string $type): bool
    {
        return in_array($type, [
            'activity_reminder',
            'attendance_open',
            'attendance_pending',
            'attendance_closing',
            'activity_changed',
            'admin_announcement',
        ], true);
    }
}
