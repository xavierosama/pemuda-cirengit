<?php

namespace App\Http\Controllers;

use App\Services\FinanceDashboardService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinanceDashboardController extends Controller
{
    public function __invoke(Request $request, FinanceDashboardService $dashboardService): View
    {
        abort_unless($request->user()?->canManageFinance(), 403, 'Hanya admin dan bendahara yang dapat mengakses modul keuangan.');

        $year = $request->integer('year') ?: now()->year;
        $month = $request->integer('month') ?: now()->month;

        $data = $dashboardService->data($year, $month);

        return view('finance.dashboard', array_merge($data, compact('year', 'month')));
    }
}
