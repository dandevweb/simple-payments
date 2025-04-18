<?php

namespace App\Enums;

enum LogStatusEnum: string
{
    case Success = 'success';
    case Fail = 'fail';
    case Pending = 'pending';

    public function label(): string
    {
        return match ($this) {
            self::Success => 'Sucesso',
            self::Fail => 'Falha',
            self::Pending => 'Pendente',
        };
    }
}
