<?php

use App\Actions\Teams\CreateTeam;
use App\Rules\TeamName;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Create a Team')] #[Layout('layouts.auth')] class extends Component {
    public string $name = '';

    public function createTeam(CreateTeam $createTeam): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255', new TeamName],
        ]);

        $team = $createTeam->handle(Auth::user(), $validated['name'], isPersonal: true);

        $this->redirectRoute('dashboard', ['current_team' => $team->slug], navigate: true);
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header
        title="Create your first team"
        description="Teams let you collaborate and manage projects together."
    />

    <form wire:submit="createTeam" class="space-y-6">
        <flux:field>
            <flux:label>Team name</flux:label>
            <flux:input
                wire:model="name"
                type="text"
                placeholder="My Team"
                autofocus
                autocomplete="off"
                data-test="create-team-name-input"
            />
            <flux:error name="name" />
        </flux:field>

        <flux:button
            type="submit"
            variant="primary"
            class="w-full"
            data-test="create-team-submit"
        >
            Create team
        </flux:button>
    </form>
</div>
