<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use Database\Seeders\DatabaseSeeder;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Components\Component;
use Override;

class Login extends BaseLogin
{
    #[Override]
    protected function getEmailFormComponent(): Component
    {
        return parent::getEmailFormComponent()
            ->default(app()->isLocal() ? DatabaseSeeder::DEFAULT_EMAIL : null);
    }

    #[Override]
    protected function getPasswordFormComponent(): Component
    {
        return parent::getPasswordFormComponent()
            ->default(app()->isLocal() ? DatabaseSeeder::DEFAULT_PASSWORD : null);
    }

    #[Override]
    protected function getRememberFormComponent(): Component
    {
        return parent::getRememberFormComponent()
            ->default(app()->isLocal());
    }
}
