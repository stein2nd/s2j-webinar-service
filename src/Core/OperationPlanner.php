<?php

declare(strict_types=1);

namespace S2J\WebinarService\Core;

final class OperationPlanner
{
    /**
     * @param array<string, mixed> $record
     * @param array<string, mixed> $context
     * @return list<array<string, mixed>>
     */
    public static function plan(array $record, array $context = []): array
    {
        $record = RecordNormalizer::normalize($record);

        $intendDelete = ! empty($context['intend_delete']);
        $intendGet = ! empty($context['intend_get']);
        $intendRetry = ! empty($context['intend_retry']);
        $surveyDocument = $context['survey_document'] ?? null;
        $hasSurvey = is_array($surveyDocument) && $surveyDocument !== [];
        $webinarTitle = isset($context['webinar_title']) && is_string($context['webinar_title'])
            ? $context['webinar_title']
            : '';

        /** @var list<array{name?: string, email?: string}> $previousPanelists */
        $previousPanelists = is_array($context['previous_panelists'] ?? null)
            ? $context['previous_panelists']
            : [];

        $webinarId = (string) $record['webinar_id'];
        $status = (string) $record['status'];

        if ($intendDelete) {
            return $webinarId !== '' ? [['op' => 'delete']] : [];
        }

        if ($webinarId === '') {
            return self::appendGet(
                self::appendSurvey(
                    self::appendPanelistDiff(
                        [['op' => 'create']],
                        $record,
                        $previousPanelists
                    ),
                    $hasSurvey,
                    $surveyDocument,
                    $webinarTitle
                ),
                $intendGet
            );
        }

        if ($status === 'dirty') {
            return self::appendGet(
                self::appendSurvey(
                    self::appendPanelistDiff(
                        [['op' => 'update']],
                        $record,
                        $previousPanelists
                    ),
                    $hasSurvey,
                    $surveyDocument,
                    $webinarTitle
                ),
                $intendGet
            );
        }

        if ($status === 'synced') {
            $ops = self::appendPanelistDiff([], $record, $previousPanelists);
            $ops = self::appendSurvey($ops, $hasSurvey, $surveyDocument, $webinarTitle);

            return self::appendGet($ops, $intendGet);
        }

        if ($status === 'error') {
            if ($intendRetry) {
                return self::appendGet(
                    self::appendSurvey(
                        self::appendPanelistDiff(
                            [['op' => 'update']],
                            $record,
                            $previousPanelists
                        ),
                        $hasSurvey,
                        $surveyDocument,
                        $webinarTitle
                    ),
                    $intendGet
                );
            }

            return $intendGet ? [['op' => 'get']] : [];
        }

        return [];
    }

    /**
     * @param list<array<string, mixed>> $ops
     * @param array<string, mixed> $record
     * @param list<array{name?: string, email?: string}> $previousPanelists
     * @return list<array<string, mixed>>
     */
    private static function appendPanelistDiff(array $ops, array $record, array $previousPanelists): array
    {
        /** @var list<array{name: string, email: string}> $current */
        $current = $record['panelists'];
        $diff = PanelistDiff::diff($previousPanelists, $current);

        foreach ($diff['remove'] as $row) {
            $ops[] = ['op' => 'remove_panelists', 'email' => $row['email']];
        }

        if ($diff['add'] !== []) {
            $ops[] = ['op' => 'add_panelists', 'panelists' => $diff['add']];
        }

        return $ops;
    }

    /**
     * @param list<array<string, mixed>> $ops
     * @param mixed $surveyDocument
     * @return list<array<string, mixed>>
     */
    private static function appendSurvey(
        array $ops,
        bool $hasSurvey,
        mixed $surveyDocument,
        string $webinarTitle
    ): array {
        if (! $hasSurvey || ! is_array($surveyDocument)) {
            return $ops;
        }

        $step = [
            'op' => 'attach_survey',
            'survey_document' => $surveyDocument,
        ];
        if ($webinarTitle !== '') {
            $step['webinar_title'] = $webinarTitle;
        }

        $ops[] = $step;

        return $ops;
    }

    /**
     * @param list<array<string, mixed>> $ops
     * @return list<array<string, mixed>>
     */
    private static function appendGet(array $ops, bool $intendGet): array
    {
        if ($intendGet) {
            $ops[] = ['op' => 'get'];
        }

        return $ops;
    }
}
