<?php

namespace App\Models;

use App\Observers\TransferObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property-read  int $id
 * @property-read  int $from_wallet_id
 * @property-read  int $to_wallet_id
 * @property-read  float $value
 * @property-read  Carbon $transferred_at
 * +@property-read  Wallet $fromWallet
 * +@property-read  Wallet $toWallet
 *
 */
#[ObservedBy(TransferObserver::class)]
class Transfer extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'from_wallet_id',
        'to_wallet_id',
        'value',
    ];

    protected $casts = [
        'transferred_at' => 'datetime',
    ];

    public function fromWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'from_wallet_id');
    }

    public function toWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'to_wallet_id');
    }
}
