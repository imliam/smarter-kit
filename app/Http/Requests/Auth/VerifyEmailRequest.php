<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Override;

final class VerifyEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, ValidationRule|array<mixed>|string> */
    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', Rule::exists('users', 'id')],
            'hash' => ['required', 'string', 'size:64'],
        ];
    }

    /** @return array<string, mixed> */
    #[Override]
    public function validationData(): array
    {
        /** @var array<string, mixed> $data */
        $data = array_merge($this->all(), [
            'id' => $this->route('id'),
            'hash' => $this->route('hash'),
        ]);

        return $data;
    }

    public function id(): string
    {
        return (string) $this->validated('id');
    }

    public function hash(): string
    {
        return (string) $this->validated('hash');
    }
}
