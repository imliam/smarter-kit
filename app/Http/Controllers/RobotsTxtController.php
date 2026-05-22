<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class RobotsTxtController extends Controller
{
    public function __invoke(): Response
    {
        $appUrl = mb_rtrim((string) config('app.url'), '/');

        $body = implode("\n", [
            'User-agent: *',
            'Disallow:',
            '',
            "Sitemap: {$appUrl}/sitemap.xml",
        ]);

        return response($body, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
        ]);
    }
}
