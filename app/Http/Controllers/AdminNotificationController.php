<?php

namespace App\Http\Controllers;

use App\Models\AdminNotification;
use App\Models\Department;
use App\Models\Member;
use App\Models\Position;
use App\Services\AdminNotificationDispatchService;
use App\Support\TableControls;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminNotificationController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();
        $type = $request->string('type')->toString();
        $channel = $request->string('channel')->toString();
        $allowedSorts = [
            'title' => 'title',
            'status' => 'status',
            'type' => 'type',
            'channel' => 'channel',
            'sent_at' => 'sent_at',
            'created_at' => 'created_at',
        ];
        $currentSort = TableControls::sort($request, $allowedSorts);
        $currentDirection = TableControls::direction($request);
        $perPage = TableControls::perPage($request);

        $adminNotifications = AdminNotification::query()
            ->with('creator')
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            }))
            ->when(array_key_exists($status, AdminNotification::STATUSES), fn ($query) => $query->where('status', $status))
            ->when(array_key_exists($type, AdminNotification::TYPES), fn ($query) => $query->where('type', $type))
            ->when(array_key_exists($channel, AdminNotification::CHANNELS), fn ($query) => $query->where('channel', $channel))
            ->tap(fn ($query) => TableControls::applySort($query, $currentSort, $currentDirection, $allowedSorts, fn ($query) => $query->latest()))
            ->paginate($perPage)
            ->withQueryString();

        $stats = [
            'total' => AdminNotification::count(),
            'draft' => AdminNotification::where('status', 'draft')->count(),
            'sent' => AdminNotification::where('status', 'sent')->count(),
        ];

        return view('admin-notifications.index', array_merge(
            compact('adminNotifications', 'stats', 'search', 'status', 'type', 'channel'),
            TableControls::viewData($request, $currentSort, $currentDirection, $perPage)
        ));
    }

    public function create(): View
    {
        return view('admin-notifications.create', $this->formData());
    }

    public function store(Request $request, AdminNotificationDispatchService $dispatchService): RedirectResponse
    {
        $validated = $this->validateNotification($request);

        $adminNotification = AdminNotification::create([
            ...$this->notificationAttributes($validated),
            'target_payload' => $this->targetPayload($validated),
            'status' => 'draft',
            'created_by' => $request->user()->id,
        ]);

        if ($request->input('submit_action') === 'send') {
            $result = $dispatchService->dispatch($adminNotification);

            return redirect()
                ->route('admin-notifications.show', $adminNotification)
                ->with('success', "Notifikasi terkirim ke {$result['delivered_count']} anggota.");
        }

        return redirect()
            ->route('admin-notifications.show', $adminNotification)
            ->with('success', 'Notifikasi berhasil disimpan sebagai draft.');
    }

    public function show(AdminNotification $adminNotification): View
    {
        $personalNotificationCount = \App\Models\Notification::query()
            ->where('dedupe_key', 'like', "admin_notification:{$adminNotification->id}:%")
            ->count();

        return view('admin-notifications.show', compact('adminNotification', 'personalNotificationCount'));
    }

    public function edit(AdminNotification $adminNotification): View|RedirectResponse
    {
        if (! $adminNotification->isDraft()) {
            return redirect()
                ->route('admin-notifications.show', $adminNotification)
                ->with('warning', 'Notifikasi yang sudah terkirim tidak bisa diedit.');
        }

        return view('admin-notifications.edit', array_merge(
            ['adminNotification' => $adminNotification],
            $this->formData()
        ));
    }

    public function update(Request $request, AdminNotification $adminNotification, AdminNotificationDispatchService $dispatchService): RedirectResponse
    {
        if (! $adminNotification->isDraft()) {
            return redirect()
                ->route('admin-notifications.show', $adminNotification)
                ->with('warning', 'Notifikasi yang sudah terkirim tidak bisa diedit.');
        }

        $validated = $this->validateNotification($request);

        $adminNotification->update([
            ...$this->notificationAttributes($validated),
            'target_payload' => $this->targetPayload($validated),
        ]);

        if ($request->input('submit_action') === 'send') {
            $result = $dispatchService->dispatch($adminNotification);

            return redirect()
                ->route('admin-notifications.show', $adminNotification)
                ->with('success', "Notifikasi terkirim ke {$result['delivered_count']} anggota.");
        }

        return redirect()
            ->route('admin-notifications.show', $adminNotification)
            ->with('success', 'Draft notifikasi berhasil diperbarui.');
    }

    public function send(AdminNotification $adminNotification, AdminNotificationDispatchService $dispatchService): RedirectResponse
    {
        if (! $adminNotification->isDraft()) {
            return redirect()
                ->route('admin-notifications.show', $adminNotification)
                ->with('warning', 'Notifikasi ini sudah pernah dikirim.');
        }

        $result = $dispatchService->dispatch($adminNotification);

        return redirect()
            ->route('admin-notifications.show', $adminNotification)
            ->with('success', "Notifikasi terkirim ke {$result['delivered_count']} anggota.");
    }

    private function validateNotification(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'message' => ['required', 'string', 'max:2000'],
            'type' => ['required', Rule::in(array_keys(AdminNotification::TYPES))],
            'target_type' => ['required', Rule::in(array_keys(AdminNotification::TARGET_TYPES))],
            'department_ids' => ['nullable', 'array'],
            'department_ids.*' => ['integer', 'exists:departments,id'],
            'position_ids' => ['nullable', 'array'],
            'position_ids.*' => ['integer', 'exists:positions,id'],
            'member_ids' => ['nullable', 'array'],
            'member_ids.*' => ['integer', 'exists:members,id'],
            'channel' => ['required', Rule::in(array_keys(AdminNotification::CHANNELS))],
            'url' => ['nullable', 'string', 'max:500', 'starts_with:/'],
            'submit_action' => ['nullable', Rule::in(['draft', 'send'])],
        ]);

        if (($validated['url'] ?? null) && str_starts_with($validated['url'], '//')) {
            throw ValidationException::withMessages([
                'url' => 'URL harus berupa path internal aplikasi.',
            ]);
        }

        $request->validate([
            'department_ids' => [Rule::requiredIf($validated['target_type'] === 'by_bidang'), 'array', 'min:1'],
            'position_ids' => [Rule::requiredIf($validated['target_type'] === 'by_jabatan'), 'array', 'min:1'],
            'member_ids' => [Rule::requiredIf($validated['target_type'] === 'selected_members'), 'array', 'min:1'],
        ]);

        return $validated;
    }

    private function notificationAttributes(array $validated): array
    {
        return [
            'title' => $validated['title'],
            'message' => $validated['message'],
            'type' => $validated['type'],
            'target_type' => $validated['target_type'],
            'channel' => $validated['channel'],
            'url' => $validated['url'] ?? null,
        ];
    }

    private function targetPayload(array $validated): array
    {
        return match ($validated['target_type']) {
            'by_bidang' => ['department_ids' => array_values($validated['department_ids'] ?? [])],
            'by_jabatan' => ['position_ids' => array_values($validated['position_ids'] ?? [])],
            'selected_members' => ['member_ids' => array_values($validated['member_ids'] ?? [])],
            default => [],
        };
    }

    private function formData(): array
    {
        return [
            'types' => AdminNotification::TYPES,
            'targetTypes' => AdminNotification::TARGET_TYPES,
            'channels' => AdminNotification::CHANNELS,
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'positions' => Position::orderBy('name')->get(['id', 'name']),
            'members' => Member::query()
                ->where('member_status', 'active')
                ->orderBy('full_name')
                ->get(['id', 'full_name', 'npa']),
        ];
    }
}
