<?php

namespace Feature\Transfer;

use App\Enums\UserTypeEnum;
use App\Models\Transfer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\HasHttpMock;

/**
 * @see \App\Http\Controllers\TransferController::store()
 */
class TransferStoreTest extends TestCase
{
    use RefreshDatabase;
    use HasHttpMock;

    #[Test]
    public function successfulTransfer(): void
    {
        $this->authorizerSuccessResponse();
        $this->notifySuccessResponse();

        $payer = $this->createUserWithBalance(UserTypeEnum::Common, 100.00);
        $payee = $this->createUserWithBalance(UserTypeEnum::Merchant, 0);

        $this->postJson(route('transfer.store'), [
            'value' => 50.00,
            'payer' => $payer->id,
            'payee' => $payee->id,
        ])->assertCreated();

        $this->assertEquals(50.00, $payer->wallet->fresh()->balance);
        $this->assertEquals(50.00, $payee->wallet->fresh()->balance);
        $this->assertDatabaseHas(Transfer::class, [
            'value' => 50.00,
            'from_wallet_id' => $payer->id,
            'to_wallet_id' => $payee->id,
        ]);
    }

    #[Test]
    public function transferFailsWhenPayerIsMerchant(): void
    {
        $this->authorizerSuccessResponse();
        $this->notifySuccessResponse();

        $payer = $this->createUserWithBalance(UserTypeEnum::Merchant, 100.00);
        $payee = $this->createUserWithBalance(UserTypeEnum::Common, 0);

        $this->postJson(route('transfer.store'), [
            'value' => 50.00,
            'payer' => $payer->id,
            'payee' => $payee->id,
        ])->assertForbidden();

        $this->assertDatabaseMissing(Transfer::class, [
            'value' => 50.00,
            'from_wallet_id' => $payer->id,
            'to_wallet_id' => $payee->id,
        ]);
    }

    #[Test]
    public function transferFailsWithInsufficientBalance(): void
    {
        $this->authorizerSuccessResponse();
        $this->notifySuccessResponse();

        $payer = $this->createUserWithBalance(UserTypeEnum::Common, 20.00);
        $payee = $this->createUserWithBalance(UserTypeEnum::Merchant, 0);

        $this->postJson(route('transfer.store'), [
            'value' => 50.00,
            'payer' => $payer->id,
            'payee' => $payee->id,
        ])->assertForbidden();
    }

    #[Test]
    public function transferFailsWhenNotAuthorized(): void
    {
        $this->authorizerFailureResponse();

        $payer = $this->createUserWithBalance(UserTypeEnum::Common, 100.00);
        $payee = $this->createUserWithBalance(UserTypeEnum::Merchant, 0);

        $this->postJson(route('transfer.store'), [
            'value' => 50.00,
            'payer' => $payer->id,
            'payee' => $payee->id,
        ])->assertForbidden();

        $this->assertEquals(100.00, $payer->wallet->fresh()->balance);
        $this->assertEquals(0.00, $payee->wallet->fresh()->balance);
    }

    #[Test]
    public function transferFailsWhenPayerAndPayeeAreTheSame(): void
    {
        $this->authorizerSuccessResponse();
        $this->notifySuccessResponse();

        $user = $this->createUserWithBalance(UserTypeEnum::Common, 100.00);

        $this->postJson(route('transfer.store'), [
            'value' => 50.00,
            'payer' => $user->id,
            'payee' => $user->id,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['payee']);
    }

    #[Test]
    #[DataProvider('invalidDataProvider')]
    public function transferFailsWithInvalidData(array $invalidData, array $errors): void
    {
        $validPayer = $this->createUserWithBalance(UserTypeEnum::Common, 0);
        $validPayee = $this->createUserWithBalance(UserTypeEnum::Merchant, 0);

        $validData = [
            'value' => 50,
            'payer' => $validPayer->id,
            'payee' => $validPayee->id,
        ];

        $data = [...$validData, ...$invalidData];

        $this->postJson(route('transfer.store'), $data)
            ->assertUnprocessable()
            ->assertJsonValidationErrors($errors);
    }

    public static function invalidDataProvider(): array
    {

        return [
            'value is null' => [
                'invalidData' => ['value' => null],
                'errors' => ['value'],
            ],
            'value is negative' => [
                'invalidData' => ['value' => -10],
                'errors' => ['value'],
            ],
            'value is zero' => [
                'invalidData' => ['value' => 0],
                'errors' => ['value'],
            ],
            'value is not numeric' => [
                'invalidData' => ['value' => 'non-numeric'],
                'errors' => ['value'],
            ],
            'payer is null' => [
                'invalidData' => ['payer' =>null],
                'errors' => ['payer'],
            ],
            'payer does not exist' => [
                'invalidData' => ['payer' => 999],
                'errors' => ['payer'],
            ],
            'payee is null' => [
                'invalidData' => ['payee' => null],
                'errors' => ['payee'],
            ],
            'payee does not exist' => [
                'invalidData' => ['payee' => 999],
                'errors' => ['payee'],
            ],
        ];
    }
}
