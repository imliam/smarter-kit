<?php

use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

new class extends Component {
    public Team $team;

    public string $deleteName = '';

    public function mount(Team $team): void
    {
        $this->team = $team;
    }

    public function getDeleteConfirmLabelProperty(): string
    {
        return 'Type "'.$this->team->name.'" to confirm';
    }

    public function deleteTeam(): void
    {
        Gate::authorize('delete', $this->team);

        $validated = $this->validate([
            'deleteName' => ['required', 'string'],
        ]);

        if ($validated['deleteName'] !== $this->team->name) {
            $this->addError('deleteName', 'The team name does not match.');

            return;
        }

        $user = Auth::user();

        $fallbackTeam = $user->isCurrentTeam($this->team)
            ? $user->fallbackTeam($this->team)
            : null;

        DB::transaction(function () use ($user) {
            User::where('current_team_id', $this->team->id)
                ->where('id', '!=', $user->id)
                ->each(fn (User $affectedUser) => $affectedUser->switchTeam($affectedUser->personalTeam()));

            $this->team->invitations()->delete();
            $this->team->memberships()->delete();
            $this->team->delete();
        });

        if ($fallbackTeam) {
            $user->switchTeam($fallbackTeam);
        }

        $this->redirectRoute('teams.index', navigate: true);
    }

    /**
     * @return Collection<int, UserTeam>
     */
    public function getOtherTeamsProperty(): Collection
    {
        return Auth::user()->toUserTeams();
    }
}; ?>

<flux:modal name="delete-team" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
    <form wire:submit="deleteTeam" class="space-y-6">
        <div>
            <flux:heading size="lg">Are you sure?</flux:heading>
            <flux:subheading>
                This action cannot be undone. This will permanently delete the team "{{ $team->name }}".
            </flux:subheading>
        </div>

        <div class="space-y-4">
            <flux:input wire:model="deleteName" :label="$this->deleteConfirmLabel" required data-test="delete-team-name" />
        </div>

        <div class="flex justify-end space-x-2 rtl:space-x-reverse">
            <flux:modal.close>
                <flux:button variant="filled">Cancel</flux:button>
            </flux:modal.close>
            <flux:button variant="danger" type="submit" data-test="delete-team-confirm">
                Delete team
            </flux:button>
        </div>
    </form>
</flux:modal>
