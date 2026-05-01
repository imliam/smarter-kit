<?php

declare(strict_types=1);

use App\Http\Middleware\AddContext;
use App\Http\Middleware\AttachRequestId;
use App\Http\Middleware\EnforceTransportSecurity;
use App\Http\Middleware\EnsureJsonApiRequest;
use App\Http\Middleware\HttpsRedirect;
use App\Http\Middleware\IdempotencyKey;
use App\Http\Middleware\SetRequestLocale;
use App\Http\Middleware\SetTeamUrlDefaults;
use App\Http\Middleware\Sunset;
use App\Support\SecurityAudit;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'abilities' => CheckAbilities::class,
            'ability' => CheckForAnyAbility::class,
            'idempotency' => IdempotencyKey::class,
            'sunset' => Sunset::class,
        ]);

        $trustedProxies = (string) env('TRUSTED_PROXIES', '*');
        $middleware->trustProxies($trustedProxies !== '' ? $trustedProxies : null);

        $trustedHosts = array_values(array_filter(array_map(
            static fn (string $host): string => mb_trim($host),
            explode(',', (string) env('TRUSTED_HOSTS', '')),
        )));
        if ($trustedHosts !== []) {
            $middleware->trustHosts(at: $trustedHosts, subdomains: false);
        }

        $middleware->api(prepend: [
            EnsureJsonApiRequest::class,
            EnforceTransportSecurity::class,
            SetRequestLocale::class,
            AttachRequestId::class,
        ]);

        $middleware->web(append: [
            HttpsRedirect::class,
            AttachRequestId::class,
            AddContext::class,
            SetTeamUrlDefaults::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthenticationException $exception, Request $request): ?JsonResponse {
            if (! $request->expectsJson()) {
                return null;
            }

            SecurityAudit::log('auth.unauthenticated', [
                'guard' => 'sanctum',
            ]);

            return new JsonResponse([
                'message' => 'Unauthenticated.',
            ], 401);
        });

        $exceptions->render(function (AuthorizationException $exception, Request $request): ?JsonResponse {
            if (! $request->expectsJson()) {
                return null;
            }

            SecurityAudit::log('auth.forbidden', [
                'exception' => $exception::class,
            ]);

            return new JsonResponse([
                'message' => 'Forbidden.',
            ], 403);
        });

        $exceptions->render(function (AccessDeniedHttpException $exception, Request $request): ?JsonResponse {
            if (! $request->expectsJson()) {
                return null;
            }

            SecurityAudit::log('auth.forbidden', [
                'exception' => $exception::class,
            ]);

            return new JsonResponse([
                'message' => 'Forbidden.',
            ], 403);
        });

        $exceptions->render(function (TooManyRequestsHttpException $exception, Request $request): ?JsonResponse {
            if (! $request->expectsJson()) {
                return null;
            }

            SecurityAudit::log('api.rate_limited');

            $response = new JsonResponse([
                'message' => 'Too many requests. Please try again later.',
            ], 429);

            $retryAfter = $exception->getHeaders()['Retry-After'] ?? null;
            if ($retryAfter !== null) {
                $response->headers->set('Retry-After', (string) $retryAfter);
            }

            return $response;
        });

        $exceptions->render(function (ValidationException $exception, Request $request): ?JsonResponse {
            if (! $request->expectsJson()) {
                return null;
            }

            SecurityAudit::log('api.validation_failed', [
                'errors' => array_keys($exception->errors()),
            ]);

            return new JsonResponse([
                'message' => 'The given data was invalid.',
                'errors' => $exception->errors(),
            ], $exception->status);
        });

        $exceptions->render(function (InvalidSignatureException $exception, Request $request): ?JsonResponse {
            if (! $request->expectsJson()) {
                return null;
            }

            SecurityAudit::log('auth.email_verification.invalid_signature');

            return new JsonResponse([
                'message' => 'Invalid verification link.',
            ], 403);
        });
    })->create();
