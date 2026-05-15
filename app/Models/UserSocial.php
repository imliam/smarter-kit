<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Model as BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int $id
 * @property int $user_id
 * @property string $social_id
 * @property string $service
 * @property string|null $token
 * @property string|null $token_secret
 * @property string|null $refresh_token
 */
class UserSocial extends BaseModel
{
    #[Override]
    protected $table = 'user_socials';

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
