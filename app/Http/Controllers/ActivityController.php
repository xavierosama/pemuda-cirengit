<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\AgendaSchedule;
use App\Models\Department;
use App\Models\Member;
use App\Support\SystemSettings;
use App\Support\TableControls;
use Illuminate\Support\Carbon;
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
        $startDate = $request->string('start_date')->toString();
        $endDate = $request->string('end_date')->toString();
        $departmentId = $request->integer('department_id') ?: null;
        $status = $request->string('status')->toString();
        $agendaScheduleId = $request->integer('agenda_schedule_id') ?: null;
        $attendanceStatus = $request->string('attendance_enabled')->toString();
        $allowedSorts = [
            'title' => 'title',
            'activity_date' => 'activity_date',
            'start_time' => 'start_time',
            'status' => 'status',
            'attendance_enabled' => 'attendance_enabled',
            'created_at' => 'created_at',
        ];
        $currentSort = TableControls::sort($request, $allowedSorts);
        $currentDirection = TableControls::direction($request);
        $perPage = TableControls::perPage($request);

        $activities = Activity::query()
            ->with(['agendaSchedule', 'department', 'pic'])
            ->when($search, fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->when($activityDate, fn ($query) => $query->whereDate('activity_date', $activityDate))
            ->when($startDate, fn ($query) => $query->whereDate('activity_date', '>=', $startDate))
            ->when($endDate, fn ($query) => $query->whereDate('activity_date', '<=', $endDate))
            ->when($departmentId, fn ($query) => $query->where('department_id', $departmentId))
            ->when(
                in_array($status, $this->statuses(), true),
                fn ($query) => $query->where('status', $status)
            )
            ->when($agendaScheduleId, fn ($query) => $query->where('agenda_schedule_id', $agendaScheduleId))
            ->when(
                in_array($attendanceStatus, ['0', '1'], true),
                fn ($query) => $query->where('attendance_enabled', $attendanceStatus === '1')
            )
            ->tap(fn ($query) => TableControls::applySort($query, $currentSort, $currentDirection, $allowedSorts, fn ($query) => $query->orderByDesc('activity_date')->orderByDesc('start_time')))
            ->paginate($perPage)
            ->withQueryString();

        $activityStats = [
            'current_month' => Activity::whereYear('activity_date', now()->year)
                ->whereMonth('activity_date', now()->month)
                ->count(),
            'scheduled' => Activity::where('status', 'scheduled')->count(),
            'completed' => Activity::where('status', 'completed')->count(),
            'postponed_cancelled' => Activity::whereIn('status', ['postponed', 'cancelled'])->count(),
            'attendance_enabled' => Activity::where('attendance_enabled', true)->count(),
        ];

        return view('activities.index', array_merge(
            compact('activities', 'activityStats', 'search', 'activityDate', 'startDate', 'endDate', 'departmentId', 'status', 'agendaScheduleId', 'attendanceStatus'),
            $this->filterOptions(),
            TableControls::viewData($request, $currentSort, $currentDirection, $perPage)
        ));
    }

    public function create(): View
    {
        return view('activities.create', $this->formOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $this->applyAutomaticAttendanceSchedule($data);
        $data['created_by'] = $request->user()->id;
        $this->ensureAttendanceToken($data);

        $activity = Activity::create($data);

        return redirect()
            ->route('activities.show', $activity)
            ->with('success', 'Kegiatan aktual berhasil ditambahkan.');
    }

    public function show(Activity $activity): View
    {
        $activity->load(['agendaSchedule', 'department', 'pic', 'creator', 'attendances']);

        $attendanceSummary = [
            'present' => $activity->attendances->where('status', 'present')->count(),
            'permission' => $activity->attendances->where('status', 'permission')->count(),
            'absent' => $activity->attendances->where('status', 'absent')->count(),
            'need_verification' => $activity->attendances->where('status', 'need_verification')->count(),
        ];
        $totalAttendances = array_sum($attendanceSummary);
        $attendanceSummary['attendance_percentage'] = $totalAttendances > 0
            ? round(($attendanceSummary['present'] / $totalAttendances) * 100, 2)
            : 0;

        return view('activities.show', compact('activity', 'attendanceSummary'));
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
        $this->applyAutomaticAttendanceSchedule($data);
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

        $data = array_merge([
            'activity_date' => $activity->activity_date?->format('Y-m-d'),
            'start_time' => $activity->start_time ? substr($activity->start_time, 0, 5) : null,
            'end_time' => $activity->end_time ? substr($activity->end_time, 0, 5) : null,
        ], $validated);
        $this->applyAutomaticAttendanceSchedule($data);
        $this->ensureAttendanceToken($data, $activity);

        $activity->update($data);

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
        $attendanceDefaults = app(SystemSettings::class)->attendanceDefaults();
        $picId = $agendaSchedule->pic_id ?: $this->departmentChairPicId($agendaSchedule->department_id);
        $data = [
            'agenda_schedule_id' => $agendaSchedule->id,
            'department_id' => $agendaSchedule->department_id,
            'pic_id' => $picId,
            'title' => $agendaSchedule->title,
            'description' => $agendaSchedule->description,
            'activity_date' => $validated['activity_date'],
            'start_time' => $agendaSchedule->start_time,
            'end_time' => $agendaSchedule->end_time,
            'location' => $agendaSchedule->default_location,
            'latitude' => $agendaSchedule->default_latitude,
            'longitude' => $agendaSchedule->default_longitude,
            'attendance_radius' => $agendaSchedule->default_radius ?: $attendanceDefaults['radius'],
            'status' => 'scheduled',
            'created_by' => $request->user()->id,
        ];
        $this->applyAutomaticAttendanceSchedule($data);
        $this->ensureAttendanceToken($data);

        $activity = Activity::create($data);

        return redirect()
            ->route('activities.edit', $activity)
            ->with('success', 'Kegiatan dibuat dari jadwal. Lengkapi atau periksa detailnya.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'agenda_schedule_id' => ['nullable', 'exists:agenda_schedules,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'pic_id' => ['nullable', 'exists:members,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'activity_date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'location' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'attendance_radius' => ['required', 'numeric', 'min:1'],
            'status' => ['required', Rule::in($this->statuses())],
            'change_reason' => ['nullable', 'string'],
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

    private function applyAutomaticAttendanceSchedule(array &$data): void
    {
        $attendanceTimes = $this->defaultAttendanceTimes(
            $data['activity_date'],
            $data['start_time'] ?? null,
            $data['end_time'] ?? null,
            app(SystemSettings::class)->attendanceDefaults()
        );

        $data['attendance_enabled'] = in_array($data['status'] ?? null, ['scheduled', 'relocated', 'completed'], true);
        $data['attendance_open_at'] = $attendanceTimes['open_at'] ?? null;
        $data['attendance_close_at'] = $attendanceTimes['close_at'] ?? null;
    }

    private function defaultAttendanceTimes(string $activityDate, ?string $startTime, ?string $endTime, array $attendanceDefaults): array
    {
        $times = [];
        $date = Carbon::parse($activityDate)->format('Y-m-d');

        if ($startTime) {
            $times['open_at'] = Carbon::createFromFormat('Y-m-d H:i', "{$date} {$startTime}")
                ->subMinutes($attendanceDefaults['open_minutes_before']);
        }

        if ($endTime) {
            $times['close_at'] = Carbon::createFromFormat('Y-m-d H:i', "{$date} {$endTime}");
        }

        return $times;
    }

    private function formOptions(): array
    {
        $agendaSchedules = AgendaSchedule::orderBy('title')->get();
        $departmentChairPics = $this->departmentChairPicMap();

        return [
            'agendaSchedules' => $agendaSchedules,
            'agendaScheduleDefaults' => $agendaSchedules->mapWithKeys(fn (AgendaSchedule $agendaSchedule) => [
                (string) $agendaSchedule->id => [
                    'department_id' => $agendaSchedule->department_id ? (string) $agendaSchedule->department_id : '',
                    'pic_id' => $agendaSchedule->pic_id ? (string) $agendaSchedule->pic_id : '',
                    'description' => $agendaSchedule->description ?: '',
                    'start_time' => $agendaSchedule->start_time ? substr($agendaSchedule->start_time, 0, 5) : '',
                    'end_time' => $agendaSchedule->end_time ? substr($agendaSchedule->end_time, 0, 5) : '',
                    'default_location' => $agendaSchedule->default_location ?: '',
                    'default_latitude' => $agendaSchedule->default_latitude !== null ? (string) $agendaSchedule->default_latitude : '',
                    'default_longitude' => $agendaSchedule->default_longitude !== null ? (string) $agendaSchedule->default_longitude : '',
                    'default_radius' => $agendaSchedule->default_radius ? (string) $agendaSchedule->default_radius : '',
                ],
            ])->all(),
            'departmentChairPics' => $departmentChairPics,
            'departments' => Department::where('status', 'active')->orderBy('name')->get(),
            'members' => Member::where('member_status', 'active')->orderBy('full_name')->get(),
            'statuses' => $this->statuses(),
            'attendanceDefaults' => app(SystemSettings::class)->attendanceDefaults(),
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

    private function departmentChairPicMap(): array
    {
        return Member::query()
            ->where('member_status', 'active')
            ->whereNotNull('department_id')
            ->whereHas('position', fn ($query) => $query->whereRaw('LOWER(name) = ?', ['ketua bidang']))
            ->orderBy('id')
            ->get(['id', 'department_id'])
            ->unique('department_id')
            ->mapWithKeys(fn (Member $member) => [(string) $member->department_id => (string) $member->id])
            ->all();
    }

    private function departmentChairPicId(?int $departmentId): ?int
    {
        if (! $departmentId) {
            return null;
        }

        $picId = $this->departmentChairPicMap()[(string) $departmentId] ?? null;

        return $picId ? (int) $picId : null;
    }
}
