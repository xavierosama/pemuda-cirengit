<?php

namespace App\Services;

use App\Models\Member;
use App\Models\MemberFeeRecord;
use App\Models\MemberFeeSetting;
use App\Models\MembershipFeeSetting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class MemberFeeService
{
    public function __construct(private readonly FinanceNotificationService $notificationService)
    {
    }

    public function amountForMember(Member $member, int $year, int $month): int
    {
        $date = Carbon::create($year, $month, 1)->toDateString();

        $memberOverride = Schema::hasTable('member_fee_settings')
            ? MemberFeeSetting::query()
                ->where('member_id', $member->id)
                ->activeFor($date)
                ->latest('effective_from')
                ->first()
            : null;

        if ($memberOverride) {
            return (int) $memberOverride->amount;
        }

        return Schema::hasTable('membership_fee_settings')
            ? (int) (MembershipFeeSetting::query()
                ->activeFor($date)
                ->latest('effective_from')
                ->value('default_amount') ?? 0)
            : 0;
    }

    public function generate(int $year, int $month, int $recordedBy): array
    {
        $created = 0;
        $skipped = 0;
        $records = collect();

        Member::query()
            ->where('member_status', 'active')
            ->with('user')
            ->orderBy('full_name')
            ->chunkById(100, function (Collection $members) use ($year, $month, $recordedBy, &$created, &$skipped, $records) {
                foreach ($members as $member) {
                    if (MemberFeeRecord::query()->period($year, $month)->where('member_id', $member->id)->exists()) {
                        $skipped++;
                        continue;
                    }

                    $record = MemberFeeRecord::query()->create([
                        'member_id' => $member->id,
                        'year' => $year,
                        'month' => $month,
                        'amount' => $this->amountForMember($member, $year, $month),
                        'status' => 'unpaid',
                        'recorded_by' => $recordedBy,
                    ]);

                    $created++;
                    $records->push($record);
                }
            });

        $records->each(fn (MemberFeeRecord $record) => $this->notificationService->feeGenerated($record));

        return [
            'created' => $created,
            'skipped' => $skipped,
        ];
    }

    public function updateStatus(MemberFeeRecord $record, array $data, int $recordedBy): MemberFeeRecord
    {
        $previousStatus = $record->status;

        $record->fill([
            'amount' => $data['amount'],
            'status' => $data['status'],
            'paid_at' => $data['status'] === 'paid'
                ? ($data['paid_at'] ?? now()->toDateString())
                : null,
            'note' => $data['note'] ?? null,
            'recorded_by' => $recordedBy,
        ])->save();

        if ($record->status !== $previousStatus) {
            if ($record->status === 'paid') {
                $this->notificationService->feePaid($record);
            } elseif ($record->status === 'waived') {
                $this->notificationService->feeWaived($record);
            }
        }

        return $record->fresh(['member.user']);
    }

    public function currentDefaultAmount(): int
    {
        return Schema::hasTable('membership_fee_settings')
            ? (int) (MembershipFeeSetting::query()
                ->activeFor(now()->toDateString())
                ->latest('effective_from')
                ->value('default_amount') ?? 0)
            : 0;
    }
}
