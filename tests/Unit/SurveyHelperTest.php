<?php

declare(strict_types=1);

namespace S2J\WebinarService\Tests\Unit;

use PHPUnit\Framework\TestCase;
use S2J\WebinarService\Tests\Unit\Support\RecordFixtures;

use function S2J\WebinarService\build_survey_update_request;
use function S2J\WebinarService\build_webinar_request;

final class SurveyHelperTest extends TestCase
{
    use RecordFixtures;

    /** @return array<string, mixed> */
    private function sampleDocument(): array
    {
        return [
            'questions' => [
                [
                    'prompt' => 'Q',
                    'required' => true,
                    'answer_kind' => 'short',
                    'internal_name' => 'must_not_send',
                ],
            ],
        ];
    }

    public function testBuildSurveyUpdateRequestMatchesAttachSurveySot(): void
    {
        $doc = $this->sampleDocument();
        $title = 'Topic Fallback';
        $helper = build_survey_update_request($doc, '99', $title);
        $sot = build_webinar_request(
            'attach_survey',
            $this->validRecord(['webinar_id' => '99', 'topic' => 'ignored when step title set']),
            ['op' => 'attach_survey', 'survey_document' => $doc, 'webinar_title' => $title]
        );
        self::assertSame($sot['material'], $helper['material']);
    }

    public function testAttachSurveyFallsBackToRecordTopicWhenStepTitleOmitted(): void
    {
        $doc = $this->sampleDocument();
        $record = $this->validRecord(['webinar_id' => '1', 'topic' => 'Event Topic']);
        $body = build_webinar_request('attach_survey', $record, [
            'op' => 'attach_survey',
            'survey_document' => $doc,
        ])['material']['body'];
        self::assertSame('Event Topic', $body['custom_survey']['title']);
    }

    public function testBuildSurveyUpdateRequestNullTitleMatchesSotWithoutStepTitle(): void
    {
        $doc = $this->sampleDocument();
        $helper = build_survey_update_request($doc, '1', null);
        $sot = build_webinar_request(
            'attach_survey',
            ['provider' => 'zoom', 'webinar_id' => '1'],
            ['op' => 'attach_survey', 'survey_document' => $doc]
        );
        self::assertSame($sot['material'], $helper['material']);
    }

    public function testSurveyTopLevelFlagsAndOmitsInternalNameInQuestions(): void
    {
        $body = build_survey_update_request($this->sampleDocument(), '1', 'Custom')['material']['body'];
        self::assertTrue($body['show_in_the_browser']);
        self::assertFalse($body['show_in_the_follow_up_email']);
        self::assertSame('Custom', $body['custom_survey']['title']);
        self::assertArrayNotHasKey('internal_name', $body['custom_survey']['questions'][0]);
    }
}
