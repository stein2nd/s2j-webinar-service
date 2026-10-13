<?php

declare(strict_types=1);

namespace S2J\WebinarService\Tests\Unit\Support;

trait RecordFixtures
{
    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    protected function validRecord(array $overrides = []): array
    {
        return array_merge([
            'provider' => 'zoom',
            'topic' => 'Test Webinar',
            'start_at' => '2026-10-09T15:00:00+09:00',
            'timezone' => 'Asia/Tokyo',
            'duration_minutes' => 60,
            'panelists' => [
                ['name' => 'Taro', 'email' => 'Taro@Example.com'],
            ],
        ], $overrides);
    }

    /**
     * @return array{previous_panelists: list<array{name: string, email: string}>}
     */
    protected function matchedPanelistsContext(): array
    {
        return [
            'previous_panelists' => [
                ['name' => 'Taro', 'email' => 'Taro@Example.com'],
            ],
        ];
    }
}
