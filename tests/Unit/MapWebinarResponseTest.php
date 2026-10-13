<?php

declare(strict_types=1);

namespace S2J\WebinarService\Tests\Unit;

use PHPUnit\Framework\TestCase;
use S2J\WebinarService\Tests\Unit\Support\RecordFixtures;

use function S2J\WebinarService\map_webinar_response;

final class MapWebinarResponseTest extends TestCase
{
    use RecordFixtures;

    public function testUnknownOpThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        map_webinar_response('none', 200, [], $this->validRecord());
    }

    public function testCreateSuccessSetsIdAndJoinUrl(): void
    {
        $record = $this->validRecord();
        $mapped = map_webinar_response('create', 201, [
            'id' => '42',
            'uuid' => 'uuid-42',
            'join_url' => 'https://zoom.us/j/42',
        ], $record);
        self::assertSame('42', $mapped['record']['webinar_id']);
        self::assertSame('uuid-42', $mapped['record']['webinar_uuid']);
        self::assertSame('https://zoom.us/j/42', $mapped['record']['join_url']);
        self::assertSame('synced', $mapped['record']['status']);
        self::assertSame([], $mapped['deficiencies']);
    }

    public function testCreateFailureKeepsWebinarIdEmpty(): void
    {
        $record = $this->validRecord();
        $mapped = map_webinar_response('create', 400, ['message' => 'Bad'], $record);
        self::assertSame('', $mapped['record']['webinar_id']);
        self::assertSame('error', $mapped['record']['status']);
        self::assertStringNotContainsString('access_token', $mapped['record']['last_error']);
    }

    public function testUpdateSuccessOn204NullBody(): void
    {
        $record = $this->validRecord(['webinar_id' => '1', 'status' => 'dirty']);
        $mapped = map_webinar_response('update', 204, null, $record);
        self::assertSame('synced', $mapped['record']['status']);
        self::assertSame('', $mapped['record']['last_error']);
    }

    public function testPanelistSuccessSetsSynced(): void
    {
        $record = $this->validRecord(['webinar_id' => '1', 'status' => 'error', 'last_error' => 'x']);
        $mapped = map_webinar_response('add_panelists', 201, [], $record);
        self::assertSame('synced', $mapped['record']['status']);
        self::assertSame('', $mapped['record']['last_error']);
    }

    public function testGetSuccessDoesNotChangeStatusReturnsStartUrl(): void
    {
        $record = $this->validRecord([
            'webinar_id' => '1',
            'status' => 'dirty',
            'topic' => 'Local Topic',
            'start_at' => '2026-10-09T15:00:00+09:00',
            'duration_minutes' => 60,
        ]);
        $mapped = map_webinar_response('get', 200, [
            'topic' => 'Zoom Topic',
            'start_time' => '2026-11-01T10:00:00Z',
            'duration' => 999,
            'join_url' => 'https://zoom.us/j/1',
            'start_url' => 'https://zoom.us/s/1',
        ], $record);
        self::assertSame('dirty', $mapped['record']['status']);
        self::assertSame('Local Topic', $mapped['record']['topic']);
        self::assertSame('2026-10-09T15:00:00+09:00', $mapped['record']['start_at']);
        self::assertSame(60, $mapped['record']['duration_minutes']);
        self::assertSame('https://zoom.us/j/1', $mapped['record']['join_url']);
        self::assertSame('https://zoom.us/s/1', $mapped['start_url']);
    }

    public function testGetFailureDoesNotChangeStatus(): void
    {
        $record = $this->validRecord(['webinar_id' => '1', 'status' => 'synced']);
        $mapped = map_webinar_response('get', 404, ['message' => 'missing'], $record);
        self::assertSame('synced', $mapped['record']['status']);
    }

    public function testDeleteFailureKeepsWebinarId(): void
    {
        $record = $this->validRecord(['webinar_id' => '1', 'status' => 'synced']);
        $mapped = map_webinar_response('delete', 500, ['message' => 'fail'], $record);
        self::assertSame('1', $mapped['record']['webinar_id']);
        self::assertSame('error', $mapped['record']['status']);
    }

    public function testDeleteSuccessClearsIds(): void
    {
        $record = $this->validRecord(['webinar_id' => '1', 'status' => 'synced']);
        $mapped = map_webinar_response('delete', 204, null, $record);
        self::assertSame('', $mapped['record']['webinar_id']);
        self::assertSame('not_created', $mapped['record']['status']);
    }

    public function testAttachSurveySuccessSynced(): void
    {
        $record = $this->validRecord(['webinar_id' => '1', 'status' => 'synced']);
        $mapped = map_webinar_response('attach_survey', 204, null, $record);
        self::assertSame('synced', $mapped['record']['status']);
    }
}
