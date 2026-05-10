<?php

declare(strict_types=1);

namespace App\Http\Controllers\WellKnown;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class TrafficAdviceController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            ['user-agent' => '*', 'disallow' => false],
        ]);
    }
}
