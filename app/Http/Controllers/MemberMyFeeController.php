<?php

namespace App\Http\Controllers;

use App\Models\MemberFeeRecord;
use App\Services\MemberFeeService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberMyFeeController extends Controller
{
    public function __invoke(Request $request, MemberFeeService $feeService): View
    {
        $user = $request->user()->load('member');
        $member = $user->member;

        abort_unless($member, 404, 'Data anggota belum terhubung.');

        $year = $request->integer('year') ?: now()->year;
        $month = now()->month;

        $records = MemberFeeRecord::query()
            ->where('member_id', $member->id)
            ->where('year', $year)
            ->orderBy('month')
            ->get()
            ->keyBy('month');

        $currentRecord = $records->get($month);

        $summary = [
            'paid' => $records->where('status', 'paid')->count(),
            'unpaid' => $records->where('status', 'unpaid')->count(),
            'waived' => $records->where('status', 'waived')->count(),
            'default_amount' => $feeService->amountForMember($member, now()->year, now()->month),
        ];

        return view('member.fees.index', compact('user', 'member', 'records', 'currentRecord', 'summary', 'year'));
    }
}
