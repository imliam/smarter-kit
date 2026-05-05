<?php

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('API Tokens')] class extends Component {
    public string $tokenName = '';

    /** @var list<string> */
    public array $tokenAbilities = [];

    public ?string $tokenExpiresAt = null;

    public ?string $newTokenValue = null;

    public bool $showTokenModal = false;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->tokenAbilities = $this->availableAbilities();
    }

    /**
     * Create a new personal access token.
     */
    public function createToken(): void
    {
        $this->validate([
            'tokenName' => ['required', 'string', 'max:255'],
            'tokenAbilities' => ['required', 'array', 'min:1'],
            'tokenAbilities.*' => ['string'],
            'tokenExpiresAt' => ['nullable', 'date', 'after:today'],
        ]);

        $expiresAt = $this->tokenExpiresAt ? \Illuminate\Support\Facades\Date::parse($this->tokenExpiresAt)->endOfDay() : null;

        $token = Auth::user()->createToken($this->tokenName, $this->tokenAbilities, $expiresAt);

        $this->newTokenValue = $token->plainTextToken;
        $this->showTokenModal = true;
        $this->tokenName = '';
        $this->tokenAbilities = $this->availableAbilities();
        $this->tokenExpiresAt = null;
    }

    /**
     * Revoke a single token by ID.
     */
    public function revokeToken(int $tokenId): void
    {
        Auth::user()->tokens()->whereKey($tokenId)->delete();
    }

    /**
     * Revoke all tokens for the authenticated user.
     */
    public function revokeAllTokens(): void
    {
        Auth::user()->tokens()->delete();
    }

    /**
     * Dismiss the new token modal, discarding the plain-text value.
     */
    public function dismissNewToken(): void
    {
        $this->showTokenModal = false;
        $this->newTokenValue = null;
    }

    /**
     * The user's existing personal access tokens.
     *
     * @return Collection<int, PersonalAccessToken>
     */
    #[Computed]
    public function tokens(): Collection
    {
        return Auth::user()->tokens()->latest('id')->get();
    }

    /**
     * The available token abilities from config.
     *
     * @return list<string>
     */
    private function availableAbilities(): array
    {
        $abilities = config('sanctum.abilities.default', []);

        return is_array($abilities) ? array_values($abilities) : [];
    }
}; ?>

<section class="w-full">
    @include ('partials.settings-heading')

    <x-pages::settings.layout
        heading="API Tokens"
        subheading="Manage personal access tokens for API authentication"
    >
        @php ($docsUrl = config('scribe.laravel.docs_url'))
        @if ($docsUrl)
            <div class="mb-6">
                <flux:link :href="$docsUrl" target="_blank" class="text-sm"
                    >Browse API documentation</flux:link
                >
            </div>
        @endif
        <form wire:submit="createToken" class="mt-6 space-y-6">
            <flux:input
                wire:model="tokenName"
                label="Token name"
                placeholder="e.g. my-app, ios-app"
                required
                autocomplete="off"
            />

            <flux:fieldset>
                <flux:legend>Abilities</flux:legend>
                <flux:description
                    >Select which permissions this token should
                    have.</flux:description
                >
                <flux:checkbox.group
                    wire:model="tokenAbilities"
                    class="mt-3 flex flex-col gap-2"
                >
                    @foreach ($this->availableAbilities() as $ability)
                        <flux:checkbox :value="$ability" :label="$ability" />
                    @endforeach
                </flux:checkbox.group>
                @error ('tokenAbilities')
                    <flux:error>{{ $message }}</flux:error>
                @enderror
            </flux:fieldset>

            <flux:input
                wire:model="tokenExpiresAt"
                type="date"
                label="Expires at"
                description="Leave blank for a token that never expires."
                :min="now()->addDay()->toDateString()"
            />

            <flux:button variant="primary" type="submit">
                Create token
            </flux:button>
        </form>

        {{-- Existing tokens list --}}
        @if ($this->tokens->isNotEmpty())
            <section class="mt-12">
                <div class="flex items-center justify-between">
                    <flux:heading>Active tokens</flux:heading>

                    <flux:modal.trigger name="revoke-all-tokens-modal">
                        <flux:button variant="ghost" size="sm">
                            Revoke all
                        </flux:button>
                    </flux:modal.trigger>
                </div>

                <div class="mt-4 space-y-3">
                    @foreach ($this->tokens as $token)
                        <div
                            class="flex items-center justify-between rounded-lg border border-zinc-200 px-4 py-3 dark:border-zinc-700"
                        >
                            <div class="space-y-0.5">
                                <flux:text
                                    class="font-medium"
                                    >{{ $token->name }}</flux:text
                                >
                                <flux:text size="sm" variant="subtle">
                                    Created {{ $token->created_at->diffForHumans() }}
                                    @if ($token->last_used_at)
                                        &middot; Last used {{ $token->last_used_at->diffForHumans() }}
                                    @else
                                        &middot; Never used
                                    @endif
                                    @if ($token->expires_at)
                                        &middot; Expires {{ $token->expires_at->diffForHumans() }}
                                    @endif
                                </flux:text>
                            </div>

                            <flux:button
                                wire:click="revokeToken({{ $token->id }})"
                                wire:confirm="Are you sure you want to revoke this token?"
                                variant="ghost"
                                size="sm"
                                icon="trash"
                            />
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </x-pages::settings.layout>

    {{-- New token value modal --}}
    <flux:modal wire:model="showTokenModal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Token created</flux:heading>
                <flux:subheading>
                    Copy your new token now. You won't be able to see it again.
                </flux:subheading>
            </div>

            <flux:input
                :value="$newTokenValue"
                label="Your new token"
                readonly
                copyable
                class="font-mono"
            />

            <flux:button
                wire:click="dismissNewToken"
                variant="primary"
                class="w-full"
            >
                I've copied my token
            </flux:button>
        </div>
    </flux:modal>

    {{-- Revoke all confirmation modal --}}
    <flux:modal name="revoke-all-tokens-modal" class="md:w-96">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">Revoke all tokens</flux:heading>
                <flux:subheading>
                    This will permanently revoke all API tokens. Any
                    applications using them will lose access immediately.
                </flux:subheading>
            </div>

            <div class="flex gap-3">
                <flux:modal.close>
                    <flux:button variant="ghost" class="flex-1"
                        >Cancel</flux:button
                    >
                </flux:modal.close>

                <flux:modal.close>
                    <flux:button
                        wire:click="revokeAllTokens"
                        variant="danger"
                        class="flex-1"
                    >
                        Revoke all
                    </flux:button>
                </flux:modal.close>
            </div>
        </div>
    </flux:modal>
</section>
