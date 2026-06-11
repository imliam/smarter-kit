<?php

declare(strict_types=1);

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\Auth\SocialLoginCallbackController;
use App\Http\Controllers\Auth\SocialLoginRedirectController;
use App\Http\Controllers\RobotsTxtController;
use App\Http\Controllers\SitemapController;
use App\Http\Middleware\EnsureTeamMembership;
use App\Models\Article;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::view('/', 'welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt', RobotsTxtController::class)->name('robots-txt');

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function (): void {
        Route::view('dashboard', 'dashboard')->name('dashboard');
    });

Route::middleware(['auth'])->group(function (): void {
    Route::livewire('invitations/{invitation}/accept', 'pages::teams.accept-invitation')->name('invitations.accept');
});

Route::get('/login/{service}', SocialLoginRedirectController::class)->name('social-login.redirect');
Route::get('/login/{service}/callback', SocialLoginCallbackController::class)->name('social-login.callback');

require __DIR__.'/web/settings.php';
require __DIR__.'/web/well-known.php';

$reservedArticlePaths = implode('|', array_map(preg_quote(...), Article::RESERVED_TOP_LEVEL_PATHS));

Route::get('{path}', ArticleController::class)
    ->where('path', "^(?!({$reservedArticlePaths})(/|$)).+")
    ->name('articles.show');
