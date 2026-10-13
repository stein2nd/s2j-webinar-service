<?php

declare(strict_types=1);

namespace S2J\WebinarService\Tests\Unit;

use PHPUnit\Framework\TestCase;
use S2J\WebinarService\Tests\Unit\Support\RecordFixtures;

use function S2J\WebinarService\normalize_webinar_record;

final class RecordNormalizeTest extends TestCase
{
    use RecordFixtures;

    public function testMissingScalarsDefaultPerSpec(): void
    {
        $normalized = normalize_webinar_record([
            'panelists' => [['name' => 'A', 'email' => 'a@example.com']],
        ]);

        self::assertSame('', $normalized['topic']);
        self::assertSame('', $normalized['start_at']);
        self::assertSame('', $normalized['timezone']);
        self::assertSame(0, $normalized['duration_minutes']);
        self::assertSame('cloud', $normalized['auto_recording']);
        self::assertSame(0, $normalized['approval_type']);
    }

    public function testIdEmptyWithoutLastErrorIsNotCreated(): void
    {
        $normalized = normalize_webinar_record($this->validRecord([
            'webinar_id' => '',
            'status' => 'synced',
        ]));
        self::assertSame('not_created', $normalized['status']);
    }

    public function testIdEmptyWithLastErrorStaysError(): void
    {
        $normalized = normalize_webinar_record($this->validRecord([
            'webinar_id' => '',
            'last_error' => 'Create failed',
            'status' => 'synced',
        ]));
        self::assertSame('error', $normalized['status']);
    }

    public function testIdNonEmptyMissingStatusBecomesDirty(): void
    {
        $normalized = normalize_webinar_record($this->validRecord(['webinar_id' => '99']));
        self::assertSame('dirty', $normalized['status']);
    }

    public function testPanelistEmailCasePreservedOnRecord(): void
    {
        $normalized = normalize_webinar_record($this->validRecord());
        self::assertSame('Taro@Example.com', $normalized['panelists'][0]['email']);
    }

    public function testUnknownKeysAreDroppedFromNormalizedRecord(): void
    {
        $normalized = normalize_webinar_record($this->validRecord(['future_field' => 'x']));
        self::assertArrayNotHasKey('future_field', $normalized);
    }

    public function testProviderDefaultsToZoomWhenOmitted(): void
    {
        $input = $this->validRecord();
        unset($input['provider']);
        self::assertSame('zoom', normalize_webinar_record($input)['provider']);
    }
}
