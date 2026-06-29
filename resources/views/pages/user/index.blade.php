@extends('layouts.user.app', ['title' => 'Bank Data BBGTK Sulawesi Selatan'])

@section('content')
    @push('styles')
    @endpush

    <div class="main-content ">
        <section class="section">
            <div class="section-header">
                <h1 class="text-primary"><u> Sejarah BBGTK Sulawesi Selatan</u> </h1>

            </div>

            <div class="section-body">
                {{-- <h2 class="section-title">This is Example Page</h2>
                <p class="section-lead">This page is just an example for you to create your own page.</p> --}}
                <div class="card">

                    <div class="card-body">
                        <div class="text-center">

                            <img src="https://BBGTKsulsel.kemdikbud.go.id/assets/berita/DSC02734.jpg" class="img-fluid m-3"
                                alt="">
                        </div>
                        <div class="text-justify">
                            <p>
                                Sejarah lahirnya Balai Besar Guru Penggerak (BBGTK) Sulawesi Selatan berasal dari Bidang
                                Pendidikan Masyarakat (Penmas) yang merupakan salah satu bidang di Kanwil Depdikbud Propinsi
                                Sulawesi Selatan. Bidang Penmas dipimpin oleh seorang kepala bidang (kepala bidang dikmas)
                                yang membawahi empat kelompok kerja yaitu:
                            </p>

                            <ol>
                                <li>Tenaga Teknis</li>
                                <li>Bina Program</li>
                                <li>Bina Sarjana</li>
                                <li>Supervisi Pelaporan, Evaluasi dan Monitoring (SPEM)</li>
                            </ol>

                            <p>
                                Di samping itu, Kepala Bidang Penmas mengelola Balai Pendidikan Masyarakat (Balai Penmas)
                                Sulawesi Selatan yang pada saat itu diserahi tugas melaksanaan proyek pendidikan non formal
                                (PNF).
                                <br>
                                Dengan terbitnya SK Menteri Pendidikan dan Kebudayaan, Nomor 022/O/1991 tanggal 20 Februari
                                1991, maka Balai Penmas beralih menjadi Balai Pengembangan Kegiatan Belajar (BPKB). BPKB
                                merupakan UPT Ditjen Diklusepora yang memiliki tugas melaksanakan pengembangan, bimbingan,
                                dan ujicoba program pendidikan luar sekolah, pemuda dan olahraga berdasarkan kebijakan
                                Ditjen Diklusepora. BPKB Sulawesi Selatan mewilayahi empat propinsi, yaitu; Sulawesi
                                Selatan, Sulawesi Utara, Sulawesi Tengah dan Sulawesi Tenggara.

                            </p>

                            <p>
                                Setelah enam tahun BPKB Sulawesi Selatan berkiprah melaksanakan tugas dan fungsinya, lahirlah SK Menteri Pendidikan Nasional, Nomor 115/O/2003 tanggal 31 Juli 2003 tentang Organisasi dan Tata Kerja BPPLSP, yang meningkatkan status BPKB Sulawesi Selatan menjadi Balai Pengembangan Pendidikan Luar Sekolah dan Pemuda (BPPLSP) Regional V, dengan delapan propinsi wilayah kerja yaitu: Propinsi Sulawesi Selatan, Sulawesi Utara, Sulawesi Tengah, Sulawesi Tenggara, Gorontalo, Maluku, Maluku Utara, dan Papua.
                            </p>

                            <p>
                                Tahun 2007 terbit peraturan Menteri Pendidikan Nasional yang mengatur tentang Tata Kerja dan Organisasi Balai Pengembangan Pendidikan Non Formal dan Informal (BPPNFI) dengan Nomor 28 Tahun 2007 tanggal 25 Juli 2007. Dengan terbitnya peraturan menteri tersebut maka Balai Pengembangan Pendidikan Luar Sekolah dan Pemuda (BPPLSP) Regional V berubah nomenklatur menjadi Balai Pengembangan Pendidikan Non Formal dan Informal (BPPNFI) Regional V. BPPNFI Regional V memiliki wilayah kerja yang terdiri atas enam propinsi yaitu: Sulawesi Selatan, Sulawesi Barat, Sulawesi Utara, Sulawesi Tenggara, dan Gorontalo.
                            </p>

                            <p>
                                Kemudian pada tahun 2012 BPPNFI berubah nomenklatur menjadi BP-PAUDNI. BPPNFI Regional V berganti nama menjadi BP-PAUDNI Regional III. Sesuai Peraturan Menteri Pendidikan dan Kebudayaan Nomor 17 Tahun 2012 tentang Organisasi dan Tata Kerja Balai Pengembangan Pendidikan Anak Usia Dini, Nonformal dan Informal (BP-PAUDNI), meliputi wilayah kerja yang sama sebelumnya yang terdiri atas enam propinsi yakni Provinsi Sulawesi Selatan, Provinsi Sulawesi Utara, Provinsi Gorontalo, Provinsi Sulawesi Tengah, Provinsi Sulawesi Tenggara, dan Provinsi Sulawesi Barat.
                            </p>

                            <p>
                                Setelah itu, Pada tahun 2015 kembali ditetapkan Peraturan Menteri Pendidikan dan Kebudayaan Nomor 69 Tahun 2015 tentang Organisasi dan Tata Kerja Balai Pengembangan Pendidikan Anak Usia Dini, dan Pendidikan Masyarakat, maka ditetapkan BP-PAUDNI berubah nomenklatur menjadi BP-PAUD dan Dikmas. BP-PAUDNI Regional III berganti nama menjadi BP-PAUD dan Dikmas Sulawesi Selatan. Namun masih meliputi wilayah kerja yang sama sebelumnya yang terdiri atas enam propinsi yakni Provinsi Sulawesi Selatan, Provinsi Sulawesi Utara, ProvinsiGorontalo, Provinsi Sulawesi Tengah, Provinsi Sulawesi Tenggara, dan Provinsi Sulawesi Barat.
                            </p>

                            <p>
                                Berselang 2 tahun kemudian yakni Tahun 2017, kembali ditetapkan Peraturan Menteri Pendidikan dan Kebudayaan Nomor 5 Tahun 2017 tentang Organisasi dan Tata Kerja Balai Pengembangan Pendidikan Anak Usia Dini, dan Pendidikan Masyarakat, terkait perubahan wilayah kerja dan ditetapkan hanya mencakup khusus Sulawesi Selatan. Bersamaan dengan itu, di beberapa propinsi di Sulawesi Selatan juga dibentuk BP PAUD dan Dikmas mencakup wilayah kerja masing-masing diantaranya: Sulawesi Tenggara, Sulawesi Barat, Sulawesi Tengah, Sulawesi Utara, dan Gorontalo.
                            </p>

                            <p>
                                Di Tahun 2022, Berdasarkan Permendikbud Ristek RI Nomor 14 tahun 2022 tentang Organisasi dan Tata Kerja Balai Besar Guru Penggerak dan Balai Guru Penggerak, terjadi perubahan nomenklatur terhadap Balai Pengembangan Pendidikan Anak Usia Dini dan Pendidikan Masyarakat (BP PAUD dan Dikmas) Provinsi Sulawesi Selatan, yang bertanggung jawab kepada Direktorat Jenderal Pendidikan Anak Usia Dini, Pendidikan Dasar, dan Pendidikan Menengah (Ditjen PAUD, Dikdas, dan Dikmen) beralih menjadi Balai Besar Guru Penggerak (BBGTK) Provinsi Sulawesi Selatan, yang bertanggung jawab kepada Direktorat Jenderal Guru dan Tenaga Kependidikan Kemendikbudristek.
                            </p>

                            <p>
                                BBGTK menjadi rumah yang baru serta tanggungjawabnya akan berbeda dengan sebelumnya. Namun posisi BBGTK tetap sama sebagai UPT atau Unit Pelaksana Teknis, yakni penghubung antara Kemedikbudristek di Jakarta dengan pemerintah daerah. 
                            </p>

                        </div>
                    </div>
                    {{-- <div class="card-footer bg-whitesmoke">
                        This is card footer
                    </div> --}}
                </div>
            </div>
        </section>
    </div>

    @push('scripts')
    @endpush
@endsection
