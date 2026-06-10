<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\AgendaSchedule;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Member;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $statistics = [
            'active_members' => Member::where('member_status', 'active')->count(),
            'active_departments' => Department::where('status', 'active')->count(),
            'active_agenda_schedules' => AgendaSchedule::where('is_active', true)->count(),
            'monthly_activities' => Activity::whereBetween('activity_date', [$monthStart, $monthEnd])->count(),
            'need_verification_attendances' => Attendance::where('verification_status', 'need_verification')->count(),
        ];

        $upcomingActivities = Activity::query()
            ->with('department')
            ->whereDate('activity_date', '>=', today())
            ->orderBy('activity_date')
            ->orderByRaw('start_time is null')
            ->orderBy('start_time')
            ->limit(5)
            ->get();

        $monthlyAttendanceCounts = Attendance::query()
            ->whereHas('activity', fn ($query) => $query->whereBetween('activity_date', [$monthStart, $monthEnd]))
            ->selectRaw("sum(case when status = 'present' then 1 else 0 end) as present")
            ->selectRaw("sum(case when status = 'permission' then 1 else 0 end) as permission")
            ->selectRaw("sum(case when status = 'absent' then 1 else 0 end) as absent")
            ->selectRaw("sum(case when status = 'need_verification' then 1 else 0 end) as need_verification")
            ->first();
        $monthlyAttendanceTotal = (int) $monthlyAttendanceCounts->present
            + (int) $monthlyAttendanceCounts->permission
            + (int) $monthlyAttendanceCounts->absent
            + (int) $monthlyAttendanceCounts->need_verification;
        $monthlyAttendanceSummary = [
            'present' => (int) $monthlyAttendanceCounts->present,
            'permission' => (int) $monthlyAttendanceCounts->permission,
            'absent' => (int) $monthlyAttendanceCounts->absent,
            'need_verification' => (int) $monthlyAttendanceCounts->need_verification,
            'attendance_percentage' => $monthlyAttendanceTotal > 0
                ? round(((int) $monthlyAttendanceCounts->present / $monthlyAttendanceTotal) * 100, 2)
                : 0,
        ];

        $activeAgendaSchedules = AgendaSchedule::query()
            ->with(['department', 'pic'])
            ->where('is_active', true)
            ->latest('updated_at')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'statistics',
            'upcomingActivities',
            'monthlyAttendanceSummary',
            'activeAgendaSchedules'
        ));
    }
}
