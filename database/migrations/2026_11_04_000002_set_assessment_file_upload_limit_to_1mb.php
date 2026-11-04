<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('assessment_form_fields')) {
            return;
        }

        DB::table('assessment_form_fields')
            ->where('tipe_field', 'file')
            ->orderBy('id')
            ->chunkById(100, function ($fields): void {
                foreach ($fields as $field) {
                    $options = is_array($field->opsi_field)
                        ? $field->opsi_field
                        : json_decode((string) $field->opsi_field, true);
                    $updates = [];

                    if (is_array($options) && is_numeric($options['max_size_kb'] ?? null)) {
                        $options['max_size_kb'] = min(max((int) $options['max_size_kb'], 1), 1024);
                        $updates['opsi_field'] = json_encode($options, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    }

                    if (is_string($field->bantuan) && $field->bantuan !== '') {
                        $help = preg_replace('/\b5\s*MB\b/i', '1 MB', $field->bantuan);

                        if ($help !== null && $help !== $field->bantuan) {
                            $updates['bantuan'] = $help;
                        }
                    }

                    if ($updates !== []) {
                        DB::table('assessment_form_fields')
                            ->where('id', $field->id)
                            ->update($updates);
                    }
                }
            });
    }

    public function down(): void
    {
        // Existing field limits cannot be restored safely after normalization.
    }
};
