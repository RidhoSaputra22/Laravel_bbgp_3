<?php

namespace Tests\Feature;

use App\Enum\AssessmentKetenagaanType;
use App\Http\Controllers\Assessment\PortalController;
use App\Models\AssessmentAssignment;
use App\Models\AssessmentAssignmentTarget;
use App\Models\AssessmentAttempt;
use App\Models\Guru;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use ReflectionClass;
use Tests\TestCase;

class AssessmentResultViewTest extends TestCase
{
    public function test_result_view_shows_download_button_for_stakeholder_assignment(): void
    {
        $viewData = $this->buildViewData(AssessmentKetenagaanType::STAKEHOLDER->value);

        $response = $this->view('assessment.result.result', array_merge($viewData, [
            'isStakeholderDownloadAvailable' => true,
            'stakeholderResultDownloadUrl' => route('assessment.portal.result.download', 42),
        ]));

        $response->assertSee('Download PDF Jawaban');
        $response->assertSee(route('assessment.portal.result.download', 42, absolute: false));
    }

    public function test_result_view_hides_download_button_for_non_stakeholder_assignment(): void
    {
        $viewData = $this->buildViewData(AssessmentKetenagaanType::TENAGA_PENDIDIK->value);

        $response = $this->view('assessment.result.result', array_merge($viewData, [
            'isStakeholderDownloadAvailable' => false,
            'stakeholderResultDownloadUrl' => null,
        ]));

        $response->assertDontSee('Download PDF Jawaban');
        $response->assertDontSee(route('assessment.portal.result.download', 42, absolute: false));
    }

    public function test_result_view_shows_certificate_link_cards_when_available(): void
    {
        $viewData = $this->buildViewData(AssessmentKetenagaanType::TENAGA_PENDIDIK->value);
        $viewData['certificateLinks'] = [
            [
                'assessment_title' => 'Portfolio Guru',
                'form_title' => 'Pengalaman Pelatihan',
                'field_label' => 'Riwayat Pengalaman Pelatihan',
                'link_label' => 'Link Google Drive Sertifikat',
                'title' => 'Bimtek Numerasi',
                'detail' => 'Penyelenggara: BBGTK Sulsel • Tahun: 2026',
                'url' => 'https://drive.google.com/file/d/sertifikat-1/view',
                'row_number' => 1,
            ],
        ];

        $response = $this->view('assessment.result.result', $viewData);

        $response->assertSee('Link Sertifikasi Peserta');
        $response->assertSee('Bimtek Numerasi');
        $response->assertSee('Lihat Sertifikat');
        $response->assertSee('https://drive.google.com/file/d/sertifikat-1/view');
    }

