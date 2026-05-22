<?php

declare(strict_types=1);

namespace App\Http\Controllers;

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

        return $sitemap;
    }
}
