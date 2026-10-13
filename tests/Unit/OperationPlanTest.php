<?php

declare(strict_types=1);

namespace S2J\WebinarService\Tests\Unit;

use PHPUnit\Framework\TestCase;
use S2J\WebinarService\Tests\Unit\Support\RecordFixtures;

use function S2J\WebinarService\plan_webinar_operations;

final class OperationPlanTest extends TestCase
{
    use RecordFixtures;

    public function testPlanDoesNotRevalidate(): void
    {
        $ops = plan_webinar_operations($this->validRecord(['topic' => '']), []);
        self::assertSame('create', $ops[0]['op']);
    }

    public function testEmptyPlanWhenNothingToDo(): void
    {
        $ops = plan_webinar_operations(
            $this->validRecord(['webinar_id' => '1', 'status' => 'synced']),
            $this->matchedPanelistsContext()
        );
        self::assertSame([], $ops);
    }

    public function testDeleteOnlyNeverAppendsGet(): void
    {
        $ops = plan_webinar_operations(
            $this->validRecord(['webinar_id' => '1', 'status' => 'synced']),
            ['intend_delete' => true, 'intend_get' => true]
        );
        self::assertSame([['op' => 'delete']], $ops);
    }

    public function testCreateWithIntendGetAppendsGetAtEnd(): void
    {
        $ops = plan_webinar_operations($this->validRecord(), ['intend_get' => true]);
        self::assertSame(['create', 'add_panelists', 'get'], array_column($ops, 'op'));
    }

    public function testCreateWithoutSurveyDocumentIsAllowed(): void
    {
        $ops = plan_webinar_operations($this->validRecord(), []);
        self::assertSame(['create', 'add_panelists'], array_column($ops, 'op'));
    }

    public function testColumnOrderUpdateRemoveAddSurveyGet(): void
    {
        $record = $this->validRecord([
            'webinar_id' => '1',
            'status' => 'dirty',
            'panelists' => [
                ['name' => 'New', 'email' => 'new@example.com'],
            ],
        ]);
        $context = array_merge($this->matchedPanelistsContext(), [
            'survey_document' => ['questions' => []],
            'intend_get' => true,
        ]);
        $ops = plan_webinar_operations($record, $context);
        self::assertSame(
            ['update', 'remove_panelists', 'add_panelists', 'attach_survey', 'get'],
            array_column($ops, 'op')
        );
        self::assertArrayHasKey('survey_document', $ops[3]);
    }

    public function testErrorWithIdEmptyPlansCreate(): void
    {
        $ops = plan_webinar_operations($this->validRecord([
            'status' => 'error',
            'last_error' => 'failed',
        ]), []);
        self::assertSame('create', $ops[0]['op']);
    }

    public function testErrorWithIdNoRetryEmptyOrGetOnly(): void
    {
        $record = $this->validRecord(['webinar_id' => '1', 'status' => 'error']);
        self::assertSame([], plan_webinar_operations($record, $this->matchedPanelistsContext()));
        self::assertSame(
            [['op' => 'get']],
            plan_webinar_operations($record, array_merge($this->matchedPanelistsContext(), ['intend_get' => true]))
        );
    }

    public function testIntendGetOnlyReturnsGetWhenSynced(): void
    {
        $ops = plan_webinar_operations(
            $this->validRecord(['webinar_id' => '1', 'status' => 'synced']),
            array_merge($this->matchedPanelistsContext(), ['intend_get' => true])
        );
        self::assertSame([['op' => 'get']], $ops);
    }

    public function testIntendRetryWithoutPanelistDiffMatchesDirtyUpdateColumn(): void
    {
        $ops = plan_webinar_operations(
            $this->validRecord(['webinar_id' => '1', 'status' => 'error']),
            array_merge($this->matchedPanelistsContext(), ['intend_retry' => true])
        );
        self::assertSame([['op' => 'update']], $ops);
    }

    public function testAttachSurveyStepIncludesSurveyDocument(): void
    {
        $doc = ['questions' => []];
        $ops = plan_webinar_operations(
            $this->validRecord(['webinar_id' => '1', 'status' => 'synced']),
            array_merge($this->matchedPanelistsContext(), ['survey_document' => $doc])
        );
        self::assertSame('attach_survey', $ops[0]['op']);
        self::assertSame($doc, $ops[0]['survey_document']);
    }

    public function testRemovePanelistsOneEmailPerOperationElement(): void
    {
        $record = $this->validRecord([
            'webinar_id' => '1',
            'status' => 'synced',
            'panelists' => [['name' => 'B', 'email' => 'b@example.com']],
        ]);
        $context = [
            'previous_panelists' => [
                ['name' => 'A', 'email' => 'a@example.com'],
                ['name' => 'Taro', 'email' => 'Taro@Example.com'],
            ],
        ];
        $ops = plan_webinar_operations($record, $context);
        $removes = array_values(array_filter($ops, static fn (array $op): bool => $op['op'] === 'remove_panelists'));
        self::assertCount(2, $removes);
        self::assertSame('a@example.com', $removes[0]['email']);
        self::assertSame('taro@example.com', $removes[1]['email']);
    }
}
