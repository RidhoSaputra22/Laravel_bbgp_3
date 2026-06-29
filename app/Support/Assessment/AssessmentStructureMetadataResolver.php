<?php

namespace App\Support\Assessment;

use App\Enum\AssessmentInstrumentType;
use App\Enum\KompetensiGuru;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AssessmentStructureMetadataResolver
{
    public function decorateAssessment(array $assessment): array
    {
        $instrument = $this->resolveAssessmentInstrument($assessment);

        return array_merge($assessment, [
            'instrument_type' => $instrument?->value,
            'instrument_label' => $instrument?->label(),
        ]);
    }

    public function decorateForm(array $form, ?array $assessment = null): array
    {
        $kompetensi = $this->resolveFormCompetency($form, $assessment);
        $indicator = $this->resolveIndicator($form);

        return array_merge($form, [
            'kompetensi' => $kompetensi?->value,
            'kompetensi_label' => $kompetensi?->label(),
            'indikator_kode' => $indicator['kode'],
            'indikator_label' => $indicator['label'],
            'is_scoreable' => $this->isScoreableForm($form, $assessment),
        ]);
    }

    public function resolveAssessmentInstrument(array $assessment): ?AssessmentInstrumentType
    {
        $explicitInstrument = AssessmentInstrumentType::tryFromMixed($assessment['instrument_type'] ?? null);

        if ($explicitInstrument) {
            return $explicitInstrument;
        }

        $haystack = Str::lower($this->joinTexts([
            $assessment['kode_assessment'] ?? null,
            $assessment['judul'] ?? null,
            $assessment['deskripsi'] ?? null,
            $assessment['petunjuk'] ?? null,
        ]));

        return match (true) {
            Str::contains($haystack, ['pilihan ganda', 'pg kompleks', 'soal situasional']) => AssessmentInstrumentType::PILIHAN_GANDA_KOMPLEKS,
            Str::contains($haystack, ['studi kasus', 'kasus']) => AssessmentInstrumentType::STUDI_KASUS,
            Str::contains($haystack, ['monitoring', 'observasi', 'eviden lapangan', 'eviden']) => AssessmentInstrumentType::MONITORING_OBSERVASI_EVIDEN,
            Str::contains($haystack, ['portofolio', 'portfolio']) => AssessmentInstrumentType::PORTOFOLIO,
            default => null,
        };
    }

    public function resolveFormCompetency(array $form, ?array $assessment = null): ?KompetensiGuru
    {
        $explicitCompetency = KompetensiGuru::tryFromMixed($form['kompetensi'] ?? null);

        if ($explicitCompetency) {
            return $explicitCompetency;
        }

        $haystack = Str::lower($this->joinTexts([
            $form['kode_form'] ?? null,
            $form['judul_form'] ?? null,
            $form['deskripsi'] ?? null,
            $this->collectFieldTexts($form),
            $assessment['judul'] ?? null,
        ]));

        return match (true) {
            Str::contains($haystack, ['pedagogik', 'form-ped', ' kompetensi pedagogik']) => KompetensiGuru::PEDAGOGIK,
            Str::contains($haystack, ['kepribadian', 'form-kep', ' kompetensi kepribadian']) => KompetensiGuru::KEPRIBADIAN,
            Str::contains($haystack, ['sosial', 'kompetensi sosial', 'form-sos']) => KompetensiGuru::SOSIAL,
            Str::contains($haystack, ['profesional', 'form-pro', 'kompetensi profesional']) => KompetensiGuru::PROFESIONAL,
            default => null,
        };
    }

    public function resolveIndicator(array $form): array
    {
        $explicitCode = trim((string) ($form['indikator_kode'] ?? ''));
        $explicitLabel = trim((string) ($form['indikator_label'] ?? ''));

        if ($explicitCode !== '' || $explicitLabel !== '') {
            return [
                'kode' => $explicitCode !== '' ? $explicitCode : ($form['kode_form'] ?? null),
                'label' => $explicitLabel !== '' ? $explicitLabel : trim((string) ($form['judul_form'] ?? '')),
            ];
        }

        $description = trim((string) ($form['deskripsi'] ?? ''));

        if (
            $description !== ''
            && preg_match('/indikator\s+([0-9]+(?:\.[0-9]+)+)\s*[—:-]?\s*(.+)?/iu', $description, $matches) === 1
        ) {
            return [
                'kode' => trim((string) ($matches[1] ?? '')),
                'label' => trim((string) ($matches[2] ?? '')) ?: trim((string) ($form['judul_form'] ?? '')),
            ];
        }

        $title = trim((string) ($form['judul_form'] ?? ''));

        if ($title !== '' && preg_match('/^([0-9]+(?:\.[0-9]+)+)\s+(.+)$/u', $title, $matches) === 1) {
            $segments = explode('.', trim((string) $matches[1]));
            $indicatorCode = count($segments) >= 2
                ? implode('.', array_slice($segments, 0, 2))
                : trim((string) $matches[1]);

            return [
                'kode' => $indicatorCode,
                'label' => trim((string) ($matches[2] ?? '')),
            ];
        }

        $fallbackCode = trim((string) ($form['kode_form'] ?? ''));
        $fallbackLabel = trim((string) ($form['judul_form'] ?? ''));

        return [
            'kode' => $fallbackCode !== '' ? $fallbackCode : null,
            'label' => $fallbackLabel !== '' ? $fallbackLabel : null,
        ];
    }

    public function isScoreableForm(array $form, ?array $assessment = null): bool
    {
        if (array_key_exists('is_scoreable', $form)) {
            return (bool) $form['is_scoreable'];
        }

        return $this->resolveFormCompetency($form, $assessment) !== null;
    }

    private function collectFieldTexts(array $form): string
    {
        return collect($form['fields'] ?? [])
            ->flatMap(function ($field) {
                return [
                    $field['label'] ?? null,
                    $field['deskripsi'] ?? null,
                    $field['bantuan'] ?? null,
                ];
            })
            ->filter(fn ($value) => filled($value))
            ->implode(' ');
    }

    private function joinTexts(array $values): string
    {
        return collect(Arr::flatten($values))
            ->filter(fn ($value) => filled($value))
            ->map(fn ($value) => trim((string) $value))
            ->implode(' ');
    }
}
