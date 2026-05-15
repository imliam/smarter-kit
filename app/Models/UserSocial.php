<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Model as BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

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
