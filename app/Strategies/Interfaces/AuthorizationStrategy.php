<?php

namespace App\Strategies\Interfaces;

interface AuthorizationStrategy
{
    public static function authorize(): bool;
}
