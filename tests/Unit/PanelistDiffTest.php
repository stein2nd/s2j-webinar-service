<?php

declare(strict_types=1);

namespace S2J\WebinarService\Tests\Unit;

use PHPUnit\Framework\TestCase;

use function S2J\WebinarService\build_webinar_request;
use function S2J\WebinarService\diff_panelists;

final class PanelistDiffTest extends TestCase
{
    public function testAddAndRemove(): void
    {
        $diff = diff_panelists(
            [['name' => 'Old', 'email' => 'old@example.com']],
            [['name' => 'New', 'email' => 'new@example.com']]
        );
        self::assertSame([['email' => 'old@example.com']], $diff['remove']);
        self::assertSame([['name' => 'New', 'email' => 'new@example.com']], $diff['add']);
    }

    public function testNameOnlyChangeIsRemoveThenAdd(): void
    {
        $diff = diff_panelists(
            [['name' => 'Taro', 'email' => 'taro@example.com']],
            [['name' => 'Taro Yamada', 'email' => 'taro@example.com']]
        );
        self::assertSame([['email' => 'taro@example.com']], $diff['remove']);
        self::assertSame([['name' => 'Taro Yamada', 'email' => 'taro@example.com']], $diff['add']);
    }

    public function testReorderOnlyProducesNoDiff(): void
    {
        $diff = diff_panelists(
            [
                ['name' => 'A', 'email' => 'a@example.com'],
                ['name' => 'B', 'email' => 'b@example.com'],
            ],
            [
                ['name' => 'B', 'email' => 'b@example.com'],
                ['name' => 'A', 'email' => 'a@example.com'],
            ]
        );
        self::assertSame([], $diff['add']);
        self::assertSame([], $diff['remove']);
    }

    public function testNameIsTrimmedInDiffAdd(): void
    {
        $diff = diff_panelists([], [['name' => '  Taro  ', 'email' => 'taro@example.com']]);
        self::assertSame('Taro', $diff['add'][0]['name']);
    }

    public function testAddPanelistsRequestBodyLowercasesEmail(): void
    {
        $record = [
            'provider' => 'zoom',
            'topic' => 'T',
            'start_at' => '2026-10-09T15:00:00+09:00',
            'timezone' => 'Asia/Tokyo',
            'duration_minutes' => 60,
            'panelists' => [['name' => 'Taro', 'email' => 'Taro@Example.com']],
            'webinar_id' => '1',
            'status' => 'synced',
        ];
        $built = build_webinar_request('add_panelists', $record, [
            'op' => 'add_panelists',
            'panelists' => [['name' => 'Taro', 'email' => 'Taro@Example.com']],
        ]);
        self::assertSame('taro@example.com', $built['material']['body']['panelists'][0]['email']);
    }
}
