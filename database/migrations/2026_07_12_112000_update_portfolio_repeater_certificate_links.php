<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('assessment_form_fields')) {
            return;
        }

        $fieldColumnMap = $this->repeaterColumnMap();

        DB::table('assessment_form_fields')
            ->select(['id', 'nama_field', 'opsi_field'])
            ->where('tipe_field', 'repeater')
            ->whereIn('nama_field', array_keys($fieldColumnMap))
            ->orderBy('id')
            ->chunkById(100, function ($fields) use ($fieldColumnMap) {
                foreach ($fields as $field) {
                    $config = $this->decodeRepeaterConfig($field->opsi_field ?? null);
                    $columns = collect($config['columns'] ?? [])
                        ->filter(fn ($column) => is_array($column))
                        ->values()
                        ->all();

                    if ($columns === []) {
                        continue;
                    }

                    $columnDefinition = $fieldColumnMap[$field->nama_field] ?? null;

                    if (! is_array($columnDefinition)) {
                        continue;
                    }

                    $alreadyExists = collect($columns)->contains(function ($column) use ($columnDefinition) {
                        return trim((string) ($column['nama_field'] ?? '')) === $columnDefinition['nama_field'];
                    });

                    if ($alreadyExists) {
                        continue;
                    }

                    $columns[] = $columnDefinition;
                    $config['columns'] = array_values($columns);

                    $this->persistRepeaterConfig((int) $field->id, $config);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('assessment_form_fields')) {
            return;
        }

        $fieldColumnMap = $this->repeaterColumnMap();

        DB::table('assessment_form_fields')
            ->select(['id', 'nama_field', 'opsi_field'])
            ->where('tipe_field', 'repeater')
            ->whereIn('nama_field', array_keys($fieldColumnMap))
            ->orderBy('id')
            ->chunkById(100, function ($fields) use ($fieldColumnMap) {
                foreach ($fields as $field) {
                    $config = $this->decodeRepeaterConfig($field->opsi_field ?? null);
                    $columns = collect($config['columns'] ?? [])
                        ->filter(fn ($column) => is_array($column))
                        ->reject(function ($column) use ($field, $fieldColumnMap) {
                            return trim((string) ($column['nama_field'] ?? '')) === ($fieldColumnMap[$field->nama_field]['nama_field'] ?? '');
                        })
                        ->values()
                        ->all();

                    if (($config['columns'] ?? []) === $columns) {
                        continue;
                    }

                    $config['columns'] = $columns;
                    $this->persistRepeaterConfig((int) $field->id, $config);
                }
            });
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function repeaterColumnMap(): array
    {
        return [
            'pengalaman_pelatihan' => [
                'label' => 'Link Google Drive Sertifikat',
                'nama_field' => 'link_google_drive_sertifikat',
                'tipe_field' => 'url',
                'placeholder' => 'https://drive.google.com/...',
                'is_required' => false,
            ],
            'pengalaman_mengajar' => [
                'label' => 'Link Google Drive Sertifikat / SK',
                'nama_field' => 'link_google_drive_sertifikat_sk',
                'tipe_field' => 'url',
                'placeholder' => 'https://drive.google.com/...',
                'is_required' => false,
            ],
            'penguasaan_profesional' => [
                'label' => 'Link Google Drive Sertifikat / SK',
                'nama_field' => 'link_google_drive_sertifikat_sk',
                'tipe_field' => 'url',
                'placeholder' => 'https://drive.google.com/...',
                'is_required' => false,
            ],
            'karya_inovasi_best_practice' => [
                'label' => 'Link Google Drive Sertifikat / SK',
                'nama_field' => 'link_google_drive_sertifikat_sk',
                'tipe_field' => 'url',
                'placeholder' => 'https://drive.google.com/...',
                'is_required' => false,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeRepeaterConfig(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function persistRepeaterConfig(int $fieldId, array $config): void
    {
        DB::table('assessment_form_fields')
            ->where('id', $fieldId)
            ->update([
                'opsi_field' => json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'updated_at' => now(),
            ]);
    }
};
