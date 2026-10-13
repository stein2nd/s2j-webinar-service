<?php

declare(strict_types=1);

namespace S2J\WebinarService\Tests\Unit\Support;

use S2J\WebinarService\Core\WebinarProviderDescriptor;

final class MockWebinarProvider
{
    public const ID = 'mock';

    public static function descriptor(): WebinarProviderDescriptor
    {
        return new WebinarProviderDescriptor(
            self::ID,
            'Mock',
            [self::class, 'buildRequest'],
            [self::class, 'mapResult'],
            [self::class, 'oauthMaterials'],
            [self::class, 'buildTrackingSourcesRequest'],
            [self::class, 'mapTrackingSourcesResponse'],
        );
    }

    /**
     * @param array<string, mixed> $record
     * @param array<string, mixed> $step
     * @return array{method: string, path: string, body: array<string, mixed>|null}
     */
    public static function buildRequest(string $op, array $record, array $step): array
    {
        return match ($op) {
            'create' => [
                'method' => 'POST',
                'path' => '/mock/webinars',
                'body' => ['marker' => 'mock-create'],
            ],
            default => throw new \InvalidArgumentException('Mock op not stubbed: ' . $op),
        };
    }

    /**
     * @param array<string, mixed>|null $body
     * @param array<string, mixed> $record
     * @return array{record: array<string, mixed>}
     */
    public static function mapResult(string $op, int $status, ?array $body, array $record): array
    {
        return ['record' => $record];
    }

    /**
     * @param array<string, mixed> $config
     * @return array{method: string, path: string, body: null}|string
     */
    public static function oauthMaterials(string $kind, array $config, mixed ...$args): array|string
    {
        throw new \LogicException('Mock oauth not used in registry tests');
    }

    /**
     * @return array{method: string, path: string, body: null}
     */
    public static function buildTrackingSourcesRequest(string $webinarId): array
    {
        throw new \LogicException('Mock tracking build not used in registry tests');
    }

    /**
     * @return array{items: list<array<string, mixed>>, deficiencies: list<string>}
     */
    public static function mapTrackingSourcesResponse(int $status, string $body): array
    {
        throw new \LogicException('Mock tracking map not used in registry tests');
    }
}
