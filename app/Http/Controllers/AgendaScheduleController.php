<?php

namespace App\Http\Controllers;

use App\Models\AgendaSchedule;
use App\Models\Department;
use App\Models\Member;
use App\Support\TableControls;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AgendaScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $departmentId = $request->integer('department_id') ?: null;
        $scheduleType = $request->string('schedule_type')->toString();
        $activeStatus = $request->string('is_active')->toString();
        $allowedSorts = [
            'title' => 'title',
            'schedule_type' => 'schedule_type',
            'start_time' => 'start_time',
            'is_active' => 'is_active',
            'created_at' => 'created_at',
        ];
        $currentSort = TableControls::sort($request, $allowedSorts);
        $currentDirection = TableControls::direction($request);
        $perPage = TableControls::perPage($request);

        $agendaSchedules = AgendaSchedule::query()
            ->with(['department', 'pic'])
            ->when($search, fn ($query) => $query->where('title', 'like', "%{$search}%"))
            ->when($departmentId, fn ($query) => $query->where('department_id', $departmentId))
            ->when(
                in_array($scheduleType, ['once', 'daily', 'weekly', 'monthly'], true),
                fn ($query) => $query->where('schedule_type', $scheduleType)
            )
            ->when(
                in_array($activeStatus, ['0', '1'], true),
                fn ($query) => $query->where('is_active', $activeStatus === '1')
            )
            ->tap(fn ($query) => TableControls::applySort($query, $currentSort, $currentDirection, $allowedSorts, fn ($query) => $query->latest()))
            ->paginate($perPage)
            ->withQueryString();

        $departments = Department::orderBy('name')->get(['id', 'name']);
        $agendaStats = [
            'active' => AgendaSchedule::where('is_active', true)->count(),
            'inactive' => AgendaSchedule::where('is_active', false)->count(),
            'weekly' => AgendaSchedule::where('schedule_type', 'weekly')->count(),
            'monthly' => AgendaSchedule::where('schedule_type', 'monthly')->count(),
        ];

        return view('agenda-schedules.index', array_merge(compact(
            'agendaSchedules',
            'departments',
            'agendaStats',
            'search',
            'departmentId',
            'scheduleType',
            'activeStatus'
        ), TableControls::viewData($request, $currentSort, $currentDirection, $perPage)));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('agenda-schedules.create', $this->formOptions());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['created_by'] = $request->user()->id;

        AgendaSchedule::create($data);

        return redirect()
            ->route('agenda-schedules.index')
            ->with('success', 'Jadwal agenda berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(AgendaSchedule $agendaSchedule): View
    {
        $agendaSchedule->load(['department', 'pic', 'creator']);

        return view('agenda-schedules.show', compact('agendaSchedule'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AgendaSchedule $agendaSchedule): View
    {
        return view('agenda-schedules.edit', array_merge(
            ['agendaSchedule' => $agendaSchedule],
            $this->formOptions()
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AgendaSchedule $agendaSchedule): RedirectResponse
    {
        $agendaSchedule->update($this->validatedData($request));

        return redirect()
            ->route('agenda-schedules.index')
            ->with('success', 'Jadwal agenda berhasil diperbarui.');
    }

    public function deactivate(AgendaSchedule $agendaSchedule): RedirectResponse
    {
        $agendaSchedule->update(['is_active' => false]);

        return redirect()
            ->route('agenda-schedules.index')
            ->with('success', 'Jadwal agenda berhasil dinonaktifkan.');
    }

    private function formOptions(): array
    {
        return [
            'departments' => Department::where('status', 'active')->orderBy('name')->get(),
            'members' => Member::where('member_status', 'active')->orderBy('full_name')->get(),
        ];
    }

    private function validatedData(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'pic_id' => ['nullable', 'exists:members,id'],
            'schedule_type' => ['required', Rule::in(['once', 'daily', 'weekly', 'monthly'])],
            'day_of_week' => ['nullable', 'required_if:schedule_type,weekly', 'integer', 'between:0,6'],
            'day_of_month' => ['nullable', 'required_if:schedule_type,monthly', 'integer', 'between:1,31'],
            'specific_date' => ['nullable', 'required_if:schedule_type,once', 'date'],
            'start_time' => ['nullable', 'regex:/^([01][0-9]|2[0-3]):[0-5][0-9]$/'],
            'end_time' => ['nullable', 'regex:/^([01][0-9]|2[0-3]):[0-5][0-9]$/'],
            'default_location' => ['nullable', 'string', 'max:255'],
            'default_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'default_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'default_radius' => ['required', 'numeric', 'min:1'],
            'is_active' => ['required', 'boolean'],
        ], [
            'start_time.regex' => 'Waktu mulai harus menggunakan format 24 jam HH:mm, contoh 20:00.',
            'end_time.regex' => 'Waktu selesai harus menggunakan format 24 jam HH:mm, contoh 20:00.',
        ]);

        if ($validated['schedule_type'] !== 'weekly') {
            $validated['day_of_week'] = null;
        }

        if ($validated['schedule_type'] !== 'monthly') {
            $validated['day_of_month'] = null;
        }

        if ($validated['schedule_type'] !== 'once') {
            $validated['specific_date'] = null;
        }

        return $validated;
    }
}
