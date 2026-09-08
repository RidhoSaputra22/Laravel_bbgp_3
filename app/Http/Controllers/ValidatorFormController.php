<?php

namespace App\Http\Controllers;

use App\Models\ValidatorForm;
use App\Models\ValidatorFormField;
use App\Support\Assessment\ValidatorAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ValidatorFormController extends Controller
{
    public function index()
    {
        ValidatorAccess::authorizeAdmin();

        return view('pages.admin.assessment.validator.form.index', [
            'menu' => 'assessment-validator',
            'forms' => ValidatorForm::withCount(['sections', 'assignments'])
                ->with('sections.fields')
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    public function create()
    {
        ValidatorAccess::authorizeAdmin();

        return view('pages.admin.assessment.validator.form.editor', [
            'menu' => 'assessment-validator',
            'form' => new ValidatorForm(['status' => 'draft', 'is_active' => true]),
            'fieldTypes' => ValidatorFormField::TYPES,
            'builderData' => [],
        ]);
    }

    public function store(Request $request)
    {
        ValidatorAccess::authorizeAdmin();
        $validated = $this->validatePayload($request);

        $form = DB::transaction(function () use ($validated) {
            $form = ValidatorForm::create([
                'code' => $this->resolveCode($validated['code'] ?? null, $validated['title']),
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'instructions' => $validated['instructions'] ?? null,
                'status' => $validated['status'],
                'is_active' => (bool) ($validated['is_active'] ?? false),
                'created_by' => session('user_id') ?: null,
            ]);

            $this->syncStructure($form, $validated['sections']);

            return $form;
        });

        return redirect()->route('assessment.validator.form.show', $form)->with('message', 'store');
    }

    public function show(ValidatorForm $form)
    {
        ValidatorAccess::authorizeAdmin();

        return view('pages.admin.assessment.validator.form.show', [
            'menu' => 'assessment-validator',
            'form' => $form->load('sections.fields')->loadCount('assignments'),
        ]);
    }

    public function edit(ValidatorForm $form)
    {
        ValidatorAccess::authorizeAdmin();
        $this->ensureEditable($form);
        $form->load('sections.fields');

        $builderData = $form->sections->map(fn ($section) => [
            'title' => $section->title,
            'description' => $section->description,
            'fields' => $section->fields->map(fn ($field) => [
                'label' => $field->label,
                'description' => $field->description,
                'field_type' => $field->field_type,
                'options_text' => implode("\n", $field->options ?? []),
                'is_required' => $field->is_required,
                'is_scored' => $field->is_scored,
                'max_score' => $field->max_score,
                'is_active' => $field->is_active,
            ])->values()->all(),
        ])->values()->all();

        return view('pages.admin.assessment.validator.form.editor', [
            'menu' => 'assessment-validator',
            'form' => $form,
            'fieldTypes' => ValidatorFormField::TYPES,
            'builderData' => $builderData,
        ]);
    }

    public function update(Request $request, ValidatorForm $form)
    {
        ValidatorAccess::authorizeAdmin();
        $this->ensureEditable($form);
        $validated = $this->validatePayload($request, $form->id);

        DB::transaction(function () use ($form, $validated) {
            $form->update([
                'code' => $this->resolveCode($validated['code'] ?? null, $validated['title'], $form->id),
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'instructions' => $validated['instructions'] ?? null,
                'status' => $validated['status'],
                'is_active' => (bool) ($validated['is_active'] ?? false),
            ]);
            $form->sections()->delete();
            $this->syncStructure($form, $validated['sections']);
        });

        return redirect()->route('assessment.validator.form.show', $form)->with('message', 'update');
    }

    public function destroy(ValidatorForm $form)
    {
        ValidatorAccess::authorizeAdmin();
        $this->ensureEditable($form);
        $form->delete();

        return redirect()->route('assessment.validator.form.index')->with('validator_success', 'Form validator berhasil dihapus.');
    }

    private function validatePayload(Request $request, ?int $formId = null): array
    {
        $validator = Validator::make($request->all(), [
            'code' => ['nullable', 'string', 'max:60', Rule::unique('validator_forms', 'code')->ignore($formId)],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'instructions' => ['nullable', 'string', 'max:10000'],
            'status' => ['required', Rule::in(['draft', 'published', 'inactive'])],
            'is_active' => ['nullable', 'boolean'],
            'sections' => ['required', 'array', 'min:1'],
            'sections.*.title' => ['required', 'string', 'max:255'],
            'sections.*.description' => ['nullable', 'string', 'max:3000'],
            'sections.*.fields' => ['required', 'array', 'min:1'],
            'sections.*.fields.*.label' => ['required', 'string', 'max:255'],
            'sections.*.fields.*.description' => ['nullable', 'string', 'max:3000'],
            'sections.*.fields.*.field_type' => ['required', Rule::in(array_keys(ValidatorFormField::TYPES))],
            'sections.*.fields.*.options_text' => ['nullable', 'string', 'max:10000'],
            'sections.*.fields.*.is_required' => ['nullable', 'boolean'],
            'sections.*.fields.*.is_scored' => ['nullable', 'boolean'],
            'sections.*.fields.*.max_score' => ['nullable', 'numeric', 'min:0.01', 'max:100000'],
            'sections.*.fields.*.is_active' => ['nullable', 'boolean'],
        ], [
            'sections.required' => 'Minimal satu bagian form validator wajib dibuat.',
            'sections.*.title.required' => 'Judul setiap bagian wajib diisi.',
            'sections.*.fields.required' => 'Setiap bagian wajib memiliki minimal satu butir validasi.',
            'sections.*.fields.*.label.required' => 'Pertanyaan atau indikator validasi wajib diisi.',
        ]);

        $validator->after(function ($validator) use ($request) {
            foreach ((array) $request->input('sections', []) as $sectionIndex => $section) {
                foreach ((array) ($section['fields'] ?? []) as $fieldIndex => $field) {
                    $type = $field['field_type'] ?? null;
                    $options = $this->normalizeOptions($field['options_text'] ?? null);

                    if (in_array($type, ['select', 'radio', 'checkbox'], true) && count($options) < 2) {
                        $validator->errors()->add(
                            "sections.$sectionIndex.fields.$fieldIndex.options_text",
                            'Tipe pilihan wajib memiliki minimal dua opsi (satu opsi per baris).'
                        );
                    }

                    if (! empty($field['is_scored']) && empty($field['max_score'])) {
                        $validator->errors()->add(
                            "sections.$sectionIndex.fields.$fieldIndex.max_score",
                            'Skor maksimum wajib diisi untuk butir yang dinilai.'
                        );
                    }

                    if (! empty($field['is_scored'])) {
                        $scoreableTypes = ['number', 'likert', 'select', 'radio'];
                        $hasNonNumericOption = in_array($type, ['likert', 'select', 'radio'], true)
                            && collect($options)->contains(fn ($option) => ! is_numeric($option));

                        if (! in_array($type, $scoreableTypes, true) || $hasNonNumericOption) {
                            $validator->errors()->add(
                                "sections.$sectionIndex.fields.$fieldIndex.is_scored",
                                'Butir berskor harus bertipe angka/Likert atau memiliki opsi bernilai angka.'
                            );
                        }
                    }
                }
            }
        });

        return $validator->validate();
    }

    private function syncStructure(ValidatorForm $form, array $sections): void
    {
        foreach (array_values($sections) as $sectionIndex => $sectionData) {
            $section = $form->sections()->create([
                'title' => $sectionData['title'],
                'description' => $sectionData['description'] ?? null,
                'sort_order' => $sectionIndex + 1,
            ]);

            foreach (array_values($sectionData['fields']) as $fieldIndex => $fieldData) {
                $type = $fieldData['field_type'];
                $options = $this->normalizeOptions($fieldData['options_text'] ?? null);

                if ($type === 'likert' && empty($options)) {
                    $options = ['1', '2', '3', '4', '5'];
                }

                $section->fields()->create([
                    'label' => $fieldData['label'],
                    'description' => $fieldData['description'] ?? null,
                    'field_type' => $type,
                    'options' => $options ?: null,
                    'is_required' => (bool) ($fieldData['is_required'] ?? false),
                    'is_scored' => (bool) ($fieldData['is_scored'] ?? false),
                    'max_score' => ! empty($fieldData['is_scored']) ? $fieldData['max_score'] : null,
                    'sort_order' => $fieldIndex + 1,
                    'is_active' => (bool) ($fieldData['is_active'] ?? false),
                ]);
            }
        }
    }

    private function normalizeOptions(?string $options): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $options))
            ->map(fn ($option) => trim($option))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function resolveCode(?string $requestedCode, string $title, ?int $ignoreId = null): string
    {
        $base = strtoupper(trim((string) $requestedCode));
        $base = $base !== '' ? Str::slug($base, '-') : 'VF-'.strtoupper(Str::slug($title, '-'));
        $base = Str::limit($base, 52, '');
        $code = $base;
        $suffix = 2;

        while (ValidatorForm::where('code', $code)->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))->exists()) {
            $code = $base.'-'.$suffix++;
        }

        return $code;
    }

    private function ensureEditable(ValidatorForm $form): void
    {
        if ($form->assignments()->exists()) {
            throw ValidationException::withMessages([
                'form' => 'Form yang sudah pernah ditugaskan dikunci untuk menjaga konsistensi audit. Buat form baru untuk revisi.',
            ]);
        }
    }
}
