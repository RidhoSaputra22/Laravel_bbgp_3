@extends('layouts.landing.app')
@section('content')
    <div id="banner-area" class="banner-area"
        style="background-image:url({{ asset('landing/images/banner/bannerKontak.png') }})">
        <div class="banner-text">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="banner-heading">
                            <h1 class="banner-title">Alat Peraga Pembelajaran <br> </h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb justify-content-center">
                                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Alat Peraga</li>
                                </ol>
                            </nav>
                        </div>
                    </div><!-- Col end -->
                </div><!-- Row end -->
            </div><!-- Container end -->
        </div><!-- Banner text end -->
    </div><!-- Banner area end -->

    {{-- Section Alat Peraga Matematika --}}
    <section id="alat-peraga-matematika" class="main-container">
        <div class="container">
            <div class="row text-center">
                <div class="col-12">
                    <h2 class="section-title">Alat Peraga Matematika</h2>
                    <h3 class="section-sub-title">Koleksi Alat Peraga Berdasarkan Jenjang</h3>
                </div>
            </div>
            <!--/ Title row end -->

            <div class="row">
                {{-- SD --}}
                <div class="col-md-4">
                    <div class="alat-peraga-card">
                        <div class="alat-peraga-image">
                            <img src="{{ asset('landing/images/lab-virtual/mtk/sd.png') }}"
                                alt="Alat Peraga Matematika SD" class="img-fluid">
                            <div class="alat-peraga-overlay">
                                <i class="fas fa-calculator"></i>
                            </div>
                        </div>
                        <div class="alat-peraga-content">
                            <h4>Sekolah Dasar (SD)</h4>
                        </div>
                    </div>
                </div><!-- Col SD end -->

                {{-- SMP --}}
                <div class="col-md-4">
                    <div class="alat-peraga-card">
                        <div class="alat-peraga-image">
                            <img src="{{ asset('landing/images/lab-virtual/mtk/smp.png') }}"
                                alt="Alat Peraga Matematika SMP" class="img-fluid">
                            <div class="alat-peraga-overlay">
                                <i class="fas fa-calculator"></i>
                            </div>
                        </div>
                        <div class="alat-peraga-content">
                            <h4>Sekolah Menengah Pertama (SMP)</h4>
                        </div>
                    </div>
                </div><!-- Col SMP end -->

                {{-- SMA --}}
                <div class="col-md-4">
                    <div class="alat-peraga-card">
                        <div class="alat-peraga-image">
                            <img src="{{ asset('landing/images/lab-virtual/mtk/sma.png') }}"
                                alt="Alat Peraga Matematika SMA" class="img-fluid">
                            <div class="alat-peraga-overlay">
                                <i class="fas fa-calculator"></i>
                            </div>
                        </div>
                        <div class="alat-peraga-content">
                            <h4>Sekolah Menengah Atas (SMA)</h4>
                        </div>
                    </div>
                </div><!-- Col SMA end -->

            </div><!-- Row end -->

            <div class="gap-60"></div>

        </div><!-- Container end -->
    </section><!-- Section Matematika end -->

    {{-- Section Alat Peraga IPA --}}
    <section id="alat-peraga-ipa" class="main-container" style="background-color: #f9f9f9;">
        <div class="container">
            <div class="row text-center">
                <div class="col-12">
                    <h2 class="section-title">Alat Peraga IPA</h2>
                    <h3 class="section-sub-title">Koleksi Alat Peraga Berdasarkan Jenjang</h3>
                </div>
            </div>
            <!--/ Title row end -->

            <div class="row">
                {{-- SD --}}
                <div class="col-md-4">
                    <div class="alat-peraga-card">
                        <div class="alat-peraga-image">
                            <img src="{{ asset('landing/images/lab-virtual/ipa/sd.png') }}" alt="Alat Peraga IPA SD"
                                class="img-fluid">
                            <div class="alat-peraga-overlay">
                                <i class="fas fa-flask"></i>
                            </div>
                        </div>
                        <div class="alat-peraga-content">
                            <h4>Sekolah Dasar (SD)</h4>
                        </div>
                    </div>
                </div><!-- Col SD end -->

                {{-- SMP --}}
                <div class="col-md-4">
                    <div class="alat-peraga-card">
                        <div class="alat-peraga-image">
                            <img src="{{ asset('landing/images/lab-virtual/ipa/smp.png') }}" alt="Alat Peraga IPA SMP"
                                class="img-fluid">
                            <div class="alat-peraga-overlay">
                                <i class="fas fa-flask"></i>
                            </div>
                        </div>
                        <div class="alat-peraga-content">
                            <h4>Sekolah Menengah Pertama (SMP)</h4>
                        </div>
                    </div>
                </div><!-- Col SMP end -->

                {{-- SMA --}}
                <div class="col-md-4">
                    <div class="alat-peraga-card">
                        <div class="alat-peraga-image">
                            <img src="{{ asset('landing/images/lab-virtual/ipa/sma.png') }}" alt="Alat Peraga IPA SMA"
                                class="img-fluid">
                            <div class="alat-peraga-overlay">
                                <i class="fas fa-flask"></i>
                            </div>
                        </div>
                        <div class="alat-peraga-content">
                            <h4>Sekolah Menengah Atas (SMA)</h4>
                        </div>
                    </div>
                </div><!-- Col SMA end -->

            </div><!-- Row end -->

            <div class="gap-60"></div>

        </div><!-- Container end -->
    </section><!-- Section IPA end -->

    {{-- Custom CSS --}}
    <style>
        .alat-peraga-card {
            margin-bottom: 30px;
            transition: transform 0.3s ease;
            cursor: pointer;
        }

        .alat-peraga-card:hover {
            transform: translateY(-10px);
        }

        .alat-peraga-image {
            position: relative;
            overflow: hidden;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .alat-peraga-image img {
            width: 100%;
            height: 300px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .alat-peraga-card:hover .alat-peraga-image img {
            transform: scale(1.1);
        }

        .alat-peraga-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .alat-peraga-card:hover .alat-peraga-overlay {
            opacity: 1;
        }

        .alat-peraga-overlay i {
            font-size: 60px;
            color: #fff;
        }

        .alat-peraga-content {
            padding: 20px 10px;
            text-align: center;
        }

        .alat-peraga-content h4 {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin: 0;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .alat-peraga-image img {
                height: 250px;
            }

            .alat-peraga-content h4 {
                font-size: 16px;
            }
        }

        /* Section spacing */
        .gap-60 {
            height: 60px;
        }
    </style>
@endsection
