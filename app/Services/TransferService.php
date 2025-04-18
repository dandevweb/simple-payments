<?php

namespace App\Services;

use App\Enums\LogStatusEnum;
use App\Exceptions\AuthorizationException;
use App\Exceptions\TransferException;
use App\Models\Log\AuthorizationLog;
use App\Models\Log\TransferLog;
use App\Models\Transfer;
use App\Models\User;
use App\Repositories\Interfaces\LogRepositoryInterface;
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
        protected LogRepositoryInterface $logRepository,
    ) {}

    /**
     * @throws TransferException
     * @throws ConnectionException|Throwable
     */
    public function execute(float $value, int $payerId, int $payeeId): void
    {
        $transferLog = $this->logRepository->saveLog(new TransferLog, [
            'payer_id' => $payerId,
            'payee_id' => $payeeId,
            'value' => $value,
            'status' => LogStatusEnum::Pending,
        ]);

        try {
            $payer = $this->userRepository->findUserWithBalance($payerId);
            $payee = $this->userRepository->findUserWithBalance($payeeId);

            if ($payer->isMerchant()) {
                throw new TransferException('Merchant cannot send money.', Response::HTTP_FORBIDDEN);
            }

            if ($this->walletRepository->getBalance($payer->wallet) < $value) {
                throw new TransferException('Insufficient balance.', Response::HTTP_FORBIDDEN);
            }

            $authLog = $this->authorizer($payerId);

            $transfer = $this->createTransaction($value, $payer, $payee);

            $this->logRepository->saveLog($authLog, [
                'status' => LogStatusEnum::Success,
                'transfer_id' => $transfer->id,
            ]);

            $this->logRepository->saveLog($transferLog, [
                'status' => LogStatusEnum::Success,
                'transfer_id' => $transfer->id,
            ]);

        } catch (Throwable $e) {
            $this->logRepository->saveLog($transferLog, [
                'status' => LogStatusEnum::Fail,
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * @throws ConnectionException
     * @throws AuthorizationException
     */
    private function authorizer(int $payerId): AuthorizationLog
    {
        $authResponse = Http::get(config('services.authorizer.url'));

        $log = $this->logRepository->saveLog(new AuthorizationLog, [
            'payer_id' => $payerId,
            'status' => $authResponse->successful()
                ? LogStatusEnum::Success
                : LogStatusEnum::Fail,
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
    private function createTransaction(float $value, User $payer, User $payee,): Transfer
    {
        return DB::transaction(function () use ($value, $payer, $payee) {
            $this->walletRepository->decrementBalance($payer->wallet, $value);
            $this->walletRepository->incrementBalance($payee->wallet, $value);

            return $this->transferRepository->create($payer, $payee, $value);
        });
    }
}
