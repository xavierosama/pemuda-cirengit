<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Member;
use App\Support\TableControls;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $departmentId = $request->integer('department_id') ?: null;
        $activityStatus = $request->string('activity_status')->toString();
        $attendanceStatus = $request->string('attendance_enabled')->toString();
        $startDate = $request->string('start_date')->toString();
        $endDate = $request->string('end_date')->toString();
        $activityStatuses = ['scheduled', 'completed', 'holiday', 'postponed', 'relocated', 'cancelled'];
        $allowedSorts = [
            'title' => 'title',
            'activity_date' => 'activity_date',
            'start_time' => 'start_time',
            'status' => 'status',
            'attendance_enabled' => 'attendance_enabled',
        ];
        $currentSort = TableControls::sort($request, $allowedSorts);
        $currentDirection = TableControls::direction($request);
        $perPage = TableControls::perPage($request);

        $activities = Activity::query()
            ->with(['department', 'pic'])
            ->withCount([
                'attendances as present_count' => fn ($query) => $query->where('status', 'present'),
                'attendances as permission_count' => fn ($query) => $query->where('status', 'permission'),
                'attendances as absent_count' => fn ($query) => $query->where('status', 'absent'),
                'attendances as need_verification_count' => fn ($query) => $query->where('status', 'need_verification'),
            ])
            ->when($search, fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->when($departmentId, fn ($query) => $query->where('department_id', $departmentId))
            ->when(in_array($activityStatus, $activityStatuses, true), fn ($query) => $query->where('status', $activityStatus))
            ->when(
                in_array($attendanceStatus, ['0', '1'], true),
                fn ($query) => $query->where('attendance_enabled', $attendanceStatus === '1')
            )
            ->when($startDate, fn ($query) => $query->whereDate('activity_date', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->whereDate('activity_date', '<=', $endDate))
            ->tap(fn ($query) => TableControls::applySort($query, $currentSort, $currentDirection, $allowedSorts, fn ($query) => $query->orderByDesc('activity_date')->orderByDesc('start_time')))
            ->paginate($perPage)
            ->withQueryString();

        $monthActivityIds = Activity::whereYear('activity_date', now()->year)
            ->whereMonth('activity_date', now()->month)
            ->pluck('id');

        $attendanceStats = [
            'active_activities' => Activity::where('attendance_enabled', true)->count(),
            'monthly_total' => Attendance::whereIn('activity_id', $monthActivityIds)->count(),
            'monthly_present' => Attendance::whereIn('activity_id', $monthActivityIds)->where('status', 'present')->count(),
            'monthly_permission' => Attendance::whereIn('activity_id', $monthActivityIds)->where('status', 'permission')->count(),
            'monthly_absent' => Attendance::whereIn('activity_id', $monthActivityIds)->where('status', 'absent')->count(),
            'need_verification' => Attendance::where('status', 'need_verification')->count(),
        ];

        return view('attendances.index', [
            'activities' => $activities,
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'activityStatuses' => $activityStatuses,
            'attendanceStats' => $attendanceStats,
            'search' => $search,
            'departmentId' => $departmentId,
            'activityStatus' => $activityStatus,
            'attendanceStatus' => $attendanceStatus,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ] + TableControls::viewData($request, $currentSort, $currentDirection, $perPage));
    }

    public function byActivity(Request $request, Activity $activity): View
    {
        $activity->load(['department', 'pic']);
        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();
        $departmentId = $request->integer('department_id') ?: null;
        $allowedSorts = [
            'npa' => fn (Attendance $attendance) => $attendance->member->npa ?? '',
            'full_name' => fn (Attendance $attendance) => $attendance->member->full_name,
            'status' => fn (Attendance $attendance) => $attendance->status,
            'checked_in_at' => fn (Attendance $attendance) => $attendance->checked_in_at?->timestamp ?? 0,
            'verification_status' => fn (Attendance $attendance) => $attendance->verification_status,
        ];
        $currentSort = TableControls::sort($request, $allowedSorts);
        $currentDirection = TableControls::direction($request);
        $perPage = TableControls::perPage($request);

        $allAttendances = $activity->attendances()
            ->with(['member.department', 'member.position'])
            ->whereHas('member')
            ->get()
            ->sortBy('member.full_name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $summary = collect($this->statuses())
            ->mapWithKeys(fn ($status) => [$status => $allAttendances->where('status', $status)->count()]);
        $totalAttendances = $summary->sum();
        $attendancePercentage = $totalAttendances > 0
            ? round(($summary['present'] / $totalAttendances) * 100, 2)
            : 0;

        $attendances = $allAttendances
            ->when($search, function ($rows) use ($search) {
                $keyword = str($search)->lower()->toString();

                return $rows->filter(fn (Attendance $attendance) => str($attendance->member->full_name)->lower()->contains($keyword)
                    || str($attendance->member->npa ?? '')->lower()->contains($keyword));
            })
            ->when(in_array($status, $this->statuses(), true), fn ($rows) => $rows->where('status', $status))
            ->when($departmentId, fn ($rows) => $rows->filter(fn (Attendance $attendance) => (int) $attendance->member->department_id === (int) $departmentId))
            ->when(
                $currentSort,
                fn ($rows) => $currentDirection === 'desc'
                    ? $rows->sortByDesc($allowedSorts[$currentSort], SORT_NATURAL | SORT_FLAG_CASE)
                    : $rows->sortBy($allowedSorts[$currentSort], SORT_NATURAL | SORT_FLAG_CASE)
            )
            ->values();
        $attendances = new LengthAwarePaginator(
            $attendances->forPage(LengthAwarePaginator::resolveCurrentPage(), $perPage)->values(),
            $attendances->count(),
            $perPage,
            LengthAwarePaginator::resolveCurrentPage(),
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $departments = Department::orderBy('name')->get(['id', 'name']);

        return view('attendances.activity', array_merge(compact(
            'activity',
            'attendances',
            'summary',
            'attendancePercentage',
            'departments',
            'search',
            'status',
            'departmentId'
        ), TableControls::viewData($request, $currentSort, $currentDirection, $perPage)));
    }

    public function createManual(Activity $activity): View
    {
        return view('attendances.create', [
            'activity' => $activity,
            'members' => Member::where('member_status', 'active')->orderBy('full_name')->get(),
            'statuses' => $this->statuses(),
        ]);
    }

    public function storeManual(Request $request, Activity $activity): RedirectResponse
    {
        $validated = $request->validate([
            'activity_id' => ['required', Rule::in([$activity->id])],
            'member_id' => ['required', 'exists:members,id'],
            'status' => ['required', Rule::in($this->statuses())],
            'notes' => ['nullable', 'string'],
        ]);

        $attendance = Attendance::firstOrNew([
            'activity_id' => $activity->id,
            'member_id' => $validated['member_id'],
        ]);

        $attendance->fill([
            'status' => $validated['status'],
            'attendance_method' => 'manual',
            'notes' => $validated['notes'] ?? null,
        ]);

        if (! $attendance->exists) {
            $attendance->created_by = $request->user()->id;
        }

        $attendance->save();

        return redirect()
            ->route('activities.attendances.index', $activity)
            ->with('success', 'Data kehadiran anggota berhasil disimpan.');
    }

    public function createBulk(Activity $activity): View
    {
        $members = Member::query()
            ->with('department')
            ->where('member_status', 'active')
            ->orderBy('full_name')
            ->get();

        $existingAttendances = $activity->attendances()
            ->whereIn('member_id', $members->pluck('id'))
            ->get()
            ->keyBy('member_id');

        return view('attendances.bulk', compact('activity', 'members', 'existingAttendances'));
    }

    public function storeBulk(Request $request, Activity $activity): RedirectResponse
    {
        $validated = $request->validate([
            'activity_id' => ['required', Rule::in([$activity->id])],
            'attendances' => ['required', 'array'],
            'attendances.*.member_id' => ['required', 'distinct', Rule::exists('members', 'id')->where('member_status', 'active')],
            'attendances.*.status' => ['required', Rule::in($this->statuses())],
            'attendances.*.notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($validated, $activity, $request) {
            foreach ($validated['attendances'] as $row) {
                $attendance = Attendance::firstOrNew([
                    'activity_id' => $activity->id,
                    'member_id' => $row['member_id'],
                ]);

                $attendance->fill([
                    'status' => $row['status'],
                    'attendance_method' => 'manual',
                    'notes' => $row['notes'] ?? null,
                ]);

                if (! $attendance->exists) {
                    $attendance->created_by = $request->user()->id;
                }

                $attendance->save();
            }
        });

        return redirect()
            ->route('activities.attendances.index', $activity)
            ->with('success', 'Daftar hadir massal berhasil disimpan.');
    }

    public function syncParticipants(Request $request, Activity $activity): RedirectResponse
    {
        $members = Member::query()
            ->where('member_status', 'active')
            ->get(['id']);
        $totalFound = $members->count();
        $skipped = 0;

        $existingMemberIds = $activity->attendances()
            ->whereIn('member_id', $members->pluck('id'))
            ->pluck('member_id');
        $existingCount = $existingMemberIds->count();

        $newMemberIds = $members->pluck('id')->diff($existingMemberIds);
        $addedCount = $newMemberIds->count();

        DB::transaction(function () use ($activity, $newMemberIds, $request) {
            foreach ($newMemberIds as $memberId) {
                Attendance::create([
                    'activity_id' => $activity->id,
                    'member_id' => $memberId,
                    'status' => 'absent',
                    'attendance_method' => 'manual',
                    'verification_status' => 'valid',
                    'checked_in_at' => null,
                    'created_by' => $request->user()->id,
                ]);
            }
        });

        return redirect()
            ->route('activities.attendances.index', $activity)
            ->with('success', sprintf(
                'Sinkronisasi peserta selesai. %d anggota aktif ditemukan, %d attendance baru ditambahkan, %d anggota sudah ada di daftar hadir, %d anggota dilewati.',
                $totalFound,
                $addedCount,
                $existingCount,
                $skipped
            ));
    }

    public function edit(Attendance $attendance): View
    {
        $attendance->load(['activity', 'member.department']);

        return view('attendances.edit', [
            'attendance' => $attendance,
            'statuses' => $this->statuses(),
        ]);
    }

    public function update(Request $request, Attendance $attendance): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in($this->statuses())],
            'notes' => ['nullable', 'string'],
        ]);

        $attendance->update($validated);

        return redirect()
            ->route('activities.attendances.index', $attendance->activity_id)
            ->with('success', 'Status kehadiran berhasil diperbarui.');
    }

    public function destroy(Attendance $attendance): RedirectResponse
    {
        $activityId = $attendance->activity_id;
        $attendance->delete();

        return redirect()
            ->route('activities.attendances.index', $activityId)
            ->with('success', 'Data kehadiran berhasil dihapus.');
    }

    public function verify(Request $request, Attendance $attendance): RedirectResponse
    {
        $attendance->update([
            'status' => 'present',
            'verification_status' => 'valid',
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ]);

        return back()->with('success', 'Presensi berhasil diverifikasi sebagai valid.');
    }

    public function reject(Request $request, Attendance $attendance): RedirectResponse
    {
        $attendance->update([
            'status' => 'need_verification',
            'verification_status' => 'rejected',
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
        ]);

        return back()->with('success', 'Presensi telah ditolak.');
    }

    private function statuses(): array
    {
        return ['present', 'permission', 'absent', 'need_verification'];
    }
}
