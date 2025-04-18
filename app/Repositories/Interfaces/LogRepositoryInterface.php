<?php

namespace App\Repositories\Interfaces;

use App\Models\Log\AuthorizationLog;
use App\Models\Log\TransferLog;

interface LogRepositoryInterface
{
    public function saveLog(AuthorizationLog|TransferLog $logModel, array $data): AuthorizationLog|TransferLog;
}
