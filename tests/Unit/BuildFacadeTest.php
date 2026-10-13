<?php

declare(strict_types=1);

namespace S2J\WebinarService\Tests\Unit;

use PHPUnit\Framework\TestCase;
use S2J\WebinarService\Tests\Unit\Support\RecordFixtures;

use function S2J\WebinarService\build_webinar_request;

final class BuildFacadeTest extends TestCase
{
    use RecordFixtures;

    public function testUnknownOpThrowsInvalidArgumentException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        build_webinar_request('none', $this->validRecord());
    }

    /**
     * @dataProvider idRequiredOpsProvider
     */
    public function testIdRequiredOpsThrowWhenWebinarIdEmpty(string $op): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $step = match ($op) {
            'add_panelists' => ['op' => $op, 'panelists' => [['name' => 'A', 'email' => 'a@example.com']]],
            'remove_panelists' => ['op' => $op, 'email' => 'a@example.com'],
            'attach_survey' => ['op' => $op, 'survey_document' => ['questions' => []]],
            default => ['op' => $op],
        };
        build_webinar_request($op, $this->validRecord(['webinar_id' => '']), $step);
    }

    /** @return iterable<string, array{0: string}> */
    public static function idRequiredOpsProvider(): iterable
    {
        foreach (['update', 'delete', 'get', 'add_panelists', 'remove_panelists', 'attach_survey'] as $op) {
            yield $op => [$op];
        }
    }
}
