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

class AssessmentEvaluasiLikertKepalaSekolahSeeder extends Seeder
{
    private const ASSESSMENT_CODE = 'ASM-KS-LIKERT-001';

    /**
     * Format tebal pada sumber menandai butir negatif. Karena hasil tempel
     * tidak mempertahankan format tebal dan posisinya tidak seragam,
     * pemetaan butir negatif ditentukan eksplisit per subindikator.
     */
    private const NEGATIVE_ITEM_POSITIONS_BY_FORM = [
        '1.1.1' => [3, 6],
        '1.1.2' => [2, 6],
        '1.1.3' => [3, 6],
        '1.2.1' => [3, 6],
        '1.2.2' => [2, 6],
        '1.2.3' => [3, 6],
        '1.3.1' => [2, 6],
        '1.3.2' => [3, 6],
        '1.3.3' => [2, 6],
        '2.1.1' => [3, 6],
        '2.1.2' => [2, 6],
        '2.2.1' => [3, 6],
        '2.2.2' => [2, 6],
        '2.2.3' => [3, 6],
        '2.3.1' => [2, 6],
        '2.3.2' => [3, 6],
        '3.1.1' => [2, 6],
        '3.1.2' => [3, 6],
        '3.1.3' => [2, 6],
        '3.2.1' => [3, 6],
        '3.2.2' => [2, 6],
        '3.3.1' => [3, 6],
        '3.3.2' => [2, 6],
        '3.3.3' => [3, 6],
    ];

    private const EXPECTED_FORM_COUNT = 24;

    private const EXPECTED_ITEM_COUNT = 168;

    private const EXPECTED_NEGATIVE_ITEM_COUNT = 48;

