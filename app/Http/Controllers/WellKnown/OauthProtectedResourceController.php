<?php

declare(strict_types=1);

namespace App\Http\Controllers\WellKnown;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class OauthProtectedResourceController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $appUrl = mb_rtrim((string) config('app.url'), '/');

        return response()->json([
            'resource' => $appUrl,
            'authorization_servers' => [$appUrl],
            'bearer_methods_supported' => ['header'],
            'scopes_supported' => (array) config('sanctum.abilities.default'),
            'resource_documentation' => $appUrl.'/docs',
        ]);
    }
}
