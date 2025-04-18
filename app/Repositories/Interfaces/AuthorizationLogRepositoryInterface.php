<?php

namespace App\Repositories\Interfaces;

use App\Models\AuthorizationLog;

interface AuthorizationLogRepositoryInterface
{
    public function createLog(array $data): AuthorizationLog;
}
