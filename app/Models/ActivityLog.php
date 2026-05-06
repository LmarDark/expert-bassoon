<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $actor_id
 * @property string $event
 * @property string|null $target_username
 * @property string|null $ip_address
 * @property \Illuminate\Support\Carbon $created_at
 */
final class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['actor_id', 'event', 'target_username', 'ip_address'];

    /** @return BelongsTo<User, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
