<?php

declare(strict_types=1);

namespace S2J\WebinarService\Core;

final class RecordNormalizer
{
    /**
     * @param array<string, mixed> $record
     * @return array<string, mixed>
     */
    public static function normalize(array $record): array
    {
        $provider = isset($record['provider']) && is_string($record['provider']) && $record['provider'] !== ''
            ? $record['provider']
            : 'zoom';

        $topic = self::stringField($record, 'topic', '');
        $agenda = self::stringField($record, 'agenda', '');
        $startAt = self::stringField($record, 'start_at', '');
        $timezone = self::stringField($record, 'timezone', '');
        $duration = isset($record['duration_minutes']) && is_numeric($record['duration_minutes'])
            ? (int) $record['duration_minutes']
            : 0;

        $autoRecording = array_key_exists('auto_recording', $record) && is_string($record['auto_recording'])
            ? $record['auto_recording']
            : 'cloud';

        $approvalType = array_key_exists('approval_type', $record) && is_numeric($record['approval_type'])
            ? (int) $record['approval_type']
            : 0;

        $panelists = self::normalizePanelists($record['panelists'] ?? []);

        $webinarId = self::stringField($record, 'webinar_id', '');
        $webinarUuid = self::stringField($record, 'webinar_uuid', '');
        $joinUrl = self::stringField($record, 'join_url', '');
        $lastError = self::stringField($record, 'last_error', '');

        $status = self::normalizeStatus($record, $webinarId, $lastError);

        return [
            'provider' => $provider,
            'topic' => $topic,
            'agenda' => $agenda,
            'start_at' => $startAt,
            'timezone' => $timezone,
            'duration_minutes' => $duration,
            'auto_recording' => $autoRecording,
            'approval_type' => $approvalType,
            'panelists' => $panelists,
            'webinar_id' => $webinarId,
            'webinar_uuid' => $webinarUuid,
            'join_url' => $joinUrl,
            'status' => $status,
            'last_error' => $lastError,
        ];
    }

    /**
     * @param mixed $panelists
     * @return list<array{name: string, email: string}>
     */
    private static function normalizePanelists(mixed $panelists): array
    {
        if (! is_array($panelists)) {
            return [];
        }

        $out = [];
        foreach ($panelists as $row) {
            if (! is_array($row)) {
                continue;
            }
            $name = isset($row['name']) && is_string($row['name']) ? $row['name'] : '';
            $email = isset($row['email']) && is_string($row['email']) ? $row['email'] : '';
            $out[] = ['name' => $name, 'email' => $email];
        }

        return $out;
    }

    /**
     * @param array<string, mixed> $record
     */
    private static function normalizeStatus(array $record, string $webinarId, string $lastError): string
    {
        $inputStatus = isset($record['status']) && is_string($record['status']) ? $record['status'] : '';

        if ($webinarId === '') {
            if ($lastError !== '' || $inputStatus === 'error') {
                return 'error';
            }

            return 'not_created';
        }

        if ($inputStatus === '') {
            return 'dirty';
        }

        if (in_array($inputStatus, ['not_created', 'synced', 'dirty', 'error'], true)) {
            return $inputStatus;
        }

        return 'dirty';
    }

    /**
     * @param array<string, mixed> $record
     */
    private static function stringField(array $record, string $key, string $default): string
    {
        if (! isset($record[$key]) || ! is_string($record[$key])) {
            return $default;
        }

        return $record[$key];
    }
}
