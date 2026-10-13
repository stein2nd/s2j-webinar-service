<?php

declare(strict_types=1);

namespace S2J\WebinarService;

use S2J\WebinarService\Core\OperationPlanner;
use S2J\WebinarService\Core\PanelistDiff;
use S2J\WebinarService\Core\ProviderRegistry;
use S2J\WebinarService\Core\RecordNormalizer;
use S2J\WebinarService\Core\Validator;
use S2J\WebinarService\Core\WebinarFacade;

/**
 * @param array<string, mixed> $record
 * @return array<string, mixed>
 */
function normalize_webinar_record(array $record): array
{
    return RecordNormalizer::normalize($record);
}

/**
 * @param array<string, mixed> $record
 * @return array{record: array<string, mixed>, deficiencies: list<string>}
 */
function validate_webinar_record(array $record): array
{
    return Validator::validate($record);
}

/**
 * @param array<string, mixed> $record
 * @param array<string, mixed> $context
 * @return list<array<string, mixed>>
 */
function plan_webinar_operations(array $record, array $context = []): array
{
    return OperationPlanner::plan($record, $context);
}

/**
 * @param list<array{name?: string, email?: string}> $previous
 * @param list<array{name?: string, email?: string}> $current
 * @return array{add: list<array{name: string, email: string}>, remove: list<array{email: string}>}
 */
function diff_panelists(array $previous, array $current): array
{
    return PanelistDiff::diff($previous, $current);
}

/**
 * @param array<string, mixed> $record
 * @param array<string, mixed> $step
 * @return array{
 *   material?: array{method: string, path: string, body: array<string, mixed>|null},
 *   deficiencies: list<string>
 * }
 */
function build_webinar_request(string $op, array $record, array $step = []): array
{
    return WebinarFacade::buildRequest($op, $record, $step);
}

/**
 * @param array<string, mixed>|null $body
 * @param array<string, mixed> $record
 * @return array{record: array<string, mixed>, start_url?: string, deficiencies: list<string>}
 */
function map_webinar_response(string $op, int $http_status, ?array $body, array $record): array
{
    return WebinarFacade::mapResponse($op, $http_status, $body, $record);
}

/**
 * @param array<string, mixed> $survey_document
 * @return array{
 *   material?: array{method: string, path: string, body: array<string, mixed>|null},
 *   deficiencies: list<string>
 * }
 */
function build_survey_update_request(array $survey_document, string $webinar_id, ?string $webinar_title = null): array
{
    $record = RecordNormalizer::normalize([
        'provider' => 'zoom',
        'webinar_id' => $webinar_id,
    ]);

    $step = ['op' => 'attach_survey', 'survey_document' => $survey_document];
    if ($webinar_title !== null && trim($webinar_title) !== '') {
        $step['webinar_title'] = trim($webinar_title);
    }

    return build_webinar_request('attach_survey', $record, $step);
}

/**
 * @param array<string, mixed> $config
 */
function build_oauth_authorize_url(array $config): string
{
    $descriptor = ProviderRegistry::lookup('zoom');
    if ($descriptor === null) {
        throw new \RuntimeException('zoom provider missing');
    }
    $result = $descriptor->oauthMaterials('authorize', $config);
    if (! is_string($result)) {
        throw new \RuntimeException('authorize must return URL string');
    }

    return $result;
}

/**
 * @param array<string, mixed> $config
 * @return array{method: string, path: string, body: array<string, mixed>|null}
 */
function build_oauth_token_exchange_request(array $config, string $code): array
{
    $descriptor = ProviderRegistry::lookup('zoom');
    if ($descriptor === null) {
        throw new \RuntimeException('zoom provider missing');
    }
    $result = $descriptor->oauthMaterials('token', $config, $code);
    if (! is_array($result)) {
        throw new \RuntimeException('token must return RequestMaterial');
    }

    return $result;
}

/**
 * @param array<string, mixed> $config
 * @return array{method: string, path: string, body: array<string, mixed>|null}
 */
function build_oauth_refresh_request(array $config, string $refresh_token): array
{
    $descriptor = ProviderRegistry::lookup('zoom');
    if ($descriptor === null) {
        throw new \RuntimeException('zoom provider missing');
    }
    $result = $descriptor->oauthMaterials('refresh', $config, $refresh_token);
    if (! is_array($result)) {
        throw new \RuntimeException('refresh must return RequestMaterial');
    }

    return $result;
}

/**
 * @return array{
 *   material?: array{method: string, path: string, body: array<string, mixed>|null},
 *   deficiencies: list<string>
 * }
 */
function build_tracking_sources_request(string $provider, string $webinar_id): array
{
    return WebinarFacade::buildTrackingSourcesRequest($provider, $webinar_id);
}

/**
 * @return array{items: list<array<string, mixed>>, deficiencies: list<string>}
 */
function map_tracking_sources_response(string $provider, int $status, string $body): array
{
    return WebinarFacade::mapTrackingSourcesResponse($provider, $status, $body);
}
