<?php

namespace Database\Seeders;

use App\Enum\AssessmentInstrumentType;
use App\Enum\KompetensiGuru;
use App\Enum\LevelKompetensi;
use App\Models\Assessment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AssessmentPilihanGandaKepalaSekolahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $assessments = [
                [
                    'kode_assessment' => 'ASM-KS-PG-001',
                    'judul' => 'Tes Pilihan Ganda Kompleks Kompetensi Kepala Sekolah',
                    'deskripsi' => 'Instrumen pemetaan kompetensi kepala sekolah yang mencakup kompetensi kepribadian, sosial, dan profesional melalui 72 soal situasional.',
                    'petunjuk' => 'Pilihlah jawaban yang paling sesuai dengan kondisi atau pemahaman Anda saat ini secara jujur. Tidak ada jawaban yang salah; setiap pilihan merepresentasikan level kompetensi dari Level 1 (Paham) hingga Level 5 (Ahli).',
                    'instrument_type' => AssessmentInstrumentType::PILIHAN_GANDA_KOMPLEKS->value,
                    'status' => 'publish',
                    'is_active' => true,
                    'forms' => [
                        [
                            'judul_form' => '1.1.1 Makna, tujuan, dan pandangan hidup kepemimpinan satuan pendidikan berdasarkan prinsip moral dan keyakinan terhadap Tuhan Yang Maha Esa dalam memimpin satuan pendidikan',
                            'deskripsi' => 'Kompetensi Kepribadian — Indikator 1.1: Kematangan moral, emosi, dan spiritual dalam berperilaku sesuai dengan kode etik.',
                            'kompetensi' => KompetensiGuru::KEPRIBADIAN->value,
                            'indikator_kode' => '1.1',
                            'indikator_label' => 'Kematangan moral, emosi, dan spiritual dalam berperilaku sesuai dengan kode etik',
                            'is_scoreable' => true,
                            'urutan' => 1,
                            'is_active' => true,
                            'fields' => [
                                [
                                    'label' => 'Soal 1. Sebagai kepala sekolah, tindakan yang paling tepat dilakukan adalah ...',
                                    'deskripsi' => 'Sebuah sekolah mengalami penurunan prestasi akademik dan meningkatnya pelanggaran disiplin peserta didik. Sebagian guru mengusulkan agar kepala sekolah lebih berfokus pada pencapaian target nilai, sedangkan sebagian lainnya menilai bahwa pembentukan karakter dan budaya sekolah harus menjadi prioritas utama. Kepala sekolah perlu menentukan arah kepemimpinan yang mampu menjawab kedua kebutuhan tersebut.',
                                    'nama_field' => 'soal_001',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Kepribadian | Indikator: 1.1 — Kematangan moral, emosi, dan spiritual dalam berperilaku sesuai dengan kode etik | Subindikator: 1.1.1 — Makna, tujuan, dan pandangan hidup kepemimpinan satuan pendidikan berdasarkan prinsip moral dan keyakinan terhadap Tuhan Yang Maha Esa dalam memimpin satuan pendidikan | Level HOTS: C4 – Analisis | Pendekatan: Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengamati berbagai pandangan yang berkembang di lingkungan sekolah serta mengarahkan penyamaan persepsi mengenai tujuan penyelenggaraan pendidikan.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menguraikan nilai-nilai moral yang menjadi landasan kepemimpinan sekolah serta mengarahkan penyusunan prioritas yang lebih seimbang.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Mengimplementasikan kepemimpinan yang mengintegrasikan pencapaian akademik dan pembentukan karakter serta mengarahkan seluruh warga sekolah pada tujuan bersama.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menganalisis keterkaitan antara nilai moral, budaya sekolah, dan mutu pendidikan serta mengarahkan pengambilan keputusan yang lebih berintegritas.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Mengembangkan arah kepemimpinan yang berlandaskan nilai moral, spiritual, dan kebermanfaatan bagi seluruh warga sekolah secara berkelanjutan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 1,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 2. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Dalam rapat penyusunan program sekolah, muncul berbagai usulan yang berpotensi meningkatkan citra sekolah secara cepat, namun sebagian di antaranya kurang selaras dengan nilai-nilai integritas dan tujuan pendidikan yang telah disepakati bersama.',
                                    'nama_field' => 'soal_002',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Kepribadian | Indikator: 1.1 — Kematangan moral, emosi, dan spiritual dalam berperilaku sesuai dengan kode etik | Subindikator: 1.1.1 — Makna, tujuan, dan pandangan hidup kepemimpinan satuan pendidikan berdasarkan prinsip moral dan keyakinan terhadap Tuhan Yang Maha Esa dalam memimpin satuan pendidikan | Level HOTS: C5 – Evaluasi | Pendekatan: Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mencermati kesesuaian berbagai usulan program dengan nilai yang dianut sekolah serta mengarahkan pembahasan yang objektif.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menafsirkan dampak setiap usulan terhadap budaya sekolah dan perkembangan peserta didik serta mengarahkan pertimbangan yang lebih bijaksana.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Menggunakan prinsip moral sebagai dasar dalam menetapkan prioritas program sekolah serta mengarahkan pelaksanaannya secara konsisten.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menelaah keselarasan antara kebijakan sekolah dan nilai kepemimpinan yang diyakini serta mengarahkan penyempurnaan kebijakan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Merekonstruksi arah pengembangan sekolah berdasarkan nilai moral dan spiritual sehingga seluruh kebijakan mendukung pendidikan yang bermartabat.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 2,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 3. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Sekolah sedang menyusun Rencana Kerja Jangka Menengah yang akan menjadi arah pengembangan sekolah selama beberapa tahun ke depan. Kepala sekolah ingin memastikan bahwa seluruh program yang dirancang tidak hanya meningkatkan mutu akademik, tetapi juga membangun budaya sekolah yang berkarakter.',
                                    'nama_field' => 'soal_003',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Kepribadian | Indikator: 1.1 — Kematangan moral, emosi, dan spiritual dalam berperilaku sesuai dengan kode etik | Subindikator: 1.1.1 — Makna, tujuan, dan pandangan hidup kepemimpinan satuan pendidikan berdasarkan prinsip moral dan keyakinan terhadap Tuhan Yang Maha Esa dalam memimpin satuan pendidikan | Level HOTS: C6 – Kreasi | Pendekatan: Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengidentifikasi nilai-nilai utama yang perlu menjadi dasar pengembangan sekolah serta mengarahkan penyusunan program yang relevan.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menggambarkan keterkaitan antara visi sekolah dan nilai kepemimpinan yang dianut serta mengarahkan penyusunan program yang lebih selaras.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Mengorganisasi penyusunan program sekolah berdasarkan visi, nilai, dan kebutuhan warga sekolah serta mengarahkan implementasi yang konsisten.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Mengevaluasi kesesuaian seluruh program dengan tujuan pendidikan dan nilai kepemimpinan sekolah serta mengarahkan penyempurnaan yang diperlukan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Merancang sistem kepemimpinan sekolah yang mengintegrasikan nilai moral, spiritual, dan peningkatan mutu secara berkelanjutan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 3,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                            ],
                        ],
                        [
                            'judul_form' => '1.1.2 Pengelolaan Emosi dalam Menjalankan Peran sebagai Kepala Sekolah',
                            'deskripsi' => 'Kompetensi Kepribadian — Indikator 1.1: Kematangan moral, emosi, dan spiritual dalam berperilaku sesuai dengan kode etik.',
                            'kompetensi' => KompetensiGuru::KEPRIBADIAN->value,
                            'indikator_kode' => '1.1',
                            'indikator_label' => 'Kematangan moral, emosi, dan spiritual dalam berperilaku sesuai dengan kode etik',
                            'is_scoreable' => true,
                            'urutan' => 2,
                            'is_active' => true,
                            'fields' => [
                                [
                                    'label' => 'Soal 4. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Pada saat rapat evaluasi sekolah, terjadi perdebatan yang cukup keras antara beberapa guru mengenai pembagian beban kerja. Suasana rapat menjadi tegang dan mulai memengaruhi hubungan kerja antaranggota tim. Seluruh peserta rapat menunggu respons kepala sekolah.',
                                    'nama_field' => 'soal_004',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Kepribadian | Indikator: 1.1 — Kematangan moral, emosi, dan spiritual dalam berperilaku sesuai dengan kode etik | Subindikator: 1.1.2 — Pengelolaan Emosi dalam Menjalankan Peran sebagai Kepala Sekolah | Level HOTS: C4 – Analisis | Pendekatan: Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengamati dinamika emosi yang berkembang selama rapat serta mengarahkan suasana agar kembali kondusif.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menggali penyebab munculnya ketegangan di antara anggota tim serta mengarahkan komunikasi yang lebih terbuka.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Memfasilitasi penyelesaian perbedaan pendapat melalui dialog yang terarah serta mengarahkan tercapainya kesepahaman bersama.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menganalisis faktor-faktor yang memengaruhi konflik emosional dalam tim serta mengarahkan penyelesaian yang berorientasi pada hubungan jangka panjang.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Mengembangkan budaya komunikasi yang mampu menjaga stabilitas emosi dan kolaborasi seluruh warga sekolah secara berkelanjutan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 1,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 5. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Kepala sekolah menerima kritik dari orang tua, komite sekolah, dan guru mengenai kebijakan baru yang belum menunjukkan hasil sesuai harapan. Kritik tersebut disampaikan melalui berbagai media dan mulai memengaruhi kepercayaan warga sekolah.',
                                    'nama_field' => 'soal_005',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Kepribadian | Indikator: 1.1 — Kematangan moral, emosi, dan spiritual dalam berperilaku sesuai dengan kode etik | Subindikator: 1.1.2 — Pengelolaan Emosi dalam Menjalankan Peran sebagai Kepala Sekolah | Level HOTS: C5 – Evaluasi | Pendekatan: Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengenali berbagai respons yang muncul terhadap kebijakan sekolah serta mengarahkan komunikasi yang lebih terbuka.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menafsirkan substansi kritik yang disampaikan berbagai pihak serta mengarahkan dialog yang lebih konstruktif.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Menggunakan komunikasi yang tenang dan berbasis data dalam menjelaskan kebijakan sekolah serta mengarahkan penyelesaian yang kolaboratif.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menilai pengaruh pengelolaan emosi terhadap efektivitas kepemimpinan sekolah serta mengarahkan penyempurnaan pendekatan yang digunakan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Mengintegrasikan kecerdasan emosional dalam setiap proses pengambilan keputusan dan komunikasi kepemimpinan secara berkelanjutan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 2,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 6. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Sekolah sedang melaksanakan berbagai program perubahan secara bersamaan, sehingga muncul tekanan kerja yang tinggi pada kepala sekolah dan seluruh tenaga pendidik. Kondisi tersebut berpotensi memengaruhi kualitas kepemimpinan dan iklim kerja sekolah.',
                                    'nama_field' => 'soal_006',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Kepribadian | Indikator: 1.1 — Kematangan moral, emosi, dan spiritual dalam berperilaku sesuai dengan kode etik | Subindikator: 1.1.2 — Pengelolaan Emosi dalam Menjalankan Peran sebagai Kepala Sekolah | Level HOTS: C6 – Kreasi | Pendekatan: Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengidentifikasi faktor-faktor yang memengaruhi kondisi emosional warga sekolah serta mengarahkan pengelolaan yang lebih baik.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menguraikan strategi menjaga keseimbangan emosi dalam menghadapi perubahan organisasi serta mengarahkan penerapannya secara konsisten.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Menerapkan berbagai strategi pengelolaan emosi dalam memimpin perubahan sekolah serta mengarahkan terciptanya iklim kerja yang positif.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Mengkaji efektivitas strategi pengelolaan emosi dalam mendukung keberhasilan perubahan sekolah serta mengarahkan penyempurnaan yang diperlukan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Merancang budaya organisasi yang memperkuat ketahanan emosional seluruh warga sekolah dalam menghadapi perubahan secara berkelanjutan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 3,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                            ],
                        ],
                        [
                            'judul_form' => '1.1.3 Penerapan Kode Etik dalam Menjalankan Tugas dan Peran sebagai Kepala Sekolah',
                            'deskripsi' => 'Kompetensi Kepribadian — Indikator 1.1: Kematangan moral, emosi, dan spiritual dalam berperilaku sesuai dengan kode etik.',
                            'kompetensi' => KompetensiGuru::KEPRIBADIAN->value,
                            'indikator_kode' => '1.1',
                            'indikator_label' => 'Kematangan moral, emosi, dan spiritual dalam berperilaku sesuai dengan kode etik',
                            'is_scoreable' => true,
                            'urutan' => 3,
                            'is_active' => true,
                            'fields' => [
                                [
                                    'label' => 'Soal 7. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Dalam proses penentuan guru yang akan mengikuti pelatihan nasional, kepala sekolah menerima permintaan dari beberapa pihak agar memberikan prioritas kepada guru tertentu karena hubungan pribadi, meskipun terdapat guru lain yang lebih memenuhi persyaratan.',
                                    'nama_field' => 'soal_007',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Kepribadian | Indikator: 1.1 — Kematangan moral, emosi, dan spiritual dalam berperilaku sesuai dengan kode etik | Subindikator: 1.1.3 — Penerapan Kode Etik dalam Menjalankan Tugas dan Peran sebagai Kepala Sekolah | Level HOTS: C4 – Analisis | Pendekatan: Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengamati seluruh informasi yang berkaitan dengan proses penetapan peserta pelatihan serta mengarahkan keputusan yang sesuai ketentuan.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menggali kesesuaian setiap calon dengan persyaratan yang berlaku serta mengarahkan proses seleksi yang lebih objektif.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Menerapkan prinsip profesional dan transparansi dalam menetapkan peserta pelatihan serta mengarahkan kepercayaan warga sekolah.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menganalisis potensi konflik kepentingan dalam proses pengambilan keputusan serta mengarahkan penerapan kode etik secara konsisten.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Mengembangkan sistem tata kelola yang menjamin integritas, akuntabilitas, dan kepatuhan terhadap kode etik secara berkelanjutan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 1,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 8. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Kepala sekolah memperoleh laporan dugaan pelanggaran disiplin yang dilakukan oleh salah seorang guru senior. Di sisi lain, guru tersebut memiliki kontribusi besar terhadap kemajuan sekolah sehingga sebagian warga sekolah berharap masalah tersebut diselesaikan secara informal.',
                                    'nama_field' => 'soal_008',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Kepribadian | Indikator: 1.1 — Kematangan moral, emosi, dan spiritual dalam berperilaku sesuai dengan kode etik | Subindikator: 1.1.3 — Penerapan Kode Etik dalam Menjalankan Tugas dan Peran sebagai Kepala Sekolah | Level HOTS: C5 – Evaluasi | Pendekatan: Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mencermati informasi yang berkaitan dengan dugaan pelanggaran tersebut serta mengarahkan penanganan sesuai ketentuan.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menafsirkan dampak keputusan yang akan diambil terhadap warga sekolah serta mengarahkan proses yang adil dan objektif.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Menggunakan prosedur penyelesaian yang sesuai kode etik dan peraturan yang berlaku serta mengarahkan terciptanya keadilan organisasi.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menelaah kesesuaian proses penanganan dengan prinsip etika kepemimpinan sekolah serta mengarahkan penyempurnaan mekanisme yang diperlukan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Merekonstruksi budaya organisasi yang menjunjung integritas dan akuntabilitas sehingga kode etik menjadi bagian dari budaya kerja sekolah.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 2,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 9. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Sekolah sedang menyusun program penguatan budaya integritas yang melibatkan guru, tenaga kependidikan, peserta didik, komite sekolah, dan orang tua. Kepala sekolah bertanggung jawab memastikan seluruh kebijakan dan praktik sekolah mencerminkan nilai-nilai etika profesi.',
                                    'nama_field' => 'soal_009',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Kepribadian | Indikator: 1.1 — Kematangan moral, emosi, dan spiritual dalam berperilaku sesuai dengan kode etik | Subindikator: 1.1.3 — Penerapan Kode Etik dalam Menjalankan Tugas dan Peran sebagai Kepala Sekolah | Level HOTS: C6 – Kreasi | Pendekatan: Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengidentifikasi aspek-aspek tata kelola sekolah yang berkaitan dengan penerapan kode etik serta mengarahkan penguatan yang diperlukan.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menggambarkan peran seluruh warga sekolah dalam membangun budaya etis serta mengarahkan keterlibatan yang lebih luas.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Mengorganisasi pelaksanaan program penguatan integritas yang melibatkan seluruh warga sekolah serta mengarahkan implementasi yang konsisten.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Mengevaluasi efektivitas penerapan kode etik dalam berbagai aspek penyelenggaraan sekolah serta mengarahkan penyempurnaan yang diperlukan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Merancang sistem budaya integritas yang terintegrasi dalam tata kelola sekolah sehingga menjadi praktik profesional yang berkelanjutan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 3,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                            ],
                        ],
                        [
                            'judul_form' => '1.2.1 Refleksi dan Perencanaan Kebutuhan Pengembangan Diri untuk Peningkatan Kepemimpinan Satuan Pendidikan yang Berpusat pada Peserta Didik',
                            'deskripsi' => 'Kompetensi Kepribadian — Indikator 1.2: Pengembangan diri melalui kebiasaan refleksi.',
                            'kompetensi' => KompetensiGuru::KEPRIBADIAN->value,
                            'indikator_kode' => '1.2',
                            'indikator_label' => 'Pengembangan diri melalui kebiasaan refleksi',
                            'is_scoreable' => true,
                            'urutan' => 4,
                            'is_active' => true,
                            'fields' => [
                                [
                                    'label' => 'Soal 10. Sebagai kepala sekolah, tindakan yang paling tepat dilakukan untuk merencanakan pengembangan diri adalah ...',
                                    'deskripsi' => 'Hasil Evaluasi Diri Sekolah menunjukkan bahwa kualitas pembelajaran di beberapa kelas belum mengalami peningkatan yang signifikan meskipun berbagai program telah dilaksanakan. Kepala sekolah menyadari bahwa pola kepemimpinan yang diterapkan belum sepenuhnya mampu mendorong perubahan praktik pembelajaran di sekolah.',
                                    'nama_field' => 'soal_010',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Kepribadian | Indikator: 1.2 — Pengembangan diri melalui kebiasaan refleksi | Subindikator: 1.2.1 — Refleksi dan Perencanaan Kebutuhan Pengembangan Diri untuk Peningkatan Kepemimpinan Satuan Pendidikan yang Berpusat pada Peserta Didik | Level HOTS: C4 – Analisis | Pendekatan: Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengamati hasil evaluasi sekolah yang berkaitan dengan mutu pembelajaran serta mengarahkan identifikasi kebutuhan peningkatan kepemimpinan.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menggali hubungan antara hasil evaluasi dan kompetensi kepemimpinan yang perlu diperkuat serta mengarahkan penyusunan kebutuhan pengembangan diri.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Memanfaatkan hasil refleksi sebagai dasar menyusun rencana pengembangan kepemimpinan yang mendukung peningkatan pembelajaran peserta didik.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menganalisis akar penyebab belum optimalnya kepemimpinan sekolah berdasarkan berbagai sumber data serta mengarahkan prioritas pengembangan diri.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Mengembangkan sistem refleksi berbasis data yang secara berkelanjutan menjadi dasar peningkatan kualitas kepemimpinan sekolah.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 1,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 11. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Kepala sekolah telah mengikuti berbagai pelatihan kepemimpinan selama beberapa tahun terakhir. Namun, hasil supervisi akademik menunjukkan bahwa perubahan praktik pembelajaran guru masih belum konsisten.',
                                    'nama_field' => 'soal_011',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Kepribadian | Indikator: 1.2 — Pengembangan diri melalui kebiasaan refleksi | Subindikator: 1.2.1 — Refleksi dan Perencanaan Kebutuhan Pengembangan Diri untuk Peningkatan Kepemimpinan Satuan Pendidikan yang Berpusat pada Peserta Didik | Level HOTS: C5 – Evaluasi | Pendekatan: Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mencermati hubungan antara pelatihan yang diikuti dan perubahan yang terjadi di sekolah serta mengarahkan refleksi yang diperlukan.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menafsirkan kebutuhan pengembangan kepemimpinan berdasarkan hasil supervisi akademik serta mengarahkan penyusunan rencana yang lebih relevan.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Menggunakan hasil refleksi untuk menentukan prioritas pengembangan kepemimpinan yang berdampak pada peningkatan pembelajaran.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menelaah efektivitas berbagai kegiatan pengembangan diri terhadap peningkatan mutu sekolah serta mengarahkan penyempurnaan yang diperlukan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Merekonstruksi strategi pengembangan kepemimpinan berbasis kebutuhan nyata sekolah dan perkembangan peserta didik secara berkelanjutan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 2,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 12. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Sekolah sedang menyusun Rencana Kerja Tahunan. Kepala sekolah ingin memastikan bahwa setiap program pengembangan dirinya memiliki keterkaitan langsung dengan peningkatan mutu pembelajaran dan perkembangan peserta didik.',
                                    'nama_field' => 'soal_012',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Kepribadian | Indikator: 1.2 — Pengembangan diri melalui kebiasaan refleksi | Subindikator: 1.2.1 — Refleksi dan Perencanaan Kebutuhan Pengembangan Diri untuk Peningkatan Kepemimpinan Satuan Pendidikan yang Berpusat pada Peserta Didik | Level HOTS: C6 – Kreasi | Pendekatan: Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengidentifikasi kebutuhan pengembangan kepemimpinan berdasarkan kondisi sekolah serta mengarahkan penyusunan prioritas yang sesuai.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menggambarkan hubungan antara kebutuhan pengembangan diri dan peningkatan mutu sekolah serta mengarahkan penyusunan program yang relevan.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Mengorganisasi rencana pengembangan diri yang selaras dengan kebutuhan sekolah serta mengarahkan implementasi secara bertahap.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Mengevaluasi kesesuaian rencana pengembangan diri terhadap tujuan peningkatan mutu sekolah serta mengarahkan penyempurnaan yang diperlukan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Merancang sistem pengembangan kepemimpinan berbasis refleksi yang terintegrasi dengan peningkatan mutu satuan pendidikan secara berkelanjutan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 3,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                            ],
                        ],
                        [
                            'judul_form' => '1.2.2 Cara Adaptif Melakukan Pengembangan Diri untuk Meningkatkan Kepemimpinan Satuan Pendidikan yang Berpusat pada Peserta Didik',
                            'deskripsi' => 'Kompetensi Kepribadian — Indikator 1.2: Pengembangan diri melalui kebiasaan refleksi.',
                            'kompetensi' => KompetensiGuru::KEPRIBADIAN->value,
                            'indikator_kode' => '1.2',
                            'indikator_label' => 'Pengembangan diri melalui kebiasaan refleksi',
                            'is_scoreable' => true,
                            'urutan' => 5,
                            'is_active' => true,
                            'fields' => [
                                [
                                    'label' => 'Soal 13. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Perubahan kebijakan pendidikan, perkembangan teknologi, dan meningkatnya kebutuhan belajar peserta didik menuntut kepala sekolah terus memperbarui kompetensi kepemimpinannya. Namun, kesempatan mengikuti pelatihan formal sangat terbatas.',
                                    'nama_field' => 'soal_013',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Kepribadian | Indikator: 1.2 — Pengembangan diri melalui kebiasaan refleksi | Subindikator: 1.2.2 — Cara Adaptif Melakukan Pengembangan Diri untuk Meningkatkan Kepemimpinan Satuan Pendidikan yang Berpusat pada Peserta Didik | Level HOTS: C4 – Analisis | Pendekatan: Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengamati berbagai peluang belajar profesional yang tersedia serta mengarahkan pemanfaatannya sesuai kebutuhan sekolah.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menguraikan berbagai alternatif pengembangan diri yang dapat dilakukan secara mandiri maupun kolaboratif serta mengarahkan pemilihan yang relevan.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Memanfaatkan berbagai sumber belajar profesional secara adaptif untuk meningkatkan kualitas kepemimpinan sekolah.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menganalisis efektivitas berbagai strategi pengembangan diri terhadap peningkatan mutu kepemimpinan serta mengarahkan pemilihan yang lebih tepat.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Mengembangkan budaya belajar sepanjang hayat yang memungkinkan kepemimpinan sekolah terus berkembang sesuai perubahan pendidikan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 1,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 14. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Kepala sekolah memperoleh berbagai kesempatan belajar melalui komunitas kepala sekolah, webinar, coaching, studi banding, dan pendampingan. Waktu yang tersedia terbatas sehingga diperlukan prioritas dalam memilih kegiatan yang paling berdampak.',
                                    'nama_field' => 'soal_014',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Kepribadian | Indikator: 1.2 — Pengembangan diri melalui kebiasaan refleksi | Subindikator: 1.2.2 — Cara Adaptif Melakukan Pengembangan Diri untuk Meningkatkan Kepemimpinan Satuan Pendidikan yang Berpusat pada Peserta Didik | Level HOTS: C5 – Evaluasi | Pendekatan: Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengenali berbagai peluang pengembangan profesional yang tersedia serta mengarahkan pemilihan sesuai kebutuhan sekolah.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menafsirkan manfaat setiap bentuk pengembangan diri terhadap peningkatan kepemimpinan sekolah serta mengarahkan prioritas yang tepat.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Menggunakan kegiatan pengembangan diri yang paling relevan dengan tantangan sekolah serta mengarahkan peningkatan mutu pembelajaran.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menilai dampak setiap alternatif pengembangan diri terhadap perubahan kinerja sekolah serta mengarahkan keputusan yang lebih efektif.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Mengintegrasikan berbagai bentuk pengembangan profesional menjadi strategi pembelajaran kepemimpinan yang berkelanjutan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 2,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 15. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Sekolah ingin membangun budaya belajar organisasi, di mana kepala sekolah, guru, dan tenaga kependidikan terus mengembangkan kompetensinya melalui berbagai jejaring profesional.',
                                    'nama_field' => 'soal_015',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Kepribadian | Indikator: 1.2 — Pengembangan diri melalui kebiasaan refleksi | Subindikator: 1.2.2 — Cara Adaptif Melakukan Pengembangan Diri untuk Meningkatkan Kepemimpinan Satuan Pendidikan yang Berpusat pada Peserta Didik | Level HOTS: C6 – Kreasi | Pendekatan: Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengidentifikasi kebutuhan belajar profesional warga sekolah serta mengarahkan pemanfaatan berbagai sumber pengembangan diri.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menggambarkan strategi pengembangan profesional yang sesuai dengan kebutuhan sekolah serta mengarahkan pelaksanaannya secara bertahap.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Mengorganisasi berbagai kegiatan belajar profesional yang mendukung peningkatan mutu sekolah serta mengarahkan keterlibatan seluruh warga sekolah.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Mengkaji efektivitas berbagai program pengembangan profesional terhadap perubahan budaya belajar organisasi serta mengarahkan penyempurnaan yang diperlukan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Merancang ekosistem pembelajaran organisasi yang adaptif terhadap perubahan dan berorientasi pada peningkatan mutu sekolah secara berkelanjutan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 3,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                            ],
                        ],
                        [
                            'judul_form' => '1.2.3 Penerapan Hasil Pengembangan Diri yang Berkelanjutan untuk Perbaikan Kualitas Kepemimpinan Satuan Pendidikan',
                            'deskripsi' => 'Kompetensi Kepribadian — Indikator 1.2: Pengembangan diri melalui kebiasaan refleksi.',
                            'kompetensi' => KompetensiGuru::KEPRIBADIAN->value,
                            'indikator_kode' => '1.2',
                            'indikator_label' => 'Pengembangan diri melalui kebiasaan refleksi',
                            'is_scoreable' => true,
                            'urutan' => 6,
                            'is_active' => true,
                            'fields' => [
                                [
                                    'label' => 'Soal 16. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Setelah mengikuti pelatihan kepemimpinan pembelajaran, kepala sekolah memperoleh berbagai strategi baru untuk meningkatkan kualitas supervisi akademik. Namun, sebagian besar strategi tersebut belum diterapkan secara konsisten di sekolah.',
                                    'nama_field' => 'soal_016',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Kepribadian | Indikator: 1.2 — Pengembangan diri melalui kebiasaan refleksi | Subindikator: 1.2.3 — Penerapan Hasil Pengembangan Diri yang Berkelanjutan untuk Perbaikan Kualitas Kepemimpinan Satuan Pendidikan | Level HOTS: C4 – Analisis | Pendekatan: Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengamati peluang penerapan hasil pelatihan dalam pengelolaan sekolah serta mengarahkan pemanfaatannya sesuai kebutuhan.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menggali keterkaitan hasil pelatihan dengan tantangan kepemimpinan sekolah serta mengarahkan penerapan yang relevan.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Menerapkan hasil pengembangan diri dalam praktik kepemimpinan sekolah serta mengarahkan peningkatan mutu pembelajaran.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menganalisis efektivitas penerapan hasil pengembangan diri terhadap perubahan kinerja sekolah serta mengarahkan penyempurnaan yang diperlukan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Mengembangkan sistem implementasi hasil pengembangan diri yang terintegrasi dengan peningkatan mutu sekolah secara berkelanjutan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 1,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 17. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Selama satu tahun terakhir kepala sekolah telah mengikuti beberapa program peningkatan kompetensi. Setelah dilakukan evaluasi, hanya sebagian kecil hasil pelatihan yang berdampak nyata terhadap peningkatan kualitas pembelajaran di sekolah.',
                                    'nama_field' => 'soal_017',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Kepribadian | Indikator: 1.2 — Pengembangan diri melalui kebiasaan refleksi | Subindikator: 1.2.3 — Penerapan Hasil Pengembangan Diri yang Berkelanjutan untuk Perbaikan Kualitas Kepemimpinan Satuan Pendidikan | Level HOTS: C5 – Evaluasi | Pendekatan: Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mencermati hasil penerapan berbagai program pengembangan diri di sekolah serta mengarahkan refleksi terhadap perubahan yang terjadi.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menafsirkan faktor-faktor yang memengaruhi keberhasilan penerapan hasil pengembangan diri serta mengarahkan perbaikan yang diperlukan.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Menggunakan hasil evaluasi sebagai dasar penyempurnaan praktik kepemimpinan sekolah serta mengarahkan peningkatan mutu pembelajaran.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menelaah dampak berbagai inovasi kepemimpinan terhadap perkembangan sekolah serta mengarahkan tindak lanjut yang lebih efektif.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Merekonstruksi sistem implementasi pengembangan profesional yang menghasilkan perubahan nyata pada budaya dan mutu sekolah.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 2,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 18. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Sekolah sedang menjalankan program transformasi pembelajaran selama tiga tahun ke depan. Kepala sekolah ingin memastikan bahwa setiap hasil pengembangan profesional yang diperoleh dapat diterapkan secara sistematis dan menjadi budaya kerja sekolah.',
                                    'nama_field' => 'soal_018',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Kepribadian | Indikator: 1.2 — Pengembangan diri melalui kebiasaan refleksi | Subindikator: 1.2.3 — Penerapan Hasil Pengembangan Diri yang Berkelanjutan untuk Perbaikan Kualitas Kepemimpinan Satuan Pendidikan | Level HOTS: C6 – Kreasi | Pendekatan: Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengidentifikasi hasil pengembangan profesional yang relevan dengan kebutuhan sekolah serta mengarahkan penerapannya secara bertahap.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menjelaskan peluang penerapan hasil pengembangan diri kepada seluruh warga sekolah serta mengarahkan pemanfaatannya secara konsisten.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Mengorganisasi implementasi hasil pengembangan profesional dalam berbagai program sekolah serta mengarahkan peningkatan kualitas layanan pendidikan.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Mengevaluasi keberhasilan penerapan hasil pengembangan diri terhadap mutu sekolah serta mengarahkan penyempurnaan berkelanjutan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Merancang sistem pembelajaran organisasi yang menjadikan hasil pengembangan profesional sebagai budaya peningkatan mutu sekolah secara berkelanjutan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 3,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                            ],
                        ],
                        [
                            'judul_form' => '1.3.1 Empati terhadap Peserta Didik dalam Pengambilan Keputusan',
                            'deskripsi' => 'Kompetensi Kepribadian — Indikator 1.3: Orientasi berpusat pada peserta didik.',
                            'kompetensi' => KompetensiGuru::KEPRIBADIAN->value,
                            'indikator_kode' => '1.3',
                            'indikator_label' => 'Orientasi berpusat pada peserta didik',
                            'is_scoreable' => true,
                            'urutan' => 7,
                            'is_active' => true,
                            'fields' => [
                                [
                                    'label' => 'Soal 19. Sebagai kepala sekolah, tindakan yang paling tepat dilakukan adalah ...',
                                    'deskripsi' => 'Sekolah berencana menghapus program bantuan belajar sore karena keterbatasan anggaran. Namun, hasil identifikasi menunjukkan bahwa sebagian besar peserta program merupakan peserta didik yang mengalami kesulitan belajar dan berasal dari keluarga dengan keterbatasan ekonomi. Kepala sekolah harus menentukan keputusan yang mempertimbangkan kondisi tersebut.',
                                    'nama_field' => 'soal_019',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Kepribadian | Indikator: 1.3 — Orientasi berpusat pada peserta didik | Subindikator: 1.3.1 — Empati terhadap Peserta Didik dalam Pengambilan Keputusan | Level HOTS: C4 – Analisis | Pendekatan: Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengamati dampak rencana kebijakan terhadap kondisi peserta didik serta mengarahkan pertimbangan yang lebih berorientasi pada kebutuhan mereka.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menggali informasi mengenai kebutuhan peserta didik yang terdampak serta mengarahkan penyusunan alternatif keputusan yang lebih tepat.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Mempertimbangkan aspirasi dan kebutuhan peserta didik dalam menetapkan kebijakan sekolah serta mengarahkan keputusan yang lebih berpihak.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menganalisis konsekuensi jangka pendek dan jangka panjang dari setiap alternatif keputusan terhadap perkembangan peserta didik.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Mengembangkan mekanisme pengambilan keputusan berbasis kepentingan terbaik peserta didik yang diterapkan secara konsisten di sekolah.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 1,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 20. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Dalam evaluasi program sekolah, beberapa peserta didik menyampaikan bahwa sebagian kebijakan sekolah belum mempertimbangkan pengalaman dan kebutuhan mereka sebagai pengguna layanan pendidikan. Kepala sekolah ingin memperbaiki proses pengambilan keputusan agar lebih responsif terhadap suara peserta didik.',
                                    'nama_field' => 'soal_020',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Kepribadian | Indikator: 1.3 — Orientasi berpusat pada peserta didik | Subindikator: 1.3.1 — Empati terhadap Peserta Didik dalam Pengambilan Keputusan | Level HOTS: C5 – Evaluasi | Pendekatan: Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengenali berbagai pengalaman peserta didik terhadap kebijakan sekolah serta mengarahkan penyempurnaan proses pengambilan keputusan.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menafsirkan kebutuhan dan harapan peserta didik terhadap layanan sekolah serta mengarahkan perbaikan kebijakan yang relevan.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Memanfaatkan masukan peserta didik sebagai salah satu dasar dalam penyusunan kebijakan sekolah serta mengarahkan keputusan yang lebih responsif.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menelaah efektivitas mekanisme pengambilan keputusan dalam memenuhi kebutuhan peserta didik serta mengarahkan penyempurnaan yang diperlukan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Merekonstruksi tata kelola pengambilan keputusan yang mengintegrasikan suara peserta didik sebagai bagian dari budaya sekolah.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 2,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 21. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Sekolah akan menyusun kebijakan baru mengenai pengembangan budaya belajar. Kepala sekolah ingin memastikan bahwa seluruh kebijakan yang dihasilkan benar-benar mencerminkan kebutuhan belajar peserta didik yang beragam.',
                                    'nama_field' => 'soal_021',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Kepribadian | Indikator: 1.3 — Orientasi berpusat pada peserta didik | Subindikator: 1.3.1 — Empati terhadap Peserta Didik dalam Pengambilan Keputusan | Level HOTS: C6 – Kreasi | Pendekatan: Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengidentifikasi kebutuhan belajar peserta didik yang menjadi dasar penyusunan kebijakan sekolah serta mengarahkan proses yang lebih partisipatif.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menggambarkan hubungan antara kebutuhan peserta didik dan kebijakan sekolah serta mengarahkan penyusunan program yang lebih relevan.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Mengorganisasi proses penyusunan kebijakan yang melibatkan berbagai informasi tentang kebutuhan peserta didik serta mengarahkan implementasi yang lebih tepat.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Mengevaluasi kesesuaian kebijakan sekolah terhadap perkembangan dan kebutuhan peserta didik serta mengarahkan penyempurnaan yang diperlukan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Merancang sistem pengambilan keputusan yang secara konsisten menempatkan kepentingan terbaik peserta didik sebagai prioritas utama.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 3,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                            ],
                        ],
                        [
                            'judul_form' => '1.3.2 Respek terhadap Hak Peserta Didik dalam Menjalankan Peran sebagai Kepala Sekolah',
                            'deskripsi' => 'Kompetensi Kepribadian — Indikator 1.3: Orientasi berpusat pada peserta didik.',
                            'kompetensi' => KompetensiGuru::KEPRIBADIAN->value,
                            'indikator_kode' => '1.3',
                            'indikator_label' => 'Orientasi berpusat pada peserta didik',
                            'is_scoreable' => true,
                            'urutan' => 8,
                            'is_active' => true,
                            'fields' => [
                                [
                                    'label' => 'Soal 22. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Dalam penyusunan tata tertib baru, sekolah berencana menerapkan aturan yang membatasi seluruh bentuk penyampaian aspirasi peserta didik karena dianggap dapat mengganggu ketertiban sekolah. Sebagian guru mendukung usulan tersebut, sementara perwakilan peserta didik berharap tetap memiliki ruang untuk menyampaikan pendapat secara bertanggung jawab.',
                                    'nama_field' => 'soal_022',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Kepribadian | Indikator: 1.3 — Orientasi berpusat pada peserta didik | Subindikator: 1.3.2 — Respek terhadap Hak Peserta Didik dalam Menjalankan Peran sebagai Kepala Sekolah | Level HOTS: C4 – Analisis | Pendekatan: Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengamati berbagai kepentingan yang muncul dalam penyusunan tata tertib serta mengarahkan perlindungan terhadap hak peserta didik.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menggali keseimbangan antara ketertiban sekolah dan hak peserta didik untuk berpartisipasi serta mengarahkan penyusunan aturan yang lebih proporsional.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Memfasilitasi penyusunan tata tertib yang tetap menghormati hak peserta didik serta mengarahkan terciptanya budaya sekolah yang demokratis.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menganalisis dampak kebijakan sekolah terhadap pemenuhan hak peserta didik serta mengarahkan penyempurnaan regulasi yang diperlukan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Mengembangkan sistem tata kelola sekolah yang menjamin penghormatan terhadap hak peserta didik dalam seluruh kebijakan sekolah.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 1,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 23. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Hasil survei iklim sekolah menunjukkan bahwa sebagian peserta didik belum merasa memiliki kesempatan yang sama untuk mengikuti kegiatan sekolah sesuai minat dan potensinya. Kepala sekolah ingin memastikan bahwa seluruh kebijakan sekolah menghormati hak setiap peserta didik.',
                                    'nama_field' => 'soal_023',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Kepribadian | Indikator: 1.3 — Orientasi berpusat pada peserta didik | Subindikator: 1.3.2 — Respek terhadap Hak Peserta Didik dalam Menjalankan Peran sebagai Kepala Sekolah | Level HOTS: C5 – Evaluasi | Pendekatan: Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mencermati hasil survei mengenai pengalaman peserta didik di sekolah serta mengarahkan identifikasi kebutuhan perbaikan.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menafsirkan hubungan antara kebijakan sekolah dan terpenuhinya hak peserta didik serta mengarahkan penyesuaian yang diperlukan.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Menggunakan hasil survei sebagai dasar penyempurnaan kebijakan sekolah sehingga kesempatan belajar menjadi lebih setara.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menilai efektivitas kebijakan sekolah dalam menjamin hak peserta didik serta mengarahkan tindak lanjut yang lebih tepat.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Mengintegrasikan prinsip penghormatan terhadap hak peserta didik ke dalam seluruh proses penyelenggaraan pendidikan di sekolah.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 2,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 24. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Sekolah sedang menyusun program penguatan budaya sekolah yang menghargai keberagaman, partisipasi, dan perlindungan hak peserta didik. Kepala sekolah bertugas memastikan seluruh program dapat diterapkan secara konsisten.',
                                    'nama_field' => 'soal_024',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Kepribadian | Indikator: 1.3 — Orientasi berpusat pada peserta didik | Subindikator: 1.3.2 — Respek terhadap Hak Peserta Didik dalam Menjalankan Peran sebagai Kepala Sekolah | Level HOTS: C6 – Kreasi | Pendekatan: Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengidentifikasi aspek-aspek sekolah yang berkaitan dengan pemenuhan hak peserta didik serta mengarahkan penguatan yang diperlukan.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menguraikan bentuk perlindungan hak peserta didik dalam berbagai program sekolah serta mengarahkan pelaksanaan yang lebih konsisten.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Mengorganisasi pelaksanaan program sekolah yang menjamin penghormatan terhadap hak peserta didik serta mengarahkan keterlibatan seluruh warga sekolah.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Mengkaji efektivitas program sekolah dalam memenuhi hak peserta didik serta mengarahkan penyempurnaan yang berkelanjutan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Merancang sistem budaya sekolah yang memastikan hak peserta didik terlindungi dalam setiap kebijakan dan praktik pendidikan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 3,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                            ],
                        ],
                        [
                            'judul_form' => '1.3.3 Kepedulian terhadap Keselamatan dan Keamanan Peserta Didik sebagai Individu dan Kelompok dalam Menjalankan Peran sebagai Kepala Sekolah',
                            'deskripsi' => 'Kompetensi Kepribadian — Indikator 1.3: Orientasi berpusat pada peserta didik.',
                            'kompetensi' => KompetensiGuru::KEPRIBADIAN->value,
                            'indikator_kode' => '1.3',
                            'indikator_label' => 'Orientasi berpusat pada peserta didik',
                            'is_scoreable' => true,
                            'urutan' => 9,
                            'is_active' => true,
                            'fields' => [
                                [
                                    'label' => 'Soal 25. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Dalam satu semester terakhir terjadi beberapa insiden kecelakaan ringan di lingkungan sekolah, seperti peserta didik terpeleset di area yang licin dan cedera saat kegiatan olahraga. Selain itu, beberapa peserta didik menyampaikan kekhawatiran mengenai perundungan yang terjadi di luar pengawasan guru.',
                                    'nama_field' => 'soal_025',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Kepribadian | Indikator: 1.3 — Orientasi berpusat pada peserta didik | Subindikator: 1.3.3 — Kepedulian terhadap Keselamatan dan Keamanan Peserta Didik sebagai Individu dan Kelompok dalam Menjalankan Peran sebagai Kepala Sekolah | Level HOTS: C4 – Analisis | Pendekatan: Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengamati berbagai risiko keselamatan dan keamanan yang dialami peserta didik serta mengarahkan penanganan yang diperlukan.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menggali faktor-faktor penyebab munculnya berbagai risiko di lingkungan sekolah serta mengarahkan langkah pencegahan yang sesuai.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Mengoordinasikan upaya perlindungan peserta didik melalui kebijakan dan pengawasan sekolah serta mengarahkan terciptanya lingkungan belajar yang aman.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menganalisis efektivitas sistem perlindungan peserta didik di sekolah serta mengarahkan penyempurnaan tata kelola keselamatan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Mengembangkan budaya sekolah yang mengintegrasikan keselamatan fisik, psikologis, dan sosial sebagai prioritas utama penyelenggaraan pendidikan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 1,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 26. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Sekolah telah memiliki prosedur penanganan keadaan darurat dan pencegahan perundungan. Namun, hasil simulasi menunjukkan bahwa sebagian warga sekolah belum memahami perannya ketika menghadapi situasi darurat maupun pelaporan kasus kekerasan.',
                                    'nama_field' => 'soal_026',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Kepribadian | Indikator: 1.3 — Orientasi berpusat pada peserta didik | Subindikator: 1.3.3 — Kepedulian terhadap Keselamatan dan Keamanan Peserta Didik sebagai Individu dan Kelompok dalam Menjalankan Peran sebagai Kepala Sekolah | Level HOTS: C5 – Evaluasi | Pendekatan: Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengenali tingkat kesiapan warga sekolah dalam menerapkan prosedur keselamatan serta mengarahkan penguatan yang diperlukan.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menafsirkan hambatan yang memengaruhi efektivitas pelaksanaan prosedur keselamatan sekolah serta mengarahkan penyempurnaan yang sesuai.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Memfasilitasi penguatan kapasitas warga sekolah dalam menerapkan prosedur keselamatan serta mengarahkan perlindungan peserta didik.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menelaah efektivitas sistem perlindungan peserta didik berdasarkan hasil evaluasi dan simulasi serta mengarahkan perbaikan yang diperlukan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Merekonstruksi sistem manajemen keselamatan sekolah yang melibatkan seluruh warga sekolah dan mitra terkait secara berkelanjutan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 2,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 27. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Pemerintah daerah menetapkan seluruh sekolah untuk mengembangkan Program Sekolah Aman yang mencakup keselamatan fisik, keamanan digital, kesehatan mental, kesiapsiagaan bencana, dan perlindungan anak. Kepala sekolah bertanggung jawab merancang implementasi program tersebut di satuan pendidikannya.',
                                    'nama_field' => 'soal_027',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Kepribadian | Indikator: 1.3 — Orientasi berpusat pada peserta didik | Subindikator: 1.3.3 — Kepedulian terhadap Keselamatan dan Keamanan Peserta Didik sebagai Individu dan Kelompok dalam Menjalankan Peran sebagai Kepala Sekolah | Level HOTS: C6 – Kreasi | Pendekatan: Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengidentifikasi kebutuhan perlindungan peserta didik di lingkungan sekolah serta mengarahkan penyusunan program yang relevan.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menggambarkan keterkaitan berbagai aspek keselamatan peserta didik dengan program sekolah serta mengarahkan implementasi yang lebih terstruktur.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Mengorganisasi pelaksanaan Program Sekolah Aman bersama seluruh warga sekolah serta mengarahkan perlindungan peserta didik secara menyeluruh.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Mengevaluasi efektivitas pelaksanaan program keselamatan terhadap perlindungan peserta didik serta mengarahkan penyempurnaan yang diperlukan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Merancang sistem perlindungan peserta didik yang terintegrasi dalam tata kelola sekolah dan budaya organisasi secara berkelanjutan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 3,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                            ],
                        ],
                        [
                            'judul_form' => '2.1.1 Pemberdayaan Guru dan Tenaga Kependidikan untuk Peningkatan Kualitas Pembelajaran di Satuan Pendidikan',
                            'deskripsi' => 'Kompetensi Sosial — Indikator 2.1: Pemberdayaan warga satuan pendidikan untuk meningkatkan kualitas pembelajaran.',
                            'kompetensi' => KompetensiGuru::SOSIAL->value,
                            'indikator_kode' => '2.1',
                            'indikator_label' => 'Pemberdayaan warga satuan pendidikan untuk meningkatkan kualitas pembelajaran',
                            'is_scoreable' => true,
                            'urutan' => 10,
                            'is_active' => true,
                            'fields' => [
                                [
                                    'label' => 'Soal 28. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Hasil supervisi akademik menunjukkan bahwa sebagian guru masih menggunakan strategi pembelajaran yang kurang bervariasi sehingga keterlibatan peserta didik belum optimal. Di sisi lain, terdapat beberapa guru yang telah menerapkan praktik pembelajaran inovatif, tetapi belum pernah membagikan pengalamannya kepada rekan sejawat. Kepala sekolah ingin memberdayakan seluruh guru agar terjadi peningkatan kualitas pembelajaran secara merata.',
                                    'nama_field' => 'soal_028',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Sosial | Indikator: 2.1 — Pemberdayaan warga satuan pendidikan untuk meningkatkan kualitas pembelajaran | Subindikator: 2.1.1 — Pemberdayaan Guru dan Tenaga Kependidikan untuk Peningkatan Kualitas Pembelajaran di Satuan Pendidikan | Level HOTS: C4 – Analisis | Pendekatan: Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengamati potensi dan kebutuhan pengembangan guru di sekolah serta mengarahkan pemanfaatan sumber daya yang tersedia.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menggali kompetensi dan pengalaman guru yang dapat dikembangkan bersama serta mengarahkan kolaborasi yang lebih bermakna.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Memfasilitasi kegiatan belajar bersama antarguru untuk saling berbagi praktik pembelajaran serta mengarahkan peningkatan kompetensi secara kolektif.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menganalisis kebutuhan pengembangan profesional berdasarkan data supervisi dan kinerja guru serta mengarahkan program pemberdayaan yang lebih tepat.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Mengembangkan budaya belajar organisasi yang memberdayakan seluruh guru dan tenaga kependidikan sebagai penggerak peningkatan mutu pembelajaran.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 1,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 29. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Sekolah telah melaksanakan berbagai pelatihan internal bagi guru dan tenaga kependidikan. Namun, hasil evaluasi menunjukkan bahwa dampaknya terhadap perubahan praktik pembelajaran masih belum merata di seluruh kelas.',
                                    'nama_field' => 'soal_029',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Sosial | Indikator: 2.1 — Pemberdayaan warga satuan pendidikan untuk meningkatkan kualitas pembelajaran | Subindikator: 2.1.1 — Pemberdayaan Guru dan Tenaga Kependidikan untuk Peningkatan Kualitas Pembelajaran di Satuan Pendidikan | Level HOTS: C5 – Evaluasi | Pendekatan: Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mencermati hasil pelaksanaan program pengembangan yang telah dilakukan serta mengarahkan identifikasi peluang perbaikan.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menafsirkan faktor-faktor yang memengaruhi keberhasilan pemberdayaan guru dan tenaga kependidikan serta mengarahkan tindak lanjut yang sesuai.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Menggunakan hasil evaluasi sebagai dasar penyempurnaan program pengembangan profesional serta mengarahkan peningkatan kualitas pembelajaran.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menelaah efektivitas strategi pemberdayaan terhadap perubahan praktik pembelajaran di sekolah serta mengarahkan penyempurnaan yang diperlukan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Mengintegrasikan sistem pemberdayaan profesional yang berkelanjutan berdasarkan kebutuhan guru, tenaga kependidikan, dan perkembangan peserta didik.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 2,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 30. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Sekolah akan mengembangkan Program Sekolah Pembelajar yang melibatkan guru, tenaga kependidikan, dan tim manajemen sekolah sebagai komunitas belajar profesional. Kepala sekolah bertugas merancang sistem yang mampu menjaga keberlangsungan program tersebut.',
                                    'nama_field' => 'soal_030',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Sosial | Indikator: 2.1 — Pemberdayaan warga satuan pendidikan untuk meningkatkan kualitas pembelajaran | Subindikator: 2.1.1 — Pemberdayaan Guru dan Tenaga Kependidikan untuk Peningkatan Kualitas Pembelajaran di Satuan Pendidikan | Level HOTS: C6 – Kreasi | Pendekatan: Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengidentifikasi potensi kontribusi setiap warga sekolah dalam pengembangan program serta mengarahkan pembagian peran yang sesuai.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menggambarkan hubungan antara pemberdayaan warga sekolah dan peningkatan mutu pembelajaran serta mengarahkan pelaksanaan program yang terencana.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Mengorganisasi kolaborasi guru dan tenaga kependidikan dalam komunitas belajar profesional serta mengarahkan peningkatan kualitas pembelajaran.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Mengevaluasi efektivitas mekanisme pemberdayaan yang diterapkan terhadap peningkatan mutu sekolah serta mengarahkan penyempurnaan yang diperlukan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Merancang ekosistem pemberdayaan profesional yang menjadikan seluruh warga sekolah sebagai agen perubahan pembelajaran secara berkelanjutan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 3,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                            ],
                        ],
                        [
                            'judul_form' => '2.1.2 Pemberdayaan Orang Tua/Wali untuk Peningkatan Kualitas Pembelajaran di Satuan Pendidikan',
                            'deskripsi' => 'Kompetensi Sosial — Indikator 2.1: Pemberdayaan warga satuan pendidikan untuk meningkatkan kualitas pembelajaran.',
                            'kompetensi' => KompetensiGuru::SOSIAL->value,
                            'indikator_kode' => '2.1',
                            'indikator_label' => 'Pemberdayaan warga satuan pendidikan untuk meningkatkan kualitas pembelajaran',
                            'is_scoreable' => true,
                            'urutan' => 11,
                            'is_active' => true,
                            'fields' => [
                                [
                                    'label' => 'Soal 31. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Hasil evaluasi sekolah menunjukkan bahwa keterlibatan orang tua dalam mendukung pembelajaran peserta didik masih rendah. Sebagian besar komunikasi antara sekolah dan orang tua hanya terjadi ketika pembagian rapor atau ketika muncul masalah kedisiplinan peserta didik.',
                                    'nama_field' => 'soal_031',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Sosial | Indikator: 2.1 — Pemberdayaan warga satuan pendidikan untuk meningkatkan kualitas pembelajaran | Subindikator: 2.1.2 — Pemberdayaan Orang Tua/Wali untuk Peningkatan Kualitas Pembelajaran di Satuan Pendidikan | Level HOTS: C4 – Analisis | Pendekatan: Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengamati pola keterlibatan orang tua dalam kegiatan sekolah serta mengarahkan identifikasi kebutuhan penguatan kemitraan.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menggali faktor-faktor yang memengaruhi partisipasi orang tua dalam mendukung pembelajaran serta mengarahkan penyusunan strategi yang lebih tepat.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Memfasilitasi berbagai bentuk kemitraan sekolah dan orang tua untuk mendukung perkembangan belajar peserta didik secara berkelanjutan.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menganalisis efektivitas hubungan sekolah dan orang tua terhadap kualitas pembelajaran serta mengarahkan penyempurnaan program kemitraan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Mengembangkan sistem kolaborasi sekolah dan keluarga yang memberdayakan orang tua sebagai mitra strategis dalam pendidikan peserta didik.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 1,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 32. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Sekolah telah melaksanakan berbagai kegiatan yang melibatkan orang tua, seperti seminar parenting dan pertemuan rutin kelas. Namun, hasil evaluasi menunjukkan bahwa kegiatan tersebut belum memberikan dampak nyata terhadap peningkatan keterlibatan orang tua dalam mendukung proses belajar peserta didik di rumah.',
                                    'nama_field' => 'soal_032',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Sosial | Indikator: 2.1 — Pemberdayaan warga satuan pendidikan untuk meningkatkan kualitas pembelajaran | Subindikator: 2.1.2 — Pemberdayaan Orang Tua/Wali untuk Peningkatan Kualitas Pembelajaran di Satuan Pendidikan | Level HOTS: C5 – Evaluasi | Pendekatan: Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mencermati hasil pelaksanaan program kemitraan yang telah dilakukan serta mengarahkan identifikasi kebutuhan penyempurnaan.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menafsirkan hubungan antara bentuk kegiatan dan tingkat keterlibatan orang tua serta mengarahkan perbaikan pendekatan yang digunakan.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Menggunakan hasil evaluasi sebagai dasar pengembangan program kemitraan yang lebih mendukung pembelajaran peserta didik.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menelaah efektivitas berbagai strategi pemberdayaan orang tua terhadap kualitas pembelajaran serta mengarahkan inovasi yang diperlukan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Mengintegrasikan kemitraan sekolah, keluarga, dan komunitas dalam sistem pendampingan belajar peserta didik secara berkelanjutan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 2,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 33. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Sekolah sedang menyusun Program "Kemitraan Sekolah dan Keluarga" yang bertujuan meningkatkan keterlibatan orang tua dalam mendukung perkembangan akademik, karakter, dan kesejahteraan peserta didik. Kepala sekolah bertugas memastikan program tersebut dapat berjalan secara berkelanjutan.',
                                    'nama_field' => 'soal_033',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Sosial | Indikator: 2.1 — Pemberdayaan warga satuan pendidikan untuk meningkatkan kualitas pembelajaran | Subindikator: 2.1.2 — Pemberdayaan Orang Tua/Wali untuk Peningkatan Kualitas Pembelajaran di Satuan Pendidikan | Level HOTS: C6 – Kreasi | Pendekatan: Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengidentifikasi kebutuhan kolaborasi antara sekolah dan keluarga dalam mendukung pembelajaran serta mengarahkan penyusunan program yang sesuai.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menguraikan bentuk kontribusi orang tua dalam mendukung perkembangan peserta didik serta mengarahkan pembagian peran yang lebih jelas.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Mengorganisasi pelaksanaan program kemitraan yang melibatkan orang tua dalam berbagai kegiatan pembelajaran serta mengarahkan kolaborasi yang lebih efektif.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Mengkaji dampak program kemitraan terhadap kualitas layanan pendidikan dan perkembangan peserta didik serta mengarahkan penyempurnaan yang diperlukan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Merancang model kemitraan sekolah dan keluarga yang adaptif terhadap kebutuhan peserta didik serta mendukung peningkatan mutu sekolah secara berkelanjutan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 3,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                            ],
                        ],
                        [
                            'judul_form' => '2.2.1 Komunikasi Efektif dengan Warga Satuan Pendidikan yang Mengarah pada Peningkatan Kualitas Satuan Pendidikan',
                            'deskripsi' => 'Kompetensi Sosial — Indikator 2.2: Kolaborasi untuk peningkatan kualitas satuan pendidikan.',
                            'kompetensi' => KompetensiGuru::SOSIAL->value,
                            'indikator_kode' => '2.2',
                            'indikator_label' => 'Kolaborasi untuk peningkatan kualitas satuan pendidikan',
                            'is_scoreable' => true,
                            'urutan' => 12,
                            'is_active' => true,
                            'fields' => [
                                [
                                    'label' => 'Soal 34. Sebagai kepala sekolah, tindakan yang paling tepat dilakukan adalah ...',
                                    'deskripsi' => 'Hasil Evaluasi Diri Sekolah menunjukkan penurunan capaian literasi peserta didik. Namun, dalam rapat sekolah setiap unit kerja memiliki persepsi yang berbeda mengenai penyebab masalah dan prioritas perbaikannya. Akibatnya, berbagai program berjalan sendiri-sendiri tanpa arah yang sama.',
                                    'nama_field' => 'soal_034',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Sosial | Indikator: 2.2 — Kolaborasi untuk peningkatan kualitas satuan pendidikan | Subindikator: 2.2.1 — Komunikasi Efektif dengan Warga Satuan Pendidikan yang Mengarah pada Peningkatan Kualitas Satuan Pendidikan | Level HOTS: C4 – Analisis | Pendekatan: Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengamati berbagai pandangan warga sekolah mengenai permasalahan yang dihadapi serta mengarahkan penyamaan persepsi terhadap tujuan bersama.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menggali informasi dari setiap unsur warga sekolah mengenai akar permasalahan serta mengarahkan komunikasi yang lebih terbuka.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Memfasilitasi komunikasi kolaboratif antarwarga sekolah untuk menyusun prioritas perbaikan mutu serta mengarahkan kesepakatan bersama.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menganalisis efektivitas pola komunikasi organisasi terhadap pencapaian tujuan sekolah serta mengarahkan penyempurnaan mekanisme koordinasi.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Mengembangkan sistem komunikasi partisipatif yang memperkuat kolaborasi seluruh warga sekolah dalam meningkatkan mutu pendidikan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 1,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 35. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Sekolah telah melaksanakan rapat koordinasi secara rutin. Meskipun demikian, hasil evaluasi menunjukkan bahwa banyak informasi penting tidak dipahami secara sama oleh guru, tenaga kependidikan, dan tim manajemen sehingga pelaksanaan program sering mengalami perbedaan interpretasi.',
                                    'nama_field' => 'soal_035',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Sosial | Indikator: 2.2 — Kolaborasi untuk peningkatan kualitas satuan pendidikan | Subindikator: 2.2.1 — Komunikasi Efektif dengan Warga Satuan Pendidikan yang Mengarah pada Peningkatan Kualitas Satuan Pendidikan | Level HOTS: C5 – Evaluasi | Pendekatan: Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mencermati hambatan komunikasi yang terjadi dalam pelaksanaan program sekolah serta mengarahkan identifikasi kebutuhan perbaikan.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menafsirkan penyebab terjadinya perbedaan pemahaman antarwarga sekolah serta mengarahkan penyempurnaan alur komunikasi.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Menggunakan mekanisme komunikasi yang lebih sistematis dalam pelaksanaan program sekolah serta mengarahkan keselarasan pelaksanaan.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menelaah efektivitas sistem komunikasi organisasi terhadap kualitas pelaksanaan program sekolah serta mengarahkan penyempurnaan yang diperlukan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Mengintegrasikan berbagai saluran komunikasi menjadi sistem informasi sekolah yang mendukung pengambilan keputusan secara kolaboratif.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 2,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 36. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Sekolah sedang menjalankan program transformasi pembelajaran yang melibatkan guru, tenaga kependidikan, komite sekolah, dan peserta didik. Kepala sekolah perlu membangun komunikasi yang mampu menjaga komitmen seluruh pihak selama program berlangsung.',
                                    'nama_field' => 'soal_036',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Sosial | Indikator: 2.2 — Kolaborasi untuk peningkatan kualitas satuan pendidikan | Subindikator: 2.2.1 — Komunikasi Efektif dengan Warga Satuan Pendidikan yang Mengarah pada Peningkatan Kualitas Satuan Pendidikan | Level HOTS: C6 – Kreasi | Pendekatan: Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengidentifikasi kebutuhan komunikasi setiap kelompok warga sekolah serta mengarahkan penyampaian informasi yang lebih terencana.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menguraikan peran komunikasi dalam mendukung keberhasilan program sekolah serta mengarahkan keterlibatan setiap pihak.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Mengorganisasi komunikasi berkala yang melibatkan seluruh pemangku kepentingan sekolah serta mengarahkan keterlaksanaan program secara konsisten.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Mengevaluasi efektivitas strategi komunikasi terhadap keterlibatan warga sekolah serta mengarahkan penyempurnaan yang diperlukan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Merancang sistem komunikasi organisasi yang adaptif dan transparan untuk mendukung peningkatan mutu sekolah secara berkelanjutan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 3,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                            ],
                        ],
                        [
                            'judul_form' => '2.2.2 Pengorganisasian Tugas-Tugas Bersama Warga Satuan Pendidikan untuk Peningkatan Kualitas Satuan Pendidikan',
                            'deskripsi' => 'Kompetensi Sosial — Indikator 2.2: Kolaborasi untuk peningkatan kualitas satuan pendidikan.',
                            'kompetensi' => KompetensiGuru::SOSIAL->value,
                            'indikator_kode' => '2.2',
                            'indikator_label' => 'Kolaborasi untuk peningkatan kualitas satuan pendidikan',
                            'is_scoreable' => true,
                            'urutan' => 13,
                            'is_active' => true,
                            'fields' => [
                                [
                                    'label' => 'Soal 37. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Sekolah membentuk tim peningkatan mutu pembelajaran. Setelah beberapa bulan berjalan, sebagian kegiatan terlaksana kurang optimal karena pembagian tugas tidak jelas, beberapa anggota mengerjakan tugas yang sama, sedangkan tugas lain belum ditangani.',
                                    'nama_field' => 'soal_037',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Sosial | Indikator: 2.2 — Kolaborasi untuk peningkatan kualitas satuan pendidikan | Subindikator: 2.2.2 — Pengorganisasian Tugas-Tugas Bersama Warga Satuan Pendidikan untuk Peningkatan Kualitas Satuan Pendidikan | Level HOTS: C4 – Analisis | Pendekatan: Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengamati pelaksanaan pembagian tugas dalam tim serta mengarahkan penyesuaian tanggung jawab yang diperlukan.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menggali kesesuaian antara tugas, kompetensi, dan beban kerja anggota tim serta mengarahkan pembagian tugas yang lebih proporsional.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Mengoordinasikan pembagian tugas berdasarkan kompetensi dan tujuan program serta mengarahkan kolaborasi yang lebih efektif.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menganalisis efektivitas struktur kerja tim terhadap pencapaian program sekolah serta mengarahkan penyempurnaan mekanisme organisasi.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Mengembangkan sistem kerja kolaboratif yang mengoptimalkan kontribusi seluruh warga sekolah dalam peningkatan mutu pendidikan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 1,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 38. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Dalam pelaksanaan Program Sekolah Ramah Anak, beberapa unit kerja melaksanakan kegiatan yang saling tumpang tindih, sementara beberapa target program belum terlaksana karena belum ada koordinasi yang jelas.',
                                    'nama_field' => 'soal_038',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Sosial | Indikator: 2.2 — Kolaborasi untuk peningkatan kualitas satuan pendidikan | Subindikator: 2.2.2 — Pengorganisasian Tugas-Tugas Bersama Warga Satuan Pendidikan untuk Peningkatan Kualitas Satuan Pendidikan | Level HOTS: C5 – Evaluasi | Pendekatan: Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mencermati keterlaksanaan tugas setiap unit kerja dalam program sekolah serta mengarahkan identifikasi hambatan koordinasi.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menafsirkan hubungan antara pembagian tugas dan pencapaian target program serta mengarahkan penyempurnaan koordinasi.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Menggunakan mekanisme koordinasi yang memperjelas peran setiap unit kerja serta mengarahkan pencapaian tujuan program.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menilai efektivitas pengorganisasian tugas terhadap keberhasilan program sekolah serta mengarahkan penyempurnaan yang diperlukan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Mengintegrasikan sistem pengorganisasian lintas unit kerja yang adaptif untuk mendukung peningkatan kualitas satuan pendidikan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 2,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 39. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Sekolah akan melaksanakan program peningkatan mutu selama satu tahun yang melibatkan seluruh unsur sekolah. Kepala sekolah ingin memastikan bahwa setiap warga sekolah memahami perannya dan bekerja secara sinergis.',
                                    'nama_field' => 'soal_039',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Sosial | Indikator: 2.2 — Kolaborasi untuk peningkatan kualitas satuan pendidikan | Subindikator: 2.2.2 — Pengorganisasian Tugas-Tugas Bersama Warga Satuan Pendidikan untuk Peningkatan Kualitas Satuan Pendidikan | Level HOTS: C6 – Kreasi | Pendekatan: Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengidentifikasi kebutuhan pembagian tugas berdasarkan tujuan program sekolah serta mengarahkan penempatan peran yang sesuai.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menggambarkan hubungan antarperan dalam pelaksanaan program sekolah serta mengarahkan koordinasi yang lebih efektif.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Mengorganisasi struktur pelaksanaan program yang melibatkan seluruh warga sekolah serta mengarahkan kolaborasi yang berkelanjutan.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Mengkaji efektivitas pengorganisasian tugas terhadap peningkatan mutu sekolah serta mengarahkan penyempurnaan yang diperlukan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Merancang sistem kerja kolaboratif berbasis tanggung jawab bersama yang mendukung peningkatan kualitas satuan pendidikan secara berkelanjutan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 3,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                            ],
                        ],
                        [
                            'judul_form' => '2.2.3 Inisiatif Berkontribusi untuk Mencapai Tujuan Bersama dalam Peningkatan Kualitas Satuan Pendidikan',
                            'deskripsi' => 'Kompetensi Sosial — Indikator 2.2: Kolaborasi untuk peningkatan kualitas satuan pendidikan.',
                            'kompetensi' => KompetensiGuru::SOSIAL->value,
                            'indikator_kode' => '2.2',
                            'indikator_label' => 'Kolaborasi untuk peningkatan kualitas satuan pendidikan',
                            'is_scoreable' => true,
                            'urutan' => 14,
                            'is_active' => true,
                            'fields' => [
                                [
                                    'label' => 'Soal 40. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Sekolah memperoleh hasil akreditasi yang menunjukkan beberapa indikator mutu belum memenuhi target. Sebagian warga sekolah menunggu arahan sebelum melakukan perbaikan sehingga berbagai tindak lanjut berjalan lambat.',
                                    'nama_field' => 'soal_040',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Sosial | Indikator: 2.2 — Kolaborasi untuk peningkatan kualitas satuan pendidikan | Subindikator: 2.2.3 — Inisiatif Berkontribusi untuk Mencapai Tujuan Bersama dalam Peningkatan Kualitas Satuan Pendidikan | Level HOTS: C4 – Analisis | Pendekatan: Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengamati peluang perbaikan yang dapat dilakukan bersama warga sekolah serta mengarahkan keterlibatan sesuai peran masing-masing.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menggali potensi kontribusi setiap warga sekolah terhadap peningkatan mutu serta mengarahkan kolaborasi yang lebih aktif.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Menggerakkan warga sekolah untuk mengambil peran dalam pelaksanaan program peningkatan mutu serta mengarahkan pencapaian tujuan bersama.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menganalisis faktor-faktor yang memengaruhi rendahnya partisipasi warga sekolah serta mengarahkan strategi pemberdayaan yang lebih efektif.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Mengembangkan budaya kepemimpinan partisipatif yang mendorong seluruh warga sekolah berinisiatif meningkatkan kualitas pendidikan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 1,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 41. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Dalam pelaksanaan berbagai program sekolah, kepala sekolah melihat bahwa ide-ide inovatif dari guru dan tenaga kependidikan belum banyak diwujudkan menjadi program nyata karena belum ada mekanisme yang mendukung inisiatif bersama.',
                                    'nama_field' => 'soal_041',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Sosial | Indikator: 2.2 — Kolaborasi untuk peningkatan kualitas satuan pendidikan | Subindikator: 2.2.3 — Inisiatif Berkontribusi untuk Mencapai Tujuan Bersama dalam Peningkatan Kualitas Satuan Pendidikan | Level HOTS: C5 – Evaluasi | Pendekatan: Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengenali berbagai gagasan yang berkembang di lingkungan sekolah serta mengarahkan identifikasi peluang pengembangan.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menafsirkan faktor-faktor yang menghambat munculnya inisiatif warga sekolah serta mengarahkan perbaikan budaya kerja.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Memfasilitasi ruang kolaborasi bagi warga sekolah untuk mengembangkan berbagai inisiatif peningkatan mutu serta mengarahkan implementasi bersama.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menelaah efektivitas sistem organisasi dalam mendorong partisipasi warga sekolah serta mengarahkan penyempurnaan mekanisme yang diperlukan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Merekonstruksi budaya organisasi yang mendorong inovasi dan kepemimpinan bersama dalam meningkatkan kualitas satuan pendidikan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 2,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 42. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Sekolah akan melaksanakan program transformasi digital selama tiga tahun. Keberhasilan program sangat bergantung pada keterlibatan aktif seluruh warga sekolah dalam merancang, melaksanakan, mengevaluasi, dan menyempurnakan setiap tahap pelaksanaannya.',
                                    'nama_field' => 'soal_042',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Sosial | Indikator: 2.2 — Kolaborasi untuk peningkatan kualitas satuan pendidikan | Subindikator: 2.2.3 — Inisiatif Berkontribusi untuk Mencapai Tujuan Bersama dalam Peningkatan Kualitas Satuan Pendidikan | Level HOTS: C6 – Kreasi | Pendekatan: Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengidentifikasi peluang kontribusi seluruh warga sekolah dalam program transformasi serta mengarahkan pembagian peran yang sesuai.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menguraikan bentuk kontribusi yang dapat diberikan setiap unsur sekolah terhadap keberhasilan program serta mengarahkan kolaborasi yang lebih terencana.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Mengorganisasi keterlibatan seluruh warga sekolah dalam setiap tahapan program serta mengarahkan pencapaian tujuan secara kolektif.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Mengevaluasi efektivitas kontribusi setiap unsur sekolah terhadap keberhasilan program serta mengarahkan penyempurnaan yang diperlukan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Merancang budaya kolaboratif yang mendorong inisiatif, inovasi, dan tanggung jawab bersama dalam peningkatan kualitas satuan pendidikan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 3,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                            ],
                        ],
                        [
                            'judul_form' => '2.3.1 Berpartisipasi Aktif dalam Organisasi Profesi dan Jejaring yang Lebih Luas untuk Peningkatan Kualitas Kepemimpinan di Satuan Pendidikan',
                            'deskripsi' => 'Kompetensi Sosial — Indikator 2.3: Keterlibatan dalam organisasi profesi dan jejaring yang lebih luas untuk peningkatan kualitas satuan pendidikan.',
                            'kompetensi' => KompetensiGuru::SOSIAL->value,
                            'indikator_kode' => '2.3',
                            'indikator_label' => 'Keterlibatan dalam organisasi profesi dan jejaring yang lebih luas untuk peningkatan kualitas satuan pendidikan',
                            'is_scoreable' => true,
                            'urutan' => 15,
                            'is_active' => true,
                            'fields' => [
                                [
                                    'label' => 'Soal 43. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Kepala sekolah menghadapi tantangan dalam meningkatkan budaya literasi di sekolah. Di wilayahnya terdapat forum kepala sekolah dan jejaring komunitas belajar yang secara rutin membahas praktik kepemimpinan pembelajaran, namun selama ini sekolah belum aktif berpartisipasi dalam kegiatan tersebut.',
                                    'nama_field' => 'soal_043',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Sosial | Indikator: 2.3 — Keterlibatan dalam organisasi profesi dan jejaring yang lebih luas untuk peningkatan kualitas satuan pendidikan | Subindikator: 2.3.1 — Berpartisipasi Aktif dalam Organisasi Profesi dan Jejaring yang Lebih Luas untuk Peningkatan Kualitas Kepemimpinan di Satuan Pendidikan | Level HOTS: C4 – Analisis | Pendekatan: Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengamati peluang pembelajaran yang tersedia melalui organisasi profesi serta mengarahkan pemanfaatannya sesuai kebutuhan sekolah.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menggali manfaat berbagai jejaring profesional terhadap peningkatan kepemimpinan sekolah serta mengarahkan keterlibatan yang lebih terencana.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Berpartisipasi secara aktif dalam organisasi profesi untuk memperoleh dan menerapkan berbagai praktik kepemimpinan yang relevan bagi sekolah.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menganalisis kontribusi organisasi profesi terhadap peningkatan kualitas kepemimpinan sekolah serta mengarahkan kolaborasi yang lebih strategis.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Mengembangkan kemitraan profesional yang berkelanjutan melalui berbagai jejaring untuk memperkuat transformasi kepemimpinan satuan pendidikan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 1,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 44. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Selama beberapa tahun terakhir kepala sekolah rutin mengikuti kegiatan organisasi profesi. Namun, hasil evaluasi menunjukkan bahwa keterlibatan tersebut belum memberikan dampak nyata terhadap peningkatan mutu sekolah karena sebagian besar pengetahuan yang diperoleh belum ditindaklanjuti.',
                                    'nama_field' => 'soal_044',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Sosial | Indikator: 2.3 — Keterlibatan dalam organisasi profesi dan jejaring yang lebih luas untuk peningkatan kualitas satuan pendidikan | Subindikator: 2.3.1 — Berpartisipasi Aktif dalam Organisasi Profesi dan Jejaring yang Lebih Luas untuk Peningkatan Kualitas Kepemimpinan di Satuan Pendidikan | Level HOTS: C5 – Evaluasi | Pendekatan: Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mencermati hasil keterlibatan dalam organisasi profesi terhadap perkembangan sekolah serta mengarahkan refleksi yang diperlukan.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menafsirkan faktor-faktor yang memengaruhi pemanfaatan hasil jejaring profesional di sekolah serta mengarahkan tindak lanjut yang lebih relevan.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Menggunakan pengalaman dari organisasi profesi sebagai dasar penyempurnaan kepemimpinan sekolah serta mengarahkan perubahan yang terukur.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menelaah efektivitas partisipasi dalam organisasi profesi terhadap peningkatan mutu satuan pendidikan serta mengarahkan penyempurnaan strategi.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Mengintegrasikan hasil pembelajaran dari berbagai jejaring profesional ke dalam sistem pengembangan sekolah secara berkelanjutan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 2,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 45. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Sekolah akan menjadi bagian dari jejaring sekolah rujukan yang bekerja sama dengan berbagai organisasi profesi, perguruan tinggi, dan dunia usaha untuk mengembangkan inovasi pembelajaran dan kepemimpinan sekolah.',
                                    'nama_field' => 'soal_045',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Sosial | Indikator: 2.3 — Keterlibatan dalam organisasi profesi dan jejaring yang lebih luas untuk peningkatan kualitas satuan pendidikan | Subindikator: 2.3.1 — Berpartisipasi Aktif dalam Organisasi Profesi dan Jejaring yang Lebih Luas untuk Peningkatan Kualitas Kepemimpinan di Satuan Pendidikan | Level HOTS: C6 – Kreasi | Pendekatan: Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengidentifikasi peluang kerja sama yang mendukung pengembangan sekolah serta mengarahkan keterlibatan sesuai kebutuhan.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menggambarkan kontribusi berbagai jejaring profesional terhadap peningkatan mutu sekolah serta mengarahkan pemanfaatannya secara terencana.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Mengorganisasi keterlibatan sekolah dalam berbagai organisasi profesi dan jejaring strategis serta mengarahkan kolaborasi yang produktif.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Mengevaluasi efektivitas kemitraan profesional terhadap peningkatan kualitas kepemimpinan sekolah serta mengarahkan penyempurnaan yang diperlukan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Merancang sistem kolaborasi lintas jejaring yang berkelanjutan untuk mempercepat transformasi dan peningkatan mutu satuan pendidikan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 3,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                            ],
                        ],
                        [
                            'judul_form' => '2.3.2 Berbagi Praktik Baik dan Karya tentang Kepemimpinan Satuan Pendidikan untuk Peningkatan Kualitas Satuan Pendidikan yang Berpusat pada Peserta Didik',
                            'deskripsi' => 'Kompetensi Sosial — Indikator 2.3: Keterlibatan dalam organisasi profesi dan jejaring yang lebih luas untuk peningkatan kualitas satuan pendidikan.',
                            'kompetensi' => KompetensiGuru::SOSIAL->value,
                            'indikator_kode' => '2.3',
                            'indikator_label' => 'Keterlibatan dalam organisasi profesi dan jejaring yang lebih luas untuk peningkatan kualitas satuan pendidikan',
                            'is_scoreable' => true,
                            'urutan' => 16,
                            'is_active' => true,
                            'fields' => [
                                [
                                    'label' => 'Soal 46. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Sekolah berhasil meningkatkan hasil belajar peserta didik melalui program supervisi akademik yang inovatif. Beberapa sekolah lain meminta kepala sekolah untuk membagikan pengalaman tersebut dalam forum organisasi profesi, namun kepala sekolah belum pernah mendokumentasikan praktik baik yang telah dilakukan.',
                                    'nama_field' => 'soal_046',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Sosial | Indikator: 2.3 — Keterlibatan dalam organisasi profesi dan jejaring yang lebih luas untuk peningkatan kualitas satuan pendidikan | Subindikator: 2.3.2 — Berbagi Praktik Baik dan Karya tentang Kepemimpinan Satuan Pendidikan untuk Peningkatan Kualitas Satuan Pendidikan yang Berpusat pada Peserta Didik | Level HOTS: C4 – Analisis | Pendekatan: Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengamati berbagai praktik kepemimpinan yang telah memberikan dampak positif di sekolah serta mengarahkan pendokumentasian yang diperlukan.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menggali unsur-unsur keberhasilan praktik kepemimpinan sekolah serta mengarahkan penyusunan bahan berbagi pengalaman yang sistematis.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Menyajikan praktik baik kepemimpinan sekolah dalam forum profesional serta mengarahkan pertukaran pengalaman antarsekolah.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menganalisis faktor-faktor keberhasilan dan keterbatasan praktik baik sebelum dibagikan kepada jejaring profesional.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Mengembangkan model diseminasi praktik baik berbasis bukti yang dapat direplikasi untuk meningkatkan mutu satuan pendidikan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 1,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 47. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Kepala sekolah telah beberapa kali menjadi narasumber dalam forum organisasi profesi. Setelah dilakukan refleksi, sebagian besar materi yang disampaikan masih berupa paparan kegiatan dan belum menunjukkan bukti dampak terhadap peningkatan kualitas pembelajaran di sekolah.',
                                    'nama_field' => 'soal_047',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Sosial | Indikator: 2.3 — Keterlibatan dalam organisasi profesi dan jejaring yang lebih luas untuk peningkatan kualitas satuan pendidikan | Subindikator: 2.3.2 — Berbagi Praktik Baik dan Karya tentang Kepemimpinan Satuan Pendidikan untuk Peningkatan Kualitas Satuan Pendidikan yang Berpusat pada Peserta Didik | Level HOTS: C5 – Evaluasi | Pendekatan: Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengenali kekuatan dan kelemahan materi yang telah dibagikan kepada organisasi profesi serta mengarahkan penyempurnaan yang diperlukan.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menafsirkan kebutuhan peserta forum terhadap praktik kepemimpinan yang berbasis bukti serta mengarahkan penguatan materi berbagi.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Menggunakan data hasil implementasi sebagai dasar berbagi praktik baik kepemimpinan sekolah serta mengarahkan diskusi yang lebih bermakna.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menilai efektivitas praktik baik yang dibagikan terhadap peningkatan kapasitas sekolah lain serta mengarahkan penyempurnaan substansi.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Merekonstruksi strategi diseminasi praktik kepemimpinan berbasis data dan refleksi sehingga memberi dampak luas bagi peningkatan mutu pendidikan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 2,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 48. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Dinas Pendidikan membentuk jejaring praktik baik kepala sekolah yang bertujuan menghimpun berbagai inovasi kepemimpinan dari sekolah-sekolah untuk dijadikan referensi pengembangan mutu pendidikan di daerah. Kepala sekolah diminta berkontribusi secara aktif dalam program tersebut.',
                                    'nama_field' => 'soal_048',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Sosial | Indikator: 2.3 — Keterlibatan dalam organisasi profesi dan jejaring yang lebih luas untuk peningkatan kualitas satuan pendidikan | Subindikator: 2.3.2 — Berbagi Praktik Baik dan Karya tentang Kepemimpinan Satuan Pendidikan untuk Peningkatan Kualitas Satuan Pendidikan yang Berpusat pada Peserta Didik | Level HOTS: C6 – Kreasi | Pendekatan: Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengidentifikasi praktik kepemimpinan yang layak dibagikan kepada jejaring profesional serta mengarahkan penyusunan dokumentasinya.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menguraikan proses dan hasil inovasi kepemimpinan yang telah dilaksanakan di sekolah serta mengarahkan penyebarluasan pengetahuan.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Mengorganisasi proses dokumentasi dan diseminasi praktik baik kepada organisasi profesi serta mengarahkan pembelajaran bersama.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Mengkaji dampak praktik baik yang dibagikan terhadap peningkatan mutu sekolah lain serta mengarahkan penyempurnaan yang diperlukan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Merancang sistem berbagi pengetahuan dan inovasi kepemimpinan yang berkelanjutan melalui jejaring profesional untuk meningkatkan kualitas satuan pendidikan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 3,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                            ],
                        ],
                        [
                            'judul_form' => '3.1.1 Kepemimpinan Satuan Pendidikan dalam Mewujudkan Visi yang Berpusat pada Peserta Didik dengan Melibatkan Warga Satuan Pendidikan',
                            'deskripsi' => 'Kompetensi Profesional — Indikator 3.1: Pengembangan visi dan budaya belajar satuan pendidikan.',
                            'kompetensi' => KompetensiGuru::PROFESIONAL->value,
                            'indikator_kode' => '3.1',
                            'indikator_label' => 'Pengembangan visi dan budaya belajar satuan pendidikan',
                            'is_scoreable' => true,
                            'urutan' => 17,
                            'is_active' => true,
                            'fields' => [
                                [
                                    'label' => 'Soal 49. Sebagai kepala sekolah, tindakan yang paling tepat dilakukan adalah ...',
                                    'deskripsi' => 'Sekolah telah memiliki visi yang menekankan pembelajaran berpusat pada peserta didik. Namun, hasil evaluasi menunjukkan bahwa sebagian guru, tenaga kependidikan, dan komite sekolah masih memiliki pemahaman yang berbeda mengenai makna visi tersebut sehingga pelaksanaan berbagai program belum berjalan selaras.',
                                    'nama_field' => 'soal_049',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Profesional | Indikator: 3.1 — Pengembangan visi dan budaya belajar satuan pendidikan | Subindikator: 3.1.1 — Kepemimpinan Satuan Pendidikan dalam Mewujudkan Visi yang Berpusat pada Peserta Didik dengan Melibatkan Warga Satuan Pendidikan | Level HOTS: C4 – Analisis | Pendekatan: Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengamati tingkat pemahaman warga sekolah terhadap visi satuan pendidikan serta mengarahkan penyamaan persepsi secara bertahap.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menggali berbagai pandangan warga sekolah mengenai implementasi visi serta mengarahkan penyusunan langkah bersama yang lebih selaras.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Memfasilitasi keterlibatan seluruh warga sekolah dalam menerjemahkan visi ke dalam program pembelajaran serta mengarahkan pelaksanaan yang konsisten.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menganalisis keselarasan antara visi, budaya kerja, dan praktik pendidikan di sekolah serta mengarahkan penyempurnaan strategi implementasi.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Mengembangkan kepemimpinan partisipatif yang menjadikan visi sekolah sebagai budaya bersama dalam setiap pengambilan keputusan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 1,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 50. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Dalam rapat evaluasi tahunan, kepala sekolah menemukan bahwa berbagai program sekolah telah terlaksana dengan baik, tetapi sebagian besar belum memberikan dampak nyata terhadap pencapaian visi sekolah yang berpusat pada peserta didik.',
                                    'nama_field' => 'soal_050',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Profesional | Indikator: 3.1 — Pengembangan visi dan budaya belajar satuan pendidikan | Subindikator: 3.1.1 — Kepemimpinan Satuan Pendidikan dalam Mewujudkan Visi yang Berpusat pada Peserta Didik dengan Melibatkan Warga Satuan Pendidikan | Level HOTS: C5 – Evaluasi | Pendekatan: Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mencermati keterkaitan antara pelaksanaan program dan pencapaian visi sekolah serta mengarahkan identifikasi peluang perbaikan.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menafsirkan penyebab belum optimalnya implementasi visi dalam berbagai program sekolah serta mengarahkan penyempurnaan yang diperlukan.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Menggunakan hasil evaluasi sebagai dasar penyelarasan program dengan visi sekolah serta mengarahkan pelaksanaan yang lebih efektif.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menelaah efektivitas kepemimpinan dalam menggerakkan warga sekolah mencapai visi bersama serta mengarahkan penyempurnaan strategi.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Merekonstruksi mekanisme implementasi visi berbasis refleksi dan partisipasi warga sekolah untuk memperkuat budaya belajar.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 2,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 51. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Sekolah sedang menyusun Rencana Strategis lima tahunan. Kepala sekolah ingin memastikan bahwa visi sekolah tidak hanya menjadi dokumen administratif, tetapi benar-benar menjadi acuan dalam seluruh kebijakan, program, dan budaya kerja sekolah.',
                                    'nama_field' => 'soal_051',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Profesional | Indikator: 3.1 — Pengembangan visi dan budaya belajar satuan pendidikan | Subindikator: 3.1.1 — Kepemimpinan Satuan Pendidikan dalam Mewujudkan Visi yang Berpusat pada Peserta Didik dengan Melibatkan Warga Satuan Pendidikan | Level HOTS: C6 – Kreasi | Pendekatan: Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengidentifikasi keterkaitan antara visi sekolah dan kebutuhan peserta didik serta mengarahkan penyusunan program yang relevan.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menggambarkan peran setiap warga sekolah dalam mewujudkan visi bersama serta mengarahkan pembagian tanggung jawab yang jelas.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Mengorganisasi penyusunan program strategis yang selaras dengan visi sekolah serta mengarahkan implementasi secara kolaboratif.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Mengevaluasi kesesuaian seluruh program terhadap pencapaian visi sekolah serta mengarahkan penyempurnaan yang berkelanjutan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Merancang sistem kepemimpinan yang mengintegrasikan visi sekolah ke dalam budaya organisasi dan peningkatan mutu secara berkelanjutan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 3,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                            ],
                        ],
                        [
                            'judul_form' => '3.1.2 Pengembangan Kebiasaan Belajar sebagai Cerminan Visi Satuan Pendidikan yang Berpusat pada Peserta Didik',
                            'deskripsi' => 'Kompetensi Profesional — Indikator 3.1: Pengembangan visi dan budaya belajar satuan pendidikan.',
                            'kompetensi' => KompetensiGuru::PROFESIONAL->value,
                            'indikator_kode' => '3.1',
                            'indikator_label' => 'Pengembangan visi dan budaya belajar satuan pendidikan',
                            'is_scoreable' => true,
                            'urutan' => 18,
                            'is_active' => true,
                            'fields' => [
                                [
                                    'label' => 'Soal 52. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Sekolah memiliki visi membentuk peserta didik yang mandiri, bernalar kritis, dan gemar belajar. Namun, hasil observasi menunjukkan bahwa sebagian besar kegiatan sekolah masih berpusat pada penyampaian informasi oleh guru sehingga kebiasaan belajar mandiri peserta didik belum berkembang.',
                                    'nama_field' => 'soal_052',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Profesional | Indikator: 3.1 — Pengembangan visi dan budaya belajar satuan pendidikan | Subindikator: 3.1.2 — Pengembangan Kebiasaan Belajar sebagai Cerminan Visi Satuan Pendidikan yang Berpusat pada Peserta Didik | Level HOTS: C4 – Analisis | Pendekatan: Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengamati kebiasaan belajar peserta didik yang berkembang di sekolah serta mengarahkan identifikasi kebutuhan penguatan budaya belajar.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menggali faktor-faktor yang memengaruhi terbentuknya kebiasaan belajar peserta didik serta mengarahkan penyusunan strategi yang sesuai.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Memfasilitasi pengembangan berbagai kebiasaan belajar yang mencerminkan visi sekolah serta mengarahkan keterlibatan seluruh warga sekolah.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menganalisis hubungan antara budaya belajar dan pencapaian visi sekolah serta mengarahkan penyempurnaan program yang diperlukan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Mengembangkan budaya belajar sekolah yang konsisten mendukung tumbuhnya kemandirian, refleksi, dan pembelajaran sepanjang hayat.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 1,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 53. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Sekolah telah menjalankan berbagai program literasi, numerasi, dan pembelajaran berbasis proyek. Namun, hasil evaluasi menunjukkan bahwa kebiasaan belajar positif peserta didik belum berkembang secara konsisten di seluruh kelas.',
                                    'nama_field' => 'soal_053',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Profesional | Indikator: 3.1 — Pengembangan visi dan budaya belajar satuan pendidikan | Subindikator: 3.1.2 — Pengembangan Kebiasaan Belajar sebagai Cerminan Visi Satuan Pendidikan yang Berpusat pada Peserta Didik | Level HOTS: C5 – Evaluasi | Pendekatan: Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengenali variasi pelaksanaan budaya belajar di setiap kelas serta mengarahkan identifikasi kebutuhan perbaikan.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menafsirkan penyebab belum konsistennya kebiasaan belajar peserta didik di sekolah serta mengarahkan penguatan budaya belajar.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Menggunakan hasil evaluasi untuk memperkuat implementasi kebiasaan belajar yang selaras dengan visi sekolah serta mengarahkan tindak lanjut bersama.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menilai efektivitas berbagai program terhadap pembentukan budaya belajar peserta didik serta mengarahkan penyempurnaan yang diperlukan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Mengintegrasikan budaya belajar ke dalam seluruh aktivitas sekolah sehingga menjadi kebiasaan yang berkelanjutan bagi seluruh warga sekolah.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 2,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 54. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Sekolah akan melaksanakan Program Budaya Belajar selama tiga tahun yang melibatkan guru, peserta didik, orang tua, dan tenaga kependidikan. Program ini diharapkan mampu memperkuat implementasi visi sekolah dalam kehidupan sehari-hari.',
                                    'nama_field' => 'soal_054',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Profesional | Indikator: 3.1 — Pengembangan visi dan budaya belajar satuan pendidikan | Subindikator: 3.1.2 — Pengembangan Kebiasaan Belajar sebagai Cerminan Visi Satuan Pendidikan yang Berpusat pada Peserta Didik | Level HOTS: C6 – Kreasi | Pendekatan: Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengidentifikasi kebiasaan belajar yang perlu dikembangkan sesuai visi sekolah serta mengarahkan penyusunan program yang relevan.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menguraikan bentuk keterlibatan warga sekolah dalam membangun budaya belajar serta mengarahkan pelaksanaan yang lebih terstruktur.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Mengorganisasi pelaksanaan program budaya belajar yang melibatkan seluruh warga sekolah serta mengarahkan implementasi secara konsisten.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Mengkaji efektivitas pelaksanaan budaya belajar terhadap pencapaian visi sekolah serta mengarahkan penyempurnaan program.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Merancang ekosistem budaya belajar yang berkelanjutan dan menjadikan visi sekolah sebagai praktik nyata seluruh warga sekolah.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 3,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                            ],
                        ],
                        [
                            'judul_form' => '3.1.3 Pengelolaan Komunitas Belajar dalam Satuan Pendidikan yang Berbasis Data dengan Berorientasi pada Peningkatan Capaian Belajar Peserta Didik',
                            'deskripsi' => 'Kompetensi Profesional — Indikator 3.1: Pengembangan visi dan budaya belajar satuan pendidikan.',
                            'kompetensi' => KompetensiGuru::PROFESIONAL->value,
                            'indikator_kode' => '3.1',
                            'indikator_label' => 'Pengembangan visi dan budaya belajar satuan pendidikan',
                            'is_scoreable' => true,
                            'urutan' => 19,
                            'is_active' => true,
                            'fields' => [
                                [
                                    'label' => 'Soal 55. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Hasil asesmen sekolah menunjukkan penurunan kemampuan literasi peserta didik. Guru telah mengikuti berbagai pelatihan, namun diskusi dalam komunitas belajar sekolah masih didominasi oleh berbagi pengalaman tanpa menggunakan data hasil belajar sebagai dasar penyusunan tindak lanjut.',
                                    'nama_field' => 'soal_055',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Profesional | Indikator: 3.1 — Pengembangan visi dan budaya belajar satuan pendidikan | Subindikator: 3.1.3 — Pengelolaan Komunitas Belajar dalam Satuan Pendidikan yang Berbasis Data dengan Berorientasi pada Peningkatan Capaian Belajar Peserta Didik | Level HOTS: C4 – Analisis | Pendekatan: Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengamati pemanfaatan data dalam kegiatan komunitas belajar sekolah serta mengarahkan identifikasi kebutuhan perbaikan.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menggali hubungan antara data hasil belajar dan fokus diskusi komunitas belajar serta mengarahkan penggunaan data yang lebih tepat.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Memfasilitasi komunitas belajar yang menggunakan data capaian peserta didik sebagai dasar perbaikan pembelajaran serta mengarahkan tindak lanjut bersama.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menganalisis efektivitas komunitas belajar terhadap peningkatan kualitas pembelajaran berdasarkan berbagai sumber data sekolah.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Mengembangkan komunitas belajar berbasis data yang secara berkelanjutan menghasilkan inovasi untuk meningkatkan capaian belajar peserta didik.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 1,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 56. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Komunitas belajar guru telah berjalan selama satu tahun. Namun, hasil evaluasi menunjukkan bahwa sebagian besar kegiatan belum berdampak pada peningkatan hasil belajar peserta didik karena topik diskusi belum berdasarkan kebutuhan nyata yang teridentifikasi dari data sekolah.',
                                    'nama_field' => 'soal_056',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Profesional | Indikator: 3.1 — Pengembangan visi dan budaya belajar satuan pendidikan | Subindikator: 3.1.3 — Pengelolaan Komunitas Belajar dalam Satuan Pendidikan yang Berbasis Data dengan Berorientasi pada Peningkatan Capaian Belajar Peserta Didik | Level HOTS: C5 – Evaluasi | Pendekatan: Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mencermati keterkaitan kegiatan komunitas belajar dengan data hasil belajar peserta didik serta mengarahkan identifikasi peluang perbaikan.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menafsirkan penyebab belum optimalnya pemanfaatan data dalam komunitas belajar serta mengarahkan penyempurnaan fokus pembelajaran profesional.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Menggunakan data hasil belajar sebagai dasar penentuan agenda komunitas belajar serta mengarahkan peningkatan kualitas pembelajaran.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menelaah efektivitas komunitas belajar terhadap peningkatan capaian peserta didik berdasarkan bukti yang tersedia serta mengarahkan penyempurnaan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Merekonstruksi sistem komunitas belajar yang mengintegrasikan data, refleksi, dan inovasi pembelajaran secara berkelanjutan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 2,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 57. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Sekolah akan membangun komunitas belajar profesional yang melibatkan seluruh guru untuk meningkatkan hasil belajar peserta didik melalui siklus identifikasi masalah, analisis data, perencanaan tindakan, implementasi, dan refleksi secara berkelanjutan.',
                                    'nama_field' => 'soal_057',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Profesional | Indikator: 3.1 — Pengembangan visi dan budaya belajar satuan pendidikan | Subindikator: 3.1.3 — Pengelolaan Komunitas Belajar dalam Satuan Pendidikan yang Berbasis Data dengan Berorientasi pada Peningkatan Capaian Belajar Peserta Didik | Level HOTS: C6 – Kreasi | Pendekatan: Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengidentifikasi data yang diperlukan untuk mendukung kegiatan komunitas belajar serta mengarahkan pemanfaatannya secara bertahap.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menggambarkan proses pengelolaan komunitas belajar berbasis data serta mengarahkan pelaksanaan yang lebih sistematis.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Mengorganisasi siklus kerja komunitas belajar yang berorientasi pada peningkatan capaian belajar peserta didik serta mengarahkan kolaborasi profesional.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Mengevaluasi efektivitas komunitas belajar terhadap peningkatan mutu pembelajaran dan hasil belajar peserta didik serta mengarahkan penyempurnaan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Merancang sistem komunitas belajar profesional berbasis data yang menjadi budaya peningkatan mutu sekolah secara berkelanjutan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 3,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                            ],
                        ],
                        [
                            'judul_form' => '3.2.1 Kepemimpinan Pembelajaran dalam Membudayakan Lingkungan yang Aman, Nyaman, dan Inklusif untuk Warga Satuan Pendidikan',
                            'deskripsi' => 'Kompetensi Profesional — Indikator 3.2: Kepemimpinan pembelajaran yang berpusat pada peserta didik.',
                            'kompetensi' => KompetensiGuru::PROFESIONAL->value,
                            'indikator_kode' => '3.2',
                            'indikator_label' => 'Kepemimpinan pembelajaran yang berpusat pada peserta didik',
                            'is_scoreable' => true,
                            'urutan' => 20,
                            'is_active' => true,
                            'fields' => [
                                [
                                    'label' => 'Soal 58. Sebagai kepala sekolah, tindakan yang paling tepat dilakukan adalah ...',
                                    'deskripsi' => 'Hasil survei iklim sekolah menunjukkan bahwa sebagian peserta didik masih merasa kurang nyaman menyampaikan pendapat di kelas. Selain itu, beberapa peserta didik berkebutuhan khusus belum terlibat secara optimal dalam kegiatan pembelajaran. Kepala sekolah ingin membangun budaya sekolah yang lebih aman, nyaman, dan inklusif.',
                                    'nama_field' => 'soal_058',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Profesional | Indikator: 3.2 — Kepemimpinan pembelajaran yang berpusat pada peserta didik | Subindikator: 3.2.1 — Kepemimpinan Pembelajaran dalam Membudayakan Lingkungan yang Aman, Nyaman, dan Inklusif untuk Warga Satuan Pendidikan | Level HOTS: C4 – Analisis | Pendekatan: Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengamati kondisi lingkungan belajar yang dialami warga sekolah serta mengarahkan identifikasi kebutuhan perbaikan yang diperlukan.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menggali berbagai faktor yang memengaruhi rasa aman, nyaman, dan inklusif di sekolah serta mengarahkan penyusunan langkah perbaikan.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Memfasilitasi penguatan budaya sekolah yang mendukung rasa aman, nyaman, dan inklusif bagi seluruh warga sekolah serta mengarahkan implementasi secara konsisten.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menganalisis keterkaitan antara budaya sekolah, kepemimpinan pembelajaran, dan kesejahteraan warga sekolah serta mengarahkan penyempurnaan kebijakan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Mengembangkan sistem kepemimpinan pembelajaran yang menjadikan lingkungan aman, nyaman, dan inklusif sebagai budaya organisasi yang berkelanjutan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 1,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 59. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Sekolah telah memiliki berbagai kebijakan mengenai sekolah ramah anak dan pendidikan inklusif. Namun, hasil evaluasi menunjukkan bahwa penerapannya belum konsisten di seluruh kelas karena terdapat perbedaan pemahaman dan praktik di antara guru.',
                                    'nama_field' => 'soal_059',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Profesional | Indikator: 3.2 — Kepemimpinan pembelajaran yang berpusat pada peserta didik | Subindikator: 3.2.1 — Kepemimpinan Pembelajaran dalam Membudayakan Lingkungan yang Aman, Nyaman, dan Inklusif untuk Warga Satuan Pendidikan | Level HOTS: C5 – Evaluasi | Pendekatan: Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mencermati variasi pelaksanaan kebijakan di setiap kelas serta mengarahkan identifikasi kebutuhan penguatan yang diperlukan.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menafsirkan penyebab belum konsistennya implementasi budaya sekolah yang inklusif serta mengarahkan penyempurnaan pembinaan guru.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Menggunakan hasil evaluasi untuk memperkuat pendampingan guru dalam membangun lingkungan belajar yang aman dan inklusif.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menelaah efektivitas kepemimpinan pembelajaran terhadap implementasi budaya sekolah yang aman dan inklusif serta mengarahkan penyempurnaan strategi.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Mengintegrasikan pembinaan, supervisi, dan budaya organisasi dalam sistem kepemimpinan pembelajaran yang menjamin lingkungan belajar inklusif.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 2,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 60. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Sekolah akan melaksanakan Program Budaya Sekolah Inklusif selama tiga tahun yang melibatkan guru, tenaga kependidikan, peserta didik, orang tua, dan masyarakat. Kepala sekolah bertanggung jawab memastikan program tersebut menjadi bagian dari budaya sekolah.',
                                    'nama_field' => 'soal_060',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Profesional | Indikator: 3.2 — Kepemimpinan pembelajaran yang berpusat pada peserta didik | Subindikator: 3.2.1 — Kepemimpinan Pembelajaran dalam Membudayakan Lingkungan yang Aman, Nyaman, dan Inklusif untuk Warga Satuan Pendidikan | Level HOTS: C6 – Kreasi | Pendekatan: Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengidentifikasi kebutuhan pengembangan budaya sekolah yang aman dan inklusif serta mengarahkan penyusunan program yang sesuai.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menggambarkan peran seluruh warga sekolah dalam membangun lingkungan belajar yang inklusif serta mengarahkan pembagian tanggung jawab yang jelas.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Mengorganisasi pelaksanaan program budaya sekolah yang melibatkan seluruh warga sekolah serta mengarahkan implementasi secara berkelanjutan.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Mengevaluasi efektivitas program terhadap perubahan budaya sekolah dan keterlibatan warga sekolah serta mengarahkan penyempurnaan yang diperlukan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Merancang sistem kepemimpinan pembelajaran yang menumbuhkan budaya sekolah aman, nyaman, dan inklusif secara berkelanjutan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 3,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                            ],
                        ],
                        [
                            'judul_form' => '3.2.2 Kepemimpinan Pembelajaran dalam Perencanaan, Pelaksanaan, Asesmen, dan Pelaporan Capaian Belajar Peserta Didik dengan Memperhatikan Karakteristik Guru',
                            'deskripsi' => 'Kompetensi Profesional — Indikator 3.2: Kepemimpinan pembelajaran yang berpusat pada peserta didik.',
                            'kompetensi' => KompetensiGuru::PROFESIONAL->value,
                            'indikator_kode' => '3.2',
                            'indikator_label' => 'Kepemimpinan pembelajaran yang berpusat pada peserta didik',
                            'is_scoreable' => true,
                            'urutan' => 21,
                            'is_active' => true,
                            'fields' => [
                                [
                                    'label' => 'Soal 61. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Hasil supervisi akademik menunjukkan bahwa kemampuan guru dalam merancang pembelajaran, melaksanakan asesmen, dan menyusun laporan capaian belajar masih beragam. Sebagian guru telah menerapkan praktik yang baik, sementara guru lain masih memerlukan pendampingan sesuai kebutuhan kompetensinya.',
                                    'nama_field' => 'soal_061',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Profesional | Indikator: 3.2 — Kepemimpinan pembelajaran yang berpusat pada peserta didik | Subindikator: 3.2.2 — Kepemimpinan Pembelajaran dalam Perencanaan, Pelaksanaan, Asesmen, dan Pelaporan Capaian Belajar Peserta Didik dengan Memperhatikan Karakteristik Guru | Level HOTS: C4 – Analisis | Pendekatan: Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengamati variasi kompetensi guru dalam melaksanakan pembelajaran dan asesmen serta mengarahkan identifikasi kebutuhan pendampingan.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menggali karakteristik dan kebutuhan pengembangan setiap guru dalam kepemimpinan pembelajaran serta mengarahkan penyusunan pembinaan yang sesuai.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Memfasilitasi pendampingan guru berdasarkan karakteristik kompetensinya dalam perencanaan, pelaksanaan, asesmen, dan pelaporan pembelajaran.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menganalisis hubungan antara karakteristik guru dan kualitas pembelajaran peserta didik serta mengarahkan strategi pembinaan yang lebih efektif.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Mengembangkan sistem pembinaan diferensiatif yang mendukung peningkatan kompetensi guru sesuai kebutuhan profesionalnya secara berkelanjutan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 1,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 62. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Kepala sekolah telah melaksanakan supervisi akademik secara rutin. Namun, hasil evaluasi menunjukkan bahwa tindak lanjut supervisi belum memberikan perubahan yang merata karena pendekatan pembinaan yang digunakan masih sama untuk seluruh guru.',
                                    'nama_field' => 'soal_062',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Profesional | Indikator: 3.2 — Kepemimpinan pembelajaran yang berpusat pada peserta didik | Subindikator: 3.2.2 — Kepemimpinan Pembelajaran dalam Perencanaan, Pelaksanaan, Asesmen, dan Pelaporan Capaian Belajar Peserta Didik dengan Memperhatikan Karakteristik Guru | Level HOTS: C5 – Evaluasi | Pendekatan: Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengenali perbedaan kebutuhan pengembangan guru berdasarkan hasil supervisi serta mengarahkan penyusunan tindak lanjut yang lebih sesuai.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menafsirkan hubungan antara karakteristik guru dan efektivitas pembinaan yang telah dilakukan serta mengarahkan penyempurnaan strategi.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Menggunakan hasil supervisi sebagai dasar pembinaan yang disesuaikan dengan karakteristik guru serta mengarahkan peningkatan kualitas pembelajaran.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menilai efektivitas pendekatan pembinaan terhadap perubahan praktik pembelajaran guru serta mengarahkan penyempurnaan yang diperlukan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Merekonstruksi sistem supervisi akademik yang adaptif terhadap karakteristik guru dan berdampak pada peningkatan capaian belajar peserta didik.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 2,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 63. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Sekolah akan mengembangkan sistem supervisi akademik berbasis coaching yang mengintegrasikan perencanaan pembelajaran, observasi kelas, asesmen, refleksi, dan pelaporan perkembangan guru. Kepala sekolah bertugas merancang implementasinya agar sesuai dengan kebutuhan setiap guru.',
                                    'nama_field' => 'soal_063',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Profesional | Indikator: 3.2 — Kepemimpinan pembelajaran yang berpusat pada peserta didik | Subindikator: 3.2.2 — Kepemimpinan Pembelajaran dalam Perencanaan, Pelaksanaan, Asesmen, dan Pelaporan Capaian Belajar Peserta Didik dengan Memperhatikan Karakteristik Guru | Level HOTS: C6 – Kreasi | Pendekatan: Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengidentifikasi karakteristik guru yang menjadi dasar penyusunan sistem supervisi serta mengarahkan perencanaan yang relevan.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menguraikan kebutuhan pembinaan guru berdasarkan hasil supervisi akademik serta mengarahkan penyusunan program yang lebih sistematis.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Mengorganisasi pelaksanaan supervisi akademik yang memperhatikan karakteristik setiap guru serta mengarahkan peningkatan mutu pembelajaran.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Mengkaji efektivitas sistem supervisi terhadap peningkatan kompetensi guru dan capaian belajar peserta didik serta mengarahkan penyempurnaan yang diperlukan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Merancang sistem kepemimpinan pembelajaran berbasis supervisi diferensiatif yang mendukung peningkatan profesionalisme guru secara berkelanjutan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 3,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                            ],
                        ],
                        [
                            'judul_form' => '3.3.1 Penelusuran Sumber Daya Satuan Pendidikan yang Berasal dari Berbagai Sumber untuk Perencanaan dan Pelaksanaan Program',
                            'deskripsi' => 'Kompetensi Profesional — Indikator 3.3: Pengelolaan sumber daya satuan pendidikan secara efektif, transparan, dan akuntabel.',
                            'kompetensi' => KompetensiGuru::PROFESIONAL->value,
                            'indikator_kode' => '3.3',
                            'indikator_label' => 'Pengelolaan sumber daya satuan pendidikan secara efektif, transparan, dan akuntabel',
                            'is_scoreable' => true,
                            'urutan' => 22,
                            'is_active' => true,
                            'fields' => [
                                [
                                    'label' => 'Soal 64. Sebagai kepala sekolah, tindakan yang paling tepat dilakukan adalah ...',
                                    'deskripsi' => 'Sekolah berencana mengembangkan program peningkatan literasi peserta didik, tetapi anggaran yang tersedia dari dana operasional sekolah belum mencukupi. Di sisi lain, terdapat berbagai potensi dukungan dari komite sekolah, dunia usaha, alumni, perguruan tinggi, dan pemerintah daerah yang belum pernah dipetakan secara sistematis.',
                                    'nama_field' => 'soal_064',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Profesional | Indikator: 3.3 — Pengelolaan sumber daya satuan pendidikan secara efektif, transparan, dan akuntabel | Subindikator: 3.3.1 — Penelusuran Sumber Daya Satuan Pendidikan yang Berasal dari Berbagai Sumber untuk Perencanaan dan Pelaksanaan Program | Level HOTS: C4 – Analisis | Pendekatan: Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengamati berbagai potensi sumber daya yang tersedia di lingkungan sekolah serta mengarahkan identifikasi peluang pemanfaatannya.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menggali informasi mengenai berbagai sumber daya yang dapat mendukung program sekolah serta mengarahkan pemetaan yang lebih sistematis.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Memanfaatkan hasil pemetaan sumber daya sebagai dasar penyusunan program peningkatan pembelajaran peserta didik.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menganalisis relevansi, keberlanjutan, dan kontribusi setiap sumber daya terhadap prioritas program sekolah.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Mengembangkan sistem pemetaan sumber daya berbasis kebutuhan sekolah untuk mendukung pengambilan keputusan secara berkelanjutan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 1,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 65. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Selama ini sekolah lebih banyak mengandalkan satu sumber pendanaan dalam menjalankan program peningkatan mutu. Hasil evaluasi menunjukkan beberapa program penting tidak dapat dilaksanakan karena keterbatasan sumber daya yang tersedia.',
                                    'nama_field' => 'soal_065',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Profesional | Indikator: 3.3 — Pengelolaan sumber daya satuan pendidikan secara efektif, transparan, dan akuntabel | Subindikator: 3.3.1 — Penelusuran Sumber Daya Satuan Pendidikan yang Berasal dari Berbagai Sumber untuk Perencanaan dan Pelaksanaan Program | Level HOTS: C5 – Evaluasi | Pendekatan: Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mencermati pemanfaatan sumber daya yang telah digunakan sekolah serta mengarahkan identifikasi peluang pengembangan.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menafsirkan penyebab keterbatasan sumber daya terhadap pelaksanaan program sekolah serta mengarahkan pencarian alternatif yang sesuai.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Menggunakan hasil evaluasi untuk memperluas pemanfaatan berbagai sumber daya yang mendukung peningkatan mutu sekolah.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menelaah efektivitas strategi pemenuhan sumber daya terhadap pencapaian tujuan sekolah serta mengarahkan penyempurnaan yang diperlukan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Mengintegrasikan berbagai sumber daya internal dan eksternal dalam perencanaan sekolah yang adaptif dan berkelanjutan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 2,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 66. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Sekolah akan menjalankan program transformasi digital pembelajaran yang membutuhkan dukungan sarana, pendanaan, tenaga ahli, dan kemitraan dari berbagai pihak. Kepala sekolah diminta menyusun strategi pemenuhan sumber daya secara komprehensif.',
                                    'nama_field' => 'soal_066',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Profesional | Indikator: 3.3 — Pengelolaan sumber daya satuan pendidikan secara efektif, transparan, dan akuntabel | Subindikator: 3.3.1 — Penelusuran Sumber Daya Satuan Pendidikan yang Berasal dari Berbagai Sumber untuk Perencanaan dan Pelaksanaan Program | Level HOTS: C6 – Kreasi | Pendekatan: Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengidentifikasi berbagai sumber daya yang dapat dimanfaatkan untuk mendukung pelaksanaan program sekolah.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menggambarkan hubungan antara kebutuhan program dan potensi sumber daya yang tersedia serta mengarahkan penyusunannya secara sistematis.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Mengorganisasi pemanfaatan berbagai sumber daya sesuai kebutuhan program serta mengarahkan pelaksanaan yang lebih efektif.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Mengevaluasi kecukupan dan keberlanjutan sumber daya dalam mendukung transformasi sekolah serta mengarahkan penyempurnaan strategi.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Merancang sistem pengelolaan sumber daya berbasis kemitraan yang mendukung peningkatan mutu sekolah secara berkelanjutan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 3,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                            ],
                        ],
                        [
                            'judul_form' => '3.3.2 Pengelolaan Sumber Daya Satuan Pendidikan Secara Efektif untuk Peningkatan Pembelajaran Peserta Didik',
                            'deskripsi' => 'Kompetensi Profesional — Indikator 3.3: Pengelolaan sumber daya satuan pendidikan secara efektif, transparan, dan akuntabel.',
                            'kompetensi' => KompetensiGuru::PROFESIONAL->value,
                            'indikator_kode' => '3.3',
                            'indikator_label' => 'Pengelolaan sumber daya satuan pendidikan secara efektif, transparan, dan akuntabel',
                            'is_scoreable' => true,
                            'urutan' => 23,
                            'is_active' => true,
                            'fields' => [
                                [
                                    'label' => 'Soal 67. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Sekolah memiliki berbagai fasilitas pembelajaran, namun hasil observasi menunjukkan sebagian besar belum dimanfaatkan secara optimal oleh guru dan peserta didik. Beberapa ruang belajar, perangkat TIK, dan media pembelajaran lebih sering tidak digunakan daripada dimanfaatkan dalam proses pembelajaran.',
                                    'nama_field' => 'soal_067',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Profesional | Indikator: 3.3 — Pengelolaan sumber daya satuan pendidikan secara efektif, transparan, dan akuntabel | Subindikator: 3.3.2 — Pengelolaan Sumber Daya Satuan Pendidikan Secara Efektif untuk Peningkatan Pembelajaran Peserta Didik | Level HOTS: C4 – Analisis | Pendekatan: Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengamati tingkat pemanfaatan berbagai sumber daya sekolah serta mengarahkan identifikasi peluang peningkatan penggunaannya.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menggali penyebab belum optimalnya pemanfaatan sumber daya sekolah serta mengarahkan penyusunan langkah perbaikan.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Mengoptimalkan penggunaan sumber daya sekolah untuk mendukung peningkatan kualitas pembelajaran peserta didik.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menganalisis hubungan antara pemanfaatan sumber daya dan hasil belajar peserta didik serta mengarahkan strategi pengelolaan yang lebih efektif.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Mengembangkan sistem pengelolaan sumber daya yang adaptif terhadap kebutuhan pembelajaran dan perkembangan sekolah.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 1,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 68. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Dalam evaluasi tahunan diketahui bahwa beberapa program sekolah telah menyerap anggaran yang besar, namun dampaknya terhadap peningkatan kualitas pembelajaran peserta didik relatif rendah dibandingkan program lain yang menggunakan sumber daya lebih sedikit.',
                                    'nama_field' => 'soal_068',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Profesional | Indikator: 3.3 — Pengelolaan sumber daya satuan pendidikan secara efektif, transparan, dan akuntabel | Subindikator: 3.3.2 — Pengelolaan Sumber Daya Satuan Pendidikan Secara Efektif untuk Peningkatan Pembelajaran Peserta Didik | Level HOTS: C5 – Evaluasi | Pendekatan: Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mencermati keterkaitan antara penggunaan sumber daya dan hasil pelaksanaan program sekolah serta mengarahkan refleksi yang diperlukan.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menafsirkan efektivitas pemanfaatan sumber daya terhadap peningkatan pembelajaran peserta didik serta mengarahkan prioritas perbaikan.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Menggunakan hasil evaluasi untuk mengalokasikan sumber daya pada program yang lebih berdampak terhadap pembelajaran.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menilai efisiensi dan efektivitas pengelolaan sumber daya berdasarkan data capaian program serta mengarahkan penyempurnaan kebijakan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Mengintegrasikan pengambilan keputusan berbasis data dalam pengelolaan seluruh sumber daya sekolah untuk meningkatkan mutu pembelajaran.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 2,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 69. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Sekolah sedang menyusun Rencana Kerja Tahunan dengan berbagai program peningkatan mutu pembelajaran. Kepala sekolah perlu memastikan seluruh sumber daya sekolah dimanfaatkan secara efektif sehingga setiap program memberikan dampak terhadap capaian belajar peserta didik.',
                                    'nama_field' => 'soal_069',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Profesional | Indikator: 3.3 — Pengelolaan sumber daya satuan pendidikan secara efektif, transparan, dan akuntabel | Subindikator: 3.3.2 — Pengelolaan Sumber Daya Satuan Pendidikan Secara Efektif untuk Peningkatan Pembelajaran Peserta Didik | Level HOTS: C6 – Kreasi | Pendekatan: Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengidentifikasi kebutuhan sumber daya setiap program sekolah serta mengarahkan penyusunan prioritas penggunaannya.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menguraikan keterkaitan antara sumber daya dan target peningkatan pembelajaran serta mengarahkan perencanaan yang lebih sistematis.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Mengorganisasi pemanfaatan sumber daya berdasarkan prioritas program peningkatan mutu pembelajaran serta mengarahkan pelaksanaan yang efektif.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Mengkaji efektivitas penggunaan sumber daya terhadap pencapaian tujuan program sekolah serta mengarahkan penyempurnaan yang diperlukan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Merancang sistem pengelolaan sumber daya berbasis kinerja yang mendukung peningkatan hasil belajar peserta didik secara berkelanjutan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 3,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                            ],
                        ],
                        [
                            'judul_form' => '3.3.3 Pengelolaan Sumber Daya Satuan Pendidikan Secara Transparan dan Akuntabel',
                            'deskripsi' => 'Kompetensi Profesional — Indikator 3.3: Pengelolaan sumber daya satuan pendidikan secara efektif, transparan, dan akuntabel.',
                            'kompetensi' => KompetensiGuru::PROFESIONAL->value,
                            'indikator_kode' => '3.3',
                            'indikator_label' => 'Pengelolaan sumber daya satuan pendidikan secara efektif, transparan, dan akuntabel',
                            'is_scoreable' => true,
                            'urutan' => 24,
                            'is_active' => true,
                            'fields' => [
                                [
                                    'label' => 'Soal 70. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Dalam rapat komite sekolah muncul pertanyaan mengenai penggunaan anggaran beberapa program sekolah. Meskipun seluruh kegiatan telah dilaksanakan, sebagian warga sekolah merasa belum memperoleh informasi yang memadai mengenai proses perencanaan, pelaksanaan, dan pertanggungjawaban penggunaan sumber daya.',
                                    'nama_field' => 'soal_070',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Profesional | Indikator: 3.3 — Pengelolaan sumber daya satuan pendidikan secara efektif, transparan, dan akuntabel | Subindikator: 3.3.3 — Pengelolaan Sumber Daya Satuan Pendidikan Secara Transparan dan Akuntabel | Level HOTS: C4 – Analisis | Pendekatan: Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengamati kebutuhan informasi warga sekolah mengenai pengelolaan sumber daya serta mengarahkan penyampaian informasi yang diperlukan.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menggali harapan warga sekolah terhadap keterbukaan pengelolaan sumber daya serta mengarahkan penyempurnaan mekanisme pelaporan.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Menyampaikan informasi pengelolaan sumber daya secara terbuka sesuai ketentuan serta mengarahkan terciptanya kepercayaan warga sekolah.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menganalisis efektivitas mekanisme transparansi dan akuntabilitas terhadap tata kelola sekolah serta mengarahkan penyempurnaan sistem.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Mengembangkan sistem pengelolaan sumber daya yang transparan, akuntabel, dan partisipatif sebagai budaya organisasi sekolah.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 1,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 71. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Hasil audit internal menunjukkan bahwa seluruh dokumen administrasi keuangan telah lengkap, tetapi sebagian prosedur pengambilan keputusan dan pelaporan belum melibatkan warga sekolah secara optimal sehingga transparansi belum sepenuhnya dirasakan.',
                                    'nama_field' => 'soal_071',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Profesional | Indikator: 3.3 — Pengelolaan sumber daya satuan pendidikan secara efektif, transparan, dan akuntabel | Subindikator: 3.3.3 — Pengelolaan Sumber Daya Satuan Pendidikan Secara Transparan dan Akuntabel | Level HOTS: C5 – Evaluasi | Pendekatan: Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mencermati hasil audit mengenai pengelolaan sumber daya sekolah serta mengarahkan identifikasi kebutuhan penyempurnaan.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menafsirkan faktor-faktor yang memengaruhi transparansi pengelolaan sumber daya serta mengarahkan perbaikan mekanisme pelaporan.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Menggunakan hasil audit untuk memperkuat keterbukaan informasi dan akuntabilitas pengelolaan sumber daya sekolah.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Menelaah efektivitas tata kelola sumber daya terhadap kepercayaan warga sekolah serta mengarahkan penyempurnaan kebijakan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Merekonstruksi sistem tata kelola sumber daya berbasis transparansi, akuntabilitas, dan partisipasi seluruh pemangku kepentingan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 2,
                                    'is_required' => true,
                                    'is_active' => true,
                                ],
                                [
                                    'label' => 'Soal 72. Tindakan yang paling tepat dilakukan kepala sekolah adalah ...',
                                    'deskripsi' => 'Sekolah sedang mengembangkan sistem tata kelola digital untuk perencanaan program, pengelolaan anggaran, pemantauan pelaksanaan, dan pelaporan kepada warga sekolah serta pemangku kepentingan lainnya. Kepala sekolah diminta merancang sistem yang mendukung prinsip transparansi dan akuntabilitas.',
                                    'nama_field' => 'soal_072',
                                    'tipe_field' => 'radio',
                                    'placeholder' => null,
                                    'bantuan' => 'Kompetensi: Profesional | Indikator: 3.3 — Pengelolaan sumber daya satuan pendidikan secara efektif, transparan, dan akuntabel | Subindikator: 3.3.3 — Pengelolaan Sumber Daya Satuan Pendidikan Secara Transparan dan Akuntabel | Level HOTS: C6 – Kreasi | Pendekatan: Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                    'opsi_field' => $this->withCompetencyLevels([
                        [
                            'label' => 'A',
                            'value' => 'Mengidentifikasi kebutuhan informasi yang perlu disampaikan kepada warga sekolah serta mengarahkan penyusunan sistem pelaporan yang sesuai.',
                        ],
                        [
                            'label' => 'B',
                            'value' => 'Menggambarkan mekanisme pengelolaan sumber daya yang terbuka kepada seluruh pemangku kepentingan serta mengarahkan pelaksanaannya secara sistematis.',
                        ],
                        [
                            'label' => 'C',
                            'value' => 'Mengorganisasi sistem pengelolaan dan pelaporan sumber daya yang memenuhi prinsip transparansi dan akuntabilitas serta mengarahkan implementasi yang konsisten.',
                        ],
                        [
                            'label' => 'D',
                            'value' => 'Mengevaluasi efektivitas sistem tata kelola digital terhadap transparansi dan akuntabilitas sekolah serta mengarahkan penyempurnaan yang diperlukan.',
                        ],
                        [
                            'label' => 'E',
                            'value' => 'Merancang sistem tata kelola sumber daya berbasis digital yang menjamin transparansi, akuntabilitas, dan peningkatan mutu sekolah secara berkelanjutan.',
                        ],
                    ]),
                                    'nilai_default' => null,
                                    'validasi' => [
                                        'required' => true,
                                    ],
                                    'lebar_kolom' => 'col-md-12',
                                    'urutan' => 3,
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
                    'scoring_config' => $this->assessmentScoringConfig(),
                    'status' => $item['status'],
                    'is_active' => $item['is_active'],
                ]
            );

            $assessment->forms()->delete();

            foreach ($forms as $formData) {
                $formData = $this->normalizeFormMetadata($formData);
                $formSource = $formData;
                $fields = $formSource['fields'];
                $formData['scoring_config'] = $this->formScoringConfig($formSource);
                unset($formData['fields']);

                $form = $assessment->forms()->create($formData);

                foreach (array_values($fields) as $fieldIndex => $fieldData) {
                    $fieldData['scoring_config'] = $this->fieldScoringConfig($formSource, $fieldData, $fieldIndex);
                    $form->fields()->create($fieldData);
                }
            }
        }
    }

    private function withCompetencyLevels(array $options): array
    {
        return collect($options)
            ->values()
            ->map(function (array $option, int $index) {
                return [
                    ...$option,
                    'level_kompetensi' => LevelKompetensi::tryFromSequence($index + 1)?->value,
                ];
            })
            ->all();
    }

    private function assessmentScoringConfig(): array
    {
        return [
            'profile' => AssessmentInstrumentType::PILIHAN_GANDA_KOMPLEKS->value,
            'weight' => AssessmentInstrumentType::PILIHAN_GANDA_KOMPLEKS->weight(),
            'verification_gap_threshold' => 1.5,
            'empty_response_threshold_percent' => 10,
            'advanced_rules' => [
                'response_scoring_rule' => 'Pilihan level 1-5 langsung dikonversi menjadi skor 1-5 tanpa benar atau salah.',
                'overall_formula' => 'I_PG_KS = (I_Kep + I_Sos + I_Pro) / 3',
                'domain_ranges' => [
                    [
                        'kompetensi' => KompetensiGuru::KEPRIBADIAN->value,
                        'label' => KompetensiGuru::KEPRIBADIAN->label(),
                        'question_start' => 1,
                        'question_end' => 27,
                        'question_total' => 27,
                        'formula' => 'I_Kep = jumlah skor butir 1-27 / 27',
                    ],
                    [
                        'kompetensi' => KompetensiGuru::SOSIAL->value,
                        'label' => KompetensiGuru::SOSIAL->label(),
                        'question_start' => 28,
                        'question_end' => 48,
                        'question_total' => 21,
                        'formula' => 'I_Sos = jumlah skor butir 28-48 / 21',
                    ],
                    [
                        'kompetensi' => KompetensiGuru::PROFESIONAL->value,
                        'label' => KompetensiGuru::PROFESIONAL->label(),
                        'question_start' => 49,
                        'question_end' => 72,
                        'question_total' => 24,
                        'formula' => 'I_Pro = jumlah skor butir 49-72 / 24',
                    ],
                ],
                'interpretation_bands' => $this->interpretationBands(),
            ],
        ];
    }

    private function formScoringConfig(array $formData): array
    {
        $questionNumbers = $this->extractQuestionNumbersFromFields($formData['fields'] ?? []);
        $firstQuestion = $questionNumbers[0] ?? 1;
        $lastQuestion = $questionNumbers === [] ? $firstQuestion : max($questionNumbers);
        $domain = $this->resolveDomainConfigForQuestion($firstQuestion);

        return [
            'profile' => 'pilihan_ganda_kompleks',
            'weight' => count($questionNumbers) ?: 1,
            'advanced_rules' => [
                'domain_key' => $domain['key'],
                'domain_label' => $domain['label'],
                'domain_formula' => $domain['formula'],
                'question_start' => $firstQuestion,
                'question_end' => $lastQuestion,
                'question_total_in_form' => count($questionNumbers) ?: 1,
                'question_total_in_domain' => $domain['question_total'],
                'empty_response_threshold_percent' => 10,
                'reporting_focus' => 'Rata-rata domain ditampilkan sebagai level kompetensi pilihan ganda kompleks kepala sekolah.',
                'response_scoring_rule' => 'Setiap respons mewakili level 1-5 dan langsung menjadi skor 1-5.',
            ],
        ];
    }

    private function fieldScoringConfig(array $formData, array $fieldData, int $fieldIndex): array
    {
        $questionNumbers = $this->extractQuestionNumbersFromFields($formData['fields'] ?? []);
        $questionNumber = $this->extractQuestionNumberFromField($fieldData)
            ?? ($questionNumbers[$fieldIndex] ?? ($fieldIndex + 1));
        $domain = $this->resolveDomainConfigForQuestion($questionNumber);

        return [
            'enabled' => true,
            'profile' => 'pilihan_ganda_kompleks',
            'method' => 'choice_option_score',
            'weight' => 1,
            'rubric_code' => 'PG-KS-'.$questionNumber,
            'scale_min' => 1,
            'scale_max' => 5,
            'advanced_rules' => [
                'question_number' => $questionNumber,
                'domain_key' => $domain['key'],
                'domain_label' => $domain['label'],
                'score_mapping' => [
                    '1' => 'Alternatif Level 1: Paham',
                    '2' => 'Alternatif Level 2: Dasar',
                    '3' => 'Alternatif Level 3: Menengah',
                    '4' => 'Alternatif Level 4: Mumpuni',
                    '5' => 'Alternatif Level 5: Ahli',
                ],
                'level_descriptors' => [
                    '1' => 'Memilih tindakan pengenalan atau arah awal.',
                    '2' => 'Memilih tindakan prosedural atau penjelasan dasar.',
                    '3' => 'Memilih penerapan strategi secara terencana pada konteks kasus.',
                    '4' => 'Memilih tindakan analitis atau evaluatif berbasis kebutuhan, data, atau tata kelola.',
                    '5' => 'Memilih tindakan sistemik, inovatif, kolaboratif, dan berkelanjutan.',
                ],
            ],
        ];
    }

    private function normalizeFormMetadata(array $formData): array
    {
        $questionNumbers = $this->extractQuestionNumbersFromFields($formData['fields'] ?? []);
        $firstQuestion = $questionNumbers[0] ?? 1;
        $domain = $this->resolveDomainConfigForQuestion($firstQuestion);
        [$titleCode, $titleLabel] = $this->splitFormTitle((string) ($formData['judul_form'] ?? ''));
        $normalizedSuffix = $titleCode !== null
            ? str_replace('.', '', $titleCode)
            : str_pad((string) $firstQuestion, 3, '0', STR_PAD_LEFT);
        $questionStart = min($questionNumbers ?: [$firstQuestion]);
        $questionEnd = max($questionNumbers ?: [$firstQuestion]);

        $formData['kode_form'] = 'FORM-KS-'.$domain['code_prefix'].'-'.$normalizedSuffix;
        $formData['kompetensi'] = $domain['kompetensi'];
        $formData['indikator_kode'] = $titleCode ?? ('PG-KS-'.$questionStart);
        $formData['indikator_label'] = $titleLabel !== '' ? $titleLabel : $domain['indicator_label'];
        $formData['deskripsi'] = sprintf(
            'Kompetensi %s. Rentang butir %d-%d dalam domain %s. Setiap pilihan jawaban merepresentasikan level kompetensi 1-5 sesuai rubrik pilihan ganda kompleks kepala sekolah.',
            $domain['label'],
            $questionStart,
            $questionEnd,
            $domain['label']
        );

        return $formData;
    }

    private function extractQuestionNumbersFromFields(array $fields): array
    {
        return collect($fields)
            ->map(fn (array $field) => $this->extractQuestionNumberFromField($field))
            ->filter(fn ($value) => $value !== null)
            ->values()
            ->all();
    }

    private function extractQuestionNumberFromField(array $fieldData): ?int
    {
        $label = (string) ($fieldData['label'] ?? '');

        if (preg_match('/Soal\s+(\d+)\./', $label, $matches) !== 1) {
            return null;
        }

        return (int) $matches[1];
    }

    /**
     * @return array{0:?string,1:string}
     */
    private function splitFormTitle(string $title): array
    {
        if (preg_match('/^((?:\d+\.){2}\d+)\s+(.+)$/', trim($title), $matches) !== 1) {
            return [null, trim($title)];
        }

        return [$matches[1], trim($matches[2])];
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveDomainConfigForQuestion(int $questionNumber): array
    {
        return match (true) {
            $questionNumber <= 27 => [
                'key' => 'kepribadian',
                'label' => KompetensiGuru::KEPRIBADIAN->label(),
                'kompetensi' => KompetensiGuru::KEPRIBADIAN->value,
                'code_prefix' => 'KEP',
                'question_total' => 27,
                'indicator_label' => 'Kepribadian kepala sekolah',
                'formula' => 'I_Kep = jumlah skor butir domain / 27',
            ],
            $questionNumber <= 48 => [
                'key' => 'sosial',
                'label' => KompetensiGuru::SOSIAL->label(),
                'kompetensi' => KompetensiGuru::SOSIAL->value,
                'code_prefix' => 'SOS',
                'question_total' => 21,
                'indicator_label' => 'Kolaborasi, komunikasi, dan jejaring',
                'formula' => 'I_Sos = jumlah skor butir domain / 21',
            ],
            default => [
                'key' => 'profesional',
                'label' => KompetensiGuru::PROFESIONAL->label(),
                'kompetensi' => KompetensiGuru::PROFESIONAL->value,
                'code_prefix' => 'PRO',
                'question_total' => 24,
                'indicator_label' => 'Kepemimpinan pembelajaran dan tata kelola sekolah',
                'formula' => 'I_Pro = jumlah skor butir domain / 24',
            ],
        };
    }

    /**
     * @return array<int, array<string, float|string>>
     */
    private function interpretationBands(): array
    {
        return [
            [
                'min' => 1.00,
                'max' => 1.79,
                'label' => 'Level 1 - Paham',
                'implication' => 'Perlu penguatan konsep dasar, contoh praktik, dan pendampingan awal.',
            ],
            [
                'min' => 1.80,
                'max' => 2.59,
                'label' => 'Level 2 - Dasar',
                'implication' => 'Perlu latihan penerapan prosedur, simulasi, dan umpan balik terarah.',
            ],
            [
                'min' => 2.60,
                'max' => 3.39,
                'label' => 'Level 3 - Menengah',
                'implication' => 'Mampu menerapkan strategi; perlu penguatan analisis data, evaluasi, dan diferensiasi konteks.',
            ],
            [
                'min' => 3.40,
                'max' => 4.19,
                'label' => 'Level 4 - Mumpuni',
                'implication' => 'Mampu mengevaluasi dan menyesuaikan praktik; perlu perluasan peran sebagai penggerak, mentor, atau pengembang sekolah.',
            ],
            [
                'min' => 4.20,
                'max' => 5.00,
                'label' => 'Level 5 - Ahli',
                'implication' => 'Mampu mengembangkan sistem atau inovasi; diarahkan pada diseminasi, jejaring, dan penguatan kapasitas satuan pendidikan.',
            ],
        ];
    }
}
