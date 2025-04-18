<?php

namespace App\Services;

use App\Exceptions\TransferException;
use App\Models\User;
use App\Repositories\Interfaces\TransferRepositoryInterface;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\Interfaces\WalletRepositoryInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class TransferService
{
    public function __construct(
        protected WalletRepositoryInterface $walletRepository,
        protected UserRepositoryInterface $userRepository,
        protected TransferRepositoryInterface $transferRepository
    ) {}

    /**
     * @throws TransferException
     * @throws ConnectionException|Throwable
     */
    public function execute(float $value, int $payerId, int $payeeId): void
    {
        $payer = $this->userRepository->findUserWithBalance($payerId);
        $payee = $this->userRepository->findUserWithBalance($payeeId);

        if ($payer->isMerchant()) {
            throw new TransferException('Merchant cannot send money.', Response::HTTP_FORBIDDEN);
        }

        if ($this->walletRepository->getBalance($payer->wallet) < $value) {
            throw new TransferException('Insufficient balance.', Response::HTTP_FORBIDDEN);
        }

        $this->authorizer();

        $this->createTransaction($value, $payer, $payee);
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

            $this->transferRepository->createTransfer($payer, $payee, $value);
        });
    }
}
