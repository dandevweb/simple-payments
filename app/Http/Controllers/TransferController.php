<?php

namespace App\Http\Controllers;

use App\Exceptions\AuthorizationException;
use App\Exceptions\TransferException;
use App\Http\Requests\TransferRequest;
use App\Services\TransferService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class TransferController extends Controller
{
    public function __construct(protected TransferService $transferService) {}

    /**
     * @throws Throwable
     * @see \Tests\Feature\Transfer\TransferStoreTest
     */
    public function store(TransferRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $this->transferService->execute(
                $validated['value'],
                $validated['payer'],
                $validated['payee'],
            );

            return response()->json(['message' => 'Transfer successful.'], Response::HTTP_CREATED);

        } catch (TransferException|AuthorizationException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getStatusCode());

        } catch (ConnectionException $e) {
            return response()->json(['error' => 'Failed to connect to the authorizer service.'], $e->getCode());

        } catch (Throwable $e) {
            return response()->json(['error' => 'An unexpected error occurred.'], $e->getCode());
        }
    }
}
