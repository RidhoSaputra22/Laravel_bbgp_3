<?php

namespace Database\Seeders;

use App\Enum\AssessmentInstrumentType;
use App\Enum\AssessmentKetenagaanType;
use App\Enum\KompetensiGuru;
use App\Models\Assessment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AssessmentProtofolioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $assessments = [
            [
                'kode_assessment' => 'ASM-PORTOFOLIO-001',
                'judul' => 'Instrumen Portofolio Kompetensi Guru',
                'deskripsi' => 'Instrumen portofolio untuk memetakan identitas, riwayat pendidikan, pengalaman, prestasi, karya, dan refleksi diri responden.',
                'petunjuk' => 'Isilah data berikut secara jujur dan lengkap. Sertakan bukti dokumen pendukung pada setiap bagian yang relevan.',
                'instrument_type' => AssessmentInstrumentType::PORTOFOLIO->value,
                'status' => 'publish',
                'is_active' => true,
                'forms' => [
                    [
                        'judul_form' => 'Identitas Responden',
                        'kode_form' => 'FORM-IDENTITAS',
                        'deskripsi' => 'Data identitas dasar responden dalam instrumen portofolio.',
                        'is_scoreable' => false,
                        'urutan' => 1,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Nama Lengkap',
                                'deskripsi' => 'Tuliskan nama lengkap sesuai identitas resmi.',
                                'nama_field' => 'nama_lengkap',
                                'tipe_field' => 'text',
                                'placeholder' => 'Masukkan nama lengkap',
                                'bantuan' => 'Gunakan nama sesuai data administrasi.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-6',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'NIP/NUPTK',
                                'deskripsi' => 'Masukkan Nomor Induk Pegawai atau Nomor Unik Pendidik dan Tenaga Kependidikan.',
                                'nama_field' => 'nip_nuptk',
                                'tipe_field' => 'text',
                                'placeholder' => 'Masukkan NIP atau NUPTK',
                                'bantuan' => 'Isi salah satu nomor identitas kepegawaian yang tersedia.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-6',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Pangkat / Golongan',
                                'deskripsi' => 'Tuliskan pangkat atau golongan terakhir, jika ada.',
                                'nama_field' => 'pangkat_golongan',
                                'tipe_field' => 'text',
                                'placeholder' => 'Contoh: Pembina Utama Muda / IV-c',
                                'bantuan' => 'Isi sesuai data kepegawaian terbaru.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => false],
                                'lebar_kolom' => 'col-md-6',
                                'urutan' => 3,
                                'is_required' => false,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Jabatan',
                                'deskripsi' => 'Pilih jabatan utama Anda saat ini.',
                                'nama_field' => 'jabatan',
                                'tipe_field' => 'select',
                                'placeholder' => 'Pilih jabatan',
                                'bantuan' => 'Pilih jabatan yang paling sesuai.',
                                'opsi_field' => [
                                    'Guru',
                                    'Kepala Sekolah',
                                    'Pengawas Sekolah',
                                    'GTK Lainnya',
                                ],
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-6',
                                'urutan' => 4,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Satuan Pendidikan',
                                'deskripsi' => 'Tuliskan nama satuan pendidikan tempat bertugas.',
                                'nama_field' => 'satuan_pendidikan',
                                'tipe_field' => 'text',
                                'placeholder' => 'Contoh: SMP Negeri 1 Jambi',
                                'bantuan' => 'Isi nama sekolah atau instansi saat ini.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-6',
                                'urutan' => 5,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Kabupaten/Kota',
                                'deskripsi' => 'Tuliskan kabupaten atau kota lokasi satuan pendidikan.',
                                'nama_field' => 'kabupaten_kota',
                                'tipe_field' => 'text',
                                'placeholder' => 'Contoh: Kota Jambi',
                                'bantuan' => 'Gunakan nama wilayah secara lengkap.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-6',
                                'urutan' => 6,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Lama Mengajar (Tahun)',
                                'deskripsi' => 'Masukkan total pengalaman mengajar dalam tahun.',
                                'nama_field' => 'lama_mengajar',
                                'tipe_field' => 'number',
                                'placeholder' => '0',
                                'bantuan' => 'Isi jumlah tahun pengalaman mengajar.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true, 'min' => 0],
                                'lebar_kolom' => 'col-md-6',
                                'urutan' => 7,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],

                    [
                        'judul_form' => 'Riwayat Pendidikan Formal',
                        'kode_form' => 'FORM-PENDIDIKAN',
                        'deskripsi' => 'Riwayat pendidikan formal, sertifikasi, dan kualifikasi akademik responden.',
                        'kompetensi' => KompetensiGuru::PROFESIONAL->value,
                        'indikator_kode' => '4.1',
                        'indikator_label' => 'Kualifikasi dan pengembangan akademik',
                        'is_scoreable' => true,
                        'urutan' => 2,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Riwayat Pendidikan Formal',
                                'deskripsi' => 'Masukkan data pendidikan S1, sertifikasi, S2, S3, atau pendidikan relevan lainnya.',
                                'nama_field' => 'riwayat_pendidikan_formal',
                                'tipe_field' => 'repeater',
                                'placeholder' => null,
                                'bantuan' => 'Tambahkan satu baris untuk setiap jenjang pendidikan atau sertifikasi.',
                                'opsi_field' => [
                                    'min_rows' => 1,
                                    'max_rows' => 10,
                                    'columns' => [
                                        [
                                            'label' => 'Jenjang',
                                            'nama_field' => 'jenjang',
                                            'tipe_field' => 'select',
                                            'opsi_field' => ['S1', 'Sertifikasi', 'S2', 'S3'],
                                            'is_required' => true,
                                        ],
                                        [
                                            'label' => 'Gelar',
                                            'nama_field' => 'gelar',
                                            'tipe_field' => 'text',
                                            'placeholder' => 'Contoh: S.Pd., M.Pd.',
                                            'is_required' => false,
                                        ],
                                        [
                                            'label' => 'Program Studi',
                                            'nama_field' => 'program_studi',
                                            'tipe_field' => 'text',
                                            'placeholder' => 'Contoh: Pendidikan Bahasa Indonesia',
                                            'is_required' => false,
                                        ],
                                        [
                                            'label' => 'Lembaga',
                                            'nama_field' => 'lembaga',
                                            'tipe_field' => 'text',
                                            'placeholder' => 'Nama perguruan tinggi/lembaga',
                                            'is_required' => true,
                                        ],
                                        [
                                            'label' => 'Tahun Perolehan',
                                            'nama_field' => 'tahun_perolehan',
                                            'tipe_field' => 'number',
                                            'placeholder' => '2020',
                                            'is_required' => false,
                                        ],
                                    ],
                                ],
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],

                    [
                        'judul_form' => 'Pengalaman Pelatihan',
                        'kode_form' => 'FORM-PELATIHAN',
                        'deskripsi' => 'Pengalaman pelatihan yang relevan dengan profesi dalam lima tahun terakhir.',
                        'kompetensi' => KompetensiGuru::PROFESIONAL->value,
                        'indikator_kode' => '4.2',
                        'indikator_label' => 'Pengembangan profesi berkelanjutan',
                        'is_scoreable' => true,
                        'urutan' => 3,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Pengalaman Pelatihan Relevan',
                                'deskripsi' => 'Cantumkan pelatihan, workshop, bimtek, seminar, atau program pengembangan profesi dalam lima tahun terakhir.',
                                'nama_field' => 'pengalaman_pelatihan',
                                'tipe_field' => 'repeater',
                                'placeholder' => null,
                                'bantuan' => 'Tambahkan satu baris untuk setiap pelatihan.',
                                'opsi_field' => [
                                    'min_rows' => 0,
                                    'max_rows' => 20,
                                    'columns' => [
                                        [
                                            'label' => 'Nama Pelatihan',
                                            'nama_field' => 'nama_pelatihan',
                                            'tipe_field' => 'text',
                                            'placeholder' => 'Nama pelatihan',
                                            'is_required' => true,
                                        ],
                                        [
                                            'label' => 'Penyelenggara',
                                            'nama_field' => 'penyelenggara',
                                            'tipe_field' => 'text',
                                            'placeholder' => 'Nama penyelenggara',
                                            'is_required' => true,
                                        ],
                                        [
                                            'label' => 'Tahun',
                                            'nama_field' => 'tahun',
                                            'tipe_field' => 'number',
                                            'placeholder' => '2025',
                                            'is_required' => true,
                                        ],
                                        [
                                            'label' => 'Durasi (JP)',
                                            'nama_field' => 'durasi_jp',
                                            'tipe_field' => 'number',
                                            'placeholder' => '32',
                                            'is_required' => false,
                                        ],
                                        [
                                            'label' => 'Dampak terhadap Sekolah',
                                            'nama_field' => 'dampak_sekolah',
                                            'tipe_field' => 'textarea',
                                            'placeholder' => 'Jelaskan dampak pelatihan terhadap sekolah',
                                            'is_required' => false,
                                        ],
                                        [
                                            'label' => 'Link Google Drive Sertifikat',
                                            'nama_field' => 'link_google_drive_sertifikat',
                                            'tipe_field' => 'url',
                                            'placeholder' => 'https://drive.google.com/...',
                                            'is_required' => false,
                                        ],
                                    ],
                                ],
                                'nilai_default' => null,
                                'validasi' => ['required' => false],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => false,
                                'is_active' => true,
                            ],
                        ],
                    ],

                    [
                        'judul_form' => 'Pengalaman Mengajar',
                        'kode_form' => 'FORM-PENGALAMAN-MENGAJAR',
                        'deskripsi' => 'Riwayat pengalaman mengajar responden pada satuan pendidikan atau lembaga terkait.',
                        'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                        'indikator_kode' => '1.2',
                        'indikator_label' => 'Pengalaman praktik pembelajaran',
                        'is_scoreable' => true,
                        'urutan' => 4,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Riwayat Pengalaman Mengajar',
                                'deskripsi' => 'Masukkan pengalaman mengajar yang pernah atau sedang dijalankan.',
                                'nama_field' => 'pengalaman_mengajar',
                                'tipe_field' => 'repeater',
                                'placeholder' => null,
                                'bantuan' => 'Tambahkan satu baris untuk setiap pengalaman mengajar.',
                                'opsi_field' => [
                                    'min_rows' => 1,
                                    'max_rows' => 20,
                                    'columns' => [
                                        [
                                            'label' => 'Pengalaman',
                                            'nama_field' => 'pengalaman',
                                            'tipe_field' => 'text',
                                            'placeholder' => 'Contoh: Guru Mata Pelajaran IPA',
                                            'is_required' => true,
                                        ],
                                        [
                                            'label' => 'Lembaga',
                                            'nama_field' => 'lembaga',
                                            'tipe_field' => 'text',
                                            'placeholder' => 'Nama sekolah/lembaga',
                                            'is_required' => true,
                                        ],
                                        [
                                            'label' => 'Tahun',
                                            'nama_field' => 'tahun',
                                            'tipe_field' => 'text',
                                            'placeholder' => 'Contoh: 2019–2024',
                                            'is_required' => true,
                                        ],
                                        [
                                            'label' => 'Link Google Drive Sertifikat / SK',
                                            'nama_field' => 'link_google_drive_sertifikat_sk',
                                            'tipe_field' => 'url',
                                            'placeholder' => 'https://drive.google.com/...',
                                            'is_required' => false,
                                        ],
                                    ],
                                ],
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],

                    [
                        'judul_form' => 'Kolaborasi dan Kemitraan Pembelajaran',
                        'kode_form' => 'FORM-KOLABORASI',
                        'deskripsi' => 'Pengalaman kolaborasi guru dengan warga sekolah, orang tua, masyarakat, atau jejaring profesional untuk mendukung pembelajaran.',
                        'kompetensi' => KompetensiGuru::SOSIAL->value,
                        'indikator_kode' => '3.1',
                        'indikator_label' => 'Kolaborasi dan keterlibatan pihak lain',
                        'is_scoreable' => true,
                        'urutan' => 5,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Kolaborasi dan Kemitraan yang Pernah Dilakukan',
                                'deskripsi' => 'Cantumkan kegiatan kolaborasi yang mendukung pembelajaran atau perkembangan peserta didik.',
                                'nama_field' => 'kolaborasi_kemitraan',
                                'tipe_field' => 'repeater',
                                'placeholder' => null,
                                'bantuan' => 'Tambahkan satu baris untuk setiap kolaborasi atau kemitraan yang relevan, misalnya dengan rekan sejawat, orang tua, atau masyarakat.',
                                'opsi_field' => [
                                    'min_rows' => 0,
                                    'max_rows' => 20,
                                    'columns' => [
                                        [
                                            'label' => 'Program / Aktivitas',
                                            'nama_field' => 'program_aktivitas',
                                            'tipe_field' => 'text',
                                            'placeholder' => 'Contoh: Kelas Orang Tua, Lesson Study, Proyek Komunitas',
                                            'is_required' => true,
                                        ],
                                        [
                                            'label' => 'Pihak / Mitra Terlibat',
                                            'nama_field' => 'pihak_terlibat',
                                            'tipe_field' => 'text',
                                            'placeholder' => 'Contoh: Guru mapel, komite sekolah, orang tua, puskesmas',
                                            'is_required' => true,
                                        ],
                                        [
                                            'label' => 'Peran Guru dan Bentuk Komunikasi',
                                            'nama_field' => 'peran_dan_komunikasi',
                                            'tipe_field' => 'textarea',
                                            'placeholder' => 'Jelaskan peran guru, pembagian tugas, dan bentuk komunikasi yang digunakan',
                                            'is_required' => true,
                                        ],
                                        [
                                            'label' => 'Dampak terhadap Pembelajaran',
                                            'nama_field' => 'dampak_sekolah',
                                            'tipe_field' => 'textarea',
                                            'placeholder' => 'Jelaskan dampak kolaborasi terhadap pembelajaran atau dukungan peserta didik',
                                            'is_required' => false,
                                        ],
                                        [
                                            'label' => 'Bukti / Tautan Dokumen',
                                            'nama_field' => 'bukti_tautan',
                                            'tipe_field' => 'text',
                                            'placeholder' => 'Contoh: Surat tugas, notulen, foto kegiatan, tautan laporan',
                                            'is_required' => false,
                                        ],
                                    ],
                                ],
                                'nilai_default' => null,
                                'validasi' => ['required' => false],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => false,
                                'is_active' => true,
                            ],
                        ],
                    ],

                    [
                        'judul_form' => 'Penguasaan Profesional: Konten, Kurikulum, dan Kebutuhan Peserta Didik',
                        'kode_form' => 'FORM-PENGUASAAN-PROFESIONAL',
                        'deskripsi' => 'Bukti penguasaan materi, kurikulum, strategi, dan penyesuaian pembelajaran terhadap karakteristik peserta didik.',
                        'kompetensi' => KompetensiGuru::PROFESIONAL->value,
                        'indikator_kode' => '4.4',
                        'indikator_label' => 'Penguasaan konten, kurikulum, dan karakteristik peserta didik',
                        'is_scoreable' => true,
                        'urutan' => 6,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Contoh Praktik Penguasaan Profesional',
                                'deskripsi' => 'Cantumkan contoh perencanaan atau implementasi pembelajaran yang menunjukkan penguasaan konten, kurikulum, dan kebutuhan peserta didik.',
                                'nama_field' => 'penguasaan_profesional',
                                'tipe_field' => 'repeater',
                                'placeholder' => null,
                                'bantuan' => 'Tambahkan satu baris untuk setiap contoh praktik profesional, misalnya modul ajar, topik, unit pembelajaran, atau perbaikan desain pembelajaran.',
                                'opsi_field' => [
                                    'min_rows' => 1,
                                    'max_rows' => 20,
                                    'columns' => [
                                        [
                                            'label' => 'Topik / Materi',
                                            'nama_field' => 'topik_materi',
                                            'tipe_field' => 'text',
                                            'placeholder' => 'Contoh: Sistem Pernapasan / Teks Eksplanasi',
                                            'is_required' => true,
                                        ],
                                        [
                                            'label' => 'Capaian / Tujuan Pembelajaran',
                                            'nama_field' => 'capaian_tujuan',
                                            'tipe_field' => 'text',
                                            'placeholder' => 'Contoh: Peserta didik mampu menjelaskan proses dan menerapkan konsep',
                                            'is_required' => true,
                                        ],
                                        [
                                            'label' => 'Karakteristik dan Kebutuhan Peserta Didik',
                                            'nama_field' => 'karakteristik_peserta_didik',
                                            'tipe_field' => 'textarea',
                                            'placeholder' => 'Jelaskan kebutuhan belajar, hambatan, atau karakteristik peserta didik yang dipertimbangkan',
                                            'is_required' => true,
                                        ],
                                        [
                                            'label' => 'Strategi dan Sumber Belajar',
                                            'nama_field' => 'strategi_sumber_belajar',
                                            'tipe_field' => 'textarea',
                                            'placeholder' => 'Jelaskan strategi, sumber belajar, dan alasan pemilihannya',
                                            'is_required' => true,
                                        ],
                                        [
                                            'label' => 'Asesmen / Umpan Balik',
                                            'nama_field' => 'asesmen_umpan_balik',
                                            'tipe_field' => 'textarea',
                                            'placeholder' => 'Jelaskan bentuk asesmen dan umpan balik yang digunakan',
                                            'is_required' => false,
                                        ],
                                        [
                                            'label' => 'Perbaikan Berbasis Data',
                                            'nama_field' => 'perbaikan_berbasis_data',
                                            'tipe_field' => 'textarea',
                                            'placeholder' => 'Jelaskan bagaimana data hasil belajar dipakai untuk memperbaiki pembelajaran',
                                            'is_required' => false,
                                        ],
                                        [
                                            'label' => 'Link Google Drive Sertifikat / SK',
                                            'nama_field' => 'link_google_drive_sertifikat_sk',
                                            'tipe_field' => 'url',
                                            'placeholder' => 'https://drive.google.com/...',
                                            'is_required' => false,
                                        ],
                                    ],
                                ],
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],

                    [
                        'judul_form' => 'Karya, Inovasi, Best Practice, dan Diseminasi',
                        'kode_form' => 'FORM-KARYA-INOVASI',
                        'deskripsi' => 'Dokumentasi karya, inovasi, praktik baik, prestasi, atau diseminasi yang memperkuat mutu portofolio secara keseluruhan.',
                        'kompetensi' => KompetensiGuru::PROFESIONAL->value,
                        'indikator_kode' => '4.5',
                        'indikator_label' => 'Karya, inovasi, prestasi, dan diseminasi',
                        'is_scoreable' => true,
                        'urutan' => 7,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Karya / Inovasi / Prestasi / Diseminasi',
                                'deskripsi' => 'Masukkan kontribusi profesional yang pernah diterapkan, dibagikan, atau diakui.',
                                'nama_field' => 'karya_inovasi_best_practice',
                                'tipe_field' => 'repeater',
                                'placeholder' => null,
                                'bantuan' => 'Tambahkan satu baris untuk setiap karya, inovasi, praktik baik, prestasi, atau bentuk diseminasi hasil kerja Anda.',
                                'opsi_field' => [
                                    'min_rows' => 0,
                                    'max_rows' => 20,
                                    'columns' => [
                                        [
                                            'label' => 'Jenis Kontribusi',
                                            'nama_field' => 'jenis_kontribusi',
                                            'tipe_field' => 'select',
                                            'opsi_field' => ['Karya', 'Inovasi', 'Best Practice', 'Prestasi', 'Penghargaan', 'Diseminasi'],
                                            'is_required' => true,
                                        ],
                                        [
                                            'label' => 'Judul / Nama Kegiatan',
                                            'nama_field' => 'judul_kontribusi',
                                            'tipe_field' => 'text',
                                            'placeholder' => 'Masukkan judul karya, nama kegiatan, atau nama prestasi',
                                            'is_required' => true,
                                        ],
                                        [
                                            'label' => 'Deskripsi Singkat',
                                            'nama_field' => 'deskripsi_singkat',
                                            'tipe_field' => 'textarea',
                                            'placeholder' => 'Jelaskan proses, hasil, atau konteks kontribusi',
                                            'is_required' => true,
                                        ],
                                        [
                                            'label' => 'Dampak terhadap Pembelajaran / Sekolah',
                                            'nama_field' => 'dampak_pembelajaran',
                                            'tipe_field' => 'textarea',
                                            'placeholder' => 'Jelaskan dampaknya terhadap pembelajaran atau sekolah',
                                            'is_required' => false,
                                        ],
                                        [
                                            'label' => 'Diseminasi / Replikasi',
                                            'nama_field' => 'diseminasi_replikasi',
                                            'tipe_field' => 'text',
                                            'placeholder' => 'Contoh: dipresentasikan di KKG/MGMP, dibagikan ke sekolah lain',
                                            'is_required' => false,
                                        ],
                                        [
                                            'label' => 'Link Google Drive Sertifikat / SK',
                                            'nama_field' => 'link_google_drive_sertifikat_sk',
                                            'tipe_field' => 'url',
                                            'placeholder' => 'https://drive.google.com/...',
                                            'is_required' => false,
                                        ],
                                    ],
                                ],
                                'nilai_default' => null,
                                'validasi' => ['required' => false],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => false,
                                'is_active' => true,
                            ],
                        ],
                    ],

                    [
                        'judul_form' => 'Refleksi Diri',
                        'kode_form' => 'FORM-REFLEKSI-DIRI',
                        'deskripsi' => 'Refleksi responden mengenai kekuatan dan area pengembangan sebagai guru.',
                        'kompetensi' => KompetensiGuru::KEPRIBADIAN->value,
                        'indikator_kode' => '2.2',
                        'indikator_label' => 'Refleksi dan pengembangan diri',
                        'is_scoreable' => true,
                        'urutan' => 8,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Kekuatan dan Area Pengembangan Diri',
                                'deskripsi' => 'Tuliskan kekuatan utama serta area pengembangan Anda sebagai guru.',
                                'nama_field' => 'refleksi_diri',
                                'tipe_field' => 'textarea',
                                'placeholder' => 'Tuliskan kekuatan, tantangan, dan kebutuhan pengembangan diri Anda.',
                                'bantuan' => 'Jelaskan secara reflektif berdasarkan pengalaman mengajar dan kebutuhan pengembangan profesional.',
                                'opsi_field' => null,
                                'nilai_default' => null,
                                'validasi' => ['required' => true, 'min_length' => 100],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($assessments as $item) {
            $forms = $item['forms'];
            unset($item['forms']);

            $assessment = Assessment::updateOrCreate(
                ['kode_assessment' => $item['kode_assessment']],
                [
                    'judul' => $item['judul'],
                    'slug' => Str::slug($item['judul']),
                    'deskripsi' => $item['deskripsi'],
                    'petunjuk' => $item['petunjuk'],
                    'instrument_type' => $item['instrument_type'] ?? null,
                    'target_ketenagaan' => AssessmentKetenagaanType::TENAGA_PENDIDIK->value,
                    'scoring_config' => $this->assessmentScoringConfig(),
                    'status' => $item['status'],
                    'is_active' => $item['is_active'],
                ]
            );

            $assessment->forms()->delete();

            foreach ($forms as $formData) {
                $fields = $formData['fields'];
                unset($formData['fields']);
                $formData['scoring_config'] = $this->formScoringConfig($formData);

                $form = $assessment->forms()->create($formData);

                foreach (array_values($fields) as $fieldIndex => $fieldData) {
                    $fieldData['scoring_config'] = $this->fieldScoringConfig($formData, $fieldData, $fieldIndex);
                    $form->fields()->create($fieldData);
                }
            }
        }
    }

    private function assessmentScoringConfig(): array
    {
        return [
            'profile' => AssessmentInstrumentType::PORTOFOLIO->value,
            'weight' => AssessmentInstrumentType::PORTOFOLIO->weight(),
            'verification_gap_threshold' => 1.5,
            'advanced_rules' => [
                'overall_formula' => 'Skor aspek = (level / 5) x bobot. Total portofolio = jumlah skor seluruh aspek.',
                'competency_source_map' => [
                    KompetensiGuru::PEDAGOGIK->value => 'P2',
                    KompetensiGuru::KEPRIBADIAN->value => 'P3',
                    KompetensiGuru::SOSIAL->value => 'P4',
                    KompetensiGuru::PROFESIONAL->value => 'P5',
                ],
                'portfolio_quality_aspects' => ['P1', 'P6', 'P7'],
                'verification_note' => 'Aspek P7 keabsahan, kelengkapan, dan keterlacakan bukti tetap memerlukan verifikasi assessor atas dokumen yang diunggah atau dirujuk peserta.',
            ],
        ];
    }

    private function formScoringConfig(array $formData): ?array
    {
        if (! ($formData['is_scoreable'] ?? false)) {
            return null;
        }

        $config = $this->formRubricMap()[$formData['kode_form'] ?? ''] ?? [
            'rubric_code' => null,
            'weight' => 10,
            'exclude_from_competency' => false,
            'aspect_name' => 'Aspek portofolio umum',
            'accepted_evidence' => [],
            'scoring_note' => null,
        ];

        return [
            'profile' => 'portofolio',
            'weight' => $config['weight'],
            'exclude_from_competency' => $config['exclude_from_competency'],
            'advanced_rules' => [
                'rubric_code' => $config['rubric_code'],
                'aspect_name' => $config['aspect_name'],
                'accepted_evidence' => $config['accepted_evidence'],
                'scoring_note' => $config['scoring_note'],
                'competency_target' => $formData['kompetensi'] ?? null,
                'form_formula' => 'Level form dihasilkan dari auto scoring field utama, lalu dikonversi ke skala 1-5 sesuai rubrik portofolio.',
            ],
        ];
    }

    private function fieldScoringConfig(array $formData, array $fieldData, int $fieldIndex): ?array
    {
        if (! ($formData['is_scoreable'] ?? false)) {
            return null;
        }

        $formCode = $formData['kode_form'] ?? '';
        $baseConfig = [
            'enabled' => true,
            'profile' => 'portofolio',
            'weight' => 100,
            'rubric_code' => data_get($this->formScoringConfig($formData), 'advanced_rules.rubric_code'),
            'scale_min' => 1,
            'scale_max' => 5,
        ];

        $library = $this->fieldRubricLibrary();

        if (isset($library[$formCode])) {
            return array_merge($baseConfig, $library[$formCode]);
        }

        return array_merge($baseConfig, [
            'method' => ($fieldData['tipe_field'] ?? '') === 'textarea' ? 'semantic_similarity' : 'repeater_completeness',
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function formRubricMap(): array
    {
        return [
            'FORM-PENDIDIKAN' => [
                'rubric_code' => 'P1',
                'weight' => 5,
                'exclude_from_competency' => true,
                'aspect_name' => 'Relevansi riwayat pendidikan formal',
                'accepted_evidence' => ['ijazah', 'transkrip', 'sertifikat', 'bukti studi lanjut'],
                'scoring_note' => 'Menilai keterkaitan pendidikan formal dengan kebutuhan mengajar serta bukti keberlanjutan pengembangan diri.',
            ],
            'FORM-PELATIHAN' => [
                'rubric_code' => 'P1',
                'weight' => 5,
                'exclude_from_competency' => true,
                'aspect_name' => 'Relevansi pelatihan dan penerapan',
                'accepted_evidence' => ['sertifikat pelatihan', 'agenda', 'jurnal refleksi', 'rencana tindak lanjut', 'bukti penerapan'],
                'scoring_note' => 'Menilai relevansi pelatihan, bukti penerapan, dan hubungan dengan kebutuhan kelas atau sekolah.',
            ],
            'FORM-PENGALAMAN-MENGAJAR' => [
                'rubric_code' => 'P2',
                'weight' => 20,
                'exclude_from_competency' => false,
                'aspect_name' => 'Praktik pedagogik berpusat pada peserta didik',
                'accepted_evidence' => ['modul ajar', 'perangkat asesmen', 'contoh umpan balik', 'dokumentasi kegiatan belajar'],
                'scoring_note' => 'Dipakai sebagai sumber skor portofolio pedagogik pada rekap akhir kompetensi.',
            ],
            'FORM-KOLABORASI' => [
                'rubric_code' => 'P4',
                'weight' => 15,
                'exclude_from_competency' => false,
                'aspect_name' => 'Kolaborasi dengan warga sekolah, orang tua, masyarakat, dan jejaring',
                'accepted_evidence' => ['surat tugas', 'notulen', 'laporan program', 'testimoni', 'rekap kegiatan kemitraan'],
                'scoring_note' => 'Dipakai sebagai sumber skor portofolio sosial pada rekap akhir kompetensi.',
            ],
            'FORM-PENGUASAAN-PROFESIONAL' => [
                'rubric_code' => 'P5',
                'weight' => 20,
                'exclude_from_competency' => false,
                'aspect_name' => 'Penguasaan profesional: konten, kurikulum, dan karakteristik peserta didik',
                'accepted_evidence' => ['modul ajar', 'analisis capaian', 'rancangan asesmen', 'catatan perbaikan berbasis data'],
                'scoring_note' => 'Dipakai sebagai sumber skor portofolio profesional pada rekap akhir kompetensi.',
            ],
            'FORM-KARYA-INOVASI' => [
                'rubric_code' => 'P6',
                'weight' => 10,
                'exclude_from_competency' => true,
                'aspect_name' => 'Karya, inovasi, best practice, prestasi, dan diseminasi',
                'accepted_evidence' => ['naskah', 'media atau produk', 'laporan implementasi', 'data dampak', 'dokumentasi diseminasi'],
                'scoring_note' => 'Memperkuat mutu portofolio secara lintas kompetensi, tetapi tidak menjadi sumber langsung skor kompetensi akhir.',
            ],
            'FORM-REFLEKSI-DIRI' => [
                'rubric_code' => 'P3',
                'weight' => 15,
                'exclude_from_competency' => false,
                'aspect_name' => 'Kepribadian, integritas, regulasi emosi, dan refleksi',
                'accepted_evidence' => ['refleksi tertulis', 'rencana tindakan', 'catatan evaluasi diri', 'bukti perubahan praktik'],
                'scoring_note' => 'Dipakai sebagai sumber skor portofolio kepribadian pada rekap akhir kompetensi.',
            ],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function fieldRubricLibrary(): array
    {
        return [
            'FORM-PENDIDIKAN' => [
                'method' => 'repeater_completeness',
                'reference_answer' => 'Riwayat pendidikan menunjukkan relevansi jenjang, program studi, sertifikasi, lembaga, dan bukti kaitannya dengan kebutuhan mengajar.',
                'keyword_groups' => [
                    ['pendidikan', 'formal'],
                    ['sertifikasi', 'pelatihan'],
                    ['program', 'studi'],
                    ['kebutuhan', 'mengajar'],
                    ['penerapan', 'pembelajaran'],
                ],
                'synonyms' => [
                    'pelatihan' => ['diklat', 'bimtek', 'workshop'],
                    'penerapan' => ['implementasi', 'praktik'],
                ],
                'advanced_rules' => [
                    'target_rows' => 1,
                    'min_words' => 18,
                    'signal_keywords' => ['ijazah', 'sertifikat', 'lembaga', 'program studi', 'penerapan'],
                ],
            ],
            'FORM-PELATIHAN' => [
                'method' => 'repeater_completeness',
                'reference_answer' => 'Pelatihan yang dicantumkan relevan dengan kebutuhan kelas atau sekolah, memiliki penyelenggara dan tahun yang jelas, serta menunjukkan dampak penerapan.',
                'keyword_groups' => [
                    ['pelatihan', 'relevan'],
                    ['penyelenggara'],
                    ['tahun'],
                    ['dampak', 'sekolah'],
                    ['penerapan', 'praktik'],
                ],
                'synonyms' => [
                    'pelatihan' => ['workshop', 'seminar', 'bimtek', 'diklat'],
                    'dampak' => ['manfaat', 'hasil'],
                ],
                'advanced_rules' => [
                    'target_rows' => 1,
                    'min_words' => 18,
                    'signal_keywords' => ['pelatihan', 'penyelenggara', 'dampak', 'sertifikat'],
                ],
            ],
            'FORM-PENGALAMAN-MENGAJAR' => [
                'method' => 'repeater_completeness',
                'reference_answer' => 'Pengalaman mengajar menunjukkan praktik pembelajaran aktif, asesmen atau umpan balik, penyesuaian strategi, dan keberpihakan pada kebutuhan peserta didik.',
                'keyword_groups' => [
                    ['pengalaman', 'mengajar'],
                    ['strategi', 'pembelajaran'],
                    ['partisipasi', 'aktif'],
                    ['asesmen', 'umpan balik'],
                    ['kebutuhan', 'peserta'],
                ],
                'synonyms' => [
                    'asesmen' => ['penilaian', 'evaluasi'],
                    'umpan balik' => ['feedback'],
                    'peserta' => ['siswa'],
                ],
                'advanced_rules' => [
                    'target_rows' => 1,
                    'min_words' => 20,
                    'signal_keywords' => ['pembelajaran aktif', 'asesmen', 'umpan balik', 'partisipasi'],
                ],
            ],
            'FORM-KOLABORASI' => [
                'method' => 'repeater_completeness',
                'reference_answer' => 'Kolaborasi dirancang dengan komunikasi yang jelas, pembagian peran, kemitraan relevan, dan dampak nyata terhadap pembelajaran atau dukungan peserta didik.',
                'keyword_groups' => [
                    ['kolaborasi', 'kemitraan'],
                    ['orang tua', 'masyarakat'],
                    ['komunikasi', 'peran'],
                    ['dampak', 'pembelajaran'],
                    ['dukungan', 'peserta'],
                ],
                'synonyms' => [
                    'kolaborasi' => ['kerja sama', 'kemitraan'],
                    'orang tua' => ['wali'],
                    'masyarakat' => ['komunitas'],
                ],
                'advanced_rules' => [
                    'target_rows' => 1,
                    'min_words' => 20,
                    'signal_keywords' => ['kolaborasi', 'kemitraan', 'komunikasi', 'dampak'],
                ],
            ],
            'FORM-PENGUASAAN-PROFESIONAL' => [
                'method' => 'repeater_completeness',
                'reference_answer' => 'Contoh praktik profesional menunjukkan penguasaan konten, penurunan capaian kurikulum ke tujuan belajar, penyesuaian karakteristik peserta didik, asesmen, dan perbaikan berbasis data.',
                'keyword_groups' => [
                    ['konten', 'materi'],
                    ['kurikulum', 'capaian'],
                    ['karakteristik', 'peserta'],
                    ['strategi', 'sumber belajar'],
                    ['asesmen', 'umpan balik'],
                    ['data', 'perbaikan'],
                ],
                'synonyms' => [
                    'kurikulum' => ['cp', 'capaian pembelajaran'],
                    'asesmen' => ['penilaian', 'evaluasi'],
                    'peserta' => ['siswa'],
                ],
                'advanced_rules' => [
                    'target_rows' => 1,
                    'min_words' => 24,
                    'signal_keywords' => ['kurikulum', 'karakteristik', 'asesmen', 'data', 'strategi'],
                ],
            ],
            'FORM-KARYA-INOVASI' => [
                'method' => 'repeater_completeness',
                'reference_answer' => 'Karya, inovasi, prestasi, atau diseminasi terdokumentasi jelas, diterapkan, menunjukkan dampak, dan memiliki potensi replikasi atau berbagi praktik baik.',
                'keyword_groups' => [
                    ['karya', 'inovasi'],
                    ['best practice', 'praktik baik'],
                    ['prestasi', 'penghargaan'],
                    ['dampak', 'pembelajaran'],
                    ['diseminasi', 'replikasi'],
                ],
                'synonyms' => [
                    'diseminasi' => ['berbagi', 'publikasi'],
                    'replikasi' => ['adaptasi', 'pengembangan'],
                ],
                'advanced_rules' => [
                    'target_rows' => 1,
                    'min_words' => 20,
                    'signal_keywords' => ['inovasi', 'dampak', 'diseminasi', 'prestasi'],
                ],
            ],
            'FORM-REFLEKSI-DIRI' => [
                'method' => 'semantic_similarity',
                'reference_answer' => 'Jawaban reflektif menjelaskan kekuatan, area pengembangan, bukti pengalaman, pertimbangan etik, tujuan pengembangan, dan rencana tindakan yang terukur.',
                'keyword_groups' => [
                    ['kekuatan'],
                    ['area', 'pengembangan'],
                    ['refleksi', 'pengalaman'],
                    ['etik', 'integritas'],
                    ['rencana', 'tindakan'],
                ],
                'synonyms' => [
                    'refleksi' => ['evaluasi diri', 'renungan'],
                    'pengembangan' => ['perbaikan', 'peningkatan'],
                    'rencana' => ['target', 'langkah'],
                ],
                'min_words' => 30,
                'confidence_threshold' => 0.55,
                'manual_review_below_confidence' => false,
                'advanced_rules' => [
                    'min_words' => 30,
                    'signal_keywords' => ['integritas', 'refleksi', 'pengembangan', 'rencana tindakan'],
                    'structure_markers' => [
                        'analysis' => ['kekuatan', 'tantangan', 'pengalaman', 'dampak'],
                        'strategy' => ['tujuan', 'rencana', 'langkah', 'perbaikan'],
                        'evaluation' => ['indikator', 'monitoring', 'refleksi', 'umpan balik'],
                    ],
                ],
            ],
        ];
    }
}
