<x-layouts::auth title="Log in">
    @php
        $socialProviders = config('auth.social_providers');
        $lastLoginMethod = request()->cookie('last_login_method');
    @endphp

    <div
        class="flex flex-col gap-6"
        x-data="{
            passkeyError: null,
            async signInWithPasskey() {
                this.passkeyError = null;
                try {
                    const result = await Passkeys.verify();
                    window.location.href = result.redirect ?? '/dashboard';
                } catch (e) {
                    this.passkeyError =
                        e?.message ??
                        'Passkey sign in failed. Please try again.';
                }
            },
        }"
        x-init="Passkeys.autofill()"
    >
        <x-auth-header
            title="Log in to your account"
            description="Enter your email and password below to log in"
        />

        <!-- Session Status -->
        <x-auth-session-status
            class="text-center"
            :status="session('status')"
        />

        <form
            method="POST"
            action="{{ route('login.store') }}"
            class="flex flex-col gap-6"
        >
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                label="Email address"
                :value="old('email', app()->isLocal() ? \Database\Seeders\DatabaseSeeder::DEFAULT_EMAIL : '')"
                type="email"
                required
                autofocus
                autocomplete="email webauthn"
                placeholder="email@example.com"
            />

            <!-- Password -->
            <div class="relative">
                <flux:input
                    name="password"
                    label="Password"
                    type="password"
                    required
                    autocomplete="current-password"
                    placeholder="Password"
                    viewable
                    :value="app()->isLocal() ? \Database\Seeders\DatabaseSeeder::DEFAULT_PASSWORD : ''"
                />

                @if (Route::has('password.request'))
                    <flux:link
                        class="absolute end-0 top-0 text-sm"
                        :href="route('password.request')"
                        wire:navigate
                    >
                        Forgot your password?
                    </flux:link>
                @endif
            </div>

            <!-- Remember Me -->
            <flux:checkbox
                name="remember"
                label="Remember me"
                :checked="old('remember')"
            />

            <div class="flex flex-col gap-3">
                <flux:button
                    variant="primary"
                    type="submit"
                    class="w-full"
                    data-test="login-button"
                >
                    Log in
                </flux:button>

                <div class="relative flex items-center gap-3">
                    <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                    <span class="text-xs text-zinc-400">or</span>
                    <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                </div>

                <flux:button
                    type="button"
                    class="w-full"
                    x-on:click="signInWithPasskey"
                    data-test="passkey-login-button"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="8" cy="8" r="4" />
                        <path d="M14 20v-2a4 4 0 0 0-4-4H4a4 4 0 0 0-4 4v2" />
                        <line x1="19" y1="8" x2="19" y2="14" />
                        <line x1="22" y1="11" x2="16" y2="11" />
                    </svg>
                    Sign in with passkey
                </flux:button>

                <flux:error x-show="passkeyError" x-text="passkeyError" />

                @foreach ($socialProviders as $provider)
                    <div class="relative">
                        @if ($lastLoginMethod === $provider)
                            <flux:badge
                                class="absolute -top-2 right-2 z-10"
                                size="sm"
                                color="zinc"
                                >Last used</flux:badge
                            >
                        @endif
                        <flux:button
                            tag="a"
                            href="{{ route('social-login.redirect', $provider) }}"
                            class="w-full"
                        >
                            {!! svg('simpleicon-'.$provider, 'size-4')->toHtml() !!} Sign
                            in with {{ ucfirst($provider) }}
                        </flux:button>
                    </div>
                @endforeach
            </div>
        </form>

        @if (Route::has('register'))
            <div
                class="space-x-1 text-center text-sm text-zinc-600 rtl:space-x-reverse dark:text-zinc-400"
            >
                <span>Don't have an account?</span>
                <flux:link :href="route('register')" wire:navigate
                    >Sign up</flux:link
                >
            </div>
        @endif
    </div>
</x-layouts::auth>
