<?php

declare(strict_types=1);

namespace S2J\WebinarService\Tests\Unit;

use PHPUnit\Framework\TestCase;
use S2J\WebinarService\Core\Deficiency;

use function S2J\WebinarService\build_tracking_sources_request;
use function S2J\WebinarService\map_tracking_sources_response;

final class TrackingImportTest extends TestCase
{
    public function testBuildEmbedsWebinarIdInPath(): void
    {
        $built = build_tracking_sources_request('zoom', 'abc123');
        self::assertSame('/v2/webinars/abc123/tracking_sources', $built['material']['path']);
    }

    public function testBuildEmptyWebinarIdReturnsTrackingWebinarIdEmpty(): void
    {
        $built = build_tracking_sources_request('zoom', '   ');
        self::assertSame([Deficiency::TRACKING_WEBINAR_ID_EMPTY], $built['deficiencies']);
    }

    public function testBuildUnknownProviderReturnsProviderUnsupported(): void
    {
        $built = build_tracking_sources_request('teams', '1');
        self::assertSame([Deficiency::PROVIDER_UNSUPPORTED], $built['deficiencies']);
    }

    public function testMapArrayJson(): void
    {
        $json = json_encode([
            ['source_name' => 'A', 'tracking_url' => 'https://zoom.us/r/1'],
        ], JSON_THROW_ON_ERROR);
        $mapped = map_tracking_sources_response('zoom', 200, $json);
        self::assertSame([], $mapped['deficiencies']);
        self::assertCount(1, $mapped['items']);
    }

    public function testMapWrappedJson(): void
    {
        $json = json_encode([
            'tracking_sources' => [
                ['source_name' => 'B', 'tracking_url' => 'https://zoom.us/r/2', 'id' => 7],
            ],
        ], JSON_THROW_ON_ERROR);
        $mapped = map_tracking_sources_response('zoom', 200, $json);
        self::assertSame('7', $mapped['items'][0]['external_id']);
    }

    public function testMapDropsInvalidRowsPartialSuccess(): void
    {
        $json = json_encode([
            ['source_name' => 'Good', 'tracking_url' => 'https://zoom.us/r/1'],
            ['source_name' => 'Bad', 'tracking_url' => ''],
        ], JSON_THROW_ON_ERROR);
        $mapped = map_tracking_sources_response('zoom', 200, $json);
        self::assertCount(1, $mapped['items']);
    }

    public function testMapAllInvalidRowsReturnsTrackingSourcesEmpty(): void
    {
        $json = json_encode([
            ['source_name' => '', 'tracking_url' => 'https://zoom.us/r/1'],
        ], JSON_THROW_ON_ERROR);
        $mapped = map_tracking_sources_response('zoom', 200, $json);
        self::assertSame([Deficiency::TRACKING_SOURCES_EMPTY], $mapped['deficiencies']);
    }

    public function testMapNon2xxReturnsTrackingHttpError(): void
    {
        $mapped = map_tracking_sources_response('zoom', 404, '{}');
        self::assertSame([Deficiency::TRACKING_HTTP_ERROR], $mapped['deficiencies']);
        self::assertSame([], $mapped['items']);
    }

    public function testMapInvalidJsonReturnsTrackingBodyInvalid(): void
    {
        $mapped = map_tracking_sources_response('zoom', 200, 'not-json');
        self::assertSame([Deficiency::TRACKING_BODY_INVALID], $mapped['deficiencies']);
    }

    public function testMapUnknownProviderReturnsProviderUnsupported(): void
    {
        $mapped = map_tracking_sources_response('teams', 200, '[]');
        self::assertSame([Deficiency::PROVIDER_UNSUPPORTED], $mapped['deficiencies']);
    }

    public function testMapInvalidTopShapeReturnsTrackingBodyInvalid(): void
    {
        $mapped = map_tracking_sources_response('zoom', 200, '{"sources":[]}');
        self::assertSame([Deficiency::TRACKING_BODY_INVALID], $mapped['deficiencies']);
    }

    public function testMapRejectsSourceNameWithNewline(): void
    {
        $json = json_encode([
            ['source_name' => "Bad\nLine", 'tracking_url' => 'https://zoom.us/r/1'],
            ['source_name' => 'Ok', 'tracking_url' => 'https://zoom.us/r/2'],
        ], JSON_THROW_ON_ERROR);
        $mapped = map_tracking_sources_response('zoom', 200, $json);
        self::assertCount(1, $mapped['items']);
        self::assertSame('Ok', $mapped['items'][0]['source_name']);
    }
}
