<?php

declare(strict_types=1);

namespace S2J\WebinarService\Tests\Unit;

use PHPUnit\Framework\TestCase;
use S2J\WebinarService\Tests\Unit\Support\RecordFixtures;

use function S2J\WebinarService\build_webinar_request;

final class SurveyMapTest extends TestCase
{
    use RecordFixtures;

    /**
     * @return array<string, array{0: array<string, mixed>, 1: string}>
     */
    public static function supportedQuestionTypes(): array
    {
        return [
            'single' => [
                ['prompt' => 'Pick', 'required' => false, 'answer_kind' => 'single', 'choices' => ['A', 'B']],
                'single',
            ],
            'multiple' => [
                ['prompt' => 'Pick many', 'required' => true, 'answer_kind' => 'multiple', 'choices' => ['A']],
                'multiple',
            ],
            'short' => [
                ['prompt' => 'Short', 'required' => false, 'answer_kind' => 'short'],
                'short_answer',
            ],
            'long' => [
                ['prompt' => 'Long', 'required' => false, 'answer_kind' => 'long'],
                'long_answer',
            ],
            'rating' => [
                [
                    'prompt' => 'Rate',
                    'required' => true,
                    'answer_kind' => 'rating',
                    'score_min' => 1,
                    'score_max' => 5,
                    'label_low' => 'Low',
                    'label_high' => 'High',
                ],
                'rating_scale',
            ],
        ];
    }

    /**
     * @param array<string, mixed> $question
     * @dataProvider supportedQuestionTypes
     */
    public function testMapsSupportedAnswerKinds(array $question, string $expectedType): void
    {
        $built = build_webinar_request(
            'attach_survey',
            $this->validRecord(['webinar_id' => '1']),
            [
                'op' => 'attach_survey',
                'survey_document' => ['questions' => [$question]],
            ]
        );
        $mapped = $built['material']['body']['custom_survey']['questions'][0];
        self::assertSame($expectedType, $mapped['type']);
    }

    public function testUnsupportedAnswerKindIsOmittedFromQuestions(): void
    {
        $built = build_webinar_request(
            'attach_survey',
            $this->validRecord(['webinar_id' => '1']),
            [
                'op' => 'attach_survey',
                'survey_document' => [
                    'questions' => [
                        ['prompt' => 'Match', 'answer_kind' => 'matching'],
                        ['prompt' => 'Ok', 'answer_kind' => 'short'],
                    ],
                ],
            ]
        );
        $questions = $built['material']['body']['custom_survey']['questions'];
        self::assertCount(1, $questions);
        self::assertSame('short_answer', $questions[0]['type']);
    }
}
