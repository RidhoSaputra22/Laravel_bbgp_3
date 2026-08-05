<?php

namespace Database\Seeders;

use App\Enum\AssessmentInstrumentType;
use App\Enum\AssessmentKetenagaanType;
use App\Enum\KompetensiGuru;
use App\Models\Assessment;
use App\Support\Assessment\LikertScale;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;

class AssessmentEvaluasiLikertGuruSeeder extends Seeder
{
    private const ASSESSMENT_CODE = 'ASM-LIKERT-KOMP-GURU-001';

    private const NEGATIVE_ITEM_POSITIONS = [3, 6];

    private const EXPECTED_FORM_COUNT = 41;

    private const EXPECTED_ITEM_COUNT = 287;

    private const EXPECTED_NEGATIVE_ITEM_COUNT = 82;

    private const INSTRUMENT_DATA = <<<'DATA'
C|pedagogik|Kompetensi Pedagogik
I|1.1|Lingkungan pembelajaran yang aman dan nyaman bagi peserta didik
F|1.1.1|Pengelolaan Perilaku Peserta Didik yang Sulit
Q|1|Saya mengidentifikasi faktor penyebab munculnya perilaku peserta didik yang mengganggu proses pembelajaran sebagai dasar penanganan yang tepat.
Q|2|Saya menerapkan strategi pengelolaan perilaku yang mendidik, menghargai martabat peserta didik, dan mendorong perubahan perilaku secara positif.
Q|3|Saya biasanya menggunakan cara penanganan yang sama terhadap semua peserta didik tanpa mempertimbangkan penyebab perilakunya.
Q|4|Saya melibatkan peserta didik dalam menyusun dan menerapkan kesepakatan kelas untuk membangun disiplin yang bertanggung jawab.
Q|5|Saya melakukan evaluasi terhadap efektivitas strategi penanganan perilaku untuk menentukan tindak lanjut pembelajaran.
Q|6|Saya jarang meninjau kembali apakah strategi yang saya gunakan berhasil memperbaiki perilaku peserta didik.
Q|7|Saya menyesuaikan pendekatan pengelolaan perilaku berdasarkan karakteristik, kebutuhan, dan perkembangan setiap peserta didik.
F|1.1.2|Pengelolaan Kelas untuk Mencapai Pembelajaran yang Berpusat pada Peserta Didik
Q|8|Saya mengelola lingkungan kelas sehingga setiap peserta didik memperoleh kesempatan yang setara untuk aktif berpartisipasi dalam pembelajaran.
Q|9|Saya menerapkan strategi pembelajaran yang mendorong peserta didik berpikir kritis, kreatif, berkolaborasi, dan berkomunikasi secara aktif.
Q|10|Dalam pembelajaran, saya lebih sering mengatur seluruh aktivitas kelas sendiri daripada memberi kesempatan peserta didik untuk berinisiatif dan mengambil peran.
Q|11|Saya mengelola waktu, aktivitas, dan sumber belajar secara efektif agar pembelajaran berlangsung aktif, terarah, dan berpusat pada peserta didik.
Q|12|Saya memberikan ruang kepada peserta didik untuk menyampaikan ide, bertanya, berdiskusi, dan mengambil peran dalam proses pembelajaran.
Q|13|Saya jarang mengubah pengelolaan kelas meskipun peserta didik menunjukkan kebutuhan belajar yang berbeda atau pembelajaran kurang berjalan efektif.
Q|14|Saya menyesuaikan pengelolaan kelas berdasarkan hasil refleksi dan kebutuhan belajar peserta didik untuk meningkatkan kualitas pembelajaran.
F|1.1.3|Rasa Aman dan Nyaman Peserta Didik dalam Proses Pembelajaran
Q|15|Saya menciptakan suasana pembelajaran yang membuat peserta didik merasa aman, dihargai, dan percaya diri untuk berpartisipasi.
Q|16|Saya mampu mencegah dan menangani tindakan perundungan, diskriminasi, maupun bentuk kekerasan lainnya di lingkungan pembelajaran.
Q|17|Saya menganggap peserta didik harus mampu menyelesaikan sendiri rasa tidak nyaman yang mereka alami selama pembelajaran tanpa memerlukan perhatian khusus dari guru.
Q|18|Saya membangun komunikasi yang terbuka dan saling menghargai sehingga peserta didik merasa nyaman menyampaikan pendapat maupun kesulitannya.
Q|19|Saya memberikan penguatan dan umpan balik yang positif untuk meningkatkan motivasi, rasa percaya diri, dan kesejahteraan psikologis peserta didik.
Q|20|Saya jarang mengevaluasi apakah suasana kelas sudah memberikan rasa aman dan nyaman bagi seluruh peserta didik.
Q|21|Saya secara berkala mengevaluasi kondisi keamanan, kenyamanan, dan iklim belajar di kelas sebagai dasar perbaikan pembelajaran.
I|1.2|Pembelajaran efektif yang berpusat pada peserta didik
F|1.2.1|Desain Pembelajaran yang Terstruktur dan Berurutan untuk Mencapai Tujuan Pembelajaran
Q|1|Saya mampu menyusun tujuan pembelajaran yang selaras dengan capaian pembelajaran, karakteristik peserta didik, dan kebutuhan belajar.
Q|2|Saya merancang alur pembelajaran secara sistematis dan berurutan sehingga memudahkan peserta didik mencapai tujuan pembelajaran.
Q|3|Saya cenderung menggunakan desain pembelajaran yang sama untuk semua materi tanpa mempertimbangkan tujuan pembelajaran maupun kebutuhan peserta didik.
Q|4|Saya menyusun aktivitas pembelajaran yang saling berkaitan mulai dari apersepsi, kegiatan inti, hingga refleksi pembelajaran.
Q|5|Saya menyesuaikan strategi pembelajaran berdasarkan hasil asesmen awal dan perkembangan belajar peserta didik.
Q|6|Saya jarang meninjau kembali desain pembelajaran meskipun hasil belajar peserta didik belum mencapai tujuan yang diharapkan.
Q|7|Saya melakukan refleksi terhadap desain pembelajaran untuk meningkatkan efektivitas pencapaian tujuan pembelajaran.
F|1.2.2|Desain Pembelajaran yang Relevan dengan Kondisi di Sekitar Sekolah dengan Melibatkan Peserta Didik
Q|8|Saya merancang pembelajaran yang mengaitkan materi dengan kondisi lingkungan, budaya, atau potensi di sekitar sekolah.
Q|9|Saya melibatkan peserta didik dalam mengidentifikasi permasalahan nyata di lingkungan sekitar sebagai bagian dari pembelajaran.
Q|10|Saya lebih sering menggunakan contoh pembelajaran yang bersifat umum tanpa mengaitkannya dengan kondisi di sekitar sekolah atau kehidupan peserta didik.
Q|11|Saya memberikan kesempatan kepada peserta didik untuk mengeksplorasi lingkungan sekitar sebagai sumber belajar yang bermakna.
Q|12|Saya merancang kegiatan pembelajaran yang mendorong peserta didik berkolaborasi dalam menyelesaikan masalah kontekstual.
Q|13|Saya jarang mengevaluasi apakah pembelajaran yang saya rancang sudah sesuai dengan kondisi lingkungan dan pengalaman nyata peserta didik.
Q|14|Saya mengevaluasi keterkaitan pembelajaran dengan konteks kehidupan peserta didik untuk meningkatkan kebermaknaan belajar.
F|1.2.3|Pemilihan dan Penggunaan Sumber Belajar yang Sesuai dengan Tujuan Pembelajaran
Q|15|Saya memilih sumber belajar yang sesuai dengan tujuan pembelajaran, karakteristik materi, dan kebutuhan peserta didik.
Q|16|Saya memanfaatkan berbagai sumber belajar, baik cetak, digital, maupun lingkungan sekitar, untuk memperkaya pengalaman belajar peserta didik.
Q|17|Saya cenderung menggunakan sumber belajar yang sama pada setiap pembelajaran meskipun tujuan pembelajaran dan kebutuhan peserta didik berbeda.
Q|18|Saya mengembangkan atau memodifikasi sumber belajar agar lebih sesuai dengan konteks pembelajaran di kelas.
Q|19|Saya membimbing peserta didik memilih dan menggunakan sumber belajar secara kritis, tepat, dan bertanggung jawab.
Q|20|Saya jarang menilai kembali apakah sumber belajar yang digunakan benar-benar membantu peserta didik mencapai tujuan pembelajaran.
Q|21|Saya mengevaluasi efektivitas penggunaan sumber belajar sebagai dasar penyempurnaan pembelajaran berikutnya.
F|1.2.4|Instruksi Pembelajaran yang Mencakup Strategi dan Komunikasi untuk Menumbuhkan Minat dan Nalar Kritis Peserta Didik
Q|22|Saya memberikan instruksi pembelajaran yang jelas, sistematis, dan mudah dipahami oleh seluruh peserta didik.
Q|23|Saya menggunakan strategi bertanya yang mendorong peserta didik berpikir kritis, menganalisis, dan memberikan alasan atas pendapatnya.
Q|24|Saya lebih sering memberikan penjelasan satu arah daripada memberi kesempatan kepada peserta didik untuk bertanya, berdiskusi, atau mengemukakan pendapat.
Q|25|Saya membangun komunikasi yang interaktif sehingga peserta didik aktif bertanya, berdiskusi, dan menyampaikan gagasan.
Q|26|Saya memberikan tantangan belajar yang mendorong peserta didik mencari solusi terhadap masalah secara mandiri maupun kolaboratif.
Q|27|Saya jarang memberikan umpan balik yang mendorong peserta didik memperbaiki cara berpikir atau mengembangkan ide-idenya.
Q|28|Saya memberikan umpan balik yang konstruktif untuk meningkatkan motivasi, kualitas berpikir, dan keterlibatan peserta didik dalam pembelajaran.
F|1.2.5|Penggunaan Teknologi Informasi dan Komunikasi (TIK) Secara Adaptif dalam Pembelajaran
Q|29|Dalam merancang pembelajaran, saya menentukan teknologi digital yang paling sesuai dengan tujuan pembelajaran, karakteristik materi, dan kebutuhan peserta didik.
Q|30|Saya memanfaatkan berbagai aplikasi atau platform digital untuk meningkatkan keterlibatan peserta didik selama proses pembelajaran berlangsung.
Q|31|Saya tetap menggunakan media atau aplikasi yang sama meskipun kurang sesuai dengan kondisi sekolah maupun kebutuhan belajar peserta didik.
Q|32|Saya menyesuaikan penggunaan teknologi informasi dan komunikasi dengan ketersediaan fasilitas, akses internet, serta kemampuan peserta didik agar pembelajaran tetap efektif.
Q|33|Saya membimbing peserta didik agar menggunakan teknologi digital secara bijak, aman, bertanggung jawab, dan sesuai etika selama kegiatan pembelajaran.
Q|34|Saya jarang meninjau kembali apakah penggunaan teknologi benar-benar memberikan dampak terhadap peningkatan kualitas pembelajaran.
Q|35|Setelah pembelajaran selesai, saya melakukan refleksi terhadap penggunaan TIK sebagai dasar untuk menyempurnakan pembelajaran berikutnya.
I|1.3|Asesmen, umpan balik, dan pelaporan yang berpusat pada peserta didik
F|1.3.1|Perancangan Asesmen yang Berpusat pada Peserta Didik
Q|1|Sebelum pembelajaran dimulai, saya merancang asesmen yang selaras dengan tujuan pembelajaran, karakteristik materi, dan kebutuhan belajar peserta didik.
Q|2|Instrumen asesmen yang saya susun mampu mengukur pengetahuan, keterampilan, dan sikap peserta didik secara menyeluruh.
Q|3|Saya menggunakan bentuk asesmen yang sama pada hampir semua pembelajaran tanpa mempertimbangkan karakteristik materi maupun kebutuhan peserta didik.
Q|4|Dalam menyusun asesmen, saya menetapkan kriteria keberhasilan dan rubrik penilaian yang jelas agar mudah dipahami oleh peserta didik.
Q|5|Hasil identifikasi kebutuhan belajar peserta didik menjadi salah satu dasar saya dalam merancang bentuk dan teknik asesmen.
Q|6|Saya jarang menyesuaikan rancangan asesmen meskipun kemampuan awal dan kebutuhan belajar peserta didik berbeda-beda.
Q|7|Setelah merancang asesmen, saya meninjau kembali kesesuaiannya dengan tujuan pembelajaran agar informasi yang diperoleh benar-benar mendukung peningkatan kualitas belajar peserta didik.
F|1.3.2|Pelaksanaan Asesmen yang Berpusat pada Peserta Didik
Q|8|Selama proses pembelajaran, saya melaksanakan asesmen secara adil, objektif, dan sesuai dengan karakteristik serta kebutuhan peserta didik.
Q|9|Saya memadukan berbagai teknik asesmen untuk memperoleh informasi yang lebih lengkap mengenai perkembangan belajar peserta didik.
Q|10|Saya lebih sering menilai hasil belajar peserta didik menggunakan satu jenis asesmen meskipun tujuan pembelajaran memerlukan pendekatan yang berbeda.
Q|11|Dalam pelaksanaan asesmen, saya memberikan kesempatan kepada peserta didik menunjukkan kompetensinya melalui berbagai bentuk unjuk kerja, proyek, atau produk.
Q|12|Saya melibatkan peserta didik dalam proses asesmen melalui penilaian diri dan/atau penilaian antarteman dengan bimbingan yang jelas.
Q|13|Hasil asesmen yang diperoleh jarang saya gunakan untuk menyesuaikan strategi pembelajaran pada pertemuan berikutnya.
Q|14|Informasi yang diperoleh dari pelaksanaan asesmen saya manfaatkan sebagai dasar untuk memperbaiki proses pembelajaran dan mendukung perkembangan belajar peserta didik.
F|1.3.3|Umpan Balik terhadap Peserta Didik Mengenai Pembelajarannya
Q|15|Saya memberikan umpan balik yang spesifik, jelas, dan mudah dipahami agar peserta didik mengetahui langkah yang perlu dilakukan untuk meningkatkan hasil belajarnya.
Q|16|Umpan balik saya sampaikan pada waktu yang tepat sehingga peserta didik dapat segera memperbaiki proses maupun hasil belajarnya.
Q|17|Saya lebih sering hanya menyampaikan nilai hasil belajar tanpa memberikan penjelasan yang dapat membantu peserta didik memperbaiki pencapaiannya.
Q|18|Melalui umpan balik yang saya berikan, peserta didik didorong untuk melakukan refleksi terhadap proses belajar dan strategi yang telah digunakannya.
Q|19|Selain menunjukkan bagian yang perlu diperbaiki, saya juga memberikan penguatan terhadap kelebihan yang telah ditunjukkan oleh peserta didik.
Q|20|Saya jarang memanfaatkan hasil tindak lanjut dari umpan balik untuk memperbaiki proses pembelajaran pada pertemuan berikutnya.
Q|21|Informasi yang diperoleh dari tindak lanjut umpan balik saya gunakan sebagai dasar dalam menyempurnakan strategi pembelajaran berikutnya.
F|1.3.4|Penyusunan Laporan Capaian Belajar Peserta Didik
Q|22|Saya menyusun laporan capaian belajar berdasarkan data hasil asesmen yang akurat, lengkap, dan dapat dipertanggungjawabkan.
Q|23|Laporan hasil belajar yang saya susun menggambarkan perkembangan pengetahuan, keterampilan, dan sikap peserta didik secara menyeluruh.
Q|24|Saya lebih berfokus pada pemberian nilai akhir daripada menyajikan informasi mengenai perkembangan belajar peserta didik secara utuh.
Q|25|Dalam menyusun deskripsi capaian belajar, saya menggunakan bahasa yang objektif, informatif, dan sesuai dengan perkembangan masing-masing peserta didik.
Q|26|Hasil analisis asesmen saya gunakan sebagai dasar dalam menyusun rekomendasi tindak lanjut pembelajaran pada laporan hasil belajar.
Q|27|Saya jarang meninjau kembali kelengkapan dan ketepatan laporan hasil belajar sebelum disampaikan kepada pihak yang berkepentingan.
Q|28|Saya memastikan laporan capaian belajar diselesaikan tepat waktu serta sesuai dengan ketentuan dan prosedur yang berlaku.
F|1.3.5|Komunikasi Laporan Capaian Belajar Peserta Didik
Q|29|Saya menyampaikan laporan capaian belajar kepada peserta didik dan orang tua/wali menggunakan bahasa yang jelas, santun, serta mudah dipahami.
Q|30|Ketika menjelaskan hasil belajar, saya menyampaikan kekuatan, perkembangan, dan kebutuhan belajar peserta didik berdasarkan data asesmen yang valid.
Q|31|Saya lebih sering menyampaikan hasil belajar dalam bentuk nilai tanpa memberikan penjelasan mengenai perkembangan maupun langkah perbaikan yang dapat dilakukan peserta didik.
Q|32|Saya membangun komunikasi dua arah dengan peserta didik dan orang tua/wali untuk mendiskusikan tindak lanjut yang dapat mendukung peningkatan hasil belajar.
Q|33|Dalam setiap penyampaian laporan hasil belajar, saya memberikan rekomendasi yang realistis, terukur, dan dapat diterapkan sesuai kebutuhan peserta didik.
Q|34|Saya jarang memanfaatkan media komunikasi yang tersedia untuk memastikan informasi perkembangan belajar diterima dan dipahami oleh peserta didik maupun orang tua/wali.
Q|35|Saya menggunakan berbagai media komunikasi secara efektif, etis, dan sesuai kebutuhan untuk menyampaikan perkembangan belajar peserta didik.
C|kepribadian|Kompetensi Kepribadian
I|2.1|Kematangan moral, emosi, dan spiritual untuk berperilaku sesuai dengan kode etik guru.
F|2.1.1|Makna, Tujuan, dan Pandangan Hidup Guru Berdasarkan Prinsip Moral dan Keyakinannya terhadap Tuhan Yang Maha Esa
Q|1|Nilai-nilai moral dan keyakinan kepada Tuhan Yang Maha Esa menjadi landasan saya dalam mengambil keputusan serta menjalankan tanggung jawab sebagai guru.
Q|2|Dalam melaksanakan tugas, saya berupaya menunjukkan kejujuran, integritas, dan tanggung jawab sebagai bagian dari etika profesi guru.
Q|3|Dalam situasi tertentu, saya menganggap pertimbangan praktis lebih penting daripada mempertahankan prinsip moral dalam menjalankan tugas sebagai guru.
Q|4|Saya berusaha menjaga kesesuaian antara ucapan, sikap, dan tindakan sehingga dapat menjadi teladan bagi peserta didik maupun warga sekolah.
Q|5|Saya memaknai profesi guru sebagai bentuk pengabdian yang dijalankan dengan komitmen, ketulusan, dan tanggung jawab kepada peserta didik, masyarakat, serta Tuhan Yang Maha Esa.
Q|6|Saya jarang melakukan refleksi terhadap sikap dan perilaku saya untuk memastikan kesesuaiannya dengan nilai moral, etika profesi, dan keyakinan yang saya anut.
Q|7|Refleksi diri saya lakukan secara berkelanjutan sebagai upaya meningkatkan kualitas pribadi, profesional, dan spiritual dalam menjalankan profesi guru.
F|2.1.2|Pengelolaan Emosi dalam Menjalankan Peran sebagai Pendidik
Q|8|Dalam menghadapi berbagai situasi di kelas maupun tugas kedinasan, saya mampu mengendalikan emosi sehingga tetap bersikap profesional.
Q|9|Ketika menghadapi perbedaan pendapat atau permasalahan dengan peserta didik, orang tua, maupun rekan kerja, saya mengutamakan dialog yang tenang, objektif, dan berorientasi pada penyelesaian masalah.
Q|10|Dalam kondisi yang penuh tekanan, saya terkadang membiarkan emosi memengaruhi cara saya mengambil keputusan atau berinteraksi dengan orang lain.
Q|11|Saya tetap menunjukkan sikap sabar, adil, dan menghargai orang lain meskipun menghadapi tantangan dalam menjalankan tugas sebagai pendidik.
Q|12|Kritik, saran, dan masukan dari berbagai pihak saya terima secara terbuka sebagai bahan untuk memperbaiki kualitas diri dan kinerja profesional.
Q|13|Saya jarang melakukan evaluasi terhadap cara saya mengelola emosi setelah menghadapi situasi yang menantang dalam pekerjaan.
Q|14|Saya berupaya menjaga kestabilan emosi melalui pengelolaan diri yang berkelanjutan agar dapat menjalankan peran sebagai pendidik secara profesional.
F|2.1.3|Penerapan Kode Etik Guru dalam Bekerja dan Pembelajaran
Q|15|Dalam melaksanakan pembelajaran maupun tugas profesional lainnya, saya berupaya menerapkan kode etik guru secara konsisten sebagai pedoman dalam bertindak.
Q|16|Saya membangun hubungan yang profesional dengan peserta didik, orang tua/wali, rekan sejawat, dan pemangku kepentingan sesuai dengan prinsip-prinsip kode etik guru.
Q|17|Dalam situasi tertentu, saya menganggap aturan atau kode etik dapat dikesampingkan apabila dinilai dapat mempercepat penyelesaian pekerjaan.
Q|18|Saya menjaga kerahasiaan data dan informasi peserta didik serta menggunakannya hanya untuk kepentingan pembelajaran dan pengembangan peserta didik sesuai dengan ketentuan yang berlaku.
Q|19|Saya menghindari tindakan yang berpotensi menimbulkan konflik kepentingan maupun mengurangi kepercayaan terhadap profesi guru.
Q|20|Saya jarang menelaah kembali apakah tindakan dan keputusan yang saya ambil telah sepenuhnya sesuai dengan kode etik profesi guru.
Q|21|Dalam kehidupan di sekolah maupun di masyarakat, saya berupaya menjadi teladan melalui sikap profesional, integritas, dan kepatuhan terhadap etika profesi guru.
I|2.2|Pengembangan diri melalui kebiasaan refleksi
F|2.2.1|Refleksi dan Perencanaan Kebutuhan Pengembangan Diri yang Berpusat pada Peserta Didik
Q|1|Saya secara rutin melakukan refleksi terhadap proses pembelajaran untuk mengetahui aspek yang perlu ditingkatkan dalam kompetensi saya sebagai guru.
Q|2|Hasil refleksi pembelajaran saya gunakan sebagai dasar dalam menyusun rencana pengembangan diri yang berfokus pada peningkatan kualitas belajar peserta didik.
Q|3|Saya cenderung menyusun kegiatan pengembangan diri berdasarkan kesempatan yang tersedia tanpa terlebih dahulu mengidentifikasi kebutuhan pembelajaran di kelas.
Q|4|Masukan dari peserta didik, rekan sejawat, maupun kepala sekolah saya manfaatkan untuk menentukan prioritas pengembangan kompetensi yang perlu saya lakukan.
Q|5|Saya menetapkan target pengembangan diri yang realistis, terukur, dan selaras dengan kebutuhan pembelajaran peserta didik.
Q|6|Saya jarang meninjau kembali sejauh mana rencana pengembangan diri yang telah saya lakukan memberikan dampak terhadap kualitas pembelajaran.
Q|7|Saya mengevaluasi hasil pelaksanaan pengembangan diri secara berkala sebagai dasar untuk menyempurnakan praktik pembelajaran pada periode berikutnya.
F|2.2.2|Cara Adaptif Melakukan Pengembangan Diri untuk Meningkatkan Pembelajaran yang Berpusat pada Peserta Didik
Q|8|Saya memilih kegiatan pengembangan diri yang selaras dengan kebutuhan belajar peserta didik serta perkembangan praktik pembelajaran.
Q|9|Berbagai sumber belajar, komunitas belajar, pelatihan, maupun teknologi digital saya manfaatkan untuk meningkatkan kompetensi secara berkelanjutan.
Q|10|Saya cenderung mempertahankan cara mengajar yang sudah biasa digunakan meskipun tersedia pendekatan baru yang lebih sesuai dengan kebutuhan peserta didik.
Q|11|Saya menyesuaikan strategi pengembangan diri dengan perubahan kurikulum, perkembangan ilmu pengetahuan, serta tantangan pembelajaran yang dihadapi di kelas.
Q|12|Diskusi dan kolaborasi dengan rekan sejawat saya manfaatkan sebagai sarana untuk menemukan solusi atas permasalahan pembelajaran dan meningkatkan kualitas praktik mengajar.
Q|13|Saya jarang mencari kesempatan belajar baru apabila merasa metode pembelajaran yang saya gunakan sudah cukup efektif.
Q|14|Saya menunjukkan kesiapan untuk mempelajari serta menerapkan pendekatan, metode, dan inovasi baru guna meningkatkan kualitas pembelajaran yang berpusat pada peserta didik.
F|2.2.3|Penerapan Hasil Pengembangan Diri untuk Meningkatkan Pembelajaran Peserta Didik
Q|15|Pengetahuan dan keterampilan yang saya peroleh melalui kegiatan pengembangan diri saya terapkan untuk meningkatkan kualitas proses pembelajaran di kelas.
Q|16|Saya menyesuaikan hasil pengembangan diri dengan karakteristik, kebutuhan, serta kemampuan belajar peserta didik agar pembelajaran menjadi lebih efektif.
Q|17|Hasil kegiatan pengembangan diri sering kali tidak saya terapkan karena saya merasa cara pembelajaran yang selama ini digunakan sudah memadai.
Q|18|Saya menilai dampak penerapan hasil pengembangan diri terhadap keterlibatan, motivasi, dan capaian belajar peserta didik sebagai dasar perbaikan pembelajaran.
Q|19|Praktik baik yang saya peroleh dari kegiatan pengembangan diri saya bagikan kepada rekan sejawat untuk mendukung peningkatan mutu pembelajaran di sekolah.
Q|20|Saya jarang melakukan penyesuaian terhadap pembelajaran meskipun hasil evaluasi menunjukkan bahwa penerapan pengembangan diri belum memberikan dampak yang optimal.
Q|21|Saya secara berkelanjutan menyempurnakan pembelajaran berdasarkan hasil evaluasi atas penerapan pengetahuan dan keterampilan yang diperoleh dari kegiatan pengembangan diri.
I|2.3|Orientasi berpusat pada peserta didik
F|2.3.1|Interaksi Aktif dan Empatik terhadap Peserta Didik
Q|1|Saya membangun komunikasi yang terbuka, hangat, dan empatik sehingga peserta didik merasa dihargai dalam setiap proses pembelajaran.
Q|2|Sebelum memberikan tanggapan atau solusi, saya berusaha mendengarkan pendapat, pertanyaan, maupun kesulitan peserta didik dengan penuh perhatian.
Q|3|Saya cenderung memberikan tanggapan secara cepat tanpa terlebih dahulu memahami sudut pandang atau kondisi yang dialami peserta didik.
Q|4|Cara saya berinteraksi disesuaikan dengan karakteristik, kebutuhan, dan kondisi emosional setiap peserta didik agar mereka merasa nyaman dalam belajar.
Q|5|Saya berupaya membangun hubungan yang positif untuk meningkatkan rasa percaya diri, motivasi, serta keterlibatan aktif peserta didik dalam pembelajaran.
Q|6|Saya jarang mengevaluasi kembali apakah cara saya berkomunikasi telah membuat seluruh peserta didik merasa didengar dan dihargai.
Q|7|Saya secara berkala merefleksikan kualitas interaksi dengan peserta didik sebagai dasar untuk meningkatkan layanan pembelajaran yang lebih berpihak kepada mereka.
F|2.3.2|Respek terhadap Hak Peserta Didik dalam Menjalankan Peran sebagai Guru
Q|8|Saya memperlakukan setiap peserta didik secara adil tanpa membedakan latar belakang, kemampuan, agama, suku, gender, maupun kondisi lainnya.
Q|9|Saya menghargai hak setiap peserta didik untuk belajar, menyampaikan pendapat, serta mengembangkan potensi dirinya secara optimal.
Q|10|Dalam situasi tertentu, saya lebih banyak memberikan perhatian kepada peserta didik tertentu dibandingkan peserta didik lainnya tanpa mempertimbangkan kebutuhan mereka secara proporsional.
Q|11|Saya menjaga kerahasiaan informasi pribadi peserta didik dan menggunakannya secara bertanggung jawab sesuai dengan etika profesi dan ketentuan yang berlaku.
Q|12|Setiap keputusan pembelajaran yang saya ambil mempertimbangkan kepentingan terbaik bagi perkembangan akademik maupun nonakademik peserta didik.
Q|13|Saya jarang mengevaluasi apakah seluruh peserta didik telah memperoleh kesempatan yang sama untuk berpartisipasi dan berkembang dalam pembelajaran.
Q|14|Saya berupaya menciptakan lingkungan pembelajaran yang menghormati martabat, hak, dan keberagaman setiap peserta didik sehingga semua merasa diterima dan dihargai.
F|2.3.3|Kepedulian terhadap Keselamatan dan Keamanan Peserta Didik sebagai Individu dan Kelompok
Q|15|Sebelum dan selama pembelajaran berlangsung, saya mengidentifikasi berbagai potensi risiko yang dapat mengganggu keselamatan maupun keamanan peserta didik.
Q|16|Saya menerapkan langkah-langkah pencegahan untuk menciptakan lingkungan belajar yang aman, nyaman, inklusif, serta bebas dari kekerasan, perundungan, dan bentuk diskriminasi.
Q|17|Saya baru memberikan perhatian terhadap aspek keselamatan peserta didik apabila terjadi masalah atau insiden dalam kegiatan pembelajaran.
Q|18|Saya memberikan pendampingan dan perlindungan kepada peserta didik yang menghadapi situasi berisiko terhadap keselamatan fisik maupun kesejahteraan psikologisnya.
Q|19|Saya menjalin kerja sama dengan orang tua/wali, rekan guru, dan pihak terkait untuk menjaga keselamatan, keamanan, serta kesejahteraan peserta didik di lingkungan sekolah.
Q|20|Saya jarang melakukan peninjauan kembali terhadap efektivitas langkah-langkah yang telah diterapkan untuk melindungi keselamatan dan keamanan peserta didik.
Q|21|Saya mengevaluasi serta menindaklanjuti upaya perlindungan peserta didik secara berkala agar lingkungan pembelajaran semakin aman dan mendukung perkembangan mereka.
C|sosial|Kompetensi Sosial
I|3.1|Kolaborasi untuk peningkatan pembelajaran
F|3.1.1|Komunikasi Efektif dengan Warga Sekolah yang Mengarah pada Peningkatan Pembelajaran
Q|1|Saya membangun komunikasi yang terbuka, santun, dan profesional dengan warga sekolah untuk mendukung peningkatan kualitas pembelajaran.
Q|2|Saya menyampaikan informasi, gagasan, maupun hasil pembelajaran secara jelas sehingga mudah dipahami oleh warga sekolah yang berkepentingan.
Q|3|Saya cenderung menyampaikan informasi hanya ketika diminta, meskipun informasi tersebut penting untuk mendukung kelancaran pembelajaran.
Q|4|Saya menghargai masukan, pendapat, dan kebutuhan warga sekolah sebagai bahan untuk memperbaiki proses pembelajaran.
Q|5|Saya memanfaatkan berbagai media komunikasi secara efektif untuk memperkuat koordinasi dan kolaborasi dalam mendukung pembelajaran.
Q|6|Saya jarang menilai kembali apakah komunikasi yang saya lakukan telah mendukung kerja sama yang efektif dalam meningkatkan pembelajaran.
Q|7|Saya secara berkala mengevaluasi efektivitas komunikasi dengan warga sekolah sebagai dasar untuk memperkuat kolaborasi dalam meningkatkan kualitas pembelajaran.
F|3.1.2|Pengorganisasian Tugas-Tugas Bersama Rekan Sejawat untuk Peningkatan Pembelajaran
Q|8|Saya merencanakan pembagian tugas bersama rekan sejawat secara jelas dengan mempertimbangkan kompetensi dan tanggung jawab masing-masing anggota tim.
Q|9|Saya berperan aktif dalam mengoordinasikan pelaksanaan kegiatan bersama agar tujuan pembelajaran yang telah disepakati dapat tercapai secara optimal.
Q|10|Saya cenderung menyerahkan pengorganisasian tugas kepada rekan lain meskipun saya memiliki kesempatan untuk berkontribusi dalam proses tersebut.
Q|11|Saya mendorong terciptanya kerja sama yang saling mendukung, menghargai, dan bertanggung jawab di antara anggota tim.
Q|12|Saya memantau pelaksanaan tugas bersama serta memberikan bantuan atau solusi apabila terdapat kendala yang dihadapi rekan sejawat.
Q|13|Saya jarang melakukan refleksi bersama tim untuk mengevaluasi efektivitas pembagian tugas dan hasil kerja yang telah dicapai.
Q|14|Saya berpartisipasi dalam refleksi bersama rekan sejawat untuk menyempurnakan pembagian tugas dan meningkatkan kualitas pembelajaran pada kegiatan berikutnya.
F|3.1.3|Inisiatif Berkontribusi untuk Mencapai Tujuan Bersama dalam Peningkatan Pembelajaran
Q|15|Saya secara proaktif mengajukan gagasan atau inovasi yang dapat mendukung peningkatan kualitas pembelajaran di sekolah.
Q|16|Saya bersedia mengambil peran dan tanggung jawab dalam berbagai kegiatan kolaboratif yang bertujuan meningkatkan mutu pembelajaran.
Q|17|Saya lebih memilih menunggu arahan atau penugasan daripada berinisiatif memberikan kontribusi dalam penyelesaian permasalahan pembelajaran.
Q|18|Saya membantu rekan sejawat menemukan solusi atas permasalahan pembelajaran tanpa harus menunggu permintaan atau penugasan khusus.
Q|19|Saya berkontribusi dalam membangun budaya saling belajar, berbagi praktik baik, serta memberikan umpan balik yang konstruktif di lingkungan sekolah.
Q|20|Saya jarang menilai kembali sejauh mana kontribusi yang saya berikan telah mendukung pencapaian tujuan bersama dalam meningkatkan pembelajaran.
Q|21|Saya secara berkala mengevaluasi kontribusi yang telah saya berikan dan berupaya meningkatkannya agar tujuan bersama dalam peningkatan pembelajaran dapat tercapai secara optimal.
I|3.2|Keterlibatan orangtua/wali dan masyarakat dalam pembelajaran
F|3.2.1|Pendampingan Orang Tua/Wali dalam Mendukung Pembelajaran di Rumah yang Berpusat pada Peserta Didik
Q|1|Saya membangun komunikasi yang berkelanjutan dengan orang tua/wali mengenai perkembangan belajar dan kebutuhan peserta didik sebagai dasar pendampingan belajar di rumah.
Q|2|Saya memberikan arahan yang jelas kepada orang tua/wali mengenai cara mendampingi peserta didik belajar di rumah sesuai dengan karakteristik, kemampuan, dan kebutuhan belajarnya.
Q|3|Saya cenderung menyerahkan sepenuhnya proses pendampingan belajar di rumah kepada orang tua/wali tanpa melakukan koordinasi secara berkala.
Q|4|Saya melibatkan orang tua/wali dalam menyusun strategi pendampingan belajar untuk mendukung pencapaian tujuan pembelajaran peserta didik.
Q|5|Masukan dan umpan balik dari orang tua/wali mengenai kegiatan belajar di rumah saya manfaatkan untuk menyempurnakan proses pembelajaran di sekolah.
Q|6|Saya jarang mengevaluasi efektivitas kerja sama dengan orang tua/wali dalam mendukung perkembangan belajar peserta didik.
Q|7|Saya secara berkala meninjau efektivitas kemitraan dengan orang tua/wali sebagai dasar untuk meningkatkan kualitas pendampingan belajar peserta didik di rumah.
F|3.2.2|Pelibatan Pengetahuan, Keahlian, dan Perspektif Orang Tua/Wali dan Masyarakat dalam Pembelajaran yang Berpusat pada Peserta Didik
Q|8|Saya mengidentifikasi pengetahuan, keahlian, pengalaman, dan potensi yang dimiliki orang tua/wali maupun masyarakat untuk mendukung pembelajaran peserta didik.
Q|9|Saya melibatkan orang tua/wali atau anggota masyarakat sebagai narasumber, mitra, atau fasilitator sesuai dengan tujuan dan kebutuhan pembelajaran.
Q|10|Saya cenderung melaksanakan pembelajaran tanpa memanfaatkan potensi orang tua/wali atau masyarakat meskipun tersedia sumber daya yang relevan.
Q|11|Saya merancang kegiatan pembelajaran yang memanfaatkan lingkungan sekitar serta potensi masyarakat sebagai sumber belajar yang kontekstual dan bermakna bagi peserta didik.
Q|12|Saya membangun kemitraan yang saling menghargai dengan orang tua/wali dan masyarakat untuk memperkaya pengalaman belajar peserta didik.
Q|13|Saya jarang mengevaluasi sejauh mana keterlibatan orang tua/wali dan masyarakat memberikan dampak terhadap kualitas pembelajaran peserta didik.
Q|14|Saya melakukan evaluasi terhadap kontribusi orang tua/wali dan masyarakat sebagai dasar untuk memperkuat kolaborasi yang berkelanjutan dalam mendukung pembelajaran.
I|3.3|Keterlibatan dalam organisasi profesi dan jejaring yang lebih luas untuk peningkatan pembelajaran
F|3.3.1|Berpartisipasi pada Beragam Peran untuk Pemecahan Masalah Pembelajaran dalam Organisasi Profesi dan Jejaring yang Lebih Luas
Q|1|Saya berpartisipasi secara aktif dalam organisasi profesi, komunitas belajar, atau jejaring pendidikan untuk membahas dan menyelesaikan berbagai permasalahan pembelajaran.
Q|2|Saya mengambil peran sesuai kompetensi yang saya miliki dalam kegiatan organisasi profesi atau jejaring guna mendukung peningkatan mutu pembelajaran.
Q|3|Saya lebih sering menjadi peserta pasif dalam organisasi profesi dan jarang memberikan gagasan atau kontribusi terhadap penyelesaian masalah pembelajaran.
Q|4|Saya berkolaborasi dengan anggota organisasi profesi atau jejaring untuk mengembangkan solusi terhadap tantangan pembelajaran yang dihadapi di sekolah.
Q|5|Saya memanfaatkan hasil diskusi, kajian, praktik baik, atau rekomendasi dari organisasi profesi sebagai dasar untuk memperbaiki praktik pembelajaran di kelas.
Q|6|Saya jarang mengevaluasi manfaat keterlibatan saya dalam organisasi profesi terhadap peningkatan kualitas pembelajaran yang saya lakukan.
Q|7|Saya secara berkala merefleksikan kontribusi dan peran saya dalam organisasi profesi atau jejaring sebagai dasar untuk meningkatkan kualitas pembelajaran secara berkelanjutan.
F|3.3.2|Berbagi Praktik Baik dan Karya untuk Peningkatan Pembelajaran yang Berpusat pada Peserta Didik dalam Organisasi dan Jejaring yang Lebih Luas
Q|8|Saya mendokumentasikan praktik baik, inovasi, atau hasil pembelajaran sebagai bahan untuk dibagikan kepada rekan sejawat melalui organisasi profesi maupun jejaring pendidikan.
Q|9|Saya secara aktif berbagi pengalaman, strategi, media, atau hasil pembelajaran yang terbukti meningkatkan keterlibatan dan capaian belajar peserta didik.
Q|10|Saya cenderung menyimpan pengalaman atau inovasi pembelajaran untuk digunakan sendiri daripada membagikannya kepada rekan sejawat.
Q|11|Saya mempresentasikan karya, hasil refleksi, praktik baik, atau inovasi pembelajaran dalam forum organisasi profesi, komunitas belajar, maupun jejaring pendidikan.
Q|12|Saya menerima dan memanfaatkan masukan dari anggota organisasi profesi atau jejaring sebagai bahan penyempurnaan praktik pembelajaran yang saya lakukan.
Q|13|Saya jarang mengevaluasi manfaat kegiatan berbagi praktik baik terhadap peningkatan kualitas pembelajaran yang saya lakukan.
Q|14|Saya berkontribusi secara berkelanjutan dalam membangun budaya berbagi praktik baik, saling belajar, dan pembelajaran kolaboratif di organisasi profesi maupun jejaring pendidikan.
C|profesional|Kompetensi Profesional
I|4.1|Pengetahuan konten pembelajaran dan cara mengajarkannya
F|4.1.1|Struktur dan Alur Pengetahuan dari Suatu Bidang Keilmuan yang Relevan untuk Pembelajaran
Q|1|Saya memahami struktur konsep, prinsip, dan keterkaitan antarmateri dalam bidang keilmuan yang saya ampu secara menyeluruh sehingga mendukung proses pembelajaran.
Q|2|Saya menjelaskan hubungan antarkonsep secara runtut dan sistematis sehingga peserta didik mampu membangun pemahaman yang utuh.
Q|3|Saya menyampaikan materi pembelajaran tanpa selalu menghubungkannya dengan konsep prasyarat maupun konsep lanjutan yang relevan.
Q|4|Saya mengaitkan materi pembelajaran dengan perkembangan ilmu pengetahuan, konteks kehidupan, serta karakteristik peserta didik agar lebih bermakna.
Q|5|Saya meninjau kembali urutan penyajian materi untuk memastikan alur konsep mendukung pencapaian tujuan pembelajaran secara optimal.
Q|6|Saya jarang mengevaluasi apakah struktur penyajian materi yang saya gunakan sudah membantu peserta didik memahami keterkaitan antarkonsep.
Q|7|Saya melakukan refleksi dan penyempurnaan terhadap penyajian struktur materi berdasarkan hasil pembelajaran serta perkembangan bidang keilmuan yang saya ampu.
F|4.1.2|Identifikasi Pengetahuan Konten yang Relevan untuk Mencapai Tujuan Pembelajaran
Q|8|Saya mengidentifikasi materi esensial yang paling relevan untuk mendukung tercapainya tujuan pembelajaran secara efektif.
Q|9|Saya menentukan konten pembelajaran berdasarkan capaian pembelajaran, karakteristik peserta didik, serta konteks pembelajaran yang dihadapi.
Q|10|Saya cenderung menggunakan seluruh materi yang tersedia tanpa mempertimbangkan apakah konten tersebut benar-benar mendukung pencapaian tujuan pembelajaran.
Q|11|Saya mengaitkan konten pembelajaran dengan penerapannya dalam kehidupan sehari-hari agar peserta didik memperoleh pengalaman belajar yang lebih bermakna.
Q|12|Saya mengidentifikasi kemungkinan miskonsepsi peserta didik sebagai dasar dalam menentukan materi yang perlu mendapat penekanan selama pembelajaran.
Q|13|Saya jarang meninjau kembali apakah konten yang saya ajarkan masih relevan dengan perkembangan ilmu pengetahuan dan kebutuhan belajar peserta didik.
Q|14|Saya secara berkala mengevaluasi dan menyempurnakan pemilihan konten pembelajaran agar tetap selaras dengan tujuan pembelajaran, perkembangan ilmu pengetahuan, dan kebutuhan peserta didik.
F|4.1.3|Pengorganisasian Pengetahuan Konten yang Relevan terhadap Pembelajaran
Q|15|Saya mengorganisasikan materi pembelajaran secara sistematis dari konsep dasar menuju konsep yang lebih kompleks agar mudah dipahami peserta didik.
Q|16|Saya menyusun urutan penyampaian materi secara bertahap dan berkesinambungan sehingga membantu peserta didik membangun pemahaman secara utuh.
Q|17|Saya cenderung menyampaikan materi mengikuti urutan sumber belajar yang tersedia tanpa selalu mempertimbangkan kesiapan belajar peserta didik.
Q|18|Saya mengintegrasikan konsep-konsep yang saling berkaitan agar peserta didik mampu memahami hubungan antarmateri secara menyeluruh.
Q|19|Saya menyesuaikan pengorganisasian materi dengan kemampuan awal, karakteristik, dan kebutuhan belajar peserta didik untuk mendukung pembelajaran yang efektif.
Q|20|Saya jarang mengevaluasi apakah pengorganisasian materi yang saya gunakan telah mendukung peningkatan hasil belajar peserta didik.
Q|21|Saya melakukan evaluasi dan penyempurnaan terhadap pengorganisasian materi berdasarkan hasil belajar peserta didik serta refleksi pembelajaran yang telah dilakukan.
I|4.2|Karakteristik dan cara belajar peserta didik
F|4.2.1|Tahapan Perkembangan dan Karakteristik yang Relevan dengan Kebutuhan Belajar
Q|1|Saya mengidentifikasi tahapan perkembangan peserta didik sebagai dasar dalam merancang pembelajaran yang sesuai dengan kebutuhan belajarnya.
Q|2|Saya menyesuaikan strategi, metode, dan aktivitas pembelajaran berdasarkan perkembangan kognitif, sosial, emosional, serta fisik peserta didik.
Q|3|Saya cenderung menggunakan pendekatan pembelajaran yang sama untuk seluruh peserta didik tanpa mempertimbangkan perbedaan tahapan perkembangannya.
Q|4|Saya mengenali perubahan karakteristik perkembangan peserta didik dan memanfaatkannya sebagai dasar dalam menentukan pendekatan pembelajaran yang tepat.
Q|5|Saya menggunakan informasi tentang perkembangan peserta didik untuk menyesuaikan pengalaman belajar agar lebih sesuai dengan kebutuhan mereka.
Q|6|Saya jarang mengevaluasi apakah pembelajaran yang saya lakukan telah sesuai dengan tahapan perkembangan dan karakteristik peserta didik.
Q|7|Saya secara berkala merefleksikan dan memperbaiki pembelajaran berdasarkan hasil evaluasi terhadap kesesuaian pembelajaran dengan perkembangan peserta didik.
F|4.2.2|Latar Belakang Sosial, Budaya, Agama, dan Ekonomi yang Relevan dengan Kebutuhan Belajar Peserta Didik
Q|8|Saya mengidentifikasi latar belakang sosial, budaya, agama, dan ekonomi peserta didik sebagai dasar dalam merancang pembelajaran yang berpihak pada kebutuhan belajar mereka.
Q|9|Saya menyesuaikan strategi, metode, dan aktivitas pembelajaran agar menghargai keberagaman latar belakang peserta didik tanpa mengurangi kualitas pembelajaran.
Q|10|Saya cenderung menggunakan pendekatan pembelajaran yang sama tanpa mempertimbangkan perbedaan latar belakang sosial, budaya, agama, atau ekonomi peserta didik.
Q|11|Saya mengintegrasikan nilai-nilai keberagaman dalam pembelajaran untuk membangun lingkungan belajar yang inklusif, saling menghargai, dan bebas dari diskriminasi.
Q|12|Saya mengantisipasi hambatan belajar yang mungkin muncul akibat perbedaan kondisi sosial, budaya, agama, maupun ekonomi peserta didik serta menyiapkan alternatif solusi pembelajaran.
Q|13|Saya jarang mengevaluasi apakah pembelajaran yang saya lakukan telah mengakomodasi keberagaman latar belakang peserta didik secara adil.
Q|14|Saya secara berkala mengevaluasi dampak pembelajaran yang memperhatikan keberagaman latar belakang peserta didik sebagai dasar untuk meningkatkan keterlibatan dan hasil belajar mereka.
F|4.2.3|Potensi, Minat, dan Cara Belajar Peserta Didik yang Relevan dengan Kebutuhan Belajar Peserta Didik
Q|15|Saya mengidentifikasi potensi, minat, dan cara belajar peserta didik sebagai dasar dalam merancang pembelajaran yang sesuai dengan kebutuhan mereka.
Q|16|Saya menyesuaikan metode, media, serta aktivitas pembelajaran berdasarkan potensi, minat, dan karakteristik belajar peserta didik agar pembelajaran lebih efektif.
Q|17|Saya cenderung menggunakan strategi pembelajaran yang sama meskipun peserta didik memiliki potensi, minat, dan cara belajar yang berbeda.
Q|18|Saya memberikan kesempatan kepada peserta didik untuk mengembangkan potensi dan minatnya melalui berbagai aktivitas, tugas, maupun pengalaman belajar yang beragam.
Q|19|Saya memanfaatkan hasil identifikasi potensi, minat, dan cara belajar peserta didik untuk meningkatkan keterlibatan serta keberhasilan belajar mereka.
Q|20|Saya jarang meninjau kembali apakah pembelajaran yang saya lakukan telah mengakomodasi potensi, minat, dan cara belajar peserta didik secara optimal.
Q|21|Saya secara berkala mengevaluasi efektivitas pembelajaran yang disesuaikan dengan potensi, minat, dan cara belajar peserta didik sebagai dasar penyempurnaan pembelajaran berikutnya.
F|4.2.4|Karakteristik dan Cara Belajar Peserta Didik Penyandang Disabilitas
Q|22|Saya mengidentifikasi potensi, minat, dan cara belajar peserta didik sebagai dasar dalam merancang pembelajaran yang sesuai dengan kebutuhan mereka.
Q|23|Saya menyesuaikan metode, media, serta aktivitas pembelajaran berdasarkan potensi, minat, dan karakteristik belajar peserta didik agar pembelajaran lebih efektif.
Q|24|Saya cenderung menggunakan strategi pembelajaran yang sama meskipun peserta didik memiliki potensi, minat, dan cara belajar yang berbeda.
Q|25|Saya memberikan kesempatan kepada peserta didik untuk mengembangkan potensi dan minatnya melalui berbagai aktivitas, tugas, maupun pengalaman belajar yang beragam.
Q|26|Saya memanfaatkan hasil identifikasi potensi, minat, dan cara belajar peserta didik untuk meningkatkan keterlibatan serta keberhasilan belajar mereka.
Q|27|Saya jarang meninjau kembali apakah pembelajaran yang saya lakukan telah mengakomodasi potensi, minat, dan cara belajar peserta didik secara optimal.
Q|28|Saya secara berkala mengevaluasi efektivitas pembelajaran yang disesuaikan dengan potensi, minat, dan cara belajar peserta didik sebagai dasar penyempurnaan pembelajaran berikutnya.
F|4.2.5|Keragaman Kebutuhan Belajar Peserta Didik untuk Pembelajaran yang Inklusif
Q|29|Saya mengidentifikasi keragaman kebutuhan belajar peserta didik sebagai dasar dalam merancang pembelajaran yang inklusif dan berpihak pada setiap peserta didik.
Q|30|Saya menerapkan strategi pembelajaran yang memberikan kesempatan belajar yang setara sehingga seluruh peserta didik dapat berpartisipasi secara optimal.
Q|31|Saya cenderung menggunakan materi, aktivitas, dan penilaian yang sama untuk seluruh peserta didik meskipun kebutuhan belajarnya berbeda.
Q|32|Saya menyesuaikan materi, aktivitas pembelajaran, dan asesmen untuk mengakomodasi perbedaan kemampuan, karakteristik, dan kebutuhan belajar peserta didik.
Q|33|Saya membangun suasana belajar yang menghargai keberagaman sehingga setiap peserta didik merasa diterima, dihargai, dan memiliki kesempatan berkembang secara optimal.
Q|34|Saya jarang meninjau kembali apakah pembelajaran yang saya lakukan telah memenuhi kebutuhan belajar seluruh peserta didik secara inklusif.
Q|35|Saya secara berkala mengevaluasi efektivitas pembelajaran inklusif berdasarkan perkembangan dan ketercapaian belajar seluruh peserta didik sebagai dasar penyempurnaan pembelajaran.
I|4.3|Kurikulum dan cara menggunakannya
F|4.3.1|Penggunaan Kurikulum dalam Proses Pembelajaran yang Berpusat pada Peserta Didik
Q|1|Saya menganalisis capaian pembelajaran, tujuan pembelajaran, dan karakteristik peserta didik sebagai dasar dalam merancang pembelajaran yang berpusat pada peserta didik.
Q|2|Saya menerapkan kurikulum secara fleksibel dengan menyesuaikan strategi, materi, dan pengalaman belajar sesuai kebutuhan serta konteks peserta didik.
Q|3|Saya cenderung melaksanakan pembelajaran hanya mengikuti urutan materi dalam dokumen kurikulum tanpa menyesuaikannya dengan kebutuhan belajar peserta didik.
Q|4|Saya mengintegrasikan kompetensi, nilai, dan pengalaman belajar yang relevan dengan tujuan kurikulum agar pembelajaran lebih bermakna bagi peserta didik.
Q|5|Saya menggunakan hasil pemantauan perkembangan belajar peserta didik sebagai dasar untuk menyesuaikan pelaksanaan kurikulum selama proses pembelajaran.
Q|6|Saya jarang merefleksikan apakah implementasi kurikulum yang saya lakukan telah benar-benar mendukung pembelajaran yang berpusat pada peserta didik.
Q|7|Saya secara berkala mengevaluasi penerapan kurikulum dan melakukan penyempurnaan agar kualitas pembelajaran semakin sesuai dengan kebutuhan belajar peserta didik.
F|4.3.2|Penggunaan Asesmen untuk Meningkatkan Pembelajaran yang Berpusat pada Peserta Didik
Q|8|Saya memanfaatkan hasil asesmen diagnostik sebagai dasar dalam merancang pembelajaran yang sesuai dengan kebutuhan belajar peserta didik.
Q|9|Saya menggunakan informasi dari asesmen formatif untuk menyesuaikan strategi, metode, atau aktivitas pembelajaran selama proses belajar berlangsung.
Q|10|Saya cenderung menggunakan hasil asesmen hanya untuk menentukan nilai akhir tanpa memanfaatkannya sebagai dasar perbaikan proses pembelajaran.
Q|11|Saya menganalisis hasil asesmen guna mengidentifikasi kekuatan, kesulitan, serta kebutuhan belajar setiap peserta didik sebagai dasar penyusunan tindak lanjut pembelajaran.
Q|12|Saya menggunakan hasil asesmen untuk merancang program remedial, pengayaan, maupun bentuk tindak lanjut lain yang sesuai dengan kebutuhan peserta didik.
Q|13|Saya jarang mengevaluasi apakah asesmen yang saya lakukan telah memberikan informasi yang bermanfaat untuk meningkatkan kualitas pembelajaran.
Q|14|Saya secara berkala merefleksikan efektivitas penggunaan asesmen dalam meningkatkan keterlibatan, perkembangan, dan capaian belajar peserta didik sebagai dasar penyempurnaan pembelajaran.
F|4.3.3|Penggunaan Strategi untuk Meningkatkan Pembelajaran yang Berpusat pada Peserta Didik
Q|15|Saya memanfaatkan hasil asesmen diagnostik sebagai dasar dalam merancang pembelajaran yang sesuai dengan kebutuhan belajar peserta didik.
Q|16|Saya menggunakan informasi dari asesmen formatif untuk menyesuaikan strategi, metode, atau aktivitas pembelajaran selama proses belajar berlangsung.
Q|17|Saya cenderung menggunakan hasil asesmen hanya untuk menentukan nilai akhir tanpa memanfaatkannya sebagai dasar perbaikan proses pembelajaran.
Q|18|Saya menganalisis hasil asesmen guna mengidentifikasi kekuatan, kesulitan, serta kebutuhan belajar setiap peserta didik sebagai dasar penyusunan tindak lanjut pembelajaran.
Q|19|Saya menggunakan hasil asesmen untuk merancang program remedial, pengayaan, maupun bentuk tindak lanjut lain yang sesuai dengan kebutuhan peserta didik.
Q|20|Saya jarang mengevaluasi apakah asesmen yang saya lakukan telah memberikan informasi yang bermanfaat untuk meningkatkan kualitas pembelajaran.
Q|21|Saya secara berkala merefleksikan efektivitas penggunaan asesmen dalam meningkatkan keterlibatan, perkembangan, dan capaian belajar peserta didik sebagai dasar penyempurnaan pembelajaran.
F|4.3.4|Penggunaan Strategi Pembelajaran yang Efektif untuk Capaian Belajar Literasi dan Numerasi Peserta Didik
Q|22|Saya mengintegrasikan penguatan literasi dan numerasi ke dalam pembelajaran sesuai dengan karakteristik mata pelajaran yang saya ampu.
Q|23|Saya menerapkan strategi pembelajaran yang mendorong peserta didik membaca, memahami informasi, bernalar, serta memecahkan masalah secara mandiri maupun kolaboratif.
Q|24|Saya cenderung memisahkan pengembangan literasi dan numerasi dari pembelajaran karena menganggap keduanya hanya menjadi tanggung jawab mata pelajaran tertentu.
Q|25|Saya menggunakan situasi atau permasalahan dalam kehidupan sehari-hari sebagai konteks untuk memperkuat kemampuan literasi dan numerasi peserta didik.
Q|26|Saya memanfaatkan berbagai sumber belajar, media, dan teknologi yang relevan untuk meningkatkan kemampuan literasi dan numerasi peserta didik.
Q|27|Saya jarang mengevaluasi apakah strategi pembelajaran yang saya gunakan telah memberikan dampak terhadap peningkatan kemampuan literasi dan numerasi peserta didik.
Q|28|Saya secara berkala merefleksikan dan menyempurnakan strategi pembelajaran berdasarkan perkembangan kemampuan literasi dan numerasi peserta didik.
DATA;

