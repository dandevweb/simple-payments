<?php

namespace App\Services;

use App\Enums\AuthorizationLogStatusEnum;
use App\Exceptions\AuthorizationException;
use App\Exceptions\TransferException;
use App\Models\AuthorizationLog;
use App\Models\User;
use App\Repositories\Interfaces\AuthorizationLogRepositoryInterface;
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
        protected TransferRepositoryInterface $transferRepository,
        protected AuthorizationLogRepositoryInterface $authorizationLogRepository,
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

        $log = $this->authorizer($payerId);

        $this->createTransaction($value, $payer, $payee, $log);
    }

    /**
     * @throws ConnectionException
     * @throws AuthorizationException
     */
    private function authorizer(int $payerId): AuthorizationLog
    {
        $authResponse = Http::get(config('services.authorizer.url'));

        $log = $this->authorizationLogRepository->createLog([
            'payer_id' => $payerId,
            'status' => $authResponse->successful()
                ? AuthorizationLogStatusEnum::Success
                : AuthorizationLogStatusEnum::Fail,
            'response_message' => $authResponse->json('data.status'),
        ]);

        if ($authResponse->failed() || ($authResponse->json('data.authorization') !== true)) {
            throw new AuthorizationException('Unauthorized transfer.');
        }

        return $log;
    }

    /**
     * @throws Throwable
     */
    private function createTransaction(float $value, User $payer, User $payee, AuthorizationLog $log): void
    {
        DB::transaction(function () use ($value, $payer, $payee, $log) {
            $this->walletRepository->decrementBalance($payer->wallet, $value);
            $this->walletRepository->incrementBalance($payee->wallet, $value);

            $this->transferRepository->create($payer, $payee, $value);

            $log->transfer()->associate($this->transferRepository->create($payer, $payee, $value));
        });
    }
}
