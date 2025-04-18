<?php

namespace App\Repositories;

use App\Models\AuthorizationLog;
use App\Repositories\Interfaces\AuthorizationLogRepositoryInterface;

class AuthorizationLogRepository implements AuthorizationLogRepositoryInterface
{
    public function createLog(array $data): AuthorizationLog
    {
        $log = AuthorizationLog::query()->make($data);

        if (data_get($data, 'payer_id')) {
            $log->payer()->associate($data['payer_id']);
        }

        $log->save();

        return $log;
    }
}