    public function run(): void
    {
        $forms = $this->forms();

        $assessment = Assessment::updateOrCreate(
            ['kode_assessment' => self::ASSESSMENT_CODE],
            [
                'judul' => 'Angket Kompetensi Guru - Skala Likert',
                'slug' => Str::slug('Angket Kompetensi Guru Skala Likert'),
                'deskripsi' => 'Instrumen pemetaan kompetensi guru BBGTK Sulawesi Selatan berbasis skala Likert untuk kompetensi pedagogik, kepribadian, sosial, dan profesional.',
                'petunjuk' => 'Pilihlah jawaban yang sesuai dengan kondisi atau pemahaman Anda saat ini secara jujur. Skala Likert: 1 = Sangat Tidak Setuju/Mampu/Menguasai; 2 = Tidak Setuju/Mampu/Menguasai; 3 = Cukup Setuju/Mampu/Menguasai; 4 = Setuju/Mampu/Menguasai; 5 = Sangat Setuju/Mampu/Menguasai.',
                'instrument_type' => AssessmentInstrumentType::SKALA_LIKERT->value,
                'target_ketenagaan' => AssessmentKetenagaanType::TENAGA_PENDIDIK->value,
                'scoring_config' => $this->assessmentScoringConfig($forms),
                'status' => 'publish',
                'is_active' => true,
            ]
        );

        $assessment->forms()->delete();

        foreach ($forms as $formIndex => $formData) {
            $form = $assessment->forms()->create([
                'judul_form' => $formData['subindikator_kode'].' '.$formData['subindikator_label'],
                'kode_form' => 'FORM-LIKERT-'.$this->compactCode($formData['subindikator_kode']),
                'deskripsi' => sprintf(
                    '%s - Indikator %s: %s',
                    $formData['kompetensi_label'],
                    $formData['indikator_kode'],
                    $formData['indikator_label']
                ),
                'kompetensi' => $formData['kompetensi'],
                'indikator_kode' => $formData['indikator_kode'],
                'indikator_label' => $formData['indikator_label'],
                'is_scoreable' => true,
                'scoring_config' => $this->formScoringConfig($formData),
                'urutan' => $formIndex + 1,
                'is_active' => true,
            ]);

            foreach ($formData['items'] as $item) {
                $form->fields()->create([
                    'label' => $item['statement'],
                    'deskripsi' => null,
                    'nama_field' => sprintf(
                        'likert_%s_%03d',
                        $this->compactCode($formData['subindikator_kode']),
                        $item['source_number']
                    ),
                    'tipe_field' => LikertScale::FIELD_TYPE,
                    'placeholder' => null,
                    'bantuan' => 'Skala Likert: 1 = Sangat Tidak Setuju/Mampu/Menguasai; 2 = Tidak Setuju/Mampu/Menguasai; 3 = Cukup Setuju/Mampu/Menguasai; 4 = Setuju/Mampu/Menguasai; 5 = Sangat Setuju/Mampu/Menguasai.',
                    'opsi_field' => $this->likertOptions(),
                    'nilai_default' => null,
                    'validasi' => [
                        'required' => true,
                        'in' => ['1', '2', '3', '4', '5'],
                    ],
                    'scoring_config' => $this->fieldScoringConfig($formData, $item),
                    'lebar_kolom' => 'col-md-12',
                    'urutan' => $item['position'],
                    'is_required' => true,
                    'is_active' => true,
                ]);
            }
        }
    }