    private const INSTRUMENT_DATA = <<<'DATA'
C|kepribadian|Kompetensi Kepribadian
I|1.1|Kematangan moral, emosi, dan spiritual dalam berperilaku sesuai dengan kode etik
F|1.1.1|Makna, tujuan, dan pandangan hidup kepemimpinan satuan pendidikan berdasarkan prinsip moral dan keyakinan terhadap Tuhan Yang Maha Esa dalam memimpin satuan pendidikan
Q|1|Saya menjadikan nilai-nilai moral, etika, dan keyakinan kepada Tuhan Yang Maha Esa sebagai landasan utama dalam mengambil keputusan sebagai kepala satuan pendidikan.
Q|2|Saya memandang kepemimpinan satuan pendidikan sebagai amanah yang harus dijalankan dengan integritas, tanggung jawab, keteladanan, dan semangat melayani.
Q|3|Saya lebih mengutamakan pencapaian target administrasi meskipun terkadang harus mengabaikan pertimbangan nilai moral dalam pengambilan keputusan.
Q|4|Saya menunjukkan keselarasan antara ucapan, sikap, dan tindakan sehingga menjadi teladan bagi guru, tenaga kependidikan, peserta didik, dan warga sekolah.
Q|5|Saya melakukan refleksi terhadap keputusan dan tindakan kepemimpinan untuk memastikan kesesuaiannya dengan nilai moral, etika profesi, dan tujuan pendidikan.
Q|6|Saya jarang mempertimbangkan nilai-nilai moral dan keyakinan spiritual ketika menentukan kebijakan atau menyelesaikan persoalan di satuan pendidikan.
Q|7|Saya berupaya membangun budaya sekolah yang menjunjung tinggi integritas, kejujuran, tanggung jawab, dan nilai-nilai kemanusiaan dalam setiap aktivitas pendidikan.
F|1.1.2|Pengelolaan Emosi dalam Menjalankan Peran sebagai Kepala Sekolah
Q|8|Saya mampu mengendalikan emosi secara profesional ketika menghadapi permasalahan, konflik, maupun tekanan dalam memimpin satuan pendidikan.
Q|9|Saya mudah menunjukkan kemarahan atau kekecewaan kepada guru, tenaga kependidikan, maupun pihak lain ketika menghadapi situasi yang tidak sesuai dengan harapan saya.
Q|10|Saya mengambil keputusan secara tenang, objektif, dan berdasarkan pertimbangan yang matang meskipun berada dalam situasi yang penuh tekanan.
Q|11|Saya menerima kritik, masukan, dan perbedaan pendapat secara terbuka sebagai bagian dari upaya meningkatkan kualitas kepemimpinan.
Q|12|Saya mampu menjaga sikap profesional dan menjadi teladan dalam mengelola emosi ketika berinteraksi dengan seluruh warga sekolah dan pemangku kepentingan.
Q|13|Saya sering membiarkan suasana hati memengaruhi cara saya berkomunikasi dan mengambil keputusan sebagai kepala sekolah.
Q|14|Saya secara berkala melakukan refleksi dan pengelolaan diri agar mampu mempertahankan kestabilan emosi dalam menjalankan kepemimpinan satuan pendidikan.
F|1.1.3|Penerapan Kode Etik dalam Menjalankan Tugas dan Peran sebagai Kepala Sekolah
Q|1|Saya menerapkan kode etik kepala sekolah secara konsisten dalam mengambil keputusan, memimpin warga sekolah, dan melaksanakan tugas profesional.
Q|2|Saya menjaga hubungan profesional dengan guru, tenaga kependidikan, peserta didik, orang tua, dan pemangku kepentingan sesuai dengan prinsip etika dan peraturan yang berlaku.
Q|3|Saya menganggap pelanggaran terhadap kode etik masih dapat ditoleransi apabila bertujuan mempercepat penyelesaian pekerjaan atau mencapai target tertentu.
Q|4|Saya menjaga kerahasiaan informasi dan data satuan pendidikan serta menggunakannya secara bertanggung jawab sesuai dengan ketentuan yang berlaku.
Q|5|Saya menjadi teladan dalam menjunjung tinggi integritas, kejujuran, akuntabilitas, dan profesionalisme dalam setiap pelaksanaan tugas sebagai kepala sekolah.
Q|6|Saya jarang meninjau kembali apakah kebijakan dan tindakan yang saya ambil telah sesuai dengan kode etik dan prinsip profesionalisme kepala sekolah.
Q|7|Saya secara berkala melakukan refleksi terhadap pelaksanaan tugas kepemimpinan untuk memastikan seluruh keputusan dan tindakan tetap sesuai dengan kode etik profesi serta peraturan yang berlaku.
I|1.2|Pengembangan diri melalui kebiasaan refleksi
F|1.2.1|Refleksi dan Perencanaan Kebutuhan Pengembangan Diri untuk Peningkatan Kepemimpinan Satuan Pendidikan yang Berpusat pada Peserta Didik
Q|1|Saya melakukan refleksi secara berkala terhadap efektivitas kepemimpinan yang saya jalankan sebagai dasar untuk meningkatkan mutu layanan pembelajaran yang berpusat pada peserta didik.
Q|2|Saya menggunakan hasil evaluasi, data mutu pendidikan, serta masukan dari warga sekolah sebagai dasar dalam menyusun rencana pengembangan diri.
Q|3|Saya merasa pengalaman yang saya miliki sudah cukup sehingga tidak perlu lagi melakukan refleksi atau menyusun rencana pengembangan diri secara teratur.
Q|4|Saya menetapkan prioritas pengembangan kompetensi kepemimpinan berdasarkan kebutuhan nyata satuan pendidikan dan perkembangan belajar peserta didik.
Q|5|Saya menyusun rencana pengembangan diri yang memiliki tujuan, indikator keberhasilan, dan langkah tindak lanjut yang jelas untuk meningkatkan kualitas kepemimpinan sekolah.
Q|6|Saya jarang mengevaluasi apakah kegiatan pengembangan diri yang saya ikuti benar-benar sesuai dengan kebutuhan peningkatan mutu satuan pendidikan.
Q|7|Saya secara berkala meninjau kembali hasil pengembangan diri dan menyesuaikan rencana pengembangan berikutnya agar semakin mendukung kepemimpinan yang berpusat pada peserta didik.
F|1.2.2|Cara Adaptif Melakukan Pengembangan Diri untuk Meningkatkan Kepemimpinan Satuan Pendidikan yang Berpusat pada Peserta Didik
Q|1|Saya secara aktif memilih berbagai bentuk pengembangan diri yang sesuai dengan kebutuhan peningkatan mutu kepemimpinan dan pembelajaran di satuan pendidikan.
Q|2|Saya lebih memilih menggunakan cara kepemimpinan yang sudah biasa saya lakukan daripada mempelajari pendekatan baru yang lebih sesuai dengan kebutuhan sekolah.
Q|3|Saya memanfaatkan komunitas belajar, jejaring profesional, pelatihan, mentoring, dan teknologi digital untuk meningkatkan kompetensi kepemimpinan.
Q|4|Saya menyesuaikan strategi pengembangan diri dengan perubahan kebijakan pendidikan, perkembangan ilmu pengetahuan, serta kebutuhan warga sekolah dan peserta didik.
Q|5|Saya terbuka terhadap gagasan, inovasi, dan praktik baik dari berbagai sumber sebagai bahan untuk meningkatkan efektivitas kepemimpinan satuan pendidikan.
Q|6|Saya jarang menyesuaikan cara mengembangkan kompetensi diri meskipun menghadapi tantangan baru dalam memimpin satuan pendidikan.
Q|7|Saya secara berkala mengevaluasi efektivitas berbagai kegiatan pengembangan diri yang saya ikuti dan mengadaptasinya agar semakin berdampak pada peningkatan mutu pembelajaran yang berpusat pada peserta didik.
F|1.2.3|Penerapan Hasil Pengembangan Diri yang Berkelanjutan untuk Perbaikan Kualitas Kepemimpinan Satuan Pendidikan
Q|1|Saya menerapkan pengetahuan, keterampilan, dan wawasan yang diperoleh dari kegiatan pengembangan diri untuk meningkatkan efektivitas kepemimpinan di satuan pendidikan.
Q|2|Saya mengadaptasi hasil pelatihan, pendampingan, atau komunitas belajar menjadi kebijakan, program, atau strategi yang mendukung peningkatan mutu pembelajaran.
Q|3|Saya jarang menerapkan hasil pengembangan diri karena cara kepemimpinan yang selama ini saya gunakan sudah dianggap memadai.
Q|4|Saya mengevaluasi dampak penerapan hasil pengembangan diri terhadap peningkatan kinerja guru, budaya sekolah, dan kualitas pembelajaran.
Q|5|Saya mendorong guru dan tenaga kependidikan untuk bersama-sama menerapkan praktik baik yang diperoleh dari kegiatan pengembangan profesional demi peningkatan mutu satuan pendidikan.
Q|6|Saya tidak merasa perlu meninjau kembali efektivitas hasil pengembangan diri setelah diterapkan dalam kepemimpinan sekolah.
Q|7|Saya secara berkelanjutan menyempurnakan praktik kepemimpinan berdasarkan hasil evaluasi penerapan pengembangan diri agar memberikan dampak yang lebih besar terhadap mutu layanan pendidikan dan hasil belajar peserta didik.
I|1.3|Orientasi berpusat pada peserta didik
F|1.3.1|Empati terhadap Peserta Didik dalam Pengambilan Keputusan
Q|1|Saya mempertimbangkan kebutuhan, kepentingan, dan kesejahteraan peserta didik sebagai dasar utama dalam setiap pengambilan keputusan di satuan pendidikan.
Q|2|Saya lebih mengutamakan kemudahan pengelolaan sekolah daripada mempertimbangkan dampak keputusan terhadap kebutuhan peserta didik.
Q|3|Saya menggunakan data, aspirasi, dan masukan dari peserta didik maupun guru sebagai bahan pertimbangan dalam menetapkan kebijakan yang berkaitan dengan pembelajaran dan layanan pendidikan.
Q|4|Saya berupaya memahami kondisi, tantangan, dan keberagaman latar belakang peserta didik sebelum menetapkan kebijakan atau mengambil keputusan yang memengaruhi mereka.
Q|5|Saya mendorong terciptanya kebijakan sekolah yang berpihak pada perkembangan akademik, sosial, emosional, dan karakter peserta didik.
Q|6|Saya jarang mengevaluasi apakah keputusan yang saya ambil telah memberikan manfaat nyata bagi perkembangan dan kesejahteraan peserta didik.
Q|7|Saya secara berkala merefleksikan dampak keputusan yang saya ambil untuk memastikan seluruh kebijakan sekolah tetap berorientasi pada kepentingan terbaik peserta didik.
F|1.3.2|Respek terhadap Hak Peserta Didik dalam Menjalankan Peran sebagai Kepala Sekolah
Q|8|Saya memastikan setiap kebijakan dan program sekolah menghormati hak peserta didik untuk memperoleh layanan pendidikan yang adil, aman, dan berkualitas.
Q|9|Saya mendorong terciptanya budaya sekolah yang menghargai keberagaman, kesetaraan, dan martabat setiap peserta didik tanpa diskriminasi.
Q|10|Saya menganggap perbedaan latar belakang atau kemampuan peserta didik dapat menjadi dasar pemberian perlakuan yang berbeda dalam memperoleh layanan sekolah.
Q|11|Saya memastikan peserta didik memperoleh kesempatan untuk menyampaikan pendapat, aspirasi, dan kebutuhannya melalui mekanisme yang sesuai.
Q|12|Saya mengawasi pelaksanaan kebijakan sekolah agar seluruh warga sekolah menghormati hak-hak peserta didik sesuai dengan peraturan dan etika pendidikan.
Q|13|Saya jarang mengevaluasi apakah kebijakan sekolah yang saya tetapkan telah benar-benar melindungi dan menghormati hak peserta didik.
Q|14|Saya secara berkala meninjau dan menyempurnakan kebijakan sekolah agar semakin berpihak pada pemenuhan hak, perlindungan, dan perkembangan optimal seluruh peserta didik.
F|1.3.3|Kepedulian terhadap Keselamatan dan Keamanan Peserta Didik sebagai Individu dan Kelompok dalam Menjalankan Peran sebagai Kepala Sekolah
Q|15|Saya memastikan tersedianya kebijakan dan program sekolah yang mendukung terciptanya lingkungan belajar yang aman, nyaman, dan melindungi seluruh peserta didik.
Q|16|Saya menganggap persoalan keselamatan dan keamanan peserta didik dapat ditangani setelah terjadi masalah karena bukan menjadi prioritas utama dalam pengelolaan sekolah.
Q|17|Saya mengidentifikasi berbagai potensi risiko yang dapat mengancam keselamatan fisik, psikologis, dan sosial peserta didik sebagai dasar penyusunan langkah pencegahan.
Q|18|Saya membangun budaya sekolah yang mencegah kekerasan, perundungan, diskriminasi, dan berbagai bentuk ancaman terhadap keamanan peserta didik.
Q|19|Saya melibatkan guru, tenaga kependidikan, orang tua/wali, dan pihak terkait dalam menjaga keselamatan serta kesejahteraan peserta didik di satuan pendidikan.
Q|20|Saya jarang melakukan evaluasi terhadap kebijakan dan praktik sekolah dalam memastikan perlindungan keselamatan dan keamanan peserta didik.
Q|21|Saya melakukan pemantauan dan tindak lanjut secara berkelanjutan untuk memastikan lingkungan sekolah tetap aman, inklusif, dan mendukung perkembangan peserta didik secara optimal.
C|sosial|Kompetensi Sosial
I|2.1|Pemberdayaan warga satuan pendidikan untuk meningkatkan kualitas pembelajaran
F|2.1.1|Pemberdayaan Guru dan Tenaga Kependidikan untuk Peningkatan Kualitas Pembelajaran di Satuan Pendidikan
Q|1|Saya mengidentifikasi potensi, kebutuhan, dan kompetensi guru serta tenaga kependidikan sebagai dasar dalam menyusun program pemberdayaan yang tepat.
Q|2|Saya memberikan kesempatan kepada guru dan tenaga kependidikan untuk mengembangkan kompetensi melalui pelatihan, komunitas belajar, maupun kegiatan pengembangan profesional lainnya.
Q|3|Saya lebih banyak menentukan program peningkatan kompetensi guru dan tenaga kependidikan tanpa melibatkan mereka dalam proses perencanaan maupun pengambilan keputusan.
Q|4|Saya membangun budaya kolaborasi yang mendorong guru dan tenaga kependidikan saling berbagi praktik baik untuk meningkatkan kualitas layanan pendidikan.
Q|5|Saya memberikan dukungan, apresiasi, dan penguatan terhadap upaya guru dan tenaga kependidikan dalam meningkatkan kualitas pembelajaran di satuan pendidikan.
Q|6|Saya menganggap pengembangan kompetensi guru dan tenaga kependidikan merupakan tanggung jawab pribadi masing-masing sehingga tidak perlu menjadi fokus kepemimpinan sekolah.
Q|7|Saya melakukan evaluasi terhadap efektivitas program pemberdayaan guru dan tenaga kependidikan sebagai dasar perbaikan berkelanjutan untuk peningkatan mutu pembelajaran.
F|2.1.2|Pemberdayaan Orang Tua/Wali untuk Peningkatan Kualitas Pembelajaran di Satuan Pendidikan
Q|8|Saya membangun komunikasi yang efektif dengan orang tua/wali untuk memahami kebutuhan, perkembangan, dan tantangan belajar peserta didik.
Q|9|Saya menganggap keterlibatan orang tua/wali dalam pembelajaran bukan bagian penting dari tanggung jawab kepemimpinan sekolah.
Q|10|Saya melibatkan orang tua/wali dalam mendukung program sekolah yang bertujuan meningkatkan kualitas pembelajaran dan perkembangan peserta didik.
Q|11|Saya membangun kemitraan dengan orang tua/wali melalui komunikasi, forum diskusi, dan kegiatan kolaboratif untuk mendukung keberhasilan belajar peserta didik.
Q|12|Saya menghargai kontribusi, pengalaman, dan masukan orang tua/wali sebagai sumber informasi dalam merancang kebijakan pembelajaran yang berpihak pada peserta didik.
Q|13|Saya hanya melibatkan orang tua/wali ketika terdapat masalah peserta didik dan tidak menjadikan mereka sebagai mitra dalam peningkatan kualitas pembelajaran.
Q|14|Saya mengevaluasi efektivitas keterlibatan orang tua/wali dalam mendukung pembelajaran sebagai dasar penguatan kemitraan sekolah dan keluarga.
I|2.2|Kolaborasi untuk peningkatan kualitas satuan pendidikan
F|2.2.1|Komunikasi Efektif dengan Warga Satuan Pendidikan yang Mengarah pada Peningkatan Kualitas Satuan Pendidikan
Q|1|Saya membangun komunikasi yang terbuka, jelas, dan profesional dengan guru, tenaga kependidikan, peserta didik, serta warga satuan pendidikan untuk mendukung peningkatan mutu sekolah.
Q|2|Saya menyampaikan visi, kebijakan, program, dan harapan sekolah secara efektif agar dipahami dan didukung oleh seluruh warga satuan pendidikan.
Q|3|Saya lebih sering menyampaikan keputusan sekolah secara satu arah tanpa memberikan kesempatan kepada warga satuan pendidikan untuk menyampaikan pendapat atau masukan.
Q|4|Saya menciptakan ruang dialog dan kolaborasi untuk membangun kepercayaan serta memperkuat hubungan kerja antarwarga satuan pendidikan.
Q|5|Saya menggunakan berbagai media dan strategi komunikasi yang sesuai untuk memastikan informasi penting sekolah tersampaikan secara efektif dan tepat sasaran.
Q|6|Saya jarang mengevaluasi kualitas komunikasi yang dilakukan dengan warga satuan pendidikan sehingga berbagai kendala komunikasi sering berulang.
Q|7|Saya melakukan refleksi terhadap efektivitas komunikasi dengan warga satuan pendidikan sebagai dasar perbaikan kepemimpinan dan peningkatan kualitas sekolah.
F|2.2.2|Pengorganisasian Tugas-Tugas Bersama Warga Satuan Pendidikan untuk Peningkatan Kualitas Satuan Pendidikan
Q|1|Saya menyusun pembagian tugas dan tanggung jawab bersama warga satuan pendidikan berdasarkan kebutuhan program sekolah dan kompetensi masing-masing individu.
Q|2|Saya cenderung memberikan tugas kepada warga satuan pendidikan tanpa mempertimbangkan kemampuan, minat, dan kesiapan mereka dalam melaksanakan tanggung jawab tersebut.
Q|3|Saya melibatkan guru, tenaga kependidikan, dan warga sekolah lainnya dalam merencanakan serta melaksanakan program peningkatan kualitas satuan pendidikan.
Q|4|Saya membangun kerja sama yang efektif dengan membangun koordinasi, kejelasan peran, dan tanggung jawab bersama dalam mencapai tujuan sekolah.
Q|5|Saya memberikan dukungan dan fasilitasi kepada warga satuan pendidikan agar mampu menjalankan tugas secara optimal dan berkontribusi terhadap kemajuan sekolah.
Q|6|Saya menganggap pengaturan tugas dan tanggung jawab warga satuan pendidikan cukup dilakukan melalui instruksi pimpinan tanpa perlu koordinasi dan evaluasi bersama.
Q|7|Saya melakukan evaluasi bersama terhadap pelaksanaan tugas dan program sekolah untuk meningkatkan efektivitas kerja serta kualitas layanan pendidikan.
F|2.2.3|Inisiatif Berkontribusi untuk Mencapai Tujuan Bersama dalam Peningkatan Kualitas Satuan Pendidikan
Q|1|Saya secara aktif mengambil inisiatif dalam merancang dan mengembangkan program yang mendukung peningkatan kualitas satuan pendidikan.
Q|2|Saya mendorong warga satuan pendidikan untuk berpartisipasi dan berkontribusi dalam mencapai visi, misi, dan tujuan sekolah.
Q|3|Saya cenderung menunggu arahan dari pihak lain sebelum mengambil tindakan dalam menyelesaikan permasalahan atau mengembangkan program sekolah.
Q|4|Saya berperan sebagai penggerak perubahan dengan memberikan gagasan, solusi, dan dukungan terhadap berbagai upaya peningkatan mutu pendidikan.
Q|5|Saya membangun budaya kerja yang mendorong kepedulian, tanggung jawab bersama, dan komitmen seluruh warga sekolah terhadap kemajuan satuan pendidikan.
Q|6|Saya menganggap keberhasilan peningkatan kualitas sekolah merupakan tanggung jawab beberapa pihak tertentu saja sehingga tidak perlu melibatkan seluruh warga satuan pendidikan.
Q|7|Saya melakukan refleksi terhadap kontribusi dan kepemimpinan yang telah dilakukan untuk terus meningkatkan efektivitas pencapaian tujuan bersama sekolah.
I|2.3|Keterlibatan dalam organisasi profesi dan jejaring yang lebih luas untuk peningkatan kualitas satuan pendidikan
F|2.3.1|Berpartisipasi Aktif dalam Organisasi Profesi dan Jejaring yang Lebih Luas untuk Peningkatan Kualitas Kepemimpinan di Satuan Pendidikan
Q|1|Saya aktif mengikuti organisasi profesi, komunitas kepemimpinan, atau jejaring pendidikan sebagai sarana meningkatkan kompetensi kepemimpinan sekolah.
Q|2|Saya menganggap keterlibatan dalam organisasi profesi dan jejaring pendidikan tidak terlalu diperlukan karena peningkatan kualitas kepemimpinan dapat dilakukan secara mandiri.
Q|3|Saya berkontribusi dalam berbagai kegiatan organisasi profesi atau jejaring yang mendukung peningkatan mutu pengelolaan satuan pendidikan.
Q|4|Saya memanfaatkan hasil diskusi, kajian, dan pengalaman dari organisasi profesi atau jejaring untuk memperkaya praktik kepemimpinan di sekolah.
Q|5|Saya membangun hubungan kolaboratif dengan berbagai pihak di luar satuan pendidikan untuk memperoleh wawasan dan solusi dalam menghadapi tantangan kepemimpinan sekolah.
Q|6|Saya jarang terlibat dalam organisasi profesi atau jejaring pendidikan karena kegiatan tersebut tidak memberikan pengaruh langsung terhadap pengelolaan sekolah.
Q|7|Saya melakukan refleksi terhadap pengalaman dan kontribusi dalam organisasi profesi atau jejaring sebagai dasar pengembangan kualitas kepemimpinan secara berkelanjutan.
F|2.3.2|Berbagi Praktik Baik dan Karya tentang Kepemimpinan Satuan Pendidikan untuk Peningkatan Kualitas Satuan Pendidikan yang Berpusat pada Peserta Didik
Q|1|Saya mendokumentasikan praktik baik, inovasi, dan pengalaman kepemimpinan sekolah yang berdampak terhadap peningkatan kualitas pembelajaran.
Q|2|Saya membagikan pengalaman dan gagasan kepemimpinan kepada rekan kepala sekolah, organisasi profesi, atau jejaring pendidikan sebagai sumber pembelajaran bersama.
Q|3|Saya merasa praktik kepemimpinan yang saya lakukan cukup untuk diketahui di lingkungan sekolah sendiri sehingga tidak perlu dibagikan kepada pihak lain.
Q|4|Saya memanfaatkan forum profesional, komunitas belajar, atau media berbagi untuk menyampaikan karya dan praktik baik dalam mengembangkan satuan pendidikan.
Q|5|Saya menerima masukan dari berbagai pihak terhadap praktik kepemimpinan yang saya bagikan sebagai bahan penyempurnaan pengelolaan sekolah.
Q|6|Saya jarang mendokumentasikan atau membagikan hasil inovasi kepemimpinan karena menganggap praktik tersebut hanya relevan untuk kebutuhan sekolah sendiri.
Q|7|Saya secara berkelanjutan mengembangkan budaya berbagi praktik baik kepemimpinan yang berorientasi pada peningkatan kualitas satuan pendidikan dan kepentingan terbaik peserta didik.
C|profesional|Kompetensi Profesional
I|3.1|Pengembangan visi dan budaya belajar satuan pendidikan
F|3.1.1|Kepemimpinan Satuan Pendidikan dalam Mewujudkan Visi yang Berpusat pada Peserta Didik dengan Melibatkan Warga Satuan Pendidikan
Q|1|Saya mengembangkan visi satuan pendidikan yang menempatkan kebutuhan, perkembangan, dan keberhasilan peserta didik sebagai prioritas utama.
Q|2|Saya menyusun dan menjalankan visi sekolah berdasarkan pertimbangan pimpinan saja tanpa melibatkan warga satuan pendidikan.
Q|3|Saya melibatkan guru, tenaga kependidikan, peserta didik, orang tua/wali, dan pemangku kepentingan dalam memahami serta mewujudkan visi satuan pendidikan.
Q|4|Saya menerjemahkan visi sekolah ke dalam program, kebijakan, dan budaya satuan pendidikan yang mendukung pembelajaran berpusat pada peserta didik.
Q|5|Saya membangun komitmen bersama warga satuan pendidikan agar setiap kegiatan sekolah selaras dengan visi yang telah ditetapkan.
Q|6|Saya menganggap keterlibatan warga satuan pendidikan dalam mewujudkan visi sekolah tidak terlalu diperlukan karena tanggung jawab utama berada pada kepala sekolah.
Q|7|Saya melakukan evaluasi dan refleksi secara berkala terhadap ketercapaian visi sekolah sebagai dasar penyempurnaan kepemimpinan dan program satuan pendidikan.
F|3.1.2|Pengembangan Kebiasaan Belajar sebagai Cerminan Visi Satuan Pendidikan yang Berpusat pada Peserta Didik
Q|1|Saya mengembangkan budaya belajar di satuan pendidikan yang mendorong peserta didik aktif, mandiri, dan bertanggung jawab terhadap proses belajarnya.
Q|2|Saya mendorong guru dan tenaga kependidikan untuk menciptakan lingkungan belajar yang mendukung rasa ingin tahu, kreativitas, dan perkembangan potensi peserta didik.
Q|3|Saya lebih berfokus pada pencapaian target administratif sekolah dibandingkan membangun kebiasaan belajar yang sesuai dengan kebutuhan peserta didik.
Q|4|Saya mengembangkan kebijakan dan program sekolah yang membangun kebiasaan refleksi, kolaborasi, literasi, dan pembelajaran sepanjang hayat.
Q|5|Saya memberikan dukungan kepada warga sekolah untuk menciptakan praktik pembelajaran yang sesuai dengan visi sekolah yang berpusat pada peserta didik.
Q|6|Saya menganggap pembentukan kebiasaan belajar peserta didik sepenuhnya menjadi tanggung jawab guru di kelas sehingga tidak perlu menjadi perhatian utama kepala sekolah.
Q|7|Saya melakukan pemantauan dan evaluasi terhadap budaya belajar yang berkembang di sekolah sebagai dasar peningkatan kualitas pembelajaran.
F|3.1.3|Pengelolaan Komunitas Belajar dalam Satuan Pendidikan yang Berbasis Data dengan Berorientasi pada Peningkatan Capaian Belajar Peserta Didik
Q|1|Saya mengembangkan komunitas belajar di satuan pendidikan sebagai wadah kolaborasi untuk meningkatkan kualitas pembelajaran dan capaian belajar peserta didik.
Q|2|Saya menjalankan komunitas belajar hanya sebagai kegiatan rutin sekolah tanpa mengaitkannya dengan kebutuhan peningkatan pembelajaran dan hasil belajar peserta didik.
Q|3|Saya mendorong guru dan tenaga kependidikan memanfaatkan data hasil belajar, asesmen, dan informasi lain sebagai dasar dalam komunitas belajar.
Q|4|Saya memfasilitasi kegiatan komunitas belajar yang berfokus pada refleksi praktik pembelajaran, berbagi strategi, dan penyelesaian masalah pembelajaran.
Q|5|Saya memastikan komunitas belajar berjalan secara kolaboratif dengan melibatkan warga sekolah untuk meningkatkan kualitas layanan pendidikan.
Q|6|Saya tidak terlalu memperhatikan hasil atau data capaian belajar peserta didik dalam menentukan arah kegiatan komunitas belajar di sekolah.
Q|7|Saya melakukan pemantauan dan evaluasi terhadap efektivitas komunitas belajar sebagai dasar perbaikan berkelanjutan dalam peningkatan mutu pembelajaran.
I|3.2|Kepemimpinan pembelajaran yang berpusat pada peserta didik
F|3.2.1|Kepemimpinan Pembelajaran dalam Membudayakan Lingkungan yang Aman, Nyaman, dan Inklusif untuk Warga Satuan Pendidikan
Q|1|Saya membangun budaya satuan pendidikan yang menciptakan rasa aman, nyaman, dan dihargai bagi seluruh warga sekolah dalam mendukung proses pembelajaran.
Q|2|Saya mengembangkan kebijakan dan program sekolah yang mendorong terciptanya lingkungan belajar yang ramah, inklusif, dan berpihak pada peserta didik.
Q|3|Saya menganggap penciptaan lingkungan sekolah yang aman dan nyaman lebih banyak menjadi tanggung jawab guru di kelas daripada menjadi bagian dari kepemimpinan kepala sekolah.
Q|4|Saya memastikan setiap warga satuan pendidikan memperoleh perlakuan yang adil serta kesempatan yang sama untuk berkembang dan berpartisipasi.
Q|5|Saya mendorong pencegahan dan penanganan berbagai bentuk kekerasan, diskriminasi, maupun perilaku yang menghambat terciptanya iklim sekolah yang positif.
Q|6|Saya jarang melakukan evaluasi terhadap kondisi lingkungan sekolah karena menganggap suasana belajar yang aman dan nyaman sudah terbentuk dengan sendirinya.
Q|7|Saya melakukan refleksi dan tindak lanjut secara berkelanjutan untuk memperkuat budaya sekolah yang aman, nyaman, dan inklusif bagi seluruh warga satuan pendidikan.
F|3.2.2|Kepemimpinan Pembelajaran dalam Perencanaan, Pelaksanaan, Asesmen, dan Pelaporan Capaian Belajar Peserta Didik dengan Memperhatikan Karakteristik Guru
Q|1|Saya mengarahkan guru untuk menyusun perencanaan pembelajaran yang sesuai dengan kebutuhan peserta didik dan karakteristik pembelajaran di satuan pendidikan.
Q|2|Saya menyerahkan sepenuhnya penyusunan perencanaan, pelaksanaan, dan evaluasi pembelajaran kepada guru tanpa memberikan arahan atau pendampingan.
Q|3|Saya memberikan dukungan kepada guru dalam memilih strategi pembelajaran, metode, dan sumber belajar yang sesuai dengan karakteristik peserta didik.
Q|4|Saya mendorong guru memanfaatkan hasil asesmen sebagai dasar perbaikan pembelajaran dan peningkatan capaian belajar peserta didik.
Q|5|Saya memfasilitasi guru dalam menyusun laporan capaian belajar peserta didik secara objektif, informatif, dan sesuai perkembangan peserta didik.
Q|6|Saya lebih berfokus pada pencapaian administrasi pembelajaran dibandingkan mendampingi guru dalam meningkatkan kualitas proses belajar dan asesmen peserta didik.
Q|7|Saya melakukan supervisi, refleksi, dan tindak lanjut terhadap praktik pembelajaran guru untuk meningkatkan kualitas layanan pendidikan di satuan pendidikan.
I|3.3|Pengelolaan sumber daya satuan pendidikan secara efektif, transparan, dan akuntabel
F|3.3.1|Penelusuran Sumber Daya Satuan Pendidikan yang Berasal dari Berbagai Sumber untuk Perencanaan dan Pelaksanaan Program
Q|1|Saya mengidentifikasi berbagai sumber daya yang tersedia di satuan pendidikan sebagai dasar penyusunan program yang berorientasi pada peningkatan mutu pembelajaran.
Q|2|Saya memetakan potensi sumber daya manusia, sarana prasarana, lingkungan, dan jejaring kemitraan untuk mendukung pencapaian tujuan satuan pendidikan.
Q|3|Saya lebih sering menyusun program sekolah berdasarkan kebiasaan sebelumnya tanpa melakukan analisis terhadap ketersediaan dan kebutuhan sumber daya yang ada.
Q|4|Saya melibatkan warga satuan pendidikan dan pemangku kepentingan dalam menggali potensi sumber daya yang dapat mendukung pelaksanaan program sekolah.
Q|5|Saya menggunakan hasil pemetaan sumber daya sebagai pertimbangan dalam menentukan prioritas program dan pengambilan keputusan di satuan pendidikan.
Q|6|Saya menganggap pencarian dan pengembangan sumber daya tambahan bukan bagian penting dari tugas kepemimpinan kepala sekolah selama program sekolah tetap berjalan.
Q|7|Saya melakukan evaluasi terhadap pemanfaatan sumber daya satuan pendidikan untuk memastikan efektivitas dan keberlanjutan pelaksanaan program sekolah.
F|3.3.2|Pengelolaan Sumber Daya Satuan Pendidikan Secara Efektif untuk Peningkatan Pembelajaran Peserta Didik
Q|1|Saya mengelola sumber daya satuan pendidikan secara terencana untuk mendukung peningkatan kualitas pembelajaran peserta didik.
Q|2|Saya lebih banyak menggunakan sumber daya sekolah berdasarkan kebiasaan yang sudah berjalan tanpa mempertimbangkan dampaknya terhadap peningkatan pembelajaran peserta didik.
Q|3|Saya mengoptimalkan pemanfaatan sumber daya manusia, sarana prasarana, teknologi, dan lingkungan sekolah untuk mendukung proses pembelajaran.
Q|4|Saya memastikan penggunaan sumber daya sekolah dilakukan secara efektif, efisien, transparan, dan sesuai dengan prioritas kebutuhan pembelajaran.
Q|5|Saya melibatkan warga satuan pendidikan dalam pemanfaatan dan pengelolaan sumber daya untuk mencapai tujuan peningkatan mutu pembelajaran.
Q|6|Saya menganggap pengelolaan sumber daya sekolah hanya berkaitan dengan administrasi dan keuangan sehingga tidak perlu dikaitkan langsung dengan kualitas pembelajaran.
Q|7|Saya melakukan pemantauan dan evaluasi terhadap pemanfaatan sumber daya untuk memastikan kontribusinya terhadap perkembangan dan capaian belajar peserta didik.
F|3.3.3|Pengelolaan Sumber Daya Satuan Pendidikan Secara Transparan dan Akuntabel
Q|1|Saya mengelola sumber daya satuan pendidikan berdasarkan prinsip keterbukaan, tanggung jawab, dan kepentingan terbaik bagi peningkatan mutu pendidikan.
Q|2|Saya menyampaikan informasi mengenai perencanaan, pemanfaatan, dan evaluasi sumber daya sekolah kepada pihak terkait secara jelas dan dapat dipertanggungjawabkan.
Q|3|Saya menganggap informasi terkait pengelolaan sumber daya sekolah cukup diketahui oleh pihak tertentu saja sehingga tidak perlu disampaikan secara terbuka kepada warga satuan pendidikan.
Q|4|Saya menerapkan mekanisme pengelolaan sumber daya sekolah yang sesuai dengan aturan, prosedur, dan prinsip akuntabilitas.
Q|5|Saya melibatkan warga satuan pendidikan dalam pengawasan dan evaluasi penggunaan sumber daya untuk membangun kepercayaan bersama.
Q|6|Saya lebih memprioritaskan penyelesaian program sekolah daripada memastikan proses pengelolaan sumber daya dilakukan secara transparan dan terdokumentasi dengan baik.
Q|7|Saya melakukan refleksi dan perbaikan secara berkala terhadap tata kelola sumber daya sekolah agar semakin efektif, transparan, dan akuntabel.
DATA;

