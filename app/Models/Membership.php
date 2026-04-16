<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TeamRole;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Override;

class Membership extends Pivot
{
    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    #[Override]
    public $incrementing = true;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    #[Override]
    protected $table = 'team_members';

    /**
     * Get the team that the membership belongs to.
     *
     * @return BelongsTo<Team, $this>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the user that belongs to this membership.
     *
     * @return BelongsTo<Model, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
        ];
    }
}
