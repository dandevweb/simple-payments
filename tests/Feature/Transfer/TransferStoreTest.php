<?php

namespace Feature\Transfer;

use App\Enums\UserTypeEnum;
use App\Models\User;
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

        /** @var User $payer */
        $payer = User::factory()->create(['type' => UserTypeEnum::Common]);
        $payer->wallet()->create(['balance' => 100.00]);

        /** @var User $payee */
        $payee = User::factory()->create(['type' => UserTypeEnum::Merchant]);
        $payee->wallet()->create(['balance' => 0]);

        $response = $this->postJson(route('transfer.store'), [
            'value' => 50.00,
            'payer' => $payer->id,
            'payee' => $payee->id,
        ]);

        $response->assertStatus(201);

        $this->assertEquals(50.00, $payer->wallet->fresh()->balance);
        $this->assertEquals(50.00, $payee->wallet->fresh()->balance);
    }

    #[Test]
    public function transferFailsWhenPayerIsMerchant(): void
    {
        $this->authorizerSuccessResponse();
        $this->notifySuccessResponse();

        /** @var User $payer */
        $payer = User::factory()->create(['type' => UserTypeEnum::Merchant]);
        $payer->wallet()->create(['balance' => 100.00]);

        /** @var User $payee */
        $payee = User::factory()->create(['type' => UserTypeEnum::Common]);
        $payee->wallet()->create(['balance' => 0]);

        $response = $this->postJson(route('transfer.store'), [
            'value' => 50.00,
            'payer' => $payer->id,
            'payee' => $payee->id,
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function transferFailsWithInsufficientBalance(): void
    {
        $this->authorizerSuccessResponse();
        $this->notifySuccessResponse();

        /** @var User $payer */
        $payer = User::factory()->create(['type' => UserTypeEnum::Common]);
        $payer->wallet()->create(['balance' => 20.00]);

        /** @var User $payee */
        $payee = User::factory()->create(['type' => UserTypeEnum::Merchant]);
        $payee->wallet()->create(['balance' => 0]);

        $response = $this->postJson(route('transfer.store'), [
            'value' => 50.00,
            'payer' => $payer->id,
            'payee' => $payee->id,
        ]);

        $response->assertStatus(400);
    }

    #[Test]
    public function transferFailsWhenNotAuthorized(): void
    {
        $this->authorizerFailureResponse();

        /** @var User $payer */
        $payer = User::factory()->create(['type' => UserTypeEnum::Common]);
        $payer->wallet()->create(['balance' => 100.00]);

        /** @var User $payee */
        $payee = User::factory()->create(['type' => UserTypeEnum::Merchant]);
        $payee->wallet()->create(['balance' => 0]);

        $response = $this->postJson(route('transfer.store'), [
            'value' => 50.00,
            'payer' => $payer->id,
            'payee' => $payee->id,
        ]);

        $response->assertStatus(403);

        $this->assertEquals(100.00, $payer->wallet->fresh()->balance);
        $this->assertEquals(0.00, $payee->wallet->fresh()->balance);
    }

    #[Test]
    public function transferFailsWhenNotificationFails(): void
    {
        $this->authorizerSuccessResponse();
        $this->notifyFailureResponse();

        /** @var User $payer */
        $payer = User::factory()->create(['type' => UserTypeEnum::Common]);
        $payer->wallet()->create(['balance' => 100.00]);

        /** @var User $payee */
        $payee = User::factory()->create(['type' => UserTypeEnum::Merchant]);
        $payee->wallet()->create(['balance' => 0]);

        $response = $this->postJson(route('transfer.store'), [
            'value' => 50.00,
            'payer' => $payer->id,
            'payee' => $payee->id,
        ]);

        $response->assertStatus(500);

        $this->assertEquals(100.00, $payer->wallet->fresh()->balance);
        $this->assertEquals(0.00, $payee->wallet->fresh()->balance);
    }

    #[Test]
    public function transferFailsWhenPayerAndPayeeAreTheSame(): void
    {
        $this->authorizerSuccessResponse();
        $this->notifySuccessResponse();

        /** @var User $user */
        $user = User::factory()->create(['type' => UserTypeEnum::Common]);
        $user->wallet()->create(['balance' => 100.00]);

        $response = $this->postJson(route('transfer.store'), [
            'value' => 50.00,
            'payer' => $user->id,
            'payee' => $user->id,
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['payee']);
    }

    #[Test]
    #[DataProvider('invalidDataProvider')]
    public function transferFailsWithInvalidData(array $invalidData, array $errors): void
    {
        $validPayer = User::factory()->create(['type' => UserTypeEnum::Common]);
        $validPayee = User::factory()->create(['type' => UserTypeEnum::Merchant]);

        $validData = [
            'value' => 50,
            'payer' => $validPayer->id,
            'payee' => $validPayee->id,
        ];

        $data = array_merge($validData, $invalidData);

        $response = $this->postJson(route('transfer.store'), $data);

        $response->assertUnprocessable()
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
