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

class AssessmentAngketPemetaanKompetensiPengawasSekolahSeeder extends Seeder
{
    private const ASSESSMENT_CODE = 'ASM-PENGAWAS-ANGKET-2026';

    // Butir negatif pada naskah angket berada di posisi 4 dan 7 setiap subindikator.
    private const NEGATIVE_ITEM_POSITIONS = [4, 7];

    private const EXPECTED_FORM_COUNT = 23;

    private const EXPECTED_ITEM_COUNT = 161;

    private const EXPECTED_NEGATIVE_ITEM_COUNT = 46;

    private const INSTRUMENT_DATA = <<<'DATA'
C|kepribadian|Kompetensi Kepribadian
I|1.1|Kematangan moral, emosi dan spiritual dalam berperilaku sesuai dengan kode etik.
F|1.1.1|Makna, tujuan, dan pandangan hidup berdasarkan prinsip moral dan keyakinan terhadap Tuhan Yang Maha Esa dalam menjalankan peran sebagai pengawas sekolah.
Q|1|Saya memaknai peran pengawas sekolah sebagai amanah profesional untuk mendampingi satuan pendidikan dalam meningkatkan kualitas layanan pendidikan yang berpihak pada peserta didik.
Q|2|Saya menjadikan nilai kejujuran, keadilan, tanggung jawab, dan keteladanan sebagai pertimbangan dalam memberikan pendampingan dan rekomendasi kepada satuan pendidikan.
Q|3|Saya menggunakan keyakinan terhadap Tuhan Yang Maha Esa sebagai landasan moral untuk menjaga integritas, objektivitas, dan tanggung jawab dalam menjalankan tugas pengawasan.
Q|4|Saya lebih mengutamakan penyelesaian tugas pengawasan secara administratif daripada memastikan bahwa pendampingan yang diberikan benar-benar memberikan manfaat bagi peningkatan kualitas pembelajaran dan layanan satuan pendidikan.
Q|5|Saya mampu menjaga konsistensi antara nilai moral yang saya yakini dengan sikap dan keputusan yang saya tunjukkan ketika menghadapi berbagai situasi dalam pelaksanaan tugas pengawasan.
Q|6|Saya melakukan refleksi terhadap makna dan tujuan tugas saya sebagai pengawas sekolah agar pelaksanaan pendampingan tidak berhenti pada pemenuhan kewajiban, tetapi mendorong perbaikan yang berkelanjutan.
Q|7|Saya cenderung menganggap pertimbangan moral dan nilai-nilai keagamaan sebagai urusan pribadi yang tidak perlu menjadi landasan dalam menjalankan tanggung jawab profesional sebagai pengawas sekolah.
F|1.1.2|Pengelolaan emosi dalam menjalankan peran sebagai pengawas sekolah.
Q|1|Saya mampu mengenali perubahan emosi diri ketika menghadapi situasi supervisi yang menantang dan menyesuaikan respons sebelum mengambil tindakan.
Q|2|Saya tetap mampu memberikan umpan balik secara objektif dan konstruktif ketika kepala sekolah atau guru menunjukkan respons yang berbeda dengan harapan saya.
Q|3|Saya menggunakan teknik pengendalian diri yang tepat agar tekanan, ketegangan, atau perbedaan pendapat tidak memengaruhi kualitas pertimbangan profesional saya.
Q|4|Saya cenderung membiarkan kekecewaan terhadap respons satuan pendidikan memengaruhi cara saya berkomunikasi dan memberikan pendampingan berikutnya.
Q|5|Saya mampu mempertahankan sikap tenang dan menghargai pihak lain ketika menghadapi kritik, penolakan, atau perbedaan pandangan selama proses pendampingan.
Q|6|Saya melakukan refleksi terhadap respons emosional yang muncul selama pelaksanaan tugas untuk memperbaiki cara berkomunikasi dan membangun hubungan profesional yang lebih efektif.
Q|7|Ketika menghadapi persoalan yang berulang di satuan pendidikan, saya lebih banyak mengikuti reaksi emosional awal daripada mempertimbangkan data, konteks, dan kebutuhan pendampingan.
F|1.1.3|Penerapan kode etik dalam menjalankan tugas dan peran sebagai pengawas sekolah.
Q|1|Saya menjadikan prinsip integritas, objektivitas, keadilan, dan tanggung jawab sebagai landasan dalam melaksanakan supervisi dan pendampingan satuan pendidikan.
Q|2|Saya menjaga kerahasiaan informasi yang diperoleh selama supervisi dan pendampingan serta menggunakannya hanya untuk kepentingan peningkatan mutu pendidikan.
Q|3|Saya mampu mengenali potensi konflik kepentingan dalam pelaksanaan tugas dan mengambil langkah yang tepat agar tidak memengaruhi objektivitas penilaian maupun rekomendasi yang saya berikan.
Q|4|Saya cenderung menyesuaikan hasil penilaian atau rekomendasi dengan harapan pihak tertentu agar hubungan kerja tetap nyaman, meskipun tidak sepenuhnya didukung oleh bukti yang saya peroleh.
Q|5|Saya menyampaikan temuan, umpan balik, dan rekomendasi secara profesional dengan mempertimbangkan bukti, konteks satuan pendidikan, serta prinsip penghormatan terhadap pihak yang didampingi.
Q|6|Ketika menghadapi dilema etis, saya mempertimbangkan prinsip kode etik, dampak keputusan, kepentingan peserta didik, dan tanggung jawab profesi sebelum menentukan tindakan.
Q|7|Saya lebih mengutamakan penyelesaian tugas pengawasan secara cepat daripada memastikan setiap proses supervisi dan pendampingan telah memenuhi prinsip etika profesi.
I|1.2|Pengembangan diri melalui kebiasaan refleksi.
F|1.2.1|Refleksi untuk perencanaan pengembangan diri dalam peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.
Q|1|Saya menggunakan hasil supervisi, pendampingan, dan data satuan pendidikan sebagai bahan refleksi untuk mengidentifikasi kekuatan serta area kompetensi yang masih perlu saya kembangkan.
Q|2|Saya mampu mengidentifikasi kesenjangan antara kompetensi yang saya miliki dengan tuntutan peran pengawas sekolah dalam meningkatkan mutu layanan yang berpusat pada peserta didik.
Q|3|Saya menetapkan prioritas pengembangan diri berdasarkan hasil refleksi, kebutuhan satuan pendidikan, dan perubahan tuntutan profesional pengawas sekolah.
Q|4|Saya cenderung merencanakan pengembangan diri berdasarkan kegiatan yang tersedia tanpa terlebih dahulu mengaitkannya dengan hasil refleksi dan kebutuhan nyata satuan pendidikan yang saya dampingi.
Q|5|Saya menyusun rencana pengembangan diri yang memiliki tujuan, strategi, indikator keberhasilan, dan rentang waktu yang jelas untuk meningkatkan kualitas praktik kepengawasan.
Q|6|Saya memanfaatkan umpan balik dari kepala sekolah, guru, rekan sejawat, atau pihak terkait sebagai bahan untuk menguji kembali hasil refleksi dan menyempurnakan rencana pengembangan diri.
Q|7|Saya lebih banyak merefleksikan keberhasilan penyelesaian tugas administratif daripada menelaah dampak praktik kepengawasan saya terhadap peningkatan mutu layanan bagi peserta didik.
F|1.2.2|Cara adaptif melakukan pengembangan diri untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.
Q|1|Saya memilih bentuk pengembangan diri berdasarkan kebutuhan kompetensi yang telah saya identifikasi dan tantangan nyata yang dihadapi satuan pendidikan yang saya dampingi.
Q|2|Saya memanfaatkan berbagai sumber belajar, termasuk pelatihan, komunitas profesional, praktik baik, literatur, dan teknologi digital untuk memperbarui kompetensi kepengawasan.
Q|3|Saya menyesuaikan strategi pengembangan diri ketika kebutuhan satuan pendidikan atau tuntutan profesional pengawas sekolah mengalami perubahan.
Q|4|Saya cenderung mengikuti kegiatan pengembangan kompetensi yang bersifat umum tanpa menilai terlebih dahulu relevansinya dengan kebutuhan pengembangan diri dan konteks satuan pendidikan yang saya dampingi.
Q|5|Saya mengombinasikan pembelajaran mandiri, kolaborasi dengan rekan sejawat, dan pengalaman praktik untuk memperkuat kompetensi yang saya perlukan dalam menjalankan tugas kepengawasan.
Q|6|Saya mencoba, mengevaluasi, dan menyempurnakan strategi pengembangan diri berdasarkan perubahan kebutuhan serta hasil penerapannya dalam praktik kepengawasan.
Q|7|Saya lebih mengandalkan pengalaman kerja yang sudah saya miliki daripada mencari pengetahuan atau pendekatan baru ketika menghadapi tuntutan kepengawasan yang berubah.
F|1.2.3|Penerapan hasil pengembangan diri untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.
Q|1|Saya menerapkan pengetahuan, keterampilan, atau pendekatan baru yang diperoleh dari kegiatan pengembangan diri ke dalam praktik supervisi dan pendampingan satuan pendidikan.
Q|2|Saya menyesuaikan hasil pengembangan diri dengan konteks, kebutuhan, dan karakteristik satuan pendidikan sebelum menerapkannya dalam praktik kepengawasan.
Q|3|Saya menguji efektivitas penerapan hasil pengembangan diri melalui data, umpan balik, atau bukti perubahan yang diperoleh selama proses pendampingan.
Q|4|Saya cenderung menganggap kegiatan pengembangan diri telah berhasil apabila saya memperoleh sertifikat atau menyelesaikan kegiatan, meskipun belum menerapkannya dalam praktik kepengawasan.
Q|5|Saya menggunakan hasil refleksi dari penerapan pengetahuan atau keterampilan baru untuk menyempurnakan strategi supervisi dan pendampingan berikutnya.
Q|6|Saya membagikan hasil pengembangan diri yang relevan kepada kepala sekolah, guru, atau rekan sejawat sebagai bagian dari upaya memperluas dampak peningkatan mutu layanan pendidikan.
Q|7|Saya lebih sering mempertahankan cara kerja kepengawasan yang sudah biasa dilakukan daripada mencoba menerapkan hasil pengembangan diri yang berpotensi meningkatkan mutu pendampingan.
I|1.3|Orientasi berpusat pada peserta didik.
F|1.3.1|Empati terhadap peserta didik dalam pengambilan keputusan pendampingan kepada kepala sekolah.
Q|1|Saya mempertimbangkan pengalaman, kebutuhan, dan kondisi peserta didik ketika memberikan rekomendasi kepada kepala sekolah terkait peningkatan mutu layanan pendidikan.
Q|2|Saya menggunakan informasi tentang pengalaman belajar peserta didik sebagai salah satu bahan untuk memahami dampak keputusan atau kebijakan satuan pendidikan.
Q|3|Saya mampu melihat suatu persoalan satuan pendidikan dari perspektif peserta didik sebelum menentukan arah pendampingan kepada kepala sekolah.
Q|4|Saya cenderung lebih memprioritaskan kemudahan pelaksanaan program bagi pengelola satuan pendidikan daripada mempertimbangkan pengalaman dan kebutuhan peserta didik yang terdampak.
Q|5|Saya menggali berbagai sumber informasi secara proporsional untuk memahami kondisi peserta didik sebelum memberikan rekomendasi yang berkaitan dengan layanan pendidikan.
Q|6|Saya menyesuaikan pendekatan pendampingan kepada kepala sekolah ketika menemukan bahwa keputusan yang direncanakan belum cukup mempertimbangkan kebutuhan atau pengalaman peserta didik.
Q|7|Saya jarang meninjau kembali dampak keputusan pendampingan terhadap pengalaman dan kebutuhan peserta didik setelah rekomendasi diberikan.
F|1.3.2|Respek terhadap hak peserta didik dalam pendampingan kepada kepala sekolah.
Q|1|Saya menjadikan penghormatan terhadap hak peserta didik sebagai pertimbangan dalam memberikan arahan dan rekomendasi kepada kepala sekolah.
Q|2|Saya mempertimbangkan hak peserta didik untuk memperoleh layanan pendidikan yang adil, aman, bermutu, dan sesuai dengan kebutuhannya ketika melakukan pendampingan.
Q|3|Saya mendorong kepala sekolah untuk memastikan bahwa keputusan dan kebijakan satuan pendidikan tidak mengabaikan hak peserta didik karena perbedaan kondisi, kemampuan, latar belakang, atau kebutuhan belajar.
Q|4|Saya cenderung lebih mengutamakan kepatuhan peserta didik terhadap aturan satuan pendidikan daripada menelaah apakah aturan tersebut telah mempertimbangkan hak dan kebutuhan mereka.
Q|5|Saya menggunakan data dan informasi yang relevan untuk mengidentifikasi kemungkinan terjadinya perlakuan yang tidak adil atau diskriminatif terhadap peserta didik.
Q|6|Saya mendorong kepala sekolah untuk menyediakan mekanisme yang memungkinkan suara, kebutuhan, dan pengalaman peserta didik menjadi bahan pertimbangan dalam peningkatan layanan pendidikan.
Q|7|Saya jarang meninjau kembali rekomendasi pendampingan meskipun terdapat informasi bahwa rekomendasi tersebut berpotensi mengurangi pemenuhan hak peserta didik.
F|1.3.3|Kepedulian terhadap keselamatan dan keamanan peserta didik sebagai individu dan kelompok dalam menjalankan peran sebagai pengawas sekolah.
Q|1|Saya memasukkan aspek keselamatan dan keamanan peserta didik sebagai pertimbangan penting dalam melakukan supervisi dan memberikan rekomendasi kepada kepala sekolah.
Q|2|Saya mampu mengidentifikasi potensi risiko terhadap keselamatan dan keamanan peserta didik melalui informasi, data, dan hasil pendampingan satuan pendidikan.
Q|3|Saya mendorong kepala sekolah untuk membangun sistem pencegahan dan penanganan risiko yang melindungi peserta didik sebagai individu maupun kelompok.
Q|4|Saya cenderung menganggap persoalan keselamatan dan keamanan peserta didik sebagai tanggung jawab internal sekolah yang tidak perlu menjadi bagian penting dari pendampingan saya.
Q|5|Saya mempertimbangkan kelompok peserta didik yang memiliki kerentanan atau kebutuhan perlindungan tertentu ketika menelaah kondisi keselamatan dan keamanan satuan pendidikan.
Q|6|Saya menggunakan temuan supervisi dan informasi terkait risiko sebagai dasar untuk mendorong kepala sekolah melakukan tindakan pencegahan dan tindak lanjut yang terukur.
Q|7|Saya lebih berfokus pada terpenuhinya prosedur administratif daripada memastikan bahwa langkah yang dilakukan satuan pendidikan benar-benar memberikan perlindungan bagi peserta didik.
C|sosial|Kompetensi Sosial
I|2.1|Kolaborasi untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.
F|2.1.1|Komunikasi efektif dengan kepala sekolah untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.
Q|1|Saya menyampaikan hasil supervisi, temuan, dan rekomendasi kepada kepala sekolah secara jelas, objektif, dan berorientasi pada perbaikan mutu layanan peserta didik.
Q|2|Saya menyesuaikan cara berkomunikasi dengan karakteristik, kebutuhan, dan konteks kepala sekolah agar pesan pendampingan dapat dipahami dan ditindaklanjuti secara tepat.
Q|3|Saya menggunakan pertanyaan terbuka dan mendengarkan secara aktif untuk memahami perspektif kepala sekolah sebelum menyepakati langkah perbaikan.
Q|4|Saya cenderung menyampaikan rekomendasi secara satu arah tanpa memberikan ruang yang memadai kepada kepala sekolah untuk menyampaikan perspektif, kendala, atau alternatif solusi.
Q|5|Saya mampu mengelola perbedaan pendapat dengan kepala sekolah secara konstruktif tanpa mengurangi objektivitas dan tujuan pendampingan.
Q|6|Saya menggunakan data dan bukti yang relevan sebagai dasar pembicaraan agar komunikasi dengan kepala sekolah tidak didominasi oleh asumsi atau pendapat pribadi.
Q|7|Saya jarang melakukan tindak lanjut terhadap kesepakatan dengan kepala sekolah setelah komunikasi pendampingan selesai dilakukan.
F|2.1.2|Kerja sama dengan seluruh kepala sekolah dampingan dan rekan sejawat untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.
Q|1|Saya membangun kerja sama dengan kepala sekolah dampingan untuk mengidentifikasi dan menyelesaikan persoalan mutu layanan pendidikan secara bersama-sama.
Q|2|Saya memfasilitasi pertukaran praktik baik antarsatuan pendidikan dampingan agar pengalaman dan solusi yang relevan dapat dimanfaatkan untuk peningkatan layanan peserta didik.
Q|3|Saya berkolaborasi dengan rekan sejawat untuk menganalisis persoalan kepengawasan, mengembangkan alternatif solusi, dan memperkuat kualitas pendampingan.
Q|4|Saya cenderung bekerja secara individual dalam menjalankan kepengawasan karena menganggap kerja sama dengan pengawas lain tidak banyak memberikan manfaat bagi penyelesaian masalah di satuan pendidikan dampingan.
Q|5|Saya membangun jejaring kerja yang memungkinkan kepala sekolah dampingan dan rekan sejawat saling berbagi sumber daya, pengetahuan, dan pengalaman untuk mendukung peningkatan mutu layanan.
Q|6|Saya menyesuaikan pola kerja sama berdasarkan karakteristik, kebutuhan, dan kapasitas masing-masing satuan pendidikan serta kompetensi rekan sejawat yang terlibat.
Q|7|Saya lebih banyak mempertahankan pola kerja sama yang sudah berjalan daripada mengevaluasi dan mengembangkan bentuk kolaborasi baru ketika kebutuhan satuan pendidikan berubah.
I|2.2|Keterlibatan pemangku kepentingan untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.
F|2.2.1|Pelibatan pemangku kepentingan dalam pendampingan kepala sekolah untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.
Q|1|Saya mengidentifikasi pemangku kepentingan yang memiliki peran, kompetensi, sumber daya, atau pengaruh yang relevan terhadap peningkatan mutu layanan satuan pendidikan.
Q|2|Saya memfasilitasi kepala sekolah dalam menentukan bentuk keterlibatan pemangku kepentingan yang sesuai dengan kebutuhan dan prioritas peningkatan mutu satuan pendidikan.
Q|3|Saya membangun ruang kolaborasi yang memungkinkan pemangku kepentingan memberikan pengetahuan, perspektif, sumber daya, atau dukungan sesuai kapasitasnya.
Q|4|Saya cenderung melibatkan pemangku kepentingan hanya ketika satuan pendidikan membutuhkan bantuan untuk menyelesaikan persoalan yang sudah terjadi.
Q|5|Saya memastikan pelibatan pemangku kepentingan tetap mempertimbangkan kepentingan peserta didik, tujuan satuan pendidikan, pembagian peran, serta batas tanggung jawab masing-masing pihak.
Q|6|Saya mengevaluasi kontribusi pemangku kepentingan dalam pendampingan kepala sekolah dan menggunakan hasilnya untuk memperbaiki pola kolaborasi berikutnya.
Q|7|Saya lebih mengutamakan keterlibatan pemangku kepentingan yang sudah dikenal daripada membuka peluang kolaborasi dengan pihak lain yang memiliki kompetensi atau sumber daya yang lebih sesuai dengan kebutuhan satuan pendidikan.
F|2.2.2|Berkoordinasi secara berkala dengan pemangku kepentingan untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.
Q|1|Saya menetapkan pola komunikasi dan koordinasi dengan pemangku kepentingan berdasarkan kebutuhan, tujuan, dan prioritas peningkatan mutu layanan satuan pendidikan.
Q|2|Saya menyampaikan informasi yang relevan mengenai perkembangan pendampingan dan kebutuhan satuan pendidikan secara tepat waktu kepada pemangku kepentingan yang berkepentingan.
Q|3|Saya menggunakan koordinasi berkala untuk menyelaraskan peran, tanggung jawab, sumber daya, dan dukungan yang diperlukan dalam peningkatan mutu layanan pendidikan.
Q|4|Saya cenderung melakukan koordinasi dengan pemangku kepentingan hanya ketika terdapat masalah yang membutuhkan penyelesaian segera.
Q|5|Saya membuka ruang dialog untuk memperoleh masukan, mengklarifikasi informasi, dan membangun kesepahaman sebelum menetapkan tindak lanjut bersama pemangku kepentingan.
Q|6|Saya mendokumentasikan hasil koordinasi dan menggunakan kesepakatan yang diperoleh sebagai dasar untuk memantau pelaksanaan tindak lanjut.
Q|7|Saya lebih banyak mengandalkan komunikasi informal sehingga hasil koordinasi dan pembagian tindak lanjut tidak selalu terdokumentasi secara jelas.
I|2.3|Keterlibatan dalam organisasi profesi dan jejaring yang lebih luas untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.
F|2.3.1|Berpartisipasi aktif dalam organisasi profesi dan jejaring yang lebih luas untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.
Q|1|Saya berpartisipasi aktif dalam organisasi profesi atau jejaring pengawas sebagai sarana memperluas wawasan dan meningkatkan kualitas praktik kepengawasan.
Q|2|Saya memanfaatkan jejaring profesional untuk bertukar pengalaman, menganalisis persoalan kepengawasan, dan menemukan alternatif solusi yang relevan dengan kebutuhan satuan pendidikan.
Q|3|Saya berkontribusi dalam kegiatan organisasi profesi atau jejaring melalui gagasan, pengalaman, sumber daya, atau keahlian yang dapat mendukung peningkatan mutu layanan pendidikan.
Q|4|Saya cenderung mengikuti kegiatan organisasi profesi atau jejaring sebatas memenuhi kewajiban kehadiran tanpa memanfaatkannya sebagai ruang belajar dan pengembangan praktik kepengawasan.
Q|5|Saya membangun hubungan profesional dengan pihak di luar lingkungan kerja untuk memperoleh perspektif baru mengenai peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.
Q|6|Saya menindaklanjuti pengetahuan atau peluang kolaborasi yang diperoleh melalui organisasi profesi dan jejaring ke dalam praktik pendampingan yang relevan.
Q|7|Saya lebih mengandalkan pengalaman dan jejaring yang sudah saya miliki daripada secara aktif mencari dan membangun jejaring baru yang dapat memperkaya praktik kepengawasan.
F|2.3.2|Berbagi praktik baik dan karya pendampingan kepada kepala sekolah untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.
Q|1|Saya mengidentifikasi praktik pendampingan yang terbukti memberikan kontribusi terhadap peningkatan mutu layanan satuan pendidikan untuk dikembangkan menjadi praktik baik.
Q|2|Saya mendokumentasikan proses, bukti, hasil, dan pembelajaran dari praktik pendampingan agar dapat dipahami dan dimanfaatkan oleh kepala sekolah.
Q|3|Saya membagikan praktik baik atau karya pendampingan dengan menjelaskan konteks, strategi, bukti hasil, dan kondisi yang perlu diperhatikan ketika diterapkan pada satuan pendidikan lain.
Q|4|Saya cenderung membagikan pengalaman pendampingan secara umum tanpa mendokumentasikan bukti yang memadai mengenai proses dan hasilnya.
Q|5|Saya menyesuaikan bentuk dan media berbagi praktik baik dengan kebutuhan kepala sekolah agar pengetahuan yang dibagikan mudah dipahami dan dapat diterapkan secara kontekstual.
Q|6|Saya menggunakan umpan balik dari kepala sekolah atau rekan sejawat untuk memperbaiki kualitas karya dan praktik pendampingan yang saya bagikan.
Q|7|Saya lebih sering menyimpan pengalaman dan karya pendampingan sebagai dokumentasi pribadi daripada membagikannya untuk memperluas pembelajaran profesional.
C|profesional|Kompetensi Profesional
I|3.1|Pendampingan kepada kepala sekolah dalam pengembangan diri untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.
F|3.1.1|Pendampingan kepada kepala sekolah dalam mengidentifikasi kebutuhan pengembangan diri kepala sekolah.
Q|1|Saya menggunakan hasil supervisi, refleksi, data kinerja, dan kebutuhan satuan pendidikan sebagai bahan untuk membantu kepala sekolah mengidentifikasi kebutuhan pengembangan dirinya.
Q|2|Saya memfasilitasi kepala sekolah untuk menganalisis kesenjangan antara kompetensi yang dimiliki dengan kompetensi yang diperlukan dalam meningkatkan mutu layanan satuan pendidikan.
Q|3|Saya membantu kepala sekolah membedakan kebutuhan pengembangan diri yang bersifat prioritas dengan kebutuhan yang dapat dikembangkan secara bertahap berdasarkan konteks satuan pendidikan.
Q|4|Saya cenderung menentukan kebutuhan pengembangan diri kepala sekolah berdasarkan penilaian saya sendiri tanpa memberikan ruang yang cukup bagi kepala sekolah untuk melakukan refleksi dan mengidentifikasi kebutuhannya.
Q|5|Saya mengaitkan kebutuhan pengembangan diri kepala sekolah dengan tantangan kepemimpinan, kebutuhan guru, serta dampaknya terhadap pengalaman dan capaian belajar peserta didik.
Q|6|Saya menggunakan pertanyaan reflektif dan bukti yang relevan untuk membantu kepala sekolah memperoleh pemahaman yang lebih mendalam mengenai area kompetensi yang perlu dikembangkan.
Q|7|Saya lebih banyak menggunakan hasil penilaian administratif sebagai dasar identifikasi kebutuhan pengembangan diri daripada menggali praktik kepemimpinan dan tantangan nyata yang dihadapi kepala sekolah.
F|3.1.2|Pendampingan kepada kepala sekolah untuk menyusun rencana pengembangan diri.
Q|1|Saya memfasilitasi kepala sekolah menyusun tujuan pengembangan diri yang jelas dan relevan dengan kebutuhan peningkatan mutu layanan satuan pendidikan.
Q|2|Saya membantu kepala sekolah menentukan strategi, sumber belajar, dan kegiatan pengembangan diri yang sesuai dengan kebutuhan kompetensinya.
Q|3|Saya mendampingi kepala sekolah menetapkan indikator keberhasilan dan bukti capaian yang dapat digunakan untuk mengetahui perkembangan pengembangan dirinya.
Q|4|Saya cenderung memberikan format atau contoh rencana pengembangan diri untuk langsung diikuti kepala sekolah tanpa terlebih dahulu memastikan kesesuaiannya dengan kebutuhan dan konteks satuan pendidikan.
Q|5|Saya membantu kepala sekolah menetapkan prioritas, tahapan, dan rentang waktu pengembangan diri agar rencana dapat dilaksanakan secara realistis dan berkelanjutan.
Q|6|Saya mendorong kepala sekolah mengantisipasi hambatan pelaksanaan dan menyiapkan alternatif strategi agar rencana pengembangan diri tetap dapat berjalan ketika kondisi berubah.
Q|7|Saya lebih berfokus pada kelengkapan dokumen rencana pengembangan diri daripada memastikan bahwa tujuan, strategi, dan indikator keberhasilannya benar-benar dapat mendukung perubahan praktik kepemimpinan.
F|3.1.3|Pendampingan kepada kepala sekolah dalam melaksanakan pengembangan diri sesuai dengan rencana pengembangan diri.
Q|1|Saya membantu kepala sekolah menerjemahkan rencana pengembangan diri menjadi langkah-langkah pelaksanaan yang dapat dilakukan dan dipantau secara bertahap.
Q|2|Saya menggunakan hasil pemantauan dan refleksi bersama untuk membantu kepala sekolah mengetahui kemajuan pelaksanaan pengembangan dirinya.
Q|3|Saya memfasilitasi kepala sekolah untuk menyesuaikan strategi pengembangan diri ketika terdapat perubahan kebutuhan, hambatan, atau kondisi satuan pendidikan.
Q|4|Saya cenderung menilai keberhasilan pengembangan diri kepala sekolah berdasarkan keikutsertaan dalam kegiatan atau pelatihan tanpa menelaah perubahan praktik yang dihasilkan.
Q|5|Saya membantu kepala sekolah menghubungkan hasil pengembangan diri dengan perubahan dalam praktik kepemimpinan dan peningkatan mutu layanan satuan pendidikan.
Q|6|Saya menggunakan bukti pelaksanaan, umpan balik, dan refleksi kepala sekolah untuk memberikan penguatan atau rekomendasi perbaikan terhadap proses pengembangan dirinya.
Q|7|Saya lebih banyak mengingatkan kepala sekolah untuk menyelesaikan rencana pengembangan diri daripada mendampingi proses penerapan hasil pengembangan tersebut dalam praktik kepemimpinan.
I|3.2|Pendampingan kepada kepala sekolah dalam pengembangan satuan pendidikan untuk peningkatan mutu layanan pendidikan yang berpusat pada peserta didik.
F|3.2.1|Pemetaaan komitmen perubahan kepala sekolah dampingan, strategi, dan metode pendampingan pada perencanaan pendampingan satuan pendidikan berbasis profil satuan pendidikan untuk peningkatan mutu layanan pendidikan yang berpusat pada peserta didik.
Q|1|Saya menganalisis profil satuan pendidikan dan bukti pendukung untuk memahami kondisi, kebutuhan prioritas, serta tantangan perubahan yang dihadapi kepala sekolah.
Q|2|Saya memetakan tingkat komitmen kepala sekolah terhadap perubahan berdasarkan hasil dialog, refleksi, bukti praktik, dan respons terhadap kebutuhan peningkatan mutu satuan pendidikan.
Q|3|Saya menggunakan hasil pemetaan komitmen perubahan dan profil satuan pendidikan sebagai dasar dalam menentukan strategi serta metode pendampingan yang sesuai.
Q|4|Saya cenderung menggunakan strategi dan metode pendampingan yang sama untuk seluruh kepala sekolah dampingan tanpa mempertimbangkan perbedaan profil, kebutuhan, dan kesiapan perubahan masing-masing satuan pendidikan.
Q|5|Saya merancang pendekatan pendampingan yang mempertimbangkan kesiapan kepala sekolah, kapasitas satuan pendidikan, kompleksitas masalah, dan prioritas peningkatan mutu layanan peserta didik.
Q|6|Saya menguji kembali kesesuaian strategi pendampingan berdasarkan perubahan kondisi, respons kepala sekolah, dan bukti perkembangan yang diperoleh selama proses pendampingan.
Q|7|Saya lebih banyak menggunakan kelengkapan dokumen profil satuan pendidikan sebagai dasar perencanaan pendampingan daripada menganalisis makna data dan kondisi nyata yang ditunjukkan oleh profil tersebut.
F|3.2.2|Pendampingan kepada kepala sekolah dalam perencanaan program pengembangan satuan pendidikan berbasis profil satuan pendidikan untuk peningkatan mutu layanan yang berpusat pada peserta didik.
Q|1|Saya memfasilitasi kepala sekolah menganalisis profil satuan pendidikan untuk menentukan masalah dan prioritas yang perlu direspons melalui program pengembangan satuan pendidikan.
Q|2|Saya membantu kepala sekolah merumuskan tujuan program yang jelas dan terukur berdasarkan kebutuhan serta prioritas yang teridentifikasi dari profil satuan pendidikan.
Q|3|Saya mendampingi kepala sekolah menyusun strategi, tahapan kegiatan, indikator keberhasilan, dan sumber daya yang diperlukan untuk melaksanakan program pengembangan satuan pendidikan.
Q|4|Saya cenderung memberikan rancangan program yang sudah jadi kepada kepala sekolah untuk diikuti daripada memfasilitasi kepala sekolah menganalisis kebutuhan dan menyusun program sesuai konteks satuan pendidikannya.
Q|5|Saya membantu kepala sekolah memastikan keterkaitan antara program yang direncanakan dengan peningkatan kualitas pembelajaran, layanan, dan pengalaman belajar peserta didik.
Q|6|Saya memfasilitasi kepala sekolah mengantisipasi risiko, hambatan, dan kebutuhan penyesuaian agar program pengembangan satuan pendidikan dapat dilaksanakan secara realistis.
Q|7|Saya lebih berfokus pada kesesuaian format dan kelengkapan dokumen perencanaan program daripada menguji apakah program tersebut benar-benar menjawab kebutuhan yang ditunjukkan oleh profil satuan pendidikan.
F|3.2.3|Pendampingan kepada kepala sekolah dalam pelaksanaan program pengembangan satuan pendidikan untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.
Q|1|Saya mendampingi kepala sekolah menerjemahkan rencana program pengembangan satuan pendidikan menjadi langkah pelaksanaan, pembagian peran, dan target yang dapat dipantau.
Q|2|Saya menggunakan data pelaksanaan dan perkembangan program untuk membantu kepala sekolah mengetahui kemajuan, hambatan, dan kebutuhan penyesuaian program.
Q|3|Saya memfasilitasi kepala sekolah dalam mengambil keputusan adaptif ketika kondisi pelaksanaan program berbeda dari yang direncanakan.
Q|4|Saya cenderung menunggu laporan akhir program sebelum memberikan pendampingan atau membantu kepala sekolah mengatasi hambatan selama pelaksanaan.
Q|5|Saya membantu kepala sekolah menggunakan bukti pelaksanaan untuk menilai apakah program mulai memberikan perubahan terhadap mutu layanan dan pengalaman belajar peserta didik.
Q|6|Saya mendorong kepala sekolah melakukan refleksi bersama warga satuan pendidikan untuk menentukan perbaikan atau tindak lanjut berdasarkan hasil pelaksanaan program.
Q|7|Saya lebih mengutamakan agar program terlaksana sesuai jadwal daripada mempertimbangkan penyesuaian yang diperlukan ketika pelaksanaan menunjukkan bahwa strategi yang digunakan belum efektif.
I|3.3|Pendampingan kepada kepala sekolah dalam mengelola implementasi kebijakan pendidikan pada satuan pendidikan untuk peningkatan mutu layanan pendidikan yang berpusat pada peserta didik.
F|3.3.1|Pendampingan kepada kepala sekolah dalam mengkaji kebijakan pendidikan untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.
Q|1|Saya membantu kepala sekolah mengidentifikasi tujuan, prinsip, ruang lingkup, dan implikasi kebijakan pendidikan yang relevan dengan kondisi satuan pendidikan.
Q|2|Saya memfasilitasi kepala sekolah menganalisis kesesuaian kebijakan dengan kebutuhan, karakteristik, kapasitas, dan prioritas peningkatan mutu satuan pendidikan.
Q|3|Saya membantu kepala sekolah menggunakan data dan bukti satuan pendidikan untuk memahami potensi dampak kebijakan terhadap pembelajaran dan layanan bagi peserta didik.
Q|4|Saya cenderung menyampaikan isi kebijakan kepada kepala sekolah untuk diterapkan tanpa terlebih dahulu membantu menganalisis relevansi, konsekuensi, dan kebutuhan penyesuaiannya dengan konteks satuan pendidikan.
Q|5|Saya memfasilitasi kepala sekolah mengidentifikasi peluang, risiko, hambatan, dan kebutuhan sumber daya yang mungkin muncul dalam penerapan kebijakan pendidikan.
Q|6|Saya membantu kepala sekolah menerjemahkan substansi kebijakan menjadi implikasi terhadap perencanaan, pengelolaan, pembelajaran, dan layanan satuan pendidikan.
Q|7|Saya lebih berfokus pada memastikan kepala sekolah mengetahui ketentuan kebijakan daripada mendalami bagaimana kebijakan tersebut dapat diterapkan untuk menjawab kebutuhan nyata peserta didik dan satuan pendidikan.
F|3.3.2|Pendampingan kepada kepala sekolah dalam implementasi kebijakan pendidikan untuk peningkatan mutu layanan satuan pendidikan yang berpusat pada peserta didik.
Q|1|Saya membantu kepala sekolah menerjemahkan kebijakan pendidikan menjadi langkah implementasi yang sesuai dengan kebutuhan dan konteks satuan pendidikan.
Q|2|Saya mendampingi kepala sekolah menetapkan prioritas, pembagian peran, sumber daya, dan tahapan implementasi kebijakan secara realistis.
Q|3|Saya menggunakan data dan bukti pelaksanaan untuk membantu kepala sekolah memantau kemajuan serta mengidentifikasi hambatan implementasi kebijakan.
Q|4|Saya cenderung menganggap kebijakan telah terimplementasi dengan baik selama dokumen dan kegiatan yang dipersyaratkan telah tersedia atau terlaksana.
Q|5|Saya memfasilitasi kepala sekolah dalam menyesuaikan strategi implementasi ketika ditemukan kesenjangan antara kebijakan, kondisi lapangan, dan kebutuhan peserta didik.
Q|6|Saya membantu kepala sekolah mengevaluasi dampak implementasi kebijakan terhadap mutu pembelajaran dan layanan satuan pendidikan sebagai dasar perbaikan berikutnya.
Q|7|Saya lebih mengutamakan kepatuhan terhadap prosedur implementasi kebijakan daripada memastikan bahwa penerapannya menghasilkan perbaikan nyata bagi peserta didik dan satuan pendidikan.
DATA;

