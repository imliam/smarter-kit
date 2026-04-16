<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TeamRole;
use Database\Factories\TeamInvitationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Override;

class TeamInvitation extends Model
{
    /** @use HasFactory<TeamInvitationFactory> */
    use HasFactory;

    /**
     * Get the team that the invitation belongs to.
     *
     * @return BelongsTo<Team, $this>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the user who sent the invitation.
     *
     * @return BelongsTo<Model, $this>
     */
    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /** Determine if the invitation has been accepted. */
    public function isAccepted(): bool
    {
        return $this->accepted_at !== null;
    }

    /** Determine if the invitation is pending. */
    public function isPending(): bool
    {
        return $this->accepted_at === null && ! $this->isExpired();
    }

    /** Determine if the invitation has expired. */
    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    /** Get the route key for the model. */
    #[Override]
    public function getRouteKeyName(): string
    {
        return 'code';
    }

    /** Bootstrap the model and its traits. */
    #[Override]
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (TeamInvitation $invitation): void {
            if (empty($invitation->code)) {
                $invitation->code = Str::random(64);
            }
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'role' => TeamRole::class,
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }
}
