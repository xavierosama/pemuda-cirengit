<?php

namespace App\Http\Controllers;

use App\Models\FinancialCategory;
use App\Models\FinancialTransaction;
use App\Support\TableControls;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FinancialTransactionController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->canManageFinance(), 403);

        return $this->listing($request);
    }

    public function income(Request $request): View
    {
        abort_unless($request->user()?->canManageFinance(), 403);

        $request->merge(['type' => 'income']);

        return $this->listing($request);
    }

    public function expenses(Request $request): View
    {
        abort_unless($request->user()?->canManageFinance(), 403);

        $request->merge(['type' => 'expense']);

        return $this->listing($request);
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()?->canManageFinance(), 403);

        $transaction = new FinancialTransaction([
            'type' => $request->string('type')->toString() ?: 'income',
            'transaction_date' => now(),
        ]);

        return view('finance.transactions.create', $this->formData($transaction));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->canManageFinance(), 403);

        $validated = $this->validatedData($request);
        $validated['created_by'] = $request->user()->id;

        FinancialTransaction::query()->create($validated);

        return redirect()->route('finance.transactions.index', ['type' => $validated['type']])
            ->with('success', 'Transaksi keuangan berhasil ditambahkan.');
    }

    public function edit(Request $request, FinancialTransaction $transaction): View
    {
        abort_unless($request->user()?->canManageFinance(), 403);

        return view('finance.transactions.edit', $this->formData($transaction));
    }

    public function update(Request $request, FinancialTransaction $transaction): RedirectResponse
    {
        abort_unless($request->user()?->canManageFinance(), 403);

        $validated = $this->validatedData($request);
        $transaction->update($validated);

        return redirect()->route('finance.transactions.index', ['type' => $validated['type']])
            ->with('success', 'Transaksi keuangan berhasil diperbarui.');
    }

    public function destroy(Request $request, FinancialTransaction $transaction): RedirectResponse
    {
        abort_unless($request->user()?->canManageFinance(), 403);

        $transaction->delete();

        return back()->with('success', 'Transaksi keuangan berhasil dihapus.');
    }

    private function listing(Request $request): View
    {
        $search = $request->string('search')->toString();
        $type = $request->string('type')->toString();
        $categoryId = $request->integer('category');
        $year = $request->integer('year') ?: now()->year;
        $month = $request->integer('month');
        $perPage = TableControls::perPage($request);

        $transactions = FinancialTransaction::query()
            ->with(['category', 'creator'])
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('reference_no', 'like', "%{$search}%");
            }))
            ->when(in_array($type, ['income', 'expense'], true), fn ($query) => $query->where('type', $type))
            ->when($categoryId, fn ($query) => $query->where('financial_category_id', $categoryId))
            ->whereYear('transaction_date', $year)
            ->when($month, fn ($query) => $query->whereMonth('transaction_date', $month))
            ->latest('transaction_date')
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $categories = FinancialCategory::query()
            ->when(in_array($type, ['income', 'expense'], true), fn ($query) => $query->where('type', $type))
            ->orderBy('name')
            ->get();

        return view('finance.transactions.index', array_merge(
            compact('transactions', 'categories', 'search', 'type', 'categoryId', 'year', 'month'),
            TableControls::viewData($request, null, 'desc', $perPage)
        ));
    }

    private function formData(FinancialTransaction $transaction): array
    {
        $categories = FinancialCategory::query()
            ->active()
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return compact('transaction', 'categories');
    }

    private function validatedData(Request $request): array
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['income', 'expense'])],
            'financial_category_id' => [
                'required',
                Rule::exists('financial_categories', 'id')->where(fn ($query) => $query->where('type', $request->input('type'))),
            ],
            'amount' => ['required', 'integer', 'min:1'],
            'transaction_date' => ['required', 'date'],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
            'payment_method' => ['nullable', Rule::in(['cash', 'transfer', 'other'])],
            'reference_no' => ['nullable', 'string', 'max:120'],
        ]);

        $validated['payment_method'] = $validated['payment_method'] ?: null;

        return $validated;
    }
}