    public function run(): void
    {
        $forms = $this->forms();

        $this->persistAssessment([
            'kode_assessment' => self::ASSESSMENT_CODE,
            'judul' => 'Angket Kompetensi Pengawas',
            'deskripsi' => 'Angket kompetensi pemetaan kompetensi kepribadian, sosial, dan profesional pengawas sekolah BBGTK Sulawesi Selatan Tahun 2026.',
            'petunjuk' => 'Pilihlah jawaban yang sesuai dengan kondisi atau pemahaman Anda saat ini secara jujur. Pilihan jawaban tidak ada yang salah. Skala Likert: 1 = Sangat Tidak Setuju/Mampu/Menguasai; 2 = Tidak Setuju/Mampu/Menguasai; 3 = Cukup Setuju/Mampu/Menguasai; 4 = Setuju/Mampu/Menguasai; 5 = Sangat Setuju/Mampu/Menguasai.',
            'instrument_type' => AssessmentInstrumentType::SKALA_LIKERT->value,
            'scoring_config' => $this->assessmentScoringConfig($forms),
            'forms' => $forms,
        ]);
    }

    private function persistAssessment(array $config): void
    {
        $forms = $config['forms'];
        unset($config['forms']);

        $assessment = Assessment::updateOrCreate(
            ['kode_assessment' => $config['kode_assessment']],
            [
                'judul' => $config['judul'],
                'slug' => Str::slug($config['judul']),
                'deskripsi' => $config['deskripsi'],
                'petunjuk' => $config['petunjuk'],
                'instrument_type' => $config['instrument_type'],
                'target_ketenagaan' => AssessmentKetenagaanType::TENAGA_KEPENDIDIKAN->value,
                'target_jabatan' => ['Pengawas'],
                'scoring_config' => $config['scoring_config'],
                'status' => 'publish',
                'is_active' => true,
            ]
        );

        $assessment->forms()->delete();

        foreach ($forms as $formData) {
            $fields = $formData['fields'];
            unset($formData['fields']);
            $form = $assessment->forms()->create($formData);

            foreach ($fields as $fieldData) {
                $form->fields()->create($fieldData);
            }
        }
    }

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
                    'label' => $value,
                ];

                continue;
            }

            if ($type === 'I') {
                if ($currentCompetency === null) {
                    throw new RuntimeException("Indikator muncul sebelum kompetensi: {$line}");
                }

                $currentIndicator = [
                    'kode' => $code,
                    'label' => $value,
                ];

                continue;
            }

            if ($type === 'F') {
                if ($currentCompetency === null || $currentIndicator === null) {
                    throw new RuntimeException("Subindikator muncul sebelum metadata lengkap: {$line}");
                }

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
                    throw new RuntimeException("Pertanyaan muncul sebelum subindikator: {$line}");
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

        $this->validateForms($forms);

        return array_map(fn (array $form, int $index) => [
            'judul_form' => $form['subindikator_kode'].' '.$form['subindikator_label'],
            'kode_form' => 'FORM-PENGAWAS-ANGKET-'.str_replace('.', '', $form['subindikator_kode']),
            'deskripsi' => $form['kompetensi_label'].' — '.$form['indikator_label'].'.',
            'kompetensi' => $form['kompetensi'],
            'indikator_kode' => $form['indikator_kode'],
            'indikator_label' => $form['indikator_label'],
            'is_scoreable' => true,
            'scoring_config' => $this->formScoringConfig($form),
            'urutan' => $index + 1,
            'is_active' => true,
            'fields' => array_map(
                fn (array $item, int $itemIndex) => [
                    'label' => $item['statement'],
                    'deskripsi' => $form['subindikator_label'],
                    'nama_field' => 'angket_'.$this->compactCode($form['subindikator_kode']).'_'.$item['position'],
                    'tipe_field' => LikertScale::FIELD_TYPE,
                    'placeholder' => null,
                    'bantuan' => 'Skala Likert: 1 = Sangat Tidak Setuju/Mampu/Menguasai; 2 = Tidak Setuju/Mampu/Menguasai; 3 = Cukup Setuju/Mampu/Menguasai; 4 = Setuju/Mampu/Menguasai; 5 = Sangat Setuju/Mampu/Menguasai.',
                    'opsi_field' => $this->likertOptions(),
                    'nilai_default' => null,
                    'validasi' => [
                        'required' => true,
                        'in' => ['1', '2', '3', '4', '5'],
                    ],
                    'scoring_config' => $this->fieldScoringConfig($form, $item),
                    'urutan' => $itemIndex + 1,
                    'is_required' => true,
                    'is_active' => true,
                ],
                $form['items'],
                array_keys($form['items'])
            ),
        ], $forms, array_keys($forms));
    }

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
                'source' => 'Instrumen Pemetaan Kompetensi Pengawas Sekolah BBGTK Sulawesi Selatan 2026',
                'method' => LikertScale::SCORING_METHOD,
                'positive_formula' => 'X',
                'negative_formula' => '6 - X',
                'aggregation' => 'Skor subindikator, indikator, dan kompetensi dihitung dari rata-rata skor Likert terkoreksi.',
                'competencies' => $this->competencySummary($forms),
            ],
        ];
    }

    private function formScoringConfig(array $form): array
    {
        $itemCount = count($form['items']);

        return [
            'profile' => AssessmentInstrumentType::SKALA_LIKERT->value,
            'weight' => $itemCount,
            'advanced_rules' => [
                'competency' => $form['kompetensi'],
                'competency_label' => $form['kompetensi_label'],
                'indicator_code' => $form['indikator_kode'],
                'indicator' => $form['indikator_label'],
                'sub_indicator_code' => $form['subindikator_kode'],
                'sub_indicator' => $form['subindikator_label'],
                'item_count' => $itemCount,
                'negative_statement_count' => collect($form['items'])->where('is_negative_statement', true)->count(),
                'minimum_score' => $itemCount * LikertScale::SCALE_MIN,
                'maximum_score' => $itemCount * LikertScale::SCALE_MAX,
                'form_formula' => 'Rata-rata skor Likert terkoreksi seluruh item pada subindikator.',
            ],
        ];
    }

    private function fieldScoringConfig(array $form, array $item): array
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
                'competency' => $form['kompetensi'],
                'indicator_code' => $form['indikator_kode'],
                'sub_indicator_code' => $form['subindikator_kode'],
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

    private function validateForms(array $forms): void
    {
        $formCount = count($forms);
        $itemCount = $this->itemCount($forms);
        $negativeItemCount = $this->negativeItemCount($forms);

        if ($formCount !== self::EXPECTED_FORM_COUNT) {
            throw new RuntimeException("Jumlah form angket tidak sesuai. Didapat {$formCount}, seharusnya ".self::EXPECTED_FORM_COUNT.'.');
        }

        if ($itemCount !== self::EXPECTED_ITEM_COUNT) {
            throw new RuntimeException("Jumlah butir angket tidak sesuai. Didapat {$itemCount}, seharusnya ".self::EXPECTED_ITEM_COUNT.'.');
        }

        if ($negativeItemCount !== self::EXPECTED_NEGATIVE_ITEM_COUNT) {
            throw new RuntimeException("Jumlah butir negatif tidak sesuai. Didapat {$negativeItemCount}, seharusnya ".self::EXPECTED_NEGATIVE_ITEM_COUNT.'.');
        }

        foreach ($forms as $form) {
            if (count($form['items']) !== 7) {
                throw new RuntimeException("Subindikator {$form['subindikator_kode']} harus berisi 7 butir Likert.");
            }
        }
    }

    private function itemCount(array $forms): int
    {
        return array_sum(array_map(
            fn (array $form): int => count($form['items'] ?? $form['fields'] ?? []),
            $forms
        ));
    }

    private function negativeItemCount(array $forms): int
    {
        return array_sum(array_map(
            function (array $form): int {
                $items = $form['items'] ?? $form['fields'] ?? [];

                return collect($items)->filter(fn (array $item): bool =>
                    (bool) ($item['is_negative_statement'] ?? data_get($item, 'scoring_config.is_negative_statement', false))
                )->count();
            },
            $forms
        ));
    }

    private function competencySummary(array $forms): array
    {
        $summary = [];

        foreach ($forms as $form) {
            $key = $form['kompetensi'];
            $summary[$key] ??= [
                'label' => data_get($form, 'scoring_config.advanced_rules.competency_label', $key),
                'form_count' => 0,
                'item_count' => 0,
                'negative_statement_count' => 0,
                'indicators' => [],
            ];
            $summary[$key]['form_count']++;
            $items = $form['items'] ?? $form['fields'] ?? [];
            $summary[$key]['item_count'] += count($items);
            $summary[$key]['negative_statement_count'] += collect($items)->filter(fn (array $item): bool =>
                (bool) ($item['is_negative_statement'] ?? data_get($item, 'scoring_config.is_negative_statement', false))
            )->count();
            $summary[$key]['indicators'][$form['indikator_kode']] = $form['indikator_label'];
        }

        return $summary;
    }

    private function compactCode(string $code): string
    {
        return str_replace('.', '', $code);
    }
}
