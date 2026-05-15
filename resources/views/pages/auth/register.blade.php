<x-layouts::auth title="Register">
    @php
        $socialProviders = config('auth.social_providers');
        $lastLoginMethod = request()->cookie('last_login_method');
    @endphp

    <div class="flex flex-col gap-6">
        <x-auth-header
            title="Create an account"
            description="Enter your details below to create your account"
        />

        <!-- Session Status -->
        <x-auth-session-status
            class="text-center"
            :status="session('status')"
        />

        <form
            method="POST"
            action="{{ route('register.store') }}"
            class="flex flex-col gap-6"
        >
            @csrf
            <!-- Name -->
            <flux:input
                name="name"
                label="Name"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                placeholder="Full name"
            />

            <!-- Email Address -->
            <flux:input
                name="email"
                label="Email address"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
            />

            @php $passwordRules = \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString(); @endphp

            <!-- Password -->
            <flux:input
                name="password"
                label="Password"
                type="password"
                required
                autocomplete="new-password"
                passwordrules="{{ $passwordRules }}"
                placeholder="Password"
                viewable
            />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                label="Confirm password"
                type="password"
                required
                autocomplete="new-password"
                passwordrules="{{ $passwordRules }}"
                placeholder="Confirm password"
                viewable
            />

            <div class="flex items-center justify-end">
                <flux:button
                    type="submit"
                    variant="primary"
                    class="w-full"
                    data-test="register-user-button"
                >
                    Create account
                </flux:button>
            </div>
        </form>

        @if ($socialProviders)
            <div class="flex flex-col gap-3">
                <div class="relative flex items-center gap-3">
                    <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                    <span class="text-xs text-zinc-400">or</span>
                    <div class="h-px flex-1 bg-zinc-200 dark:bg-zinc-700"></div>
                </div>

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
                            up with {{ ucfirst($provider) }}
                        </flux:button>
                    </div>
                @endforeach
            </div>
        @endif

        <div
            class="space-x-1 text-center text-sm text-zinc-600 rtl:space-x-reverse dark:text-zinc-400"
        >
            <span>Already have an account?</span>
            <flux:link :href="route('login')" wire:navigate>Log in</flux:link>
        </div>
    </div>
</x-layouts::auth>
