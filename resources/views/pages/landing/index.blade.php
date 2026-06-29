@extends('layouts.landing.app')
@section('content')
    @push('styles')
        <style>
            /* Instagram Feed Styles */
            .instagram-card {
                background: white;
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                transition: all 0.3s ease;
                height: 100%;
                display: flex;
                flex-direction: column;
            }

            .instagram-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            }

            .instagram-header {
                padding: 15px;
                display: flex;
                align-items: center;
                gap: 10px;
                border-bottom: 1px solid #efefef;
            }

            .instagram-avatar {
                width: 40px;
                height: 40px;
                border-radius: 50%;
                border: 2px solid #e1306c;
            }

            .instagram-username {
                font-weight: 600;
                color: #262626;
                font-size: 14px;
            }

            .instagram-image {
                width: 100%;
                height: 300px;
                object-fit: cover;
            }

            .instagram-caption {
                padding: 15px;
                font-size: 14px;
                color: #262626;
                line-height: 1.6;
                flex-grow: 1;
            }

            .instagram-footer {
                padding: 15px;
                border-top: 1px solid #efefef;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .instagram-likes {
                color: #8e8e8e;
                font-size: 13px;
            }

            .instagram-date {
                color: #8e8e8e;
                font-size: 12px;
            }

            /* YouTube Video Styles */
            .youtube-card {
                background: white;
                border-radius: 12px;
                overflow: hidden;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                transition: all 0.3s ease;
                cursor: pointer;
            }

            .youtube-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 25px rgba(255, 0, 0, 0.2);
            }

            .youtube-thumbnail {
                position: relative;
                width: 100%;
                height: 200px;
                overflow: hidden;
            }

            .youtube-thumbnail img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                transition: transform 0.3s;
            }

            .youtube-card:hover .youtube-thumbnail img {
                transform: scale(1.05);
            }

            .youtube-play-btn {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: rgba(255, 0, 0, 0.9);
                width: 60px;
                height: 60px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 24px;
            }

            .youtube-duration {
                position: absolute;
                bottom: 10px;
                right: 10px;
                background: rgba(0, 0, 0, 0.8);
                color: white;
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 12px;
                font-weight: 600;
            }

            .youtube-content {
                padding: 15px;
            }

            .youtube-title {
                font-weight: 600;
                color: #030303;
                font-size: 15px;
                margin-bottom: 8px;
                display: -webkit-box;
                -webkit-line-clamp: 2;
                -webkit-box-orient: vertical;
                overflow: hidden;
            }

            .youtube-channel {
                color: #606060;
                font-size: 13px;
                margin-bottom: 5px;
            }

            .youtube-stats {
                display: flex;
                gap: 15px;
                color: #606060;
                font-size: 12px;
            }

            /* Category Tabs */
            .category-tabs {
                display: flex;
                gap: 15px;
                margin-bottom: 30px;
                justify-content: center;
                flex-wrap: wrap;
            }

            .category-tab {
                background: white;
                border: 2px solid #ddd;
                color: #666;
                padding: 10px 25px;
                border-radius: 25px;
                cursor: pointer;
                transition: all 0.3s;
                font-weight: 600;
            }

            .category-tab.active {
                background: #ff0000;
                border-color: #ff0000;
                color: white;
            }

            .category-tab:hover {
                transform: translateY(-2px);
            }

            /* Google Drive Styles */
            .gdrive-card {
                background: white;
                border-radius: 12px;
                padding: 25px;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                transition: all 0.3s ease;
                text-align: center;
                height: 100%;
            }

            .gdrive-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            }

            .gdrive-icon {
                font-size: 64px;
                margin-bottom: 20px;
                color: #4285f4;
            }

            .gdrive-title {
                font-size: 18px;
                font-weight: 700;
                color: #333;
                margin-bottom: 10px;
            }

            .gdrive-description {
                color: #666;
                font-size: 14px;
                margin-bottom: 20px;
                line-height: 1.6;
            }

            .gdrive-btn {
                background: linear-gradient(135deg, #4285f4 0%, #34a853 100%);
                color: white;
                padding: 12px 30px;
                border-radius: 25px;
                text-decoration: none;
                display: inline-block;
                font-weight: 600;
                transition: all 0.3s;
            }

            .gdrive-btn:hover {
                transform: scale(1.05);
                color: white;
                text-decoration: none;
                box-shadow: 0 4px 15px rgba(66, 133, 244, 0.4);
            }

            /* Section Divider */
            .section-divider {
                width: 60px;
                height: 4px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                margin: 0 auto 30px;
            }
        </style>
    @endpush
    <div class="banner-carousel banner-carousel-1 mb-0">
        <div class="banner-carousel-item" style="background-image:url({{ asset('landing/images/banner/bbgtk.jpg') }})">
            <div class="slider-content">
                <div class="container h-100">
                    <div class="row align-items-center h-100">
                        <div class="col-md-12 text-center">
                            <h2 class="slide-title" data-animation-in="slideInLeft">Selamat Datang di</h2>
                            <h3 class="slide-sub-title" data-animation-in="slideInRight">BBGTK Provinsi <br> Sulawesi Selatan
                            </h3>
                            <p data-animation-in="slideInLeft" data-duration-in="1.2">
                                {{-- <a href="services.html" class="slider btn btn-primary">Our Services</a>
                                <a href="contact.html" class="slider btn btn-primary border">Contact Now</a> --}}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="banner-carousel-item"
            style="background-image:url({{ asset('landing/images/slider-main/slide2.png') }})">
            <div class="slider-content text-left">
                <div class="container h-100">
                    <div class="row align-items-center h-100">
                        <div class="col-md-12">
                            <h2 class="slide-title-box" data-animation-in="slideInDown">Siap Melayani Anda</h2>
                            <h3 class="slide-title" data-animation-in="fadeIn">Dedikasi Kami untuk Guru</h3>
                            <h3 class="slide-sub-title" data-animation-in="slideInLeft">BBGTK Provinsi Sulawesi Selatan</h3>
                            <p data-animation-in="slideInRight">
                                {{-- <a href="services.html" class="slider btn btn-primary border">Pelayanan Kami</a> --}}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="banner-carousel-item"
            style="background-image:url({{ asset('landing/images/slider-main/slide4.png') }})">
            <div class="slider-content text-right">
                <div class="container h-100">
                    <div class="row align-items-center h-100">
                        <div class="col-md-12">
                            <h2 class="slide-title" data-animation-in="slideInDown">Temui Para Penggerak Kami</h2>
                            <h3 class="slide-sub-title" data-animation-in="fadeIn">Keberlanjutan dalam Pendidikan</h3>
                            <p class="slider-description lead" data-animation-in="slideInRight">
                                Kami akan mendukung Anda
                                dalam meraih kesuksesan melalui pendidikan yang berkelanjutan.
                            </p>
                            <div data-animation-in="slideInLeft">
                                <a href="{{ route('user.kontak') }}" class="slider btn btn-primary"
                                    aria-label="contact-with-us">Hubungi
                                    Kami</a>
                                {{-- <a href="about.html" class="slider btn btn-primary border"
                                    aria-label="learn-more-about-us">Learn more</a> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="call-to-action-box no-padding">
        <div class="container my-container">
            <div class="action-style-box">
                <ul class="info-box my-box-wrap">
                    <li class="single-info">
                        <div class="info-icon">
                            <i class="fab fa-whatsapp fa-lg"></i>
                        </div>
                        <div class="info-my-content">
                            <a href="">
                                <p>Unit Layanan Terpadu (ULT)</p>
                            </a>
                        </div>
                    </li>
                    <li class="single-info">
                        <div class="info-icon">
                            <i class="fas fa-award fa-lg"></i>
                        </div>
                        <div class="info-my-content">
                            <a href="">
                                <p>Standar Pelayanan</p>
                            </a>
                        </div>
                    </li>
                    <li class="single-info">
                        <div class="info-icon">
                            <i class="fas fa-users fa-lg"></i>
                        </div>
                        <div class="info-my-content">
                            <a href="{{ route('user.monitoring') }}">
                                <p>Monitoring dan Evaluasi Kegiatan</p>
                            </a>
                        </div>
                    </li>
                    <li class="single-info">
                        <div class="info-icon">
                            <i class="fas fa-paste fa-lg"></i>
                        </div>
                        <div class="info-my-content">
                            <a href="">
                                <p class="multi-line">Akuntabilitas Kinerja Instansi Pemerintah (AKIP)</p>
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </section>


    {{-- slider icon img --}}
    <section id="ts-service-area" class="ts-service-area pb-0">
        <div class="container">

            <div class="my-center-slider my-icon-slider">

                <div class=" ">
                    <div class="ts-service-box">
                        <div class="ts-service-image-wrapper">
                            <img loading="lazy" style="width: 250px" class="img img-fluid text-center mx-auto"
                                src="{{ asset('landing/images/icon-slider/logo-nogratifikasi.png') }}"
                                alt="logo nogratifikasi">
                        </div>
                    </div><!-- Service1 end -->
                </div><!-- Col 1 end -->
                <div class=" ">
                    <div class="ts-service-box">
                        <div class="ts-service-image-wrapper">
                            <img loading="lazy" style="width: 250px" class="img img-fluid text-center mx-auto"
                                src="{{ asset('landing/images/icon-slider/logo-berakhlak.png') }}" alt="logo berakhlak">
                        </div>
                    </div><!-- Service1 end -->
                </div><!-- Col 1 end -->
                <div class=" ">
                    <div class="ts-service-box">
                        <div class="ts-service-image-wrapper">
                            <img loading="lazy" style="width: 250px" class="img img-fluid text-center mx-auto"
                                src="{{ asset('landing/images/icon-slider/logo-bangga-melayani.png') }}"
                                alt="logo bangga melayani">
                        </div>
                    </div><!-- Service1 end -->
                </div><!-- Col 1 end -->
                <div class=" ">
                    <div class="ts-service-box">
                        <div class="ts-service-image-wrapper">
                            <img loading="lazy" style="width: 250px" class="img img-fluid text-center mx-auto"
                                src="{{ asset('landing/images/icon-slider/sehat-tanpa-korupsi.png') }}"
                                alt="logo sehat tanpa korupsi">
                        </div>
                    </div><!-- Service1 end -->
                </div><!-- Col 1 end -->
                <div class=" ">
                    <div class="ts-service-box">
                        <div class="ts-service-image-wrapper">
                            <img loading="lazy" style="width: 250px" class="img img-fluid text-center mx-auto"
                                src="{{ asset('landing/images/icon-slider/kami-siap-zi-wbk.png') }}"
                                alt="logo kami siap zi wbk">
                        </div>
                    </div><!-- Service1 end -->
                </div><!-- Col 1 end -->



            </div><!-- Main row end -->


        </div>
        <!--/ Container end -->
    </section><!-- Service end -->




    <section id="ts-features" class="ts-features">
        <div class="container">
            <div class="row">


                <div class="col-lg-6">
                    <div class="ts-intro">
                        <h2 class="into-title">Tentang Kami</h2>
                        <h3 class="into-sub-title">BBGTK Sulawesi Selatan</h3>
                        <p class="my-sub-content">
                            BBGTK Provinsi Sulawesi Selatan adalah Unit Pelaksana Teknis Direktorat Jenderal Guru dan Tenaga
                            Kependidikan Kemendikbudristek dalam Bidang Pengembangan dan Pemberdayaan Guru, Pendidik
                            lainnya, Tenaga Kependidikan, Calon Kepala Sekolah, Kepala Sekolah, Calon Pengawas Sekolah, dan
                            Pengawas Sekolah di Provinsi Sulawesi Selatan.
                        </p>
                    </div><!-- Intro box end -->



                </div><!-- Col end -->

                <div class="col-lg-6 mt-4 mt-lg-4 justify-content-center">
                    <h3 class="into-sub-title"> </h3>
                    <div class="box-video">

                        <!--<iframe width="420" height="315" title="Program Pengembangan keprofesian Guru. Pendidikan Jasmani, olahraga dan kesehatan" src="https://www.youtube.com/embed/gJ3g7xX9O-s"-->
                        <!--    allowfullscreen>-->
                        <!--</iframe>-->
                        <div class="video-placeholder" data-src="https://www.youtube.com/embed/tSsWpY7uwpA"
                            onclick="loadVideo(this)">
                            <div class="video-title">Balai Besar Guru dan Tenaga Kependidikan Sulawesi Selatan</div>
                        </div>
                        <!--<div class="video-title">Balai Besar Guru Penggerak</div>-->
                    </div>
                    <!--/ Accordion end -->
                </div><!-- Col end -->




            </div><!-- Row end -->
        </div><!-- Container end -->
    </section><!-- Feature are end -->

    <section id="ts-service-area" class="ts-service-area pb-0">
        <div class="container">

            <div class="row my-icon2-slider">

                <div class="col-lg col-md ">
                    <a href="{{ route('user.analisisPelatihan') }}" target="_blank">
                        <div class="ts-service-box">
                            <div class="ts-service-image-wrapper">
                                <img loading="lazy" class="w-100"
                                    src="{{ asset('landing/images/icon-slider/slider2/icon-web-jurnal.png') }}"
                                    alt="icon web jurnal">
                            </div>
                            <div class="text-center">
                                <div class="ts-service-info">
                                    <h3 class="service-box-title"><a
                                            href="{{ route('user.analisisPelatihan') }}">Analisis Kebutuhan
                                            Pelatihan</a></h3>

                                </div>
                            </div>
                        </div><!-- Service1 end -->
                    </a>
                </div><!-- Col 1 end -->

                <div class="col-lg col-md ">
                    <a href="{{ route('user.pengaduan') }}" target="_blank">
                        <div class="ts-service-box">
                            <div class="ts-service-image-wrapper">
                                <img loading="lazy" class="w-100"
                                    src="{{ asset('landing/images/icon-slider/slider2/icon-web-pengaduan.png') }}"
                                    alt="icon web pengaduan">
                            </div>
                            <div class="text-center">
                                <div class="ts-service-info">
                                    <h3 class="service-box-title"><a href="#">Standar pelayanan di lingukngan BGTK
                                            Sulsel</a></h3>

                                </div>
                            </div>
                        </div><!-- Service1 end -->
                    </a>
                </div><!-- Col 1 end -->

                <div class="col-lg col-md ">
                    <div class="ts-service-box">
                        <div class="ts-service-image-wrapper">
                            <img loading="lazy" class="w-100"
                                src="{{ asset('landing/images/icon-slider/slider2/icon-web-ppid.png') }}"
                                alt="icon web ppid">
                        </div>
                        <div class="text-center">
                            <div class="ts-service-info">
                                <h3 class="service-box-title"><a
                                        href="https://sites.google.com/instruktur.belajar.id/ult-bbgpsulsel">PPID</a></h3>

                            </div>
                        </div>
                    </div><!-- Service1 end -->
                </div><!-- Col 1 end -->

                <div class="col-lg col-md ">
                    <div class="ts-service-box">
                        <div class="ts-service-image-wrapper">
                            <img loading="lazy" class="w-100"
                                src="{{ asset('landing/images/icon-slider/slider2/icon-web-sim-penggiat.png') }}"
                                alt="icon web sim penggiat">
                        </div>
                        <div class="text-center">
                            <div class="ts-service-info">
                                <h3 class="service-box-title"><a href="#">SIM BBGTK Sul-Sel</a></h3>

                            </div>
                        </div>
                    </div><!-- Service1 end -->
                </div><!-- Col 1 end -->

                <div class="col-lg col-md ">
                    <a href="{{ route('user.lab-virtual') }}" target="_blank">
                        <div class="ts-service-box">
                            <div class="ts-service-image-wrapper">
                                <img loading="lazy" class="w-100"
                                    src="{{ asset('landing/images/icon-slider/slider2/icon-web-virtual-tour.png') }}"
                                    alt="icon web virtual tour">
                            </div>
                            <div class="text-center">
                                <div class="ts-service-info">
                                    {{-- <h3 class="service-box-title"><a href="#">Tur Virtual</a></h3> --}}
                                    <h3 class="service-box-title"><a href="#">Lab Virtual</a></h3>
    
                                </div>
                            </div>
                        </div><!-- Service1 end -->
                    </a>
                </div><!-- Col 1 end -->

                <div class="col-lg col-md ">
                    <a href="{{ route('user.analisisSLB') }}" target="_blank">
                        <div class="ts-service-box">
                            <div class="ts-service-image-wrapper">
                                <img loading="lazy" class="w-100"
                                    src="{{ asset('landing/images/icon-slider/slider2/icon-web-visualisasi-data.png') }}"
                                    alt="icon web Pendidik dan Tenaga Kependidikan SLB">
                            </div>
                            <div class="text-center">
                                <div class="ts-service-info">
                                    <h3 class="service-box-title"><a href="#">Pendidik dan Tenaga
                                            Kependidikan SLB</a></h3>

                                </div>
                            </div>
                        </div><!-- Service1 end -->
                    </a>
                </div><!-- Col 1 end -->

                <div class="col-lg col-md ">
                    <a href="{{ route('penyewaan.landing') }}" target="_blank">
                        <div class="ts-service-box">
                            <div class="ts-service-image-wrapper">
                                <img loading="lazy" class="w-100"
                                    src="{{ asset('landing/images/icon-slider/slider2/icon-sewa-fasilitas.png') }}"
                                    alt="Sewa Fasilitas">
                            </div>
                            <div class="text-center">
                                <div class="ts-service-info">
                                    <h3 class="service-box-title"><a href="#">Sewa Fasilitas</a></h3>

                                </div>
                            </div>
                        </div><!-- Service1 end -->
                    </a>
                </div><!-- Col 1 end -->

                <div class="col-lg col-md ">
                    <a href="{{ route('user.buletin-diksi') }}" target="_blank">
                        <div class="ts-service-box">
                            <div class="ts-service-image-wrapper">
                                <img loading="lazy" class="w-100"
                                    src="{{ asset('landing/images/icon-slider/slider2/buletin-diksi.png') }}"
                                    alt="Buletin Diksi">
                            </div>
                            <div class="text-center">
                                <div class="ts-service-info">
                                    <h3 class="service-box-title"><a href="#">Buletin Diksi</a></h3>

                                </div>
                            </div>
                        </div><!-- Service1 end -->
                    </a>
                </div><!-- Col 1 end -->


            </div><!-- Main row end -->


        </div>
        <!--/ Container end -->
    </section><!-- Service end -->

    <section id="ts-service-area" class="ts-service-area pb-0">
        <div class="container">
            <div class="row text-center">
                <div class="col-12">
                    <h2 class="section-title">BBGTK Sul-Sel</h2>
                    <h3 class="section-sub-title">Berita Terkini</h3>
                </div>
            </div>
            <!--/ Title row end -->
            @php
                use Illuminate\Support\Str;
            @endphp
            <div class="row my-posts-slider">
                @foreach ($datas['berita'] as $v)
                    <div class="col-lg-4 col-md-6 mb-5">
                        <div class="ts-service-box">
                            <div class="ts-service-image-wrapper">
                                <img loading="lazy" class="w-100" src="{{ asset('upload/berita/' . $v->thumbnail) }}"
                                    alt="thumbnail berita" title="{{ $v->thumbnail }}">
                            </div>
                            <div class="d-flex">
                                <div class="ts-service-info">
                                    <h3 class="service-box-title"><a
                                            href="{{ route('user.detail.post', ['jenis' => 'berita', 'id' => $v->id]) }}">{{ $v->judul }}</a>
                                    </h3>
                                    <p>
                                        {{ Str::limit(strip_tags($v->isi), 120, '...') }}
                                        {{-- {!! Str::limit($v->isi, 150, '...') !!} --}}
                                    </p>
                                    <a class="learn-more d-inline-block"
                                        href="{{ route('user.detail.post', ['jenis' => 'berita', 'id' => $v->id]) }}"
                                        aria-label="service-details"><i class="fa fa-caret-right"></i> Detail...</a>
                                </div>
                            </div>
                        </div><!-- Service1 end -->
                    </div><!-- Col 1 end -->
                @endforeach



            </div><!-- Main row end -->


        </div>
        <!--/ Container end -->
    </section><!-- Service end -->

    {{-- Instagram Posts Section --}}
    <section id="instagram-feed" class="news">
        <div class="container">
            <div class="row text-center">
                <div class="col-12">
                    <h2 class="section-title">BBGTK Sul-Sel</h2>
                    <h3 class="section-sub-title">Postingan Instagram Terbaru</h3>
                    <div class="section-divider"></div>
                </div>
            </div>

            <div class="row" id="instagram-container">
                <div class="col-12 text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-3">Memuat postingan Instagram...</p>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="https://www.instagram.com/bbgtksulsel/" target="_blank" class="btn btn-primary">
                    <i class="fab fa-instagram mr-2"></i>Lihat Semua di Instagram
                </a>
            </div>
        </div>
    </section>

    {{-- YouTube Videos Section --}}
    <section id="youtube-videos" class="ts-service-area pb-0" style="background: #f8f9fa;">
        <div class="container">
            <div class="row text-center">
                <div class="col-12">
                    <h2 class="section-title">BBGTK Sul-Sel</h2>
                    <h3 class="section-sub-title">Video Kegiatan dan Edukasi Terbaru</h3>
                    <div class="section-divider"></div>
                </div>
            </div>

            {{-- Category Tabs --}}
            <div class="category-tabs">
                {{-- <button class="category-tab active" data-category="all">Semua Video</button>
                <button class="category-tab" data-category="edukasi">Edukasi</button> --}}
                {{-- <button class="category-tab" data-category="webinar">Webinar</button> --}}
            </div>

            <div class="row" id="youtube-container">
                {{-- YouTube videos akan dimuat via JavaScript --}}
                <div class="col-12 text-center py-5">
                    <div class="spinner-border text-danger" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-3">Memuat video YouTube...</p>
                </div>
            </div>

            <div class="text-center mt-4 pb-4">
                <a href="https://www.youtube.com/@bbgtksulsel/" target="_blank" class="btn btn-danger">
                    <i class="fab fa-youtube mr-2"></i>Lihat Channel YouTube
                </a>
            </div>
        </div>
    </section>

    {{-- Video Pembelajaran Google Drive --}}
    <section id="video-pembelajaran" class="ts-service-area pb-0">
        <div class="container">
            <div class="row text-center">
                <div class="col-12">
                    <h2 class="section-title">BBGTK Sul-Sel</h2>
                    <h3 class="section-sub-title">Video Pembelajaran</h3>
                    <div class="section-divider"></div>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                    <div class="gdrive-card">
                        <div class="gdrive-icon" style="color: #ff6b6b;">
                            <i class="fas fa-baby"></i>
                        </div>
                        <h4 class="gdrive-title">PAUD</h4>
                        <p class="gdrive-description">
                            Video pembelajaran untuk jenjang Pendidikan Anak Usia Dini
                        </p>
                        <a href="https://drive.google.com/drive/folders/YOUR_FOLDER_PAUD" target="_blank"
                            class="gdrive-btn">
                            <i class="fab fa-google-drive mr-1"></i>Akses
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                    <div class="gdrive-card">
                        <div class="gdrive-icon" style="color: #4ecdc4;">
                            <i class="fas fa-school"></i>
                        </div>
                        <h4 class="gdrive-title">SD</h4>
                        <p class="gdrive-description">
                            Video pembelajaran untuk jenjang Sekolah Dasar
                        </p>
                        <a href="https://drive.google.com/drive/folders/YOUR_FOLDER_SD" target="_blank"
                            class="gdrive-btn mt-4">
                            <i class="fab fa-google-drive mr-1"></i>Akses
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                    <div class="gdrive-card">
                        <div class="gdrive-icon" style="color: #ffd93d;">
                            <i class="fas fa-book-reader"></i>
                        </div>
                        <h4 class="gdrive-title">SMP</h4>
                        <p class="gdrive-description">
                            Video pembelajaran untuk jenjang Sekolah Menengah Pertama
                        </p>
                        <a href="https://drive.google.com/drive/folders/YOUR_FOLDER_SMP" target="_blank"
                            class="gdrive-btn">
                            <i class="fab fa-google-drive mr-1"></i>Akses
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 col-sm-6 mb-4">
                    <div class="gdrive-card">
                        <div class="gdrive-icon" style="color: #6c5ce7;">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <h4 class="gdrive-title">SMA</h4>
                        <p class="gdrive-description">
                            Video pembelajaran untuk jenjang Sekolah Menengah Atas
                        </p>
                        <a href="https://drive.google.com/drive/folders/YOUR_FOLDER_SMA" target="_blank"
                            class="gdrive-btn">
                            <i class="fab fa-google-drive mr-1"></i>Akses
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>


    {{-- Buku Digital Google Drive --}}
    <section id="buku-digital" class="ts-service-area pb-0" style="background: #f8f9fa;">
        <div class="container">
            <div class="row text-center">
                <div class="col-12">
                    <h2 class="section-title">BBGTK Sul-Sel</h2>
                    <h3 class="section-sub-title">Buku Digital</h3>
                    <div class="section-divider"></div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="gdrive-card">
                        <div class="gdrive-icon" style="color: #ff6b6b;">
                            <i class="fas fa-baby"></i>
                        </div>
                        <h4 class="gdrive-title">PAUD</h4>
                        <p class="gdrive-description">
                            Modul lengkap untuk jenjang Pendidikan Anak Usia Dini
                        </p>
                        <a href="https://drive.google.com/drive/folders/YOUR_FOLDER_ID_4" target="_blank"
                            class="gdrive-btn">
                            <i class="fas fa-download mr-2"></i>Download
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="gdrive-card">
                        <div class="gdrive-icon" style="color: #4ecdc4;">
                            <i class="fas fa-school"></i>
                        </div>
                        <h4 class="gdrive-title">SD</h4>
                        <p class="gdrive-description">
                            Modul pembelajaran untuk jenjang Sekolah Dasar
                        </p>
                        <a href="https://drive.google.com/drive/folders/YOUR_FOLDER_ID_5" target="_blank"
                            class="gdrive-btn">
                            <i class="fas fa-download mr-2"></i>Download
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="gdrive-card">
                        <div class="gdrive-icon" style="color: #ffd93d;">
                            <i class="fas fa-book-reader"></i>
                        </div>
                        <h4 class="gdrive-title">SMP</h4>
                        <p class="gdrive-description">
                            Modul pembelajaran untuk jenjang Sekolah Menengah Pertama
                        </p>
                        <a href="https://drive.google.com/drive/folders/YOUR_FOLDER_ID_6" target="_blank"
                            class="gdrive-btn">
                            <i class="fas fa-download mr-2"></i>Download
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="gdrive-card">
                        <div class="gdrive-icon" style="color: #6c5ce7;">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <h4 class="gdrive-title">SMA</h4>
                        <p class="gdrive-description">
                            Modul pembelajaran untuk jenjang Sekolah Menengah Atas
                        </p>
                        <a href="https://drive.google.com/drive/folders/YOUR_FOLDER_ID_7" target="_blank"
                            class="gdrive-btn">
                            <i class="fas fa-download mr-2"></i>Download
                        </a>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="gdrive-card">
                        <div class="gdrive-icon" style="color: #5171ff;">
                            <i class="fas fa-child"></i>
                        </div>
                        <h4 class="gdrive-title">SLB</h4>
                        <p class="gdrive-description">
                            Modul pembelajaran untuk jenjang Sekolah Luar Biasa
                        </p>
                        <a href="https://drive.google.com/drive/folders/YOUR_FOLDER_ID_7" target="_blank"
                            class="gdrive-btn">
                            <i class="fas fa-download mr-2"></i>Download
                        </a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <div class="gdrive-card">
                        <div class="gdrive-icon" style="color: #ff4242;">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <h4 class="gdrive-title">Modul BK</h4>
                        <p class="gdrive-description">
                            Modul Pembelajaran 7 Jurus BK Hebat
                        </p>
                        <a href="https://drive.google.com/drive/folders/1DYxA5Jy25JzXl5huNh7YJpDK_1-RQNpI" target="_blank"
                            class="gdrive-btn">
                            <i class="fas fa-download mr-2"></i>Download
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>


    {{-- <section id="ts-service-area" class="ts-service-area pb-0">
        <div class="container">
            <div class="row text-center">
                <div class="col-12">
                    <h2 class="section-title">BBGTK Sul-Sel</h2>
                    <h3 class="section-sub-title">Artikel Terkini</h3>
                </div>
            </div>

            <div class="row my-artikel-slider">
                @foreach ($datas['artikel'] as $v)
                    <div class="col-lg-4 col-md-6 mb-5">
                        <div class="ts-service-box">
                            <div class="ts-service-image-wrapper">
                                <img loading="lazy" class="w-100" src="{{ asset('upload/artikel/' . $v->thumbnail) }}"
                                    alt="thumbnail artikel" title="{{ $v->thumbnail }}">
                            </div>
                            <div class="d-flex">
                                <div class="ts-service-info">
                                    <h3 class="service-box-title"><a
                                            href="{{ route('user.detail.post', ['jenis' => 'artikel', 'id' => $v->id]) }}">{{ $v->judul }}</a>
                                    </h3>
                                    <p>
                                        {{ Str::limit(strip_tags($v->isi), 120, '...') }}
                                    </p>
                                    <a class="learn-more d-inline-block"
                                        href="{{ route('user.detail.post', ['jenis' => 'artikel', 'id' => $v->id]) }}"
                                        aria-label="service-details"><i class="fa fa-caret-right"></i> Learn more</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach


            </div>


        </div>
    </section> --}}

    {{-- <section id="news" class="news">
        <div class="container">
            <div class="row text-center">
                <div class="col-12">
                    <h2 class="section-title">BBGTK Sul-Sel</h2>
                    <h3 class="section-sub-title">Agenda Terkini</h3>
                </div>
            </div>

            <div class="row my-posts-slider">
                @foreach ($datas['agenda'] as $v)
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="latest-post">
                            <div class="latest-post-media">
                                <a href="{{ route('user.detail.post', ['jenis' => 'agenda', 'id' => $v->id]) }}"
                                    class="latest-post-img">
                                    <img loading="lazy" class="img-fluid"
                                        src="{{ asset('upload/agenda/' . $v->thumbnail) }}" alt="thumbnail agenda"
                                        title="{{ $v->thumbnail }}">
                                </a>
                            </div>
                            <div class="post-body">
                                <h4 class="post-title">
                                    <a href="{{ route('user.detail.post', ['jenis' => 'agenda', 'id' => $v->id]) }}"
                                        class="d-inline-block">{{ $v->nama_kegiatan }}</a>
                                </h4>
                                <div class="latest-post-meta">
                                    <span class="post-item-date">
                                        <?php
                                        setlocale(LC_ALL, 'IND');
                                        
                                        $tgl_kegiatan = strftime('%d %B %Y', strtotime($v->tgl_kegiatan));
                                        ?>
                                        <i class="fa fa-clock-o"></i> {{ $tgl_kegiatan }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach


            </div>

        </div>
    </section> --}}

    @push('scripts')
        <script>
            function loadVideo(element) {
                var iframe = document.createElement('iframe');
                iframe.setAttribute('width', '420');
                iframe.setAttribute('height', '315');
                iframe.setAttribute('title',
                    'Program Pengembangan keprofesian Guru. Pendidikan Jasmani, olahraga dan kesehatan');
                iframe.setAttribute('src', element.getAttribute('data-src'));
                iframe.setAttribute('allowfullscreen', '');
                element.parentNode.replaceChild(iframe, element);
            }

            // Optionally, you can use Intersection Observer to load video only when in viewport
            document.addEventListener('DOMContentLoaded', function() {
                var lazyVideos = [].slice.call(document.querySelectorAll('.video-placeholder'));

                if ('IntersectionObserver' in window) {
                    var lazyVideoObserver = new IntersectionObserver(function(entries, observer) {
                        entries.forEach(function(video) {
                            if (video.isIntersecting) {
                                loadVideo(video.target);
                                lazyVideoObserver.unobserve(video.target);
                            }
                        });
                    });

                    lazyVideos.forEach(function(video) {
                        lazyVideoObserver.observe(video);
                    });
                } else {
                    // Fallback for older browsers
                    lazyVideos.forEach(function(video) {
                        loadVideo(video);
                    });
                }
            });

            function loadInstagramPosts() {
                // OPSI 1: Menggunakan Instagram Basic Display API
                // const accessToken = 'YOUR_INSTAGRAM_ACCESS_TOKEN';
                // const userId = 'YOUR_INSTAGRAM_USER_ID';
                // const instagramAPI =
                //     `https://graph.instagram.com/${userId}/media?fields=id,caption,media_type,media_url,permalink,timestamp&access_token=${accessToken}`;

                // $.ajax({
                //     url: instagramAPI,
                //     method: 'GET',
                //     success: function(response) {
                //         displayInstagramPosts(response.data);
                //     },
                //     error: function(error) {
                //         console.error('Instagram API Error:', error);
                //         $('#instagram-container').html(`
                //             <div class="col-12 text-center">
                //                 <p class="text-muted">Tidak dapat memuat postingan Instagram. Silakan coba lagi nanti.</p>
                //             </div>
                //         `);
                //     }
                // });
            }

            function displayInstagramPosts(posts) {
                const container = $('#instagram-container');
                container.empty();

                // Tampilkan 6 post terbaru
                posts.slice(0, 6).forEach(post => {
                    if (post.media_type === 'IMAGE' || post.media_type === 'CAROUSEL_ALBUM') {
                        const postHTML = `
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="instagram-card">
                                    <div class="instagram-header">
                                        <img src="${'{{ asset('landing/images/fav.png') }}'}" class="instagram-avatar" alt="BBGTK">
                                        <div class="instagram-username">@bbgtkprov.sulsel</div>
                                    </div>
                                    <img src="${post.media_url}" class="instagram-image" alt="Instagram Post">
                                    <div class="instagram-caption">
                                        ${post.caption ? post.caption.substring(0, 100) + '...' : ''}
                                    </div>
                                    <div class="instagram-footer">
                                        <div class="instagram-likes">
                                            <i class="far fa-heart"></i> Lihat di Instagram
                                        </div>
                                        <a href="${post.permalink}" target="_blank" class="btn btn-sm btn-outline-primary">
                                            Buka <i class="fas fa-external-link-alt ml-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        `;
                        container.append(postHTML);
                    }
                });
            }

            // ==================== YOUTUBE VIDEOS ====================
            function loadYouTubeVideos(category = 'all') {

                const API_KEY = '{{ $datas['api_key'] }}';
                const CHANNEL_ID = '{{ $datas['channel_id'] }}';

                let playlistId;
                if (category === 'edukasi') {
                    playlistId = 'YOUR_TUTORIAL_PLAYLIST_ID';
                } else if (category === 'webinar') {
                    playlistId = 'YOUR_WEBINAR_PLAYLIST_ID';
                } else {
                    // All videos from channel
                    playlistId = `UU${CHANNEL_ID.substring(2)}`; // Convert channel ID to uploads playlist
                }

                const youtubeAPI =
                    `https://youtube.googleapis.com/youtube/v3/search?key=${API_KEY}&part=snippet&channelId=${CHANNEL_ID}&type=video&maxResults=3&order=date`;

                $.ajax({
                    url: youtubeAPI,
                    method: 'GET',
                    success: function(response) {
                        displayYouTubeVideos(response.items);
                    },
                    error: function(error) {
                        console.error('YouTube API Error:', error);
                        $('#youtube-container').html(`
                            <div class="col-12 text-center">
                                <p class="text-muted">Tidak dapat memuat video YouTube. Silakan coba lagi nanti.</p>
                            </div>
                        `);
                    }
                });
            }

            function displayYouTubeVideos(videos) {
                const container = $('#youtube-container');
                container.empty();
                videos.forEach(video => {
                    const snippet = video.snippet;
                    const videoId = video.id.videoId;
                    const videoHTML = `
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="youtube-card" onclick="window.open('https://www.youtube.com/watch?v=${videoId}', '_blank')">
                                <div class="youtube-thumbnail">
                                    <img src="${snippet.thumbnails.medium.url}" alt="${snippet.title}">
                                    <div class="youtube-play-btn">
                                        <i class="fab fa-youtube"></i>
                                    </div>
                                </div>
                                <div class="youtube-content">
                                    <h4 class="youtube-title">${snippet.title}</h4>
                                    <div class="youtube-channel">BBGTK Provinsi Sulsel</div>
                                    <div class="youtube-stats">
                                        <span><i class="far fa-clock"></i> ${formatDate(snippet.publishedAt)}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    container.append(videoHTML);
                });
            }

            function formatDate(dateString) {
                const date = new Date(dateString);
                const now = new Date();
                const diff = Math.floor((now - date) / (1000 * 60 * 60 * 24));

                if (diff === 0) return 'Hari ini';
                if (diff === 1) return 'Kemarin';
                if (diff < 7) return diff + ' hari lalu';
                if (diff < 30) return Math.floor(diff / 7) + ' minggu lalu';
                return Math.floor(diff / 30) + ' bulan lalu';
            }

            // Category tabs handler
            $('.category-tab').on('click', function() {
                $('.category-tab').removeClass('active');
                $(this).addClass('active');

                const category = $(this).data('category');
                loadYouTubeVideos(category);
            });

            // Load on page ready
            $(document).ready(function() {
                const container = $('#youtube-container');
                container.empty()
                // Uncomment when API keys are configured
                loadInstagramPosts();
                loadYouTubeVideos();

                // Temporary demo data
                setTimeout(() => {
                    // $('#instagram-container').html(
                    //     '<div class="col-12 text-center"><p class="text-muted">Konfigurasi Instagram API untuk menampilkan postingan</p></div>'
                    // );
                    // $('#youtube-container').html(
                    //     '<div class="col-12 text-center"><p class="text-muted">Konfigurasi YouTube API untuk menampilkan video</p></div>'
                    // );
                }, 1000);
            });
        </script>
    @endpush
@endsection
