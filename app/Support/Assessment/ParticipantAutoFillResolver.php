<?php

namespace App\Support\Assessment;

use App\Models\Guru;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ParticipantAutoFillResolver
{
    /**
     * @return array<string, string>
     */
    public function options(): array
    {
        return [
            'nama_lengkap' => 'Nama Lengkap',
            'no_ktp' => 'NIK / No. KTP',
            'nip' => 'NIP',
            'nuptk' => 'NUPTK',
            'nip_nuptk' => 'NIP / NUPTK',
            'golongan' => 'Golongan',
            'jabatan' => 'Jabatan',
            'status_kepegawaian' => 'Status Kepegawaian',
            'eksternal_jabatan' => 'Ketenagaan',
            'jenis_jabatan' => 'Jenis Jabatan',
            'kategori_jabatan' => 'Kategori Jabatan',
            'tugas_jabatan' => 'Tugas Jabatan',
            'latar_jabatan' => 'Latar Jabatan',
            'gender' => 'Jenis Kelamin',
            'tempat_lahir' => 'Tempat Lahir',
            'tgl_lahir' => 'Tanggal Lahir',
            'agama' => 'Agama',
            'pendidikan' => 'Pendidikan Terakhir',
            'email' => 'Email',
            'no_hp' => 'No. HP',
            'no_wa' => 'No. WhatsApp',
            'satuan_pendidikan' => 'Satuan Pendidikan',
            'npsn_sekolah' => 'NPSN Sekolah',
            'kabupaten' => 'Kabupaten / Kota',
            'alamat_satuan' => 'Alamat Satuan Pendidikan',
            'alamat_rumah' => 'Alamat Rumah',
            'npwp' => 'NPWP',
            'no_rek' => 'No. Rekening',
            'jenis_bank' => 'Jenis Bank',
        ];
    }

    public function supportsFieldType(?string $fieldType): bool
    {
        return in_array((string) $fieldType, [
            'text',
            'textarea',
            'number',
            'email',
            'date',
            'select',
            'radio',
            'checkbox',
        ], true);
    }

    public function normalizeSource(?string $source, ?string $fieldType = null): ?string
    {
        $source = trim((string) $source);

        if ($source === '' || ! array_key_exists($source, $this->options())) {
            return null;
        }

        if ($fieldType !== null && ! $this->supportsFieldType($fieldType)) {
            return null;
        }

        return $source;
    }

    public function label(?string $source): ?string
    {
        $normalizedSource = $this->normalizeSource($source);

        return $normalizedSource ? $this->options()[$normalizedSource] : null;
    }

    public function inferSourceFromField(?string $label, ?string $fieldName = null): ?string
    {
        $haystack = $this->normalizeKeywordSource(
            collect([$fieldName, $label])->filter()->implode(' ')
        );

        if ($haystack === '') {
            return null;
        }

        if (in_array($haystack, ['nama', 'nama lengkap'], true)) {
            return 'nama_lengkap';
        }

        foreach ($this->inferenceMap() as $source => $needles) {
            foreach ($needles as $needle) {
                if (Str::contains($haystack, $needle)) {
                    return $source;
                }
            }
        }

        return null;
    }

    public function resolveForField(array $field, Guru $guru): ?array
    {
        $fieldType = trim((string) ($field['tipe_field'] ?? 'text')) ?: 'text';
        $source = $this->normalizeSource($field['autofill_source'] ?? null, $fieldType);

        if (! $source) {
            return null;
        }

        $rawValue = $this->resolveRawValue($source, $guru);

        if (is_array($rawValue)) {
            $rawValue = array_values(array_filter(array_map(
                fn ($item) => trim((string) $item),
                $rawValue
            ), fn ($item) => $item !== ''));
        } else {
            $rawValue = trim((string) ($rawValue ?? ''));
        }

        if ($rawValue === '' || $rawValue === []) {
            return null;
        }

        return match ($fieldType) {
            'checkbox' => $this->resolveCheckboxFieldValue($field, $source, $rawValue),
            'select', 'radio' => $this->resolveChoiceFieldValue($field, $source, (string) $rawValue),
            'number' => $this->resolveNumberFieldValue($source, (string) $rawValue),
            'email' => $this->resolveEmailFieldValue($source, (string) $rawValue),
            'date' => $this->resolveDateFieldValue($source, (string) $rawValue),
            default => $this->resolveTextFieldValue($fieldType, $source, (string) $rawValue),
        };
    }

    private function resolveTextFieldValue(string $fieldType, string $source, string $value): ?array
    {
        return $value === '' ? null : [
            'source' => $source,
            'source_label' => $this->label($source),
            'value' => $value,
            'answer' => [
                'text' => $value,
                'payload' => [
                    'type' => $fieldType,
                    'value' => $value,
                ],
            ],
        ];
    }

    private function resolveNumberFieldValue(string $source, string $value): ?array
    {
        if ($value === '' || ! is_numeric($value)) {
            return null;
        }

        return [
            'source' => $source,
            'source_label' => $this->label($source),
            'value' => $value,
            'answer' => [
                'text' => $value,
                'payload' => [
                    'type' => 'number',
                    'value' => $value,
                ],
            ],
        ];
    }

    private function resolveEmailFieldValue(string $source, string $value): ?array
    {
        if ($value === '' || ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        return [
            'source' => $source,
            'source_label' => $this->label($source),
            'value' => $value,
            'answer' => [
                'text' => $value,
                'payload' => [
                    'type' => 'email',
                    'value' => $value,
                ],
            ],
        ];
    }

    private function resolveDateFieldValue(string $source, string $value): ?array
    {
        try {
            $normalized = Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $exception) {
            $normalized = null;
        }

        if (! $normalized) {
            return null;
        }

        return [
            'source' => $source,
            'source_label' => $this->label($source),
            'value' => $normalized,
            'answer' => [
                'text' => $normalized,
                'payload' => [
                    'type' => 'date',
                    'value' => $normalized,
                ],
            ],
        ];
    }

    private function resolveChoiceFieldValue(array $field, string $source, string $rawValue): ?array
    {
        $matchedOption = collect(ChoiceOptionNormalizer::normalizeMany($field['opsi_field'] ?? []))
            ->first(fn (array $option) => $this->matchesChoiceOption($option, $rawValue));

        if (! is_array($matchedOption)) {
            return null;
        }

        $value = trim((string) ($matchedOption['value'] ?? ''));

        if ($value === '') {
            return null;
        }

        return [
            'source' => $source,
            'source_label' => $this->label($source),
            'value' => $value,
            'answer' => [
                'text' => $value,
                'payload' => array_filter([
                    'type' => $field['tipe_field'] ?? 'select',
                    'value' => $value,
                    'label' => trim((string) ($matchedOption['label'] ?? '')) ?: null,
                    'score' => is_numeric($matchedOption['score'] ?? null) ? (float) $matchedOption['score'] : null,
                    'level_kompetensi' => $matchedOption['level_kompetensi'] ?? null,
                    'level_kompetensi_label' => $matchedOption['level_kompetensi_label'] ?? null,
                ], static fn ($item) => $item !== null && $item !== ''),
            ],
        ];
    }

    private function resolveCheckboxFieldValue(array $field, string $source, array|string $rawValue): ?array
    {
        $selectedRawValues = is_array($rawValue)
            ? $rawValue
            : (preg_split('/[\r\n,;|]+/', $rawValue) ?: []);

        $selectedRawValues = array_values(array_filter(array_map(
            fn ($item) => trim((string) $item),
            $selectedRawValues
        ), fn ($item) => $item !== ''));

        if ($selectedRawValues === []) {
            return null;
        }

        $selectedOptions = collect(ChoiceOptionNormalizer::normalizeMany($field['opsi_field'] ?? []))
            ->filter(function (array $option) use ($selectedRawValues) {
                foreach ($selectedRawValues as $value) {
                    if ($this->matchesChoiceOption($option, $value)) {
                        return true;
                    }
                }

                return false;
            })
            ->values();

        if ($selectedOptions->isEmpty()) {
            return null;
        }

        $selectedValues = $selectedOptions
            ->pluck('value')
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->values()
            ->all();

        if ($selectedValues === []) {
            return null;
        }

        return [
            'source' => $source,
            'source_label' => $this->label($source),
            'value' => $selectedValues,
            'answer' => [
                'text' => implode(', ', $selectedValues),
                'payload' => [
                    'type' => 'checkbox',
                    'values' => $selectedValues,
                    'selected_options' => $selectedOptions->map(fn (array $option) => array_filter([
                        'label' => $option['label'] ?? null,
                        'value' => $option['value'] ?? null,
                        'score' => $option['score'] ?? null,
                    ], static fn ($item) => $item !== null && $item !== ''))->all(),
                ],
            ],
        ];
    }

    private function resolveRawValue(string $source, Guru $guru): mixed
    {
        return match ($source) {
            'nip_nuptk' => $this->firstFilled([
                data_get($guru, 'nip'),
                data_get($guru, 'nuptk'),
            ]),
            'jabatan' => $this->firstFilled([
                data_get($guru, 'jabatan'),
                data_get($guru, 'jenis_jabatan'),
                data_get($guru, 'eksternal_jabatan'),
                data_get($guru, 'status_kepegawaian'),
            ]),
            'golongan' => $this->firstFilled([
                data_get($guru, 'golongan'),
                data_get($guru, 'pangkat_golongan'),
            ]),
            default => data_get($guru, $source),
        };
    }

    private function firstFilled(array $values): ?string
    {
        foreach ($values as $value) {
            $text = trim((string) ($value ?? ''));

            if ($text !== '') {
                return $text;
            }
        }

        return null;
    }

    private function matchesChoiceOption(array $option, string $rawValue): bool
    {
        $normalizedRawValue = $this->normalizeKeywordSource($rawValue);

        return collect($option['aliases'] ?? [])
            ->contains(function ($alias) use ($normalizedRawValue) {
                return $this->normalizeKeywordSource((string) $alias) === $normalizedRawValue;
            });
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function inferenceMap(): array
    {
        return [
            'nama_lengkap' => ['nama_lengkap', 'nama lengkap', 'nama peserta'],
            'no_ktp' => ['nik', 'no ktp', 'nomor ktp', 'ktp'],
            'nip_nuptk' => ['nip_nuptk', 'nip nuptk'],
            'nip' => [' nip ', 'nomor induk pegawai', 'nip'],
            'nuptk' => ['nuptk'],
            'golongan' => ['golongan', 'pangkat'],
            'jabatan' => ['jabatan'],
            'status_kepegawaian' => ['status_kepegawaian', 'status kepegawaian'],
            'eksternal_jabatan' => ['ketenagaan', 'kelompok jabatan'],
            'jenis_jabatan' => ['jenis_jabatan', 'jenis jabatan'],
            'kategori_jabatan' => ['kategori_jabatan', 'kategori jabatan'],
            'tugas_jabatan' => ['tugas_jabatan', 'tugas jabatan'],
            'latar_jabatan' => ['latar_jabatan', 'latar jabatan'],
            'gender' => ['jenis_kelamin', 'jenis kelamin', 'gender', 'kelamin'],
            'tempat_lahir' => ['tempat_lahir', 'tempat lahir'],
            'tgl_lahir' => ['tanggal_lahir', 'tanggal lahir', 'tgl_lahir', 'tgl lahir', 'lahir'],
            'agama' => ['agama'],
            'pendidikan' => ['pendidikan', 'kualifikasi akademik'],
            'email' => ['email', 'surel'],
            'no_hp' => ['no_hp', 'nomor hp', 'no hp', 'telepon'],
            'no_wa' => ['no_wa', 'nomor wa', 'nomor whatsapp', 'whatsapp'],
            'satuan_pendidikan' => ['satuan_pendidikan', 'satuan pendidikan', 'sekolah', 'instansi'],
            'npsn_sekolah' => ['npsn'],
            'kabupaten' => ['kabupaten_kota', 'kabupaten kota', 'kabupaten', 'kota'],
            'alamat_satuan' => ['alamat_satuan', 'alamat satuan'],
            'alamat_rumah' => ['alamat_rumah', 'alamat rumah'],
            'npwp' => ['npwp'],
            'no_rek' => ['no_rek', 'nomor rekening', 'rekening'],
            'jenis_bank' => ['jenis_bank', 'jenis bank', 'bank'],
        ];
    }

    private function normalizeKeywordSource(string $value): string
    {
        $normalized = Str::of($value)
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->trim()
            ->value();

        return preg_replace('/\s+/', ' ', $normalized) ?: '';
    }
}
