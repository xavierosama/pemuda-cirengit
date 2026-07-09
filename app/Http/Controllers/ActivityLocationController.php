<?php

namespace App\Http\Controllers;

use App\Models\ActivityLocation;
use App\Support\TableControls;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ActivityLocationController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();
        $allowedSorts = [
            'name' => 'name',
            'radius_meters' => 'radius_meters',
            'is_active' => 'is_active',
            'created_at' => 'created_at',
        ];
        $currentSort = TableControls::sort($request, $allowedSorts);
        $currentDirection = TableControls::direction($request);
        $perPage = TableControls::perPage($request);

        $activityLocations = ActivityLocation::query()
            ->withCount('agendaSchedules')
            ->when($search, fn ($query) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('address', 'like', "%{$search}%")))
            ->when(in_array($status, ['active', 'inactive'], true), fn ($query) => $query->where('is_active', $status === 'active'))
            ->tap(fn ($query) => TableControls::applySort($query, $currentSort, $currentDirection, $allowedSorts, fn ($query) => $query->latest()))
            ->paginate($perPage)
            ->withQueryString();

        $locationStats = [
            'total' => ActivityLocation::count(),
            'active' => ActivityLocation::where('is_active', true)->count(),
            'inactive' => ActivityLocation::where('is_active', false)->count(),
        ];

        return view('activity-locations.index', array_merge(
            compact('activityLocations', 'locationStats', 'search', 'status'),
            TableControls::viewData($request, $currentSort, $currentDirection, $perPage)
        ));
    }

    public function create(): View
    {
        return view('activity-locations.create');
    }

    public function store(Request $request): RedirectResponse
    {
        ActivityLocation::create($this->validatedData($request));

        return redirect()
            ->route('activity-locations.index')
            ->with('success', 'Lokasi kegiatan berhasil ditambahkan.');
    }

    public function show(ActivityLocation $activityLocation): View
    {
        $activityLocation->loadCount('agendaSchedules');

        return view('activity-locations.show', compact('activityLocation'));
    }

    public function edit(ActivityLocation $activityLocation): View
    {
        return view('activity-locations.edit', compact('activityLocation'));
    }

    public function update(Request $request, ActivityLocation $activityLocation): RedirectResponse
    {
        $activityLocation->update($this->validatedData($request));

        return redirect()
            ->route('activity-locations.index')
            ->with('success', 'Lokasi kegiatan berhasil diperbarui.');
    }

    public function deactivate(ActivityLocation $activityLocation): RedirectResponse
    {
        $activityLocation->update(['is_active' => false]);

        return redirect()
            ->route('activity-locations.index')
            ->with('success', 'Lokasi kegiatan berhasil dinonaktifkan.');
    }

    private function validatedData(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'radius_meters' => ['required', 'integer', 'min:1'],
            'is_active' => ['required', Rule::in(['0', '1'])],
        ]);
    }
}
