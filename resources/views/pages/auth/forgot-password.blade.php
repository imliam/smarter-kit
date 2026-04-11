<x-layouts::auth title="Forgot password">
    <div class="flex flex-col gap-6">
        <x-auth-header title="Forgot password" description="Enter your email to receive a password reset link" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                label="Email address"
                type="email"
                required
                autofocus
                placeholder="email@example.com"
            />

            <flux:button variant="primary" type="submit" class="w-full" data-test="email-password-reset-link-button">
                Email password reset link
            </flux:button>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-400">
            <span>Or, return to</span>
            <flux:link :href="route('login')" wire:navigate>log in</flux:link>
        </div>
    </div>
</x-layouts::auth>
