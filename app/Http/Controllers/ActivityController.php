<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\AgendaSchedule;
use App\Models\Department;
use App\Models\Member;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $activityDate = $request->string('activity_date')->toString();
        $departmentId = $request->integer('department_id') ?: null;
        $status = $request->string('status')->toString();
        $agendaScheduleId = $request->integer('agenda_schedule_id') ?: null;

        $activities = Activity::query()
            ->with(['agendaSchedule', 'department', 'pic'])
            ->when($search, fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->when($activityDate, fn ($query) => $query->whereDate('activity_date', $activityDate))
            ->when($departmentId, fn ($query) => $query->where('department_id', $departmentId))
            ->when(
                in_array($status, $this->statuses(), true),
                fn ($query) => $query->where('status', $status)
            )
            ->when($agendaScheduleId, fn ($query) => $query->where('agenda_schedule_id', $agendaScheduleId))
            ->orderByDesc('activity_date')
            ->orderByDesc('start_time')
            ->paginate(10)
            ->withQueryString();

        return view('activities.index', array_merge(
            compact('activities', 'search', 'activityDate', 'departmentId', 'status', 'agendaScheduleId'),
            $this->filterOptions()
        ));
    }

    public function create(): View
    {
        return view('activities.create', $this->formOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['created_by'] = $request->user()->id;
        $this->ensureAttendanceToken($data);

        $activity = Activity::create($data);

        return redirect()
            ->route('activities.show', $activity)
            ->with('success', 'Kegiatan aktual berhasil ditambahkan.');
    }

    public function show(Activity $activity): View
    {
        $activity->load(['agendaSchedule', 'department', 'pic', 'creator']);

        return view('activities.show', compact('activity'));
    }

    public function edit(Activity $activity): View
    {
        return view('activities.edit', array_merge(
            ['activity' => $activity],
            $this->formOptions()
        ));
    }

    public function update(Request $request, Activity $activity): RedirectResponse
    {
        $data = $this->validatedData($request);
        $this->ensureAttendanceToken($data, $activity);
        $activity->update($data);

        return redirect()
            ->route('activities.show', $activity)
            ->with('success', 'Kegiatan aktual berhasil diperbarui.');
    }

    public function destroy(Activity $activity): RedirectResponse
    {
        $activity->delete();

        return redirect()
            ->route('activities.index')
            ->with('success', 'Kegiatan aktual berhasil dihapus.');
    }

    public function updateStatus(Request $request, Activity $activity): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in($this->statuses())],
            'change_reason' => ['nullable', 'string'],
        ]);

        $activity->update($validated);

        return redirect()
            ->route('activities.show', $activity)
            ->with('success', 'Status kegiatan berhasil diperbarui.');
    }

    public function createFromSchedule(AgendaSchedule $agendaSchedule): View
    {
        $agendaSchedule->load(['department', 'pic']);

        return view('activities.create-from-schedule', compact('agendaSchedule'));
    }

    public function storeFromSchedule(Request $request, AgendaSchedule $agendaSchedule): RedirectResponse
    {
        $validated = $request->validate([
            'activity_date' => ['required', 'date'],
        ]);

        $activity = Activity::create([
            'agenda_schedule_id' => $agendaSchedule->id,
            'department_id' => $agendaSchedule->department_id,
            'pic_id' => $agendaSchedule->pic_id,
            'title' => $agendaSchedule->title,
            'activity_date' => $validated['activity_date'],
            'start_time' => $agendaSchedule->start_time,
            'end_time' => $agendaSchedule->end_time,
            'location' => $agendaSchedule->default_location,
            'latitude' => $agendaSchedule->default_latitude,
            'longitude' => $agendaSchedule->default_longitude,
            'attendance_radius' => $agendaSchedule->default_radius,
            'status' => 'scheduled',
            'attendance_enabled' => false,
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('activities.edit', $activity)
            ->with('success', 'Kegiatan dibuat dari jadwal. Lengkapi atau periksa detailnya.');
    }

    public function attendancesPlaceholder(Activity $activity): RedirectResponse
    {
        return redirect()
            ->route('activities.show', $activity)
            ->with('info', 'Daftar hadir kegiatan akan tersedia pada tahap presensi berikutnya.');
    }

    private function validatedData(Request $request): array
    {
        $attendanceCloseRules = ['nullable', 'date'];

        if ($request->filled('attendance_open_at') && $request->filled('attendance_close_at')) {
            $attendanceCloseRules[] = 'after:attendance_open_at';
        }

        return $request->validate([
            'agenda_schedule_id' => ['nullable', 'exists:agenda_schedules,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'pic_id' => ['nullable', 'exists:members,id'],
            'title' => ['required', 'string', 'max:255'],
            'activity_date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'location' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'attendance_radius' => ['required', 'numeric', 'min:1'],
            'status' => ['required', Rule::in($this->statuses())],
            'change_reason' => ['nullable', 'string'],
            'attendance_enabled' => ['required', 'boolean'],
            'attendance_open_at' => ['nullable', 'date'],
            'attendance_close_at' => $attendanceCloseRules,
        ]);
    }

    private function ensureAttendanceToken(array &$data, ?Activity $activity = null): void
    {
        if (! $data['attendance_enabled'] || $activity?->attendance_token) {
            return;
        }

        do {
            $token = Str::random(40);
        } while (Activity::where('attendance_token', $token)->exists());

        $data['attendance_token'] = $token;
    }

    private function formOptions(): array
    {
        return [
            'agendaSchedules' => AgendaSchedule::orderBy('title')->get(),
            'departments' => Department::where('status', 'active')->orderBy('name')->get(),
            'members' => Member::where('member_status', 'active')->orderBy('full_name')->get(),
            'statuses' => $this->statuses(),
        ];
    }

    private function filterOptions(): array
    {
        return [
            'agendaSchedules' => AgendaSchedule::orderBy('title')->get(['id', 'title']),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'statuses' => $this->statuses(),
        ];
    }

    private function statuses(): array
    {
        return ['scheduled', 'completed', 'holiday', 'postponed', 'relocated', 'cancelled'];
    }
}
