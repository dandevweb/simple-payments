<?php

namespace App\Models\Log;

use App\Enums\LogStatusEnum;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogBase extends Model
{
    protected $fillable = [
        'status',
        'response_message',
    ];

    protected $casts = [
        'status' => LogStatusEnum::class,
    ];

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(Transfer::class);
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payer_id');
    }
}
