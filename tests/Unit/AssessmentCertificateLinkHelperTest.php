<?php

namespace Tests\Unit;

use App\Support\Assessment\AssessmentCertificateLinkHelper;
use Tests\TestCase;

class AssessmentCertificateLinkHelperTest extends TestCase
{
    public function test_collects_certificate_links_from_repeater_answers(): void
    {
        $snapshot = [
            'assessments' => [
                [
                    'judul' => 'Portfolio Guru',
                    'forms' => [
                        [
                            'judul_form' => 'Pengalaman Pelatihan',
                            'fields' => [
                                [
                                    'id' => 101,
                                    'label' => 'Riwayat Pengalaman Pelatihan',
                                    'nama_field' => 'pengalaman_pelatihan',
                                    'tipe_field' => 'repeater',
                                    'opsi_field' => [
                                        'columns' => [
                                            [
                                                'label' => 'Nama Pelatihan',
                                                'nama_field' => 'nama_pelatihan',
                                                'tipe_field' => 'text',
                                            ],
                                            [
                                                'label' => 'Penyelenggara',
                                                'nama_field' => 'penyelenggara',
                                                'tipe_field' => 'text',
                                            ],
                                            [
                                                'label' => 'Link Google Drive Sertifikat',
                                                'nama_field' => 'link_google_drive_sertifikat',
                                                'tipe_field' => 'url',
                                            ],
                                        ],
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
                'rows' => [
                    [
                        'nama_pelatihan' => 'Bimtek Numerasi',
                        'penyelenggara' => 'BBGTK Sulsel',
                        'link_google_drive_sertifikat' => 'https://drive.google.com/file/d/sertifikat-1/view',
                    ],
                    [
                        'nama_pelatihan' => 'Workshop Literasi',
                        'penyelenggara' => 'P4TK',
                        'link_google_drive_sertifikat' => 'https://docs.google.com/document/d/sertifikat-2/view',
                    ],
                ],
                'columns' => [],
                'payload' => [],
            ],
        ];

        $links = AssessmentCertificateLinkHelper::collectFromSnapshot($snapshot, $answerLookup);

        $this->assertCount(2, $links);
        $this->assertSame('Bimtek Numerasi', $links[0]['title']);
        $this->assertSame('Pengalaman Pelatihan', $links[0]['form_title']);
        $this->assertSame('https://drive.google.com/file/d/sertifikat-1/view', $links[0]['url']);
        $this->assertSame('Workshop Literasi', $links[1]['title']);
        $this->assertSame('https://docs.google.com/document/d/sertifikat-2/view', $links[1]['url']);
        $this->assertStringContainsString('Penyelenggara: P4TK', (string) $links[1]['detail']);
    }

    public function test_ignores_invalid_or_non_certificate_links(): void
    {
        $snapshot = [
            'assessments' => [
                [
                    'judul' => 'Portfolio Guru',
                    'forms' => [
                        [
                            'judul_form' => 'Kolaborasi',
                            'fields' => [
                                [
                                    'id' => 201,
                                    'label' => 'Kolaborasi',
                                    'nama_field' => 'kolaborasi_kemitraan',
                                    'tipe_field' => 'repeater',
                                    'opsi_field' => [
                                        'columns' => [
                                            [
                                                'label' => 'Program',
                                                'nama_field' => 'program',
                                                'tipe_field' => 'text',
                                            ],
                                            [
                                                'label' => 'Link Dokumentasi',
                                                'nama_field' => 'link_dokumentasi',
                                                'tipe_field' => 'url',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $answerLookup = [
            201 => [
                'rows' => [
                    [
                        'program' => 'Lesson Study',
                        'link_dokumentasi' => 'https://example.com/dokumentasi',
                    ],
                ],
                'columns' => [],
                'payload' => [],
            ],
        ];

        $links = AssessmentCertificateLinkHelper::collectFromSnapshot($snapshot, $answerLookup);

        $this->assertSame([], $links);
    }

    public function test_ignores_non_google_drive_links_for_google_drive_certificate_fields(): void
    {
        $snapshot = [
            'assessments' => [
                [
                    'judul' => 'Portfolio Guru',
                    'forms' => [
                        [
                            'judul_form' => 'Pengalaman Mengajar',
                            'fields' => [
                                [
                                    'id' => 301,
                                    'label' => 'Riwayat Pengalaman Mengajar',
                                    'nama_field' => 'pengalaman_mengajar',
                                    'tipe_field' => 'repeater',
                                    'opsi_field' => [
                                        'columns' => [
                                            [
                                                'label' => 'Pengalaman',
                                                'nama_field' => 'pengalaman',
                                                'tipe_field' => 'text',
                                            ],
                                            [
                                                'label' => 'Link Google Drive Sertifikat / SK',
                                                'nama_field' => 'link_google_drive_sertifikat_sk',
                                                'tipe_field' => 'url',
                                                'placeholder' => 'https://drive.google.com/...',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $answerLookup = [
            301 => [
                'rows' => [
                    [
                        'pengalaman' => 'Guru Kelas 5',
                        'link_google_drive_sertifikat_sk' => 'https://example.com/sk.pdf',
                    ],
                ],
                'columns' => [],
                'payload' => [],
            ],
        ];

        $links = AssessmentCertificateLinkHelper::collectFromSnapshot($snapshot, $answerLookup);

        $this->assertSame([], $links);
    }

    public function test_collects_certificate_links_from_legacy_text_columns_without_snapshot_metadata(): void
    {
        $snapshot = [
            'assessments' => [
                [
                    'judul' => 'Portfolio Guru',
                    'forms' => [
                        [
                            'judul_form' => 'Pengalaman Pelatihan',
                            'fields' => [
                                [
                                    'id' => 401,
                                    'label' => 'Riwayat Pengalaman Pelatihan',
                                    'nama_field' => 'pengalaman_pelatihan',
                                    'tipe_field' => 'repeater',
                                    'opsi_field' => [
                                        'columns' => [
                                            [
                                                'label' => 'Nama Pelatihan',
                                                'nama_field' => 'nama_pelatihan',
                                                'tipe_field' => 'text',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $answerLookup = [
            401 => [
                'rows' => [
                    [
                        'nama_pelatihan' => 'Bimtek Deep Learning',
                        'link_google_drive_sertifikat' => 'https://drive.google.com/file/d/sertifikat-legacy/view',
                    ],
                ],
                'columns' => [],
                'payload' => [],
            ],
        ];

        $links = AssessmentCertificateLinkHelper::collectFromSnapshot($snapshot, $answerLookup);

        $this->assertCount(1, $links);
        $this->assertSame('Bimtek Deep Learning', $links[0]['title']);
        $this->assertSame('https://drive.google.com/file/d/sertifikat-legacy/view', $links[0]['url']);
        $this->assertSame('Link Google Drive Sertifikat', $links[0]['link_label']);
    }

    public function test_collects_certificate_links_from_single_url_fields(): void
    {
        $snapshot = [
            'assessments' => [
                [
                    'judul' => 'Portfolio Guru',
                    'forms' => [
                        [
                            'judul_form' => 'Sertifikasi',
                            'fields' => [
                                [
                                    'id' => 501,
                                    'label' => 'Link Sertifikat Pendidik',
                                    'nama_field' => 'link_sertifikat_pendidik',
                                    'tipe_field' => 'url',
                                    'deskripsi' => 'Tautan sertifikat pendidik',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $answerLookup = [
            501 => [
                'text' => 'https://drive.google.com/file/d/sertifikat-pendidik/view',
                'payload' => [
                    'value' => 'https://drive.google.com/file/d/sertifikat-pendidik/view',
                ],
            ],
        ];

        $links = AssessmentCertificateLinkHelper::collectFromSnapshot($snapshot, $answerLookup);

        $this->assertCount(1, $links);
        $this->assertSame('Link Sertifikat Pendidik', $links[0]['title']);
        $this->assertSame('https://drive.google.com/file/d/sertifikat-pendidik/view', $links[0]['url']);
    }
}
