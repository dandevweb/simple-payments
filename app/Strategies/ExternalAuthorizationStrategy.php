<?php

namespace App\Strategies;

use App\Strategies\Interfaces\AuthorizationStrategy;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class ExternalAuthorizationStrategy implements AuthorizationStrategy
{
    /**
     * @throws ConnectionException
     */
    public static function authorize(): bool
    {
        $authResponse = Http::get(config('services.authorizer.url'));
        return $authResponse->successful() && $authResponse->json('data.authorization') === true;
    }
}
