<?php

namespace Tests\Unit;

use App\Support\Assessment\AssessmentFileAttachmentHelper;
use Tests\TestCase;

class AssessmentFileAttachmentHelperTest extends TestCase
{
    public function test_collects_uploaded_image_file_fields(): void
    {
        $snapshot = [
            'assessments' => [
                [
                    'judul' => 'Assessment Bukti',
                    'forms' => [
                        [
                            'judul_form' => 'Dokumentasi',
                            'fields' => [
                                [
                                    'id' => 101,
                                    'label' => 'Foto Kegiatan',
                                    'nama_field' => 'foto_kegiatan',
                                    'tipe_field' => 'file',
                                    'opsi_field' => [
                                        'input_mode' => 'file',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $answerLookup = [
            101 => [
                'text' => 'bukti.png',
                'payload' => [
                    'original_name' => 'bukti.png',
                    'mime_type' => 'image/png',
                    'size' => 2048,
                ],
                'file_path' => 'assessment/attempts/1/bukti.png',
                'file_url' => '/storage/assessment/attempts/1/bukti.png',
            ],
        ];

        $attachments = AssessmentFileAttachmentHelper::collectFromSnapshot($snapshot, $answerLookup);

        $this->assertCount(1, $attachments);
        $this->assertSame('Foto Kegiatan', $attachments[0]['field_label']);
        $this->assertSame('bukti.png', $attachments[0]['file_name']);
        $this->assertSame('image', $attachments[0]['preview_type']);
        $this->assertSame('/storage/assessment/attempts/1/bukti.png', $attachments[0]['preview_url']);
        $this->assertSame('2.0 KB', $attachments[0]['size_text']);
    }

    public function test_collects_google_drive_link_file_fields_with_embed_preview(): void
    {
        $snapshot = [
            'assessments' => [
                [
                    'judul' => 'Assessment Bukti',
                    'forms' => [
                        [
                            'judul_form' => 'Portofolio',
                            'fields' => [
                                [
                                    'id' => 201,
                                    'label' => 'Link Sertifikat',
                                    'nama_field' => 'link_sertifikat',
                                    'tipe_field' => 'file',
                                    'opsi_field' => [
                                        'input_mode' => 'link',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $driveUrl = 'https://drive.google.com/drive/folders/1abcDEF_234?usp=drive_link';

        $answerLookup = [
            201 => [
                'text' => $driveUrl,
                'payload' => [
                    'input_mode' => 'link',
                    'link_url' => $driveUrl,
                ],
                'file_path' => null,
                'file_url' => $driveUrl,
            ],
        ];

        $attachments = AssessmentFileAttachmentHelper::collectFromSnapshot($snapshot, $answerLookup);

        $this->assertCount(1, $attachments);
        $this->assertSame('Link Sertifikat', $attachments[0]['file_name']);
        $this->assertSame('embed', $attachments[0]['preview_type']);
        $this->assertSame('https://drive.google.com/embeddedfolderview?id=1abcDEF_234#grid', $attachments[0]['preview_url']);
        $this->assertSame('Link file', $attachments[0]['source_label']);
    }

    public function test_ignores_non_file_fields(): void
    {
        $snapshot = [
            'assessments' => [
                [
                    'forms' => [
                        [
                            'fields' => [
                                [
                                    'id' => 301,
                                    'label' => 'Link Referensi',
                                    'tipe_field' => 'url',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $answerLookup = [
            301 => [
                'text' => 'https://example.com/file.pdf',
                'payload' => [],
                'file_url' => null,
            ],
        ];

        $this->assertSame([], AssessmentFileAttachmentHelper::collectFromSnapshot($snapshot, $answerLookup));
    }
}
