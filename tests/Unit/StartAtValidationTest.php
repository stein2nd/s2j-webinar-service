<?php

declare(strict_types=1);

namespace S2J\WebinarService\Tests\Unit;

use PHPUnit\Framework\TestCase;
use S2J\WebinarService\Core\Deficiency;
use S2J\WebinarService\Tests\Unit\Support\RecordFixtures;

use function S2J\WebinarService\validate_webinar_record;

final class StartAtValidationTest extends TestCase
{
    use RecordFixtures;

    /**
     * @return iterable<string, array{0: string, 1: bool}>
     */
    public static function startAtBoundaryProvider(): iterable
    {
        yield 'empty' => ['', false];
        yield 'offset_missing' => ['2026-10-09T15:00:00', false];
        yield 'offset_plus_nine' => ['2026-10-09T15:00:00+09:00', true];
        yield 'zulu' => ['2026-10-09T06:00:00Z', true];
        yield 'unparseable' => ['not-a-datetime', false];
    }

    /**
     * @dataProvider startAtBoundaryProvider
     */
    public function testStartAtBoundaryMatchesValidationSpec(string $startAt, bool $valid): void
    {
        $result = validate_webinar_record($this->validRecord(['start_at' => $startAt]));
        $hasInvalid = in_array(Deficiency::START_AT_INVALID, $result['deficiencies'], true);
        self::assertSame(! $valid, $hasInvalid);
    }
}
