<?php

declare(strict_types=1);

use App\Http\Controllers\WellKnown\ApiCatalogController;
use App\Http\Controllers\WellKnown\GpcController;
use App\Http\Controllers\WellKnown\OauthProtectedResourceController;
use App\Http\Controllers\WellKnown\SecurityTxtController;
use App\Http\Controllers\WellKnown\TrafficAdviceController;
use Illuminate\Support\Facades\Route;

Route::prefix('.well-known')->name('well-known.')->group(function (): void {
    Route::get('security.txt', SecurityTxtController::class)->name('security-txt');
    Route::redirect('change-password', '/settings/security')->name('change-password');
    Route::get('gpc.json', GpcController::class)->name('gpc');
    Route::get('oauth-protected-resource', OauthProtectedResourceController::class)->name('oauth-protected-resource');
    Route::get('api-catalog', ApiCatalogController::class)->name('api-catalog');
    Route::get('traffic-advice', TrafficAdviceController::class)->name('traffic-advice');
});
