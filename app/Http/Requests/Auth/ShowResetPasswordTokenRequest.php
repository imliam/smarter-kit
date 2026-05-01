<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Override;

final class ShowResetPasswordTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
        ];
    }

    /** @return array<string, mixed> */
    #[Override]
    public function validationData(): array
    {
        return array_merge($this->all(), [
            'token' => $this->route('token'),
        ]);
    }

    public function token(): string
    {
        return (string) $this->validated('token');
    }

    public function email(): ?string
    {
        $email = $this->validated('email');

        return isset($email) ? (string) $email : null;
    }
}
