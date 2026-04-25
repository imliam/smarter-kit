<?php

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Profile settings')] class extends Component {
    use ProfileValidationRules;
    use WithFileUploads;

    public string $name = '';

    public string $email = '';

    public $avatar;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the authenticated user's avatar.
     */
    public function updateAvatar(): void
    {
        $this->validate([
            'avatar' => ['required', 'image', 'max:2048', 'mimes:jpeg,png,webp'],
        ]);

        $user = Auth::user();

        if ($user->avatar_url) {
            Storage::disk('public')->delete($user->avatar_url);
        }

        $user->avatar_url = $this->avatar->store('avatars', 'public');
        $user->save();

        $this->avatar = null;

        $this->dispatch('avatar-updated');
    }

    /**
     * Remove the authenticated user's avatar.
     */
    public function removeAvatar(): void
    {
        $user = Auth::user();

        if ($user->avatar_url) {
            Storage::disk('public')->delete($user->avatar_url);
            $user->avatar_url = null;
            $user->save();
        }

        $this->dispatch('avatar-updated');
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate($this->profileRules($user->id));

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return ! Auth::user() instanceof MustVerifyEmail
            || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
    }
}; ?>

<section class="w-full">
    @include ('partials.settings-heading')

    <flux:heading class="sr-only">Profile settings</flux:heading>

    <x-pages::settings.layout
        heading="Profile"
        subheading="Update your name and email address"
    >
        <div class="my-6 space-y-4">
            <flux:heading size="sm">Avatar</flux:heading>

            <div class="flex items-center gap-6">
                <flux:avatar
                    :src="auth()->user()->avatarUrl()"
                    :initials="auth()->user()->initials()"
                    size="xl"
                />

                <div class="space-y-3">
                    <form wire:submit="updateAvatar" class="space-y-2">
                        <flux:field>
                            <flux:input
                                type="file"
                                wire:model="avatar"
                                accept="image/jpeg,image/png,image/webp"
                            />
                            <flux:error name="avatar" />
                        </flux:field>

                        <div class="flex items-center gap-2">
                            <flux:button
                                type="submit"
                                variant="primary"
                                size="sm"
                                wire:loading.attr="disabled"
                                wire:target="avatar,updateAvatar"
                            >
                                Save avatar
                            </flux:button>

                            @if (auth()->user()->avatar_url)
                                <flux:button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    wire:click="removeAvatar"
                                    wire:confirm="Are you sure you want to remove your avatar?"
                                >
                                    Remove
                                </flux:button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <flux:separator />

        <form
            wire:submit="updateProfileInformation"
            class="my-6 w-full space-y-6"
        >
            <flux:input
                wire:model="name"
                label="Name"
                type="text"
                required
                autofocus
                autocomplete="name"
            />

            <div>
                <flux:input
                    wire:model="email"
                    label="Email"
                    type="email"
                    required
                    autocomplete="email"
                />

                @if ($this->hasUnverifiedEmail)
                    <div>
                        <flux:text class="mt-4">
                            Your email address is unverified.

                            <flux:link
                                class="cursor-pointer text-sm"
                                wire:click.prevent="resendVerificationNotification"
                            >
                                Click here to re-send the verification email.
                            </flux:link>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                            <flux:text
                                class="!dark:text-green-400 mt-2 font-medium !text-green-600"
                            >
                                A new verification link has been sent to your
                                email address.
                            </flux:text>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button
                        variant="primary"
                        type="submit"
                        class="w-full"
                        data-test="update-profile-button"
                    >
                        Save
                    </flux:button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    Saved.
                </x-action-message>
            </div>
        </form>

        @if ($this->showDeleteUser)
            <livewire:pages::settings.delete-user-form />
        @endif
    </x-pages::settings.layout>
</section>
