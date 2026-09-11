<?php

namespace App\Services;

use App\Models\FinancialTransaction;
use App\Models\Member;
use App\Models\MemberFeeRecord;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FinanceDashboardService
{
    public function data(int $year, int $month): array
    {
        $period = Carbon::create($year, $month, 1);
        $monthStart = $period->copy()->startOfMonth();
        $monthEnd = $period->copy()->endOfMonth();
        $hasTransactionsTable = Schema::hasTable('financial_transactions');
        $hasMemberFeeRecordsTable = Schema::hasTable('member_fee_records');
        $hasMembersTable = Schema::hasTable('members');

        $totalIncome = $hasTransactionsTable ? FinancialTransaction::query()->type('income')->sum('amount') : 0;
        $totalExpense = $hasTransactionsTable ? FinancialTransaction::query()->type('expense')->sum('amount') : 0;
        $monthIncome = $hasTransactionsTable ? $this->transactionsBetween('income', $monthStart, $monthEnd)->sum('amount') : 0;
        $monthExpense = $hasTransactionsTable ? $this->transactionsBetween('expense', $monthStart, $monthEnd)->sum('amount') : 0;
        $activeMembers = $hasMembersTable ? Member::where('member_status', 'active')->count() : 0;

        $feeCounts = $hasMemberFeeRecordsTable
            ? MemberFeeRecord::query()
                ->period($year, $month)
                ->select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status')
            : collect();

        $paidCount = (int) ($feeCounts['paid'] ?? 0);
        $unpaidCount = (int) ($feeCounts['unpaid'] ?? 0);
        $waivedCount = (int) ($feeCounts['waived'] ?? 0);
        $feeRecordCount = $paidCount + $unpaidCount + $waivedCount;
        $compliance = $feeRecordCount > 0 ? round(($paidCount / $feeRecordCount) * 100, 1) : 0;

        $feeIncome = $hasMemberFeeRecordsTable
            ? MemberFeeRecord::query()
                ->period($year, $month)
                ->where('status', 'paid')
                ->sum('amount')
            : 0;
        $topExpenseCategories = $this->topExpenseCategories($monthStart, $monthEnd, $hasTransactionsTable);
        $latestTransactions = $hasTransactionsTable
            ? FinancialTransaction::query()
                ->with('category')
                ->latest('transaction_date')
                ->latest()
                ->limit(5)
                ->get()
            : collect();

        return [
            'period' => $period,
            'summary' => [
                'balance' => $totalIncome - $totalExpense,
                'month_income' => $monthIncome,
                'month_expense' => $monthExpense,
                'month_surplus' => $monthIncome - $monthExpense,
                'fee_income' => $feeIncome,
                'paid_count' => $paidCount,
                'unpaid_count' => $unpaidCount,
                'waived_count' => $waivedCount,
                'active_members' => $activeMembers,
                'compliance' => $compliance,
            ],
            'charts' => [
                'monthlyTrend' => $this->monthlyTrend($year, $hasTransactionsTable),
                'incomeCategories' => $this->categoryComposition('income', $monthStart, $monthEnd, $hasTransactionsTable),
                'expenseCategories' => $this->categoryComposition('expense', $monthStart, $monthEnd, $hasTransactionsTable),
                'feeStatus' => [
                    'labels' => ['Lunas', 'Belum Bayar', 'Dibebaskan'],
                    'data' => [$paidCount, $unpaidCount, $waivedCount],
                ],
                'feeTrend' => $this->feeTrend($year, $hasMemberFeeRecordsTable),
            ],
            'topExpenseCategories' => $topExpenseCategories,
            'latestTransactions' => $latestTransactions,
            'insights' => $this->insights(
                $monthIncome,
                $monthExpense,
                $unpaidCount,
                $compliance,
                $topExpenseCategories,
                $hasTransactionsTable,
                $hasMemberFeeRecordsTable,
            ),
        ];
    }

    private function transactionsBetween(string $type, Carbon $start, Carbon $end)
    {
        return FinancialTransaction::query()
            ->type($type)
            ->whereBetween('transaction_date', [$start->toDateString(), $end->toDateString()]);
    }

    private function monthlyTrend(int $year, bool $hasTransactionsTable): array
    {
        $labels = [];
        $income = [];
        $expense = [];

        for ($month = 1; $month <= 12; $month++) {
            $date = Carbon::create($year, $month, 1);
            $labels[] = $date->translatedFormat('M');
            if (! $hasTransactionsTable) {
                $income[] = 0;
                $expense[] = 0;

                continue;
            }

            $income[] = (int) FinancialTransaction::query()->type('income')
                ->whereYear('transaction_date', $year)
                ->whereMonth('transaction_date', $month)
                ->sum('amount');
            $expense[] = (int) FinancialTransaction::query()->type('expense')
                ->whereYear('transaction_date', $year)
                ->whereMonth('transaction_date', $month)
                ->sum('amount');
        }

        return compact('labels', 'income', 'expense');
    }

    private function categoryComposition(string $type, Carbon $start, Carbon $end, bool $hasTransactionsTable): array
    {
        if (! $hasTransactionsTable) {
            return [
                'labels' => [],
                'data' => [],
            ];
        }

        $rows = FinancialTransaction::query()
            ->with('category')
            ->type($type)
            ->whereBetween('transaction_date', [$start->toDateString(), $end->toDateString()])
            ->select('financial_category_id', DB::raw('sum(amount) as total'))
            ->groupBy('financial_category_id')
            ->get();

        return [
            'labels' => $rows->map(fn ($row) => $row->category?->name ?? 'Tanpa Kategori')->values(),
            'data' => $rows->map(fn ($row) => (int) $row->total)->values(),
        ];
    }

    private function topExpenseCategories(Carbon $start, Carbon $end, bool $hasTransactionsTable): Collection
    {
        if (! $hasTransactionsTable) {
            return collect();
        }

        return FinancialTransaction::query()
            ->with('category')
            ->type('expense')
            ->whereBetween('transaction_date', [$start->toDateString(), $end->toDateString()])
            ->select('financial_category_id', DB::raw('sum(amount) as total'))
            ->groupBy('financial_category_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();
    }

    private function feeTrend(int $year, bool $hasMemberFeeRecordsTable): array
    {
        $labels = [];
        $paid = [];
        $total = [];

        for ($month = 1; $month <= 12; $month++) {
            $date = Carbon::create($year, $month, 1);
            $labels[] = $date->translatedFormat('M');
            if (! $hasMemberFeeRecordsTable) {
                $paid[] = 0;
                $total[] = 0;

                continue;
            }

            $paid[] = (int) MemberFeeRecord::query()->period($year, $month)->where('status', 'paid')->count();
            $total[] = (int) MemberFeeRecord::query()->period($year, $month)->count();
        }

        return compact('labels', 'paid', 'total');
    }

    private function insights(
        int $income,
        int $expense,
        int $unpaidCount,
        float $compliance,
        Collection $topExpenseCategories,
        bool $hasTransactionsTable,
        bool $hasMemberFeeRecordsTable,
    ): array
    {
        $insights = [];
        $surplus = $income - $expense;

        if (! $hasTransactionsTable || ! $hasMemberFeeRecordsTable) {
            $missingTables = collect([
                'financial_transactions' => $hasTransactionsTable,
                'member_fee_records' => $hasMemberFeeRecordsTable,
            ])
                ->filter(fn (bool $exists) => ! $exists)
                ->keys()
                ->implode(', ');

            $insights[] = [
                'tone' => 'amber',
                'title' => 'Migration finance belum lengkap',
                'message' => 'Jalankan php artisan migrate agar tabel '.$missingTables.' tersedia.',
            ];
        }

        $insights[] = [
            'tone' => $surplus >= 0 ? 'emerald' : 'red',
            'title' => $surplus >= 0 ? 'Kas bulan ini surplus' : 'Kas bulan ini defisit',
            'message' => 'Selisih bulan ini Rp '.number_format(abs($surplus), 0, ',', '.').'.',
        ];

        $insights[] = [
            'tone' => $unpaidCount > 0 ? 'amber' : 'emerald',
            'title' => $unpaidCount > 0 ? "{$unpaidCount} anggota belum bayar" : 'Iuran bulan ini aman',
            'message' => 'Kepatuhan iuran bulan ini '.$compliance.'%.',
        ];

        $topExpense = $topExpenseCategories->first();
        if ($topExpense) {
            $insights[] = [
                'tone' => 'slate',
                'title' => 'Pengeluaran terbesar',
                'message' => ($topExpense->category?->name ?? 'Tanpa Kategori').' menjadi kategori pengeluaran terbesar bulan ini.',
            ];
        } elseif ($income === 0 && $expense === 0) {
            $insights[] = [
                'tone' => 'blue',
                'title' => 'Belum ada transaksi bulan ini',
                'message' => 'Tambahkan pemasukan atau pengeluaran agar dashboard mulai terbaca.',
            ];
        }

        return $insights;
    }
}
