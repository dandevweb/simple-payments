<?php

namespace Tests\Traits;

use Illuminate\Support\Facades\Http;

trait HasHttpMock
{
    protected function authorizerSuccessResponse(): void
    {
        Http::fake([
            config('services.authorizer.url') => Http::response([
                'data' => ['authorization' => true]], 200),
        ]);
    }

    protected function authorizerFailureResponse(): void
    {
        Http::fake([
            config('services.authorizer.url') => Http::response(['data' => ['authorization' => false]], 403),
        ]);
    }

    protected function notifySuccessResponse(): void
    {
        Http::fake([
            config('services.notifier.url') => Http::response([], 200),
        ]);
    }

    protected function notifyFailureResponse(): void
    {
        Http::fake([
            config('services.notifier.url') => Http::response(['error' => 'Service unavailable'], 500),
        ]);
    }

}
