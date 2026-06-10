<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Member;
use App\Models\Position;
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
            ->when(
                in_array($memberStatus, ['active', 'inactive', 'alumni', 'moved'], true),
                fn ($query) => $query->where('member_status', $memberStatus)
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $departments = Department::orderBy('name')->get(['id', 'name']);
        $positions = Position::orderBy('name')->get(['id', 'name']);

        return view('members.index', compact(
            'members',
            'departments',
            'positions',
            'search',
            'departmentId',
            'positionId',
            'memberStatus'
        ));
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

    private function validatedData(Request $request): array
    {
        $member = $request->route('member');

        return $request->validate([
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
            'department_id' => ['nullable', 'exists:departments,id'],
            'position_id' => ['nullable', 'exists:positions,id'],
            'member_status' => ['required', Rule::in(['active', 'inactive', 'alumni', 'moved'])],
            'notes' => ['nullable', 'string'],
        ], [
            'npa.unique' => 'NPA sudah digunakan oleh anggota lain.',
        ]);
    }
}
