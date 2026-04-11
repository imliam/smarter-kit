<x-layouts::auth title="Reset password">
    <div class="flex flex-col gap-6">
        <x-auth-header title="Reset password" description="Please enter your new password below" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-6">
            @csrf
            <!-- Token -->
            <input type="hidden" name="token" value="{{ request()->route('token') }}">

            <!-- Email Address -->
            <flux:input
                name="email"
                value="{{ request('email') }}"
                label="Email"
                type="email"
                required
                autocomplete="email"
            />

            <!-- Password -->
            <flux:input
                name="password"
                label="Password"
                type="password"
                required
                autocomplete="new-password"
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
                placeholder="Confirm password"
                viewable
            />

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full" data-test="reset-password-button">
                    Reset password
                </flux:button>
            </div>
        </form>
    </div>
</x-layouts::auth>
