<?php

namespace App\Http\Controllers;

use App\Models\ArticleCategory;
use App\Support\TableControls;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ArticleCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeArticleManagement($request);

        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();
        $allowedSorts = [
            'name' => 'name',
            'sort_order' => 'sort_order',
            'is_active' => 'is_active',
            'created_at' => 'created_at',
        ];
        $currentSort = TableControls::sort($request, $allowedSorts);
        $currentDirection = TableControls::direction($request);
        $perPage = TableControls::perPage($request);

        $categories = ArticleCategory::query()
            ->withCount('articles')
            ->when($search, fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->tap(fn ($query) => TableControls::applySort(
                $query,
                $currentSort,
                $currentDirection,
                $allowedSorts,
                fn ($query) => $query->orderByRaw('sort_order is null')->orderBy('sort_order')->orderBy('name')
            ))
            ->paginate($perPage)
            ->withQueryString();

        $categoryStats = [
            'total' => ArticleCategory::count(),
            'active' => ArticleCategory::where('is_active', true)->count(),
            'inactive' => ArticleCategory::where('is_active', false)->count(),
        ];

        return view('article-categories.index', array_merge(
            compact('categories', 'categoryStats', 'search', 'status'),
            TableControls::viewData($request, $currentSort, $currentDirection, $perPage)
        ));
    }

    public function create(Request $request): View
    {
        $this->authorizeArticleManagement($request);

        return view('article-categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeArticleManagement($request);

        $validated = $this->validatedData($request);
        $validated['slug'] = $this->uniqueSlug(($validated['slug'] ?? null) ?: $validated['name']);

        ArticleCategory::create($validated);

        return redirect()->route('article-categories.index')->with('success', 'Kategori kajian berhasil ditambahkan.');
    }

    public function show(Request $request, ArticleCategory $articleCategory): View
    {
        $this->authorizeArticleManagement($request);

        $articleCategory->loadCount('articles');

        return view('article-categories.show', compact('articleCategory'));
    }

    public function edit(Request $request, ArticleCategory $articleCategory): View
    {
        $this->authorizeArticleManagement($request);

        return view('article-categories.edit', compact('articleCategory'));
    }

    public function update(Request $request, ArticleCategory $articleCategory): RedirectResponse
    {
        $this->authorizeArticleManagement($request);

        $validated = $this->validatedData($request, $articleCategory);
        $validated['slug'] = $this->uniqueSlug(($validated['slug'] ?? null) ?: $validated['name'], $articleCategory);

        $articleCategory->update($validated);

        return redirect()->route('article-categories.index')->with('success', 'Kategori kajian berhasil diperbarui.');
    }

    public function destroy(Request $request, ArticleCategory $articleCategory): RedirectResponse
    {
        $this->authorizeArticleManagement($request);

        if ($articleCategory->articles()->exists()) {
            return back()->with('warning', 'Kategori yang sudah memiliki artikel tidak dapat dihapus.');
        }

        $articleCategory->delete();

        return redirect()->route('article-categories.index')->with('success', 'Kategori kajian berhasil dihapus.');
    }

    private function validatedData(Request $request, ?ArticleCategory $category = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:140', Rule::unique('article_categories', 'slug')->ignore($category)],
            'description' => ['nullable', 'string', 'max:1000'],
            'color' => ['nullable', 'string', 'max:40'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = ($validated['sort_order'] ?? null) ?: null;
        $validated['description'] = ($validated['description'] ?? null) ?: null;
        $validated['color'] = ($validated['color'] ?? null) ?: null;

        return $validated;
    }

    private function uniqueSlug(string $value, ?ArticleCategory $ignore = null): string
    {
        $slug = Str::slug($value);
        $base = $slug ?: Str::random(8);
        $counter = 2;

        while (ArticleCategory::where('slug', $slug)->when($ignore, fn ($query) => $query->whereKeyNot($ignore->id))->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function authorizeArticleManagement(Request $request): void
    {
        abort_unless(in_array($request->user()?->role, ['admin', 'secretary'], true), 403);
    }
}
