<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\AgendaSchedule;
use App\Models\Attendance;
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
            'active_agenda_schedules' => AgendaSchedule::where('is_active', true)->count(),
            'monthly_activities' => Activity::whereBetween('activity_date', [$monthStart, $monthEnd])->count(),
            'scheduled_activities' => Activity::where('status', 'scheduled')->count(),
            'holiday_activities' => Activity::where('status', 'holiday')->count(),
            'monthly_attendances' => Attendance::whereHas('activity', fn ($query) => $query
                ->whereBetween('activity_date', [$monthStart, $monthEnd]))
                ->count(),
        ];

        $upcomingActivities = Activity::query()
            ->whereDate('activity_date', '>=', today())
            ->orderBy('activity_date')
            ->orderByRaw('start_time is null')
            ->orderBy('start_time')
            ->limit(5)
            ->get();

        $recentAttendanceActivities = Activity::query()
            ->whereHas('attendances')
            ->withCount([
                'attendances as present_count' => fn ($query) => $query->where('status', 'present'),
                'attendances as permission_count' => fn ($query) => $query->where('status', 'permission'),
                'attendances as absent_count' => fn ($query) => $query->where('status', 'absent'),
                'attendances as need_verification_count' => fn ($query) => $query->where('status', 'need_verification'),
            ])
            ->orderByDesc('activity_date')
            ->orderByDesc('start_time')
            ->limit(5)
            ->get();

        $activeAgendaSchedules = AgendaSchedule::query()
            ->with(['department', 'pic'])
            ->where('is_active', true)
            ->latest('updated_at')
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'statistics',
            'upcomingActivities',
            'recentAttendanceActivities',
            'activeAgendaSchedules'
        ));
    }
}
