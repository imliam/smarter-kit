<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $sitemap = Cache::remember('sitemap', now()->addHours(24), fn (): Sitemap => $this->buildSitemap());

        return response($sitemap->render(), 200, [
            'Content-Type' => 'application/xml',
        ]);
    }

    private function buildSitemap(): Sitemap
    {
        $sitemap = Sitemap::create();

        $sitemap->add(
            Url::create('/')
                ->setPriority(1.0)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                ->setLastModificationDate(now()),
        );

        Article::query()
            ->publiclyVisible()
            ->orderBy('path')
            ->each(function (Article $article) use ($sitemap): void {
                $sitemap->add(
                    Url::create($article->url())
                        ->setPriority(0.8)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                        ->setLastModificationDate($article->updated_at ?? now()),
                );
            });

        return $sitemap;
    }
}
