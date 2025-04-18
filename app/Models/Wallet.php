<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property-read int $user_id
 * @property-read int $balance
 * @property-read User $user
 */
class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'balance',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
