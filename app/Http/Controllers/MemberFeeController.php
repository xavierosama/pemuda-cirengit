<?php

namespace App\Http\Controllers;

use App\Models\Member;
use App\Models\MemberFeeRecord;
use App\Models\MembershipFeeSetting;
use App\Services\MemberFeeService;
use App\Support\TableControls;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MemberFeeController extends Controller
{
    public function index(Request $request, MemberFeeService $feeService): View
    {
        abort_unless($request->user()?->canManageFinance(), 403);

        $year = $request->integer('year') ?: now()->year;
        $month = $request->integer('month') ?: now()->month;
        $status = $request->string('status')->toString();
        $search = $request->string('search')->toString();
        $perPage = TableControls::perPage($request);
        $hasFeeRecordsTable = Schema::hasTable('member_fee_records');

        $records = $hasFeeRecordsTable
            ? MemberFeeRecord::query()
                ->with(['member.department', 'member.position', 'recorder'])
                ->period($year, $month)
                ->when(in_array($status, ['paid', 'unpaid', 'waived'], true), fn ($query) => $query->where('status', $status))
                ->when($search, fn ($query) => $query->whereHas('member', function ($query) use ($search) {
                    $query->where('full_name', 'like', "%{$search}%")
                        ->orWhere('npa', 'like', "%{$search}%");
                }))
                ->join('members', 'members.id', '=', 'member_fee_records.member_id')
                ->orderBy('members.full_name')
                ->select('member_fee_records.*')
                ->paginate($perPage)
                ->withQueryString()
            : new LengthAwarePaginator([], 0, $perPage, $request->integer('page') ?: 1, [
                'path' => $request->url(),
                'query' => $request->query(),
            ]);

        $stats = [
            'total' => $hasFeeRecordsTable ? MemberFeeRecord::period($year, $month)->count() : 0,
            'paid' => $hasFeeRecordsTable ? MemberFeeRecord::period($year, $month)->where('status', 'paid')->count() : 0,
            'unpaid' => $hasFeeRecordsTable ? MemberFeeRecord::period($year, $month)->where('status', 'unpaid')->count() : 0,
            'waived' => $hasFeeRecordsTable ? MemberFeeRecord::period($year, $month)->where('status', 'waived')->count() : 0,
            'active_members' => Schema::hasTable('members') ? Member::where('member_status', 'active')->count() : 0,
            'default_amount' => $feeService->currentDefaultAmount(),
        ];

        return view('finance.member-fees.index', array_merge(
            compact('records', 'stats', 'year', 'month', 'status', 'search', 'hasFeeRecordsTable'),
            TableControls::viewData($request, null, 'asc', $perPage)
        ));
    }

    public function generate(Request $request, MemberFeeService $feeService): RedirectResponse
    {
        abort_unless($request->user()?->canManageFinance(), 403);

        if (! Schema::hasTable('member_fee_records')) {
            return back()->with('warning', 'Tabel member_fee_records belum tersedia. Jalankan php artisan migrate terlebih dahulu.');
        }

        $validated = $request->validate([
            'year' => ['required', 'integer', 'min:2020', 'max:2100'],
            'month' => ['required', 'integer', 'min:1', 'max:12'],
        ]);

        $result = $feeService->generate((int) $validated['year'], (int) $validated['month'], $request->user()->id);

        return redirect()
            ->route('finance.member-fees.index', ['year' => $validated['year'], 'month' => $validated['month']])
            ->with('success', "Generate iuran selesai. {$result['created']} dibuat, {$result['skipped']} dilewati.");
    }

    public function update(Request $request, MemberFeeRecord $record, MemberFeeService $feeService): RedirectResponse
    {
        abort_unless($request->user()?->canManageFinance(), 403);

        $validated = $request->validate([
            'amount' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::in(['unpaid', 'paid', 'waived'])],
            'paid_at' => ['nullable', 'date'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $feeService->updateStatus($record, $validated, $request->user()->id);

        return back()->with('success', 'Status iuran anggota berhasil diperbarui.');
    }

    public function updateSetting(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->canManageFinance(), 403);

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:120'],
            'default_amount' => ['required', 'integer', 'min:0'],
            'effective_from' => ['nullable', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
        ]);

        MembershipFeeSetting::query()->update(['is_active' => false]);
        MembershipFeeSetting::query()->create([
            'name' => $validated['name'] ?: 'Iuran Bulanan',
            'default_amount' => $validated['default_amount'],
            'effective_from' => $validated['effective_from'] ?? now()->startOfMonth()->toDateString(),
            'effective_to' => $validated['effective_to'] ?? null,
            'is_active' => true,
        ]);

        return back()->with('success', 'Nominal iuran default berhasil diperbarui.');
    }
}
