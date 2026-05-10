<?php

declare(strict_types=1);

namespace App\Http\Controllers\WellKnown;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class GpcController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'gpc' => true,
            'version' => 1,
            'lastUpdate' => config('security.gpc_last_update', '2024-01-01'),
        ]);
    }
}