    /**
     * Format tebal pada sumber menandai butir negatif. Pada teks tempel,
     * posisinya konsisten sebagai butir ke-3 dan ke-6 di setiap subindikator.
     *
     * @return array<int, array<string, mixed>>
     */
    private function forms(): array
    {
        $forms = [];
        $currentCompetency = null;
        $currentIndicator = null;

        foreach (preg_split('/\R/', trim(self::INSTRUMENT_DATA)) as $line) {
            $parts = explode('|', trim($line), 3);

            if (count($parts) !== 3) {
                throw new RuntimeException("Baris data instrumen tidak valid: {$line}");
            }

            [$type, $code, $value] = $parts;

            if ($type === 'C') {
                $competency = KompetensiGuru::tryFrom($code);

                if (! $competency) {
                    throw new RuntimeException("Kode kompetensi tidak valid: {$code}");
                }

                $currentCompetency = [
                    'kode' => $competency->value,
                    'label' => $competency->label(),
                ];

                continue;
            }

            if ($type === 'I') {
                $this->ensureCurrentCompetency($currentCompetency, $line);

                $currentIndicator = [
                    'kode' => $code,
                    'label' => $value,
                ];

                continue;
            }

            if ($type === 'F') {
                $this->ensureCurrentCompetency($currentCompetency, $line);
                $this->ensureCurrentIndicator($currentIndicator, $line);

                $forms[] = [
                    'kompetensi' => $currentCompetency['kode'],
                    'kompetensi_label' => $currentCompetency['label'],
                    'indikator_kode' => $currentIndicator['kode'],
                    'indikator_label' => $currentIndicator['label'],
                    'subindikator_kode' => $code,
                    'subindikator_label' => $value,
                    'items' => [],
                ];

                continue;
            }

            if ($type === 'Q') {
                if ($forms === []) {
                    throw new RuntimeException("Pertanyaan muncul sebelum form: {$line}");
                }

                $lastFormIndex = array_key_last($forms);
                $position = count($forms[$lastFormIndex]['items']) + 1;

                $forms[$lastFormIndex]['items'][] = [
                    'source_number' => (int) $code,
                    'position' => $position,
                    'statement' => $value,
                    'is_negative_statement' => in_array($position, self::NEGATIVE_ITEM_POSITIONS, true),
                ];

                continue;
            }

            throw new RuntimeException("Tipe baris data instrumen tidak dikenal: {$type}");
        }

        $this->validateParsedForms($forms);

        return $forms;
    }

