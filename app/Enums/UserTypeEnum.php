<?php

namespace app\Enums;

enum UserTypeEnum: string
{
    case Common = 'common';
    case Merchant = 'merchant';

    public function label(): string
    {
        return match ($this) {
            self::Common => 'Comum',
            self::Merchant => 'Comerciante',
        };
    }
}
