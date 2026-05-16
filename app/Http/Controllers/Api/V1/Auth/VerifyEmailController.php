<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Requests\Auth\VerifyEmailRequest;
use App\Models\User;
use App\Support\SecurityAudit;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Subgroup;
use Knuckles\Scribe\Attributes\Unauthenticated;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group(name: 'Authentication')]
#[Subgroup(name: 'Email Verification')]
#[Endpoint(title: 'Verify Email', description: 'Verify a user email using the signed verification link parameters.')]
#[Unauthenticated]
#[UrlParam('id', type: 'string', description: 'User ID from the signed verification link.', required: true, example: '019e2df4-3309-7000-a000-000000000001')]
#[UrlParam('hash', type: 'string', description: 'SHA-256 hash of the email from the signed verification link.', required: true, example: 'a8d2e6b9c4f1e3d7b0a2c5f8e1d4b7a0c3f6e9d2b5a8c1f4e7d0b3a6c9f2e5d8')]
#[Response(content: ['message' => 'Email verified successfully.'], status: 200, description: 'Email was verified.')]
#[Response(content: ['message' => 'Invalid verification link.'], status: 403, description: 'Signed URL was invalid, expired, or mismatched.')]
final class VerifyEmailController
{
    public function __invoke(VerifyEmailRequest $request): JsonResponse
    {
        $user = User::query()->findOrFail($request->id());

        if (! hash_equals(hash('sha256', $user->getEmailForVerification()), $request->hash())) {
            SecurityAudit::log('auth.email_verification.failed', [
                'user_id' => (string) $user->getKey(),
                'reason' => 'hash_mismatch',
            ]);

            abort(403, 'Invalid verification link.');
        }

        $wasAlreadyVerified = $user->hasVerifiedEmail();

        if (! $wasAlreadyVerified && $user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        SecurityAudit::log('auth.email_verification.succeeded', [
            'user_id' => (string) $user->getKey(),
            'already_verified' => $wasAlreadyVerified,
        ]);

        return new JsonResponse([
            'message' => 'Email verified successfully.',
        ]);
    }
}
