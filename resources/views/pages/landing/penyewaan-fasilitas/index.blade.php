@extends('layouts.landing.app')
@section('content')
    @push('styles')
        <style>
            .room-card {
                background: #fff;
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
                transition: all 0.3s ease;
                height: 100%;
                display: flex;
                flex-direction: column;
            }

            .room-card:hover {
                transform: translateY(-10px);
                box-shadow: 0 10px 30px rgba(255, 107, 0, 0.2);
            }

            .room-image {
                width: 100%;
                height: 250px;
                object-fit: cover;
                position: relative;
                cursor: pointer;
                transition: all 0.3s ease;
            }

            .room-image:hover {
                opacity: 0.9;
            }

            .room-badge {
                position: absolute;
                top: 15px;
                right: 15px;
                color: white;
                padding: 8px 15px;
                border-radius: 20px;
                font-weight: 600;
                font-size: 12px;
                text-transform: uppercase;
            }

            .room-content {
                padding: 25px;
                flex-grow: 1;
                display: flex;
                flex-direction: column;
            }

            .room-title {
                font-size: 18px;
                font-weight: 700;
                color: #333;
                margin-bottom: 15px;
            }

            .room-price {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 12px 20px;
                border-radius: 8px;
                margin-bottom: 15px;
                text-align: center;
            }

            .room-price h4 {
                margin: 0;
                font-size: 24px;
                font-weight: 700;
            }

            .room-price small {
                font-size: 12px;
                opacity: 0.9;
            }

            .room-details {
                background: #1376bd;
                padding: 15px;
                border-radius: 8px;
                margin-bottom: 15px;
                font-size: 14px;
                line-height: 1.8;
                flex-grow: 1;
            }

            .room-details ul {
                list-style: none;
                padding: 0;
                margin: 0;
            }

            .room-details ul li {
                padding: 5px 0;
                color: #666;
            }

            .room-details ul li:before {
                content: "✓ ";
                color: #ff6b00;
                font-weight: bold;
                margin-right: 8px;
            }

            .btn-whatsapp {
                background: #25D366;
                color: white;
                padding: 12px 30px;
                border-radius: 50px;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                font-weight: 600;
                transition: all 0.3s ease;
                width: 100%;
                margin-top: auto;
            }

            .btn-whatsapp:hover {
                background: #20ba5a;
                color: white;
                transform: scale(1.05);
                text-decoration: none;
            }

            .btn-whatsapp i {
                margin-right: 8px;
                font-size: 18px;
            }

            .section-category {
                margin-top: 60px;
                margin-bottom: 40px;
            }

            .category-title {
                font-size: 28px;
                font-weight: 700;
                color: #333;
                margin-bottom: 10px;
                position: relative;
                padding-bottom: 15px;
            }

            .category-title:after {
                content: '';
                position: absolute;
                bottom: 0;
                left: 0;
                width: 60px;
                height: 3px;
                background: #ff6b00;
            }

            .category-subtitle {
                color: #666;
                font-size: 16px;
                margin-bottom: 30px;
            }

            .simple-card {
                background: #fff;
                border-radius: 10px;
                overflow: hidden;
                box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
                transition: all 0.3s ease;
                margin-bottom: 30px;
            }

            .simple-card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            }

            .simple-card-image {
                width: 100%;
                height: 200px;
                object-fit: cover;
                cursor: pointer;
                transition: all 0.3s ease;
            }

            .simple-card-image:hover {
                opacity: 0.9;
            }

            .simple-card-content {
                padding: 20px;
            }

            .simple-card-title {
                font-size: 16px;
                font-weight: 600;
                color: #333;
                margin-bottom: 15px;
            }

            .no-image-placeholder {
                width: 100%;
                height: 250px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 48px;
            }

            /* Image Preview Modal Styles */
            .image-preview-modal {
                display: none;
                position: fixed;
                z-index: 9999;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.95);
                animation: fadeIn 0.3s ease;
            }

            .image-preview-modal.active {
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .modal-content-wrapper {
                position: relative;
                max-width: 90%;
                max-height: 90%;
                animation: zoomIn 0.3s ease;
            }

            .modal-image {
                max-width: 100%;
                max-height: 90vh;
                object-fit: contain;
                border-radius: 8px;
                box-shadow: 0 10px 50px rgba(0, 0, 0, 0.5);
            }

            .modal-close {
                position: absolute;
                top: -40px;
                right: 0;
                color: white;
                font-size: 35px;
                font-weight: bold;
                cursor: pointer;
                background: rgba(255, 255, 255, 0.1);
                width: 45px;
                height: 45px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.3s ease;
            }

            .modal-close:hover {
                background: rgba(255, 107, 0, 0.8);
                transform: rotate(90deg);
            }

            .modal-title {
                position: absolute;
                bottom: -50px;
                left: 0;
                right: 0;
                color: white;
                text-align: center;
                font-size: 18px;
                font-weight: 600;
                padding: 10px;
                background: rgba(0, 0, 0, 0.5);
                border-radius: 5px;
            }

            .image-zoom-icon {
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: rgba(255, 107, 0, 0.9);
                color: white;
                width: 50px;
                height: 50px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 24px;
                opacity: 0;
                transition: all 0.3s ease;
                pointer-events: none;
            }

            .room-card:hover .image-zoom-icon,
            .simple-card:hover .image-zoom-icon {
                opacity: 1;
            }

            @keyframes fadeIn {
                from {
                    opacity: 0;
                }

                to {
                    opacity: 1;
                }
            }

            @keyframes zoomIn {
                from {
                    transform: scale(0.8);
                    opacity: 0;
                }

                to {
                    transform: scale(1);
                    opacity: 1;
                }
            }

            /* Responsive */
            @media (max-width: 768px) {
                .modal-content-wrapper {
                    max-width: 95%;
                    max-height: 85%;
                }

                .modal-close {
                    top: -35px;
                    font-size: 28px;
                    width: 40px;
                    height: 40px;
                }

                .modal-title {
                    font-size: 16px;
                    bottom: -45px;
                }
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
                            <h1 class="banner-title">Penyewaan Ruangan BBGTK</h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb justify-content-center">
                                    <li class="breadcrumb-item"><a href="/">Home</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Penyewaan Ruangan</li>
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

            {{-- ASRAMA SECTION --}}
            @if ($asramas->count() > 0)
                <div class="section-category">
                    <div class="row">
                        <div class="col-12 text-center">
                            <h2 class="category-title">
                                <i class="fas fa-bed mr-2 text-primary"></i>Asrama
                            </h2>
                            <p class="category-subtitle">Fasilitas penginapan yang nyaman untuk peserta kegiatan</p>
                        </div>
                    </div>

                    <div class="row">
                        @foreach ($asramas as $asrama)
                            <div class="col-lg-4 col-md-6 mb-4">
                                <div class="room-card">
                                    <div style="position: relative;">
                                        @if ($asrama->foto_utama)
                                            <img src="{{ asset('upload/penyewaan/' . $asrama->foto_utama) }}"
                                                alt="{{ $asrama->nama_ruangan }}" class="room-image preview-image"
                                                data-title="{{ $asrama->nama_ruangan }}">
                                            <div class="image-zoom-icon">
                                                <i class="fas fa-search-plus"></i>
                                            </div>
                                        @else
                                            <div class="no-image-placeholder">
                                                <i class="fas fa-bed"></i>
                                            </div>
                                        @endif
                                        <div class="room-badge bg-success">Mulai Rp.
                                            {{ number_format($asrama->harga_per_malam, 0, ',', '.') }}</div>
                                    </div>

                                    <div class="room-content">
                                        <h3 class="room-title text-center">{{ $asrama->nama_ruangan }}</h3>

                                        @if ($asrama->rincian_harga)
                                            <div class="room-details text-white">
                                                {!! $asrama->rincian_harga !!}
                                            </div>
                                        @endif

                                        <a href="https://wa.me/6281356484509?text=Halo, saya ingin menanyakan tentang {{ $asrama->nama_ruangan }}"
                                            target="_blank" class="btn-whatsapp">
                                            <i class="fab fa-whatsapp"></i> Hubungi via WhatsApp
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- AULA & KELAS SECTION --}}
            @if ($aulas->count() > 0 || $kelas->count() > 0)
                <div class="section-category">
                    <div class="row">
                        <div class="col-12 text-center">
                            <h2 class="category-title">
                                <i class="fas fa-building mr-2 text-info"></i>Aula & Ruang Kelas
                            </h2>
                            <p class="category-subtitle">Ruang pertemuan dan pembelajaran yang luas dan nyaman</p>
                        </div>
                    </div>

                    <div class="row">
                        {{-- AULA --}}
                        @foreach ($aulas as $aula)
                            <div class="col-lg-4 col-md-4 col-sm-6 mb-4">
                                <div class="simple-card">
                                    <div style="position: relative;">
                                        @if ($aula->foto_utama)
                                            <img src="{{ asset('upload/penyewaan/' . $aula->foto_utama) }}"
                                                alt="{{ $aula->nama_ruangan }}" class="simple-card-image preview-image"
                                                data-title="{{ $aula->nama_ruangan }}">
                                            <div class="image-zoom-icon">
                                                <i class="fas fa-search-plus"></i>
                                            </div>
                                        @else
                                            <div class="no-image-placeholder" style="height: 200px; font-size: 36px;">
                                                <i class="fas fa-building"></i>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="simple-card-content">
                                        <h4 class="simple-card-title text-center">{{ $aula->nama_ruangan }}</h4>
                                        <a href="https://wa.me/6281356484509?text=Halo, saya ingin menanyakan tentang {{ $aula->nama_ruangan }}"
                                            target="_blank" class="btn-whatsapp">
                                            <i class="fab fa-whatsapp"></i> Hubungi WA
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        {{-- KELAS --}}
                        @foreach ($kelas as $kls)
                            <div class="col-lg-4 col-md-4 col-sm-6 mb-4">
                                <div class="simple-card">
                                    <div style="position: relative;">
                                        @if ($kls->foto_utama)
                                            <img src="{{ asset('upload/penyewaan/' . $kls->foto_utama) }}"
                                                alt="{{ $kls->nama_ruangan }}" class="simple-card-image preview-image"
                                                data-title="{{ $kls->nama_ruangan }}">
                                            <div class="image-zoom-icon">
                                                <i class="fas fa-search-plus"></i>
                                            </div>
                                        @else
                                            <div class="no-image-placeholder" style="height: 200px; font-size: 36px;">
                                                <i class="fas fa-chalkboard"></i>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="simple-card-content">
                                        <h4 class="simple-card-title text-center">{{ $kls->nama_ruangan }}</h4>
                                        <a href="https://wa.me/6281356484509?text=Halo, saya ingin menanyakan tentang {{ $kls->nama_ruangan }}"
                                            target="_blank" class="btn-whatsapp">
                                            <i class="fab fa-whatsapp"></i> Hubungi WA
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- LABORATORIUM SECTION --}}
            @if ($laboratoriums->count() > 0)
                <div class="section-category">
                    <div class="row">
                        <div class="col-12 text-center">
                            <h2 class="category-title">
                                <i class="fas fa-flask mr-2 text-warning"></i>Laboratorium
                            </h2>
                            <p class="category-subtitle">Fasilitas laboratorium dengan peralatan lengkap</p>
                        </div>
                    </div>

                    <div class="row">
                        @foreach ($laboratoriums as $lab)
                            <div class="col-lg-6 col-md-6 col-sm-6 mb-4">
                                <div class="simple-card">
                                    <div style="position: relative;">
                                        @if ($lab->foto_utama)
                                            <img src="{{ asset('upload/penyewaan/' . $lab->foto_utama) }}"
                                                alt="{{ $lab->nama_ruangan }}" class="simple-card-image preview-image"
                                                data-title="{{ $lab->nama_ruangan }}">
                                            <div class="image-zoom-icon">
                                                <i class="fas fa-search-plus"></i>
                                            </div>
                                        @else
                                            <div class="no-image-placeholder" style="height: 200px; font-size: 36px;">
                                                <i class="fas fa-flask"></i>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="simple-card-content">
                                        <h4 class="simple-card-title text-center">{{ $lab->nama_ruangan }}</h4>
                                        <a href="https://wa.me/6281356484509?text=Halo, saya ingin menanyakan tentang {{ $lab->nama_ruangan }}"
                                            target="_blank" class="btn-whatsapp">
                                            <i class="fab fa-whatsapp"></i> Hubungi WA
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="gap-40"></div>

            {{-- Info Contact --}}
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        <h5><i class="fas fa-info-circle mr-2"></i>Informasi Penyewaan</h5>
                        <p class="mb-0">
                            Untuk informasi lebih lanjut dan reservasi, silakan hubungi kami melalui WhatsApp.
                            <br>Kami siap melayani kebutuhan ruangan Anda.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </section>

    {{-- Image Preview Modal --}}
    <div id="imagePreviewModal" class="image-preview-modal">
        <div class="modal-content-wrapper">
            <span class="modal-close">&times;</span>
            <img id="modalImage" class="modal-image" src="" alt="Preview">
            <div id="modalTitle" class="modal-title"></div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const modal = document.getElementById('imagePreviewModal');
                const modalImg = document.getElementById('modalImage');
                const modalTitle = document.getElementById('modalTitle');
                const closeBtn = document.querySelector('.modal-close');
                const previewImages = document.querySelectorAll('.preview-image');

                // Open modal when image is clicked
                previewImages.forEach(img => {
                    img.addEventListener('click', function() {
                        modal.classList.add('active');
                        modalImg.src = this.src;
                        modalTitle.textContent = this.getAttribute('data-title') || this.alt;
                        document.body.style.overflow = 'hidden'; // Prevent scrolling
                    });
                });

                // Close modal when X is clicked
                closeBtn.addEventListener('click', function() {
                    closeModal();
                });

                // Close modal when clicking outside the image
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        closeModal();
                    }
                });

                // Close modal with ESC key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && modal.classList.contains('active')) {
                        closeModal();
                    }
                });

                function closeModal() {
                    modal.classList.remove('active');
                    document.body.style.overflow = ''; // Restore scrolling
                }
            });
        </script>
    @endpush
@endsection