    public function run(): void
    {
        $forms = $this->forms();

        $assessment = Assessment::updateOrCreate(
            ['kode_assessment' => self::ASSESSMENT_CODE],
            [
                'judul' => 'Angket Kompetensi Kepala Sekolah - Skala Likert',
                'slug' => Str::slug('Angket Kompetensi Kepala Sekolah Skala Likert'),
                'deskripsi' => 'Instrumen pemetaan kompetensi kepala sekolah BBGTK Sulawesi Selatan berbasis skala Likert untuk kompetensi kepribadian, sosial, dan profesional.',
                'petunjuk' => 'Pilihlah jawaban yang sesuai dengan kondisi atau pemahaman Anda saat ini secara jujur. Skala Likert: 1 = Sangat Tidak Setuju/Mampu/Menguasai; 2 = Tidak Setuju/Mampu/Menguasai; 3 = Cukup Setuju/Mampu/Menguasai; 4 = Setuju/Mampu/Menguasai; 5 = Sangat Setuju/Mampu/Menguasai.',
                'instrument_type' => AssessmentInstrumentType::SKALA_LIKERT->value,
                'target_ketenagaan' => AssessmentKetenagaanType::TENAGA_KEPENDIDIKAN->value,
                'scoring_config' => $this->assessmentScoringConfig($forms),
                'status' => 'publish',
                'is_active' => true,
            ]
        );

        $assessment->forms()->delete();

        foreach ($forms as $formIndex => $formData) {
            $form = $assessment->forms()->create([
                'judul_form' => $formData['subindikator_kode'].' '.$formData['subindikator_label'],
                'kode_form' => 'FORM-LIKERT-KS-'.$this->compactCode($formData['subindikator_kode']),
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
                        'likert_ks_%s_%03d',
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
                    'negative_item_positions' => $this->negativeItemPositions($code),
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
                    'is_negative_statement' => in_array(
                        $position,
                        $forms[$lastFormIndex]['negative_item_positions'],
                        true
                    ),
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
                'source' => 'Instrumen Pemetaan Kompetensi Kepala Sekolah BBGTK Sulawesi Selatan',
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

        if (count(self::NEGATIVE_ITEM_POSITIONS_BY_FORM) !== self::EXPECTED_FORM_COUNT) {
            throw new RuntimeException(
                'Jumlah pemetaan butir negatif tidak sesuai dengan jumlah subindikator kepala sekolah.'
            );
        }

        foreach ($forms as $form) {
            if (count($form['items']) !== 7) {
                throw new RuntimeException(
                    "Subindikator {$form['subindikator_kode']} harus berisi 7 butir Likert."
                );
            }

            if (count($form['negative_item_positions']) !== 2) {
                throw new RuntimeException(
                    "Subindikator {$form['subindikator_kode']} harus memiliki tepat 2 butir negatif."
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

    /**
     * @return array<int, int>
     */
    private function negativeItemPositions(string $subindikatorCode): array
    {
        $positions = self::NEGATIVE_ITEM_POSITIONS_BY_FORM[$subindikatorCode] ?? null;

        if ($positions === null) {
            throw new RuntimeException(
                "Pemetaan butir negatif belum didefinisikan untuk subindikator {$subindikatorCode}."
            );
        }

        return $positions;
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
