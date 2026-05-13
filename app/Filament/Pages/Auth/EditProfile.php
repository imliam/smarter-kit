<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Illuminate\Support\Facades\Auth;

class EditProfile extends BaseEditProfile
{
    /** After the profile is saved, sign out all other devices if the password was changed. */
    protected function afterSave(): void
    {
        $password = $this->data['password'] ?? null;

        if (filled($password)) {
            Auth::logoutOtherDevices($password);
        }
    }
}
