<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $activityId = $request->integer('activity_id') ?: null;
        $status = $request->string('status')->toString();
        $memberId = $request->integer('member_id') ?: null;
        $departmentId = $request->integer('department_id') ?: null;

        $attendances = Attendance::query()
            ->with(['activity', 'member.department'])
            ->when($activityId, fn ($query) => $query->where('activity_id', $activityId))
            ->when(in_array($status, $this->statuses(), true), fn ($query) => $query->where('status', $status))
            ->when($memberId, fn ($query) => $query->where('member_id', $memberId))
            ->when($departmentId, function ($query) use ($departmentId) {
                $query->whereHas('member', fn ($memberQuery) => $memberQuery->where('department_id', $departmentId));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('attendances.index', [
            'attendances' => $attendances,
            'activities' => Activity::orderByDesc('activity_date')->orderBy('title')->get(),
            'members' => Member::orderBy('full_name')->get(['id', 'full_name']),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'statuses' => $this->statuses(),
            'activityId' => $activityId,
            'status' => $status,
            'memberId' => $memberId,
            'departmentId' => $departmentId,
        ]);
    }

    public function byActivity(Activity $activity): View
    {
        $activity->load(['department', 'pic']);

        $attendances = $activity->attendances()
            ->with('member.department')
            ->get()
            ->sortBy('member.full_name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $summary = collect($this->statuses())
            ->mapWithKeys(fn ($status) => [$status => $attendances->where('status', $status)->count()]);

        return view('attendances.activity', compact('activity', 'attendances', 'summary'));
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

        $attendance->update(array_merge($validated, ['attendance_method' => 'manual']));

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

    private function statuses(): array
    {
        return ['present', 'permission', 'absent', 'need_verification'];
    }
}
