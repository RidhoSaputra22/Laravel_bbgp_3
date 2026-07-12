<?php

namespace App\Support\Assessment;

use App\Enum\AssessmentKetenagaanType;
use App\Models\GolonganP3k;
use App\Models\Jabatan;
use App\Models\JabatanKependidikan;
use App\Models\JabatanPendidik;
use App\Models\JabatanPenugasanGolongan;
use App\Models\JabatanStakeHolder;
use App\Models\JenisJabatan;
use App\Models\JenisTugas;
use App\Models\Kabupaten;
use App\Models\Kepegawaian;
use App\Models\LatarJabatan;
use App\Models\Pendidikan;
use App\Models\SatuanPendidikan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AssessmentFieldLookupResolver
{
    /**
     * @var array<string, array<int, array<string, string>>>
     */
    private array $resolvedOptionsCache = [];

    /**
     * @var array<string, array<string, mixed>>
     */
    private array $previewCache = [];

    /**
     * @return array<string, string>
     */
    public function options(): array
    {
        return [
            'master_golongan' => 'Master Golongan',
            'master_golongan_pns' => 'Master Golongan PNS',
            'master_golongan_pppk' => 'Master Golongan PPPK',
            'master_status_kepegawaian' => 'Master Status Kepegawaian',
            'master_pendidikan' => 'Master Pendidikan',
            'master_kabupaten' => 'Master Kabupaten / Kota',
            'master_satuan_pendidikan' => 'Master Satuan Pendidikan',
            'master_jabatan_umum' => 'Master Jabatan Umum',
            'master_jabatan_pendidik' => 'Master Jabatan Tenaga Pendidik',
            'master_jabatan_kependidikan' => 'Master Jabatan Tenaga Kependidikan',
            'master_jabatan_stakeholder' => 'Master Jabatan Stakeholder',
            'master_jenis_jabatan' => 'Master Jenis Jabatan',
            'master_tugas_jabatan' => 'Master Tugas Jabatan',
            'master_latar_jabatan' => 'Master Latar Jabatan',
        ];
    }

    public function supportsFieldType(?string $fieldType): bool
    {
        return (string) $fieldType === 'select';
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

    /**
     * @return array<string, array{label: string, total: int, preview: array<int, array{label: string, value: string}>}>
     */
    public function previewCatalog(int $limit = 10): array
    {
        return collect(array_keys($this->options()))
            ->mapWithKeys(fn (string $source) => [$source => $this->resolvePreview($source, $limit)])
            ->all();
    }

    /**
     * @return array{label: string, total: int, preview: array<int, array{label: string, value: string}>}
     */
    public function resolvePreview(?string $source, int $limit = 10): array
    {
        $normalizedSource = $this->normalizeSource($source, 'select');

        if (! $normalizedSource) {
            return [
                'label' => '',
                'total' => 0,
                'preview' => [],
            ];
        }

        $cacheKey = $normalizedSource.':'.$limit;

        if (array_key_exists($cacheKey, $this->previewCache)) {
            return $this->previewCache[$cacheKey];
        }

        $options = $this->resolveOptions($normalizedSource);
        $preview = array_slice($options, 0, max($limit, 0));

        return $this->previewCache[$cacheKey] = [
            'label' => $this->label($normalizedSource) ?? $normalizedSource,
            'total' => count($options),
            'preview' => $preview,
        ];
    }

    public function inferSourceFromField(
        ?string $label,
        ?string $fieldName = null,
        AssessmentKetenagaanType|string|null $targetKetenagaan = null
    ): ?string {
        $haystack = $this->normalizeKeywordSource(
            collect([$fieldName, $label])->filter()->implode(' ')
        );
        $target = AssessmentKetenagaanType::tryFromMixed($targetKetenagaan);

        if ($haystack === '') {
            return null;
        }

        if (
            $this->containsAny($haystack, ['golongan pppk', 'pangkat pppk'])
            || $haystack === 'pppk'
        ) {
            return 'master_golongan_pppk';
        }

        if (
            $this->containsAny($haystack, ['golongan pns', 'pangkat pns'])
            || $haystack === 'pns'
        ) {
            return 'master_golongan_pns';
        }

        if ($this->containsAny($haystack, ['jenis jabatan'])) {
            return 'master_jenis_jabatan';
        }

        if ($this->containsAny($haystack, ['tugas jabatan'])) {
            return 'master_tugas_jabatan';
        }

        if ($this->containsAny($haystack, ['latar jabatan'])) {
            return 'master_latar_jabatan';
        }

        if (
            Str::contains($haystack, 'jabatan')
            && ! $this->containsAny($haystack, ['jenis jabatan', 'tugas jabatan', 'latar jabatan'])
        ) {
            return match ($target) {
                AssessmentKetenagaanType::TENAGA_PENDIDIK => 'master_jabatan_pendidik',
                AssessmentKetenagaanType::TENAGA_KEPENDIDIKAN => 'master_jabatan_kependidikan',
                AssessmentKetenagaanType::STAKEHOLDER => 'master_jabatan_stakeholder',
                default => 'master_jabatan_umum',
            };
        }

        foreach ($this->inferenceMap() as $source => $needles) {
            if ($this->containsAny($haystack, $needles)) {
                return $source;
            }
        }

        return null;
    }

    /**
     * @return array<int, array{label: string, value: string}>
     */
    public function resolveOptions(?string $source): array
    {
        $normalizedSource = $this->normalizeSource($source, 'select');

        if (! $normalizedSource) {
            return [];
        }

        if (array_key_exists($normalizedSource, $this->resolvedOptionsCache)) {
            return $this->resolvedOptionsCache[$normalizedSource];
        }

        return $this->resolvedOptionsCache[$normalizedSource] = $this->resolveOptionLabels($normalizedSource)
            ->map(fn (string $label) => [
                'label' => $label,
                'value' => $label,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function inferenceMap(): array
    {
        return [
            'master_golongan' => ['golongan', 'pangkat'],
            'master_status_kepegawaian' => ['status kepegawaian'],
            'master_pendidikan' => ['pendidikan', 'kualifikasi akademik'],
            'master_kabupaten' => ['kabupaten kota', 'kabupaten', 'kota'],
            'master_satuan_pendidikan' => ['satuan pendidikan', 'npsn sekolah', 'sekolah', 'instansi'],
        ];
    }

    /**
     * @return Collection<int, string>
     */
    private function resolveOptionLabels(string $source): Collection
    {
        return match ($source) {
            'master_golongan' => $this->mergeModelNames([
                JabatanPenugasanGolongan::class,
                GolonganP3k::class,
            ]),
            'master_golongan_pns' => $this->resolveModelNames(JabatanPenugasanGolongan::class),
            'master_golongan_pppk' => $this->resolveModelNames(GolonganP3k::class),
            'master_status_kepegawaian' => $this->resolveModelNames(Kepegawaian::class),
            'master_pendidikan' => $this->resolveModelNames(Pendidikan::class),
            'master_kabupaten' => $this->resolveModelNames(Kabupaten::class),
            'master_satuan_pendidikan' => $this->resolveModelNames(SatuanPendidikan::class),
            'master_jabatan_umum' => $this->resolveModelNames(Jabatan::class),
            'master_jabatan_pendidik' => $this->resolveModelNames(JabatanPendidik::class),
            'master_jabatan_kependidikan' => $this->resolveModelNames(JabatanKependidikan::class),
            'master_jabatan_stakeholder' => $this->resolveModelNames(JabatanStakeHolder::class),
            'master_jenis_jabatan' => $this->resolveModelNames(JenisJabatan::class),
            'master_tugas_jabatan' => $this->resolveModelNames(JenisTugas::class),
            'master_latar_jabatan' => $this->resolveModelNames(LatarJabatan::class),
            default => collect(),
        };
    }

    /**
     * @param  array<int, class-string<\Illuminate\Database\Eloquent\Model>>  $modelClasses
     * @return Collection<int, string>
     */
    private function mergeModelNames(array $modelClasses): Collection
    {
        return collect($modelClasses)
            ->flatMap(fn (string $modelClass) => $this->resolveModelNames($modelClass)->all())
            ->map(fn ($name) => trim((string) $name))
            ->filter(fn (string $name) => $name !== '')
            ->unique(fn (string $name) => Str::lower($name))
            ->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->values();
    }

    /**
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $modelClass
     * @return Collection<int, string>
     */
    private function resolveModelNames(string $modelClass): Collection
    {
        $table = (new $modelClass)->getTable();

        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'name')) {
            return collect();
        }

        return $modelClass::query()
            ->select('name')
            ->orderBy('name')
            ->pluck('name')
            ->map(fn ($name) => trim((string) $name))
            ->filter(fn (string $name) => $name !== '')
            ->unique(fn (string $name) => Str::lower($name))
            ->values();
    }

    private function containsAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (Str::contains($haystack, $this->normalizeKeywordSource($needle))) {
                return true;
            }
        }

        return false;
    }

    private function normalizeKeywordSource(?string $value): string
    {
        return Str::of((string) $value)
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->trim()
            ->replaceMatches('/\s+/', ' ')
            ->value();
    }
}
