<?php

namespace Tests\Unit;

use App\Enums\UserTypeEnum;
use App\Exceptions\TransferException;
use App\Models\User;
use App\Services\TransferService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\HasHttpMock;
use Throwable;

/**
 * @see \app\Services\TransferService
 */
class TransferServiceTest extends TestCase
{
    use RefreshDatabase;
    use HasHttpMock;

    protected TransferService $transferService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->transferService = app(TransferService::class);
    }

    /**
     * @throws Throwable
     * @throws ConnectionException
     */
    #[Test]
    public function merchantCannotSendMoney(): void
    {
        $payer = $this->createUserWithBalance(UserTypeEnum::Merchant, 100.00);
        $payee = $this->createUserWithBalance(UserTypeEnum::Common, 0);

        $this->expectException(TransferException::class);
        $this->expectExceptionMessage('Merchant cannot send money.');

        $this->transferService->execute(50.00, $payer->id, $payee->id);
    }

    /**
     * @throws Throwable
     * @throws ConnectionException
     */
    #[Test]
    public function insufficientBalanceFails(): void
    {
        $payer = $this->createUserWithBalance(UserTypeEnum::Common, 20.00);
        $payee = $this->createUserWithBalance(UserTypeEnum::Common, 0);

        $this->expectException(TransferException::class);
        $this->expectExceptionMessage('Insufficient balance.');

        $this->transferService->execute(50.00, $payer->id, $payee->id);
    }

    /**
     * @throws TransferException
     * @throws Throwable
     * @throws ConnectionException
     */
    #[Test]
    public function successfulTransferWithAuthorization(): void
    {
        $this->authorizerSuccessResponse();
        $this->notifySuccessResponse();

        $payer = $this->createUserWithBalance(UserTypeEnum::Common, 100.00);
        $payee = $this->createUserWithBalance(UserTypeEnum::Common, 0);

        $this->transferService->execute(50.00, $payer->id, $payee->id);

        $this->assertEquals(50.00, $payer->wallet->fresh()->balance);
        $this->assertEquals(50.00, $payee->wallet->fresh()->balance);
    }

    /**
     * @throws Throwable
     * @throws ConnectionException
     */
    #[Test]
    public function transferFailsIfNotAuthorized(): void
    {
        $this->authorizerFailureResponse();

        $payer = $this->createUserWithBalance(UserTypeEnum::Common, 100.00);
        $payee = $this->createUserWithBalance(UserTypeEnum::Common, 0);

        $this->expectException(TransferException::class);
        $this->expectExceptionMessage('Unauthorized transfer.');

        $this->transferService->execute(50.00, $payer->id, $payee->id);
    }

    /**
     * @throws TransferException
     * @throws Throwable
     * @throws ConnectionException
     */
    #[Test]
    public function transferFailsWithNonExistentPayer(): void
    {
        $payee = $this->createUserWithBalance(UserTypeEnum::Common, 0);

        $this->expectException(ModelNotFoundException::class);

        $this->transferService->execute(50.00, 999, $payee->id);
    }

    /**
     * @throws TransferException
     * @throws Throwable
     * @throws ConnectionException
     */
    #[Test]
    public function transferFailsWithNonExistentPayee(): void
    {
        $payer = $this->createUserWithBalance(UserTypeEnum::Common, 100.00);

        $this->expectException(ModelNotFoundException::class);

        $this->transferService->execute(50.00, $payer->id, 999);
    }

}
