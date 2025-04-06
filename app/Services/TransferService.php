<?php

namespace App\Services;

use App\Exceptions\TransferException;
use App\Models\Transfer;
use App\Models\User;
use App\Repositories\WalletRepository;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;
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
        $payer = $this->getUserWhitBalance($payerId);
        $payee = $this->getUserWhitBalance($payeeId);

        if ($payer->isMerchant()) {
            throw new TransferException('Merchant cannot send money.', Response::HTTP_FORBIDDEN);
        }

        if ($this->walletRepository->getBalance($payer->wallet) < $value) {
            throw new TransferException('Insufficient balance.', Response::HTTP_FORBIDDEN);
        }

        $this->authorizer();

        $this->createTransaction($value, $payer, $payee);
    }

    private function getUserWhitBalance(int $payerId): User
    {
        return User::query()
            ->with('wallet:id,balance,user_id')
            ->findOrFail($payerId);
    }

    /**
     * @throws ConnectionException
     * @throws TransferException
     */
    private function authorizer(): void
    {
        $authResponse = Http::get(config('services.authorizer.url'));
        if ($authResponse->failed() || ($authResponse->json('data.authorization') !== true)) {
            throw new TransferException('Unauthorized transfer.', 403);
        }
    }

    /**
     * @throws Throwable
     */
    private function createTransaction(float $value, User $payer, User $payee): void
    {
        DB::transaction(function () use ($value, $payer, $payee) {
            $this->walletRepository->decrementBalance($payer->wallet, $value);
            $this->walletRepository->incrementBalance($payee->wallet, $value);

            $this->createTransferHistory($payer, $payee, $value);
        });
    }

    private function createTransferHistory(User $payer, User $payee, float $value): void
    {
        Transfer::query()->create([
            'from_wallet_id' => $payer->id,
            'to_wallet_id' => $payee->id,
            'value' => $value,
        ]);
    }
}
