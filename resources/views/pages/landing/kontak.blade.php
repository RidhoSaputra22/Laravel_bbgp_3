@extends('layouts.landing.app')
@section('content')
    <div id="banner-area" class="banner-area" style="background-image:url({{ asset('landing/images/banner/bannerKontak.png')}})">
        <div class="banner-text">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="banner-heading">
                            <h1 class="banner-title">Kontak</h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb justify-content-center">
                                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Hubungi Kami</li>
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
                    <h2 class="section-title">Kunjungi Kami</h2>
                    <h3 class="section-sub-title">Temukan Lokasi</h3>
                </div>
            </div>
            <!--/ Title row end -->

            <div class="row">
                <div class="col-md-4">
                    <div class="ts-service-box-bg text-center h-100">
                        <span class="ts-service-icon icon-round">
                            <i class="fas fa-map-marker-alt mr-0"></i>
                        </span>
                        <div class="ts-service-box-content">
                            <h4>Kunjungi Kantor Kami</h4>
                            <p>90231 Jl. Adiyaksa No.2, Pandang, Kec. Panakkukang, Kota Makassar, Sulawesi
                              Selatan</p>
                        </div>
                    </div>
                </div><!-- Col 1 end -->

                <div class="col-md-4">
                    <div class="ts-service-box-bg text-center h-100">
                        <span class="ts-service-icon icon-round">
                            <i class="fa fa-envelope mr-0"></i>
                        </span>
                        <div class="ts-service-box-content">
                            <h4>Email Kami</h4>
                            <p>bbgtksulsel@kemdikbud.go.id </p>
                        </div>
                    </div>
                </div><!-- Col 2 end -->

                <div class="col-md-4">
                    <div class="ts-service-box-bg text-center h-100">
                        <span class="ts-service-icon icon-round">
                            <i class="fa fa-phone-square mr-0"></i>
                        </span>
                        <div class="ts-service-box-content">
                            <h4>Hubungi Kami</h4>
                            <p>(0411) 440065, 421460 </p>
                        </div>
                    </div>
                </div><!-- Col 3 end -->

            </div><!-- 1st row end -->

            <div class="gap-60"></div>

            <div class="google-map">
              <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3973.6360074660843!2d119.444923!3d-5.162121999999999!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dbee30920b03ac9%3A0xa738c1f4d75fc525!2sBBGP%20Sulawesi%20Selatan!5e0!3m2!1sid!2sid!4v1719594902720!5m2!1sid!2sid" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>

            </div>

            <div class="gap-40"></div>

        </div><!-- Conatiner end -->
    </section><!-- Main container end -->
@endsection
