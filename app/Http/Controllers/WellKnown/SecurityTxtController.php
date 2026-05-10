<?php

declare(strict_types=1);

namespace App\Http\Controllers\WellKnown;

use App\Http\Controllers\Controller;
use Carbon\CarbonImmutable;
use Illuminate\Http\Response;

class SecurityTxtController extends Controller
{
    public function __invoke(): Response
    {
        $contact = config('security.contact_email');
        $expires = CarbonImmutable::now()->addYear()->toRfc3339String();
        $appUrl = mb_rtrim((string) config('app.url'), '/');

        $body = implode("\n", [
            "Contact: mailto:{$contact}",
            "Expires: {$expires}",
            'Preferred-Languages: en',
            "Canonical: {$appUrl}/.well-known/security.txt",
        ]);

        return response($body, 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
        ]);
    }
}
