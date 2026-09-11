<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\View\View;

class PublicLandingController extends Controller
{
    public function __invoke(): View
    {
        $featuredArticles = Article::query()
            ->with(['category', 'author', 'managedByBidang'])
            ->published()
            ->featured()
            ->latest('published_at')
            ->limit(3)
            ->get();

        $latestArticles = Article::query()
            ->with(['category', 'author', 'managedByBidang'])
            ->latestPublished()
            ->limit(6)
            ->get();

        $popularArticles = Article::query()
            ->with(['category', 'author', 'managedByBidang'])
            ->popular()
            ->limit(3)
            ->get();

        $categories = ArticleCategory::query()
            ->active()
            ->withCount(['articles' => fn ($query) => $query->published()])
            ->orderByRaw('sort_order is null')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('public.home', compact('featuredArticles', 'latestArticles', 'popularArticles', 'categories'));
    }
}
