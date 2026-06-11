<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\ArticleVisibility;
use App\Models\Article;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ArticleController extends Controller
{
    public function __invoke(string $path): View|RedirectResponse
    {
        $article = Article::query()
            ->published()
            ->where('path', Article::normalizePath($path))
            ->firstOrFail();

        if ($article->visibility === ArticleVisibility::Authenticated && Auth::guest()) {
            return redirect()->guest(route('login'));
        }

        return view('articles.show', [
            'article' => $article,
        ]);
    }
}
