<?php

declare(strict_types=1);

namespace S2J\WebinarService\Tests\Unit;

use PHPUnit\Framework\TestCase;
use S2J\WebinarService\Tests\Unit\Support\RecordFixtures;

use function S2J\WebinarService\build_webinar_request;

final class BuildWebinarRequestTest extends TestCase
{
    use RecordFixtures;

    public function testUpdateWithEmptyWebinarIdThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        build_webinar_request('update', $this->validRecord(['webinar_id' => '']));
    }

    public function testAttachSurveyRequiresSurveyDocumentInStep(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        build_webinar_request(
            'attach_survey',
            $this->validRecord(['webinar_id' => '1']),
            ['op' => 'attach_survey']
        );
    }

    public function testAttachSurveyBodyUsesCustomSurveyWrapper(): void
    {
        $step = [
            'op' => 'attach_survey',
            'survey_document' => [
                'questions' => [
                    ['prompt' => 'Q1', 'required' => true, 'answer_kind' => 'short'],
                ],
            ],
        ];
        $built = build_webinar_request('attach_survey', $this->validRecord(['webinar_id' => '1']), $step);
        self::assertArrayHasKey('custom_survey', $built['material']['body']);
        self::assertSame('short_answer', $built['material']['body']['custom_survey']['questions'][0]['type']);
    }
}
