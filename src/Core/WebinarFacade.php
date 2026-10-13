<?php

declare(strict_types=1);

namespace S2J\WebinarService\Core;

final class WebinarFacade
{
    /**
     * @param array<string, mixed> $record
     * @param array<string, mixed> $step
     * @return array{
     *   material?: array{method: string, path: string, body: array<string, mixed>|null},
     *   deficiencies: list<string>
     * }
     */
    public static function buildRequest(string $op, array $record, array $step = []): array
    {
        self::assertKnownOp($op);
        $record = RecordNormalizer::normalize($record);

        $descriptor = ProviderRegistry::lookup((string) $record['provider']);
        if ($descriptor === null) {
            return ['deficiencies' => [Deficiency::PROVIDER_UNSUPPORTED]];
        }

        self::assertBuildPreconditions($op, $record, $step);

        return [
            'material' => $descriptor->buildRequest($op, $record, $step),
            'deficiencies' => [],
        ];
    }

    /**
     * @param array<string, mixed>|null $body
     * @param array<string, mixed> $record
     * @return array{record: array<string, mixed>, start_url?: string, deficiencies: list<string>}
     */
    public static function mapResponse(string $op, int $httpStatus, ?array $body, array $record): array
    {
        self::assertKnownOp($op);
        $record = RecordNormalizer::normalize($record);

        $descriptor = ProviderRegistry::lookup((string) $record['provider']);
        if ($descriptor === null) {
            return [
                'record' => $record,
                'deficiencies' => [Deficiency::PROVIDER_UNSUPPORTED],
            ];
        }

        $mapped = $descriptor->mapResult($op, $httpStatus, $body, $record);

        $out = [
            'record' => $mapped['record'],
            'deficiencies' => [],
        ];
        if (isset($mapped['start_url']) && is_string($mapped['start_url']) && $mapped['start_url'] !== '') {
            $out['start_url'] = $mapped['start_url'];
        }

        return $out;
    }

    private static function assertKnownOp(string $op): void
    {
        if (! in_array($op, Deficiency::WEBINAR_OPS, true)) {
            throw new \InvalidArgumentException('Unknown op: ' . $op);
        }
    }

    /**
     * @param array<string, mixed> $record
     * @param array<string, mixed> $step
     */
    private static function assertBuildPreconditions(string $op, array $record, array $step): void
    {
        if (in_array($op, Deficiency::WEBINAR_ID_REQUIRED_OPS, true) && (string) $record['webinar_id'] === '') {
            throw new \InvalidArgumentException('webinar_id required for op: ' . $op);
        }

        if ($op === 'attach_survey') {
            if (! isset($step['survey_document']) || ! is_array($step['survey_document'])) {
                throw new \InvalidArgumentException('attach_survey requires survey_document in step');
            }
        }
    }

    /**
     * @return array{
     *   material?: array{method: string, path: string, body: array<string, mixed>|null},
     *   deficiencies: list<string>
     * }
     */
    public static function buildTrackingSourcesRequest(string $provider, string $webinarId): array
    {
        $descriptor = ProviderRegistry::lookup($provider);
        if ($descriptor === null) {
            return ['deficiencies' => [Deficiency::PROVIDER_UNSUPPORTED]];
        }

        if (trim($webinarId) === '') {
            return ['deficiencies' => [Deficiency::TRACKING_WEBINAR_ID_EMPTY]];
        }

        return [
            'material' => $descriptor->buildTrackingSourcesRequest(trim($webinarId)),
            'deficiencies' => [],
        ];
    }

    /**
     * @return array{items: list<array<string, mixed>>, deficiencies: list<string>}
     */
    public static function mapTrackingSourcesResponse(string $provider, int $status, string $body): array
    {
        $descriptor = ProviderRegistry::lookup($provider);
        if ($descriptor === null) {
            return [
                'items' => [],
                'deficiencies' => [Deficiency::PROVIDER_UNSUPPORTED],
            ];
        }

        return $descriptor->mapTrackingSourcesResponse($status, $body);
    }
}
