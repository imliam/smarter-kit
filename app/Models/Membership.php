<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\TeamRole;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Override;

/**
 * @property string $id
 * @property string $team_id
 * @property string $user_id
 * @property TeamRole $role
 * @property CarbonImmutable|null $created_at
 * @property CarbonImmutable|null $updated_at
 * @property-read Team|null $team
 * @property-read User $user
 *
 * @mixin Model
 */
class Membership extends Pivot
{
    /** @use HasFactory<Factory<static>> */
    use HasFactory;

    use HasUuids;

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
     * @return BelongsTo<User, $this>
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
