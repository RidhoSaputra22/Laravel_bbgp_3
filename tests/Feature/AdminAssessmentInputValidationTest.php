<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminAssessmentInputValidationTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::query()->create([
            'name' => 'Assessment Admin Test',
            'username' => 'assessment-admin-'.uniqid(),
            'password' => 'password',
            'role' => 'admin',
        ]);

        $this->actingAs($this->admin);
    }

    private function adminSession(): array
    {
        return [
            'cek' => true,
            'role' => 'admin',
            'user_id' => $this->admin->id,
        ];
    }

    public function test_assessment_store_rejects_missing_required_structure(): void
    {
        $this->withSession($this->adminSession())
            ->post(route('assessment.store'), [])
            ->assertSessionHasErrors(['judul', 'target_ketenagaan', 'status', 'forms']);
    }

    public function test_assessment_store_rejects_invalid_form_and_field_values(): void
    {
        $this->withSession($this->adminSession())
            ->post(route('assessment.store'), [
                'judul' => 'Assessment Invalid Input',
                'target_ketenagaan' => 'target-invalid',
                'status' => 'status-invalid',
                'forms' => [[
                    'judul_form' => 'Form Invalid',
                    'is_scoreable' => true,
                    'fields' => [[
                        'label' => 'Pertanyaan',
                        'tipe_field' => 'tipe-invalid',
                    ]],
                ]],
            ])
            ->assertSessionHasErrors([
                'target_ketenagaan',
                'status',
                'forms.0.kompetensi',
                'forms.0.fields.0.tipe_field',
            ]);
    }

    public function test_assessment_store_persists_a_minimal_valid_form(): void
    {
        $title = 'Assessment Valid '.uniqid();

        $this->withSession($this->adminSession())
            ->post(route('assessment.store'), [
                'judul' => $title,
                'deskripsi' => 'Assessment dari feature test.',
                'target_ketenagaan' => 'tenaga_pendidik',
                'instrument_type' => 'portofolio',
                'status' => 'draft',
                'is_active' => '1',
                'forms' => [[
                    'judul_form' => 'Identitas Responden',
                    'kode_form' => 'FORM-FEATURE-'.uniqid(),
                    'is_scoreable' => false,
                    'is_active' => true,
                    'fields' => [[
                        'label' => 'Nama Lengkap',
                        'tipe_field' => 'text',
                        'is_required' => true,
                        'is_active' => true,
                    ]],
                ]],
            ])
            ->assertRedirect(route('assessment.index'));

        $assessment = Assessment::query()->where('judul', $title)->firstOrFail();
        $this->assertSame('draft', $assessment->status);
        $this->assertCount(1, $assessment->forms()->first()->fields);
    }

    public function test_assessment_choice_options_use_newline_as_the_only_separator(): void
    {
        $title = 'Assessment Newline Options '.uniqid();

        $this->withSession($this->adminSession())
            ->post(route('assessment.store'), [
                'judul' => $title,
                'target_ketenagaan' => 'tenaga_pendidik',
                'status' => 'draft',
                'forms' => [[
                    'judul_form' => 'Pilihan',
                    'is_scoreable' => false,
                    'fields' => [[
                        'label' => 'Pilihan jawaban',
                        'tipe_field' => 'checkbox',
                        'opsi_field_text' => "Ya, mungkin\nTidak",
                    ]],
                ]],
            ])
            ->assertRedirect(route('assessment.index'));

        $field = Assessment::query()
            ->where('judul', $title)
            ->firstOrFail()
            ->forms()
            ->firstOrFail()
            ->fields()
            ->firstOrFail();

        $this->assertSame([
            ['label' => 'Ya, mungkin', 'value' => 'Ya, mungkin'],
            ['label' => 'Tidak', 'value' => 'Tidak'],
        ], $field->opsi_field);
    }

    public function test_validator_form_requires_sections_and_fields(): void
    {
        $this->withSession($this->adminSession())
            ->post(route('assessment.validator.form.store'), [])
            ->assertSessionHasErrors(['title', 'status', 'sections']);

        $this->withSession($this->adminSession())
            ->post(route('assessment.validator.form.store'), [
                'title' => 'Validator Invalid Choices',
                'status' => 'draft',
                'sections' => [[
                    'title' => 'Bagian 1',
                    'fields' => [[
                        'label' => 'Pilihan',
                        'field_type' => 'radio',
                        'options_text' => 'Satu',
                    ]],
                ]],
            ])
            ->assertSessionHasErrors(['sections.0.fields.0.options_text']);
    }

    public function test_validator_form_persists_a_minimal_valid_structure(): void
    {
        $title = 'Validator Valid '.uniqid();

        $this->withSession($this->adminSession())
            ->post(route('assessment.validator.form.store'), [
                'title' => $title,
                'description' => 'Form validator feature test.',
                'status' => 'draft',
                'is_active' => true,
                'sections' => [[
                    'title' => 'Bagian Identitas',
                    'fields' => [[
                        'label' => 'Catatan validator',
                        'field_type' => 'textarea',
                        'is_required' => true,
                        'is_active' => true,
                    ]],
                ]],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('validator_forms', ['title' => $title, 'status' => 'draft']);
    }
}
