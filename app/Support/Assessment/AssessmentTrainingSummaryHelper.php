<?php

namespace App\Support\Assessment;

class AssessmentTrainingSummaryHelper
{
    private const TRAINING_FIELD_NAME = 'pengalaman_pelatihan';

    private const TRAINING_NAME_COLUMN = 'nama_pelatihan';

    private const TRAINING_DURATION_COLUMN = 'durasi_jp';

    public static function buildAttemptSummaryFromSnapshot(array $snapshot, array $answerLookup): array
    {
        return static::buildParticipantSummary(
            static::extractRowsFromSnapshot($snapshot, $answerLookup)
        );
    }

    public static function extractRowsFromSnapshot(array $snapshot, array $answerLookup): array
    {
        $rows = [];

        foreach (($snapshot['assessments'] ?? []) as $assessment) {
            if (! is_array($assessment)) {
                continue;
            }

            foreach (($assessment['forms'] ?? []) as $form) {
                if (! is_array($form)) {
                    continue;
                }

                foreach (($form['fields'] ?? []) as $field) {
                    if (! is_array($field) || ! static::isTrainingField($field)) {
                        continue;
                    }

                    $fieldId = (int) ($field['id'] ?? 0);

                    if ($fieldId < 1) {
                        continue;
                    }

                    $answer = $answerLookup[$fieldId] ?? null;

                    if (! is_array($answer)) {
                        continue;
                    }

                    $rows = array_merge($rows, static::extractRowsFromAnswer($answer));
                }
            }
        }

        return static::normalizeTrainingRows($rows);
    }

    public static function extractRowsFromAnswer(array $answer): array
    {
        $payload = is_array($answer['payload'] ?? null) ? $answer['payload'] : [];

        if (! isset($payload['rows']) && isset($answer['rows'])) {
            $payload['rows'] = $answer['rows'];
        }

        if (! isset($payload['columns']) && isset($answer['columns'])) {
            $payload['columns'] = $answer['columns'];
        }

        return static::extractRowsFromPayload($payload);
    }

    public static function extractRowsFromPayload(mixed $payload): array
    {
        $normalizedPayload = static::normalizePayload($payload);

        if (! static::isTrainingPayload($normalizedPayload)) {
            return [];
        }

        $rows = is_array($normalizedPayload['rows'] ?? null) ? $normalizedPayload['rows'] : [];

        return static::normalizeTrainingRows($rows);
    }

    public static function buildParticipantSummary(array $rows): array
    {
        $normalizedRows = static::normalizeTrainingRows($rows);
        $totalTrainings = count($normalizedRows);
        $totalJp = 0.0;
        $cumulativeJp = 0.0;
        $maxJp = 0.0;
        $chartLabels = [];
        $chartTitles = [];
        $chartProviders = [];
        $chartYears = [];
        $chartJpValues = [];
        $chartCumulativeValues = [];

        foreach ($normalizedRows as $row) {
            $jp = (float) ($row['jp'] ?? 0);
            $totalJp += $jp;
            $cumulativeJp += $jp;
            $maxJp = max($maxJp, $jp);
            $chartLabels[] = (string) ($row['label'] ?? 'Pelatihan');
            $chartTitles[] = (string) ($row['title'] ?? ($row['label'] ?? 'Pelatihan'));
            $chartProviders[] = $row['provider'] ?? null;
            $chartYears[] = $row['year'] ?? null;
            $chartJpValues[] = round($jp, 2);
            $chartCumulativeValues[] = round($cumulativeJp, 2);
        }

        $averageJp = $totalTrainings > 0 ? $totalJp / $totalTrainings : 0.0;

        return [
            'has_data' => $totalTrainings > 0,
            'total_trainings' => $totalTrainings,
            'total_jp' => round($totalJp, 2),
            'formatted_total_jp' => static::formatJp($totalJp),
            'average_jp' => round($averageJp, 2),
            'formatted_average_jp' => static::formatJp($averageJp),
            'max_jp' => round($maxJp, 2),
            'formatted_max_jp' => static::formatJp($maxJp),
            'entries' => $normalizedRows,
            'chart' => [
                'labels' => $chartLabels,
                'titles' => $chartTitles,
                'providers' => $chartProviders,
                'years' => $chartYears,
                'jp_values' => $chartJpValues,
                'cumulative_jp_values' => $chartCumulativeValues,
            ],
        ];
    }

