<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\Attendance;
use App\Models\Member;
use Illuminate\Support\Facades\DB;

class AttendanceSyncService
{
    public function syncActiveMembers(Activity $activity, ?int $createdBy = null): array
    {
        $members = Member::query()
            ->where('member_status', 'active')
            ->get(['id']);

        $existingMemberIds = $activity->attendances()
            ->whereIn('member_id', $members->pluck('id'))
            ->pluck('member_id');

        $newMemberIds = $members->pluck('id')->diff($existingMemberIds);

        DB::transaction(function () use ($activity, $newMemberIds, $createdBy) {
            foreach ($newMemberIds as $memberId) {
                Attendance::create([
                    'activity_id' => $activity->id,
                    'member_id' => $memberId,
                    'status' => 'absent',
                    'attendance_method' => 'manual',
                    'verification_status' => 'valid',
                    'checked_in_at' => null,
                    'created_by' => $createdBy,
                ]);
            }
        });

        return [
            'active_members_found' => $members->count(),
            'created' => $newMemberIds->count(),
            'already_exists' => $existingMemberIds->count(),
            'skipped' => 0,
        ];
    }
}
