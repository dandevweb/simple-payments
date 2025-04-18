<?php

namespace App\Models\Log;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferLog extends LogBase
{
    protected $fillable = [
        'value',
        'status',
        'error_message',
    ];

    public function payee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'payee_id');
    }
}