    public static function buildAggregateSummary(iterable $participantRowSets, int $participantTotal = 0): array
    {
        $normalizedParticipantSets = [];

        foreach ($participantRowSets as $participantRows) {
            $normalizedRows = static::normalizeTrainingRows(is_array($participantRows) ? $participantRows : []);

            if ($normalizedRows !== []) {
                $normalizedParticipantSets[] = $normalizedRows;
            }
        }

        if ($participantTotal < 1) {
            $participantTotal = count($normalizedParticipantSets);
        }

        $participantWithTrainingTotal = count($normalizedParticipantSets);
        $totalEntries = 0;
        $totalJp = 0.0;
        $aggregateByOrder = [];

        foreach ($normalizedParticipantSets as $participantRows) {
            foreach ($participantRows as $index => $row) {
                $order = $index + 1;
                $jp = (float) ($row['jp'] ?? 0);

                if (! isset($aggregateByOrder[$order])) {
                    $aggregateByOrder[$order] = [
                        'label' => 'Pelatihan '.$order,
                        'order' => $order,
                        'jp_total' => 0.0,
                        'participant_total' => 0,
                    ];
                }

                $aggregateByOrder[$order]['jp_total'] += $jp;
                $aggregateByOrder[$order]['participant_total']++;
                $totalEntries++;
                $totalJp += $jp;
            }
        }

        ksort($aggregateByOrder);

        $chartLabels = [];
        $chartJpTotals = [];
        $chartParticipantTotals = [];

        foreach ($aggregateByOrder as $aggregateRow) {
            $chartLabels[] = $aggregateRow['label'];
            $chartJpTotals[] = round((float) $aggregateRow['jp_total'], 2);
            $chartParticipantTotals[] = (int) $aggregateRow['participant_total'];
        }

        $averageEntriesPerParticipant = $participantTotal > 0 ? $totalEntries / $participantTotal : 0.0;
        $averageJpPerParticipant = $participantTotal > 0 ? $totalJp / $participantTotal : 0.0;
        $averageJpPerTraining = $totalEntries > 0 ? $totalJp / $totalEntries : 0.0;

        return [
            'has_data' => $totalEntries > 0,
            'participant_total' => $participantTotal,
            'participant_with_training_total' => $participantWithTrainingTotal,
            'participant_without_training_total' => max($participantTotal - $participantWithTrainingTotal, 0),
            'total_entries' => $totalEntries,
            'total_jp' => round($totalJp, 2),
            'formatted_total_jp' => static::formatJp($totalJp),
            'average_entries_per_participant' => round($averageEntriesPerParticipant, 2),
            'formatted_average_entries_per_participant' => number_format($averageEntriesPerParticipant, 2),
            'average_jp_per_participant' => round($averageJpPerParticipant, 2),
            'formatted_average_jp_per_participant' => static::formatJp($averageJpPerParticipant),
            'average_jp_per_training' => round($averageJpPerTraining, 2),
            'formatted_average_jp_per_training' => static::formatJp($averageJpPerTraining),
            'series' => array_values($aggregateByOrder),
            'chart' => [
                'labels' => $chartLabels,
                'jp_totals' => $chartJpTotals,
                'participant_totals' => $chartParticipantTotals,
            ],
        ];
    }

