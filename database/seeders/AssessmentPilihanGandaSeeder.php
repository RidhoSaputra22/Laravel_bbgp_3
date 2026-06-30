<?php

namespace Database\Seeders;

use App\Enum\AssessmentInstrumentType;
use App\Enum\AssessmentKetenagaanType;
use App\Enum\KompetensiGuru;
use App\Enum\LevelKompetensi;
use App\Models\Assessment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AssessmentPilihanGandaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $assessments = [
            [
                'kode_assessment' => 'ASM-KOMP-GURU-003',
                'judul' => 'Tes Pilihan Ganda Kompleks Kompetensi Guru',
                'deskripsi' => 'Instrumen pemetaan kompetensi guru yang mencakup kompetensi pedagogik, kepribadian, sosial, dan profesional melalui 123 soal situasional.',
                'petunjuk' => 'Pilihlah jawaban yang paling sesuai dengan kondisi atau pemahaman Anda saat ini secara jujur. Tidak ada jawaban yang salah; setiap pilihan merepresentasikan level kompetensi dari Level 1 (Paham) hingga Level 5 (Ahli).',
                'instrument_type' => AssessmentInstrumentType::PILIHAN_GANDA_KOMPLEKS->value,
                'status' => 'publish',
                'is_active' => true,
                'forms' => [

                    // Kompetensi 1: Pedagogik
                    [
                        'judul_form' => '1.1.1 Pengelolaan Perilaku Peserta Didik yang Sulit',
                        'kode_form' => 'FORM-PED-111',
                        'deskripsi' => 'Kompetensi Pedagogik — Indikator 1.1: Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                        'indikator_kode' => '1.1',
                        'indikator_label' => 'Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'is_scoreable' => true,
                        'urutan' => 1,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Soal 1. Sebagai guru, tindakan yang paling tepat untuk menangani perilaku Bima secara edukatif adalah ....',
                                'deskripsi' => 'Saat kegiatan diskusi berlangsung, Bima beberapa kali memotong pembicaraan teman-temannya dan selalu ingin mendominasi kelompok. Akibatnya, anggota kelompok lain menjadi enggan menyampaikan pendapat dan suasana diskusi kurang kondusif.',
                                'nama_field' => 'soal_001',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 1.1.1 — Pengelolaan Perilaku Peserta Didik yang Sulit. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengenali faktor yang memengaruhi perilaku Bima melalui pengamatan selama diskusi serta mengarahkan partisipasinya secara lebih proporsional.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menguraikan kembali aturan diskusi kepada Bima serta mengarahkan pentingnya memberi kesempatan yang setara kepada seluruh anggota kelompok.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Melaksanakan strategi pengelolaan diskusi yang terstruktur bersama kelompok serta mengarahkan setiap anggota berpartisipasi secara seimbang.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menelaah berbagai faktor yang menyebabkan dominasi Bima dalam kelompok serta mengarahkan langkah pendampingan yang lebih sesuai.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengembangkan pendekatan pembinaan perilaku kolaboratif bersama peserta didik sehingga interaksi kelompok menjadi lebih positif dan berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 2. Sebagai guru, tindakan yang paling tepat untuk membantu Rani memperbaiki perilakunya adalah ....',
                                'deskripsi' => 'Rani sering meninggalkan tempat duduknya tanpa izin ketika pembelajaran berlangsung. Meskipun telah beberapa kali diingatkan, perilaku tersebut masih sering terjadi dan mulai mengganggu konsentrasi teman-temannya.',
                                'nama_field' => 'soal_002',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 1.1.1 — Pengelolaan Perilaku Peserta Didik yang Sulit. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mencermati situasi yang mendorong Rani meninggalkan tempat duduknya serta mengarahkan perilaku yang lebih sesuai selama pembelajaran berlangsung.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menafsirkan kembali harapan perilaku di kelas kepada Rani serta mengarahkan pentingnya mengikuti aturan yang telah disepakati bersama.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengimplementasikan pengelolaan perilaku yang konsisten bersama Rani serta mengarahkan keterlibatannya secara positif dalam kegiatan belajar.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Mengkaji faktor-faktor yang memengaruhi perilaku Rani bersama pihak terkait serta mengarahkan bentuk pendampingan yang lebih tepat.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merekonstruksi strategi pembinaan perilaku yang berkelanjutan bersama peserta didik sehingga tercipta kebiasaan belajar yang lebih positif.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 3. Sebagai guru, tindakan yang paling tepat untuk menangani perilaku Andi secara konstruktif adalah ....',
                                'deskripsi' => 'Selama kegiatan pembelajaran berlangsung, Andi sering mengganggu teman di sebelahnya dengan mengajak berbicara dan bercanda secara berlebihan. Meskipun suasana kelas sedang fokus pada tugas yang diberikan, Andi tetap mengulangi perilaku tersebut sehingga mengganggu konsentrasi beberapa peserta didik lainnya.',
                                'nama_field' => 'soal_003',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 1.1.1 — Pengelolaan Perilaku Peserta Didik yang Sulit. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengamati pola perilaku Andi selama pembelajaran serta mengarahkan keterlibatannya pada kegiatan belajar yang lebih positif.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menggambarkan harapan perilaku yang sesuai kepada Andi serta mengarahkan pentingnya menjaga kenyamanan belajar bersama.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Menggunakan strategi pengelolaan kelas yang konsisten kepada Andi serta mengarahkan partisipasinya secara lebih produktif.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menilai berbagai faktor yang memengaruhi perilaku Andi serta mengarahkan bentuk pendampingan yang lebih sesuai.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Menciptakan program pembinaan perilaku yang melibatkan berbagai pihak serta mengarahkan perubahan perilaku secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => '1.1.2 Pengelolaan Kelas untuk Mencapai Pembelajaran yang Berpusat pada Peserta Didik',
                        'kode_form' => 'FORM-PED-112',
                        'deskripsi' => 'Kompetensi Pedagogik — Indikator 1.1: Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                        'indikator_kode' => '1.1',
                        'indikator_label' => 'Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'is_scoreable' => true,
                        'urutan' => 2,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Soal 4. Tindakan yang paling tepat untuk mengelola kelas agar pembelajaran lebih berpusat pada peserta didik adalah ....',
                                'deskripsi' => 'Dalam pembelajaran IPA, guru lebih banyak menjelaskan materi di depan kelas selama hampir seluruh jam pelajaran. Sebagian besar peserta didik hanya mendengarkan dan mencatat tanpa kesempatan untuk mengeksplorasi ide atau pengalaman mereka.',
                                'nama_field' => 'soal_004',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 1.1.2 — Pengelolaan Kelas untuk Mencapai Pembelajaran yang Berpusat pada Peserta Didik. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengamati tingkat keterlibatan peserta didik selama pembelajaran serta mengarahkan mereka untuk berpartisipasi lebih aktif dalam kegiatan belajar.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menggambarkan berbagai bentuk partisipasi belajar kepada peserta didik serta mengarahkan mereka untuk lebih terlibat dalam proses pembelajaran.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Menggunakan kegiatan pembelajaran yang memberi ruang eksplorasi kepada peserta didik serta mengarahkan keterlibatan mereka secara aktif.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menganalisis pola keterlibatan peserta didik selama pembelajaran serta mengarahkan perbaikan strategi pengelolaan kelas yang lebih efektif.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengintegrasikan berbagai pendekatan pembelajaran aktif sehingga peserta didik dapat berpartisipasi secara optimal dan berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 5. Sebagai guru, langkah yang paling tepat untuk meningkatkan keterlibatan seluruh peserta didik adalah ....',
                                'deskripsi' => 'Saat kegiatan tanya jawab berlangsung, hanya beberapa peserta didik yang aktif menjawab pertanyaan. Sebagian besar peserta didik lainnya cenderung pasif dan menunggu arahan dari guru.',
                                'nama_field' => 'soal_005',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 1.1.2 — Pengelolaan Kelas untuk Mencapai Pembelajaran yang Berpusat pada Peserta Didik. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Menemukan pola partisipasi peserta didik selama kegiatan berlangsung serta mengarahkan keterlibatan yang lebih merata dalam pembelajaran.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Mengemukakan pentingnya kontribusi setiap peserta didik dalam pembelajaran serta mengarahkan mereka untuk berani berpartisipasi.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mempraktikkan strategi pembelajaran yang memberi kesempatan setara kepada seluruh peserta didik untuk berpartisipasi secara aktif.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menilai efektivitas pola interaksi yang terjadi di kelas serta mengarahkan perbaikan pengelolaan pembelajaran yang lebih inklusif.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Menciptakan sistem pembelajaran partisipatif yang mendorong keterlibatan seluruh peserta didik secara konsisten dan berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 6. Sebagai guru, tindakan yang paling tepat untuk memperkuat pembelajaran yang berpusat pada peserta didik adalah ....',
                                'deskripsi' => 'Pada saat pembelajaran berlangsung, guru menyadari bahwa sebagian besar peserta didik hanya menunggu instruksi tanpa berinisiatif mencari informasi atau mengemukakan gagasan mereka sendiri. Akibatnya, proses belajar menjadi kurang aktif dan kurang memberikan ruang bagi peserta didik untuk mengembangkan potensinya.',
                                'nama_field' => 'soal_006',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 1.1.2 — Pengelolaan Kelas untuk Mencapai Pembelajaran yang Berpusat pada Peserta Didik. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Menemukan bentuk keterlibatan peserta didik yang masih terbatas serta mengarahkan mereka untuk lebih aktif belajar.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menjelaskan pentingnya peran aktif peserta didik dalam pembelajaran serta mengarahkan keterlibatan mereka secara lebih mandiri.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mempraktikkan kegiatan belajar yang memberi ruang eksplorasi kepada peserta didik serta mengarahkan keterlibatan yang lebih bermakna.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menganalisis hambatan yang membatasi partisipasi peserta didik serta mengarahkan perbaikan strategi pembelajaran yang sesuai.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengembangkan ekosistem pembelajaran yang mendorong kemandirian peserta didik serta mengarahkan keterlibatan yang berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => '1.1.3 Rasa Aman dan Nyaman Peserta Didik dalam Proses Pembelajaran',
                        'kode_form' => 'FORM-PED-113',
                        'deskripsi' => 'Kompetensi Pedagogik — Indikator 1.1: Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                        'indikator_kode' => '1.1',
                        'indikator_label' => 'Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'is_scoreable' => true,
                        'urutan' => 3,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Soal 7. Sebagai guru, tindakan yang paling tepat untuk membangun rasa aman dan nyaman peserta didik adalah ....',
                                'deskripsi' => 'Seorang peserta didik terlihat enggan menjawab pertanyaan guru karena khawatir ditertawakan oleh teman-temannya ketika memberikan jawaban yang kurang tepat.',
                                'nama_field' => 'soal_007',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 1.1.3 — Rasa Aman dan Nyaman Peserta Didik dalam Proses Pembelajaran. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengenali kondisi yang membuat peserta didik ragu berpartisipasi serta mengarahkan suasana belajar yang lebih mendukung dan menghargai.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menguraikan pentingnya sikap saling menghormati kepada peserta didik serta mengarahkan terbentuknya interaksi yang lebih positif.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Melaksanakan kegiatan pembelajaran yang mendorong penghargaan terhadap setiap pendapat serta mengarahkan partisipasi yang lebih percaya diri.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menelaah faktor-faktor yang memengaruhi rasa aman peserta didik serta mengarahkan langkah perbaikan lingkungan belajar yang sesuai.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengembangkan budaya kelas yang menghargai keberagaman pendapat sehingga rasa aman dan nyaman tumbuh secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 8. Sebagai guru, tindakan yang paling tepat untuk memperkuat rasa aman dan nyaman peserta didik dalam situasi tersebut adalah ....',
                                'deskripsi' => 'Dalam kegiatan kelompok, beberapa peserta didik sering mengabaikan pendapat temannya yang dianggap kurang mampu. Akibatnya, peserta didik tersebut menjadi semakin pasif dan kurang percaya diri.',
                                'nama_field' => 'soal_008',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 1.1.3 — Rasa Aman dan Nyaman Peserta Didik dalam Proses Pembelajaran. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mencermati bentuk interaksi yang terjadi dalam kelompok serta mengarahkan peserta didik untuk saling menghargai kontribusi temannya.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menafsirkan kembali nilai-nilai kebersamaan kepada peserta didik serta mengarahkan pentingnya menghormati setiap pendapat yang muncul.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengimplementasikan pengelolaan kelompok yang memberi kesempatan setara kepada seluruh peserta didik untuk berkontribusi secara aktif.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Mengkaji penyebab munculnya perilaku eksklusif dalam kelompok serta mengarahkan langkah perbaikan yang lebih tepat.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengintegrasikan berbagai strategi pembelajaran inklusif sehingga setiap peserta didik merasa dihargai dan nyaman berpartisipasi.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 9. Sebagai guru, tindakan yang paling tepat untuk membangun rasa aman dan nyaman peserta didik tersebut dalam pembelajaran adalah ....',
                                'deskripsi' => 'Seorang peserta didik baru yang pindah dari sekolah lain tampak lebih sering menyendiri dan jarang berinteraksi dengan teman-temannya. Saat kegiatan kelompok berlangsung, peserta didik tersebut cenderung diam dan hanya mengikuti arahan tanpa menyampaikan pendapat.',
                                'nama_field' => 'soal_009',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 1.1.3 — Rasa Aman dan Nyaman Peserta Didik dalam Proses Pembelajaran. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengenali kebutuhan peserta didik melalui interaksi yang positif serta mengarahkan keterlibatannya secara bertahap dalam kegiatan kelas.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Mengemukakan pentingnya sikap saling menerima kepada seluruh peserta didik serta mengarahkan terciptanya suasana yang inklusif.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengimplementasikan kegiatan kolaboratif yang mendukung interaksi positif serta mengarahkan keterlibatan peserta didik secara aktif.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Mengkaji faktor-faktor yang memengaruhi kenyamanan peserta didik serta mengarahkan langkah pendampingan yang lebih tepat.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengintegrasikan berbagai strategi pembelajaran inklusif yang berkelanjutan serta mengarahkan tumbuhnya rasa memiliki dalam kelas.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => '1.2.1 Desain Pembelajaran yang Terstruktur dan Berurutan untuk Mencapai Tujuan Pembelajaran',
                        'kode_form' => 'FORM-PED-121',
                        'deskripsi' => 'Kompetensi Pedagogik — Indikator 1.1: Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                        'indikator_kode' => '1.1',
                        'indikator_label' => 'Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'is_scoreable' => true,
                        'urutan' => 4,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Soal 10. Berdasarkan kondisi tersebut, tindakan yang paling tepat dilakukan guru untuk meningkatkan kualitas desain pembelajaran adalah ...',
                                'deskripsi' => 'Seorang guru telah menyusun kegiatan pembelajaran yang menarik dan melibatkan berbagai aktivitas peserta didik. Namun, setelah dievaluasi, beberapa kegiatan yang dilaksanakan ternyata tidak mendukung pencapaian tujuan pembelajaran yang telah ditetapkan di awal.',
                                'nama_field' => 'soal_010',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 1.2.1 — Desain Pembelajaran yang Terstruktur dan Berurutan untuk Mencapai Tujuan Pembelajaran. Level HOTS: C4 – Analisis. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengenali keterkaitan antara tujuan dan kegiatan pembelajaran serta mengarahkan perbaikan urutan kegiatan yang digunakan.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menguraikan hubungan antara tujuan dan aktivitas pembelajaran serta mengarahkan penyusunan langkah belajar yang lebih selaras.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengimplementasikan penyusunan kegiatan yang mengikuti tujuan pembelajaran serta mengarahkan ketercapaian kompetensi secara bertahap.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menganalisis kesesuaian antar komponen pembelajaran serta mengarahkan penyempurnaan alur belajar yang lebih efektif.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengembangkan desain pembelajaran yang terintegrasi dan berkelanjutan serta mengarahkan pencapaian tujuan secara optimal.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 11. Sebagai guru, tindakan yang paling tepat dilakukan untuk memperbaiki desain pembelajaran berikutnya adalah ...',
                                'deskripsi' => 'Dalam refleksi pembelajaran, guru menemukan bahwa sebagian besar peserta didik mengalami kesulitan memahami materi lanjutan karena penguasaan konsep prasyarat belum terbentuk dengan baik.',
                                'nama_field' => 'soal_011',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 1.2.1 — Desain Pembelajaran yang Terstruktur dan Berurutan untuk Mencapai Tujuan Pembelajaran. Level HOTS: C5 – Evaluasi. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mencermati urutan materi yang telah diajarkan serta mengarahkan penguatan konsep yang belum dipahami peserta didik.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menafsirkan hubungan antar materi pembelajaran serta mengarahkan penyusunan kegiatan yang lebih sistematis.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Menggunakan tahapan pembelajaran yang memperhatikan konsep prasyarat serta mengarahkan kesiapan belajar peserta didik.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menelaah efektivitas urutan pembelajaran yang telah diterapkan serta mengarahkan perbaikan berdasarkan kebutuhan belajar.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merekonstruksi desain pembelajaran berdasarkan hasil evaluasi serta mengarahkan perkembangan kompetensi peserta didik secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 12. Tindakan yang paling tepat dilakukan guru dalam merancang pembelajaran tersebut adalah ...',
                                'deskripsi' => 'Sekolah mendorong seluruh guru untuk menyusun pembelajaran yang mampu mengembangkan kompetensi peserta didik secara bertahap mulai dari pemahaman konsep hingga penerapan dalam kehidupan nyata.',
                                'nama_field' => 'soal_012',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 1.2.1 — Desain Pembelajaran yang Terstruktur dan Berurutan untuk Mencapai Tujuan Pembelajaran. Level HOTS: C6 – Kreasi. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Menemukan kebutuhan belajar peserta didik serta mengarahkan penyusunan kegiatan yang mendukung pencapaian tujuan.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menjelaskan tahapan pembelajaran yang akan dilaksanakan serta mengarahkan keterlibatan peserta didik dalam proses belajar.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Melaksanakan penyusunan aktivitas belajar secara bertahap serta mengarahkan pencapaian tujuan pembelajaran yang ditetapkan.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Mengevaluasi keterpaduan setiap tahapan pembelajaran serta mengarahkan penyempurnaan alur pembelajaran yang digunakan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merancang pengalaman belajar yang terstruktur dan berkesinambungan serta mengarahkan penguasaan kompetensi secara menyeluruh.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => '1.2.2 Desain Pembelajaran yang Relevan dengan Kondisi di Sekitar Sekolah dengan Melibatkan Peserta Didik',
                        'kode_form' => 'FORM-PED-122',
                        'deskripsi' => 'Kompetensi Pedagogik — Indikator 1.1: Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                        'indikator_kode' => '1.1',
                        'indikator_label' => 'Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'is_scoreable' => true,
                        'urutan' => 5,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Soal 13. Berdasarkan kondisi tersebut, tindakan yang paling tepat dilakukan guru untuk meningkatkan relevansi pembelajaran adalah ...',
                                'deskripsi' => 'Dalam pembelajaran IPA tentang pencemaran lingkungan, guru menggunakan contoh-contoh yang terdapat dalam buku teks dari daerah lain. Padahal, di sekitar sekolah terdapat sungai yang mengalami permasalahan sampah dan sering menjadi perhatian masyarakat setempat.',
                                'nama_field' => 'soal_013',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 1.2.2 — Desain Pembelajaran yang Relevan dengan Kondisi di Sekitar Sekolah dengan Melibatkan Peserta Didik. Level HOTS: C4 – Analisis. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengamati potensi lingkungan sekitar sekolah serta mengarahkan peserta didik mengenali keterkaitannya dengan materi pembelajaran.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menggambarkan hubungan antara materi pembelajaran dan kondisi lingkungan sekitar serta mengarahkan keterlibatan peserta didik dalam pembelajaran.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Menggunakan lingkungan sekitar sebagai konteks pembelajaran serta mengarahkan peserta didik menghubungkan konsep dengan kehidupan nyata.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menganalisis peluang pemanfaatan lingkungan sekitar dalam pembelajaran serta mengarahkan keterlibatan peserta didik secara lebih bermakna.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengembangkan pengalaman belajar berbasis lingkungan sekitar yang terintegrasi serta mengarahkan partisipasi peserta didik secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 14. Tindakan yang paling tepat dilakukan guru untuk meningkatkan kualitas pembelajaran tersebut adalah ...',
                                'deskripsi' => 'Guru telah mengajak peserta didik melakukan observasi lingkungan sekitar sekolah. Namun, sebagian besar peserta didik hanya mengumpulkan data tanpa mampu menghubungkannya dengan konsep yang sedang dipelajari.',
                                'nama_field' => 'soal_014',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 1.2.2 — Desain Pembelajaran yang Relevan dengan Kondisi di Sekitar Sekolah dengan Melibatkan Peserta Didik. Level HOTS: C5 – Evaluasi. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengenali keterkaitan antara hasil observasi dan materi pembelajaran serta mengarahkan peserta didik menemukan hubungan keduanya.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menguraikan hubungan antara temuan lapangan dan konsep pembelajaran serta mengarahkan peserta didik memahami keterkaitannya.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengimplementasikan kegiatan analisis hasil observasi secara terarah serta mengarahkan peserta didik mengaitkan fakta dan konsep.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menelaah efektivitas pemanfaatan lingkungan sekitar dalam pembelajaran serta mengarahkan perbaikan strategi yang digunakan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merekonstruksi pembelajaran berbasis lingkungan yang lebih kontekstual serta mengarahkan peserta didik membangun pemahaman yang mendalam.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 15. Tindakan yang paling tepat dilakukan guru untuk mewujudkan tujuan tersebut adalah ...',
                                'deskripsi' => 'Sekolah berkomitmen mengembangkan pembelajaran yang mampu menghubungkan materi pelajaran dengan kehidupan peserta didik dan potensi yang ada di lingkungan sekitar sekolah.',
                                'nama_field' => 'soal_015',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 1.2.2 — Desain Pembelajaran yang Relevan dengan Kondisi di Sekitar Sekolah dengan Melibatkan Peserta Didik. Level HOTS: C6 – Kreasi. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Menemukan berbagai potensi lingkungan sekitar yang relevan serta mengarahkan peserta didik mengenali manfaatnya dalam pembelajaran.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menjelaskan keterkaitan potensi lingkungan dengan materi pembelajaran serta mengarahkan keterlibatan peserta didik dalam kegiatan belajar.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Melaksanakan pembelajaran yang memanfaatkan potensi lingkungan sekitar serta mengarahkan peserta didik membangun pengalaman belajar nyata.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Mengevaluasi kesesuaian konteks lingkungan dalam pembelajaran serta mengarahkan penyempurnaan desain pembelajaran yang digunakan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merancang pembelajaran kontekstual berbasis lingkungan sekitar secara berkelanjutan serta mengarahkan keterlibatan peserta didik secara optimal.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => '1.2.3 Pemilihan dan Penggunaan Sumber Belajar yang Sesuai dengan Tujuan Pembelajaran',
                        'kode_form' => 'FORM-PED-123',
                        'deskripsi' => 'Kompetensi Pedagogik — Indikator 1.1: Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                        'indikator_kode' => '1.1',
                        'indikator_label' => 'Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'is_scoreable' => true,
                        'urutan' => 6,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Soal 16. Berdasarkan kondisi tersebut, tindakan yang paling tepat dilakukan guru adalah ...',
                                'deskripsi' => 'Guru menggunakan satu jenis sumber belajar berupa buku teks untuk seluruh kegiatan pembelajaran. Setelah dievaluasi, peserta didik mengalami kesulitan memahami konsep yang bersifat abstrak.',
                                'nama_field' => 'soal_016',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 1.2.3 — Pemilihan dan Penggunaan Sumber Belajar yang Sesuai dengan Tujuan Pembelajaran. Level HOTS: C4 – Analisis. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mencermati karakteristik sumber belajar yang digunakan serta mengarahkan pemanfaatannya sesuai kebutuhan peserta didik.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menafsirkan kesesuaian sumber belajar dengan tujuan pembelajaran serta mengarahkan penggunaannya secara lebih efektif.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Menggunakan berbagai sumber belajar yang relevan dengan materi serta mengarahkan peserta didik memperoleh pengalaman belajar yang lebih kaya.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menganalisis efektivitas sumber belajar yang digunakan serta mengarahkan pemilihan sumber yang lebih sesuai.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengembangkan sistem pemanfaatan sumber belajar yang beragam dan terintegrasi serta mengarahkan pencapaian tujuan pembelajaran secara optimal.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 17. Tindakan yang paling tepat dilakukan guru adalah ...',
                                'deskripsi' => 'Dalam pembelajaran sejarah, guru menyediakan artikel, video dokumenter, dan arsip digital. Namun, peserta didik lebih banyak mengakses sumber yang mudah dipahami tanpa membandingkan informasi dari berbagai sumber.',
                                'nama_field' => 'soal_017',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 1.2.3 — Pemilihan dan Penggunaan Sumber Belajar yang Sesuai dengan Tujuan Pembelajaran. Level HOTS: C5 – Evaluasi. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengamati pola penggunaan sumber belajar oleh peserta didik serta mengarahkan pemanfaatannya secara lebih beragam.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menguraikan kelebihan berbagai sumber belajar yang tersedia serta mengarahkan penggunaannya sesuai kebutuhan pembelajaran.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengimplementasikan kegiatan yang melibatkan beragam sumber belajar serta mengarahkan peserta didik membandingkan informasi yang diperoleh.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menilai efektivitas penggunaan sumber belajar yang telah dimanfaatkan serta mengarahkan perbaikan strategi pembelajaran.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengintegrasikan berbagai sumber belajar dalam pengalaman belajar yang komprehensif serta mengarahkan pembentukan pemahaman yang lebih mendalam.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 18. Tindakan yang paling tepat dilakukan guru untuk mengoptimalkan sumber belajar tersebut adalah ...',
                                'deskripsi' => 'Sekolah memiliki akses terhadap berbagai sumber belajar cetak, digital, dan lingkungan sekitar yang dapat dimanfaatkan dalam pembelajaran.',
                                'nama_field' => 'soal_018',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 1.2.3 — Pemilihan dan Penggunaan Sumber Belajar yang Sesuai dengan Tujuan Pembelajaran. Level HOTS: C6 – Kreasi. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengenali berbagai sumber belajar yang tersedia serta mengarahkan pemanfaatannya dalam kegiatan pembelajaran.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menggambarkan fungsi setiap sumber belajar yang tersedia serta mengarahkan penggunaannya sesuai tujuan pembelajaran.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mempraktikkan penggunaan berbagai sumber belajar dalam pembelajaran serta mengarahkan peserta didik memanfaatkannya secara aktif.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Mengkaji kesesuaian sumber belajar terhadap kebutuhan pembelajaran serta mengarahkan penyempurnaan penggunaannya.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Menciptakan ekosistem pembelajaran yang memadukan berbagai sumber belajar secara terpadu serta mengarahkan pencapaian kompetensi secara optimal.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => '1.2.4 Instruksi Pembelajaran yang Mencakup Strategi dan Komunikasi untuk Menumbuhkan Minat dan Nalar Kritis Peserta Didik',
                        'kode_form' => 'FORM-PED-124',
                        'deskripsi' => 'Kompetensi Pedagogik — Indikator 1.1: Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                        'indikator_kode' => '1.1',
                        'indikator_label' => 'Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'is_scoreable' => true,
                        'urutan' => 7,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Soal 19. Tindakan yang paling tepat dilakukan guru untuk meningkatkan nalar kritis peserta didik adalah ...',
                                'deskripsi' => 'Saat pembelajaran berlangsung, guru lebih sering memberikan informasi secara langsung. Akibatnya, peserta didik jarang mengajukan pertanyaan maupun memberikan alasan terhadap jawaban yang mereka sampaikan.',
                                'nama_field' => 'soal_019',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 1.2.4 — Instruksi Pembelajaran yang Mencakup Strategi dan Komunikasi untuk Menumbuhkan Minat dan Nalar Kritis Peserta Didik. Level HOTS: C4 – Analisis. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengamati respons peserta didik selama pembelajaran serta mengarahkan mereka untuk lebih aktif mengemukakan pendapat.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menjelaskan pentingnya mengemukakan alasan dalam menjawab pertanyaan serta mengarahkan partisipasi peserta didik.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Menggunakan strategi tanya jawab yang mendorong pemikiran peserta didik serta mengarahkan eksplorasi berbagai kemungkinan jawaban.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menganalisis kualitas interaksi pembelajaran yang terjadi serta mengarahkan perbaikan strategi komunikasi yang digunakan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengembangkan budaya dialog kritis dalam pembelajaran secara berkelanjutan serta mengarahkan peserta didik membangun argumentasi yang kuat.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 20. Tindakan yang paling tepat dilakukan guru adalah ...',
                                'deskripsi' => 'Guru telah menggunakan metode diskusi, namun sebagian besar peserta didik masih cenderung menerima pendapat teman tanpa memberikan tanggapan atau pertanyaan kritis.',
                                'nama_field' => 'soal_020',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 1.2.4 — Instruksi Pembelajaran yang Mencakup Strategi dan Komunikasi untuk Menumbuhkan Minat dan Nalar Kritis Peserta Didik. Level HOTS: C5 – Evaluasi. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengenali pola partisipasi peserta didik dalam diskusi serta mengarahkan mereka untuk memberikan tanggapan yang lebih aktif.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menguraikan pentingnya memberikan alasan terhadap pendapat yang disampaikan serta mengarahkan peserta didik berargumentasi secara logis.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengimplementasikan strategi diskusi yang menuntut peserta didik memberikan alasan serta mengarahkan munculnya pemikiran yang lebih kritis.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menelaah efektivitas strategi komunikasi yang telah digunakan serta mengarahkan perbaikan proses diskusi yang berlangsung.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merekonstruksi desain interaksi pembelajaran yang menumbuhkan budaya berpikir kritis serta mengarahkan partisipasi yang lebih bermakna.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 21. Tindakan yang paling tepat dilakukan guru adalah ...',
                                'deskripsi' => 'Sekolah menginginkan proses pembelajaran yang mampu meningkatkan rasa ingin tahu, minat belajar, dan kemampuan berpikir kritis peserta didik secara berkelanjutan.',
                                'nama_field' => 'soal_021',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 1.2.4 — Instruksi Pembelajaran yang Mencakup Strategi dan Komunikasi untuk Menumbuhkan Minat dan Nalar Kritis Peserta Didik. Level HOTS: C6 – Kreasi. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Menemukan minat belajar peserta didik serta mengarahkan keterlibatan mereka dalam berbagai aktivitas pembelajaran.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Mengemukakan pertanyaan yang mendorong rasa ingin tahu peserta didik serta mengarahkan keterlibatan mereka dalam pembelajaran.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Melaksanakan strategi pembelajaran yang memicu eksplorasi gagasan serta mengarahkan peserta didik berpikir lebih mendalam.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Mengevaluasi efektivitas strategi komunikasi yang digunakan serta mengarahkan penyempurnaan proses pembelajaran.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merancang pengalaman belajar yang mendorong rasa ingin tahu dan berpikir kritis secara berkelanjutan serta mengarahkan pembelajaran yang bermakna.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => '1.2.5 Penggunaan Teknologi Informasi dan Komunikasi (TIK) Secara Adaptif dalam Pembelajaran',
                        'kode_form' => 'FORM-PED-125',
                        'deskripsi' => 'Kompetensi Pedagogik — Indikator 1.1: Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                        'indikator_kode' => '1.1',
                        'indikator_label' => 'Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'is_scoreable' => true,
                        'urutan' => 8,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Soal 22. Tindakan yang paling tepat dilakukan guru adalah ...',
                                'deskripsi' => 'Guru menggunakan aplikasi presentasi digital dalam setiap pembelajaran. Namun, peserta didik tetap menunjukkan keterlibatan yang rendah dan cenderung hanya menjadi penerima informasi.',
                                'nama_field' => 'soal_022',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 1.2.5 — Penggunaan Teknologi Informasi dan Komunikasi (TIK) Secara Adaptif dalam Pembelajaran. Level HOTS: C4 – Analisis. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mencermati penggunaan teknologi yang telah dilakukan serta mengarahkan pemanfaatannya sesuai kebutuhan pembelajaran.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menafsirkan hubungan antara penggunaan teknologi dan keterlibatan peserta didik serta mengarahkan pemanfaatannya secara lebih tepat.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Menggunakan teknologi yang memungkinkan interaksi peserta didik secara aktif serta mengarahkan keterlibatan mereka dalam pembelajaran.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menganalisis efektivitas penggunaan teknologi yang diterapkan serta mengarahkan perbaikan strategi pembelajaran digital.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengembangkan pemanfaatan teknologi yang adaptif dan berpusat pada peserta didik serta mengarahkan pembelajaran yang lebih bermakna.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 23. Tindakan yang paling tepat dilakukan guru adalah ...',
                                'deskripsi' => 'Sekolah menyediakan berbagai platform digital pembelajaran, namun guru menemukan bahwa tidak semua peserta didik memiliki kemampuan dan akses yang sama dalam memanfaatkan teknologi tersebut.',
                                'nama_field' => 'soal_023',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 1.2.5 — Penggunaan Teknologi Informasi dan Komunikasi (TIK) Secara Adaptif dalam Pembelajaran. Level HOTS: C5 – Evaluasi. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengamati kondisi peserta didik dalam mengakses teknologi serta mengarahkan penggunaan yang sesuai dengan kebutuhan belajar.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menggambarkan berbagai alternatif penggunaan teknologi yang tersedia serta mengarahkan pemanfaatannya secara lebih fleksibel.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengimplementasikan penggunaan teknologi yang mempertimbangkan kondisi peserta didik serta mengarahkan keterlibatan mereka secara optimal.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menilai kesesuaian penggunaan teknologi terhadap karakteristik peserta didik serta mengarahkan penyesuaian strategi pembelajaran.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengintegrasikan berbagai pilihan teknologi yang adaptif terhadap kebutuhan peserta didik serta mengarahkan pembelajaran yang inklusif.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 24. Tindakan yang paling tepat dilakukan guru adalah ...',
                                'deskripsi' => 'Sekolah mendorong guru untuk memanfaatkan teknologi digital tidak hanya sebagai alat penyampaian materi, tetapi juga sebagai sarana kolaborasi, eksplorasi, dan refleksi pembelajaran.',
                                'nama_field' => 'soal_024',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 1.2.5 — Penggunaan Teknologi Informasi dan Komunikasi (TIK) Secara Adaptif dalam Pembelajaran. Level HOTS: C6 – Kreasi. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengenali potensi teknologi yang tersedia serta mengarahkan pemanfaatannya dalam berbagai kegiatan pembelajaran.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menjelaskan manfaat teknologi bagi proses belajar peserta didik serta mengarahkan penggunaannya secara bertanggung jawab.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mempraktikkan penggunaan teknologi dalam aktivitas belajar yang beragam serta mengarahkan partisipasi peserta didik secara aktif.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Mengkaji efektivitas penggunaan teknologi dalam pembelajaran serta mengarahkan penyempurnaan strategi yang digunakan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Menciptakan ekosistem pembelajaran digital yang adaptif dan kolaboratif serta mengarahkan pengembangan kompetensi peserta didik secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => '1.3.1 Perancangan Asesmen yang Berpusat pada Peserta Didik',
                        'kode_form' => 'FORM-PED-131',
                        'deskripsi' => 'Kompetensi Pedagogik — Indikator 1.1: Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                        'indikator_kode' => '1.1',
                        'indikator_label' => 'Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'is_scoreable' => true,
                        'urutan' => 9,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Soal 25. Berdasarkan kondisi tersebut, tindakan yang paling tepat dilakukan guru dalam merancang asesmen adalah ...',
                                'deskripsi' => 'Seorang guru merancang asesmen akhir berupa tes tertulis pilihan ganda untuk seluruh materi yang telah dipelajari. Namun, tujuan pembelajaran juga mencakup kemampuan kolaborasi, komunikasi, dan pemecahan masalah yang tidak sepenuhnya dapat diukur melalui tes tersebut.',
                                'nama_field' => 'soal_025',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 1.3.1 — Perancangan Asesmen yang Berpusat pada Peserta Didik. Level HOTS: C4 – Analisis. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengenali keterkaitan antara tujuan pembelajaran dan bentuk asesmen serta mengarahkan penyesuaian instrumen yang digunakan.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menguraikan karakteristik kompetensi yang akan diukur serta mengarahkan pemilihan bentuk asesmen yang lebih sesuai.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengimplementasikan berbagai bentuk asesmen yang relevan dengan tujuan pembelajaran serta mengarahkan pengukuran kompetensi secara lebih menyeluruh.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menganalisis keselarasan antara tujuan, aktivitas, dan asesmen pembelajaran serta mengarahkan penyempurnaan rancangan asesmen.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengembangkan sistem asesmen autentik yang terintegrasi dengan proses pembelajaran serta mengarahkan pengukuran kompetensi secara komprehensif.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 26. Tindakan yang paling tepat dilakukan guru untuk memperbaiki rancangan asesmen tersebut adalah ...',
                                'deskripsi' => 'Guru menemukan bahwa instrumen asesmen yang digunakan mampu mengukur penguasaan materi, tetapi belum memberikan informasi yang cukup mengenai perkembangan proses belajar setiap peserta didik.',
                                'nama_field' => 'soal_026',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 1.3.1 — Perancangan Asesmen yang Berpusat pada Peserta Didik. Level HOTS: C5 – Evaluasi. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mencermati informasi yang dihasilkan dari asesmen serta mengarahkan penambahan data yang mendukung perkembangan belajar peserta didik.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menafsirkan kebutuhan informasi tentang perkembangan belajar peserta didik serta mengarahkan penyempurnaan rancangan asesmen.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Menggunakan berbagai teknik asesmen yang mendokumentasikan proses belajar peserta didik serta mengarahkan pemantauan perkembangan secara berkala.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menelaah efektivitas instrumen asesmen yang digunakan serta mengarahkan perbaikan berdasarkan kebutuhan pembelajaran.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merekonstruksi rancangan asesmen yang mendukung pemetaan perkembangan belajar peserta didik secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 27. Tindakan yang paling tepat dilakukan guru adalah ...',
                                'deskripsi' => 'Sekolah mendorong seluruh guru untuk merancang asesmen yang tidak hanya mengukur hasil belajar, tetapi juga membantu peserta didik memahami kekuatan dan kebutuhan belajarnya.',
                                'nama_field' => 'soal_027',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 1.3.1 — Perancangan Asesmen yang Berpusat pada Peserta Didik. Level HOTS: C6 – Kreasi. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Menemukan kebutuhan belajar peserta didik melalui asesmen serta mengarahkan pemanfaatan hasil asesmen dalam pembelajaran.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menjelaskan tujuan dan manfaat asesmen kepada peserta didik serta mengarahkan keterlibatan mereka dalam proses asesmen.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Melaksanakan perancangan asesmen yang memberi informasi tentang capaian belajar serta mengarahkan tindak lanjut pembelajaran.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Mengevaluasi kualitas rancangan asesmen yang digunakan serta mengarahkan penyempurnaan berdasarkan kebutuhan peserta didik.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merancang sistem asesmen berkelanjutan yang mendukung refleksi dan pengembangan diri peserta didik secara optimal.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => '1.3.2 Pelaksanaan Asesmen yang Berpusat pada Peserta Didik',
                        'kode_form' => 'FORM-PED-132',
                        'deskripsi' => 'Kompetensi Pedagogik — Indikator 1.1: Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                        'indikator_kode' => '1.1',
                        'indikator_label' => 'Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'is_scoreable' => true,
                        'urutan' => 10,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Soal 28. Berdasarkan kondisi tersebut, tindakan yang paling tepat dilakukan guru adalah ...',
                                'deskripsi' => 'Dalam pelaksanaan asesmen, seluruh peserta didik diberikan tugas yang sama dengan waktu yang sama. Namun, terdapat beberapa peserta didik yang membutuhkan cara berbeda untuk menunjukkan pemahamannya terhadap materi yang dipelajari.',
                                'nama_field' => 'soal_028',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 1.3.2 — Pelaksanaan Asesmen yang Berpusat pada Peserta Didik. Level HOTS: C4 – Analisis. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengamati kebutuhan peserta didik selama asesmen serta mengarahkan pelaksanaan asesmen yang lebih sesuai.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menggambarkan berbagai alternatif pelaksanaan asesmen kepada peserta didik serta mengarahkan keterlibatan mereka secara optimal.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengimplementasikan pelaksanaan asesmen yang memberikan kesempatan beragam kepada peserta didik serta mengarahkan mereka menunjukkan kompetensinya.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menganalisis kesesuaian pelaksanaan asesmen terhadap kebutuhan peserta didik serta mengarahkan penyesuaian yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengembangkan pelaksanaan asesmen yang fleksibel dan inklusif serta mengarahkan peserta didik mencapai performa terbaiknya.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 29. Tindakan yang paling tepat dilakukan guru adalah ...',
                                'deskripsi' => 'Guru telah melaksanakan asesmen proyek kepada peserta didik. Namun, hasil refleksi menunjukkan bahwa sebagian peserta didik kurang memahami kriteria keberhasilan yang digunakan dalam penilaian.',
                                'nama_field' => 'soal_029',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 1.3.2 — Pelaksanaan Asesmen yang Berpusat pada Peserta Didik. Level HOTS: C5 – Evaluasi. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengenali kendala peserta didik dalam memahami asesmen serta mengarahkan pemahaman terhadap kriteria yang digunakan.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menguraikan indikator keberhasilan asesmen kepada peserta didik serta mengarahkan pemahaman yang lebih jelas.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Menggunakan strategi pelaksanaan asesmen yang melibatkan peserta didik memahami kriteria penilaian serta mengarahkan partisipasi yang lebih aktif.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menilai efektivitas pelaksanaan asesmen yang telah dilakukan serta mengarahkan penyempurnaan proses asesmen berikutnya.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengintegrasikan keterlibatan peserta didik dalam seluruh proses asesmen serta mengarahkan terbentuknya kepemilikan terhadap pembelajaran.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 30. Tindakan yang paling tepat dilakukan guru adalah ...',
                                'deskripsi' => 'Sekolah berupaya membangun budaya asesmen yang tidak hanya berfungsi sebagai alat pengukuran, tetapi juga sebagai bagian dari proses belajar yang bermakna bagi peserta didik.',
                                'nama_field' => 'soal_030',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 1.3.2 — Pelaksanaan Asesmen yang Berpusat pada Peserta Didik. Level HOTS: C6 – Kreasi. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Menemukan peluang pemanfaatan asesmen dalam pembelajaran serta mengarahkan peserta didik memahami hasil yang diperoleh.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menafsirkan peran asesmen dalam mendukung pembelajaran peserta didik serta mengarahkan keterlibatan mereka secara aktif.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mempraktikkan pelaksanaan asesmen yang terhubung dengan proses pembelajaran serta mengarahkan refleksi terhadap hasil belajar.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Mengkaji efektivitas pelaksanaan asesmen dalam mendukung pembelajaran serta mengarahkan perbaikan yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Menciptakan budaya asesmen partisipatif yang mendorong refleksi dan perbaikan belajar secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => '1.3.3 Umpan Balik terhadap Peserta Didik Mengenai Pembelajarannya',
                        'kode_form' => 'FORM-PED-133',
                        'deskripsi' => 'Kompetensi Pedagogik — Indikator 1.1: Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                        'indikator_kode' => '1.1',
                        'indikator_label' => 'Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'is_scoreable' => true,
                        'urutan' => 11,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Soal 31. Berdasarkan kondisi tersebut, tindakan yang paling tepat dilakukan guru dalam memberikan umpan balik adalah ...',
                                'deskripsi' => 'Setelah mengoreksi hasil tugas peserta didik, guru hanya memberikan nilai angka tanpa penjelasan mengenai kelebihan maupun bagian yang masih perlu diperbaiki.',
                                'nama_field' => 'soal_031',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 1.3.3 — Umpan Balik terhadap Peserta Didik Mengenai Pembelajarannya. Level HOTS: C4 – Analisis. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengamati hasil belajar peserta didik serta mengarahkan perhatian pada aspek yang perlu ditingkatkan.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menjelaskan kekuatan dan kelemahan hasil belajar peserta didik serta mengarahkan langkah perbaikan yang dapat dilakukan.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengimplementasikan umpan balik yang spesifik terhadap hasil belajar peserta didik serta mengarahkan tindak lanjut pembelajaran.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menganalisis kebutuhan peserta didik berdasarkan hasil belajar serta mengarahkan umpan balik yang lebih bermakna.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengembangkan sistem umpan balik berkelanjutan yang mendukung refleksi dan peningkatan kualitas belajar peserta didik.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 32. Tindakan yang paling tepat dilakukan guru adalah ...',
                                'deskripsi' => 'Guru telah memberikan umpan balik tertulis pada tugas peserta didik. Namun, sebagian besar peserta didik hanya melihat nilai akhirnya tanpa memanfaatkan komentar yang diberikan.',
                                'nama_field' => 'soal_032',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 1.3.3 — Umpan Balik terhadap Peserta Didik Mengenai Pembelajarannya. Level HOTS: C5 – Evaluasi. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mencermati respons peserta didik terhadap umpan balik yang diberikan serta mengarahkan pemanfaatannya dalam belajar.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menggambarkan manfaat umpan balik bagi perkembangan belajar peserta didik serta mengarahkan penggunaannya secara lebih optimal.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Menggunakan strategi tindak lanjut terhadap umpan balik yang diberikan serta mengarahkan peserta didik melakukan perbaikan belajar.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menelaah efektivitas bentuk umpan balik yang digunakan serta mengarahkan penyempurnaan strategi pemberian umpan balik.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merekonstruksi mekanisme umpan balik yang melibatkan refleksi peserta didik serta mengarahkan perbaikan belajar secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 33. Tindakan yang paling tepat dilakukan guru adalah ...',
                                'deskripsi' => 'Sekolah ingin membangun budaya belajar yang menempatkan umpan balik sebagai bagian penting dari proses pengembangan kompetensi peserta didik, bukan sekadar pelengkap setelah penilaian.',
                                'nama_field' => 'soal_033',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 1.3.3 — Umpan Balik terhadap Peserta Didik Mengenai Pembelajarannya. Level HOTS: C6 – Kreasi. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengenali kebutuhan peserta didik melalui hasil belajar serta mengarahkan pemberian umpan balik yang relevan.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Mengemukakan tujuan pemberian umpan balik kepada peserta didik serta mengarahkan pemanfaatannya dalam proses belajar.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Melaksanakan pemberian umpan balik secara berkala kepada peserta didik serta mengarahkan tindak lanjut pembelajaran.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Mengevaluasi dampak umpan balik terhadap perkembangan peserta didik serta mengarahkan penyempurnaan strategi yang digunakan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merancang sistem umpan balik reflektif yang terintegrasi dalam pembelajaran serta mengarahkan pengembangan kompetensi secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => '1.3.4 Penyusunan Laporan Capaian Belajar Peserta Didik',
                        'kode_form' => 'FORM-PED-134',
                        'deskripsi' => 'Kompetensi Pedagogik — Indikator 1.1: Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                        'indikator_kode' => '1.1',
                        'indikator_label' => 'Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'is_scoreable' => true,
                        'urutan' => 12,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Soal 34. Berdasarkan kondisi tersebut, tindakan yang paling tepat dilakukan guru dalam menyusun laporan capaian belajar adalah ...',
                                'deskripsi' => 'Setelah menyelesaikan satu fase pembelajaran, guru menyusun laporan capaian belajar peserta didik yang hanya berisi nilai akhir. Orang tua menyampaikan kesulitan memahami perkembangan kompetensi dan kebutuhan belajar anak karena informasi yang tersedia sangat terbatas.',
                                'nama_field' => 'soal_034',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 1.3.4 — Penyusunan Laporan Capaian Belajar Peserta Didik. Level HOTS: C4 – Analisis. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengamati informasi hasil belajar yang tersedia serta mengarahkan penyajian data yang lebih mudah dipahami.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menguraikan capaian belajar peserta didik berdasarkan hasil asesmen serta mengarahkan pemahaman yang lebih jelas terhadap perkembangannya.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengimplementasikan penyusunan laporan yang memuat capaian dan kebutuhan belajar peserta didik serta mengarahkan tindak lanjut pembelajaran.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menganalisis kelengkapan informasi dalam laporan hasil belajar serta mengarahkan penyempurnaan pelaporan yang lebih informatif.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengembangkan sistem pelaporan yang menggambarkan perkembangan kompetensi peserta didik secara komprehensif dan berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 35. Tindakan yang paling tepat dilakukan guru untuk meningkatkan kualitas laporan capaian belajar adalah ...',
                                'deskripsi' => 'Guru telah menyusun laporan hasil belajar yang memuat nilai, deskripsi capaian, dan catatan perkembangan peserta didik. Namun, sebagian orang tua masih mengalami kesulitan memahami makna informasi yang disajikan dalam laporan tersebut.',
                                'nama_field' => 'soal_035',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 1.3.4 — Penyusunan Laporan Capaian Belajar Peserta Didik. Level HOTS: C5 – Evaluasi. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mencermati bagian laporan yang kurang dipahami orang tua serta mengarahkan penyajian informasi yang lebih jelas.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menafsirkan kebutuhan informasi orang tua terhadap perkembangan peserta didik serta mengarahkan penyempurnaan isi laporan.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Menggunakan format pelaporan yang lebih mudah dipahami orang tua serta mengarahkan pemanfaatan informasi dalam mendukung belajar anak.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menelaah efektivitas laporan yang telah disusun serta mengarahkan perbaikan berdasarkan kebutuhan pemangku kepentingan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merekonstruksi sistem pelaporan yang komunikatif dan berorientasi perkembangan peserta didik secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 36. Tindakan yang paling tepat dilakukan guru untuk mendukung tujuan tersebut adalah ...',
                                'deskripsi' => 'Sekolah sedang mengembangkan sistem pelaporan hasil belajar yang tidak hanya menunjukkan capaian akademik, tetapi juga memberikan gambaran perkembangan kompetensi dan karakter peserta didik secara utuh.',
                                'nama_field' => 'soal_036',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 1.3.4 — Penyusunan Laporan Capaian Belajar Peserta Didik. Level HOTS: C6 – Kreasi. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Menemukan informasi perkembangan peserta didik yang relevan serta mengarahkan penyajiannya dalam laporan hasil belajar.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menjelaskan berbagai aspek perkembangan peserta didik dalam laporan serta mengarahkan pemahaman terhadap capaian belajar.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Melaksanakan penyusunan laporan yang memuat berbagai aspek perkembangan peserta didik serta mengarahkan tindak lanjut pembelajaran.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Mengevaluasi kualitas informasi yang disajikan dalam laporan serta mengarahkan penyempurnaan pelaporan secara berkala.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merancang sistem pelaporan holistik yang mendukung pemantauan perkembangan peserta didik secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => '1.3.5 Komunikasi Laporan Capaian Belajar Peserta Didik',
                        'kode_form' => 'FORM-PED-135',
                        'deskripsi' => 'Kompetensi Pedagogik — Indikator 1.1: Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                        'indikator_kode' => '1.1',
                        'indikator_label' => 'Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'is_scoreable' => true,
                        'urutan' => 13,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Soal 37. Berdasarkan kondisi tersebut, tindakan yang paling tepat dilakukan guru dalam mengomunikasikan laporan capaian belajar adalah ...',
                                'deskripsi' => 'Dalam pertemuan dengan orang tua, guru hanya menyampaikan nilai hasil belajar peserta didik tanpa memberikan penjelasan mengenai kekuatan, tantangan, dan langkah pengembangan yang dapat dilakukan selanjutnya.',
                                'nama_field' => 'soal_037',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 1.3.5 — Komunikasi Laporan Capaian Belajar Peserta Didik. Level HOTS: C4 – Analisis. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengenali informasi penting yang perlu disampaikan kepada orang tua serta mengarahkan pemahaman terhadap perkembangan belajar peserta didik.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menggambarkan capaian dan kebutuhan belajar peserta didik kepada orang tua serta mengarahkan pemahaman yang lebih menyeluruh.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengimplementasikan komunikasi laporan yang memuat capaian dan tindak lanjut belajar peserta didik serta mengarahkan kolaborasi dengan orang tua.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menganalisis efektivitas komunikasi laporan yang dilakukan serta mengarahkan perbaikan penyampaian informasi yang lebih bermakna.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengembangkan komunikasi pelaporan yang membangun kemitraan berkelanjutan antara sekolah dan orang tua.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 38. Tindakan yang paling tepat dilakukan guru untuk meningkatkan efektivitas komunikasi laporan hasil belajar adalah ...',
                                'deskripsi' => 'Guru telah menyampaikan laporan hasil belajar kepada orang tua. Namun, sebagian orang tua menganggap laporan tersebut hanya sebagai informasi nilai dan belum memanfaatkannya untuk mendukung proses belajar anak di rumah.',
                                'nama_field' => 'soal_038',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 1.3.5 — Komunikasi Laporan Capaian Belajar Peserta Didik. Level HOTS: C5 – Evaluasi. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengamati respons orang tua terhadap laporan yang diberikan serta mengarahkan pemahaman terhadap manfaat informasi tersebut.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menguraikan makna informasi dalam laporan hasil belajar kepada orang tua serta mengarahkan pemanfaatannya dalam pendampingan belajar.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Menggunakan strategi komunikasi yang melibatkan diskusi mengenai perkembangan peserta didik serta mengarahkan kolaborasi yang lebih aktif.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menilai efektivitas komunikasi laporan yang telah dilakukan serta mengarahkan penyempurnaan pendekatan yang digunakan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengintegrasikan komunikasi pelaporan dengan program kemitraan sekolah dan keluarga serta mengarahkan dukungan belajar yang berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 39. Tindakan yang paling tepat dilakukan guru untuk mewujudkan tujuan tersebut adalah ...',
                                'deskripsi' => 'Sekolah ingin membangun budaya komunikasi yang menjadikan laporan hasil belajar sebagai sarana kolaborasi antara guru, peserta didik, dan orang tua dalam mendukung perkembangan peserta didik secara optimal.',
                                'nama_field' => 'soal_039',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 1.3.5 — Komunikasi Laporan Capaian Belajar Peserta Didik. Level HOTS: C6 – Kreasi. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Menemukan kebutuhan informasi para pemangku kepentingan serta mengarahkan penyampaian laporan yang lebih relevan.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Mengemukakan tujuan dan manfaat laporan hasil belajar kepada berbagai pihak serta mengarahkan pemahaman yang lebih baik.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mempraktikkan komunikasi laporan yang melibatkan dialog mengenai perkembangan peserta didik serta mengarahkan kolaborasi yang positif.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Mengkaji efektivitas berbagai bentuk komunikasi laporan yang digunakan serta mengarahkan penyempurnaan strategi pelaporan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Menciptakan sistem komunikasi pelaporan yang partisipatif dan berkelanjutan serta mengarahkan sinergi seluruh pihak dalam mendukung perkembangan peserta didik.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => '2.1.1 Makna, Tujuan, dan Pandangan Hidup Guru Berdasarkan Prinsip Moral dan Keyakinannya terhadap Tuhan Yang Maha Esa',
                        'kode_form' => 'FORM-PED-211',
                        'deskripsi' => 'Kompetensi Pedagogik — Indikator 1.1: Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                        'indikator_kode' => '1.1',
                        'indikator_label' => 'Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'is_scoreable' => true,
                        'urutan' => 14,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Soal 40. Berdasarkan situasi tersebut, tindakan yang paling tepat untuk memperkuat makna profesi guru adalah ...',
                                'deskripsi' => 'Seorang guru tetap melaksanakan tugasnya dengan penuh tanggung jawab meskipun menghadapi berbagai keterbatasan sarana dan tantangan dalam proses pembelajaran. Guru tersebut memandang profesinya bukan sekadar pekerjaan, tetapi juga sebagai bentuk pengabdian yang memberikan manfaat bagi peserta didik dan masyarakat.',
                                'nama_field' => 'soal_040',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 2.1.1 — Makna, Tujuan, dan Pandangan Hidup Guru Berdasarkan Prinsip Moral dan Keyakinannya terhadap Tuhan Yang Maha Esa. Level HOTS: C4 – Analisis. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengenali nilai-nilai yang mendasari profesi guru serta mengarahkan pelaksanaan tugas yang lebih bertanggung jawab.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menguraikan makna pengabdian dalam profesi guru serta mengarahkan pelaksanaan tugas sesuai nilai yang diyakini.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengimplementasikan nilai-nilai moral dalam pelaksanaan tugas sehari-hari serta mengarahkan pelayanan pendidikan yang lebih baik.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menganalisis keterkaitan antara nilai hidup dan praktik profesional guru serta mengarahkan penguatan komitmen profesi.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengembangkan orientasi hidup yang selaras dengan nilai moral dan spiritual serta mengarahkan praktik profesional yang berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 41. Tindakan yang paling tepat dilakukan guru dalam memaknai keberhasilan profesinya adalah ...',
                                'deskripsi' => 'Dalam sebuah diskusi profesi, beberapa guru memiliki pandangan yang berbeda mengenai makna keberhasilan dalam menjalankan tugas sebagai pendidik. Sebagian berfokus pada capaian akademik peserta didik, sementara yang lain menekankan pembentukan karakter dan nilai kehidupan.',
                                'nama_field' => 'soal_041',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 2.1.1 — Makna, Tujuan, dan Pandangan Hidup Guru Berdasarkan Prinsip Moral dan Keyakinannya terhadap Tuhan Yang Maha Esa. Level HOTS: C5 – Evaluasi. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mencermati berbagai pandangan tentang keberhasilan profesi guru serta mengarahkan pemahaman yang lebih luas.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menafsirkan makna keberhasilan profesi berdasarkan nilai moral yang diyakini serta mengarahkan praktik pendidikan yang positif.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Menggunakan nilai-nilai moral sebagai dasar dalam menjalankan tugas profesi serta mengarahkan perkembangan peserta didik secara utuh.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menelaah keselarasan antara tujuan hidup dan praktik profesi guru serta mengarahkan penguatan integritas diri.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merekonstruksi orientasi profesional berdasarkan nilai moral dan spiritual yang diyakini serta mengarahkan kebermanfaatan yang berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 42. Tindakan yang paling tepat dilakukan guru untuk mewujudkan tujuan tersebut adalah ...',
                                'deskripsi' => 'Sekolah mendorong guru untuk menjadi teladan yang tidak hanya unggul secara profesional, tetapi juga menunjukkan integritas moral dan spiritual dalam kehidupan sehari-hari.',
                                'nama_field' => 'soal_042',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 2.1.1 — Makna, Tujuan, dan Pandangan Hidup Guru Berdasarkan Prinsip Moral dan Keyakinannya terhadap Tuhan Yang Maha Esa. Level HOTS: C6 – Kreasi. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Menemukan nilai-nilai positif yang relevan dengan profesi guru serta mengarahkan penerapannya dalam kehidupan sehari-hari.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menjelaskan hubungan antara nilai moral dan tugas profesi guru serta mengarahkan penerapan yang lebih konsisten.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Melaksanakan tugas profesi berdasarkan nilai moral dan spiritual yang diyakini serta mengarahkan keteladanan bagi peserta didik.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Mengevaluasi keselarasan antara nilai yang diyakini dan perilaku profesional yang ditunjukkan serta mengarahkan perbaikan diri.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merancang pengembangan diri yang memadukan aspek moral, spiritual, dan profesional secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => '2.1.2 Pengelolaan Emosi dalam Menjalankan Peran sebagai Pendidik',
                        'kode_form' => 'FORM-PED-212',
                        'deskripsi' => 'Kompetensi Pedagogik — Indikator 1.1: Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                        'indikator_kode' => '1.1',
                        'indikator_label' => 'Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'is_scoreable' => true,
                        'urutan' => 15,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Soal 43. Berdasarkan situasi tersebut, tindakan yang paling tepat dilakukan guru dalam mengelola emosinya adalah ...',
                                'deskripsi' => 'Saat pembelajaran berlangsung, seorang peserta didik berulang kali mengabaikan arahan guru dan mengganggu teman-temannya. Kondisi tersebut membuat guru merasa kesal dan frustrasi karena kegiatan belajar menjadi terganggu.',
                                'nama_field' => 'soal_043',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 2.1.2 — Pengelolaan Emosi dalam Menjalankan Peran sebagai Pendidik. Level HOTS: C4 – Analisis. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengenali perasaan yang muncul dalam dirinya serta mengarahkan respons yang lebih terkendali terhadap situasi yang dihadapi.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menguraikan faktor yang memengaruhi munculnya emosi tersebut serta mengarahkan pengelolaan diri yang lebih positif.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengimplementasikan strategi pengendalian emosi dalam menghadapi situasi yang menantang serta mengarahkan penyelesaian masalah secara konstruktif.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menganalisis dampak emosi terhadap keputusan yang diambil serta mengarahkan pengelolaan emosi yang lebih efektif.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengembangkan kemampuan regulasi emosi yang mendukung pengambilan keputusan profesional secara konsisten.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 44. Tindakan yang paling tepat dilakukan guru dalam menghadapi situasi tersebut adalah ...',
                                'deskripsi' => 'Guru menerima kritik dari orang tua peserta didik terkait metode pembelajaran yang digunakan. Kritik tersebut disampaikan dengan nada yang kurang menyenangkan dan berpotensi menimbulkan ketegangan dalam komunikasi.',
                                'nama_field' => 'soal_044',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 2.1.2 — Pengelolaan Emosi dalam Menjalankan Peran sebagai Pendidik. Level HOTS: C5 – Evaluasi. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengamati respons emosional yang muncul dalam dirinya serta mengarahkan komunikasi yang tetap profesional.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menafsirkan isi kritik yang disampaikan orang tua secara objektif serta mengarahkan komunikasi yang lebih terbuka.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Menggunakan strategi komunikasi yang tenang dan terarah serta mengarahkan penyelesaian masalah secara kolaboratif.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menilai pengaruh emosi terhadap kualitas komunikasi yang dibangun serta mengarahkan perbaikan interaksi yang lebih efektif.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengintegrasikan kemampuan regulasi emosi dan komunikasi profesional dalam membangun hubungan yang konstruktif.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 45. Tindakan yang paling tepat dilakukan guru untuk menjaga kestabilan emosi dalam jangka panjang adalah ...',
                                'deskripsi' => 'Guru menghadapi berbagai tekanan pekerjaan, seperti tuntutan administrasi, target pembelajaran, serta kebutuhan peserta didik yang beragam. Kondisi tersebut berpotensi memengaruhi kualitas interaksi dan pengambilan keputusan dalam menjalankan tugas.',
                                'nama_field' => 'soal_045',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 2.1.2 — Pengelolaan Emosi dalam Menjalankan Peran sebagai Pendidik. Level HOTS: C6 – Kreasi. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Menemukan faktor-faktor yang memengaruhi kondisi emosinya serta mengarahkan pengelolaan diri yang lebih baik.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menggambarkan strategi menjaga keseimbangan emosi dalam bekerja serta mengarahkan penerapannya secara konsisten.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mempraktikkan teknik pengelolaan emosi dalam berbagai situasi kerja serta mengarahkan peningkatan kualitas interaksi profesional.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Mengkaji efektivitas strategi pengelolaan emosi yang digunakan serta mengarahkan penyempurnaan secara berkelanjutan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Menciptakan sistem pengembangan diri yang mendukung ketahanan emosional dan profesional secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => '2.1.3 Penerapan Kode Etik Guru dalam Bekerja dan Pembelajaran',
                        'kode_form' => 'FORM-PED-213',
                        'deskripsi' => 'Kompetensi Pedagogik — Indikator 1.1: Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                        'indikator_kode' => '1.1',
                        'indikator_label' => 'Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'is_scoreable' => true,
                        'urutan' => 16,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Soal 46. Berdasarkan kode etik guru, tindakan yang paling tepat dilakukan adalah ...',
                                'deskripsi' => 'Seorang guru memperoleh informasi pribadi mengenai kondisi keluarga peserta didik saat melakukan pendampingan. Informasi tersebut diketahui karena adanya kepercayaan yang diberikan oleh peserta didik kepada guru.',
                                'nama_field' => 'soal_046',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 2.1.3 — Penerapan Kode Etik Guru dalam Bekerja dan Pembelajaran. Level HOTS: C4 – Analisis. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengenali pentingnya menjaga kerahasiaan informasi peserta didik serta mengarahkan penggunaan informasi secara bertanggung jawab.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menguraikan prinsip kerahasiaan dalam profesi guru serta mengarahkan penerapan sesuai ketentuan yang berlaku.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengimplementasikan prinsip kode etik dalam pengelolaan informasi peserta didik serta mengarahkan perlindungan hak peserta didik.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menganalisis implikasi etis dari penggunaan informasi pribadi peserta didik serta mengarahkan pengambilan keputusan yang tepat.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengembangkan budaya profesional yang menjunjung tinggi etika dan kerahasiaan dalam lingkungan pendidikan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 47. Tindakan yang paling tepat dilakukan guru sesuai kode etik profesi adalah ...',
                                'deskripsi' => 'Dalam proses penilaian, guru menemukan bahwa salah satu peserta didik merupakan kerabat dekatnya. Guru menyadari bahwa kondisi tersebut berpotensi memengaruhi objektivitas dalam memberikan penilaian.',
                                'nama_field' => 'soal_047',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 2.1.3 — Penerapan Kode Etik Guru dalam Bekerja dan Pembelajaran. Level HOTS: C5 – Evaluasi. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mencermati potensi konflik kepentingan yang mungkin muncul serta mengarahkan pelaksanaan tugas secara objektif.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menjelaskan pentingnya prinsip keadilan dalam penilaian serta mengarahkan pelaksanaan tugas secara profesional.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Menggunakan prosedur penilaian yang transparan dan terukur serta mengarahkan objektivitas dalam pengambilan keputusan.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menelaah potensi bias dalam proses penilaian serta mengarahkan penerapan prinsip etika profesi secara konsisten.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merekonstruksi praktik penilaian yang menjamin akuntabilitas dan integritas profesional secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 48. Tindakan yang paling tepat dilakukan guru untuk mendukung upaya tersebut adalah ...',
                                'deskripsi' => 'Sekolah berupaya memperkuat budaya kerja profesional yang berlandaskan kode etik guru dalam seluruh aspek pelayanan pendidikan dan interaksi dengan warga sekolah.',
                                'nama_field' => 'soal_048',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 2.1.3 — Penerapan Kode Etik Guru dalam Bekerja dan Pembelajaran. Level HOTS: C6 – Kreasi. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengamati berbagai praktik profesional di lingkungan sekolah serta mengarahkan penerapan kode etik dalam bekerja.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Mengemukakan pentingnya kode etik dalam menjalankan profesi guru serta mengarahkan penerapannya secara konsisten.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Melaksanakan praktik kerja yang sesuai dengan kode etik profesi serta mengarahkan terciptanya budaya kerja yang positif.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Mengevaluasi kesesuaian praktik kerja dengan prinsip kode etik profesi serta mengarahkan perbaikan yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merancang penguatan budaya etis yang melibatkan seluruh warga sekolah serta mengarahkan profesionalisme yang berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => '2.2.1 Refleksi dan Perencanaan Kebutuhan Pengembangan Diri yang Berpusat pada Peserta Didik',
                        'kode_form' => 'FORM-PED-221',
                        'deskripsi' => 'Kompetensi Pedagogik — Indikator 1.1: Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                        'indikator_kode' => '1.1',
                        'indikator_label' => 'Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'is_scoreable' => true,
                        'urutan' => 17,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Soal 49. Berdasarkan kondisi tersebut, tindakan yang paling tepat dilakukan guru dalam melakukan refleksi pengembangan diri adalah ...',
                                'deskripsi' => 'Setelah melaksanakan pembelajaran selama satu semester, seorang guru menemukan bahwa sebagian peserta didik masih kurang aktif bertanya, berdiskusi, dan mengemukakan pendapat. Guru ingin meningkatkan kualitas pembelajaran agar lebih mampu memenuhi kebutuhan belajar peserta didik.',
                                'nama_field' => 'soal_049',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 2.2.1 — Refleksi dan Perencanaan Kebutuhan Pengembangan Diri yang Berpusat pada Peserta Didik. Level HOTS: C4 – Analisis. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengenali aspek pembelajaran yang perlu diperbaiki serta mengarahkan identifikasi kebutuhan pengembangan diri yang relevan.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menguraikan hubungan antara hasil pembelajaran dan kompetensi guru serta mengarahkan penentuan kebutuhan pengembangan diri.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengimplementasikan kegiatan refleksi secara terencana terhadap praktik pembelajaran serta mengarahkan penyusunan kebutuhan pengembangan diri.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menganalisis data hasil belajar dan pengalaman mengajar secara mendalam serta mengarahkan perencanaan pengembangan diri yang tepat.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengembangkan sistem refleksi berkelanjutan yang berfokus pada kebutuhan peserta didik serta mengarahkan peningkatan kompetensi profesional.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 50. Tindakan yang paling tepat dilakukan guru dalam merencanakan pengembangan dirinya adalah ...',
                                'deskripsi' => 'Guru telah mengikuti berbagai pelatihan dalam beberapa tahun terakhir. Namun, hasil evaluasi menunjukkan bahwa sebagian besar pelatihan yang diikuti belum memberikan dampak yang signifikan terhadap peningkatan kualitas pembelajaran di kelas.',
                                'nama_field' => 'soal_050',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 2.2.1 — Refleksi dan Perencanaan Kebutuhan Pengembangan Diri yang Berpusat pada Peserta Didik. Level HOTS: C5 – Evaluasi. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mencermati kesesuaian pelatihan yang pernah diikuti dengan kebutuhan pembelajaran serta mengarahkan perbaikan perencanaan pengembangan diri.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menafsirkan kebutuhan pengembangan diri berdasarkan tantangan pembelajaran yang dihadapi serta mengarahkan pemilihan kegiatan yang lebih relevan.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Menggunakan hasil refleksi pembelajaran sebagai dasar menentukan kegiatan pengembangan diri serta mengarahkan peningkatan kompetensi mengajar.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menelaah efektivitas program pengembangan diri yang telah diikuti serta mengarahkan penyusunan rencana yang lebih berdampak.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merekonstruksi perencanaan pengembangan diri berbasis kebutuhan peserta didik dan data pembelajaran secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 51. Tindakan yang paling tepat dilakukan guru untuk mewujudkan tujuan tersebut adalah ...',
                                'deskripsi' => 'Sekolah mendorong guru untuk membangun budaya refleksi yang menjadikan setiap pengalaman pembelajaran sebagai dasar dalam meningkatkan kualitas layanan kepada peserta didik.',
                                'nama_field' => 'soal_051',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 2.2.1 — Refleksi dan Perencanaan Kebutuhan Pengembangan Diri yang Berpusat pada Peserta Didik. Level HOTS: C6 – Kreasi. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Menemukan berbagai pengalaman pembelajaran yang perlu diperbaiki serta mengarahkan penyusunan rencana pengembangan diri.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menjelaskan hasil refleksi pembelajaran yang telah dilakukan serta mengarahkan penetapan prioritas pengembangan diri.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Melaksanakan refleksi pembelajaran secara berkala serta mengarahkan penyusunan langkah pengembangan diri yang relevan.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Mengevaluasi keterkaitan antara kebutuhan peserta didik dan kompetensi guru serta mengarahkan penyempurnaan rencana pengembangan diri.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merancang sistem refleksi dan perencanaan pengembangan diri yang berorientasi pada peningkatan kualitas pembelajaran peserta didik.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => '2.2.2 Cara Adaptif Melakukan Pengembangan Diri untuk Meningkatkan Pembelajaran yang Berpusat pada Peserta Didik',
                        'kode_form' => 'FORM-PED-222',
                        'deskripsi' => 'Kompetensi Pedagogik — Indikator 1.1: Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                        'indikator_kode' => '1.1',
                        'indikator_label' => 'Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'is_scoreable' => true,
                        'urutan' => 18,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Soal 52. Berdasarkan kondisi tersebut, tindakan yang paling tepat dilakukan guru adalah ...',
                                'deskripsi' => 'Seorang guru menyadari bahwa kebutuhan belajar peserta didik terus berkembang, sementara strategi pembelajaran yang digunakan cenderung sama dari tahun ke tahun. Guru ingin melakukan pengembangan diri agar pembelajaran menjadi lebih relevan.',
                                'nama_field' => 'soal_052',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 2.2.2 — Cara Adaptif Melakukan Pengembangan Diri untuk Meningkatkan Pembelajaran yang Berpusat pada Peserta Didik. Level HOTS: C4 – Analisis. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengamati perubahan kebutuhan belajar peserta didik serta mengarahkan pemilihan kegiatan pengembangan diri yang sesuai.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menggambarkan berbagai alternatif pengembangan diri yang tersedia serta mengarahkan peningkatan kompetensi yang dibutuhkan.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengimplementasikan kegiatan pengembangan diri yang relevan dengan kebutuhan pembelajaran serta mengarahkan peningkatan praktik mengajar.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menganalisis efektivitas berbagai pilihan pengembangan diri yang tersedia serta mengarahkan pemilihan strategi yang tepat.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengembangkan pola pengembangan diri yang adaptif terhadap perubahan kebutuhan peserta didik dan pembelajaran.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 53. Tindakan yang paling tepat dilakukan guru dalam menentukan strategi pengembangan diri adalah ...',
                                'deskripsi' => 'Guru memperoleh berbagai kesempatan pengembangan diri melalui pelatihan, komunitas belajar, webinar, dan pendampingan sejawat. Namun, waktu yang tersedia terbatas sehingga tidak semua kegiatan dapat diikuti.',
                                'nama_field' => 'soal_053',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 2.2.2 — Cara Adaptif Melakukan Pengembangan Diri untuk Meningkatkan Pembelajaran yang Berpusat pada Peserta Didik. Level HOTS: C5 – Evaluasi. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengenali peluang pengembangan diri yang tersedia serta mengarahkan pemilihan kegiatan yang mendukung pembelajaran.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menguraikan manfaat berbagai kegiatan pengembangan diri yang tersedia serta mengarahkan pemilihan kegiatan yang relevan.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Menggunakan kegiatan pengembangan diri yang sesuai kebutuhan pembelajaran serta mengarahkan peningkatan kualitas praktik mengajar.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menilai relevansi dan dampak berbagai kegiatan pengembangan diri serta mengarahkan pemilihan yang paling efektif.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengintegrasikan berbagai sumber pengembangan diri secara strategis serta mengarahkan peningkatan kompetensi secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 54. Tindakan yang paling tepat dilakukan guru untuk mendukung tujuan tersebut adalah ...',
                                'deskripsi' => 'Sekolah ingin membangun budaya belajar sepanjang hayat di kalangan guru agar mampu merespons perubahan kebutuhan peserta didik, kurikulum, dan perkembangan ilmu pengetahuan.',
                                'nama_field' => 'soal_054',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 2.2.2 — Cara Adaptif Melakukan Pengembangan Diri untuk Meningkatkan Pembelajaran yang Berpusat pada Peserta Didik. Level HOTS: C6 – Kreasi. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Menemukan peluang belajar yang dapat mendukung peningkatan kompetensi serta mengarahkan pemanfaatannya dalam pembelajaran.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menafsirkan kebutuhan pengembangan diri berdasarkan tantangan pembelajaran serta mengarahkan pemilihan sumber belajar yang sesuai.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mempraktikkan berbagai bentuk pengembangan diri yang relevan serta mengarahkan peningkatan kualitas pembelajaran peserta didik.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Mengkaji efektivitas strategi pengembangan diri yang digunakan serta mengarahkan penyempurnaan secara berkelanjutan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Menciptakan sistem pengembangan diri yang adaptif dan berkesinambungan serta mengarahkan peningkatan kualitas pembelajaran.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => '2.2.3 Penerapan Hasil Pengembangan Diri untuk Meningkatkan Pembelajaran Peserta Didik',
                        'kode_form' => 'FORM-PED-223',
                        'deskripsi' => 'Kompetensi Pedagogik — Indikator 1.1: Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                        'indikator_kode' => '1.1',
                        'indikator_label' => 'Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'is_scoreable' => true,
                        'urutan' => 19,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Soal 55. Berdasarkan kondisi tersebut, tindakan yang paling tepat dilakukan guru adalah ...',
                                'deskripsi' => 'Setelah mengikuti pelatihan tentang pembelajaran aktif, guru memperoleh berbagai strategi baru. Namun, sebagian strategi tersebut belum diterapkan dalam kegiatan pembelajaran sehari-hari.',
                                'nama_field' => 'soal_055',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 2.2.3 — Penerapan Hasil Pengembangan Diri untuk Meningkatkan Pembelajaran Peserta Didik. Level HOTS: C4 – Analisis. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengamati peluang penerapan hasil pelatihan dalam pembelajaran serta mengarahkan pemanfaatannya sesuai kebutuhan peserta didik.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menjelaskan manfaat hasil pengembangan diri terhadap pembelajaran serta mengarahkan penerapannya secara bertahap.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengimplementasikan hasil pengembangan diri dalam kegiatan pembelajaran serta mengarahkan peningkatan keterlibatan peserta didik.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menganalisis kesesuaian hasil pengembangan diri dengan kebutuhan pembelajaran serta mengarahkan penerapan yang lebih efektif.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengembangkan mekanisme penerapan hasil pengembangan diri yang berdampak pada peningkatan kualitas pembelajaran.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 56. Tindakan yang paling tepat dilakukan guru adalah ...',
                                'deskripsi' => 'Guru telah menerapkan strategi baru yang diperoleh dari kegiatan pengembangan diri. Setelah beberapa waktu, hasil belajar peserta didik menunjukkan peningkatan pada sebagian aspek, tetapi belum merata pada seluruh peserta didik.',
                                'nama_field' => 'soal_056',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 2.2.3 — Penerapan Hasil Pengembangan Diri untuk Meningkatkan Pembelajaran Peserta Didik. Level HOTS: C5 – Evaluasi. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mencermati dampak penerapan strategi baru terhadap pembelajaran serta mengarahkan perbaikan yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menggambarkan perubahan yang terjadi setelah penerapan strategi baru serta mengarahkan penyempurnaan praktik pembelajaran.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Menggunakan hasil evaluasi pembelajaran sebagai dasar perbaikan strategi serta mengarahkan peningkatan hasil belajar peserta didik.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menelaah efektivitas penerapan hasil pengembangan diri terhadap pembelajaran serta mengarahkan penyempurnaan yang lebih tepat.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merekonstruksi strategi pembelajaran berdasarkan hasil evaluasi dan pengembangan diri secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 57. Tindakan yang paling tepat dilakukan guru untuk mencapai tujuan tersebut adalah ...',
                                'deskripsi' => 'Sekolah berharap setiap kegiatan pengembangan diri yang diikuti guru dapat memberikan dampak nyata terhadap peningkatan kualitas pembelajaran dan hasil belajar peserta didik.',
                                'nama_field' => 'soal_057',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 2.2.3 — Penerapan Hasil Pengembangan Diri untuk Meningkatkan Pembelajaran Peserta Didik. Level HOTS: C6 – Kreasi. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengenali peluang penerapan hasil pengembangan diri dalam pembelajaran serta mengarahkan pemanfaatannya secara bertahap.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Mengemukakan hasil pengembangan diri yang relevan dengan kebutuhan pembelajaran serta mengarahkan penerapannya secara konsisten.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Melaksanakan penerapan hasil pengembangan diri dalam praktik pembelajaran serta mengarahkan peningkatan kualitas belajar peserta didik.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Mengevaluasi dampak penerapan hasil pengembangan diri terhadap pembelajaran serta mengarahkan penyempurnaan yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merancang sistem tindak lanjut hasil pengembangan diri yang terintegrasi dengan peningkatan kualitas pembelajaran peserta didik.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => '2.3.1 Interaksi Aktif dan Empatik terhadap Peserta Didik',
                        'kode_form' => 'FORM-PED-231',
                        'deskripsi' => 'Kompetensi Pedagogik — Indikator 1.1: Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                        'indikator_kode' => '1.1',
                        'indikator_label' => 'Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'is_scoreable' => true,
                        'urutan' => 20,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Soal 58. Sebagai guru yang berorientasi pada peserta didik, tindakan yang paling tepat dilakukan untuk memahami dan membantu Nisa adalah ...',
                                'deskripsi' => 'Dalam beberapa minggu terakhir, guru memperhatikan bahwa seorang peserta didik bernama Nisa yang sebelumnya aktif bertanya dan berdiskusi mulai menunjukkan perubahan perilaku. Nisa menjadi pendiam, jarang berpartisipasi dalam pembelajaran, dan sering terlihat murung saat berada di kelas. Ketika ditanya oleh teman-temannya, Nisa hanya menjawab singkat dan menghindari percakapan. Kondisi ini mulai memengaruhi keterlibatannya dalam kegiatan belajar.',
                                'nama_field' => 'soal_058',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 2.3.1 — Interaksi Aktif dan Empatik terhadap Peserta Didik. Level HOTS: C4 – Analisis, Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mencermati perubahan perilaku yang ditunjukkan Nisa serta mengarahkan interaksi yang mendukung keterlibatannya dalam pembelajaran.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menggali pengalaman belajar dan kondisi yang dirasakan Nisa melalui komunikasi yang terbuka serta mengarahkan dukungan yang sesuai.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Memfasilitasi percakapan yang hangat dan terarah dengan Nisa serta mengarahkan keterlibatannya kembali dalam aktivitas belajar.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menelaah berbagai faktor yang memengaruhi perubahan perilaku Nisa serta mengarahkan strategi pendampingan yang lebih tepat.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merancang dukungan pembelajaran yang melibatkan berbagai pihak terkait serta mengarahkan perkembangan Nisa secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 59. Tindakan yang paling tepat dilakukan guru untuk membangun interaksi yang aktif dan empatik adalah ...',
                                'deskripsi' => 'Dalam kegiatan diskusi kelompok, guru menemukan bahwa beberapa peserta didik kurang berani mengemukakan pendapat karena khawatir dianggap salah oleh teman-temannya. Akibatnya, hanya sebagian kecil peserta didik yang aktif berkontribusi dalam pembelajaran.',
                                'nama_field' => 'soal_059',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 2.3.1 — Interaksi Aktif dan Empatik terhadap Peserta Didik. Level HOTS: C5 – Evaluasi, Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengamati pola interaksi yang terjadi selama diskusi serta mengarahkan suasana yang lebih mendukung partisipasi peserta didik.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menguraikan pentingnya menghargai setiap pendapat dalam diskusi serta mengarahkan terbentuknya komunikasi yang positif.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengimplementasikan strategi diskusi yang memberi ruang aman bagi seluruh peserta didik serta mengarahkan partisipasi yang lebih aktif.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Mengevaluasi kualitas interaksi yang berkembang dalam kelompok serta mengarahkan perbaikan budaya komunikasi yang lebih inklusif.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengembangkan ekosistem pembelajaran dialogis yang menghargai keberagaman pandangan serta mengarahkan keterlibatan yang berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 60. Tindakan yang paling tepat dilakukan guru dalam program tersebut adalah ...',
                                'deskripsi' => 'Sekolah mengembangkan program "Kelas Peduli" yang bertujuan meningkatkan hubungan positif antara guru dan peserta didik. Guru diminta menyusun langkah konkret agar peserta didik merasa didengar, dihargai, dan didukung dalam proses pembelajaran.',
                                'nama_field' => 'soal_060',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 2.3.1 — Interaksi Aktif dan Empatik terhadap Peserta Didik. Level HOTS: C6 – Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengidentifikasi kebutuhan interaksi peserta didik di kelas serta mengarahkan komunikasi yang lebih terbuka.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menjelaskan pentingnya hubungan positif antara guru dan peserta didik serta mengarahkan keterlibatan yang lebih baik.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Menerapkan berbagai bentuk komunikasi yang responsif terhadap kebutuhan peserta didik serta mengarahkan partisipasi yang lebih optimal.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Mengkaji efektivitas pola interaksi yang dibangun di kelas serta mengarahkan penyempurnaan hubungan pembelajaran.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Menciptakan budaya interaksi yang empatik dan partisipatif secara berkelanjutan serta mengarahkan perkembangan peserta didik secara optimal.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => '2.3.2 Respek terhadap Hak Peserta Didik dalam Menjalankan Peran sebagai Guru',
                        'kode_form' => 'FORM-PED-232',
                        'deskripsi' => 'Kompetensi Pedagogik — Indikator 1.1: Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                        'indikator_kode' => '1.1',
                        'indikator_label' => 'Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'is_scoreable' => true,
                        'urutan' => 21,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Soal 61. Sebagai guru yang menghormati hak peserta didik, tindakan yang paling tepat dilakukan adalah ...',
                                'deskripsi' => 'Dalam sebuah pembelajaran, guru sering menunjuk peserta didik tertentu untuk menjawab pertanyaan karena dianggap lebih mampu dibandingkan teman-temannya. Akibatnya, beberapa peserta didik lain merasa kurang mendapatkan kesempatan untuk berpartisipasi dalam pembelajaran.',
                                'nama_field' => 'soal_061',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 2.3.2 — Respek terhadap Hak Peserta Didik dalam Menjalankan Peran sebagai Guru. Level HOTS: C4 – Analisis, Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengenali tingkat partisipasi peserta didik dalam pembelajaran serta mengarahkan kesempatan belajar yang lebih merata.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menggambarkan pentingnya hak setiap peserta didik untuk berpartisipasi serta mengarahkan keterlibatan yang lebih setara.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Menggunakan strategi pembelajaran yang memberi kesempatan setara kepada seluruh peserta didik serta mengarahkan partisipasi yang aktif.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menganalisis hambatan yang memengaruhi pemerataan partisipasi peserta didik serta mengarahkan perbaikan yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengembangkan sistem pembelajaran yang menjamin terpenuhinya hak partisipasi seluruh peserta didik secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 62. Tindakan yang paling tepat dilakukan guru untuk menghormati hak peserta didik dalam pembelajaran adalah ...',
                                'deskripsi' => 'Dalam kegiatan presentasi proyek, beberapa peserta didik menyampaikan bahwa mereka tidak memiliki kesempatan yang sama untuk memilih tema proyek yang sesuai dengan minat dan kemampuannya.',
                                'nama_field' => 'soal_062',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 2.3.2 — Respek terhadap Hak Peserta Didik dalam Menjalankan Peran sebagai Guru. Level HOTS: C5 – Evaluasi, Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mencermati kebutuhan dan minat peserta didik dalam kegiatan proyek serta mengarahkan pemberian pilihan yang lebih sesuai.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menafsirkan pentingnya pemberian ruang pilihan dalam pembelajaran serta mengarahkan keterlibatan peserta didik yang lebih optimal.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Memfasilitasi berbagai alternatif pilihan proyek bagi peserta didik serta mengarahkan pembelajaran yang lebih bermakna.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menilai kesesuaian desain pembelajaran terhadap hak dan kebutuhan peserta didik serta mengarahkan penyempurnaan yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengintegrasikan prinsip penghormatan terhadap hak peserta didik dalam seluruh proses pembelajaran serta mengarahkan kemandirian belajar.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 63. Tindakan yang paling tepat dilakukan guru adalah ...',
                                'deskripsi' => 'Sekolah sedang mengembangkan budaya pembelajaran yang menghargai keberagaman latar belakang, kemampuan, dan aspirasi peserta didik. Guru diminta merancang pembelajaran yang menjamin setiap peserta didik memperoleh kesempatan belajar yang adil.',
                                'nama_field' => 'soal_063',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 2.3.2 — Respek terhadap Hak Peserta Didik dalam Menjalankan Peran sebagai Guru. Level HOTS: C6 – Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengamati keragaman karakteristik peserta didik serta mengarahkan penyelenggaraan pembelajaran yang lebih inklusif.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menguraikan hak-hak peserta didik dalam pembelajaran kepada seluruh warga kelas serta mengarahkan penerapannya secara konsisten.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Melaksanakan pembelajaran yang memberi akses dan kesempatan belajar yang setara serta mengarahkan keterlibatan seluruh peserta didik.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Mengevaluasi tingkat pemenuhan hak peserta didik dalam pembelajaran serta mengarahkan perbaikan yang berkelanjutan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merancang sistem pembelajaran inklusif yang menjamin penghormatan terhadap hak peserta didik secara menyeluruh.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => '2.3.3 Kepedulian terhadap Keselamatan dan Keamanan Peserta Didik sebagai Individu dan Kelompok',
                        'kode_form' => 'FORM-PED-233',
                        'deskripsi' => 'Kompetensi Pedagogik — Indikator 1.1: Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                        'indikator_kode' => '1.1',
                        'indikator_label' => 'Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'is_scoreable' => true,
                        'urutan' => 22,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Soal 64. Tindakan yang paling tepat dilakukan guru adalah ...',
                                'deskripsi' => 'Saat kegiatan praktikum berlangsung, guru menemukan bahwa beberapa peserta didik menggunakan alat praktik tanpa memperhatikan prosedur keselamatan yang telah dijelaskan sebelumnya. Kondisi tersebut berpotensi menimbulkan risiko bagi diri sendiri maupun teman-temannya.',
                                'nama_field' => 'soal_064',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 2.3.3 — Kepedulian terhadap Keselamatan dan Keamanan Peserta Didik sebagai Individu dan Kelompok. Level HOTS: C4 – Analisis, Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengamati perilaku peserta didik selama kegiatan praktikum serta mengarahkan kepatuhan terhadap prosedur keselamatan.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menjelaskan kembali pentingnya prosedur keselamatan dalam kegiatan praktikum serta mengarahkan perilaku yang lebih aman.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengimplementasikan pengawasan dan pembimbingan selama praktikum serta mengarahkan penerapan prosedur keselamatan secara konsisten.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menganalisis faktor yang memengaruhi kepatuhan peserta didik terhadap prosedur keselamatan serta mengarahkan langkah pencegahan yang tepat.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengembangkan sistem budaya keselamatan yang melibatkan seluruh peserta didik secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 65. Tindakan yang paling tepat dilakukan guru adalah ...',
                                'deskripsi' => 'Guru menerima laporan bahwa beberapa peserta didik mengalami perundungan verbal di lingkungan sekolah. Meskipun kejadian tersebut tidak terjadi di dalam kelas, dampaknya mulai terlihat pada kepercayaan diri dan keterlibatan belajar peserta didik yang menjadi korban.',
                                'nama_field' => 'soal_065',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 2.3.3 — Kepedulian terhadap Keselamatan dan Keamanan Peserta Didik sebagai Individu dan Kelompok. Level HOTS: C5 – Evaluasi, Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengenali indikasi gangguan keamanan psikologis yang dialami peserta didik serta mengarahkan dukungan yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menggali informasi terkait kondisi yang dialami peserta didik secara hati-hati serta mengarahkan penanganan yang tepat.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Memfasilitasi langkah perlindungan dan pendampingan bagi peserta didik yang terdampak serta mengarahkan pemulihan kondisi belajar.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menelaah faktor penyebab dan dampak perundungan terhadap peserta didik serta mengarahkan strategi pencegahan yang lebih efektif.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Membangun sistem perlindungan dan budaya sekolah yang aman bagi seluruh peserta didik secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 66. Tindakan yang paling tepat dilakukan guru dalam merencanakan kegiatan tersebut adalah ...',
                                'deskripsi' => 'Sekolah berencana melaksanakan kegiatan pembelajaran luar kelas yang melibatkan seluruh peserta didik. Kegiatan tersebut memiliki potensi manfaat yang besar, namun juga mengandung berbagai risiko keselamatan dan keamanan yang perlu diantisipasi.',
                                'nama_field' => 'soal_066',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 2.3.3 — Kepedulian terhadap Keselamatan dan Keamanan Peserta Didik sebagai Individu dan Kelompok. Level HOTS: C6 – Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengidentifikasi potensi risiko yang mungkin terjadi selama kegiatan serta mengarahkan langkah antisipasi yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menggambarkan prosedur keselamatan yang harus dipatuhi peserta didik serta mengarahkan pelaksanaannya secara tertib.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengorganisasi pelaksanaan kegiatan sesuai prosedur keselamatan yang berlaku serta mengarahkan perlindungan peserta didik.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Mengkaji berbagai risiko dan kebutuhan pengamanan kegiatan secara menyeluruh serta mengarahkan strategi mitigasi yang tepat.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merancang sistem manajemen keselamatan yang melibatkan seluruh pihak terkait serta mengarahkan terciptanya lingkungan belajar yang aman.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => '3.1.1 Komunikasi Efektif dengan Warga Sekolah yang Mengarah pada Peningkatan Pembelajaran',
                        'kode_form' => 'FORM-PED-311',
                        'deskripsi' => 'Kompetensi Pedagogik — Indikator 1.1: Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                        'indikator_kode' => '1.1',
                        'indikator_label' => 'Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'is_scoreable' => true,
                        'urutan' => 23,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Soal 67. Sebagai guru, tindakan yang paling tepat dilakukan untuk membangun komunikasi yang mendukung peningkatan pembelajaran adalah ...',
                                'deskripsi' => 'Hasil asesmen menunjukkan bahwa kemampuan literasi peserta didik kelas V mengalami penurunan dalam dua semester terakhir. Guru kelas telah mencoba berbagai strategi pembelajaran, tetapi peningkatan yang terjadi belum signifikan. Kepala sekolah mendorong adanya kolaborasi antara guru kelas, guru mata pelajaran, pustakawan, dan tenaga kependidikan untuk mengatasi masalah tersebut.',
                                'nama_field' => 'soal_067',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 3.1.1 — Komunikasi Efektif dengan Warga Sekolah yang Mengarah pada Peningkatan Pembelajaran. Level HOTS: C4 – Analisis, Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengamati informasi yang dimiliki berbagai pihak di sekolah serta mengarahkan pertukaran informasi yang lebih terstruktur.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menguraikan permasalahan pembelajaran yang dihadapi kepada warga sekolah serta mengarahkan terbentuknya pemahaman bersama.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Memfasilitasi komunikasi yang melibatkan berbagai pihak terkait serta mengarahkan penyusunan langkah perbaikan pembelajaran.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menganalisis efektivitas komunikasi antarpihak dalam mendukung pembelajaran serta mengarahkan penguatan kolaborasi yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengembangkan sistem komunikasi kolaboratif yang mengintegrasikan peran warga sekolah serta mengarahkan peningkatan pembelajaran secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 68. Tindakan yang paling tepat dilakukan guru untuk meningkatkan kualitas komunikasi tersebut adalah ...',
                                'deskripsi' => 'Dalam rapat evaluasi pembelajaran, guru menemukan bahwa informasi mengenai perkembangan peserta didik sering kali tidak tersampaikan secara utuh antara guru kelas, guru mata pelajaran, dan guru BK. Akibatnya, tindak lanjut pembelajaran yang dilakukan menjadi kurang optimal.',
                                'nama_field' => 'soal_068',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 3.1.1 — Komunikasi Efektif dengan Warga Sekolah yang Mengarah pada Peningkatan Pembelajaran. Level HOTS: C5 – Evaluasi, Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengenali hambatan komunikasi yang terjadi antarwarga sekolah serta mengarahkan pertukaran informasi yang lebih efektif.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menafsirkan kebutuhan informasi yang diperlukan oleh setiap pihak serta mengarahkan komunikasi yang lebih terbuka.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengimplementasikan mekanisme berbagi informasi yang teratur antarwarga sekolah serta mengarahkan koordinasi pembelajaran yang lebih baik.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menelaah kualitas komunikasi yang telah berlangsung serta mengarahkan perbaikan berdasarkan kebutuhan pembelajaran peserta didik.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merekonstruksi sistem komunikasi profesional yang mendukung pengambilan keputusan pembelajaran secara kolaboratif.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 69. Tindakan yang paling tepat dilakukan guru untuk mendukung keberhasilan program tersebut adalah ...',
                                'deskripsi' => 'Sekolah sedang menjalankan program peningkatan numerasi yang melibatkan guru berbagai mata pelajaran, wali kelas, dan orang tua. Program tersebut membutuhkan komunikasi yang efektif agar seluruh pihak dapat menjalankan perannya secara optimal.',
                                'nama_field' => 'soal_069',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 3.1.1 — Komunikasi Efektif dengan Warga Sekolah yang Mengarah pada Peningkatan Pembelajaran. Level HOTS: C6 – Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengidentifikasi informasi penting yang perlu dikomunikasikan kepada berbagai pihak serta mengarahkan pemahaman yang sama.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menjelaskan tujuan dan peran setiap pihak dalam program numerasi serta mengarahkan keterlibatan yang lebih aktif.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengorganisasi komunikasi rutin dengan pihak terkait serta mengarahkan pelaksanaan program yang lebih terkoordinasi.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Mengevaluasi efektivitas pola komunikasi yang digunakan dalam program serta mengarahkan penyempurnaan yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merancang strategi komunikasi multipihak yang mendukung keberlanjutan program peningkatan numerasi secara menyeluruh.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => '3.1.2 Pengorganisasian Tugas-Tugas Bersama Rekan Sejawat untuk Peningkatan Pembelajaran',
                        'kode_form' => 'FORM-PED-312',
                        'deskripsi' => 'Kompetensi Pedagogik — Indikator 1.1: Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                        'indikator_kode' => '1.1',
                        'indikator_label' => 'Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'is_scoreable' => true,
                        'urutan' => 24,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Soal 70. Tindakan yang paling tepat dilakukan guru untuk mendukung keberhasilan kerja tim adalah ...',
                                'deskripsi' => 'Tim guru di sebuah sekolah sedang menyusun proyek lintas mata pelajaran. Namun, pembagian tugas yang kurang jelas menyebabkan beberapa pekerjaan dilakukan secara tumpang tindih, sementara tugas lain justru tidak terlaksana.',
                                'nama_field' => 'soal_070',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 3.1.2 — Pengorganisasian Tugas-Tugas Bersama Rekan Sejawat untuk Peningkatan Pembelajaran. Level HOTS: C4 – Analisis, Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengamati pembagian tugas yang telah ditetapkan serta mengarahkan pelaksanaan tugas yang lebih terkoordinasi.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menggambarkan peran dan tanggung jawab setiap anggota tim serta mengarahkan pelaksanaan tugas yang lebih jelas.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengimplementasikan pembagian tugas yang sesuai dengan kompetensi anggota tim serta mengarahkan pencapaian tujuan bersama.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menganalisis efektivitas pengorganisasian tugas dalam tim serta mengarahkan perbaikan mekanisme kerja yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengembangkan sistem kolaborasi yang mengoptimalkan kontribusi seluruh anggota tim serta mengarahkan peningkatan kualitas pembelajaran.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 71. Tindakan yang paling tepat dilakukan guru adalah ...',
                                'deskripsi' => 'Dalam pelaksanaan program sekolah, beberapa guru merasa beban kerja tidak terbagi secara proporsional. Kondisi ini mulai memengaruhi motivasi dan kualitas kerja sama tim dalam mendukung kegiatan pembelajaran.',
                                'nama_field' => 'soal_071',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 3.1.2 — Pengorganisasian Tugas-Tugas Bersama Rekan Sejawat untuk Peningkatan Pembelajaran. Level HOTS: C5 – Evaluasi, Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mencermati distribusi tugas yang berjalan dalam tim serta mengarahkan pelaksanaan tugas yang lebih seimbang.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menguraikan kebutuhan dan kapasitas kerja anggota tim serta mengarahkan pembagian tugas yang lebih proporsional.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Memfasilitasi penyesuaian pembagian tugas berdasarkan kesepakatan bersama serta mengarahkan kerja sama yang lebih efektif.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menilai efektivitas pengelolaan tugas dalam tim serta mengarahkan penyempurnaan mekanisme kolaborasi yang digunakan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengintegrasikan sistem pengorganisasian kerja yang adaptif dan transparan serta mengarahkan pencapaian tujuan bersama secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 72. Tindakan yang paling tepat dilakukan guru dalam mendukung pelaksanaan program tersebut adalah ...',
                                'deskripsi' => 'Sekolah merencanakan kegiatan Projek Penguatan Profil Pelajar Pancasila yang melibatkan seluruh guru. Program ini memerlukan koordinasi yang baik agar setiap tahapan kegiatan dapat berjalan sesuai tujuan yang telah ditetapkan.',
                                'nama_field' => 'soal_072',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 3.1.2 — Pengorganisasian Tugas-Tugas Bersama Rekan Sejawat untuk Peningkatan Pembelajaran. Level HOTS: C6 – Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengidentifikasi tugas yang perlu dilaksanakan dalam program serta mengarahkan pembagian kerja yang lebih terstruktur.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menafsirkan hubungan antar tugas dalam program yang akan dilaksanakan serta mengarahkan koordinasi yang lebih baik.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Menggunakan mekanisme kerja tim yang terencana dalam pelaksanaan program serta mengarahkan keterlibatan seluruh anggota tim.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Mengkaji efektivitas pengorganisasian tugas selama program berlangsung serta mengarahkan perbaikan yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Menciptakan sistem kolaborasi yang mendukung koordinasi lintas peran secara berkelanjutan serta mengarahkan keberhasilan program.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => '3.1.3 Inisiatif Berkontribusi untuk Mencapai Tujuan Bersama dalam Peningkatan Pembelajaran',
                        'kode_form' => 'FORM-PED-313',
                        'deskripsi' => 'Kompetensi Pedagogik — Indikator 1.1: Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                        'indikator_kode' => '1.1',
                        'indikator_label' => 'Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'is_scoreable' => true,
                        'urutan' => 25,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Soal 73. Tindakan yang paling tepat dilakukan guru untuk menunjukkan kontribusi dalam mencapai tujuan bersama adalah ...',
                                'deskripsi' => 'Hasil evaluasi sekolah menunjukkan bahwa kemampuan literasi peserta didik masih berada di bawah target yang ditetapkan. Sekolah telah membentuk tim peningkatan literasi, namun sebagian besar anggota tim masih menunggu arahan sebelum mengambil tindakan.',
                                'nama_field' => 'soal_073',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 3.1.3 — Inisiatif Berkontribusi untuk Mencapai Tujuan Bersama dalam Peningkatan Pembelajaran. Level HOTS: C4 – Analisis, Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengamati kebutuhan yang perlu ditindaklanjuti dalam program literasi serta mengarahkan dukungan sesuai perannya.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menggali peluang kontribusi yang dapat dilakukan dalam program literasi serta mengarahkan keterlibatan yang lebih aktif.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengimplementasikan langkah-langkah yang mendukung program literasi sesuai tugas yang diberikan serta mengarahkan pencapaian target program.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menganalisis kebutuhan program dan potensi kontribusi yang dapat diberikan serta mengarahkan dukungan yang lebih berdampak.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengembangkan inisiatif kolaboratif yang mendorong keterlibatan berbagai pihak serta mengarahkan peningkatan literasi secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 74. Tindakan yang paling tepat dilakukan guru adalah ...',
                                'deskripsi' => 'Dalam komunitas belajar guru, berbagai ide perbaikan pembelajaran telah dihasilkan. Namun, sebagian besar ide tersebut belum diwujudkan menjadi tindakan nyata karena belum ada pihak yang mengoordinasikan tindak lanjutnya.',
                                'nama_field' => 'soal_074',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 3.1.3 — Inisiatif Berkontribusi untuk Mencapai Tujuan Bersama dalam Peningkatan Pembelajaran. Level HOTS: C5 – Evaluasi, Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengenali peluang tindak lanjut dari hasil diskusi komunitas belajar serta mengarahkan implementasi yang memungkinkan.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menguraikan langkah-langkah yang dapat dilakukan berdasarkan hasil diskusi serta mengarahkan keterlibatan rekan sejawat.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Memfasilitasi pelaksanaan tindak lanjut hasil komunitas belajar bersama rekan sejawat serta mengarahkan pencapaian tujuan bersama.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menelaah potensi dampak dari berbagai alternatif tindak lanjut serta mengarahkan keputusan yang lebih efektif.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Membangun gerakan kolaboratif yang menghubungkan ide, aksi, dan evaluasi secara berkelanjutan untuk meningkatkan pembelajaran.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 75. Tindakan yang paling tepat dilakukan guru untuk mendukung keberhasilan target sekolah adalah ...',
                                'deskripsi' => 'Sekolah menetapkan target peningkatan kualitas pembelajaran berbasis proyek. Untuk mencapai target tersebut, seluruh guru didorong untuk berkontribusi tidak hanya dalam pelaksanaan, tetapi juga dalam pengembangan dan penyempurnaan program.',
                                'nama_field' => 'soal_075',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 3.1.3 — Inisiatif Berkontribusi untuk Mencapai Tujuan Bersama dalam Peningkatan Pembelajaran. Level HOTS: C6 – Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengidentifikasi area yang membutuhkan dukungan dalam program sekolah serta mengarahkan kontribusi sesuai kapasitasnya.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menjelaskan gagasan yang dapat mendukung peningkatan kualitas program serta mengarahkan kolaborasi dengan rekan sejawat.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Melaksanakan kontribusi aktif dalam pengembangan program pembelajaran serta mengarahkan pencapaian target yang ditetapkan.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Mengevaluasi efektivitas kontribusi yang telah diberikan terhadap program sekolah serta mengarahkan penyempurnaan yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merancang inovasi kolaboratif yang mendukung pencapaian tujuan sekolah secara berkelanjutan serta mengarahkan peningkatan mutu pembelajaran.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => '3.2.1 Pendampingan Orang Tua/Wali dalam Mendukung Pembelajaran di Rumah yang Berpusat pada Peserta Didik',
                        'kode_form' => 'FORM-PED-321',
                        'deskripsi' => 'Kompetensi Pedagogik — Indikator 1.1: Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                        'indikator_kode' => '1.1',
                        'indikator_label' => 'Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'is_scoreable' => true,
                        'urutan' => 26,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Soal 76. Sebagai guru, tindakan yang paling tepat dilakukan untuk memperkuat peran orang tua dalam mendukung pembelajaran di rumah adalah ...',
                                'deskripsi' => 'Hasil evaluasi menunjukkan bahwa sebagian peserta didik sering tidak menyelesaikan tugas belajar mandiri di rumah. Setelah dilakukan penelusuran, guru menemukan bahwa sebagian orang tua belum memahami cara mendampingi anak belajar tanpa mengambil alih pekerjaan yang seharusnya dilakukan oleh peserta didik.',
                                'nama_field' => 'soal_076',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 3.2.1 — Pendampingan Orang Tua/Wali dalam Mendukung Pembelajaran di Rumah yang Berpusat pada Peserta Didik. Level HOTS: C4 – Analisis, Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengamati pola pendampingan belajar yang dilakukan orang tua serta mengarahkan pemahaman yang lebih sesuai terhadap kebutuhan peserta didik.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menguraikan peran orang tua dalam mendukung kemandirian belajar anak serta mengarahkan praktik pendampingan yang lebih tepat.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Memfasilitasi komunikasi dengan orang tua mengenai strategi pendampingan belajar serta mengarahkan keterlibatan yang lebih efektif.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menganalisis faktor-faktor yang memengaruhi efektivitas pendampingan belajar di rumah serta mengarahkan solusi yang lebih sesuai.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengembangkan program kemitraan pembelajaran rumah dan sekolah yang memperkuat kemandirian belajar peserta didik secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 77. Tindakan yang paling tepat dilakukan guru untuk meningkatkan efektivitas pendampingan orang tua adalah ...',
                                'deskripsi' => 'Guru telah memberikan panduan belajar kepada orang tua melalui grup komunikasi kelas. Namun, hasil monitoring menunjukkan bahwa tingkat keterlibatan orang tua dalam mendampingi proses belajar anak masih sangat beragam.',
                                'nama_field' => 'soal_077',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 3.2.1 — Pendampingan Orang Tua/Wali dalam Mendukung Pembelajaran di Rumah yang Berpusat pada Peserta Didik. Level HOTS: C5 – Evaluasi, Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengenali variasi keterlibatan orang tua dalam mendukung pembelajaran serta mengarahkan komunikasi yang lebih responsif.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menafsirkan kebutuhan dan kendala yang dihadapi orang tua dalam mendampingi anak belajar serta mengarahkan dukungan yang sesuai.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Menggunakan berbagai bentuk komunikasi dan pendampingan kepada orang tua serta mengarahkan keterlibatan yang lebih aktif.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menelaah efektivitas strategi pelibatan orang tua yang telah diterapkan serta mengarahkan penyempurnaan pendekatan yang digunakan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merekonstruksi sistem pendampingan orang tua berbasis kebutuhan keluarga dan perkembangan peserta didik secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 78. Tindakan yang paling tepat dilakukan guru adalah ...',
                                'deskripsi' => 'Sekolah mengembangkan program "Belajar Bersama Keluarga" yang bertujuan meningkatkan keterlibatan orang tua dalam mendukung pembelajaran yang berpusat pada peserta didik. Guru diminta merancang strategi agar program tersebut berdampak nyata terhadap perkembangan belajar peserta didik.',
                                'nama_field' => 'soal_078',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 3.2.1 — Pendampingan Orang Tua/Wali dalam Mendukung Pembelajaran di Rumah yang Berpusat pada Peserta Didik. Level HOTS: C6 – Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengidentifikasi kebutuhan pendampingan belajar peserta didik di rumah serta mengarahkan keterlibatan orang tua yang relevan.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menjelaskan bentuk dukungan yang dapat diberikan orang tua dalam pembelajaran serta mengarahkan penerapannya secara bertahap.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengimplementasikan kegiatan kolaboratif yang melibatkan orang tua dalam proses belajar serta mengarahkan dukungan yang lebih bermakna.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Mengevaluasi dampak keterlibatan orang tua terhadap perkembangan belajar peserta didik serta mengarahkan perbaikan program yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merancang ekosistem kemitraan sekolah dan keluarga yang mendukung pembelajaran peserta didik secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => '3.2.2 Pelibatan Pengetahuan, Keahlian, dan Perspektif Orang Tua/Wali dan Masyarakat dalam Pembelajaran yang Berpusat pada Peserta Didik',
                        'kode_form' => 'FORM-PED-322',
                        'deskripsi' => 'Kompetensi Pedagogik — Indikator 1.1: Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                        'indikator_kode' => '1.1',
                        'indikator_label' => 'Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'is_scoreable' => true,
                        'urutan' => 27,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Soal 79. Sebagai guru, tindakan yang paling tepat dilakukan untuk memperkaya pengalaman belajar peserta didik adalah ...',
                                'deskripsi' => 'Dalam pembelajaran tentang kewirausahaan, guru menyadari bahwa materi yang diberikan masih didominasi contoh-contoh dari buku teks. Padahal, banyak orang tua peserta didik dan anggota masyarakat sekitar yang memiliki pengalaman usaha yang relevan dan dapat menjadi sumber belajar yang autentik.',
                                'nama_field' => 'soal_079',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 3.2.2 — Pelibatan Pengetahuan, Keahlian, dan Perspektif Orang Tua/Wali dan Masyarakat dalam Pembelajaran yang Berpusat pada Peserta Didik. Level HOTS: C4 – Analisis, Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengamati potensi sumber belajar yang tersedia di lingkungan sekitar serta mengarahkan pemanfaatannya dalam pembelajaran.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menggambarkan kontribusi yang dapat diberikan oleh orang tua dan masyarakat dalam pembelajaran serta mengarahkan keterlibatan yang sesuai.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Menggunakan pengalaman dan keahlian masyarakat sebagai sumber belajar kontekstual serta mengarahkan pembelajaran yang lebih bermakna.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menganalisis relevansi pengetahuan masyarakat terhadap tujuan pembelajaran serta mengarahkan pelibatan yang lebih efektif.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengembangkan kemitraan pembelajaran yang mengintegrasikan sumber daya masyarakat dalam pengalaman belajar peserta didik.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 80. Tindakan yang paling tepat dilakukan guru untuk meningkatkan kualitas pelibatan tersebut adalah ...',
                                'deskripsi' => 'Sekolah telah beberapa kali melibatkan orang tua sebagai narasumber dalam kegiatan pembelajaran. Namun, keterlibatan tersebut masih bersifat insidental dan belum terhubung secara sistematis dengan tujuan pembelajaran yang ingin dicapai.',
                                'nama_field' => 'soal_080',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 3.2.2 — Pelibatan Pengetahuan, Keahlian, dan Perspektif Orang Tua/Wali dan Masyarakat dalam Pembelajaran yang Berpusat pada Peserta Didik. Level HOTS: C5 – Evaluasi, Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mencermati bentuk keterlibatan yang telah dilakukan oleh orang tua dan masyarakat serta mengarahkan pemanfaatannya secara lebih terencana.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menguraikan hubungan antara kontribusi narasumber dan tujuan pembelajaran serta mengarahkan pelibatan yang lebih relevan.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Memfasilitasi pelibatan orang tua dan masyarakat sesuai kebutuhan pembelajaran serta mengarahkan keterhubungan dengan pengalaman belajar peserta didik.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menilai efektivitas kontribusi orang tua dan masyarakat terhadap pembelajaran serta mengarahkan penyempurnaan strategi pelibatan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengintegrasikan pengetahuan dan keahlian masyarakat dalam desain pembelajaran yang mendukung capaian belajar secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 81. Tindakan yang paling tepat dilakukan guru untuk mendukung keberhasilan program tersebut adalah ...',
                                'deskripsi' => 'Sekolah mengembangkan proyek pembelajaran berbasis potensi lokal yang melibatkan peserta didik, orang tua, dan masyarakat. Program ini bertujuan agar peserta didik memperoleh pengalaman belajar yang lebih kontekstual, autentik, dan relevan dengan kehidupan nyata.',
                                'nama_field' => 'soal_081',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 3.2.2 — Pelibatan Pengetahuan, Keahlian, dan Perspektif Orang Tua/Wali dan Masyarakat dalam Pembelajaran yang Berpusat pada Peserta Didik. Level HOTS: C6 – Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengidentifikasi potensi pengetahuan dan keahlian masyarakat yang relevan serta mengarahkan pemanfaatannya dalam pembelajaran.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menafsirkan kontribusi yang dapat diberikan oleh orang tua dan masyarakat terhadap pembelajaran serta mengarahkan pelibatan yang lebih terstruktur.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengorganisasi keterlibatan orang tua dan masyarakat dalam kegiatan proyek serta mengarahkan pengalaman belajar yang lebih autentik.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Mengkaji dampak pelibatan berbagai pihak terhadap kualitas pembelajaran serta mengarahkan penguatan kolaborasi yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Menciptakan model kemitraan pembelajaran berbasis komunitas yang mendukung perkembangan kompetensi peserta didik secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => '3.3.1 Berpartisipasi pada Beragam Peran untuk Pemecahan Masalah Pembelajaran dalam Organisasi Profesi dan Jejaring yang Lebih Luas',
                        'kode_form' => 'FORM-PED-331',
                        'deskripsi' => 'Kompetensi Pedagogik — Indikator 1.1: Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                        'indikator_kode' => '1.1',
                        'indikator_label' => 'Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'is_scoreable' => true,
                        'urutan' => 28,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Soal 82. Sebagai anggota organisasi profesi, tindakan yang paling tepat dilakukan guru adalah ...',
                                'deskripsi' => 'Dalam forum komunitas belajar guru tingkat kabupaten, berbagai sekolah melaporkan permasalahan yang sama, yaitu rendahnya kemampuan peserta didik dalam menyusun argumen dan mengemukakan pendapat secara kritis. Forum tersebut mengundang guru untuk berkontribusi mencari solusi yang dapat diterapkan di berbagai satuan pendidikan.',
                                'nama_field' => 'soal_082',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 3.3.1 — Berpartisipasi pada Beragam Peran untuk Pemecahan Masalah Pembelajaran dalam Organisasi Profesi dan Jejaring yang Lebih Luas. Level HOTS: C4 – Analisis, Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengamati berbagai permasalahan pembelajaran yang dibahas dalam forum serta mengarahkan pemahaman terhadap isu yang dihadapi bersama.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menguraikan pengalaman yang relevan dengan permasalahan pembelajaran tersebut serta mengarahkan pertukaran gagasan yang lebih bermakna.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Berkontribusi dalam diskusi pemecahan masalah pembelajaran bersama anggota forum serta mengarahkan penyusunan alternatif solusi.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menganalisis akar penyebab permasalahan pembelajaran berdasarkan berbagai perspektif serta mengarahkan pengembangan solusi yang lebih efektif.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengembangkan inisiatif kolaboratif lintas sekolah untuk mengatasi permasalahan pembelajaran secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 83. Tindakan yang paling tepat dilakukan guru dalam situasi tersebut adalah ...',
                                'deskripsi' => 'Sebuah organisasi profesi guru sedang merancang program peningkatan literasi. Dalam proses perencanaan, muncul berbagai usulan kegiatan dengan pendekatan yang berbeda-beda. Organisasi membutuhkan masukan yang didasarkan pada kebutuhan nyata peserta didik dan pengalaman praktik di sekolah.',
                                'nama_field' => 'soal_083',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 3.3.1 — Berpartisipasi pada Beragam Peran untuk Pemecahan Masalah Pembelajaran dalam Organisasi Profesi dan Jejaring yang Lebih Luas. Level HOTS: C5 – Evaluasi, Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mencermati berbagai usulan program yang disampaikan dalam organisasi serta mengarahkan perhatian pada kebutuhan pembelajaran peserta didik.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menafsirkan keterkaitan antara usulan program dan kebutuhan peserta didik serta mengarahkan pengambilan keputusan yang lebih tepat.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Menggunakan pengalaman pembelajaran di sekolah sebagai bahan pertimbangan dalam forum serta mengarahkan penyempurnaan program.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menelaah efektivitas berbagai alternatif program yang diusulkan serta mengarahkan pemilihan strategi yang lebih berdampak.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merekonstruksi rancangan program berbasis data dan kolaborasi lintas pemangku kepentingan untuk meningkatkan kualitas pembelajaran.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 84. Tindakan yang paling tepat dilakukan guru untuk mendukung keberhasilan program tersebut adalah ...',
                                'deskripsi' => 'Jejaring guru antarwilayah mengembangkan program kolaboratif untuk meningkatkan kualitas pembelajaran berbasis proyek. Setiap anggota diminta mengambil peran sesuai kapasitasnya agar program dapat memberikan manfaat bagi lebih banyak peserta didik.',
                                'nama_field' => 'soal_084',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 3.3.1 — Berpartisipasi pada Beragam Peran untuk Pemecahan Masalah Pembelajaran dalam Organisasi Profesi dan Jejaring yang Lebih Luas. Level HOTS: C6 – Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengidentifikasi kebutuhan program yang memerlukan dukungan anggota jejaring serta mengarahkan kontribusi sesuai peran yang dimiliki.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menjelaskan kemampuan dan pengalaman yang dapat dibagikan dalam program jejaring serta mengarahkan keterlibatan yang lebih aktif.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengorganisasi pelaksanaan tugas yang menjadi tanggung jawabnya dalam program serta mengarahkan pencapaian tujuan bersama.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Mengkaji peluang penguatan kolaborasi antaranggota jejaring serta mengarahkan peningkatan kualitas program yang dijalankan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merancang model kolaborasi lintas jejaring yang mendukung penyelesaian masalah pembelajaran secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => '3.3.2 Berbagi Praktik Baik dan Karya untuk Peningkatan Pembelajaran yang Berpusat pada Peserta Didik dalam Organisasi dan Jejaring yang Lebih Luas',
                        'kode_form' => 'FORM-PED-332',
                        'deskripsi' => 'Kompetensi Pedagogik — Indikator 1.1: Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                        'indikator_kode' => '1.1',
                        'indikator_label' => 'Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'is_scoreable' => true,
                        'urutan' => 29,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Soal 85. Tindakan yang paling tepat dilakukan guru dalam situasi tersebut adalah ...',
                                'deskripsi' => 'Seorang guru berhasil meningkatkan keterlibatan peserta didik melalui strategi pembelajaran yang dikembangkan di kelasnya. Hasilnya menunjukkan peningkatan partisipasi dan kualitas diskusi peserta didik. Komunitas guru di wilayahnya meminta guru tersebut berbagi pengalaman agar dapat menjadi inspirasi bagi sekolah lain.',
                                'nama_field' => 'soal_085',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 3.3.2 — Berbagi Praktik Baik dan Karya untuk Peningkatan Pembelajaran yang Berpusat pada Peserta Didik dalam Organisasi dan Jejaring yang Lebih Luas. Level HOTS: C4 – Analisis, Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengamati aspek-aspek praktik pembelajaran yang memberikan dampak positif serta mengarahkan identifikasi pengalaman yang layak dibagikan.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menggambarkan proses dan hasil praktik pembelajaran yang telah dilakukan serta mengarahkan pemahaman rekan sejawat terhadap praktik tersebut.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mempresentasikan praktik baik yang telah diterapkan kepada komunitas profesi serta mengarahkan pertukaran pengalaman pembelajaran.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menganalisis faktor-faktor yang mendukung keberhasilan praktik baik tersebut serta mengarahkan adaptasi pada konteks yang berbeda.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengembangkan model diseminasi praktik baik yang memungkinkan replikasi dan pengembangan di berbagai satuan pendidikan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 86. Tindakan yang paling tepat dilakukan guru saat berbagi praktik baik dalam forum tersebut adalah ...',
                                'deskripsi' => 'Dalam forum berbagi praktik baik, guru menemukan bahwa suatu strategi pembelajaran yang berhasil diterapkan di satu sekolah belum tentu memberikan hasil yang sama ketika diterapkan di sekolah lain dengan karakteristik peserta didik yang berbeda.',
                                'nama_field' => 'soal_086',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 3.3.2 — Berbagi Praktik Baik dan Karya untuk Peningkatan Pembelajaran yang Berpusat pada Peserta Didik dalam Organisasi dan Jejaring yang Lebih Luas. Level HOTS: C5 – Evaluasi, Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengenali karakteristik konteks yang memengaruhi keberhasilan suatu praktik pembelajaran serta mengarahkan pemahaman yang lebih realistis.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menguraikan faktor-faktor yang mendukung keberhasilan praktik baik yang dibagikan serta mengarahkan penyesuaian pada konteks yang berbeda.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Memfasilitasi diskusi mengenai kemungkinan adaptasi praktik baik pada berbagai kondisi sekolah serta mengarahkan penerapan yang lebih relevan.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menilai keterterapan praktik baik berdasarkan karakteristik peserta didik dan lingkungan belajar serta mengarahkan modifikasi yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengintegrasikan hasil refleksi berbagai sekolah untuk menghasilkan praktik pembelajaran yang lebih adaptif dan berdampak luas.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 87. Tindakan yang paling tepat dilakukan guru untuk mendukung inisiatif tersebut adalah ...',
                                'deskripsi' => 'Organisasi profesi guru sedang membangun bank praktik baik dan karya inovatif yang dapat diakses oleh guru dari berbagai daerah. Setiap anggota didorong untuk tidak hanya membagikan pengalaman, tetapi juga menghasilkan karya yang dapat dimanfaatkan oleh komunitas pendidikan secara lebih luas.',
                                'nama_field' => 'soal_087',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 3.3.2 — Berbagi Praktik Baik dan Karya untuk Peningkatan Pembelajaran yang Berpusat pada Peserta Didik dalam Organisasi dan Jejaring yang Lebih Luas. Level HOTS: C6 – Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengidentifikasi praktik dan karya yang memiliki potensi untuk dibagikan kepada komunitas profesi serta mengarahkan dokumentasi yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menafsirkan nilai manfaat dari praktik dan karya yang dimiliki bagi peningkatan pembelajaran serta mengarahkan penyebarluasan yang tepat.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Menyebarluaskan praktik baik dan karya yang relevan kepada jejaring profesi serta mengarahkan pemanfaatannya dalam pembelajaran.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Mengevaluasi dampak praktik baik dan karya yang telah dibagikan terhadap peningkatan kualitas pembelajaran serta mengarahkan penyempurnaan lebih lanjut.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Menciptakan sistem berbagi pengetahuan dan karya inovatif yang mendukung pembelajaran berpusat pada peserta didik secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => '4.1.1 Struktur dan Alur Pengetahuan dari Suatu Bidang Keilmuan yang Relevan untuk Pembelajaran',
                        'kode_form' => 'FORM-PED-411',
                        'deskripsi' => 'Kompetensi Pedagogik — Indikator 1.1: Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                        'indikator_kode' => '1.1',
                        'indikator_label' => 'Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'is_scoreable' => true,
                        'urutan' => 30,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Soal 88. Tindakan yang paling tepat dilakukan guru untuk mengatasi permasalahan tersebut adalah ...',
                                'deskripsi' => 'Seorang guru IPA menemukan bahwa banyak peserta didik mengalami kesulitan memahami konsep ekosistem. Setelah dianalisis, guru menyadari bahwa sebagian peserta didik belum memahami konsep-konsep dasar seperti makhluk hidup, kebutuhan hidup, dan hubungan antarorganisme yang seharusnya menjadi landasan sebelum mempelajari ekosistem secara utuh.',
                                'nama_field' => 'soal_088',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 4.1.1 — Struktur dan Alur Pengetahuan dari Suatu Bidang Keilmuan yang Relevan untuk Pembelajaran. Level HOTS: C4 – Analisis, Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengamati keterkaitan antar konsep dalam materi pembelajaran serta mengarahkan peserta didik memahami konsep yang mendasari pembelajaran.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menguraikan hubungan antara konsep prasyarat dan konsep yang dipelajari serta mengarahkan pembelajaran yang lebih sistematis.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Menggunakan urutan penyajian materi yang sesuai dengan struktur keilmuan serta mengarahkan pemahaman peserta didik secara bertahap.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menganalisis keterhubungan antar konsep dalam bidang keilmuan serta mengarahkan penyusunan alur pembelajaran yang lebih efektif.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengembangkan peta struktur pengetahuan yang terintegrasi untuk mendukung pembelajaran yang bermakna dan berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 89. Tindakan yang paling tepat dilakukan guru untuk memperkuat pemahaman konseptual peserta didik adalah ...',
                                'deskripsi' => 'Dalam refleksi pembelajaran matematika, guru menemukan bahwa peserta didik mampu menghafal rumus, tetapi kesulitan menjelaskan alasan di balik penggunaan rumus tersebut dalam pemecahan masalah.',
                                'nama_field' => 'soal_089',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 4.1.1 — Struktur dan Alur Pengetahuan dari Suatu Bidang Keilmuan yang Relevan untuk Pembelajaran. Level HOTS: C5 – Evaluasi, Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengenali hubungan antara konsep dan prosedur dalam materi pembelajaran serta mengarahkan pemahaman yang lebih utuh.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menafsirkan posisi setiap konsep dalam struktur bidang keilmuan serta mengarahkan pembelajaran yang lebih bermakna.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Memfasilitasi pembelajaran yang menghubungkan konsep dan prosedur secara terarah serta mengarahkan pemahaman yang lebih mendalam.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menelaah kesesuaian alur pembelajaran dengan struktur keilmuan yang dipelajari serta mengarahkan perbaikan yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merekonstruksi pengalaman belajar berdasarkan struktur konseptual bidang ilmu sehingga peserta didik membangun pemahaman yang mendalam.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 90. Tindakan yang paling tepat dilakukan guru dalam merancang pembelajaran tersebut adalah ...',
                                'deskripsi' => 'Sekolah mengembangkan pembelajaran lintas mata pelajaran yang menuntut peserta didik memahami hubungan antara berbagai konsep dari beberapa disiplin ilmu untuk menyelesaikan masalah nyata.',
                                'nama_field' => 'soal_090',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 4.1.1 — Struktur dan Alur Pengetahuan dari Suatu Bidang Keilmuan yang Relevan untuk Pembelajaran. Level HOTS: C6 – Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengidentifikasi konsep-konsep utama yang terkait dengan topik pembelajaran serta mengarahkan keterhubungan antarkonsep.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menggambarkan alur perkembangan konsep dalam bidang ilmu yang dipelajari serta mengarahkan pemahaman yang lebih sistematis.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengorganisasi penyajian konsep sesuai hubungan keilmuannya serta mengarahkan pembelajaran yang lebih terstruktur.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Mengkaji keterkaitan berbagai konsep lintas disiplin ilmu serta mengarahkan integrasi yang lebih bermakna.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merancang kerangka pembelajaran yang menghubungkan struktur berbagai bidang ilmu untuk menyelesaikan masalah autentik.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => '4.1.2 Identifikasi Pengetahuan Konten yang Relevan untuk Mencapai Tujuan Pembelajaran',
                        'kode_form' => 'FORM-PED-412',
                        'deskripsi' => 'Kompetensi Pedagogik — Indikator 1.1: Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                        'indikator_kode' => '1.1',
                        'indikator_label' => 'Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'is_scoreable' => true,
                        'urutan' => 31,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Soal 91. Tindakan yang paling tepat dilakukan guru adalah ...',
                                'deskripsi' => 'Guru akan mengajarkan topik perubahan iklim kepada peserta didik. Materi yang tersedia sangat luas, sementara waktu pembelajaran terbatas. Guru perlu menentukan konten yang paling penting agar tujuan pembelajaran dapat tercapai secara optimal.',
                                'nama_field' => 'soal_091',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 4.1.2 — Identifikasi Pengetahuan Konten yang Relevan untuk Mencapai Tujuan Pembelajaran. Level HOTS: C4 – Analisis, Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengamati berbagai materi yang tersedia serta mengarahkan pemilihan konten yang mendukung tujuan pembelajaran.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menguraikan keterkaitan antara tujuan pembelajaran dan materi yang tersedia serta mengarahkan prioritas pembelajaran.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Memilih konten yang relevan dengan capaian pembelajaran serta mengarahkan fokus pembelajaran pada kompetensi yang dituju.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menganalisis tingkat relevansi berbagai konten terhadap tujuan pembelajaran serta mengarahkan seleksi materi yang lebih tepat.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengembangkan kerangka pemilihan konten berbasis tujuan dan kebutuhan peserta didik untuk mendukung pembelajaran yang optimal.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 92. Tindakan yang paling tepat dilakukan guru dalam menentukan konten pembelajaran adalah ...',
                                'deskripsi' => 'Dalam rapat MGMP, beberapa guru menggunakan materi tambahan yang berbeda-beda untuk mencapai tujuan pembelajaran yang sama. Setiap materi memiliki kelebihan dan keterbatasan masing-masing.',
                                'nama_field' => 'soal_092',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 4.1.2 — Identifikasi Pengetahuan Konten yang Relevan untuk Mencapai Tujuan Pembelajaran. Level HOTS: C5 – Evaluasi, Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mencermati karakteristik berbagai materi yang tersedia serta mengarahkan pemilihan yang sesuai dengan kebutuhan belajar.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menafsirkan kontribusi setiap materi terhadap pencapaian tujuan pembelajaran serta mengarahkan pemanfaatan yang tepat.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Menggunakan konten yang paling mendukung pencapaian kompetensi peserta didik serta mengarahkan pembelajaran yang lebih efektif.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menilai relevansi dan kedalaman konten terhadap tujuan pembelajaran serta mengarahkan keputusan yang lebih tepat.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengintegrasikan berbagai sumber konten yang saling melengkapi untuk mendukung pencapaian tujuan pembelajaran secara optimal.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 93. Tindakan yang paling tepat dilakukan guru adalah ...',
                                'deskripsi' => 'Guru merancang proyek pembelajaran yang menuntut peserta didik menghasilkan solusi terhadap masalah lingkungan di sekitar sekolah. Agar proyek berhasil, guru harus menentukan pengetahuan yang benar-benar dibutuhkan peserta didik.',
                                'nama_field' => 'soal_093',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 4.1.2 — Identifikasi Pengetahuan Konten yang Relevan untuk Mencapai Tujuan Pembelajaran. Level HOTS: C6 – Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengidentifikasi pengetahuan yang berkaitan dengan permasalahan yang dipelajari serta mengarahkan pemilihan materi yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menggambarkan hubungan antara konten pembelajaran dan tujuan proyek serta mengarahkan fokus pembelajaran yang relevan.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengorganisasi konten yang mendukung penyelesaian proyek peserta didik serta mengarahkan pencapaian tujuan pembelajaran.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Mengevaluasi kesesuaian konten yang dipilih dengan kebutuhan proyek dan peserta didik serta mengarahkan penyempurnaan yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merancang pemetaan konten pembelajaran yang terintegrasi dengan tujuan proyek dan kebutuhan belajar peserta didik.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => '4.1.3 Pengorganisasian Pengetahuan Konten yang Relevan terhadap Pembelajaran',
                        'kode_form' => 'FORM-PED-413',
                        'deskripsi' => 'Kompetensi Pedagogik — Indikator 1.1: Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                        'indikator_kode' => '1.1',
                        'indikator_label' => 'Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'is_scoreable' => true,
                        'urutan' => 32,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Soal 94. Tindakan yang paling tepat dilakukan guru adalah ...',
                                'deskripsi' => 'Guru telah menentukan materi yang akan diajarkan, namun peserta didik masih kesulitan memahami hubungan antara konsep-konsep yang dipelajari karena penyajiannya kurang terstruktur dan tidak menunjukkan keterkaitan yang jelas.',
                                'nama_field' => 'soal_094',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 4.1.3 — Pengorganisasian Pengetahuan Konten yang Relevan terhadap Pembelajaran. Level HOTS: C4 – Analisis, Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengamati keterhubungan konsep yang akan dipelajari serta mengarahkan penyusunan materi yang lebih sistematis.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menguraikan hubungan antar konsep dalam materi pembelajaran serta mengarahkan penyajian yang lebih terstruktur.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Menyusun urutan konten berdasarkan hubungan konsep yang dipelajari serta mengarahkan pemahaman yang lebih runtut.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menganalisis efektivitas organisasi materi terhadap pemahaman peserta didik serta mengarahkan perbaikan yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengembangkan struktur konten yang memfasilitasi keterhubungan konsep dan pembelajaran bermakna secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 95. Tindakan yang paling tepat dilakukan guru adalah ...',
                                'deskripsi' => 'Dalam evaluasi pembelajaran, guru menemukan bahwa peserta didik memahami bagian-bagian materi secara terpisah, tetapi mengalami kesulitan ketika diminta menghubungkan berbagai konsep untuk memecahkan masalah.',
                                'nama_field' => 'soal_095',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 4.1.3 — Pengorganisasian Pengetahuan Konten yang Relevan terhadap Pembelajaran. Level HOTS: C5 – Evaluasi, Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengenali pola pemahaman peserta didik terhadap materi yang dipelajari serta mengarahkan keterhubungan antar konsep.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menafsirkan hubungan konsep yang belum dipahami peserta didik secara utuh serta mengarahkan penguatan pembelajaran.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Memfasilitasi pengorganisasian konsep melalui aktivitas pembelajaran yang terarah serta mengarahkan pemahaman yang lebih terpadu.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menelaah efektivitas organisasi konten yang digunakan dalam pembelajaran serta mengarahkan penyempurnaan yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merekonstruksi organisasi pengetahuan yang mendukung integrasi konsep dan penerapannya dalam berbagai konteks pembelajaran.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 96. Tindakan yang paling tepat dilakukan guru untuk mendukung keberhasilan pembelajaran tersebut dalah …',
                                'deskripsi' => 'Sekolah mengembangkan pembelajaran berbasis proyek yang menuntut peserta didik mengintegrasikan berbagai konsep untuk menghasilkan dalah terhadap masalah nyata di lingkungan sekitar.',
                                'nama_field' => 'soal_096',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 4.1.3 — Pengorganisasian Pengetahuan Konten yang Relevan terhadap Pembelajaran. Level HOTS: C6 – Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengidentifikasi konsep-konsep yang diperlukan dalam proyek serta mengarahkan keterhubungan materi yang dipelajari.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menjelaskan hubungan antara berbagai konsep yang digunakan dalam proyek serta mengarahkan pemahaman yang lebih menyeluruh.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengorganisasi konten pembelajaran sesuai kebutuhan proyek peserta didik serta mengarahkan pencapaian tujuan pembelajaran.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Mengkaji efektivitas organisasi pengetahuan dalam mendukung penyelesaian proyek serta mengarahkan penyempurnaan yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Menciptakan kerangka integrasi pengetahuan yang mendukung pemecahan masalah autentik secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => '4.2.1 Tahapan Perkembangan dan Karakteristik yang Relevan dengan Kebutuhan Belajar',
                        'kode_form' => 'FORM-PED-421',
                        'deskripsi' => 'Kompetensi Pedagogik — Indikator 1.1: Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                        'indikator_kode' => '1.1',
                        'indikator_label' => 'Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'is_scoreable' => true,
                        'urutan' => 33,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Soal 97. Tindakan yang paling tepat dilakukan guru untuk memperbaiki pembelajaran tersebut adalah ...',
                                'deskripsi' => 'Seorang guru kelas IV SD memberikan tugas abstrak yang menuntut peserta didik menganalisis berbagai kemungkinan solusi dari suatu masalah sosial yang kompleks. Namun, sebagian besar peserta didik mengalami kesulitan memahami instruksi dan tidak mampu menyelesaikan tugas secara optimal. Setelah direfleksikan, guru menyadari bahwa tugas yang diberikan belum sepenuhnya mempertimbangkan tahapan perkembangan peserta didik.',
                                'nama_field' => 'soal_097',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 4.2.1 — Tahapan Perkembangan dan Karakteristik yang Relevan dengan Kebutuhan Belajar. Level HOTS: C4 – Analisis, Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengamati karakteristik perkembangan peserta didik yang terlibat dalam pembelajaran serta mengarahkan penyesuaian aktivitas belajar yang lebih sesuai.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menggambarkan keterkaitan antara tahapan perkembangan dan tuntutan pembelajaran serta mengarahkan pemilihan aktivitas yang lebih tepat.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Menyesuaikan kegiatan pembelajaran berdasarkan karakteristik perkembangan peserta didik serta mengarahkan keterlibatan belajar yang lebih optimal.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menganalisis kesesuaian antara tuntutan tugas dan tahapan perkembangan peserta didik serta mengarahkan perbaikan desain pembelajaran.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengembangkan kerangka pembelajaran yang mengintegrasikan karakteristik perkembangan peserta didik secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 98. Tindakan yang paling tepat dilakukan guru untuk merespons kondisi tersebut adalah ...',
                                'deskripsi' => 'Dalam satu jenjang kelas, guru menemukan bahwa kemampuan berpikir, pengendalian diri, dan kemandirian belajar peserta didik berkembang dengan kecepatan yang berbeda-beda. Kondisi tersebut menyebabkan sebagian peserta didik merasa tugas terlalu mudah, sementara yang lain merasa terlalu sulit.',
                                'nama_field' => 'soal_098',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 4.2.1 — Tahapan Perkembangan dan Karakteristik yang Relevan dengan Kebutuhan Belajar. Level HOTS: C5 – Evaluasi, Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengenali variasi perkembangan peserta didik yang muncul dalam pembelajaran serta mengarahkan penyesuaian dukungan belajar yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menafsirkan pengaruh perbedaan perkembangan terhadap proses belajar peserta didik serta mengarahkan pengelolaan pembelajaran yang lebih tepat.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Memfasilitasi pengalaman belajar yang mempertimbangkan variasi perkembangan peserta didik serta mengarahkan keterlibatan belajar yang lebih optimal.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menelaah efektivitas strategi pembelajaran terhadap kebutuhan perkembangan peserta didik serta mengarahkan penyempurnaan yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merekonstruksi pendekatan pembelajaran yang adaptif terhadap keragaman perkembangan peserta didik secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 99. Tindakan yang paling tepat dilakukan guru adalah ...',
                                'deskripsi' => 'Sekolah sedang mengembangkan proyek pembelajaran lintas mata pelajaran yang akan diikuti oleh peserta didik dari berbagai tingkat perkembangan kemampuan. Guru diminta merancang aktivitas yang tetap menantang sekaligus sesuai dengan karakteristik perkembangan peserta didik.',
                                'nama_field' => 'soal_099',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 4.2.1 — Tahapan Perkembangan dan Karakteristik yang Relevan dengan Kebutuhan Belajar. Level HOTS: C6 – Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengidentifikasi karakteristik perkembangan peserta didik yang terlibat dalam proyek serta mengarahkan penyesuaian aktivitas yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menguraikan kebutuhan belajar berdasarkan tahapan perkembangan peserta didik serta mengarahkan perencanaan kegiatan yang sesuai.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengorganisasi aktivitas proyek yang mempertimbangkan karakteristik perkembangan peserta didik serta mengarahkan keterlibatan yang bermakna.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Mengkaji kesesuaian rancangan proyek terhadap kebutuhan perkembangan peserta didik serta mengarahkan penyempurnaan yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merancang pengalaman belajar proyek yang responsif terhadap perkembangan peserta didik dan mendukung pertumbuhan kompetensi secara menyeluruh.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => '4.2.2 Latar Belakang Sosial, Budaya, Agama, dan Ekonomi yang Relevan dengan Kebutuhan Belajar Peserta Didik',
                        'kode_form' => 'FORM-PED-422',
                        'deskripsi' => 'Kompetensi Pedagogik — Indikator 1.1: Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                        'indikator_kode' => '1.1',
                        'indikator_label' => 'Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'is_scoreable' => true,
                        'urutan' => 34,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Soal 100. Tindakan yang paling tepat dilakukan guru adalah ...',
                                'deskripsi' => 'Dalam satu kelas terdapat peserta didik yang berasal dari latar belakang sosial, budaya, agama, dan kondisi ekonomi yang beragam. Guru menemukan bahwa beberapa contoh dan sumber belajar yang digunakan kurang relevan dengan pengalaman hidup sebagian peserta didik sehingga keterlibatan mereka dalam pembelajaran menjadi rendah.',
                                'nama_field' => 'soal_100',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 4.2.2 — Latar Belakang Sosial, Budaya, Agama, dan Ekonomi yang Relevan dengan Kebutuhan Belajar Peserta Didik. Level HOTS: C4 – Analisis, Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengamati keragaman latar belakang peserta didik yang ada di kelas serta mengarahkan pemilihan pengalaman belajar yang lebih relevan.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menggambarkan pengaruh latar belakang peserta didik terhadap pengalaman belajar mereka serta mengarahkan penyesuaian pembelajaran yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengakomodasi keragaman latar belakang peserta didik dalam kegiatan pembelajaran serta mengarahkan keterlibatan belajar yang lebih inklusif.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menganalisis hubungan antara latar belakang peserta didik dan efektivitas pembelajaran serta mengarahkan perbaikan yang lebih tepat.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengembangkan pembelajaran yang responsif terhadap keragaman sosial, budaya, agama, dan ekonomi peserta didik secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 101. Tindakan yang paling tepat dilakukan guru sebelum menetapkan tugas tersebut adalah ...',
                                'deskripsi' => 'Guru berencana memberikan tugas proyek yang mengharuskan peserta didik menggunakan perangkat digital dan akses internet di rumah. Namun, hasil survei menunjukkan bahwa kondisi ekonomi dan akses teknologi peserta didik sangat beragam.',
                                'nama_field' => 'soal_101',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 4.2.2 — Latar Belakang Sosial, Budaya, Agama, dan Ekonomi yang Relevan dengan Kebutuhan Belajar Peserta Didik. Level HOTS: C5 – Evaluasi, Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mencermati kondisi peserta didik yang berkaitan dengan pelaksanaan tugas serta mengarahkan penyesuaian yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menafsirkan dampak keragaman kondisi ekonomi terhadap akses pembelajaran peserta didik serta mengarahkan alternatif yang lebih inklusif.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Memfasilitasi pilihan penyelesaian tugas yang mempertimbangkan kondisi peserta didik serta mengarahkan kesempatan belajar yang setara.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menilai kesesuaian rancangan tugas terhadap keragaman latar belakang peserta didik serta mengarahkan penyempurnaan yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengintegrasikan prinsip keadilan akses belajar dalam desain pembelajaran sehingga seluruh peserta didik dapat berpartisipasi secara optimal.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 102. Tindakan yang paling tepat dilakukan guru adalah ...',
                                'deskripsi' => 'Sekolah mengembangkan proyek pembelajaran berbasis budaya lokal yang melibatkan peserta didik dari berbagai latar belakang. Guru perlu memastikan bahwa kegiatan tersebut dapat menjadi sarana belajar yang inklusif dan menghargai keberagaman.',
                                'nama_field' => 'soal_102',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 4.2.2 — Latar Belakang Sosial, Budaya, Agama, dan Ekonomi yang Relevan dengan Kebutuhan Belajar Peserta Didik. Level HOTS: C6 – Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengidentifikasi keragaman latar belakang peserta didik yang terlibat dalam proyek serta mengarahkan pembelajaran yang lebih menghargai perbedaan.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menjelaskan pentingnya menghargai keragaman dalam kegiatan proyek kepada peserta didik serta mengarahkan interaksi yang positif.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengorganisasi kegiatan proyek yang memberi ruang bagi berbagai perspektif peserta didik serta mengarahkan kolaborasi yang inklusif.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Mengevaluasi keterlibatan peserta didik dari berbagai latar belakang dalam proyek serta mengarahkan perbaikan yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Menciptakan lingkungan pembelajaran yang menjadikan keragaman sebagai sumber belajar yang memperkaya pengalaman peserta didik.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => '4.2.3 Potensi, Minat, dan Cara Belajar Peserta Didik yang Relevan dengan Kebutuhan Belajar Peserta Didik',
                        'kode_form' => 'FORM-PED-423',
                        'deskripsi' => 'Kompetensi Pedagogik — Indikator 1.1: Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                        'indikator_kode' => '1.1',
                        'indikator_label' => 'Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'is_scoreable' => true,
                        'urutan' => 35,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Soal 103. Tindakan yang paling tepat dilakukan guru adalah ...',
                                'deskripsi' => 'Guru menemukan bahwa sebagian peserta didik lebih mudah memahami materi melalui kegiatan praktik, sementara yang lain lebih tertarik belajar melalui diskusi, membaca, atau media visual. Namun, pembelajaran yang dilaksanakan selama ini lebih banyak menggunakan satu pendekatan yang sama untuk seluruh peserta didik.',
                                'nama_field' => 'soal_103',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 4.2.3 — Potensi, Minat, dan Cara Belajar Peserta Didik yang Relevan dengan Kebutuhan Belajar Peserta Didik. Level HOTS: C4 – Analisis, Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengamati variasi minat dan cara belajar peserta didik yang muncul dalam pembelajaran serta mengarahkan penyesuaian strategi belajar.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menguraikan kebutuhan belajar berdasarkan minat dan cara belajar peserta didik serta mengarahkan pemilihan strategi yang lebih tepat.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Memfasilitasi beragam aktivitas belajar yang sesuai dengan karakteristik peserta didik serta mengarahkan keterlibatan belajar yang lebih optimal.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menganalisis hubungan antara minat, cara belajar, dan hasil belajar peserta didik serta mengarahkan perbaikan pembelajaran.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengembangkan pembelajaran yang mengakomodasi keragaman potensi, minat, dan cara belajar peserta didik secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 104. Tindakan yang paling tepat dilakukan guru untuk menindaklanjuti hasil tersebut adalah ...',
                                'deskripsi' => 'Guru telah menerapkan berbagai aktivitas belajar yang berbeda dalam satu kelas. Setelah dilakukan evaluasi, terlihat bahwa tingkat keterlibatan peserta didik meningkat ketika mereka diberi kesempatan memilih cara belajar yang sesuai dengan minatnya.',
                                'nama_field' => 'soal_104',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 4.2.3 — Potensi, Minat, dan Cara Belajar Peserta Didik yang Relevan dengan Kebutuhan Belajar Peserta Didik. Level HOTS: C5 – Evaluasi, Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengenali pola keterlibatan peserta didik berdasarkan aktivitas yang dipilih serta mengarahkan pengembangan pembelajaran yang lebih sesuai.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menafsirkan hubungan antara minat belajar dan partisipasi peserta didik serta mengarahkan penyesuaian strategi pembelajaran.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Menggunakan informasi tentang minat dan cara belajar peserta didik dalam merancang pembelajaran serta mengarahkan keterlibatan yang lebih aktif.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menelaah efektivitas berbagai strategi pembelajaran terhadap kebutuhan peserta didik serta mengarahkan penyempurnaan yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merekonstruksi pengalaman belajar yang memberikan pilihan dan fleksibilitas sesuai karakteristik peserta didik secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 105. Tindakan yang paling tepat dilakukan guru untuk mendukung keberhasilan proyek tersebut adalah ...',
                                'deskripsi' => 'Sekolah mengembangkan proyek pembelajaran yang memberi kesempatan kepada peserta didik untuk memilih topik, produk, dan cara penyajian hasil sesuai minat serta potensi yang dimiliki.',
                                'nama_field' => 'soal_105',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 4.2.3 — Potensi, Minat, dan Cara Belajar Peserta Didik yang Relevan dengan Kebutuhan Belajar Peserta Didik. Level HOTS: C6 – Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengidentifikasi potensi dan minat peserta didik yang relevan dengan proyek serta mengarahkan pemilihan aktivitas yang sesuai.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menggambarkan berbagai alternatif peran dan kontribusi yang dapat dipilih peserta didik serta mengarahkan partisipasi yang bermakna.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengorganisasi pembelajaran proyek yang memberi ruang bagi keragaman potensi peserta didik serta mengarahkan pencapaian tujuan pembelajaran.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Mengkaji kesesuaian rancangan proyek terhadap potensi dan minat peserta didik serta mengarahkan penyempurnaan yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merancang ekosistem pembelajaran yang memungkinkan setiap peserta didik mengembangkan potensinya secara optimal melalui proyek yang dipilih.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => '4.2.4 Karakteristik dan Cara Belajar Peserta Didik Penyandang Disabilitas',
                        'kode_form' => 'FORM-PED-424',
                        'deskripsi' => 'Kompetensi Pedagogik — Indikator 1.1: Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                        'indikator_kode' => '1.1',
                        'indikator_label' => 'Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'is_scoreable' => true,
                        'urutan' => 36,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Soal 106. Tindakan yang paling tepat dilakukan guru untuk meningkatkan keterlibatan peserta didik tersebut adalah ...',
                                'deskripsi' => 'Dalam kelas inklusif, seorang peserta didik dengan hambatan pendengaran mengalami kesulitan mengikuti diskusi kelompok karena sebagian besar komunikasi berlangsung secara lisan dan cepat. Akibatnya, peserta didik tersebut lebih banyak menjadi pengamat daripada terlibat aktif dalam proses pembelajaran.',
                                'nama_field' => 'soal_106',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 4.2.4 — Karakteristik dan Cara Belajar Peserta Didik Penyandang Disabilitas. Level HOTS: C4 – Analisis, Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengamati hambatan belajar yang dialami peserta didik selama diskusi serta mengarahkan penyesuaian pembelajaran yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menggali karakteristik belajar peserta didik yang berkaitan dengan hambatan pendengarannya serta mengarahkan dukungan yang lebih sesuai.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Memfasilitasi strategi pembelajaran yang memperluas akses komunikasi peserta didik serta mengarahkan partisipasi yang lebih aktif.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menganalisis pengaruh lingkungan belajar terhadap keterlibatan peserta didik serta mengarahkan modifikasi pembelajaran yang lebih efektif.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengembangkan sistem pembelajaran aksesibel yang mengakomodasi kebutuhan komunikasi peserta didik secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 107. Tindakan yang paling tepat dilakukan guru berdasarkan hasil pengamatan tersebut adalah ...',
                                'deskripsi' => 'Guru menemukan bahwa seorang peserta didik dengan hambatan intelektual mampu memahami materi ketika diberikan bantuan visual dan contoh konkret. Namun, ketika pembelajaran didominasi penjelasan abstrak, peserta didik tersebut mengalami kesulitan mengikuti kegiatan belajar.',
                                'nama_field' => 'soal_107',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 4.2.4 — Karakteristik dan Cara Belajar Peserta Didik Penyandang Disabilitas. Level HOTS: C5 – Evaluasi, Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengenali cara belajar yang membantu peserta didik memahami materi serta mengarahkan pemanfaatan dukungan yang relevan.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menafsirkan hubungan antara karakteristik peserta didik dan strategi pembelajaran yang digunakan serta mengarahkan penyesuaian yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Menggunakan pendekatan pembelajaran yang sesuai dengan kebutuhan peserta didik serta mengarahkan pencapaian tujuan belajar yang lebih optimal.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menelaah efektivitas strategi pembelajaran terhadap perkembangan peserta didik serta mengarahkan penyempurnaan dukungan yang diberikan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merekonstruksi pengalaman belajar yang berorientasi pada karakteristik peserta didik sehingga akses belajar dapat berlangsung secara optimal.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 108. Tindakan yang paling tepat dilakukan guru dalam merancang kegiatan tersebut adalah ...',
                                'deskripsi' => 'Sekolah mengembangkan proyek kolaboratif yang melibatkan peserta didik reguler dan peserta didik penyandang disabilitas dalam satu kelompok kerja. Guru perlu memastikan seluruh peserta didik dapat berpartisipasi secara bermakna sesuai kemampuan dan kebutuhannya.',
                                'nama_field' => 'soal_108',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 4.2.4 — Karakteristik dan Cara Belajar Peserta Didik Penyandang Disabilitas. Level HOTS: C6 – Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengidentifikasi kebutuhan belajar peserta didik yang terlibat dalam proyek serta mengarahkan dukungan yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menggambarkan berbagai bentuk partisipasi yang dapat dilakukan peserta didik dalam proyek serta mengarahkan keterlibatan yang lebih setara.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengorganisasi aktivitas proyek yang memberikan kesempatan berkontribusi kepada seluruh peserta didik serta mengarahkan kolaborasi yang inklusif.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Mengkaji kesesuaian desain proyek terhadap karakteristik peserta didik yang beragam serta mengarahkan penyempurnaan yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merancang pengalaman belajar kolaboratif yang mengoptimalkan potensi seluruh peserta didik tanpa mengabaikan kebutuhan individual.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => '4.2.5 Keragaman Kebutuhan Belajar Peserta Didik untuk Pembelajaran yang Inklusif',
                        'kode_form' => 'FORM-PED-425',
                        'deskripsi' => 'Kompetensi Pedagogik — Indikator 1.1: Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                        'indikator_kode' => '1.1',
                        'indikator_label' => 'Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'is_scoreable' => true,
                        'urutan' => 37,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Soal 109. Tindakan yang paling tepat dilakukan guru untuk memenuhi kebutuhan belajar yang beragam tersebut adalah ...',
                                'deskripsi' => 'Dalam satu kelas, guru menghadapi peserta didik dengan kemampuan akademik, minat, motivasi, dan kesiapan belajar yang sangat beragam. Ketika guru menggunakan satu jenis tugas dan satu cara belajar yang sama, sebagian peserta didik terlihat tidak tertantang, sementara yang lain kesulitan menyelesaikannya.',
                                'nama_field' => 'soal_109',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 4.2.5 — Keragaman Kebutuhan Belajar Peserta Didik untuk Pembelajaran yang Inklusif. Level HOTS: C4 – Analisis, Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengamati variasi kebutuhan belajar peserta didik yang muncul dalam pembelajaran serta mengarahkan penyesuaian yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menguraikan perbedaan kebutuhan belajar yang dimiliki peserta didik serta mengarahkan perencanaan pembelajaran yang lebih responsif.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Memfasilitasi berbagai alternatif aktivitas belajar yang sesuai dengan kebutuhan peserta didik serta mengarahkan keterlibatan yang lebih optimal.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menganalisis hubungan antara kebutuhan belajar dan efektivitas pembelajaran yang berlangsung serta mengarahkan perbaikan yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengembangkan sistem pembelajaran yang fleksibel dan adaptif terhadap keragaman kebutuhan belajar peserta didik.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 110. Tindakan yang paling tepat dilakukan guru untuk meningkatkan kualitas pembelajaran inklusif adalah ...',
                                'deskripsi' => 'Guru telah menerapkan berbagai bentuk diferensiasi pembelajaran. Namun, hasil refleksi menunjukkan bahwa sebagian peserta didik masih belum memperoleh dukungan belajar yang sesuai dengan kebutuhannya karena strategi yang digunakan belum sepenuhnya mempertimbangkan keragaman yang ada.',
                                'nama_field' => 'soal_110',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 4.2.5 — Keragaman Kebutuhan Belajar Peserta Didik untuk Pembelajaran yang Inklusif. Level HOTS: C5 – Evaluasi, Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mencermati respons peserta didik terhadap strategi pembelajaran yang digunakan serta mengarahkan penyesuaian yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menafsirkan kebutuhan belajar peserta didik berdasarkan hasil refleksi pembelajaran serta mengarahkan penguatan dukungan yang sesuai.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengadaptasi strategi pembelajaran berdasarkan kebutuhan peserta didik serta mengarahkan pengalaman belajar yang lebih bermakna.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menilai efektivitas diferensiasi yang telah diterapkan terhadap berbagai kebutuhan peserta didik serta mengarahkan penyempurnaan yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengintegrasikan berbagai bentuk dukungan belajar yang responsif terhadap keragaman peserta didik secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 111. Tindakan yang paling tepat dilakukan guru adalah ...',
                                'deskripsi' => 'Sekolah sedang mengembangkan pembelajaran inklusif berbasis proyek yang memberi kesempatan kepada seluruh peserta didik untuk menunjukkan kompetensinya melalui cara yang berbeda-beda. Guru diminta menyusun rancangan pembelajaran yang mampu mengakomodasi keragaman kebutuhan belajar peserta didik.',
                                'nama_field' => 'soal_111',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 4.2.5 — Keragaman Kebutuhan Belajar Peserta Didik untuk Pembelajaran yang Inklusif. Level HOTS: C6 – Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengidentifikasi kebutuhan belajar yang beragam pada peserta didik serta mengarahkan penyediaan pilihan belajar yang sesuai.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menjelaskan berbagai alternatif cara belajar dan menunjukkan hasil belajar kepada peserta didik serta mengarahkan partisipasi yang lebih luas.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengorganisasi pembelajaran yang menyediakan berbagai jalur belajar bagi peserta didik serta mengarahkan keterlibatan yang lebih inklusif.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Mengevaluasi kesesuaian rancangan pembelajaran terhadap kebutuhan belajar peserta didik serta mengarahkan penyempurnaan yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Menciptakan ekosistem pembelajaran inklusif yang memberikan kesempatan berkembang bagi setiap peserta didik sesuai kebutuhannya.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => '4.3.1 Penggunaan Kurikulum dalam Proses Pembelajaran yang Berpusat pada Peserta Didik',
                        'kode_form' => 'FORM-PED-431',
                        'deskripsi' => 'Kompetensi Pedagogik — Indikator 1.1: Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                        'indikator_kode' => '1.1',
                        'indikator_label' => 'Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'is_scoreable' => true,
                        'urutan' => 38,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Soal 112. Sebagai guru, tindakan yang paling tepat dilakukan untuk memanfaatkan kurikulum secara lebih berpusat pada peserta didik adalah ...',
                                'deskripsi' => 'Seorang guru telah melaksanakan seluruh materi yang tercantum dalam dokumen kurikulum sesuai urutan yang tersedia. Namun, hasil observasi menunjukkan bahwa sebagian besar peserta didik kurang memahami keterkaitan materi dengan kehidupan sehari-hari dan cenderung hanya menghafal informasi yang diberikan.',
                                'nama_field' => 'soal_112',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 4.3.1 — Penggunaan Kurikulum dalam Proses Pembelajaran yang Berpusat pada Peserta Didik. Level HOTS: C4 – Analisis, Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengamati keterkaitan antara materi yang diajarkan dan pengalaman belajar peserta didik serta mengarahkan pembelajaran yang lebih relevan.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menguraikan hubungan antara tujuan kurikulum dan kebutuhan belajar peserta didik serta mengarahkan penyesuaian pembelajaran yang sesuai.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengadaptasi pelaksanaan pembelajaran berdasarkan tujuan kurikulum dan karakteristik peserta didik serta mengarahkan keterlibatan yang lebih aktif.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menganalisis keselarasan antara implementasi kurikulum dan pengalaman belajar peserta didik serta mengarahkan penyempurnaan pembelajaran.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengembangkan strategi implementasi kurikulum yang fleksibel dan kontekstual untuk mendukung pembelajaran yang bermakna.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 113. Tindakan yang paling tepat dilakukan guru adalah ...',
                                'deskripsi' => 'Dalam forum refleksi sekolah, beberapa guru menyampaikan bahwa capaian pembelajaran telah selesai diajarkan, tetapi peserta didik masih menunjukkan kesulitan dalam menerapkan pengetahuan untuk memecahkan masalah nyata. Kepala sekolah meminta guru mengevaluasi cara penggunaan kurikulum dalam pembelajaran.',
                                'nama_field' => 'soal_113',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 4.3.1 — Penggunaan Kurikulum dalam Proses Pembelajaran yang Berpusat pada Peserta Didik. Level HOTS: C5 – Evaluasi, Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mencermati keterhubungan antara capaian pembelajaran dan aktivitas belajar yang dilaksanakan serta mengarahkan perbaikan yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menafsirkan tingkat relevansi pengalaman belajar terhadap tujuan kurikulum serta mengarahkan penyesuaian pembelajaran yang lebih bermakna.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Memanfaatkan ruang fleksibilitas kurikulum untuk menghadirkan pengalaman belajar yang lebih kontekstual serta mengarahkan keterlibatan peserta didik.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menilai efektivitas implementasi kurikulum terhadap perkembangan kompetensi peserta didik serta mengarahkan penyempurnaan yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merekonstruksi praktik implementasi kurikulum berbasis kebutuhan peserta didik untuk meningkatkan kualitas pembelajaran secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 114. Tindakan yang paling tepat dilakukan guru adalah ...',
                                'deskripsi' => 'Sekolah mengembangkan pembelajaran berbasis proyek yang mengintegrasikan berbagai mata pelajaran untuk menjawab persoalan lingkungan di sekitar sekolah. Guru diminta memastikan bahwa kegiatan proyek tetap selaras dengan kurikulum sekaligus memberi ruang bagi peserta didik untuk mengeksplorasi ide dan solusi mereka.',
                                'nama_field' => 'soal_114',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 4.3.1 — Penggunaan Kurikulum dalam Proses Pembelajaran yang Berpusat pada Peserta Didik. Level HOTS: C6 – Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengidentifikasi capaian pembelajaran yang relevan dengan proyek serta mengarahkan keterhubungan dengan aktivitas peserta didik.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menggambarkan kontribusi proyek terhadap pencapaian tujuan kurikulum serta mengarahkan pemahaman peserta didik terhadap tujuan belajar.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengorganisasi pengalaman belajar proyek yang selaras dengan kurikulum serta mengarahkan partisipasi peserta didik secara aktif.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Mengevaluasi kesesuaian pelaksanaan proyek dengan tujuan kurikulum dan kebutuhan peserta didik serta mengarahkan penyempurnaan yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merancang integrasi kurikulum dan pembelajaran berbasis proyek yang mendorong penguasaan kompetensi secara mendalam dan berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => '4.3.2 Penggunaan Asesmen untuk Meningkatkan Pembelajaran yang Berpusat pada Peserta Didik',
                        'kode_form' => 'FORM-PED-432',
                        'deskripsi' => 'Kompetensi Pedagogik — Indikator 1.1: Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                        'indikator_kode' => '1.1',
                        'indikator_label' => 'Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'is_scoreable' => true,
                        'urutan' => 39,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Soal 115. Tindakan yang paling tepat dilakukan guru dalam memanfaatkan hasil asesmen tersebut adalah ...',
                                'deskripsi' => 'Setelah melaksanakan asesmen formatif, guru memperoleh informasi bahwa sebagian besar peserta didik belum memahami konsep yang menjadi dasar pembelajaran berikutnya. Namun, jadwal pembelajaran yang telah disusun mengharuskan guru segera melanjutkan ke materi berikutnya.',
                                'nama_field' => 'soal_115',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 4.3.2 — Penggunaan Asesmen untuk Meningkatkan Pembelajaran yang Berpusat pada Peserta Didik. Level HOTS: C4 – Analisis, Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengamati informasi yang diperoleh dari asesmen peserta didik serta mengarahkan tindak lanjut pembelajaran yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menggali makna hasil asesmen terhadap kesiapan belajar peserta didik serta mengarahkan penyesuaian pembelajaran yang sesuai.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Menggunakan hasil asesmen sebagai dasar pengambilan keputusan pembelajaran serta mengarahkan penguatan kompetensi yang belum dikuasai.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menganalisis hubungan antara hasil asesmen dan kebutuhan belajar peserta didik serta mengarahkan strategi tindak lanjut yang lebih tepat.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengembangkan sistem pembelajaran responsif yang memanfaatkan data asesmen secara berkelanjutan untuk meningkatkan hasil belajar.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 116. Tindakan yang paling tepat dilakukan guru untuk meningkatkan fungsi asesmen dalam pembelajaran adalah ...',
                                'deskripsi' => 'Guru telah menggunakan berbagai bentuk asesmen selama satu semester. Namun, hasil refleksi menunjukkan bahwa sebagian peserta didik belum memahami bagaimana memanfaatkan hasil asesmen untuk memperbaiki proses belajarnya.',
                                'nama_field' => 'soal_116',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 4.3.2 — Penggunaan Asesmen untuk Meningkatkan Pembelajaran yang Berpusat pada Peserta Didik. Level HOTS: C5 – Evaluasi, Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengenali tingkat pemahaman peserta didik terhadap hasil asesmen yang diterima serta mengarahkan pemanfaatannya dalam belajar.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menguraikan hubungan antara hasil asesmen dan perbaikan proses belajar kepada peserta didik serta mengarahkan refleksi yang lebih bermakna.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Memfasilitasi penggunaan hasil asesmen oleh peserta didik untuk merencanakan perbaikan belajar serta mengarahkan pengembangan kompetensi.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menelaah efektivitas pemanfaatan asesmen dalam mendukung pembelajaran peserta didik serta mengarahkan penyempurnaan yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengintegrasikan asesmen, refleksi, dan tindak lanjut belajar dalam satu siklus pembelajaran yang berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 117. Tindakan yang paling tepat dilakukan guru adalah ...',
                                'deskripsi' => 'Sekolah mengembangkan sistem pembelajaran yang menempatkan asesmen sebagai bagian dari proses belajar, bukan hanya sebagai alat penilaian akhir. Guru diminta merancang pembelajaran proyek yang memungkinkan peserta didik memperoleh umpan balik dan memperbaiki hasil kerjanya secara bertahap.',
                                'nama_field' => 'soal_117',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 4.3.2 — Penggunaan Asesmen untuk Meningkatkan Pembelajaran yang Berpusat pada Peserta Didik. Level HOTS: C6 – Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengidentifikasi titik-titik penting dalam proyek yang memerlukan asesmen serta mengarahkan pemantauan perkembangan peserta didik.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menjelaskan fungsi asesmen dalam mendukung proses belajar kepada peserta didik serta mengarahkan pemanfaatannya secara bertahap.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengorganisasi asesmen dan umpan balik selama pelaksanaan proyek serta mengarahkan perbaikan hasil belajar peserta didik.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Mengkaji efektivitas asesmen dalam mendukung perkembangan kompetensi peserta didik selama proyek serta mengarahkan penyempurnaan yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Menciptakan sistem asesmen berkelanjutan yang mendorong refleksi, perbaikan, dan pengembangan kompetensi peserta didik secara optimal.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => '4.3.3 Penggunaan Strategi untuk Meningkatkan Pembelajaran yang Berpusat pada Peserta Didik',
                        'kode_form' => 'FORM-PED-433',
                        'deskripsi' => 'Kompetensi Pedagogik — Indikator 1.1: Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                        'indikator_kode' => '1.1',
                        'indikator_label' => 'Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'is_scoreable' => true,
                        'urutan' => 40,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Soal 118. Tindakan yang paling tepat dilakukan guru untuk meningkatkan pembelajaran yang berpusat pada peserta didik adalah ...',
                                'deskripsi' => 'Dalam pembelajaran IPAS, guru menyampaikan materi melalui ceramah selama sebagian besar waktu belajar. Hasil observasi menunjukkan bahwa hanya beberapa peserta didik yang aktif bertanya, sementara sebagian besar lainnya cenderung pasif dan menunggu arahan. Guru ingin meningkatkan keterlibatan peserta didik agar lebih aktif membangun pemahamannya sendiri.',
                                'nama_field' => 'soal_118',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 4.3.3 — Penggunaan Strategi untuk Meningkatkan Pembelajaran yang Berpusat pada Peserta Didik. Level HOTS: C4 – Analisis, Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengamati pola keterlibatan peserta didik selama pembelajaran serta mengarahkan penyesuaian strategi yang lebih melibatkan mereka.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menggali faktor yang memengaruhi partisipasi peserta didik dalam pembelajaran serta mengarahkan pemilihan strategi yang lebih sesuai.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Menerapkan strategi pembelajaran yang memberi ruang eksplorasi dan partisipasi peserta didik serta mengarahkan keterlibatan yang lebih aktif.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menganalisis efektivitas strategi yang digunakan terhadap keterlibatan peserta didik serta mengarahkan penyempurnaan pembelajaran.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengembangkan pendekatan pembelajaran yang adaptif terhadap kebutuhan peserta didik sehingga mereka menjadi subjek utama pembelajaran.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 119. Tindakan yang paling tepat dilakukan guru untuk meningkatkan efektivitas strategi pembelajaran tersebut adalah ...',
                                'deskripsi' => 'Guru telah menggunakan diskusi kelompok dalam beberapa kali pembelajaran. Namun, hasil evaluasi menunjukkan bahwa sebagian peserta didik hanya mengikuti pendapat teman yang lebih aktif tanpa benar-benar terlibat dalam proses berpikir dan pengambilan keputusan.',
                                'nama_field' => 'soal_119',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 4.3.3 — Penggunaan Strategi untuk Meningkatkan Pembelajaran yang Berpusat pada Peserta Didik. Level HOTS: C5 – Evaluasi, Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengenali tingkat keterlibatan peserta didik dalam kegiatan kelompok serta mengarahkan partisipasi yang lebih merata.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menafsirkan hambatan yang memengaruhi keterlibatan peserta didik dalam diskusi serta mengarahkan perbaikan strategi yang digunakan.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Memfasilitasi struktur kerja kelompok yang mendorong kontribusi seluruh peserta didik serta mengarahkan interaksi yang lebih bermakna.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menilai efektivitas strategi diskusi terhadap pencapaian tujuan pembelajaran serta mengarahkan penyempurnaan yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merekonstruksi desain pembelajaran kolaboratif yang menjamin keterlibatan aktif seluruh peserta didik secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 120. Tindakan yang paling tepat dilakukan guru adalah ...',
                                'deskripsi' => 'Sekolah mendorong guru menerapkan pembelajaran berbasis proyek yang memungkinkan peserta didik mengembangkan kemampuan berpikir kritis, kreativitas, komunikasi, dan kolaborasi. Guru perlu menentukan strategi yang tetap berpusat pada peserta didik sepanjang proses pembelajaran berlangsung.',
                                'nama_field' => 'soal_120',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 4.3.3 — Penggunaan Strategi untuk Meningkatkan Pembelajaran yang Berpusat pada Peserta Didik. Level HOTS: C6 – Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengidentifikasi kebutuhan belajar peserta didik dalam proyek serta mengarahkan strategi yang mendukung keterlibatan mereka.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menggambarkan berbagai strategi pembelajaran yang dapat mendukung partisipasi peserta didik serta mengarahkan penggunaannya secara tepat.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengorganisasi pengalaman belajar yang memberi ruang pengambilan keputusan kepada peserta didik serta mengarahkan pembelajaran yang lebih mandiri.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Mengevaluasi kesesuaian strategi pembelajaran terhadap kebutuhan dan perkembangan peserta didik serta mengarahkan penyempurnaan yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Merancang ekosistem pembelajaran yang memungkinkan peserta didik berperan aktif dalam merencanakan, melaksanakan, dan merefleksikan pembelajarannya.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 3,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                        ],
                    ],
                    [
                        'judul_form' => '4.3.4 Penggunaan Strategi Pembelajaran yang Efektif untuk Capaian Belajar Literasi dan Numerasi Peserta Didik',
                        'kode_form' => 'FORM-PED-434',
                        'deskripsi' => 'Kompetensi Pedagogik — Indikator 1.1: Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                        'indikator_kode' => '1.1',
                        'indikator_label' => 'Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik',
                        'is_scoreable' => true,
                        'urutan' => 41,
                        'is_active' => true,
                        'fields' => [
                            [
                                'label' => 'Soal 121. Tindakan yang paling tepat dilakukan guru untuk meningkatkan capaian literasi dan numerasi peserta didik adalah ...',
                                'deskripsi' => 'Hasil asesmen sekolah menunjukkan bahwa sebagian besar peserta didik mampu membaca teks, tetapi mengalami kesulitan memahami informasi tersirat dan menghubungkannya dengan situasi nyata. Pada saat yang sama, kemampuan numerasi peserta didik dalam menyelesaikan masalah kontekstual juga masih rendah.',
                                'nama_field' => 'soal_121',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 4.3.4 — Penggunaan Strategi Pembelajaran yang Efektif untuk Capaian Belajar Literasi dan Numerasi Peserta Didik. Level HOTS: C4 – Analisis, Problem Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengamati pola kesulitan yang dialami peserta didik dalam literasi dan numerasi serta mengarahkan pemilihan strategi yang sesuai.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menguraikan kebutuhan belajar peserta didik berdasarkan hasil asesmen serta mengarahkan penggunaan strategi yang lebih relevan.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Menggunakan strategi pembelajaran yang menghubungkan konsep dengan konteks kehidupan nyata serta mengarahkan pemahaman yang lebih mendalam.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menganalisis efektivitas strategi literasi dan numerasi yang diterapkan terhadap capaian belajar peserta didik serta mengarahkan perbaikan yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengembangkan pembelajaran literasi dan numerasi yang terintegrasi dalam berbagai konteks belajar untuk meningkatkan kompetensi secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 1,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 122. Tindakan yang paling tepat dilakukan guru untuk memperkuat kemampuan literasi dan numerasi peserta didik adalah ...',
                                'deskripsi' => 'Guru telah menerapkan berbagai aktivitas membaca dan pemecahan masalah matematika. Namun, hasil refleksi menunjukkan bahwa peserta didik masih kesulitan menjelaskan alasan di balik jawaban yang mereka berikan dan belum terbiasa menggunakan bukti untuk mendukung argumennya.',
                                'nama_field' => 'soal_122',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 4.3.4 — Penggunaan Strategi Pembelajaran yang Efektif untuk Capaian Belajar Literasi dan Numerasi Peserta Didik. Level HOTS: C5 – Evaluasi, Inquiry Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mencermati cara peserta didik memahami dan menjelaskan informasi serta mengarahkan penguatan proses berpikir yang digunakan.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menafsirkan hubungan antara strategi pembelajaran dan kemampuan bernalar peserta didik serta mengarahkan penyesuaian yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Memfasilitasi kegiatan yang mendorong peserta didik mengemukakan alasan dan bukti atas jawabannya serta mengarahkan pemikiran yang lebih kritis.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Menelaah dampak strategi pembelajaran terhadap perkembangan literasi dan numerasi peserta didik serta mengarahkan penyempurnaan yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Mengintegrasikan strategi literasi, numerasi, dan penalaran dalam pengalaman belajar yang mendukung pemecahan masalah secara mendalam.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
                                'lebar_kolom' => 'col-md-12',
                                'urutan' => 2,
                                'is_required' => true,
                                'is_active' => true,
                            ],
                            [
                                'label' => 'Soal 123. Tindakan yang paling tepat dilakukan guru adalah ...',
                                'deskripsi' => 'Sekolah mengembangkan proyek kewirausahaan yang mengharuskan peserta didik mengumpulkan data, membaca berbagai sumber informasi, melakukan perhitungan sederhana, dan menyajikan hasil analisisnya kepada warga sekolah. Guru diminta memastikan proyek tersebut mampu meningkatkan literasi dan numerasi peserta didik.',
                                'nama_field' => 'soal_123',
                                'tipe_field' => 'radio',
                                'placeholder' => null,
                                'bantuan' => 'Kompetensi: Pedagogik | Indikator: 1.1 — Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik | Subindikator: 4.3.4 — Penggunaan Strategi Pembelajaran yang Efektif untuk Capaian Belajar Literasi dan Numerasi Peserta Didik. Level HOTS: C6 – Project Based Learning. Pilih satu opsi yang paling menggambarkan kondisi atau pemahaman Anda.',
                                'opsi_field' => $this->withCompetencyLevels([
                                    [
                                        'label' => 'A',
                                        'value' => 'Mengidentifikasi peluang pengembangan literasi dan numerasi dalam proyek yang dilaksanakan serta mengarahkan aktivitas belajar yang relevan.',
                                    ],
                                    [
                                        'label' => 'B',
                                        'value' => 'Menjelaskan keterkaitan aktivitas proyek dengan pengembangan literasi dan numerasi peserta didik serta mengarahkan pemanfaatannya dalam belajar.',
                                    ],
                                    [
                                        'label' => 'C',
                                        'value' => 'Mengorganisasi kegiatan proyek yang mendorong peserta didik menggunakan keterampilan literasi dan numerasi secara aktif serta mengarahkan pencapaian tujuan belajar.',
                                    ],
                                    [
                                        'label' => 'D',
                                        'value' => 'Mengkaji efektivitas aktivitas proyek dalam mengembangkan literasi dan numerasi peserta didik serta mengarahkan penyempurnaan yang diperlukan.',
                                    ],
                                    [
                                        'label' => 'E',
                                        'value' => 'Menciptakan pengalaman belajar autentik yang mengintegrasikan literasi dan numerasi dalam pemecahan masalah nyata secara berkelanjutan.',
                                    ],
                                ]),
                                'nilai_default' => null,
                                'validasi' => ['required' => true],
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
                    'target_ketenagaan' => AssessmentKetenagaanType::TENAGA_PENDIDIK->value,
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
                'overall_formula' => 'I_PG = (I_Ped + I_Kep + I_Sos + I_Pro) / 4',
                'domain_ranges' => [
                    [
                        'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                        'label' => KompetensiGuru::PEDAGOGIK->label(),
                        'question_start' => 1,
                        'question_end' => 39,
                        'question_total' => 39,
                        'formula' => 'I_Ped = jumlah skor butir 1-39 / 39',
                    ],
                    [
                        'kompetensi' => KompetensiGuru::KEPRIBADIAN->value,
                        'label' => KompetensiGuru::KEPRIBADIAN->label(),
                        'question_start' => 40,
                        'question_end' => 66,
                        'question_total' => 27,
                        'formula' => 'I_Kep = jumlah skor butir 40-66 / 27',
                    ],
                    [
                        'kompetensi' => KompetensiGuru::SOSIAL->value,
                        'label' => KompetensiGuru::SOSIAL->label(),
                        'question_start' => 67,
                        'question_end' => 87,
                        'question_total' => 21,
                        'formula' => 'I_Sos = jumlah skor butir 67-87 / 21',
                    ],
                    [
                        'kompetensi' => KompetensiGuru::PROFESIONAL->value,
                        'label' => KompetensiGuru::PROFESIONAL->label(),
                        'question_start' => 88,
                        'question_end' => 123,
                        'question_total' => 36,
                        'formula' => 'I_Pro = jumlah skor butir 88-123 / 36',
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
                'reporting_focus' => 'Rata-rata domain ditampilkan sebagai level kompetensi pilihan ganda kompleks.',
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
            'rubric_code' => 'PG-'.$questionNumber,
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
                    '4' => 'Memilih tindakan analitis atau evaluatif berbasis kebutuhan atau data.',
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

        $formData['kode_form'] = 'FORM-'.$domain['code_prefix'].'-'.$normalizedSuffix;
        $formData['kompetensi'] = $domain['kompetensi'];
        $formData['indikator_kode'] = $titleCode ?? ('PG-'.$questionStart);
        $formData['indikator_label'] = $titleLabel !== '' ? $titleLabel : $domain['indicator_label'];
        $formData['deskripsi'] = sprintf(
            'Kompetensi %s. Rentang butir %d-%d dalam domain %s. Setiap pilihan jawaban merepresentasikan level kompetensi 1-5 sesuai rubrik pilihan ganda kompleks.',
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
            $questionNumber <= 39 => [
                'key' => 'pedagogik',
                'label' => KompetensiGuru::PEDAGOGIK->label(),
                'kompetensi' => KompetensiGuru::PEDAGOGIK->value,
                'code_prefix' => 'PED',
                'question_total' => 39,
                'indicator_label' => 'Pembelajaran berpusat pada peserta didik',
                'formula' => 'I_Ped = jumlah skor butir domain / 39',
            ],
            $questionNumber <= 66 => [
                'key' => 'kepribadian',
                'label' => KompetensiGuru::KEPRIBADIAN->label(),
                'kompetensi' => KompetensiGuru::KEPRIBADIAN->value,
                'code_prefix' => 'KEP',
                'question_total' => 27,
                'indicator_label' => 'Integritas, emosi, dan refleksi diri',
                'formula' => 'I_Kep = jumlah skor butir domain / 27',
            ],
            $questionNumber <= 87 => [
                'key' => 'sosial',
                'label' => KompetensiGuru::SOSIAL->label(),
                'kompetensi' => KompetensiGuru::SOSIAL->value,
                'code_prefix' => 'SOS',
                'question_total' => 21,
                'indicator_label' => 'Kolaborasi dan keterlibatan pihak lain',
                'formula' => 'I_Sos = jumlah skor butir domain / 21',
            ],
            default => [
                'key' => 'profesional',
                'label' => KompetensiGuru::PROFESIONAL->label(),
                'kompetensi' => KompetensiGuru::PROFESIONAL->value,
                'code_prefix' => 'PRO',
                'question_total' => 36,
                'indicator_label' => 'Penguasaan materi dan implementasi kurikulum',
                'formula' => 'I_Pro = jumlah skor butir domain / 36',
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
                'implication' => 'Mampu mengevaluasi dan menyesuaikan praktik; perlu perluasan peran sebagai penggerak atau mentor.',
            ],
            [
                'min' => 4.20,
                'max' => 5.00,
                'label' => 'Level 5 - Ahli',
                'implication' => 'Mampu mengembangkan sistem atau inovasi; diarahkan pada diseminasi, jejaring, dan penguatan kapasitas sekolah.',
            ],
        ];
    }
}
