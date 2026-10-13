<?php

declare(strict_types=1);

namespace S2J\WebinarService\Core;

final class WebinarProviderDescriptor
{
    /**
     * @param callable $buildRequest
     * @param callable $mapResult
     * @param callable $oauthMaterials
     * @param callable $buildTrackingSourcesRequest
     * @param callable $mapTrackingSourcesResponse
     */
    public function __construct(
        public readonly string $id,
        public readonly string $label,
        private $buildRequest,
        private $mapResult,
        private $oauthMaterials,
        private $buildTrackingSourcesRequest,
        private $mapTrackingSourcesResponse,
    ) {
    }

    /**
     * @param array<string, mixed> $record
     * @param array<string, mixed> $step
     * @return array{method: string, path: string, body: array<string, mixed>|null}
     */
    public function buildRequest(string $op, array $record, array $step): array
    {
        return ($this->buildRequest)($op, $record, $step);
    }

    /**
     * @param array<string, mixed>|null $body
     * @param array<string, mixed> $record
     * @return array{record: array<string, mixed>, start_url?: string}
     */
    public function mapResult(string $op, int $status, ?array $body, array $record): array
    {
        return ($this->mapResult)($op, $status, $body, $record);
    }

    /**
     * @param array<string, mixed> $config
     * @return array{method: string, path: string, body: array<string, mixed>|null}|string
     */
    public function oauthMaterials(string $kind, array $config, mixed ...$args): array|string
    {
        return ($this->oauthMaterials)($kind, $config, ...$args);
    }

    /**
     * @return array{method: string, path: string, body: null}
     */
    public function buildTrackingSourcesRequest(string $webinarId): array
    {
        return ($this->buildTrackingSourcesRequest)($webinarId);
    }

    /**
     * @return array{items: list<array<string, mixed>>, deficiencies: list<string>}
     */
    public function mapTrackingSourcesResponse(int $status, string $body): array
    {
        return ($this->mapTrackingSourcesResponse)($status, $body);
    }
}
