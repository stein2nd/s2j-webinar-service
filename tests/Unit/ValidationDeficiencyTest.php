<?php

declare(strict_types=1);

namespace S2J\WebinarService\Tests\Unit;

use PHPUnit\Framework\TestCase;
use S2J\WebinarService\Core\Deficiency;
use S2J\WebinarService\Tests\Unit\Support\RecordFixtures;

use function S2J\WebinarService\build_webinar_request;
use function S2J\WebinarService\map_webinar_response;
use function S2J\WebinarService\validate_webinar_record;

final class ValidationDeficiencyTest extends TestCase
{
    use RecordFixtures;

    /**
     * @return iterable<string, array{0: array<string, mixed>, 1: string}>
     */
    public static function deficiencyProvider(): iterable
    {
        yield Deficiency::TOPIC_EMPTY => [
            ['topic' => '   '],
            Deficiency::TOPIC_EMPTY,
        ];
        yield Deficiency::TOPIC_TOO_LONG => [
            ['topic' => str_repeat('a', 201)],
            Deficiency::TOPIC_TOO_LONG,
        ];
        yield Deficiency::AGENDA_TOO_LONG => [
            ['agenda' => str_repeat('b', 2001)],
            Deficiency::AGENDA_TOO_LONG,
        ];
        yield Deficiency::START_AT_INVALID => [
            ['start_at' => ''],
            Deficiency::START_AT_INVALID,
        ];
        yield Deficiency::TIMEZONE_INVALID => [
            ['timezone' => 'Not/AZone'],
            Deficiency::TIMEZONE_INVALID,
        ];
        yield Deficiency::DURATION_INVALID => [
            ['duration_minutes' => 0],
            Deficiency::DURATION_INVALID,
        ];
        yield Deficiency::AUTO_RECORDING_INVALID => [
            ['auto_recording' => 'bogus'],
            Deficiency::AUTO_RECORDING_INVALID,
        ];
        yield Deficiency::APPROVAL_TYPE_INVALID => [
            ['approval_type' => 9],
            Deficiency::APPROVAL_TYPE_INVALID,
        ];
        yield Deficiency::PANELISTS_EMPTY => [
            ['panelists' => []],
            Deficiency::PANELISTS_EMPTY,
        ];
        yield Deficiency::PANELIST_INCOMPLETE => [
            ['panelists' => [['name' => '', 'email' => 'a@example.com']]],
            Deficiency::PANELIST_INCOMPLETE,
        ];
        yield Deficiency::PANELIST_EMAIL_INVALID => [
            ['panelists' => [['name' => 'A', 'email' => 'not-an-email']]],
            Deficiency::PANELIST_EMAIL_INVALID,
        ];
        yield Deficiency::PROVIDER_UNSUPPORTED => [
            ['provider' => 'teams'],
            Deficiency::PROVIDER_UNSUPPORTED,
        ];
    }

    /**
     * @param array<string, mixed> $overrides
     * @dataProvider deficiencyProvider
     */
    public function testEachDeficiencyCode(array $overrides, string $code): void
    {
        $result = validate_webinar_record($this->validRecord($overrides));
        self::assertContains($code, $result['deficiencies']);
    }

    public function testDeficiencyOrderIsStable(): void
    {
        $result = validate_webinar_record([
            'provider' => 'teams',
            'topic' => '',
            'duration_minutes' => 0,
            'panelists' => [],
        ]);
        self::assertSame(
            [
                Deficiency::TOPIC_EMPTY,
                Deficiency::START_AT_INVALID,
                Deficiency::TIMEZONE_INVALID,
                Deficiency::DURATION_INVALID,
                Deficiency::PANELISTS_EMPTY,
                Deficiency::PROVIDER_UNSUPPORTED,
            ],
            $result['deficiencies']
        );
    }

    public function testMultiplePanelistDeficienciesFollowInputOrder(): void
    {
        $result = validate_webinar_record($this->validRecord([
            'panelists' => [
                ['name' => '', 'email' => 'a@example.com'],
                ['name' => 'B', 'email' => 'not-email'],
                ['name' => '', 'email' => 'c@example.com'],
            ],
        ]));

        self::assertSame(
            [
                Deficiency::PANELIST_INCOMPLETE,
                Deficiency::PANELIST_EMAIL_INVALID,
                Deficiency::PANELIST_INCOMPLETE,
            ],
            $result['deficiencies']
        );
    }

    public function testBuildAndMapUnknownProviderReturnProviderUnsupported(): void
    {
        $record = $this->validRecord(['provider' => 'teams']);
        $build = build_webinar_request('create', $record);
        self::assertSame([Deficiency::PROVIDER_UNSUPPORTED], $build['deficiencies']);

        $map = map_webinar_response('create', 200, [], $record);
        self::assertSame([Deficiency::PROVIDER_UNSUPPORTED], $map['deficiencies']);
        self::assertSame('teams', $map['record']['provider']);
    }
}
