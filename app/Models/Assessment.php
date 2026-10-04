<?php

namespace App\Models;

use App\Enum\AssessmentKetenagaanType;
use App\Models\Pivots\AssessmentAssignmentAssessment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    use HasFactory;

    public const CATEGORY_STANDARD = 'assessment';
    public const CATEGORY_EVALUASI_PELAKSANAAN = 'evaluasi_pelaksanaan';
    public const TARGET_JABATAN_ALL = '__all__';

    protected $fillable = [
        'kode_assessment',
        'judul',
        'slug',
        'deskripsi',
        'petunjuk',
        'instrument_type',
        'kategori',
        'target_ketenagaan',
        'target_jabatan',
        'scoring_config',
        'status',
        'is_active',
    ];

    protected $casts = [
        'scoring_config' => 'array',
        'target_jabatan' => 'array',
        'is_active' => 'boolean',
    ];

    public function forms()
    {
        return $this->hasMany(AssessmentForm::class)->orderBy('urutan');
    }

    public function assignments()
    {
        return $this->belongsToMany(AssessmentAssignment::class, 'assessment_assignment_assessments')
            ->using(AssessmentAssignmentAssessment::class)
            ->withPivot('urutan', 'stage_config')
            ->withTimestamps()
            ->orderByDesc('assessment_assignments.id');
    }

    public function validatorAssignments()
    {
        return $this->hasMany(ValidatorAssignment::class);
    }

    public function getTargetKetenagaanLabelAttribute(): ?string
    {
        return AssessmentKetenagaanType::tryFromMixed($this->target_ketenagaan)?->label();
    }

    public function getTargetKetenagaanBadgeClassAttribute(): string
    {
        return AssessmentKetenagaanType::tryFromMixed($this->target_ketenagaan)?->badgeClass() ?? 'secondary';
    }

    public static function normalizeTargetJabatan(mixed $targetJabatan): array
    {
        $values = collect(is_array($targetJabatan) ? $targetJabatan : [$targetJabatan])
            ->filter(fn ($jabatan) => filled($jabatan))
            ->map(fn ($jabatan) => trim((string) $jabatan))
            ->filter(fn (string $jabatan) => $jabatan !== '')
            ->unique()
            ->values();

        return $values->contains(self::TARGET_JABATAN_ALL)
            ? [self::TARGET_JABATAN_ALL]
            : $values->all();
    }

    public function targetJabatanSelections(): array
    {
        $selections = self::normalizeTargetJabatan($this->target_jabatan);

        return $selections !== [] ? $selections : [self::TARGET_JABATAN_ALL];
    }

    public function appliesToJabatanSelections(array $jabatan): bool
    {
        $targets = $this->targetJabatanSelections();
        $selected = self::normalizeTargetJabatan($jabatan);

        return in_array(self::TARGET_JABATAN_ALL, $targets, true)
            || in_array(self::TARGET_JABATAN_ALL, $selected, true)
            || $selected === []
            || array_intersect($targets, $selected) !== [];
    }

    public function coversJabatanSelections(array $jabatan): bool
    {
        $targets = $this->targetJabatanSelections();
        $selected = self::normalizeTargetJabatan($jabatan);

        return in_array(self::TARGET_JABATAN_ALL, $targets, true)
            || $selected === []
            || array_diff($selected, $targets) === [];
    }

    public function matchesTargetJabatanSelections(array $jabatan): bool
    {
        $targets = self::normalizeTargetJabatan($this->target_jabatan) ?: [self::TARGET_JABATAN_ALL];
        $selected = self::normalizeTargetJabatan($jabatan) ?: [self::TARGET_JABATAN_ALL];

        sort($targets);
        sort($selected);

        return $targets === $selected;
    }

    public function getTargetJabatanLabelsAttribute(): array
    {
        if (in_array(self::TARGET_JABATAN_ALL, $this->targetJabatanSelections(), true)) {
            return ['Semua Jabatan'];
        }

        return $this->targetJabatanSelections();
    }
}