    /**
     * @param  array<int, array<string, mixed>>  $forms
     */
    private function assessmentScoringConfig(array $forms): array
    {
        $totalItems = $this->itemCount($forms);
        $negativeItems = $this->negativeItemCount($forms);

        return [
            'profile' => AssessmentInstrumentType::SKALA_LIKERT->value,
            'weight' => AssessmentInstrumentType::SKALA_LIKERT->weight(),
            'scale_min' => LikertScale::SCALE_MIN,
            'scale_max' => LikertScale::SCALE_MAX,
            'total_items' => $totalItems,
            'negative_statement_count' => $negativeItems,
            'minimum_score' => $totalItems * LikertScale::SCALE_MIN,
            'maximum_score' => $totalItems * LikertScale::SCALE_MAX,
            'verification_gap_threshold' => 1.5,
            'empty_response_threshold_percent' => 10,
            'advanced_rules' => [
                'source' => 'Instrumen Pemetaan Kompetensi Guru BBGTK Sulawesi Selatan',
                'method' => LikertScale::SCORING_METHOD,
                'positive_formula' => 'X',
                'negative_formula' => '6 - X',
                'aggregation' => 'Skor form, indikator, dan kompetensi dihitung dari rata-rata skor Likert terkoreksi.',
                'competencies' => $this->competencySummary($forms),
                'interpretation' => [
                    ['minimum_mean' => 1.00, 'maximum_mean' => 1.80, 'category' => 'Sangat rendah'],
                    ['minimum_mean' => 1.81, 'maximum_mean' => 2.60, 'category' => 'Rendah'],
                    ['minimum_mean' => 2.61, 'maximum_mean' => 3.40, 'category' => 'Sedang'],
                    ['minimum_mean' => 3.41, 'maximum_mean' => 4.20, 'category' => 'Tinggi'],
                    ['minimum_mean' => 4.21, 'maximum_mean' => 5.00, 'category' => 'Sangat tinggi'],
                ],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $formData
     */
    private function formScoringConfig(array $formData): array
    {
        $itemCount = count($formData['items']);
        $negativeItemCount = collect($formData['items'])
            ->where('is_negative_statement', true)
            ->count();

        return [
            'profile' => AssessmentInstrumentType::SKALA_LIKERT->value,
            'weight' => $itemCount,
            'advanced_rules' => [
                'competency' => $formData['kompetensi'],
                'competency_label' => $formData['kompetensi_label'],
                'indicator_code' => $formData['indikator_kode'],
                'indicator' => $formData['indikator_label'],
                'sub_indicator_code' => $formData['subindikator_kode'],
                'sub_indicator' => $formData['subindikator_label'],
                'item_count' => $itemCount,
                'negative_statement_count' => $negativeItemCount,
                'minimum_score' => $itemCount * LikertScale::SCALE_MIN,
                'maximum_score' => $itemCount * LikertScale::SCALE_MAX,
                'form_formula' => 'Rata-rata skor Likert terkoreksi seluruh item pada subindikator.',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $formData
     * @param  array<string, mixed>  $item
     */
    private function fieldScoringConfig(array $formData, array $item): array
    {
        $isNegative = (bool) $item['is_negative_statement'];

        return [
            'enabled' => true,
            'profile' => AssessmentInstrumentType::SKALA_LIKERT->value,
            'method' => LikertScale::SCORING_METHOD,
            'weight' => 1,
            'scale_min' => LikertScale::SCALE_MIN,
            'scale_max' => LikertScale::SCALE_MAX,
            'is_negative_statement' => $isNegative,
            'advanced_rules' => [
                'source_item_number' => $item['source_number'],
                'item_position' => $item['position'],
                'competency' => $formData['kompetensi'],
                'competency_label' => $formData['kompetensi_label'],
                'indicator_code' => $formData['indikator_kode'],
                'indicator' => $formData['indikator_label'],
                'sub_indicator_code' => $formData['subindikator_kode'],
                'sub_indicator' => $formData['subindikator_label'],
                'scoring_note' => $isNegative
                    ? 'Butir negatif: skor dibalik dengan rumus 6 - X.'
                    : 'Butir positif: skor sama dengan jawaban.',
            ],
        ];
    }

    private function likertOptions(): array
    {
        return [
            ['label' => '5', 'value' => '5', 'score' => 5],
            ['label' => '4', 'value' => '4', 'score' => 4],
            ['label' => '3', 'value' => '3', 'score' => 3],
            ['label' => '2', 'value' => '2', 'score' => 2],
            ['label' => '1', 'value' => '1', 'score' => 1],
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $forms
     * @return array<string, array<string, mixed>>
     */
    private function competencySummary(array $forms): array
    {
        $summary = [];

        foreach ($forms as $form) {
            $key = $form['kompetensi'];

            $summary[$key] ??= [
                'label' => $form['kompetensi_label'],
                'form_count' => 0,
                'item_count' => 0,
                'negative_statement_count' => 0,
                'indicators' => [],
            ];

            $summary[$key]['form_count']++;
            $summary[$key]['item_count'] += count($form['items']);
            $summary[$key]['negative_statement_count'] += collect($form['items'])
                ->where('is_negative_statement', true)
                ->count();
            $summary[$key]['indicators'][$form['indikator_kode']] = $form['indikator_label'];
        }

        return $summary;
    }

    /**
     * @param  array<int, array<string, mixed>>  $forms
     */
    private function validateParsedForms(array $forms): void
    {
        $formCount = count($forms);
        $itemCount = $this->itemCount($forms);
        $negativeItemCount = $this->negativeItemCount($forms);

        if ($formCount !== self::EXPECTED_FORM_COUNT) {
            throw new RuntimeException("Jumlah form Likert tidak sesuai. Didapat {$formCount}, seharusnya ".self::EXPECTED_FORM_COUNT.'.');
        }

        if ($itemCount !== self::EXPECTED_ITEM_COUNT) {
            throw new RuntimeException("Jumlah butir Likert tidak sesuai. Didapat {$itemCount}, seharusnya ".self::EXPECTED_ITEM_COUNT.'.');
        }

        if ($negativeItemCount !== self::EXPECTED_NEGATIVE_ITEM_COUNT) {
            throw new RuntimeException("Jumlah butir negatif tidak sesuai. Didapat {$negativeItemCount}, seharusnya ".self::EXPECTED_NEGATIVE_ITEM_COUNT.'.');
        }

        foreach ($forms as $form) {
            if (count($form['items']) !== 7) {
                throw new RuntimeException(
                    "Subindikator {$form['subindikator_kode']} harus berisi 7 butir Likert."
                );
            }
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $forms
     */
    private function itemCount(array $forms): int
    {
        return array_sum(array_map(fn (array $form) => count($form['items']), $forms));
    }

    /**
     * @param  array<int, array<string, mixed>>  $forms
     */
    private function negativeItemCount(array $forms): int
    {
        return array_sum(array_map(
            fn (array $form) => collect($form['items'])->where('is_negative_statement', true)->count(),
            $forms
        ));
    }

    private function compactCode(string $code): string
    {
        return str_replace('.', '', $code);
    }

    /**
     * @param  array<string, string>|null  $currentCompetency
     */
    private function ensureCurrentCompetency(?array $currentCompetency, string $line): void
    {
        if ($currentCompetency === null) {
            throw new RuntimeException("Baris muncul sebelum kompetensi: {$line}");
        }
    }

    /**
     * @param  array<string, string>|null  $currentIndicator
     */
    private function ensureCurrentIndicator(?array $currentIndicator, string $line): void
    {
        if ($currentIndicator === null) {
            throw new RuntimeException("Baris muncul sebelum indikator: {$line}");
        }
    }
}
