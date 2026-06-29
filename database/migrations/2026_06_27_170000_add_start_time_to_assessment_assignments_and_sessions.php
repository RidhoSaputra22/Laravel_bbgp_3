<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('assessment_assignments')) {
            $this->addColumnIfMissing(
                'assessment_assignments',
                'jam_mulai',
                fn (Blueprint $table) => $table->time('jam_mulai')->nullable()
            );
        }

        if (Schema::hasTable('assessment_assignment_sessions')) {
            $this->addColumnIfMissing(
                'assessment_assignment_sessions',
                'waktu_mulai',
                fn (Blueprint $table) => $table->dateTime('waktu_mulai')->nullable()
            );
            $this->addColumnIfMissing(
                'assessment_assignment_sessions',
                'waktu_selesai',
                fn (Blueprint $table) => $table->dateTime('waktu_selesai')->nullable()
            );
        }

        $this->backfillSessionSchedules();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('assessment_assignment_sessions')) {
            Schema::table('assessment_assignment_sessions', function (Blueprint $table) {
                $columnsToDrop = [];

                foreach (['waktu_mulai', 'waktu_selesai'] as $column) {
                    if (Schema::hasColumn('assessment_assignment_sessions', $column)) {
                        $columnsToDrop[] = $column;
                    }
                }

                if ($columnsToDrop !== []) {
                    $table->dropColumn($columnsToDrop);
                }
            });
        }

        if (Schema::hasTable('assessment_assignments') && Schema::hasColumn('assessment_assignments', 'jam_mulai')) {
            Schema::table('assessment_assignments', function (Blueprint $table) {
                $table->dropColumn('jam_mulai');
            });
        }
    }

    private function backfillSessionSchedules(): void
    {
        if (
            ! Schema::hasTable('assessment_assignments') ||
            ! Schema::hasTable('assessment_assignment_sessions') ||
            ! Schema::hasColumn('assessment_assignments', 'tanggal_mulai') ||
            ! Schema::hasColumn('assessment_assignments', 'jam_mulai') ||
            ! Schema::hasColumn('assessment_assignment_sessions', 'assessment_assignment_id') ||
            ! Schema::hasColumn('assessment_assignment_sessions', 'nomor_sesi') ||
            ! Schema::hasColumn('assessment_assignment_sessions', 'durasi_sesi_jam') ||
            ! Schema::hasColumn('assessment_assignment_sessions', 'waktu_mulai') ||
            ! Schema::hasColumn('assessment_assignment_sessions', 'waktu_selesai')
        ) {
            return;
        }

        $assignments = DB::table('assessment_assignments')
            ->whereNotNull('tanggal_mulai')
            ->whereNotNull('jam_mulai')
            ->orderBy('id')
            ->get([
                'id',
                'tanggal_mulai',
                'jam_mulai',
            ]);

        foreach ($assignments as $assignment) {
            $sessions = DB::table('assessment_assignment_sessions')
                ->where('assessment_assignment_id', $assignment->id)
                ->orderBy('nomor_sesi')
                ->orderBy('id')
                ->get([
                    'id',
                    'durasi_sesi_jam',
                ]);

            if ($sessions->isEmpty()) {
                continue;
            }

            $currentStartAt = Carbon::parse($assignment->tanggal_mulai.' '.$assignment->jam_mulai);

            foreach ($sessions as $session) {
                $durationHours = max((int) $session->durasi_sesi_jam, 1);
                $sessionStartAt = $currentStartAt->copy();
                $sessionEndAt = $sessionStartAt->copy()->addHours($durationHours);

                DB::table('assessment_assignment_sessions')
                    ->where('id', $session->id)
                    ->update([
                        'waktu_mulai' => $sessionStartAt,
                        'waktu_selesai' => $sessionEndAt,
                    ]);

                $currentStartAt = $sessionEndAt;
            }
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
