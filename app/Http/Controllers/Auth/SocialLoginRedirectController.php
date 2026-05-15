<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;

class SocialLoginRedirectController extends Controller
{
    public function __invoke(string $service, Request $request): RedirectResponse
    {
        abort_unless(in_array($service, config('auth.social_providers'), true), 404);

        if ($request->boolean('connect')) {
            session(['social_connect_intent' => true]);
        }

        return Socialite::driver($service)->redirect();
    }
}