    public function test_stakeholder_pdf_view_renders_question_answers(): void
    {
        $viewData = $this->buildViewData(AssessmentKetenagaanType::STAKEHOLDER->value);

        $response = $this->view('assessment.result.pdf.stakeholder', [
            'guru' => $viewData['guru'],
            'target' => $viewData['target'],
            'attempt' => $viewData['attempt'],
            'summary' => $viewData['summary'],
            'scoringSummary' => $viewData['scoringSummary'],
            'generatedAt' => Carbon::parse('2026-07-11 07:00:00'),
            'targetKetenagaanLabel' => 'Stakeholder',
            'assessmentSections' => [
                [
                    'title' => 'Assessment Stakeholder',
                    'code' => 'ASM-STK-1',
                    'description' => 'Deskripsi assessment stakeholder',
                    'instrument_label' => 'Validasi Ahli',
                    'forms' => [
                        [
                            'title' => 'Form Stakeholder',
                            'code' => 'FORM-STK-1',
                            'description' => 'Deskripsi form stakeholder',
                            'competency_label' => null,
                            'indicator_code' => null,
                            'indicator_label' => null,
                            'questions' => [
                                [
                                    'field_id' => 901,
                                    'type' => 'textarea',
                                    'label' => '1. Tuliskan masukan Anda',
                                    'description' => 'Deskripsi pertanyaan',
                                    'help' => 'Bantuan jawaban',
                                    'is_required' => true,
                                    'has_answer' => true,
                                    'answer_text' => 'Masukan stakeholder untuk instrumen.',
                                    'answered_at' => '11 Jul 2026 06:25',
                                    'repeater_columns' => [],
                                    'repeater_rows' => [],
                                    'file_name' => '',
                                    'file_url' => null,
                                    'file_preview_data_uri' => null,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $response->assertSee('Hasil Assessment Stakeholder');
        $response->assertSee('Form Stakeholder');
        $response->assertSee('Masukan stakeholder untuk instrumen.');
    }

    public function test_pdf_preview_image_helper_converts_webp_upload_to_png_data_uri(): void
    {
        if (! function_exists('imagewebp')) {
            $this->markTestSkipped('Ekstensi GD tanpa dukungan WEBP tidak tersedia.');
        }

        Storage::fake('public');

        $image = imagecreatetruecolor(24, 24);
        $white = imagecolorallocate($image, 255, 255, 255);
        $blue = imagecolorallocate($image, 19, 118, 189);
        imagefill($image, 0, 0, $white);
        imagefilledellipse($image, 12, 12, 16, 16, $blue);

        ob_start();
        imagewebp($image);
        $webpContents = ob_get_clean();
        imagedestroy($image);

        Storage::disk('public')->put('assessment/attempts/test/tanda-tangan.webp', $webpContents);

        $controller = (new ReflectionClass(PortalController::class))->newInstanceWithoutConstructor();
        $method = (new ReflectionClass(PortalController::class))->getMethod('buildPdfImageDataUri');
        $method->setAccessible(true);

        $dataUri = $method->invoke($controller, 'assessment/attempts/test/tanda-tangan.webp');

        $this->assertIsString($dataUri);
        $this->assertStringStartsWith('data:image/png;base64,', $dataUri);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildViewData(string $targetKetenagaan): array
    {
        $guru = new Guru([
            'nama_lengkap' => 'Siti Purnama Saputra',
            'no_ktp' => '7301000000000001',
            'satuan_pendidikan' => 'SD Negeri 1 Pinrang',
            'email' => 'siti@example.test',
        ]);

        $assignment = new AssessmentAssignment([
            'kode_penugasan' => 'TGS-ASM-20260711-062307-UPE0',
            'judul_penugasan' => 'VALIDASI',
            'deskripsi' => 'Hasil pengisian assessment peserta tersimpan dan dapat ditinjau kembali kapan saja.',
            'session_enabled' => false,
            'target_ketenagaan' => $targetKetenagaan,
        ]);

        $target = new AssessmentAssignmentTarget([
            'started_at' => Carbon::parse('2026-07-11 06:24:00'),
            'deadline_at' => Carbon::parse('2026-07-11 09:24:00'),
        ]);
        $target->id = 42;
        $target->setRelation('assignment', $assignment);
        $target->setRelation('guru', $guru);

        $attempt = new AssessmentAttempt([
            'started_at' => Carbon::parse('2026-07-11 06:24:00'),
            'submitted_at' => Carbon::parse('2026-07-11 06:26:00'),
            'structure_snapshot' => [
                'meta' => [
                    'total_questions' => 1,
                    'required_questions' => 1,
                ],
                'assessments' => [
                    [
                        'id' => 701,
                        'kode_assessment' => 'ASM-STK-1',
                        'judul' => 'Assessment Stakeholder',
                        'deskripsi' => 'Deskripsi assessment stakeholder',
                        'forms' => [
                            [
                                'id' => 801,
                                'judul_form' => 'Form Stakeholder',
                                'kode_form' => 'FORM-STK-1',
                                'deskripsi' => 'Deskripsi form stakeholder',
                                'fields' => [
                                    [
                                        'id' => 901,
                                        'assessment_id' => 701,
                                        'assessment_form_id' => 801,
                                        'label' => 'Tuliskan masukan Anda',
                                        'tipe_field' => 'textarea',
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

        return [
            'menu' => 'assessment-portal',
            'guru' => $guru,
            'target' => $target,
            'attempt' => $attempt,
            'meta' => [
                'session_label' => 'Tanpa sesi',
                'session_schedule_text' => 'Akses fleksibel kapan saja',
                'label' => 'Selesai',
                'badge' => 'primary',
                'date_text' => 'Tanpa batas tanggal',
                'assessment_total' => 1,
                'form_total' => 1,
                'description' => 'Assessment peserta sudah dikirim dan hasilnya dapat ditinjau kembali kapan saja.',
            ],
            'summary' => [
                'total_questions' => 1,
                'required_questions' => 1,
                'answered_questions' => 1,
                'answered_required_questions' => 1,
                'completion_percentage' => 100,
                'duration_minutes' => 2,
            ],
            'scoringSummary' => [],
            'answerLookup' => [],
            'certificateLinks' => [],
            'viewerMode' => 'participant',
            'backUrl' => route('assessment.portal.dashboard'),
            'backLabel' => 'Kembali ke Dashboard',
        ];
    }
}
