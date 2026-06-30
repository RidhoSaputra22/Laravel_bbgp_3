<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('assessment_attempts')) {
            $this->addColumnIfMissing(
                'assessment_attempts',
                'deadline_at',
                fn (Blueprint $table) => $table->timestamp('deadline_at')->nullable()->after('started_at')
            );
            $this->addColumnIfMissing(
                'assessment_attempts',
                'completion_mode',
                fn (Blueprint $table) => $table->enum('completion_mode', ['manual', 'timeout'])->nullable()->after('submitted_at')
            );
            $this->addColumnIfMissing(
                'assessment_attempts',
                'timed_out_at',
                fn (Blueprint $table) => $table->timestamp('timed_out_at')->nullable()->after('completion_mode')
            );
        }

        if (Schema::hasTable('assessment_assignment_targets')) {
            $this->addColumnIfMissing(
                'assessment_assignment_targets',
                'deadline_at',
                fn (Blueprint $table) => $table->timestamp('deadline_at')->nullable()->after('started_at')
            );
            $this->addColumnIfMissing(
                'assessment_assignment_targets',
                'completion_mode',
                fn (Blueprint $table) => $table->enum('completion_mode', ['manual', 'timeout'])->nullable()->after('submitted_at')
            );
            $this->addColumnIfMissing(
                'assessment_assignment_targets',
                'timed_out_at',
                fn (Blueprint $table) => $table->timestamp('timed_out_at')->nullable()->after('completion_mode')
            );
        }

        $this->backfillTimingAuditFields();
    }

    public function down(): void
    {
        if (Schema::hasTable('assessment_attempts')) {
            Schema::table('assessment_attempts', function (Blueprint $table) {
                $columns = [];

                foreach (['deadline_at', 'completion_mode', 'timed_out_at'] as $column) {
                    if (Schema::hasColumn('assessment_attempts', $column)) {
                        $columns[] = $column;
                    }
                }

                if ($columns !== []) {
                    $table->dropColumn($columns);
                }
            });
        }

        if (Schema::hasTable('assessment_assignment_targets')) {
            Schema::table('assessment_assignment_targets', function (Blueprint $table) {
                $columns = [];

                foreach (['deadline_at', 'completion_mode', 'timed_out_at'] as $column) {
                    if (Schema::hasColumn('assessment_assignment_targets', $column)) {
                        $columns[] = $column;
                    }
                }

                if ($columns !== []) {
                    $table->dropColumn($columns);
                }
            });
        }
    }

    private function backfillTimingAuditFields(): void
    {
        if (
            ! Schema::hasTable('assessment_assignment_targets') ||
            ! Schema::hasTable('assessment_assignments') ||
            ! Schema::hasTable('assessment_attempts') ||
            ! Schema::hasColumn('assessment_assignment_targets', 'id') ||
            ! Schema::hasColumn('assessment_assignment_targets', 'assessment_assignment_id') ||
            ! Schema::hasColumn('assessment_assignment_targets', 'assessment_assignment_session_id') ||
            ! Schema::hasColumn('assessment_assignment_targets', 'started_at') ||
            ! Schema::hasColumn('assessment_assignment_targets', 'submitted_at') ||
            ! Schema::hasColumn('assessment_assignment_targets', 'deadline_at') ||
            ! Schema::hasColumn('assessment_assignment_targets', 'completion_mode') ||
            ! Schema::hasColumn('assessment_assignment_targets', 'timed_out_at') ||
            ! Schema::hasColumn('assessment_assignments', 'durasi_sesi_jam') ||
            ! Schema::hasColumn('assessment_assignments', 'tanggal_selesai') ||
            ! Schema::hasColumn('assessment_attempts', 'assessment_assignment_target_id') ||
            ! Schema::hasColumn('assessment_attempts', 'started_at') ||
            ! Schema::hasColumn('assessment_attempts', 'submitted_at') ||
            ! Schema::hasColumn('assessment_attempts', 'result_summary') ||
            ! Schema::hasColumn('assessment_attempts', 'deadline_at') ||
            ! Schema::hasColumn('assessment_attempts', 'completion_mode') ||
            ! Schema::hasColumn('assessment_attempts', 'timed_out_at')
        ) {
            return;
        }

        $targetQuery = DB::table('assessment_assignment_targets as target')
            ->join('assessment_assignments as assignment', 'assignment.id', '=', 'target.assessment_assignment_id')
            ->leftJoin('assessment_assignment_sessions as session', 'session.id', '=', 'target.assessment_assignment_session_id')
            ->leftJoin('assessment_attempts as attempt', 'attempt.assessment_assignment_target_id', '=', 'target.id')
            ->select([
                'target.id as target_id',
                'target.started_at as target_started_at',
                'target.submitted_at as target_submitted_at',
                'target.deadline_at as target_deadline_at',
                'target.completion_mode as target_completion_mode',
                'target.timed_out_at as target_timed_out_at',
                'assignment.durasi_sesi_jam as assignment_duration_hours',
                'assignment.tanggal_selesai as assignment_end_date',
                'session.durasi_sesi_jam as session_duration_hours',
                'session.waktu_selesai as session_end_at',
                'attempt.id as attempt_id',
                'attempt.started_at as attempt_started_at',
                'attempt.submitted_at as attempt_submitted_at',
                'attempt.deadline_at as attempt_deadline_at',
                'attempt.completion_mode as attempt_completion_mode',
                'attempt.timed_out_at as attempt_timed_out_at',
                'attempt.result_summary as attempt_result_summary',
            ])
            ->orderBy('target.id');

        $targetQuery->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                $startedAt = $row->target_started_at ?: $row->attempt_started_at;
                $submittedAt = $row->target_submitted_at ?: $row->attempt_submitted_at;
                $resolvedDeadlineAt = $this->resolveDeadlineAt($row, $startedAt);
                $resolvedCompletionMode = $this->resolveCompletionMode($row, $submittedAt);
                $resolvedTimedOutAt = $resolvedCompletionMode === 'timeout' ? $submittedAt : null;

                DB::table('assessment_assignment_targets')
                    ->where('id', $row->target_id)
                    ->update([
                        'deadline_at' => $row->target_deadline_at ?: $resolvedDeadlineAt,
                        'completion_mode' => $row->target_completion_mode ?: $resolvedCompletionMode,
                        'timed_out_at' => $row->target_timed_out_at ?: $resolvedTimedOutAt,
                    ]);

                if (! $row->attempt_id) {
                    continue;
                }

                DB::table('assessment_attempts')
                    ->where('id', $row->attempt_id)
                    ->update([
                        'deadline_at' => $row->attempt_deadline_at ?: $resolvedDeadlineAt,
                        'completion_mode' => $row->attempt_completion_mode ?: $resolvedCompletionMode,
                        'timed_out_at' => $row->attempt_timed_out_at ?: $resolvedTimedOutAt,
                    ]);
            }
        }, 'target.id', 'target_id');
    }

    private function resolveDeadlineAt(object $row, mixed $startedAt): ?string
    {
        if (! $startedAt) {
            return $this->normalizeDateTime($row->session_end_at)
                ?: $this->normalizeAssignmentEndAt($row->assignment_end_date);
        }

        $startedAtCarbon = Carbon::parse($startedAt);
        $durationHours = (int) ($row->session_duration_hours ?: 0);

        if ($durationHours <= 0) {
            $durationHours = (int) ($row->assignment_duration_hours ?: 0);
        }
        $relativeDeadlineAt = $durationHours > 0
            ? $startedAtCarbon->copy()->addHours($durationHours)
            : null;
        $sessionDeadlineAt = $this->normalizeDateTime($row->session_end_at);
        $assignmentDeadlineAt = $this->normalizeAssignmentEndAt($row->assignment_end_date);
        $effectiveDeadlineAt = $relativeDeadlineAt ? $relativeDeadlineAt->format('Y-m-d H:i:s') : null;

        foreach ([$sessionDeadlineAt, $assignmentDeadlineAt] as $candidateDeadlineAt) {
            if (! $candidateDeadlineAt) {
                continue;
            }

            if (! $effectiveDeadlineAt || Carbon::parse($candidateDeadlineAt)->lt(Carbon::parse($effectiveDeadlineAt))) {
                $effectiveDeadlineAt = $candidateDeadlineAt;
            }
        }

        return $effectiveDeadlineAt;
    }

    private function resolveCompletionMode(object $row, mixed $submittedAt): ?string
    {
        if (! $submittedAt) {
            return null;
        }

        $summary = $row->attempt_result_summary
            ? json_decode((string) $row->attempt_result_summary, true)
            : [];

        return ($summary['submission_mode'] ?? null) === 'deadline_auto'
            ? 'timeout'
            : 'manual';
    }

    private function normalizeDateTime(mixed $value): ?string
    {
        return $value ? Carbon::parse($value)->format('Y-m-d H:i:s') : null;
    }

    private function normalizeAssignmentEndAt(mixed $value): ?string
    {
        return $value ? Carbon::parse($value)->endOfDay()->format('Y-m-d H:i:s') : null;
    }

    private function addColumnIfMissing(string $tableName, string $column, callable $definition): void
    {
        if (Schema::hasColumn($tableName, $column)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($definition) {
            $definition($table);
        });
    }
};
