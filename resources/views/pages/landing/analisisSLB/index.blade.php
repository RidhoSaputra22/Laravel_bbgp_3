@extends('layouts.landing.app')
@section('content')
    <div id="banner-area" class="banner-area"
        style="background-image:url({{ asset('landing/images/banner/bannerKontak.png') }})">
        <div class="banner-text">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="banner-heading">
                            <h1 class="banner-title">Analisis Kebutuhan Pelatihan <br> </h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb justify-content-center">
                                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Analisis Kebutuhan Pelatihan</li>
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

            <div class="row text-center">
                <div class="col-12">
                    <h2 class="section-title">Silahkan pilih kebutuhan analisis</h2>
                    <h3 class="section-sub-title">Kebutuhan Analisis Pelatihan</h3>
                </div>
            </div>
            <!--/ Title row end -->

            <div class="row">
                <div class="col-md-4"></div>
                <div class="col-md-4">
                    <a href="https://docs.google.com/forms/d/e/1FAIpQLSdajA0snJDnkHHhn-rwHX8yZa_FQFolPpCga4YYmB7697EXVg/viewform?usp=header"
                        target="_blank">
                        <div class="ts-service-box-bg text-center h-100">
                            <span class="ts-service-icon icon-round">
                                <i class="fas fa-school"></i>
                            </span>
                            <div class="ts-service-box-content">
                                <h4>Pemetaan Kompetensi Guru Sekolah Luar Biasa (SLB) Tahun 2025</h4>
                            </div>
                        </div>
                    </a>
                </div><!-- Col 1 end -->
                <div class="col-md-4"></div>
            </div><!-- 1st row end -->



            <div class="gap-40"></div>

        </div><!-- Conatiner end -->
    </section><!-- Main container end -->
@endsection
