<?php

declare(strict_types=1);

namespace S2J\WebinarService\Core;

final class PanelistEmail
{
    public static function normalize(string $email): string
    {
        return strtolower(trim($email));
    }
}
