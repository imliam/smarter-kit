<?php

declare(strict_types=1);

namespace App\Http\Controllers\WellKnown;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ApiCatalogController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $appUrl = mb_rtrim((string) config('app.url'), '/');
        $docsUrl = (string) config('scribe.laravel.docs_url', '/docs');
        $docsAbsoluteUrl = $appUrl.'/'.mb_ltrim($docsUrl, '/');

        return response()->json([
            'linkset' => [
                [
                    'anchor' => $appUrl.'/api/v1',
                    'service-doc' => [
                        ['href' => $docsAbsoluteUrl, 'type' => 'text/html'],
                    ],
                    'service-desc' => [
                        ['href' => $docsAbsoluteUrl.'.openapi', 'type' => 'application/vnd.oai.openapi;version=3.0'],
                    ],
                ],
            ],
        ], 200, [
            'Content-Type' => 'application/linkset+json',
        ]);
    }
}
