@extends('layouts.landing.app')
@section('content')
    <div id="banner-area" class="banner-area"
        style="background-image:url({{ asset('landing/images/banner/bannerKontak.png') }})">
        <div class="banner-text">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="banner-heading">
                            <h1 class="banner-title">Monitoring dan Evaluasi Kegiatan <br> </h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb justify-content-center">
                                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Monitoring dan Evaluasi Kegiatan</li>
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
                    <h2 class="section-title"></h2>
                    <h3 class="section-sub-title">Monitoring dan Evaluasi</h3>
                </div>
            </div>
            <!--/ Title row end -->

            <div class="row">
                <div class="col-md-4"></div>
                <div class="col-md-4">
                    <a href="https://docs.google.com/forms/d/e/1FAIpQLSeW-_wWr2FSwn1Sb3LSr3sjqchlLyeEHprc4iQtPuTS8fadTw/viewform?usp=dialog"
                        target="_blank">
                        <div class="ts-service-box-bg text-center h-100">
                            <span class="ts-service-icon icon-round">
                                <i class="fas fa-school"></i>
                            </span>
                            <div class="ts-service-box-content">
                                <h4>Monitoring dan Evaluasi Kegiatan</h4>
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
