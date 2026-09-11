<?php

namespace App\Http\Controllers;

use App\Models\FinancialCategory;
use App\Support\TableControls;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FinancialCategoryController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->canManageFinance(), 403);

        $search = $request->string('search')->toString();
        $type = $request->string('type')->toString();
        $status = $request->string('status')->toString();
        $perPage = TableControls::perPage($request);

        $categories = FinancialCategory::query()
            ->withCount('transactions')
            ->when($search, fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->when(in_array($type, ['income', 'expense'], true), fn ($query) => $query->where('type', $type))
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderByRaw('sort_order is null')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return view('finance.categories.index', array_merge(
            compact('categories', 'search', 'type', 'status'),
            TableControls::viewData($request, null, 'asc', $perPage)
        ));
    }

    public function create(Request $request): View
    {
        abort_unless($request->user()?->canManageFinance(), 403);

        return view('finance.categories.create', ['category' => new FinancialCategory()]);
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->canManageFinance(), 403);

        FinancialCategory::query()->create($this->validatedData($request));

        return redirect()->route('finance.categories.index')->with('success', 'Kategori keuangan berhasil ditambahkan.');
    }

    public function edit(Request $request, FinancialCategory $category): View
    {
        abort_unless($request->user()?->canManageFinance(), 403);

        return view('finance.categories.edit', compact('category'));
    }

    public function update(Request $request, FinancialCategory $category): RedirectResponse
    {
        abort_unless($request->user()?->canManageFinance(), 403);

        $category->update($this->validatedData($request, $category));

        return redirect()->route('finance.categories.index')->with('success', 'Kategori keuangan berhasil diperbarui.');
    }

    public function destroy(Request $request, FinancialCategory $category): RedirectResponse
    {
        abort_unless($request->user()?->canManageFinance(), 403);

        if ($category->transactions()->exists()) {
            $category->update(['is_active' => false]);

            return back()->with('success', 'Kategori sudah dipakai transaksi, sehingga dinonaktifkan.');
        }

        $category->delete();

        return back()->with('success', 'Kategori keuangan berhasil dihapus.');
    }

    private function validatedData(Request $request, ?FinancialCategory $category = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:160', Rule::unique('financial_categories', 'slug')->ignore($category)],
            'type' => ['required', Rule::in(['income', 'expense'])],
            'description' => ['nullable', 'string'],
            'color' => ['nullable', 'string', 'max:50'],
            'icon' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['slug'] = filled($validated['slug'] ?? null)
            ? Str::slug($validated['slug'])
            : Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active', true);

        return $validated;
    }
}
