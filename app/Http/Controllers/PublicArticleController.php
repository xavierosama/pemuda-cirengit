<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicArticleController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $categorySlug = $request->string('category')->toString();
        $sort = $request->string('sort')->toString();

        $categories = ArticleCategory::query()
            ->active()
            ->withCount(['articles' => fn ($query) => $query->published()])
            ->orderByRaw('sort_order is null')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $selectedCategory = $categorySlug
            ? $categories->firstWhere('slug', $categorySlug)
            : null;

        $articles = Article::query()
            ->with(['category', 'author', 'managedByBidang'])
            ->published()
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            }))
            ->when($selectedCategory, fn ($query) => $query->where('article_category_id', $selectedCategory->id))
            ->when($sort === 'popular',
                fn ($query) => $query->orderByDesc('views_count')->latest('published_at'),
                fn ($query) => $query->latest('published_at')
            )
            ->paginate(9)
            ->withQueryString();

        return view('public.articles.index', compact('articles', 'categories', 'selectedCategory', 'search', 'sort'));
    }

    public function category(Request $request, ArticleCategory $category): View
    {
        abort_unless($category->is_active, 404);

        $request->merge(['category' => $category->slug]);

        return $this->index($request);
    }

    public function show(Request $request, Article $article): View
    {
        abort_unless($article->isPublished(), 404);

        $viewedKey = 'viewed_article_'.$article->id;
        if (! $request->session()->has($viewedKey)) {
            $article->increment('views_count');
            $request->session()->put($viewedKey, true);
            $article->refresh();
        }

        $article->load(['category', 'author', 'managedByBidang']);

        $relatedArticles = Article::query()
            ->with(['category', 'author', 'managedByBidang'])
            ->published()
            ->whereKeyNot($article->id)
            ->when($article->article_category_id, fn ($query) => $query->where('article_category_id', $article->article_category_id))
            ->latest('published_at')
            ->limit(3)
            ->get();

        return view('public.articles.show', compact('article', 'relatedArticles'));
    }
}
