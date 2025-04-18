<?php

namespace App\Enums;

enum AuthorizationLogStatusEnum: string
{
    case Success = 'success';
    case Fail = 'fail';

    public function label(): string
    {
        return match ($this) {
            self::Success => 'Sucesso',
            self::Fail => 'Falha',
        };
    }
}
