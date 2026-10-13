<?php

declare(strict_types=1);

namespace S2J\WebinarService\Core;

use S2J\WebinarService\Core\Providers\ZoomProvider;

final class ProviderRegistry
{
    /** @var array<string, WebinarProviderDescriptor>|null */
    private static ?array $providers = null;

    public static function lookup(string $id): ?WebinarProviderDescriptor
    {
        self::boot();

        return self::$providers[$id] ?? null;
    }

    public static function register(WebinarProviderDescriptor $descriptor): void
    {
        self::boot();
        self::$providers[$descriptor->id] = $descriptor;
    }

    /**
     * Resets the registry so the next lookup re-boots defaults. For PHPUnit only.
     */
    public static function resetForTesting(): void
    {
        self::$providers = null;
    }

    private static function boot(): void
    {
        if (self::$providers !== null) {
            return;
        }

        self::$providers = [
            ZoomProvider::ID => ZoomProvider::descriptor(),
        ];
    }
}
