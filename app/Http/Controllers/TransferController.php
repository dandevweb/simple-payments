<?php

namespace App\Http\Controllers;

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
     * @throws TransferException
     * @throws Throwable
     * @throws ConnectionException
     * @see \Tests\Feature\Transfer\TransferStoreTest
     */
    public function store(TransferRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $this->transferService->execute(
            $validated['value'],
            $validated['payer'],
            $validated['payee'],
        );

        return response()->json(['message' => 'Transfer successful.'], Response::HTTP_CREATED);
    }
}