    private static function normalizePayload(mixed $payload): array
    {
        if (is_array($payload)) {
            return $payload;
        }

        if (! is_string($payload)) {
            return [];
        }

        $decodedPayload = json_decode($payload, true);

        return is_array($decodedPayload) ? $decodedPayload : [];
    }

    private static function isTrainingField(array $field): bool
    {
        if (($field['tipe_field'] ?? null) !== 'repeater') {
            return false;
        }

        if (trim((string) ($field['nama_field'] ?? '')) === static::TRAINING_FIELD_NAME) {
            return true;
        }

        $columns = is_array($field['opsi_field']['columns'] ?? null) ? $field['opsi_field']['columns'] : [];

        foreach ($columns as $column) {
            if (
                is_array($column)
                && trim((string) ($column['nama_field'] ?? '')) === static::TRAINING_NAME_COLUMN
            ) {
                return true;
            }
        }

        return false;
    }

    private static function isTrainingPayload(array $payload): bool
    {
        $columns = is_array($payload['columns'] ?? null) ? $payload['columns'] : [];

        foreach ($columns as $column) {
            if (
                is_array($column)
                && trim((string) ($column['nama_field'] ?? '')) === static::TRAINING_NAME_COLUMN
            ) {
                return true;
            }
        }

        $rows = is_array($payload['rows'] ?? null) ? $payload['rows'] : [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            if (
                array_key_exists(static::TRAINING_NAME_COLUMN, $row)
                || array_key_exists(static::TRAINING_DURATION_COLUMN, $row)
            ) {
                return true;
            }
        }

        return false;
    }

    private static function normalizeTrainingRows(array $rows): array
    {
        $normalizedRows = [];

        foreach ($rows as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $trainingName = trim((string) ($row[static::TRAINING_NAME_COLUMN] ?? $row['training_name'] ?? $row['title'] ?? ''));
            $provider = trim((string) ($row['penyelenggara'] ?? $row['provider'] ?? ''));
            $year = trim((string) ($row['tahun'] ?? $row['year'] ?? ''));
            $jp = static::parseJpValue($row[static::TRAINING_DURATION_COLUMN] ?? $row['jp'] ?? null);

            if ($trainingName === '' && $provider === '' && $year === '' && $jp <= 0) {
                continue;
            }

            $order = count($normalizedRows) + 1;
            $label = 'Pelatihan '.$order;

            $normalizedRows[] = [
                'order' => $order,
                'label' => $label,
                'title' => $trainingName !== '' ? $trainingName : $label,
                'training_name' => $trainingName !== '' ? $trainingName : null,
                'provider' => $provider !== '' ? $provider : null,
                'year' => $year !== '' ? $year : null,
                'jp' => round($jp, 2),
                'formatted_jp' => static::formatJp($jp),
            ];
        }

        return $normalizedRows;
    }

    private static function parseJpValue(mixed $value): float
    {
        if (is_int($value) || is_float($value)) {
            return max((float) $value, 0.0);
        }

        if (! is_string($value)) {
            return 0.0;
        }

        $normalized = preg_replace('/[^0-9,\.\-]/', '', $value) ?? '';

        if ($normalized === '' || $normalized === '-') {
            return 0.0;
        }

        if (str_contains($normalized, ',') && str_contains($normalized, '.')) {
            $normalized = str_replace('.', '', $normalized);
            $normalized = str_replace(',', '.', $normalized);
        } elseif (substr_count($normalized, ',') === 1 && substr_count($normalized, '.') === 0) {
            $normalized = str_replace(',', '.', $normalized);
        } elseif (substr_count($normalized, '.') > 1 && substr_count($normalized, ',') === 0) {
            $normalized = str_replace('.', '', $normalized);
        }

        return is_numeric($normalized) ? max((float) $normalized, 0.0) : 0.0;
    }

    private static function formatJp(float $value): string
    {
        if (abs($value - round($value)) < 0.00001) {
            return (string) (int) round($value);
        }

        return number_format($value, 2, ',', '.');
    }
}
