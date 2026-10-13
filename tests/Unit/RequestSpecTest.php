<?php

declare(strict_types=1);

namespace S2J\WebinarService\Tests\Unit;

use PHPUnit\Framework\TestCase;
use S2J\WebinarService\Tests\Unit\Support\RecordFixtures;

use function S2J\WebinarService\build_webinar_request;

final class RequestSpecTest extends TestCase
{
    use RecordFixtures;

    /** @return array<string, mixed> */
    private function builtCreateBody(array $overrides = []): array
    {
        $built = build_webinar_request('create', $this->validRecord($overrides));

        return $built['material']['body'];
    }

    public function testCreateBodyMatchesRequestSpecSnapshot(): void
    {
        $body = $this->builtCreateBody([
            'topic' => 'Snap Title',
            'agenda' => '',
            'start_at' => '2026-10-09T15:00:00+09:00',
            'timezone' => 'Asia/Tokyo',
            'duration_minutes' => 90,
            'approval_type' => 0,
            'auto_recording' => 'cloud',
        ]);

        $expected = [
            'topic' => 'Snap Title',
            'start_time' => '2026-10-09T15:00:00',
            'timezone' => 'Asia/Tokyo',
            'duration' => 90,
            'type' => 5,
            'settings' => [
                'auto_recording' => 'cloud',
                'audio' => 'voip',
                'host_video' => false,
                'panelists_video' => false,
                'hd_video' => false,
                'meeting_authentication' => false,
                'approval_type' => 0,
                'contact_name' => 'Taro',
                'contact_email' => 'taro@example.com',
                'question_and_answer' => [
                    'enable' => true,
                    'allow_anonymous_questions' => true,
                    'answer_questions' => 'only',
                ],
            ],
        ];

        self::assertSame($expected, $body);
    }

    public function testCreatePathIsUsersMeWebinars(): void
    {
        $material = build_webinar_request('create', $this->validRecord())['material'];
        self::assertSame('POST', $material['method']);
        self::assertSame('/users/me/webinars', $material['path']);
    }

    public function testCreateOmitsAgendaWhenEmptyAndForbiddenKeys(): void
    {
        $body = $this->builtCreateBody(['agenda' => '']);
        self::assertArrayNotHasKey('agenda', $body);
        self::assertArrayNotHasKey('password', $body['settings']);
        self::assertArrayNotHasKey('panelist_authentication', $body['settings']);
    }

    public function testUpdateIncludesApprovalTypeAndAgendaWhenNonEmpty(): void
    {
        $record = $this->validRecord([
            'webinar_id' => 'wid-1',
            'agenda' => 'Details',
            'approval_type' => 1,
        ]);
        $body = build_webinar_request('update', $record)['material']['body'];
        self::assertSame(1, $body['settings']['approval_type']);
        self::assertSame('Details', $body['agenda']);
    }

    public function testPathsEmbedWebinarId(): void
    {
        $record = $this->validRecord(['webinar_id' => 'abc/ id']);
        self::assertSame('PATCH', build_webinar_request('update', $record)['material']['method']);
        self::assertSame('/webinars/abc%2F%20id', build_webinar_request('update', $record)['material']['path']);
        self::assertSame('/webinars/abc%2F%20id', build_webinar_request('delete', $record)['material']['path']);
        self::assertSame('/webinars/abc%2F%20id', build_webinar_request('get', $record)['material']['path']);
    }

    public function testRemovePanelistsUsesPerEmailDeletePath(): void
    {
        $record = $this->validRecord(['webinar_id' => '1']);
        $path = build_webinar_request('remove_panelists', $record, [
            'op' => 'remove_panelists',
            'email' => 'x@example.com',
        ])['material']['path'];
        self::assertSame('/webinars/1/panelists/x%40example.com', $path);
        self::assertStringEndsNotWith('/panelists', $path);
    }
}
