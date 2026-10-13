<?php

declare(strict_types=1);

namespace S2J\WebinarService\Tests\Unit;

use PHPUnit\Framework\TestCase;
use S2J\WebinarService\Core\ProviderRegistry;
use S2J\WebinarService\Tests\Unit\Support\MockWebinarProvider;
use S2J\WebinarService\Tests\Unit\Support\RecordFixtures;

use function S2J\WebinarService\build_webinar_request;

final class ProviderRegistryTest extends TestCase
{
    use RecordFixtures;

    protected function tearDown(): void
    {
        ProviderRegistry::resetForTesting();
        parent::tearDown();
    }

    public function testRegisteringMockProviderDoesNotBreakZoomCreatePath(): void
    {
        ProviderRegistry::register(MockWebinarProvider::descriptor());

        self::assertNotNull(ProviderRegistry::lookup('mock'));
        self::assertNotNull(ProviderRegistry::lookup('zoom'));

        $zoomMaterial = build_webinar_request('create', $this->validRecord(['provider' => 'zoom']))['material'];
        self::assertSame('/users/me/webinars', $zoomMaterial['path']);

        $mockMaterial = build_webinar_request('create', $this->validRecord(['provider' => 'mock']))['material'];
        self::assertSame('/mock/webinars', $mockMaterial['path']);
    }
}
