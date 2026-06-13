<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\AgendaSchedule;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberHomeController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user()->load(['member.department', 'member.position']);
        $attendanceHistory = collect();
        $currentActivities = collect();

        if ($user->member) {
            $attendanceHistory = Attendance::query()
                ->with('activity')
                ->where('member_id', $user->member->id)
                ->latest('checked_in_at')
                ->latest()
                ->limit(10)
                ->get();

            $currentActivities = Activity::query()
                ->with(['department', 'pic'])
                ->with(['attendances' => fn ($query) => $query->where('member_id', $user->member->id)])
                ->where('attendance_enabled', true)
                ->whereNotIn('status', ['cancelled', 'holiday'])
                ->whereNotNull('attendance_open_at')
                ->whereNotNull('attendance_close_at')
                ->where('attendance_open_at', '<=', now())
                ->where('attendance_close_at', '>=', now())
                ->orderBy('activity_date')
                ->orderByRaw('start_time is null')
                ->orderBy('start_time')
                ->limit(3)
                ->get();
        }

        $upcomingActivities = Activity::query()
            ->with(['department', 'pic'])
            ->whereDate('activity_date', '>=', now()->toDateString())
            ->where('status', '!=', 'cancelled')
            ->orderBy('activity_date')
            ->orderByRaw('start_time is null')
            ->orderBy('start_time')
            ->limit(5)
            ->get();

        $activeAgendaSchedules = collect();

        if ($upcomingActivities->isEmpty()) {
            $activeAgendaSchedules = AgendaSchedule::query()
                ->with('department')
                ->where('is_active', true)
                ->orderBy('title')
                ->limit(5)
                ->get();
        }

        return view('member.home', [
            'user' => $user,
            'attendanceHistory' => $attendanceHistory,
            'currentActivities' => $currentActivities,
            'upcomingActivities' => $upcomingActivities,
            'activeAgendaSchedules' => $activeAgendaSchedules,
        ]);
    }
}
