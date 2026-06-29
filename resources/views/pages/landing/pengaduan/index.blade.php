@extends('layouts.landing.app')
@section('content')
    @push('styles')
        <style>
            /* Carousel Styles */
            .gallery-carousel {
                position: relative;
                overflow: hidden;
                border-radius: 10px;
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            }

            .carousel-item img {
                width: 100%;
                height: 500px;
                object-fit: contain;
                
                cursor: pointer;
                transition: transform 0.3s ease;
            }

            .carousel-item img:hover {
                transform: scale(1.02);
            }

            .carousel-control-prev,
            .carousel-control-next {
                width: 50px;
                height: 50px;
                background: rgba(255, 107, 0, 0.8);
                border-radius: 50%;
                top: 50%;
                transform: translateY(-50%);
            }

            .carousel-control-prev {
                left: 20px;
            }

            .carousel-control-next {
                right: 20px;
            }

            .carousel-control-prev:hover,
            .carousel-control-next:hover {
                background: rgba(255, 107, 0, 1);
            }

            .carousel-indicators li {
                width: 12px;
                height: 12px;
                border-radius: 50%;
                background-color: rgba(255, 255, 255, 0.5);
            }

            .carousel-indicators .active {
                background-color: #ff6b00;
            }

            /* Lightbox Modal Styles */
            .lightbox-modal {
                display: none;
                position: fixed;
                z-index: 9999;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.95);
                animation: fadeIn 0.3s;
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                }

                to {
                    opacity: 1;
                }
            }

            .lightbox-content {
                position: relative;
                margin: auto;
                padding: 20px;
                width: 90%;
                max-width: 1200px;
                height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .lightbox-image {
                max-width: 100%;
                max-height: 90vh;
                object-fit: contain;
                border-radius: 5px;
                box-shadow: 0 0 50px rgba(255, 255, 255, 0.1);
            }

            .lightbox-close {
                position: absolute;
                top: 30px;
                right: 40px;
                color: #fff;
                font-size: 40px;
                font-weight: bold;
                cursor: pointer;
                z-index: 10000;
                transition: color 0.3s;
            }

            .lightbox-close:hover {
                color: #ff6b00;
            }

            .lightbox-prev,
            .lightbox-next {
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
                color: #fff;
                font-size: 50px;
                font-weight: bold;
                cursor: pointer;
                padding: 20px;
                background: rgba(255, 107, 0, 0.7);
                border-radius: 5px;
                transition: background 0.3s;
                z-index: 10000;
            }

            .lightbox-prev:hover,
            .lightbox-next:hover {
                background: rgba(255, 107, 0, 1);
            }

            .lightbox-prev {
                left: 20px;
            }

            .lightbox-next {
                right: 20px;
            }

            .lightbox-counter {
                position: absolute;
                bottom: 30px;
                left: 50%;
                transform: translateX(-50%);
                color: #fff;
                font-size: 18px;
                background: rgba(0, 0, 0, 0.7);
                padding: 10px 20px;
                border-radius: 20px;
            }

            /* Thumbnail Gallery */
            .thumbnail-gallery {
                margin-top: 40px;
            }

            .thumbnail-item {
                cursor: pointer;
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                transition: all 0.3s ease;
                margin-bottom: 20px;
            }

            .thumbnail-item:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 25px rgba(255, 107, 0, 0.3);
            }

            .thumbnail-item img {
                width: 100%;
                height: 200px;
                object-fit: contain;
            }

            .thumbnail-caption {
                padding: 15px;
                background: #fff;
                text-align: center;
                font-weight: 600;
                color: #333;
            }
        </style>
    @endpush

    <div id="banner-area" class="banner-area"
        style="background-image:url({{ asset('landing/images/banner/bannerKontak.png') }})">
        <div class="banner-text">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="banner-heading">
                            <h1 class="banner-title">Standar pelayanan di lingukngan BGTK Sulawesi Selatan<br> </h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb justify-content-center">
                                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Standar pelayanan di lingukngan BGTK Sulsel
                                    </li>
                                </ol>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section id="main-container" class="main-container">
        <div class="container">

            <div class="row text-center">
                <div class="col-12">
                    <h2 class="section-title">Pengaduan</h2>
                    <h3 class="section-sub-title">Standar Pelayanan Informasi</h3>
                </div>
            </div>

            <div class="gap-40"></div>

            <!-- Carousel Gallery -->
            <div class="row">
                <div class="col-12">
                    <div id="galleryCarousel" class="carousel slide gallery-carousel" data-ride="carousel"
                        data-interval="3000">
                        <ol class="carousel-indicators">
                            <li data-target="#galleryCarousel" data-slide-to="0" class="active"></li>
                            <li data-target="#galleryCarousel" data-slide-to="1"></li>
                            <li data-target="#galleryCarousel" data-slide-to="2"></li>
                            <li data-target="#galleryCarousel" data-slide-to="3"></li>
                            <li data-target="#galleryCarousel" data-slide-to="4"></li>
                            <li data-target="#galleryCarousel" data-slide-to="5"></li>
                        </ol>
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <img src="{{ asset('landing/images/pengaduan/1.webp') }}" alt="Kegiatan 1"
                                    onclick="openLightbox(0)">
                            </div>
                            <div class="carousel-item">
                                <img src="{{ asset('landing/images/pengaduan/2.webp') }}" alt="Kegiatan 2"
                                    onclick="openLightbox(1)">
                            </div>
                            <div class="carousel-item">
                                <img src="{{ asset('landing/images/pengaduan/3.webp') }}" alt="Kegiatan 3"
                                    onclick="openLightbox(2)">
                            </div>
                            <div class="carousel-item">
                                <img src="{{ asset('landing/images/pengaduan/4.webp') }}" alt="Kegiatan 4"
                                    onclick="openLightbox(3)">
                            </div>
                            <div class="carousel-item">
                                <img src="{{ asset('landing/images/pengaduan/5.webp') }}" alt="Kegiatan 5"
                                    onclick="openLightbox(4)">
                            </div>
                            <div class="carousel-item">
                                <img src="{{ asset('landing/images/pengaduan/6.webp') }}" alt="Kegiatan 6"
                                    onclick="openLightbox(5)">
                            </div>
                        </div>
                        <a class="carousel-control-prev" href="#galleryCarousel" role="button" data-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="sr-only">Previous</span>
                        </a>
                        <a class="carousel-control-next" href="#galleryCarousel" role="button" data-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="sr-only">Next</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="gap-60"></div>

            <!-- Thumbnail Gallery Grid -->
            <div class="row thumbnail-gallery">
                <div class="col-md-4 col-sm-6" onclick="openLightbox(0)">
                    <div class="thumbnail-item">
                        <img src="{{ asset('landing/images/pengaduan/1.webp') }}" alt="Kegiatan 1">
                        <div style="background-color:#1376bd;" class="thumbnail-caption text-light">
                            Standar Pelayanan Pengaduan
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6" onclick="openLightbox(1)">
                    <div class="thumbnail-item">
                        <img src="{{ asset('landing/images/pengaduan/2.webp') }}" alt="Kegiatan 2">
                        <div style="background-color:#1376bd;" class="thumbnail-caption text-light">
                            Standar Pelayanan Permohonan Informasi
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6" onclick="openLightbox(2)">
                    <div class="thumbnail-item">
                        <img src="{{ asset('landing/images/pengaduan/3.webp') }}" alt="Kegiatan 3">
                        <div style="background-color:#1376bd;" class="thumbnail-caption text-light">
                            Standar Pelayanan Fasilitasi Narasumber
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6" onclick="openLightbox(3)">
                    <div class="thumbnail-item">
                        <img src="{{ asset('landing/images/pengaduan/4.webp') }}" alt="Kegiatan 4">
                        <div style="background-color:#1376bd;" class="thumbnail-caption text-light">
                            Standar Pelayanan Penggunaan Sarana dan Prasaran
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6" onclick="openLightbox(4)">
                    <div class="thumbnail-item">
                        <img src="{{ asset('landing/images/pengaduan/5.webp') }}" alt="Kegiatan 5">
                        <div style="background-color:#1376bd;" class="thumbnail-caption text-light">
                            Standar Pelayanan Fasilitasi Peningkatan Kompetensi Pendidik dan Tenaga Kependidikan (PTK)
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-6" onclick="openLightbox(5)">
                    <div class="thumbnail-item">
                        <img src="{{ asset('landing/images/pengaduan/6.webp') }}" alt="Kegiatan 6">
                        <div style="background-color:#1376bd;" class="thumbnail-caption text-light">
                            Standar Pelayanan Magang dan Praktek Kerja Lapangan
                        </div>
                    </div>
                </div>
            </div>


            <div class="gap-40"></div>

        </div>
    </section>

    <!-- Lightbox Modal -->
    <div id="lightboxModal" class="lightbox-modal">
        <span class="lightbox-close" onclick="closeLightbox()">&times;</span>
        <span class="lightbox-prev" onclick="changeSlide(-1)">&#10094;</span>
        <span class="lightbox-next" onclick="changeSlide(1)">&#10095;</span>
        <div class="lightbox-content">
            <img id="lightboxImage" class="lightbox-image" src="" alt="Gallery Image">
        </div>
        <div class="lightbox-counter">
            <span id="imageCounter">1 / 6</span>
        </div>
    </div>

    @push('scripts')
        <script>
            // Array of images
            const images = [
                "{{ asset('landing/images/pengaduan/1.webp') }}",
                "{{ asset('landing/images/pengaduan/2.webp') }}",
                "{{ asset('landing/images/pengaduan/3.webp') }}",
                "{{ asset('landing/images/pengaduan/4.webp') }}",
                "{{ asset('landing/images/pengaduan/5.webp') }}",
                "{{ asset('landing/images/pengaduan/6.webp') }}"
            ];

            let currentImageIndex = 0;

            // Open lightbox
            function openLightbox(index) {
                currentImageIndex = index;
                document.getElementById('lightboxModal').style.display = 'block';
                updateLightboxImage();
                // Pause carousel when lightbox opens
                $('#galleryCarousel').carousel('pause');
            }

            // Close lightbox
            function closeLightbox() {
                document.getElementById('lightboxModal').style.display = 'none';
                // Resume carousel
                $('#galleryCarousel').carousel('cycle');
            }

            // Change slide in lightbox
            function changeSlide(direction) {
                currentImageIndex += direction;

                if (currentImageIndex >= images.length) {
                    currentImageIndex = 0;
                } else if (currentImageIndex < 0) {
                    currentImageIndex = images.length - 1;
                }

                updateLightboxImage();
            }

            // Update lightbox image
            function updateLightboxImage() {
                document.getElementById('lightboxImage').src = images[currentImageIndex];
                document.getElementById('imageCounter').textContent = (currentImageIndex + 1) + ' / ' + images.length;
            }

            // Keyboard navigation
            document.addEventListener('keydown', function(event) {
                if (document.getElementById('lightboxModal').style.display === 'block') {
                    if (event.key === 'ArrowLeft') {
                        changeSlide(-1);
                    } else if (event.key === 'ArrowRight') {
                        changeSlide(1);
                    } else if (event.key === 'Escape') {
                        closeLightbox();
                    }
                }
            });

            // Close lightbox when clicking outside image
            document.getElementById('lightboxModal').addEventListener('click', function(event) {
                if (event.target === this) {
                    closeLightbox();
                }
            });
        </script>
    @endpush
@endsection
