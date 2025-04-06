<?php

namespace Tests\Traits;

use Illuminate\Support\Facades\Http;

trait HasHttpMock
{
    protected function authorizerSuccessResponse(): void
    {
        Http::fake([
            'https://util.devi.tools/api/v2/authorize' => Http::response(['data' => ['authorization' => true]], 200),
        ]);
    }

    protected function authorizerFailureResponse(): void
    {
        Http::fake([
            'https://util.devi.tools/api/v2/authorize' => Http::response(['data' => ['authorization' => false]], 200),
        ]);
    }

    protected function notifySuccessResponse(): void
    {
        Http::fake([
            'https://util.devi.tools/api/v1/notify' => Http::response([], 200),
        ]);
    }

    protected function notifyFailureResponse(): void
    {
        Http::fake([
            'https://util.devi.tools/api/v1/notify' => Http::response(['error' => 'Service unavailable'], 500),
        ]);
    }

}
