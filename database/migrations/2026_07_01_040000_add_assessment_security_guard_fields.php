<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('assessment_assignments')) {
            $this->addColumnIfMissing(
                'assessment_assignments',
                'security_config',
                fn (Blueprint $table) => $table->json('security_config')->nullable()->after('durasi_sesi_jam')
            );
        }

        if (Schema::hasTable('assessment_attempts')) {
            $this->addColumnIfMissing(
                'assessment_attempts',
                'security_config_snapshot',
                fn (Blueprint $table) => $table->json('security_config_snapshot')->nullable()->after('structure_snapshot')
            );
            $this->addColumnIfMissing(
                'assessment_attempts',
                'serious_violation_count',
                fn (Blueprint $table) => $table->unsignedInteger('serious_violation_count')->default(0)->after('answered_required_questions')
            );
            $this->addColumnIfMissing(
                'assessment_attempts',
                'warning_violation_count',
                fn (Blueprint $table) => $table->unsignedInteger('warning_violation_count')->default(0)->after('serious_violation_count')
            );
            $this->addColumnIfMissing(
                'assessment_attempts',
                'last_violation_at',
                fn (Blueprint $table) => $table->timestamp('last_violation_at')->nullable()->after('timed_out_at')
            );
            $this->addColumnIfMissing(
                'assessment_attempts',
                'disqualified_at',
                fn (Blueprint $table) => $table->timestamp('disqualified_at')->nullable()->after('last_violation_at')
            );
            $this->addColumnIfMissing(
                'assessment_attempts',
                'disqualification_reason',
                fn (Blueprint $table) => $table->text('disqualification_reason')->nullable()->after('disqualified_at')
            );
        }

        if (! Schema::hasTable('assessment_attempt_security_events')) {
            Schema::create('assessment_attempt_security_events', function (Blueprint $table) {
                $table->id();
                $table->foreignId('assessment_attempt_id')
                    ->constrained('assessment_attempts')
                    ->cascadeOnDelete();
                $table->string('event_key', 100);
                $table->string('violation_type', 32)->nullable();
                $table->string('lock_mode', 32)->nullable();
                $table->text('message');
                $table->boolean('counts_toward_disqualify')->default(false);
                $table->timestamp('client_occurred_at')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();

                $table->index(['assessment_attempt_id', 'created_at'], 'assessment_attempt_security_events_attempt_created_idx');
                $table->index(['event_key', 'violation_type'], 'assessment_attempt_security_events_key_type_idx');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_attempt_security_events');

        if (Schema::hasTable('assessment_attempts')) {
            Schema::table('assessment_attempts', function (Blueprint $table) {
                $columns = [];

                foreach ([
                    'security_config_snapshot',
                    'serious_violation_count',
                    'warning_violation_count',
                    'last_violation_at',
                    'disqualified_at',
                    'disqualification_reason',
                ] as $column) {
                    if (Schema::hasColumn('assessment_attempts', $column)) {
                        $columns[] = $column;
                    }
                }

                if ($columns !== []) {
                    $table->dropColumn($columns);
                }
            });
        }

        if (Schema::hasTable('assessment_assignments')) {
            Schema::table('assessment_assignments', function (Blueprint $table) {
                if (Schema::hasColumn('assessment_assignments', 'security_config')) {
                    $table->dropColumn('security_config');
                }
            });
        }
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
