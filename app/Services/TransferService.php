<?php

namespace App\Services;

use App\Exceptions\TransferException;
use App\Models\Transfer;
use App\Models\User;
use App\Repositories\WalletRepository;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * @see \Tests\Unit\TransferServiceTest
 */
class TransferService
{
    public function __construct(protected WalletRepository $walletRepository) {}

    /**
     * @throws TransferException
     * @throws ConnectionException|Throwable
     */
    public function execute(float $value, int $payerId, int $payeeId): void
    {
        $payer = User::with('wallet')->findOrFail($payerId);
        $payee = User::with('wallet')->findOrFail($payeeId);

        if ($payer->isMerchant()) {
            throw new TransferException('Merchant cannot sendo money.', 403);
        }

        if ($this->walletRepository->getBalance($payer->wallet) < $value) {
            throw new TransferException('Insufficient balance.', 400);
        }

        $authResponse = Http::get('https://util.devi.tools/api/v2/authorize');
        if ($authResponse->failed() || ($authResponse->json('data.authorization') !== true)) {
            throw new TransferException('Unauthorized transfer.', 403);
        }

        DB::transaction(function () use ($value, $payer, $payee) {
            $this->walletRepository->decrementBalance($payer->wallet, $value);
            $this->walletRepository->incrementBalance($payee->wallet, $value);

            $notifyResponse = Http::post('https://util.devi.tools/api/v1/notify', [
                'user_id' => $payee->id,
                'message' => "Você recebeu R$ {$value} de {$payer->name}",
            ]);

            if ($notifyResponse->failed()) {
                throw new TransferException('Failed to send notification.', 500);
            }

            Transfer::query()->create([
                'from_wallet_id' => $payer->id,
                'to_wallet_id' => $payee->id,
                'value' => $value,
            ]);
        });
    }
}
