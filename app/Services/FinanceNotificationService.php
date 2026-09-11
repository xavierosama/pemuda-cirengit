<?php

namespace App\Services;

use App\Models\MemberFeeRecord;
use App\Models\Notification;
use App\Models\NotificationPreference;
use Illuminate\Support\Facades\Log;
use Throwable;

class FinanceNotificationService
{
    public function __construct(private readonly PushNotificationService $pushNotificationService)
    {
    }

    public function feeGenerated(MemberFeeRecord $record): void
    {
        $this->sendFeeNotification(
            $record,
            'member_fee_generated',
            'Iuran bulanan tercatat',
            'Iuran '.$this->periodLabel($record).' sudah tercatat dan menunggu pembayaran.',
            false,
        );
    }

    public function feePaid(MemberFeeRecord $record): void
    {
        $this->sendFeeNotification(
            $record,
            'member_fee_paid',
            'Iuran tercatat lunas',
            'Iuran '.$this->periodLabel($record).' sudah tercatat lunas oleh bendahara.',
            true,
        );
    }

    public function feeWaived(MemberFeeRecord $record): void
    {
        $this->sendFeeNotification(
            $record,
            'member_fee_waived',
            'Iuran dibebaskan',
            'Iuran '.$this->periodLabel($record).' tercatat dibebaskan oleh bendahara.',
            false,
        );
    }

    private function sendFeeNotification(MemberFeeRecord $record, string $type, string $title, string $message, bool $push): void
    {
        try {
            $record->loadMissing('member.user');
            $user = $record->member?->user;

            if (! $user) {
                return;
            }

            $category = 'admin_announcement';
            $url = route('member.fees.index', absolute: false);

            if (NotificationPreference::allowsInApp($user, $category)) {
                Notification::query()->updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'dedupe_key' => "{$type}:member_fee_record:{$record->id}",
                    ],
                    [
                        'member_id' => $record->member_id,
                        'type' => $type,
                        'title' => $title,
                        'message' => $message,
                        'url' => $url,
                        'related_type' => 'member_fee_record',
                        'related_id' => $record->id,
                        'data' => [
                            'source' => 'finance',
                            'year' => $record->year,
                            'month' => $record->month,
                            'status' => $record->status,
                        ],
                    ],
                );
            }

            if ($push && NotificationPreference::allowsPush($user, $category)) {
                $this->pushNotificationService->sendToMemberUser($user, $title, $message, $url, $type);
            }
        } catch (Throwable $exception) {
            Log::warning('Finance notification skipped.', [
                'record_id' => $record->id,
                'type' => $type,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function periodLabel(MemberFeeRecord $record): string
    {
        return now()->setDate($record->year, $record->month, 1)->translatedFormat('F Y');
    }
}
