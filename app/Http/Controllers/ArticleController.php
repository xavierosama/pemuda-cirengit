<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Department;
use App\Support\Articles\ArticleContent;
use App\Support\Articles\YoutubeEmbed;
use App\Support\TableControls;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeArticleManagement($request);

        $search = $request->string('search')->toString();
        $status = $request->string('status')->toString();
        $categoryId = $request->string('article_category_id')->toString();
        $allowedSorts = [
            'title' => 'title',
            'status' => 'status',
            'views_count' => 'views_count',
            'published_at' => 'published_at',
            'created_at' => 'created_at',
        ];
        $currentSort = TableControls::sort($request, $allowedSorts);
        $currentDirection = TableControls::direction($request);
        $perPage = TableControls::perPage($request);

        $articles = Article::query()
            ->with(['category', 'author', 'managedByBidang'])
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%");
            }))
            ->when(array_key_exists($status, Article::STATUSES), fn ($query) => $query->where('status', $status))
            ->when($categoryId, fn ($query) => $query->where('article_category_id', $categoryId))
            ->tap(fn ($query) => TableControls::applySort($query, $currentSort, $currentDirection, $allowedSorts, fn ($query) => $query->latest()))
            ->paginate($perPage)
            ->withQueryString();

        $categories = ArticleCategory::orderBy('name')->get(['id', 'name']);
        $articleStats = [
            'total' => Article::count(),
            'published' => Article::where('status', 'published')->count(),
            'draft' => Article::where('status', 'draft')->count(),
            'archived' => Article::where('status', 'archived')->count(),
        ];

        return view('articles.index', array_merge(
            compact('articles', 'categories', 'articleStats', 'search', 'status', 'categoryId'),
            TableControls::viewData($request, $currentSort, $currentDirection, $perPage)
        ));
    }

    public function create(Request $request): View
    {
        $this->authorizeArticleManagement($request);

        return view('articles.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeArticleManagement($request);

        $validated = $this->validatedData($request);
        $validated['slug'] = $this->uniqueSlug(($validated['slug'] ?? null) ?: $validated['title']);
        $validated['author_id'] = $request->user()->id;
        $validated['published_at'] = $this->publishedAt($validated);

        if ($request->hasFile('banner_image')) {
            $validated['banner_image'] = $request->file('banner_image')->store('articles', 'public');
        }

        $article = Article::create($validated);

        return redirect()->route('articles.show', $article)->with('success', 'Artikel kajian berhasil ditambahkan.');
    }

    public function show(Request $request, Article $article): View
    {
        $this->authorizeArticleManagement($request);

        $article->load(['category', 'author', 'managedByBidang']);

        return view('articles.show', compact('article'));
    }

    public function edit(Request $request, Article $article): View
    {
        $this->authorizeArticleManagement($request);

        return view('articles.edit', array_merge(['article' => $article], $this->formData()));
    }

    public function update(Request $request, Article $article): RedirectResponse
    {
        $this->authorizeArticleManagement($request);

        $validated = $this->validatedData($request, $article);
        $validated['slug'] = $this->uniqueSlug(($validated['slug'] ?? null) ?: $validated['title'], $article);
        $validated['published_at'] = $this->publishedAt($validated, $article);

        if ($request->hasFile('banner_image')) {
            if ($article->banner_image) {
                Storage::disk('public')->delete($article->banner_image);
            }
            $validated['banner_image'] = $request->file('banner_image')->store('articles', 'public');
        }

        $article->update($validated);

        return redirect()->route('articles.show', $article)->with('success', 'Artikel kajian berhasil diperbarui.');
    }

    public function destroy(Request $request, Article $article): RedirectResponse
    {
        $this->authorizeArticleManagement($request);

        $article->delete();

        return redirect()->route('articles.index')->with('success', 'Artikel kajian berhasil dihapus.');
    }

    public function editorImageUpload(Request $request): JsonResponse
    {
        $this->authorizeArticleManagement($request);

        $validated = $request->validate([
            'file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:1024'],
        ]);

        $path = $validated['file']->store('article-content', 'public');

        return response()->json([
            'location' => Storage::url($path),
        ]);
    }

    private function validatedData(Request $request, ?Article $article = null): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'slug' => ['nullable', 'string', 'max:200', Rule::unique('articles', 'slug')->ignore($article)],
            'article_category_id' => ['nullable', 'integer', 'exists:article_categories,id'],
            'managed_by_bidang_id' => ['nullable', 'integer', 'exists:departments,id'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'banner_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'youtube_url' => ['nullable', 'url', 'max:500'],
            'status' => ['required', Rule::in(array_keys(Article::STATUSES))],
            'published_at' => ['nullable', 'date'],
            'is_featured' => ['nullable', 'boolean'],
        ]);

        if (($validated['youtube_url'] ?? null) && ! YoutubeEmbed::videoId($validated['youtube_url'])) {
            throw ValidationException::withMessages([
                'youtube_url' => 'URL YouTube harus berasal dari youtube.com atau youtu.be yang valid.',
            ]);
        }

        $validated['content'] = ArticleContent::sanitize($validated['content']);
        if (trim(strip_tags(html_entity_decode($validated['content']))) === '') {
            throw ValidationException::withMessages([
                'content' => 'Isi kajian wajib memiliki teks yang dapat dibaca.',
            ]);
        }

        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['article_category_id'] = ($validated['article_category_id'] ?? null) ?: null;
        $validated['managed_by_bidang_id'] = ($validated['managed_by_bidang_id'] ?? null) ?: null;
        $validated['published_at'] = ($validated['published_at'] ?? null) ?: null;
        $validated['youtube_url'] = ($validated['youtube_url'] ?? null) ?: null;
        $validated['excerpt'] = ($validated['excerpt'] ?? null) ?: null;

        return $validated;
    }

    private function publishedAt(array $validated, ?Article $article = null): ?string
    {
        if ($validated['status'] !== 'published') {
            return $validated['published_at'] ?? null;
        }

        return $validated['published_at']
            ?? $article?->published_at?->toDateTimeString()
            ?? now()->toDateTimeString();
    }

    private function uniqueSlug(string $value, ?Article $ignore = null): string
    {
        $slug = Str::slug($value);
        $base = $slug ?: Str::random(8);
        $counter = 2;

        while (Article::where('slug', $slug)->when($ignore, fn ($query) => $query->whereKeyNot($ignore->id))->exists()) {
            $slug = "{$base}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    private function formData(): array
    {
        return [
            'categories' => ArticleCategory::active()->orderBy('name')->get(['id', 'name']),
            'departments' => Department::where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'statuses' => Article::STATUSES,
        ];
    }

    private function authorizeArticleManagement(Request $request): void
    {
        abort_unless(in_array($request->user()?->role, ['admin', 'secretary'], true), 403);
    }
}
