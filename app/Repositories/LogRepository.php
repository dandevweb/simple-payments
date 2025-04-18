<?php

namespace App\Repositories;

use App\Models\Log\AuthorizationLog;
use App\Models\Log\TransferLog;
use App\Repositories\Interfaces\LogRepositoryInterface;

class LogRepository implements LogRepositoryInterface
{
    public function saveLog(AuthorizationLog|TransferLog $logModel, array $data): AuthorizationLog|TransferLog
    {
        if ($logModel->exists) {
            $log = $this->loadAssociates($logModel, $data);

            return $this->update($log, $data);
        }

        $log = $logModel::query()->make($data);
        $logToSave = $this->loadAssociates($log, $data);

        $logToSave->save();

        return $log;
    }

    private function update(AuthorizationLog|TransferLog $log, array $data): AuthorizationLog|TransferLog
    {
        $log->fill($data);
        $log->save();

        return $log;
    }

    private function loadAssociates(AuthorizationLog|TransferLog $log, array $data): AuthorizationLog|TransferLog
    {
        if (data_get($data, 'payer_id')) {
            $log->payer()->associate($data['payer_id']);
        }

        if (data_get($data, 'payee_id')) {
            $log->payee()->associate($data['payee_id']);
        }

        if (data_get($data, 'transfer_id')) {
            $log->transfer()->associate($data['transfer_id']);
        }

        return $log;
    }
}
