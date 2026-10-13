<?php

declare(strict_types=1);

namespace S2J\WebinarService\Core;

final class Validator
{
    /**
     * @param array<string, mixed> $record
     * @return array{record: array<string, mixed>, deficiencies: list<string>}
     */
    public static function validate(array $record): array
    {
        $normalized = RecordNormalizer::normalize($record);
        $deficiencies = self::collectDeficiencies($normalized);

        return [
            'record' => $normalized,
            'deficiencies' => $deficiencies,
        ];
    }

    /**
     * @param array<string, mixed> $record normalized
     * @return list<string>
     */
    private static function collectDeficiencies(array $record): array
    {
        $found = [];

        $topic = trim((string) $record['topic']);
        if ($topic === '') {
            $found[] = Deficiency::TOPIC_EMPTY;
        } elseif (mb_strlen($topic) > 200) {
            $found[] = Deficiency::TOPIC_TOO_LONG;
        }

        $agenda = (string) $record['agenda'];
        if (mb_strlen($agenda) > 2000) {
            $found[] = Deficiency::AGENDA_TOO_LONG;
        }

        if (! self::isValidStartAt((string) $record['start_at'])) {
            $found[] = Deficiency::START_AT_INVALID;
        }

        if (! self::isValidTimezone((string) $record['timezone'])) {
            $found[] = Deficiency::TIMEZONE_INVALID;
        }

        $duration = (int) $record['duration_minutes'];
        if ($duration < 1) {
            $found[] = Deficiency::DURATION_INVALID;
        }

        $auto = (string) $record['auto_recording'];
        if (! in_array($auto, ['none', 'cloud', 'local'], true)) {
            $found[] = Deficiency::AUTO_RECORDING_INVALID;
        }

        $approval = (int) $record['approval_type'];
        if (! in_array($approval, [0, 1, 2], true)) {
            $found[] = Deficiency::APPROVAL_TYPE_INVALID;
        }

        /** @var list<array{name: string, email: string}> $panelists */
        $panelists = $record['panelists'];
        $panelistDeficiencies = [];
        if ($panelists === []) {
            $found[] = Deficiency::PANELISTS_EMPTY;
        } else {
            foreach ($panelists as $panelist) {
                $name = trim($panelist['name']);
                $email = trim($panelist['email']);
                if ($name === '' || $email === '') {
                    $panelistDeficiencies[] = Deficiency::PANELIST_INCOMPLETE;
                    continue;
                }
                if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $panelistDeficiencies[] = Deficiency::PANELIST_EMAIL_INVALID;
                }
            }
        }

        $providerUnsupported = false;
        $provider = (string) $record['provider'];
        if (ProviderRegistry::lookup($provider) === null) {
            $providerUnsupported = true;
        }

        $ordered = self::orderDeficiencies($found);
        if ($panelistDeficiencies !== []) {
            $ordered = array_merge($ordered, $panelistDeficiencies);
        }
        if ($providerUnsupported) {
            $ordered[] = Deficiency::PROVIDER_UNSUPPORTED;
        }

        return $ordered;
    }

    /**
     * @param list<string> $found
     * @return list<string>
     */
    private static function orderDeficiencies(array $found): array
    {
        $unique = array_values(array_unique($found));
        $order = Deficiency::WEBINAR_RECORD_ORDER;
        usort(
            $unique,
            static fn (string $a, string $b): int => array_search($a, $order, true) <=> array_search($b, $order, true)
        );

        return $unique;
    }

    private static function isValidStartAt(string $startAt): bool
    {
        if (trim($startAt) === '') {
            return false;
        }

        try {
            $dt = new \DateTimeImmutable($startAt);
        } catch (\Exception) {
            return false;
        }

        return preg_match('/(Z|[+-]\d{2}:\d{2})$/', trim($startAt)) === 1;
    }

    private static function isValidTimezone(string $timezone): bool
    {
        if (trim($timezone) === '') {
            return false;
        }

        try {
            new \DateTimeZone($timezone);
        } catch (\Exception) {
            return false;
        }

        return true;
    }
}
