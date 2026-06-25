<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Member;
use App\Models\Position;
use App\Support\DateFormatter;
use App\Support\TableControls;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MemberController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $departmentId = $request->integer('department_id') ?: null;
        $positionId = $request->integer('position_id') ?: null;
        $memberStatus = $request->string('member_status')->toString();
        $inactiveReason = $request->string('inactive_reason')->toString();
        $accountStatus = $request->string('account_status')->toString();
        $ageLimitDate = now()->subYears(41)->toDateString();
        $allowedSorts = [
            'npa' => 'npa',
            'full_name' => 'full_name',
            'email' => 'email',
            'member_status' => 'member_status',
            'created_at' => 'created_at',
        ];
        $currentSort = TableControls::sort($request, $allowedSorts);
        $currentDirection = TableControls::direction($request);
        $perPage = TableControls::perPage($request);

        $members = Member::query()
            ->with(['department', 'position', 'user'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('full_name', 'like', "%{$search}%")
                        ->orWhere('npa', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($departmentId, fn ($query) => $query->where('department_id', $departmentId))
            ->when($positionId, fn ($query) => $query->where('position_id', $positionId))
            ->when($memberStatus === 'active', fn ($query) => $query->where('member_status', 'active'))
            ->when($memberStatus === 'inactive', fn ($query) => $query->where('member_status', '!=', 'active'))
            ->when($memberStatus === 'age_limit_due', fn ($query) => $query
                ->where('member_status', 'active')
                ->whereNotNull('birth_date')
                ->whereDate('birth_date', '<=', $ageLimitDate))
            ->when(
                in_array($inactiveReason, array_keys(Member::INACTIVE_REASONS), true),
                fn ($query) => $query->where('inactive_reason', $inactiveReason)
            )
            ->when($accountStatus === 'exists', fn ($query) => $query->whereHas('user'))
            ->when($accountStatus === 'missing', fn ($query) => $query->whereDoesntHave('user'))
            ->tap(fn ($query) => TableControls::applySort($query, $currentSort, $currentDirection, $allowedSorts, fn ($query) => $query->latest()))
            ->paginate($perPage)
            ->withQueryString();

        $departments = Department::orderBy('name')->get(['id', 'name']);
        $positions = Position::orderBy('name')->get(['id', 'name']);
        $inactiveReasons = Member::INACTIVE_REASONS;
        $memberStats = [
            'total' => Member::count(),
            'active' => Member::where('member_status', 'active')->count(),
            'inactive' => Member::where('member_status', '!=', 'active')->count(),
            'age_limit_due' => Member::where('member_status', 'active')
                ->whereNotNull('birth_date')
                ->whereDate('birth_date', '<=', $ageLimitDate)
                ->count(),
            'account_exists' => Member::whereHas('user')->count(),
            'account_missing' => Member::whereDoesntHave('user')->count(),
        ];

        return view('members.index', array_merge(compact(
            'members',
            'departments',
            'positions',
            'inactiveReasons',
            'memberStats',
            'search',
            'departmentId',
            'positionId',
            'memberStatus',
            'inactiveReason',
            'accountStatus'
        ), TableControls::viewData($request, $currentSort, $currentDirection, $perPage)));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('members.create', [
            'departments' => Department::where('status', 'active')->orderBy('name')->get(),
            'positions' => Position::where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        Member::create($this->validatedData($request));

        return redirect()
            ->route('members.index')
            ->with('success', 'Data anggota berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Member $member): View
    {
        $member->load(['department', 'position', 'user']);

        return view('members.show', compact('member'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Member $member): View
    {
        return view('members.edit', [
            'member' => $member,
            'departments' => Department::orderBy('name')->get(),
            'positions' => Position::orderBy('name')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Member $member): RedirectResponse
    {
        $member->update($this->validatedData($request));

        return redirect()
            ->route('members.index')
            ->with('success', 'Data anggota berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Member $member): RedirectResponse
    {
        $member->delete();

        return redirect()
            ->route('members.index')
            ->with('success', 'Data anggota berhasil dihapus.');
    }

    public function markInactiveBecauseAgeLimit(Member $member): RedirectResponse
    {
        if (! $member->needsAgeLimitProcessing()) {
            return back()->with('info', 'Anggota ini belum memenuhi kriteria batas usia untuk diproses.');
        }

        $member->update([
            'member_status' => 'inactive',
            'inactive_reason' => 'age_limit',
            'inactive_at' => now()->toDateString(),
            'status_notes' => 'Tidak aktif karena telah mencapai batas usia anggota Pemuda.',
        ]);

        return back()->with('success', 'Anggota berhasil ditandai tidak aktif karena batas usia.');
    }

    private function validatedData(Request $request): array
    {
        $member = $request->route('member');
        $request->merge([
            'joined_at' => DateFormatter::normalizeInputDateForValidation($request->input('joined_at')),
            'birth_date' => DateFormatter::normalizeInputDateForValidation($request->input('birth_date')),
            'inactive_at' => DateFormatter::normalizeInputDateForValidation($request->input('inactive_at')),
        ]);

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'npa' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('members', 'npa')->ignore($member),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'joined_at' => ['nullable', 'date'],
            'birth_date' => ['nullable', 'date'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'position_id' => ['nullable', 'exists:positions,id'],
            'member_status' => ['required', Rule::in(['active', 'inactive'])],
            'inactive_reason' => ['nullable', Rule::in(array_keys(Member::INACTIVE_REASONS))],
            'inactive_at' => ['nullable', 'date'],
            'status_notes' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string'],
        ], [
            'npa.unique' => 'NPA sudah digunakan oleh anggota lain.',
        ]);

        if ($validated['member_status'] === 'active') {
            $validated['inactive_reason'] = null;
            $validated['inactive_at'] = null;
            $validated['status_notes'] = null;
        }

        return $validated;
    }
}
