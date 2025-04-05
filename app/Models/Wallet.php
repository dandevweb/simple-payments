<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wallet extends Model
{
    use HasFactory;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function balance(): Attribute
    {
        return Attribute::make(
            get: static fn ($value) => number_format($value / 100, 2, ',', '.'),
            set: static fn ($value) => $value * 100
        );
    }

}
