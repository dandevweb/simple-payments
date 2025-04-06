<?php

namespace Tests\Unit;

use App\Enums\UserTypeEnum;
use App\Exceptions\TransferException;
use App\Models\User;
use App\Services\TransferService;
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

    #[Test]
    public function merchantCannotSendMoney(): void
    {
        $payer = User::factory()->create(['type' => UserTypeEnum::Merchant]);
        $payer->wallet()->create(['balance' => 100.00]);
        $payee = User::factory()->create(['type' => UserTypeEnum::Common]);
        $payee->wallet()->create(['balance' => 0]);

        $this->expectException(TransferException::class);
        $this->expectExceptionMessage('Merchant cannot sendo money.');

        $this->transferService->execute(50.00, $payer->id, $payee->id);
    }

    #[Test]
    public function insufficientBalanceFails(): void
    {
        $payer = User::factory()->create(['type' => UserTypeEnum::Common]);
        $payer->wallet()->create(['balance' => 20.00]);
        $payee = User::factory()->create(['type' => UserTypeEnum::Common]);
        $payee->wallet()->create(['balance' => 0]);

        $this->expectException(TransferException::class);
        $this->expectExceptionMessage('Insufficient balance.');

        $this->transferService->execute(50.00, $payer->id, $payee->id);
    }

    #[Test]
    public function successfulTransferWithAuthorization(): void
    {
        $this->authorizerSuccessResponse();
        $this->notifySuccessResponse();

        $payer = User::factory()->create(['type' => UserTypeEnum::Common]);
        $payer->wallet()->create(['balance' => 100.00]);
        $payee = User::factory()->create(['type' => UserTypeEnum::Common]);
        $payee->wallet()->create(['balance' => 0]);

        $this->transferService->execute(50.00, $payer->id, $payee->id);

        $this->assertEquals(50.00, $payer->wallet->fresh()->balance);
        $this->assertEquals(50.00, $payee->wallet->fresh()->balance);
    }

    #[Test]
    public function transferFailsIfNotAuthorized(): void
    {
        $this->authorizerFailureResponse();

        $payer = User::factory()->create(['type' => UserTypeEnum::Common]);
        $payer->wallet()->create(['balance' => 100.00]);
        $payee = User::factory()->create(['type' => UserTypeEnum::Common]);
        $payee->wallet()->create(['balance' => 0]);

        $this->expectException(TransferException::class);
        $this->expectExceptionMessage('Unauthorized transfer.');

        $this->transferService->execute(50.00, $payer->id, $payee->id);
    }

    #[Test]
    public function transferFailsIfNotificationFails(): void
    {
        $this->authorizerSuccessResponse();
        $this->notifyFailureResponse();

        $payer = User::factory()->create(['type' => UserTypeEnum::Common]);
        $payer->wallet()->create(['balance' => 100.00]);
        $payee = User::factory()->create(['type' => UserTypeEnum::Common]);
        $payee->wallet()->create(['balance' => 0]);

        $this->expectException(TransferException::class);
        $this->expectExceptionMessage('Failed to send notification.');

        $this->transferService->execute(50.00, $payer->id, $payee->id);

        $this->assertEquals(100.00, $payer->wallet->fresh()->balance);
        $this->assertEquals(0, $payee->wallet->fresh()->balance);
    }

    #[Test]
    public function transferFailsWithZeroOrNegativeValue(): void
    {
        $payer = User::factory()->create(['type' => UserTypeEnum::Common]);
        $payer->wallet()->create(['balance' => 100.00]);
        $payee = User::factory()->create(['type' => UserTypeEnum::Common]);
        $payee->wallet()->create(['balance' => 0]);

        $this->expectException(TransferException::class);
        $this->expectExceptionMessage('Unauthorized transfer.');

        // Testa valor zero
        $this->transferService->execute(0, $payer->id, $payee->id);

        // Testa valor negativo
        $this->transferService->execute(-50.00, $payer->id, $payee->id);
    }

    #[Test]
    public function transferFailsWithNonExistentPayer(): void
    {
        $payee = User::factory()->create(['type' => UserTypeEnum::Common]);
        $payee->wallet()->create(['balance' => 0]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->transferService->execute(50.00, 999, $payee->id);
    }

    #[Test]
    public function transferFailsWithNonExistentPayee(): void
    {
        $payer = User::factory()->create(['type' => UserTypeEnum::Common]);
        $payer->wallet()->create(['balance' => 100.00]);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);

        $this->transferService->execute(50.00, $payer->id, 999);
    }

}
