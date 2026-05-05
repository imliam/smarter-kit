<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use App\Support\SecurityAudit;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;
use Knuckles\Scribe\Attributes\Subgroup;
use Knuckles\Scribe\Attributes\Unauthenticated;

#[Group(name: 'Authentication')]
#[Subgroup(name: 'Password Reset')]
#[Endpoint(title: 'Reset Password', description: 'Reset the user password using a valid reset token.')]
#[Unauthenticated]
#[BodyParam('token', type: 'string', description: 'Password reset token.', required: true, example: 'reset-token-value')]
#[BodyParam('email', type: 'string', description: 'Account email associated with token.', required: true, example: 'jane@example.com')]
#[BodyParam('password', type: 'string', description: 'New account password.', required: true, example: 'new-password123')]
#[BodyParam('password_confirmation', type: 'string', description: 'Must match password.', required: true, example: 'new-password123')]
#[Response(content: ['message' => 'Your password has been reset.'], status: 200, description: 'Password reset succeeded.')]
#[Response(
    content: [
        'message' => 'The given data was invalid.',
        'errors' => ['email' => ['This password reset token is invalid.']],
    ],
    status: 422,
    description: 'Reset token or payload was invalid.'
)]
final class ResetPasswordController
{
    public function __invoke(ResetPasswordRequest $request): JsonResponse
    {
        $resetUserId = null;

        $status = Password::broker()->reset(
            [
                'email' => $request->email(),
                'password' => $request->password(),
                'password_confirmation' => $request->passwordConfirmation(),
                'token' => $request->token(),
            ],
            function (User $user, string $password) use (&$resetUserId): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                $resetUserId = (string) $user->getKey();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            SecurityAudit::log('auth.password_reset.failed', [
                'email_hash' => SecurityAudit::hashEmail($request->email()),
                'status' => $status,
            ]);

            throw ValidationException::withMessages([
                'email' => [$this->statusToMessage((string) $status)],
            ]);
        }

        SecurityAudit::log('auth.password_reset.succeeded', [
            'user_id' => $resetUserId,
            'email_hash' => SecurityAudit::hashEmail($request->email()),
            'status' => $status,
        ]);

        return new JsonResponse([
            'message' => 'Your password has been reset.',
        ]);
    }

    private function statusToMessage(string $status): string
    {
        return match ($status) {
            Password::INVALID_TOKEN => 'This password reset token is invalid.',
            Password::INVALID_USER => "We can't find a user with that email address.",
            Password::RESET_THROTTLED => 'Please wait before retrying.',
            default => 'Unable to reset password with the provided details.',
        };
    }
}
