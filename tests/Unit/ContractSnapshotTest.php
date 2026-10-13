<?php

declare(strict_types=1);

namespace S2J\WebinarService\Tests\Unit;

use PHPUnit\Framework\TestCase;
use S2J\WebinarService\Tests\Unit\Support\RecordFixtures;

use function S2J\WebinarService\build_webinar_request;

final class ContractSnapshotTest extends TestCase
{
    use RecordFixtures;

    private const FIXTURES_DIR = __DIR__ . '/../Fixtures/contracts';

    public function testZoomCreateWebinarBodyMatchesContractFixture(): void
    {
        $body = build_webinar_request('create', $this->validRecord([
            'topic' => 'Snap Title',
            'agenda' => '',
            'start_at' => '2026-10-09T15:00:00+09:00',
            'timezone' => 'Asia/Tokyo',
            'duration_minutes' => 90,
            'approval_type' => 0,
            'auto_recording' => 'cloud',
        ]))['material']['body'];

        $fixturePath = self::FIXTURES_DIR . '/zoom_create_webinar_body.json';
        $expected = json_decode((string) file_get_contents($fixturePath), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame($expected, $body);
    }
}
