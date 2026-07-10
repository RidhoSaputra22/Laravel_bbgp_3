<?php

namespace App\Support\Assessment;

final class AssessmentSchoolTargetKey
{
    private const DELIMITER = '|||';

    /**
     * @return array{kabupaten: string|null, satuan_pendidikan: string}
     */
    public static function decode(mixed $value): array
    {
        $normalized = trim((string) ($value ?? ''));

        if ($normalized === '') {
            return [
                'kabupaten' => null,
                'satuan_pendidikan' => '',
            ];
        }

        if (! str_contains($normalized, self::DELIMITER)) {
            return [
                'kabupaten' => null,
                'satuan_pendidikan' => $normalized,
            ];
        }

        [$kabupaten, $satuanPendidikan] = explode(self::DELIMITER, $normalized, 2);

        return [
            'kabupaten' => trim($kabupaten) !== '' ? trim($kabupaten) : null,
            'satuan_pendidikan' => trim($satuanPendidikan),
        ];
    }

    public static function encode(mixed $kabupaten, mixed $satuanPendidikan): string
    {
        $normalizedSchool = trim((string) ($satuanPendidikan ?? ''));

        if ($normalizedSchool === '') {
            return '';
        }

        $normalizedKabupaten = trim((string) ($kabupaten ?? ''));

        return $normalizedKabupaten.self::DELIMITER.$normalizedSchool;
    }

    public static function label(mixed $value): string
    {
        $decoded = self::decode($value);
        $school = $decoded['satuan_pendidikan'];
        $kabupaten = $decoded['kabupaten'];

        if ($school === '') {
            return '-';
        }

        if ($kabupaten === null || $kabupaten === '') {
            return $school;
        }

        return $school.' ('.$kabupaten.')';
    }
}
