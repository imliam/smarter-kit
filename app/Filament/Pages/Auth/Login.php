<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BasePage;
use Override;

class Login extends BasePage
{
    #[Override]
    public function mount(): void
    {
        parent::mount();

        if (app()->isLocal()) {
            $this->form->fill([
                'email' => config('default_user.email'),
                'password' => config('default_user.password'),
                'remember' => true,
            ]);
        }
    }
}
