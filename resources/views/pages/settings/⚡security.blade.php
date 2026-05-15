<?php

use App\Concerns\PasswordValidationRules;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Laravel\Passkeys\Passkey;
use Laravel\Passkeys\Passkeys as PasskeysManager;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Security settings')] class extends Component {
    use PasswordValidationRules;

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $logout_other_devices_password = '';

    public bool $canManageTwoFactor;

    public bool $twoFactorEnabled;

    public bool $requiresConfirmation;

    /**
     * Mount the component.
     */
    public function mount(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        $this->canManageTwoFactor = Features::canManageTwoFactorAuthentication();

        if ($this->canManageTwoFactor) {
            if (Fortify::confirmsTwoFactorAuthentication() && is_null(auth()->user()->two_factor_confirmed_at)) {
                $disableTwoFactorAuthentication(auth()->user());
            }

            $this->twoFactorEnabled = auth()->user()->hasEnabledTwoFactorAuthentication();
            $this->requiresConfirmation = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirm');
        }
    }

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => $this->currentPasswordRules(),
                'password' => $this->passwordRules(),
            ]);
        } catch (ValidationException $validationException) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $validationException;
        }

        Auth::logoutOtherDevices($validated['current_password']);

        Auth::user()->update([
            'password' => $validated['password'],
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }

    /**
     * Log out all other devices after verifying the user's password.
     */
    public function logoutOtherDevices(): void
    {
        try {
            $validated = $this->validate([
                'logout_other_devices_password' => $this->currentPasswordRules(),
            ]);
        } catch (ValidationException $validationException) {
            $this->reset('logout_other_devices_password');

            throw $validationException;
        }

        Auth::logoutOtherDevices($validated['logout_other_devices_password']);

        $this->reset('logout_other_devices_password');

        Flux::modal('confirm-logout-other-devices')->close();

        Flux::toast(text: 'All other devices have been signed out.', variant: 'success');
    }

    /**
     * Handle the two-factor authentication enabled event.
     */
    #[On('two-factor-enabled')]
    public function onTwoFactorEnabled(): void
    {
        $this->twoFactorEnabled = true;
    }

    /**
     * Disable two-factor authentication for the user.
     */
    public function disable(DisableTwoFactorAuthentication $disableTwoFactorAuthentication): void
    {
        $disableTwoFactorAuthentication(auth()->user());

        $this->twoFactorEnabled = false;
    }

    /**
     * Delete a passkey for the authenticated user.
     */
    public function deletePasskey(int $passkeyId): void
    {
        $passkey = Auth::user()->passkeys()->findOrFail($passkeyId);
        $passkey->delete();
    }

    /**
     * Disconnect a linked social account.
     */
    public function disconnectSocial(int $socialId): void
    {
        $user = Auth::user();
        $social = $user->social()->findOrFail($socialId);

        $hasPassword = ! is_null($user->password);
        $otherSocialCount = $user->social()->where('id', '!=', $socialId)->count();
        $hasPasskeys = $user->passkeys()->exists();

        if (! $hasPassword && $otherSocialCount === 0 && ! $hasPasskeys) {
            Flux::toast(text: 'Set a password before removing your only sign-in method.', variant: 'warning');

            return;
        }

        $social->delete();

        Flux::toast(text: ucfirst((string) $social->service).' account disconnected.', variant: 'success');
    }
}; ?>

