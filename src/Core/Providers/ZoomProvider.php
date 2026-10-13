<?php

declare(strict_types=1);

namespace S2J\WebinarService\Core\Providers;

use S2J\WebinarService\Core\Deficiency;
use S2J\WebinarService\Core\PanelistEmail;
use S2J\WebinarService\Core\WebinarProviderDescriptor;

final class ZoomProvider
{
    public const ID = 'zoom';

    private const OAUTH_SCOPES = [
        'webinar:write:webinar',
        'webinar:read:webinar',
        'webinar:update:webinar',
        'webinar:delete:webinar',
        'webinar:write:panelist',
        'webinar:delete:panelist',
        'webinar:update:survey',
        'user:read:user',
    ];

    public static function descriptor(): WebinarProviderDescriptor
    {
        return new WebinarProviderDescriptor(
            self::ID,
            'Zoom',
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
        $webinarId = (string) $record['webinar_id'];

        return match ($op) {
            'create' => [
                'method' => 'POST',
                'path' => '/users/me/webinars',
                'body' => self::createBody($record),
            ],
            'update' => [
                'method' => 'PATCH',
                'path' => '/webinars/' . rawurlencode($webinarId),
                'body' => self::updateBody($record),
            ],
            'delete' => [
                'method' => 'DELETE',
                'path' => '/webinars/' . rawurlencode($webinarId),
                'body' => null,
            ],
            'get' => [
                'method' => 'GET',
                'path' => '/webinars/' . rawurlencode($webinarId),
                'body' => null,
            ],
            'add_panelists' => [
                'method' => 'POST',
                'path' => '/webinars/' . rawurlencode($webinarId) . '/panelists',
                'body' => self::addPanelistsBody($step),
            ],
            'remove_panelists' => [
                'method' => 'DELETE',
                'path' => '/webinars/'
                    . rawurlencode($webinarId)
                    . '/panelists/'
                    . rawurlencode((string) ($step['email'] ?? '')),
                'body' => null,
            ],
            'attach_survey' => [
                'method' => 'PATCH',
                'path' => '/webinars/' . rawurlencode($webinarId) . '/survey',
                'body' => self::attachSurveyBody($record, $step),
            ],
            default => throw new \InvalidArgumentException('Unknown op: ' . $op),
        };
    }

    /**
     * @param array<string, mixed> $record
     * @return array<string, mixed>
     */
    private static function createBody(array $record): array
    {
        $body = self::coreWebinarFields($record);
        $settings = $body['settings'];
        unset($body['settings']);
        $agenda = $body['agenda'] ?? null;
        if ($agenda !== null) {
            unset($body['agenda']);
        }
        $body['type'] = 5;
        $body['settings'] = $settings;
        if ($agenda !== null) {
            $body['agenda'] = $agenda;
        }

        return $body;
    }

    /**
     * @param array<string, mixed> $record
     * @return array<string, mixed>
     */
    private static function updateBody(array $record): array
    {
        return self::coreWebinarFields($record);
    }

    /**
     * @param array<string, mixed> $record
     * @return array<string, mixed>
     */
    private static function coreWebinarFields(array $record): array
    {
        $body = [
            'topic' => (string) $record['topic'],
            'start_time' => self::zoomStartTime($record),
            'timezone' => (string) $record['timezone'],
            'duration' => (int) $record['duration_minutes'],
            'settings' => self::settingsBlock($record),
        ];

        $agenda = trim((string) $record['agenda']);
        if ($agenda !== '') {
            $body['agenda'] = $agenda;
        }

        return $body;
    }

    /**
     * @param array<string, mixed> $record
     * @return array<string, mixed>
     */
    private static function settingsBlock(array $record): array
    {
        /** @var list<array{name: string, email: string}> $panelists */
        $panelists = $record['panelists'];
        $first = $panelists[0];
        $contactEmail = PanelistEmail::normalize($first['email']);

        return [
            'auto_recording' => (string) $record['auto_recording'],
            'audio' => 'voip',
            'host_video' => false,
            'panelists_video' => false,
            'hd_video' => false,
            'meeting_authentication' => false,
            'approval_type' => (int) $record['approval_type'],
            'contact_name' => trim($first['name']),
            'contact_email' => $contactEmail,
            'question_and_answer' => [
                'enable' => true,
                'allow_anonymous_questions' => true,
                'answer_questions' => 'only',
            ],
        ];
    }

    /**
     * @param array<string, mixed> $record
     */
    private static function zoomStartTime(array $record): string
    {
        $dt = new \DateTimeImmutable((string) $record['start_at']);
        $tz = new \DateTimeZone((string) $record['timezone']);

        return $dt->setTimezone($tz)->format('Y-m-d\TH:i:s');
    }

    /**
     * @param array<string, mixed> $step
     * @return array<string, mixed>
     */
    private static function addPanelistsBody(array $step): array
    {
        $panelists = [];
        foreach ($step['panelists'] ?? [] as $row) {
            if (! is_array($row)) {
                continue;
            }
            $name = isset($row['name']) && is_string($row['name']) ? trim($row['name']) : '';
            $email = isset($row['email']) && is_string($row['email']) ? PanelistEmail::normalize($row['email']) : '';
            $panelists[] = ['name' => $name, 'email' => $email];
        }

        return ['panelists' => $panelists];
    }

    /**
     * @param array<string, mixed> $record
     * @param array<string, mixed> $step
     * @return array<string, mixed>
     */
    private static function attachSurveyBody(array $record, array $step): array
    {
        /** @var array<string, mixed> $document */
        $document = $step['survey_document'];
        $stepTitle = $step['webinar_title'] ?? null;
        $hasStepTitle = is_string($stepTitle) && trim($stepTitle) !== '';
        $title = $hasStepTitle ? trim($stepTitle) : (string) $record['topic'];

        return [
            'show_in_the_browser' => true,
            'show_in_the_follow_up_email' => false,
            'custom_survey' => [
                'title' => $title,
                'questions' => self::mapSurveyQuestions($document),
            ],
        ];
    }

    /**
     * @param array<string, mixed> $document
     * @return list<array<string, mixed>>
     */
    private static function mapSurveyQuestions(array $document): array
    {
        $questions = $document['questions'] ?? [];
        if (! is_array($questions)) {
            return [];
        }

        $mapped = [];
        foreach ($questions as $question) {
            if (! is_array($question)) {
                continue;
            }
            $zoom = self::mapSurveyQuestion($question);
            if ($zoom !== null) {
                $mapped[] = $zoom;
            }
        }

        return $mapped;
    }

    /**
     * @param array<string, mixed> $question
     * @return array<string, mixed>|null
     */
    private static function mapSurveyQuestion(array $question): ?array
    {
        $kind = isset($question['answer_kind']) && is_string($question['answer_kind'])
            ? $question['answer_kind']
            : '';
        $name = isset($question['prompt']) && is_string($question['prompt']) ? $question['prompt'] : '';
        $required = ! empty($question['required']);

        $base = [
            'name' => $name,
            'answer_required' => $required,
        ];

        return match ($kind) {
            'single' => $base + [
                'type' => 'single',
                'answers' => is_array($question['choices'] ?? null) ? array_values($question['choices']) : [],
            ],
            'multiple' => $base + [
                'type' => 'multiple',
                'answers' => is_array($question['choices'] ?? null) ? array_values($question['choices']) : [],
            ],
            'short' => $base + ['type' => 'short_answer'],
            'long' => $base + ['type' => 'long_answer'],
            'rating' => $base + [
                'type' => 'rating_scale',
                'rating_min_value' => $question['score_min'] ?? 1,
                'rating_max_value' => $question['score_max'] ?? 5,
                'rating_min_label' => $question['label_low'] ?? '',
                'rating_max_label' => $question['label_high'] ?? '',
            ],
            default => null,
        };
    }

    /**
     * @param array<string, mixed>|null $body
     * @param array<string, mixed> $record
     * @return array{record: array<string, mixed>, start_url?: string}
     */
    public static function mapResult(string $op, int $status, ?array $body, array $record): array
    {
        $success = $status >= 200 && $status < 300;

        return match ($op) {
            'create' => self::mapCreate($success, $body, $record),
            'update' => self::mapSimpleMutation($success, $body, $record),
            'delete' => self::mapDelete($success, $body, $record),
            'get' => self::mapGet($success, $body, $record),
            'add_panelists', 'remove_panelists', 'attach_survey' => self::mapPanelistOrSurveySuccess(
                $success,
                $body,
                $record
            ),
            default => throw new \InvalidArgumentException('Unknown op: ' . $op),
        };
    }

    /**
     * @param array<string, mixed>|null $body
     * @param array<string, mixed> $record
     * @return array{record: array<string, mixed>, start_url?: string}
     */
    private static function mapCreate(bool $success, ?array $body, array $record): array
    {
        if (! $success || $body === null || ! isset($body['id'])) {
            $record['status'] = 'error';
            $record['last_error'] = self::errorMessage($body);
            $record['webinar_id'] = '';

            return ['record' => $record];
        }

        $record['webinar_id'] = (string) $body['id'];
        if (isset($body['uuid']) && is_string($body['uuid'])) {
            $record['webinar_uuid'] = $body['uuid'];
        }
        if (isset($body['join_url']) && is_string($body['join_url'])) {
            $record['join_url'] = $body['join_url'];
        }
        $record['status'] = 'synced';
        $record['last_error'] = '';

        return ['record' => $record];
    }

    /**
     * @param array<string, mixed>|null $body
     * @param array<string, mixed> $record
     * @return array{record: array<string, mixed>, start_url?: string}
     */
    private static function mapSimpleMutation(bool $success, ?array $body, array $record): array
    {
        if (! $success) {
            $record['status'] = 'error';
            $record['last_error'] = self::errorMessage($body);

            return ['record' => $record];
        }

        $record['status'] = 'synced';
        $record['last_error'] = '';

        return ['record' => $record];
    }

    /**
     * @param array<string, mixed>|null $body
     * @param array<string, mixed> $record
     * @return array{record: array<string, mixed>, start_url?: string}
     */
    private static function mapPanelistOrSurveySuccess(bool $success, ?array $body, array $record): array
    {
        if (! $success) {
            $record['status'] = 'error';
            $record['last_error'] = self::errorMessage($body);

            return ['record' => $record];
        }

        $record['status'] = 'synced';
        $record['last_error'] = '';

        return ['record' => $record];
    }

    /**
     * @param array<string, mixed>|null $body
     * @param array<string, mixed> $record
     * @return array{record: array<string, mixed>, start_url?: string}
     */
    private static function mapDelete(bool $success, ?array $body, array $record): array
    {
        if (! $success) {
            $record['status'] = 'error';
            $record['last_error'] = self::errorMessage($body);

            return ['record' => $record];
        }

        $record['webinar_id'] = '';
        $record['webinar_uuid'] = '';
        $record['join_url'] = '';
        $record['status'] = 'not_created';
        $record['last_error'] = '';

        return ['record' => $record];
    }

    /**
     * @param array<string, mixed>|null $body
     * @param array<string, mixed> $record
     * @return array{record: array<string, mixed>, start_url?: string}
     */
    private static function mapGet(bool $success, ?array $body, array $record): array
    {
        if (! $success) {
            $record['last_error'] = self::errorMessage($body);

            return ['record' => $record];
        }

        if ($body !== null && isset($body['join_url']) && is_string($body['join_url'])) {
            $record['join_url'] = $body['join_url'];
        }

        $out = ['record' => $record];
        if ($body !== null && isset($body['start_url']) && is_string($body['start_url']) && $body['start_url'] !== '') {
            $out['start_url'] = $body['start_url'];
        }

        return $out;
    }

    /**
     * @param array<string, mixed>|null $body
     */
    private static function errorMessage(?array $body): string
    {
        if ($body === null) {
            return 'Request failed';
        }

        foreach (['message', 'reason', 'error'] as $key) {
            if (isset($body[$key]) && is_string($body[$key]) && $body[$key] !== '') {
                return $body[$key];
            }
        }

        return 'Request failed';
    }

    /**
     * @param array<string, mixed> $config
     * @return array{method: string, path: string, body: array<string, mixed>|null}|string
     */
    public static function oauthMaterials(string $kind, array $config, mixed ...$args): array|string
    {
        self::assertOAuthConfig($config);

        return match ($kind) {
            'authorize' => self::authorizeUrl($config),
            'token' => [
                'method' => 'POST',
                'path' => 'https://zoom.us/oauth/token',
                'body' => [
                    'grant_type' => 'authorization_code',
                    'code' => (string) ($args[0] ?? ''),
                    'redirect_uri' => (string) $config['redirect_uri'],
                ],
            ],
            'refresh' => [
                'method' => 'POST',
                'path' => 'https://zoom.us/oauth/token',
                'body' => [
                    'grant_type' => 'refresh_token',
                    'refresh_token' => (string) ($args[0] ?? ''),
                ],
            ],
            default => throw new \InvalidArgumentException('Unknown oauth kind: ' . $kind),
        };
    }

    /**
     * @param array<string, mixed> $config
     */
    private static function authorizeUrl(array $config): string
    {
        $query = http_build_query([
            'response_type' => 'code',
            'client_id' => (string) $config['client_id'],
            'redirect_uri' => (string) $config['redirect_uri'],
            'scope' => implode(' ', self::OAUTH_SCOPES),
        ], '', '&', PHP_QUERY_RFC3986);

        return 'https://zoom.us/oauth/authorize?' . $query;
    }

    /**
     * @param array<string, mixed> $config
     */
    private static function assertOAuthConfig(array $config): void
    {
        foreach (['client_id', 'client_secret', 'redirect_uri'] as $key) {
            if (! isset($config[$key]) || ! is_string($config[$key]) || trim($config[$key]) === '') {
                throw new \InvalidArgumentException('Missing oauth config: ' . $key);
            }
        }
    }

    /**
     * @return array{method: string, path: string, body: null}
     */
    public static function buildTrackingSourcesRequest(string $webinarId): array
    {
        return [
            'method' => 'GET',
            'path' => '/v2/webinars/' . rawurlencode($webinarId) . '/tracking_sources',
            'body' => null,
        ];
    }

    /**
     * @return array{items: list<array<string, mixed>>, deficiencies: list<string>}
     */
    public static function mapTrackingSourcesResponse(int $status, string $body): array
    {
        if ($status < 200 || $status >= 300) {
            return [
                'items' => [],
                'deficiencies' => [Deficiency::TRACKING_HTTP_ERROR],
            ];
        }

        $decoded = json_decode($body, true);
        if (! is_array($decoded)) {
            return [
                'items' => [],
                'deficiencies' => [Deficiency::TRACKING_BODY_INVALID],
            ];
        }

        $rows = array_is_list($decoded)
            ? $decoded
            : (is_array($decoded['tracking_sources'] ?? null) ? $decoded['tracking_sources'] : null);

        if ($rows === null) {
            return [
                'items' => [],
                'deficiencies' => [Deficiency::TRACKING_BODY_INVALID],
            ];
        }

        $items = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $item = self::mapTrackingRow($row);
            if ($item !== null) {
                $items[] = $item;
            }
        }

        if ($items === []) {
            return [
                'items' => [],
                'deficiencies' => [Deficiency::TRACKING_SOURCES_EMPTY],
            ];
        }

        return ['items' => $items, 'deficiencies' => []];
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>|null
     */
    private static function mapTrackingRow(array $row): ?array
    {
        $sourceName = isset($row['source_name']) && is_string($row['source_name']) ? trim($row['source_name']) : '';
        $url = isset($row['tracking_url']) && is_string($row['tracking_url']) ? trim($row['tracking_url']) : '';
        $urlOk = str_starts_with($url, 'http://') || str_starts_with($url, 'https://');
        if ($sourceName === '' || $url === '' || ! $urlOk) {
            return null;
        }

        if (str_contains($sourceName, "\n") || str_contains($sourceName, "\r")) {
            return null;
        }

        $item = [
            'source_name' => $sourceName,
            'registration_url' => $url,
        ];
        if (array_key_exists('id', $row)) {
            $item['external_id'] = (string) $row['id'];
        }

        return $item;
    }
}
