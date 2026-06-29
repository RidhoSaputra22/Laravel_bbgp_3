<?php

namespace Tests\Feature;

use App\Models\AssessmentAssignment;
use App\Models\AssessmentAssignmentSession;
use App\Models\AssessmentAssignmentTarget;
use App\Models\AssessmentAttempt;
use App\Models\Guru;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class AssessmentShowViewTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_assessment_show_view_renders_when_answer_validation_errors_exist(): void
    {
        Carbon::setTestNow('2026-06-28 09:00:00');

        $guru = new Guru([
            'nama_lengkap' => 'Guru Assessment',
            'satuan_pendidikan' => 'SMK Contoh',
        ]);

        $assignment = new AssessmentAssignment([
            'tanggal_mulai' => '2026-06-28',
            'tanggal_selesai' => '2026-06-29',
        ]);

        $session = new AssessmentAssignmentSession([
            'label_sesi' => 'Sesi 1',
            'waktu_mulai' => Carbon::parse('2026-06-28 08:00:00'),
            'waktu_selesai' => Carbon::parse('2026-06-28 11:00:00'),
        ]);

        $target = new AssessmentAssignmentTarget([
            'started_at' => Carbon::parse('2026-06-28 08:15:00'),
        ]);
        $target->id = 6;
        $target->setRelation('assignment', $assignment);
        $target->setRelation('session', $session);

        $attempt = new AssessmentAttempt([
            'started_at' => Carbon::parse('2026-06-28 08:15:00'),
            'structure_snapshot' => [
                'meta' => [
                    'total_questions' => 2,
                    'required_questions' => 2,
                ],
                'assessments' => [
                    [
                        'id' => 101,
                        'kode_assessment' => 'ASM-1',
                        'judul' => 'Assessment Pertama',
                        'deskripsi' => 'Deskripsi pertama',
                        'petunjuk' => null,
                        'forms' => [
                            [
                                'id' => 201,
                                'judul_form' => 'Form Pertama',
                                'deskripsi' => null,
                                'fields' => [
                                    [
                                        'id' => 301,
                                        'assessment_id' => 101,
                                        'assessment_form_id' => 201,
                                        'label' => 'Nama Lengkap',
                                        'deskripsi' => null,
                                        'placeholder' => 'Isi nama',
                                        'tipe_field' => 'text',
                                        'opsi_field' => [],
                                        'is_required' => true,
                                    ],
                                ],
                            ],
                        ],
                    ],
                    [
                        'id' => 102,
                        'kode_assessment' => 'ASM-2',
                        'judul' => 'Assessment Kedua',
                        'deskripsi' => 'Deskripsi kedua',
                        'petunjuk' => null,
                        'forms' => [
                            [
                                'id' => 202,
                                'judul_form' => 'Form Kedua',
                                'deskripsi' => null,
                                'fields' => [
                                    [
                                        'id' => 302,
                                        'assessment_id' => 102,
                                        'assessment_form_id' => 202,
                                        'label' => 'Alamat Email',
                                        'deskripsi' => null,
                                        'placeholder' => 'Isi email',
                                        'tipe_field' => 'email',
                                        'opsi_field' => [],
                                        'is_required' => true,
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $response = $this
            ->withSession([
                '_old_input' => [
                    'active_assessment_index' => '1',
                    'answers' => [
                        '302' => '',
                    ],
                ],
            ])
            ->withViewErrors([
                'answers.302' => 'Jawaban wajib diisi.',
            ])
            ->view('assessment.show.show', [
                'menu' => 'assessment-portal',
                'guru' => $guru,
                'target' => $target,
                'attempt' => $attempt,
                'meta' => [
                    'session_label' => 'Sesi 1',
                    'session_schedule_text' => '28 Jun 2026, 08:00 - 11:00 WITA',
                    'label' => 'Sedang Dikerjakan',
                    'date_text' => '28 Jun 2026 - 29 Jun 2026',
                ],
            ]);

        $response->assertSee('Jawaban wajib diisi.');
        $response->assertSee('initialIndex: 1', false);
        $response->assertSee('data-assessment-panel="1"', false);
        $response->assertSee('data-field-type="email"', false);
        $response->assertSee('openFinishModal()', false);
        $response->assertSee('validateAllAssessments()', false);
    }

    public function test_radio_group_displays_sequential_labels_while_preserving_randomized_option_values(): void
    {
        $html = Blade::render(
            <<<'BLADE'
            <x-assessment::form.radio-group
                name="answers[301]"
                :options="$options"
                :selected="[]"
                id-prefix="field-301"
            />
            BLADE,
            [
                'options' => [
                    ['label' => 'Mengurai aturan diskusi kepada Bima.', 'value' => 'B'],
                    ['label' => 'Melaksanakan strategi pengelolaan diskusi.', 'value' => 'C'],
                    ['label' => 'Mengenali faktor yang memengaruhi perilaku Bima.', 'value' => 'A'],
                    ['label' => 'Mengembangkan pendekatan pembinaan perilaku kolaboratif.', 'value' => 'E'],
                    ['label' => 'Menelaah faktor penyebab dominasi Bima.', 'value' => 'D'],
                ],
            ]
        );

        $this->assertMatchesRegularExpression('/value="B"[\s\S]*?<h1 class="font-semibold">\s*A\.\s*<\/h1>/', $html);
        $this->assertMatchesRegularExpression('/value="C"[\s\S]*?<h1 class="font-semibold">\s*B\.\s*<\/h1>/', $html);
        $this->assertMatchesRegularExpression('/value="A"[\s\S]*?<h1 class="font-semibold">\s*C\.\s*<\/h1>/', $html);
        $this->assertMatchesRegularExpression('/value="E"[\s\S]*?<h1 class="font-semibold">\s*D\.\s*<\/h1>/', $html);
        $this->assertMatchesRegularExpression('/value="D"[\s\S]*?<h1 class="font-semibold">\s*E\.\s*<\/h1>/', $html);
    }
}