<section class="w-full">
    @include ('partials.settings-heading')

    <flux:heading class="sr-only">Security settings</flux:heading>

    <x-pages::settings.layout
        heading="Update password"
        subheading="Ensure your account is using a long, random password to stay secure"
    >
        @php $passwordRules = Password::defaults()->toPasswordRulesString(); @endphp
        <form method="POST" wire:submit="updatePassword" class="mt-6 space-y-6">
            <flux:input
                wire:model="current_password"
                label="Current password"
                type="password"
                required
                autocomplete="current-password"
                viewable
            />
            <flux:input
                wire:model="password"
                label="New password"
                type="password"
                required
                autocomplete="new-password"
                passwordrules="{{ $passwordRules }}"
                viewable
            />
            <flux:input
                wire:model="password_confirmation"
                label="Confirm password"
                type="password"
                required
                autocomplete="new-password"
                passwordrules="{{ $passwordRules }}"
                viewable
            />

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button
                        variant="primary"
                        type="submit"
                        class="w-full"
                        data-test="update-password-button"
                    >
                        Save
                    </flux:button>
                </div>

                <x-action-message class="me-3" on="password-updated">
                    Saved.
                </x-action-message>
            </div>
        </form>

        @if ($canManageTwoFactor)
            <section class="mt-12">
                <flux:heading>Two-factor authentication</flux:heading>
                <flux:subheading
                    >Manage your two-factor authentication
                    settings</flux:subheading
                >

                <div
                    class="mx-auto flex w-full flex-col space-y-6 text-sm"
                    wire:cloak
                >
                    @if ($twoFactorEnabled)
                        <div class="space-y-4">
                            <flux:text>
                                You will be prompted for a secure, random pin
                                during login, which you can retrieve from the
                                TOTP-supported application on your phone.
                            </flux:text>

                            <div class="flex justify-start">
                                <flux:button
                                    variant="danger"
                                    wire:click="disable"
                                >
                                    Disable 2FA
                                </flux:button>
                            </div>

                            <livewire:pages::settings.two-factor.recovery-codes
                                :$requiresConfirmation
                            />
                        </div>
                    @else
                        <div class="space-y-4">
                            <flux:text variant="subtle">
                                When you enable two-factor authentication, you
                                will be prompted for a secure pin during login.
                                This pin can be retrieved from a TOTP-supported
                                application on your phone.
                            </flux:text>

                            <flux:modal.trigger name="two-factor-setup-modal">
                                <flux:button
                                    variant="primary"
                                    wire:click="$dispatch('start-two-factor-setup')"
                                >
                                    Enable 2FA
                                </flux:button>
                            </flux:modal.trigger>

                            <livewire:pages::settings.two-factor-setup-modal
                                :requires-confirmation="$requiresConfirmation"
                            />
                        </div>
                    @endif
                </div>
            </section>
        @endif

        @if (Features::enabled(Features::passkeys()))
            <section class="mt-12">
                <flux:heading>Passkeys</flux:heading>
                <flux:subheading
                    >Sign in without a password using biometrics or a hardware
                    key</flux:subheading
                >

                <div
                    class="mt-6 space-y-4"
                    x-data="{
                        registering: false,
                        error: null,
                        async register() {
                            const name = prompt(
                                'Name this passkey (e.g. \'My MacBook\')',
                            );

                            if (name === null) return;
                            this.error = null;
                            this.registering = true;
                            try {
                                await Passkeys.register({
                                    name: name.trim() || 'My device',
                                });
                                $wire.call('$refresh');
                            } catch (e) {
                                this.error =
                                    e?.message ??
                                    'Passkey registration failed. Please try again.';
                            } finally {
                                this.registering = false;
                            }
                        },
                    }"
                >
                    @forelse (auth()->user()->passkeys as $passkey)
                        <div
                            class="flex items-center justify-between rounded-lg border border-zinc-200 px-4 py-3 dark:border-zinc-700"
                        >
                            <div class="flex items-center gap-3">
                                <flux:icon.key class="size-5 text-zinc-400" />
                                <div>
                                    <flux:text
                                        class="font-medium"
                                        >{{ $passkey->name }}</flux:text
                                    >
                                    <flux:text variant="subtle" class="text-xs">
                                        Added {{ $passkey->created_at->diffForHumans() }}
                                        @if ($passkey->last_used_at)
                                            · Last used {{ $passkey->last_used_at->diffForHumans() }}
                                        @endif
                                    </flux:text>
                                </div>
                            </div>
                            <flux:button
                                variant="danger"
                                size="sm"
                                wire:click="deletePasskey({{ $passkey->id }})"
                                wire:confirm="Are you sure you want to remove this passkey?"
                            >
                                Remove
                            </flux:button>
                        </div>
                    @empty
                        <flux:text variant="subtle">
                            You have no passkeys registered. Add one to sign in
                            without a password.
                        </flux:text>
                    @endforelse

                    <div class="flex items-center gap-4">
                        <flux:button
                            x-on:click="register"
                            x-bind:disabled="registering"
                            data-test="register-passkey-button"
                        >
                            Add passkey
                        </flux:button>

                        <flux:error x-show="error" x-text="error" />
                    </div>
                </div>
            </section>
        @endif

        <section class="mt-12">
            <flux:heading>Connected accounts</flux:heading>
            <flux:subheading
                >Manage the social accounts linked to your
                profile</flux:subheading
            >

            <div class="mt-6 space-y-4">
                @forelse (auth()->user()->social as $socialAccount)
                    @php
                        $hasPassword = ! is_null(auth()->user()->password);
                        $otherSocialCount = auth()->user()->social->where('id', '!=', $socialAccount->id)->count();
                        $hasPasskeys = auth()->user()->passkeys->isNotEmpty();
                        $canDisconnect = $hasPassword || $otherSocialCount > 0 || $hasPasskeys;
                    @endphp
                    <div
                        class="flex items-center justify-between rounded-lg border border-zinc-200 px-4 py-3 dark:border-zinc-700"
                    >
                        <div class="flex items-center gap-3">
                            {!! svg('simpleicon-'.$socialAccount->service, 'size-5 text-zinc-600 dark:text-zinc-400')->toHtml() !!}
                            <div>
                                <flux:text
                                    class="font-medium"
                                    >{{ ucfirst($socialAccount->service) }}</flux:text
                                >
                                <flux:text variant="subtle" class="text-xs">
                                    Connected {{ $socialAccount->created_at->diffForHumans() }}
                                </flux:text>
                            </div>
                        </div>
                        <flux:button
                            variant="danger"
                            size="sm"
                            wire:click="disconnectSocial({{ $socialAccount->id }})"
                            wire:confirm="Are you sure you want to disconnect this account?"
                            :disabled="! $canDisconnect"
                            :title="! $canDisconnect ? 'Set a password before removing your only sign-in method' : ''"
                        >
                            Remove
                        </flux:button>
                    </div>
                @empty
                    <flux:text variant="subtle"
                        >No social accounts connected.</flux:text
                    >
                @endforelse

                @foreach (config('auth.social_providers') as $provider)
                    @if (! auth()->user()->hasSocialLinked($provider))
                        <div
                            class="flex items-center justify-between rounded-lg border border-dashed border-zinc-200 px-4 py-3 dark:border-zinc-700"
                        >
                            <div class="flex items-center gap-3">
                                {!! svg('simpleicon-'.$provider, 'size-5 text-zinc-400')->toHtml() !!}
                                <flux:text
                                    variant="subtle"
                                    >{{ ucfirst($provider) }}</flux:text
                                >
                            </div>
                            <flux:button
                                size="sm"
                                tag="a"
                                href="{{ route('social-login.redirect', ['service' => $provider]) }}?connect=true"
                            >
                                Connect
                            </flux:button>
                        </div>
                    @endif
                @endforeach
            </div>
        </section>

        <section class="mt-12">
            <flux:heading>Log out other devices</flux:heading>
            <flux:subheading
                >Sign out of all other browser sessions across all of your
                devices</flux:subheading
            >
            <div class="mt-6 space-y-6">
                <flux:text>
                    If you believe your account has been compromised, or you've
                    recently logged in on a public or shared device, you can
                    sign out of all other active sessions here.
                </flux:text>

                <flux:modal.trigger name="confirm-logout-other-devices">
                    <flux:button
                        variant="filled"
                        data-test="logout-other-devices-button"
                    >
                        Log out other devices
                    </flux:button>
                </flux:modal.trigger>

                <flux:modal
                    name="confirm-logout-other-devices"
                    :show="$errors->has('logout_other_devices_password')"
                    focusable
                    class="max-w-lg"
                >
                    <form
                        method="POST"
                        wire:submit="logoutOtherDevices"
                        class="space-y-6"
                    >
                        <div>
                            <flux:heading size="lg"
                                >Log out other devices</flux:heading
                            >

                            <flux:subheading>
                                Please enter your password to confirm you would
                                like to sign out of all other active sessions.
                            </flux:subheading>
                        </div>

                        <flux:input
                            wire:model="logout_other_devices_password"
                            label="Password"
                            type="password"
                            viewable
                        />

                        <div
                            class="flex justify-end space-x-2 rtl:space-x-reverse"
                        >
                            <flux:modal.close>
                                <flux:button variant="filled"
                                    >Cancel</flux:button
                                >
                            </flux:modal.close>

                            <flux:button
                                variant="primary"
                                type="submit"
                                data-test="confirm-logout-other-devices-button"
                            >
                                Log out other devices
                            </flux:button>
                        </div>
                    </form>
                </flux:modal>
            </div>
        </section>
    </x-pages::settings.layout>
</section>
