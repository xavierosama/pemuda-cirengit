<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AttendanceReportController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'activity_id' => ['nullable', 'exists:activities,id'],
        ]);

        $startDate = isset($validated['start_date'])
            ? Carbon::parse($validated['start_date'])->startOfDay()
            : now()->startOfMonth();
        $endDate = isset($validated['end_date'])
            ? Carbon::parse($validated['end_date'])->endOfDay()
            : now()->endOfMonth();
        $departmentId = $validated['department_id'] ?? null;
        $activityId = $validated['activity_id'] ?? null;

        $activities = Activity::query()
            ->with('department')
            ->whereBetween('activity_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->when($departmentId, fn ($query) => $query->where('department_id', $departmentId))
            ->when($activityId, fn ($query) => $query->whereKey($activityId))
            ->orderBy('activity_date')
            ->orderBy('start_time')
            ->orderBy('title')
            ->get();

        $members = Member::query()
            ->with('department')
            ->where('member_status', 'active')
            ->when($departmentId, fn ($query) => $query->where('department_id', $departmentId))
            ->orderBy('full_name')
            ->get();

        $activityIds = $activities->pluck('id');
        $memberIds = $members->pluck('id');

        $attendances = Attendance::query()
            ->with(['activity.department', 'member.department'])
            ->whereIn('activity_id', $activityIds)
            ->whereIn('member_id', $memberIds)
            ->get();

        $statuses = ['present', 'permission', 'absent', 'need_verification'];
        $statusCounts = collect($statuses)
            ->mapWithKeys(fn ($status) => [$status => $attendances->where('status', $status)->count()])
            ->all();

        $totalPotentialAttendances = $activities->count() * $members->count();
        $attendancePercentage = $this->percentage($statusCounts['present'], $totalPotentialAttendances);

        $summary = [
            'total_activities' => $activities->count(),
            'total_active_members' => $members->count(),
            'present' => $statusCounts['present'],
            'permission' => $statusCounts['permission'],
            'absent' => $statusCounts['absent'],
            'need_verification' => $statusCounts['need_verification'],
            'attendance_percentage' => $attendancePercentage,
            'total_potential_attendances' => $totalPotentialAttendances,
        ];

        $attendancesByActivity = $attendances->groupBy('activity_id');
        $activityRows = $activities->map(function (Activity $activity) use ($attendancesByActivity, $members, $statuses) {
            $activityAttendances = $attendancesByActivity->get($activity->id, collect());
            $counts = $this->statusCounts($activityAttendances, $statuses);

            return [
                'activity' => $activity,
                'counts' => $counts,
                'attendance_percentage' => $this->percentage($counts['present'], $members->count()),
            ];
        });

        $attendancesByMember = $attendances->groupBy('member_id');
        $memberRows = $members->map(function (Member $member) use ($attendancesByMember, $activities, $statuses) {
            $memberAttendances = $attendancesByMember->get($member->id, collect());
            $counts = $this->statusCounts($memberAttendances, $statuses);

            return [
                'member' => $member,
                'counts' => $counts,
                'attendance_percentage' => $this->percentage($counts['present'], $activities->count()),
            ];
        });

        $departmentRows = $attendances
            ->where('status', 'present')
            ->groupBy(fn (Attendance $attendance) => $attendance->member->department?->name ?? 'Tanpa bidang')
            ->map(fn ($rows, $departmentName) => [
                'department' => $departmentName,
                'present' => $rows->count(),
            ])
            ->sortBy('department')
            ->values();

        $chartData = [
            'statusComposition' => [
                'labels' => ['Hadir', 'Izin', 'Tidak Hadir', 'Perlu Verifikasi'],
                'data' => [
                    $statusCounts['present'],
                    $statusCounts['permission'],
                    $statusCounts['absent'],
                    $statusCounts['need_verification'],
                ],
            ],
            'activityTrend' => [
                'labels' => $activityRows
                    ->map(fn (array $row) => $row['activity']->activity_date->format('d/m/Y').' - '.$row['activity']->title)
                    ->values(),
                'data' => $activityRows->map(fn (array $row) => $row['counts']['present'])->values(),
            ],
            'departmentAttendance' => [
                'labels' => $departmentRows->pluck('department')->values(),
                'data' => $departmentRows->pluck('present')->values(),
            ],
        ];

        return view('attendance-reports.index', [
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'activityOptions' => Activity::orderByDesc('activity_date')->orderBy('title')->get(['id', 'title', 'activity_date']),
            'filters' => [
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'department_id' => $departmentId,
                'activity_id' => $activityId,
            ],
            'summary' => $summary,
            'statusCounts' => $statusCounts,
            'activityRows' => $activityRows,
            'memberRows' => $memberRows,
            'chartData' => $chartData,
        ]);
    }

    private function statusCounts($attendances, array $statuses): array
    {
        return collect($statuses)
            ->mapWithKeys(fn ($status) => [$status => $attendances->where('status', $status)->count()])
            ->all();
    }

    private function percentage(int $value, int $total): float
    {
        if ($total === 0) {
            return 0;
        }

        return round(($value / $total) * 100, 2);
    }
}
