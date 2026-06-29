@extends('layouts.landing.app')
@section('content')
    <div id="banner-area" class="banner-area"
        style="background-image:url({{ asset('landing/images/banner/bannerKontak.png') }})">
        <div class="banner-text">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="banner-heading">
                            <h1 class="banner-title">Buletin Diksi</h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb justify-content-center">
                                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Buletin Diksi</li>
                                </ol>
                            </nav>
                        </div>
                    </div><!-- Col end -->
                </div><!-- Row end -->
            </div><!-- Container end -->
        </div><!-- Banner text end -->
    </div><!-- Banner area end -->

    <section id="main-container" class="main-container">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h2 class="section-title text-center">Buletin Diksi</h2>
                    <h3 class="section-sub-title text-center mb-5">Informasi dan Publikasi Terbaru</h3>
                </div>
            </div>

            <!-- Card Buletin -->
            <div class="row mb-5">
                <div class="col-lg-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-4">
                            <div class="row">
                                <!-- Bagian Kiri: Cover dan Button Unduh -->
                                <div class="col-md-4 text-center mb-4 mb-md-0">
                                    <div class="mb-3">
                                        <img src="{{ asset('landing/images/pengaduan/1.webp') }}"
                                            alt="Cover Buletin Diksi" class="img-fluid rounded shadow-sm"
                                            style="max-height: 350px;">
                                    </div>
                                    <a href="{{ asset('landing/files/buletin-diksi.pdf') }}" class="btn btn-primary btn-lg"
                                        download>
                                        <i class="fas fa-download me-2"></i>Unduh Buletin
                                    </a>
                                </div>

                                <!-- Bagian Kanan: Informasi Buletin -->
                                <div class="col-md-8">
                                    <h3 class="text-primary mb-3">Strategi Pembelajaran Inovatif di Era Digital (Sebagai Contoh saja)</h3>
                                    <div class="buletin-info mb-4">
                                        <div class="row">
                                            <div class="col-sm-6 mb-2">
                                                <strong><i
                                                        class="fas fa-user-edit me-2 text-secondary"></i>Penerbit:</strong>
                                                <span class="ms-2">Diksi Media</span>
                                            </div>
                                            <div class="col-sm-6 mb-2">
                                                <strong><i class="fas fa-calendar-alt me-2 text-secondary"></i>Tahun
                                                    Terbit:</strong>
                                                <span class="ms-2">2023</span>
                                            </div>
                                            <div class="col-sm-6 mb-2">
                                                <strong><i class="fas fa-book me-2 text-secondary"></i>Edisi:</strong>
                                                <span class="ms-2">Vol. 5 No. 2</span>
                                            </div>
                                            <div class="col-sm-6 mb-2">
                                                <strong><i class="fas fa-language me-2 text-secondary"></i>Bahasa:</strong>
                                                <span class="ms-2">Indonesia</span>
                                            </div>
                                            <div class="col-sm-6 mb-2">
                                                <strong><i class="fas fa-file-pdf me-2 text-secondary"></i>Format:</strong>
                                                <span class="ms-2">PDF (5.2 MB)</span>
                                            </div>
                                            <div class="col-sm-6 mb-2">
                                                <strong><i class="fas fa-file-alt me-2 text-secondary"></i>Halaman:</strong>
                                                <span class="ms-2">48 Halaman</span>
                                            </div>
                                        </div>
                                    </div>

                                    <h5 class="text-dark mb-2">Deskripsi:</h5>
                                    <p class="text-muted">
                                        Buletin Diksi edisi kali ini membahas strategi pembelajaran inovatif yang dapat
                                        diterapkan
                                        di era digital. Berisi berbagai artikel menarik dari para praktisi pendidikan,
                                        penelitian
                                        terbaru, tips pengembangan kurikulum, dan contoh praktik baik dalam pembelajaran.
                                        Buletin ini menjadi referensi penting bagi para pendidik yang ingin mengembangkan
                                        kompetensi di bidang pendidikan modern.
                                    </p>

                                    <h5 class="text-dark mb-2 mt-4">Daftar Isi:</h5>
                                    <ul class="list-unstyled text-muted">
                                        <li><i class="fas fa-check-circle text-success me-2"></i>Pendahuluan: Pendidikan di
                                            Era Digital</li>
                                        <li><i class="fas fa-check-circle text-success me-2"></i>Strategi Pembelajaran
                                            Berbasis Teknologi</li>
                                        <li><i class="fas fa-check-circle text-success me-2"></i>Media Pembelajaran
                                            Interaktif</li>
                                        <li><i class="fas fa-check-circle text-success me-2"></i>Studi Kasus: Implementasi
                                            di Sekolah</li>
                                        <li><i class="fas fa-check-circle text-success me-2"></i>Tips Pengembangan Kurikulum
                                            Digital</li>
                                        <li><i class="fas fa-check-circle text-success me-2"></i>Wawancara dengan Pakar
                                            Pendidikan</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PDF Reader Online -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0"><i class="fas fa-book-reader me-2"></i>Baca Buletin Online</h4>
                        </div>
                        <div class="card-body p-0">
                            <div class="embed-responsive embed-responsive-16by9">
                                <iframe src="{{ asset('landing/files/buletin-diksi.pdf') }}#view=fitH"
                                    class="embed-responsive-item w-100" style="height: 600px;" frameborder="0">
                                    Browser Anda tidak mendukung pembaca PDF inline.
                                    <a href="{{ asset('landing/files/buletin-diksi.pdf') }}">Unduh buletin</a> untuk
                                    membacanya.
                                </iframe>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informasi Tambahan -->
            <div class="row mt-5">
                <div class="col-12">
                    <div class="alert alert-info" role="alert">
                        <h5 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Informasi</h5>
                        <p class="mb-0">
                            Jika mengalami masalah dalam melihat buletin secara online, silakan unduh terlebih dahulu
                            menggunakan tombol "Unduh Buletin" di atas, kemudian buka file tersebut dengan aplikasi PDF
                            reader di perangkat Anda.
                        </p>
                    </div>
                </div>
            </div>
        </div><!-- Container end -->
    </section><!-- Main container end -->
@endsection

@push('styles')
    <style>
        .buletin-info strong {
            color: #495057;
        }

        .embed-responsive {
            position: relative;
            display: block;
            width: 100%;
            padding: 0;
            overflow: hidden;
        }

        .embed-responsive-16by9::before {
            padding-top: 56.25%;
        }

        .embed-responsive::before {
            display: block;
            content: "";
        }

        .embed-responsive .embed-responsive-item,
        .embed-responsive iframe,
        .embed-responsive embed,
        .embed-responsive object,
        .embed-responsive video {
            position: absolute;
            top: 0;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 0;
        }

        .card {
            transition: transform 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
        }
    </style>
@endpush
