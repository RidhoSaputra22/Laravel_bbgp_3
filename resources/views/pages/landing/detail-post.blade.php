@extends('layouts.landing.app')
@section('content')
    <div id="banner-area" class="banner-area"
        style="background-image:url({{ asset('landing/images/banner/bannerKontak.png') }})">
        <div class="banner-text">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="banner-heading">
                            <h1 class="banner-title">{{ $jenis }} </h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb justify-content-center">
                                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                                    <li class="breadcrumb-item"><a href="/">{{ $jenis }}</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Detail {{ $jenis }}</li>
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

                <div class="col-lg-8 mb-5 mb-lg-0">

                    <div class="post-content post-single">
                        <div class="post-media post-image">
                            <img loading="lazy" src="{{ asset('upload/' . $jenis . '/' . $data->thumbnail) }}"
                                class="img-fluid" alt="Thumbnail Post" title="{{ $data->thumbnail }}">
                        </div>

                        <div class="post-body">
                            <div class="entry-header">
                                <div class="post-meta">
                                    <span class="post-author">
                                        <i class="far fa-user"></i><a href="#"> {{ $data->author }}</a>
                                    </span>
                                    <span class="post-cat">
                                        <i class="far fa-folder-open"></i><a href="#"> {{ strtoupper($jenis) }} </a>
                                    </span>
                                    <?php
                                    setlocale(LC_ALL, 'IND');
                                    
                                    $tgl_publish = strftime('%B, %d %Y', strtotime($data->tgl_publish));
                                    ?>
                                    <span class="post-meta-date"><i class="far fa-calendar"></i>{{ $tgl_publish }}</span>
                                    {{-- <span class="post-comment"><i class="far fa-comment"></i> 03<a href="#"
                                            class="comments-link">Comments</a></span> --}}
                                </div>
                                <h2 class="entry-title">
                                    {{ $data->judul }}
                                </h2>
                            </div><!-- header end -->

                            <div class="entry-content">
                                {!! $data->isi !!}
                            </div>

                            <div class="tags-area d-flex align-items-center justify-content-between">
                                <div class="post-tags">
                                    <a href="#">{{ $jenis }}</a>
                                    {{-- <a href="#">Safety</a>
                                    <a href="#">Planning</a> --}}
                                </div>
                                <div class="share-items">
                                    <ul class="post-social-icons list-unstyled">
                                        <li class="social-icons-head">Share:</li>
                                        <li><a href="#"><i class="fab fa-facebook-f"></i></a></li>
                                        <li><a href="#"><i class="fab fa-twitter"></i></a></li>
                                        <li><a href="#"><i class="fab fa-google-plus"></i></a></li>
                                        <li><a href="#"><i class="fab fa-linkedin"></i></a></li>
                                    </ul>
                                </div>
                            </div>

                        </div><!-- post-body end -->
                    </div><!-- post content end -->

                    {{-- <div class="author-box d-nlock d-sm-flex">
                        <div class="author-img mb-4 mb-md-0">
                            <img loading="lazy" src="images/news/avator1.png" alt="author">
                        </div>
                        <div class="author-info">
                            <h3>Elton Themen<span>Site Engineer</span></h3>
                            <p class="mb-2">Lisicing elit, sed do eiusmod tempor ut labore et dolore magna aliqua. Ut enim
                                ad vene minim
                                veniam, quis nostrud exercitation nisi ex ea commodo.</p>
                            <p class="author-url mb-0">Website: <span><a href="#">http://www.example.com</a></span>
                            </p>

                        </div>
                    </div> <!-- Author box end --> --}}


                </div><!-- Content Col end -->

                <div class="col-lg-4">

                    <div class="sidebar sidebar-right">
                        <div class="widget recent-posts">
                            <h3 class="widget-title">{{ strtoupper($jenis) }} Terakhir</h3>


                            <ul class="list-unstyled">
                                @foreach ($latest_post as $v)
                                    <li class="d-flex align-items-center">
                                        <div class="posts-thumb">
                                            <a href="{{ route('user.detail.post', ['jenis' => $jenis, 'id' => $v->id] ) }}"><img loading="lazy" alt="img"
                                                    src="{{ asset('upload/' . $jenis . '/' . $v->thumbnail) }}"></a>
                                        </div>
                                        <div class="post-info">
                                            <h4 class="entry-title">
                                                <a href="{{ route('user.detail.post', ['jenis' => $jenis, 'id' => $v->id] ) }}">{{ $v->judul }}</a>
                                            </h4>
                                        </div>
                                    </li><!-- 1st post end-->
                                @endforeach

                            </ul>

                        </div><!-- Recent post end -->




                    </div><!-- Sidebar end -->
                </div><!-- Sidebar Col end -->

            </div><!-- Main row end -->

        </div><!-- Conatiner end -->
    </section><!-- Main container end -->
@endsection
