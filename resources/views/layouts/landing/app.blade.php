<!DOCTYPE html>
<html lang="en">

<head>

    <!-- Basic Page Needs
================================================== -->
    <meta charset="utf-8">
    <title>BBGTK Provinsi Sulawesi Selatan - Rumah Belajar Insan Pendidikan</title>

    <!-- Mobile Specific Metas
================================================== -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="description" content="BBGTK Provinsi Sulawesi Selatan">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name=author content="BBGTK Sul-Sel">
    <meta name=generator content="BBGTK Provinsi Sulawesi Selatan">

    <!-- Favicon
================================================== -->
    <link rel="icon" type="image/png" href="{{ asset('landing/images/fav.png') }}">

    <!-- CSS
================================================== -->
    <!-- Bootstrap -->
    <link rel="stylesheet" href="{{ asset('landing/plugins/bootstrap/bootstrap.min.css') }}">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="{{ asset('landing/plugins/fontawesome/css/all.min.css') }}">
    <!-- Animation -->
    <link rel="stylesheet" href="{{ asset('landing/plugins/animate-css/animate.css') }}">
    <!-- slick Carousel -->
    <link rel="stylesheet" href="{{ asset('landing/plugins/slick/slick.css') }}">
    <link rel="stylesheet" href="{{ asset('landing/plugins/slick/slick-theme.css') }}">
    <!-- Colorbox -->
    <link rel="stylesheet" href="{{ asset('landing/plugins/colorbox/colorbox.css') }}">
    <!-- Template styles-->
    <link rel="stylesheet" href="{{ asset('landing/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('landing/css/custome.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.0/css/dataTables.dataTables.css" />

    <style>
        /* Floating WhatsApp Chat */
        #wa-chat-container {
            position: fixed;
            bottom: 20px;
            right: 90px;
            z-index: 9998;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        #wa-chat-box {
            width: 400px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            transform-origin: bottom right;
            animation: slideUp 0.3s ease;
            display: none;
        }

        #wa-chat-box.active {
            display: block;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: scale(0.8) translateY(20px);
            }

            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .wa-chat-header {
            background: #1376bd;
            color: white;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .wa-chat-header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .wa-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #1376bd;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .wa-info h4 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
        }

        .wa-info p {
            margin: 3px 0 0 0;
            font-size: 12px;
            opacity: 0.9;
        }

        .wa-close-btn {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: background 0.3s;
        }

        .wa-close-btn:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .wa-chat-body {
            padding: 20px;
            background: #f0f0f0;
            min-height: 200px;
            max-height: 300px;
            overflow-y: auto;
        }

        .wa-message {
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            position: relative;
        }

        .wa-message::before {
            content: '';
            position: absolute;
            left: -8px;
            top: 15px;
            width: 0;
            height: 0;
            border-top: 8px solid transparent;
            border-bottom: 8px solid transparent;
            border-right: 8px solid white;
        }

        .wa-message p {
            margin: 0;
            color: #333;
            font-size: 14px;
            line-height: 1.5;
        }

        .wa-message-time {
            font-size: 11px;
            color: #999;
            margin-top: 8px;
            text-align: right;
        }

        .wa-chat-input {
            padding: 15px;
            background: white;
            border-top: 1px solid #e0e0e0;
        }

        .wa-input-wrapper {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .wa-input-wrapper textarea {
            flex: 1;
            border: 1px solid #ddd;
            border-radius: 25px;
            padding: 12px 18px;
            font-size: 14px;
            resize: none;
            font-family: inherit;
            outline: none;
            transition: border-color 0.3s;
        }

        .wa-input-wrapper textarea:focus {
            border-color: #25D366;
        }

        .wa-send-btn {
            background: #1376bd;
            color: white;
            border: none;
            width: 50%;
            height: 45px;
            border-radius: 5%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            transition: all 0.3s;
            box-shadow: 0 2px 10px rgba(37, 211, 102, 0.3);
        }

        .wa-send-btn:hover {
            background: #1c8cdd;
            transform: scale(1.05);
        }

        .wa-send-btn:active {
            transform: scale(0.95);
        }

        #wa-toggle-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: #25D366;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 20px rgba(37, 211, 102, 0.4);
            z-index: 9999;
            transition: all 0.3s;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 4px 20px rgba(37, 211, 102, 0.4);
            }

            50% {
                box-shadow: 0 4px 30px rgba(37, 211, 102, 0.6);
            }

            100% {
                box-shadow: 0 4px 20px rgba(37, 211, 102, 0.4);
            }
        }

        #wa-toggle-btn:hover {
            transform: scale(1.1);
            background: #20ba5a;
        }

        #wa-toggle-btn i {
            font-size: 30px;
            color: white;
        }

        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff4444;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
            border: 2px solid white;
        }

        @media (max-width: 480px) {
            #wa-chat-box {
                width: calc(100vw - 40px);
            }

            #wa-chat-container,
            #wa-toggle-btn {
                right: 20px;
            }
        }

        /* Adjustment for back-to-top button */
        .back-to-top {
            bottom: 100px !important;
        }
    </style>

    @stack('styles')

</head>

<body>
    <div class="body-inner">

        @include('layouts.landing.header')
        @include('layouts.landing.topbar')

        @yield('content')

        @include('layouts.landing.footer')

        <!-- WhatsApp Chat Widget -->
        <button id="wa-toggle-btn" aria-label="Chat WhatsApp">
            <i class="fab fa-whatsapp"></i>
            <span class="notification-badge" style="display: none;">1</span>
        </button>

        <div id="wa-chat-container">
            <div id="wa-chat-box">
                <div class="wa-chat-header">
                    <div class="wa-chat-header-left">
                        <div class="wa-avatar">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="wa-info ">
                            <h4 class="text-white">BBGTK Sulawesi Selatan</h4>
                            <p>Online - Siap membantu Anda</p>
                        </div>
                    </div>
                    <button class="wa-close-btn" id="wa-minimize-btn" aria-label="Minimize">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="wa-chat-body">
                    <div class="wa-message">
                        <p>👋 Halo! Selamat datang di <strong>BBGTK Sulawesi Selatan</strong></p>
                        <p>Ada yang bisa kami bantu hari ini?</p>
                        <div class="wa-message-time">Baru saja</div>
                    </div>
                    {{-- <div class="wa-message">
                        <p>Silakan tulis pesan Anda di bawah dan klik kirim untuk menghubungi kami via WhatsApp 💬</p>
                        <div class="wa-message-time">Baru saja</div>
                    </div> --}}
                </div>

                <div class="wa-chat-input">
                    <div class="wa-input-wrapper">
                        {{-- <textarea id="wa-message-input" rows="1" placeholder="Ketik pesan Anda..." onkeypress="handleEnterWA(event)"></textarea> --}}
                        <button class="wa-send-btn " onclick="sendWhatsApp()" aria-label="Kirim">
                            <div class="pr-2">
                                Kirim Pesan
                            </div>
                            <i class="fab fa-whatsapp "></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>


        <!-- Javascript Files
  ================================================== -->

        <!-- initialize jQuery Library -->
        <script src="{{ asset('landing/plugins/jQuery/jquery.min.js') }}"></script>
        <!-- Bootstrap jQuery -->
        <script src="{{ asset('landing/plugins/bootstrap/bootstrap.min.js') }}" defer></script>
        <!-- Slick Carousel -->
        <script src="{{ asset('landing/plugins/slick/slick.min.js') }}"></script>
        <script src="{{ asset('landing/plugins/slick/slick-animation.min.js') }}"></script>
        <!-- Color box -->
        <script src="{{ asset('landing/plugins/colorbox/jquery.colorbox.js') }}"></script>
        <!-- shuffle -->
        <script src="{{ asset('landing/plugins/shuffle/shuffle.min.js') }}" defer></script>



        <!-- Template custom -->

        <script src="{{ asset('landing/js/script.js') }}"></script>
        <script src="https://cdn.datatables.net/2.1.0/js/dataTables.js"></script>

        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <!-- WhatsApp Chat Script -->
        <script>
            // KONFIGURASI - Ganti dengan nomor WhatsApp BBGTK
            const WHATSAPP_NUMBER = '6282250365529'; // Format: 62xxx (tanpa +)

            $(document).ready(function() {
                const toggleBtn = $('#wa-toggle-btn');
                const chatBox = $('#wa-chat-box');
                const minimizeBtn = $('#wa-minimize-btn');
                const notificationBadge = $('.notification-badge');

                chatBox.toggleClass('active');
                notificationBadge.hide();
                $('#wa-message-input').focus();
                toggleBtn.hide();


                // Toggle chat box
                toggleBtn.on('click', function() {
                    chatBox.toggleClass('active');

                    if (chatBox.hasClass('active')) {
                        notificationBadge.hide();
                        toggleBtn.fadeOut(300);
                    }
                });

                // Minimize chat
                minimizeBtn.on('click', function() {
                    chatBox.removeClass('active');
                    toggleBtn.fadeIn(300);
                });

                // Close when clicking outside
                $(document).on('click', function(event) {
                    const container = $('#wa-chat-container');
                    const isClickInside = container.has(event.target).length > 0 ||
                        toggleBtn.has(event.target).length > 0 ||
                        container.is(event.target) ||
                        toggleBtn.is(event.target);

                    if (!isClickInside && chatBox.hasClass('active')) {
                        chatBox.removeClass('active');
                        toggleBtn.fadeIn(300);
                    }
                });

                // Auto-resize textarea
                $('#wa-message-input').on('input', function() {
                    this.style.height = 'auto';
                    this.style.height = Math.min(this.scrollHeight, 100) + 'px';
                });

                // Show notification badge after 5 seconds
                setTimeout(function() {
                    if (!chatBox.hasClass('active')) {
                        notificationBadge.fadeIn();
                    }
                }, 5000);
            });

            // Send to WhatsApp
            function sendWhatsApp() {
                const messageInput = document.getElementById('wa-message-input');

                const waUrl = `https://wa.me/${WHATSAPP_NUMBER}`;

                window.open(waUrl, '_blank');
                messageInput.value = '';
                messageInput.style.height = 'auto';

            }

            // Handle Enter key
            function handleEnterWA(event) {
                if (event.key === 'Enter' && !event.shiftKey) {
                    event.preventDefault();
                    sendWhatsApp();
                }
            }
        </script>

        @stack('scripts')

        @if (session('message') == 'store')
            <script>
                // iziToast.success({
                //     title: 'Sukses',
                //     message: 'Berhasil tambah data',
                //     position: 'topRight'
                // });
                Swal.fire("Berhasil", "Berhasil tambah data", "success");
            </script>
        @endif

        @if (session('message') == 'nik daftar')
            <script>
                // iziToast.success({
                //     title: 'Sukses',
                //     message: 'Berhasil tambah data',
                //     position: 'topRight'
                // });
                Swal.fire("Warning", "NIK anda telah terdaftar, silahkan menghubungi admin untuk melihat data anda", "error");
            </script>
        @endif

        {{-- success update data --}}
        @if (session('message') == 'update')
            <script>
                // iziToast.success({
                //     title: 'Sukses',
                //     message: 'Berhasil update data',
                //     position: 'topRight'
                // });
                Swal.fire("Berhasil", "Berhasil update data", "success");
            </script>
        @endif

        {{-- success login --}}
        @if (session('message') == 'sukses login')
            <script>
                Swal.fire("Berhasil", "Berhasil Login", "success");
            </script>
        @endif


        @if (session('message') == 'error golongan')
            <script>
                Swal.fire("Warning", "Golongan tidak valid", "error");
            </script>
        @endif

        {{-- validasi barang keluar --}}
        @if (session('message') == 'stok error')
            <script>
                Swal.fire("Warning", "Jumlah yang anda masukkan tidak valid dengan Stok barang", "error");
            </script>
        @endif

        {{-- failed login --}}
        @if (session('message') == 'gagal login')
            <script>
                Swal.fire("Warning", "Periksa kembali username dan password anda", "error");
            </script>
        @endif

        {{--  login dulu --}}
        @if (session('message') == 'need login')
            <script>
                Swal.fire("Warning", "Anda harus login terlebih dahulu", "error");
            </script>
        @endif


        {{--  succces logout --}}
        @if (session('message') == 'sukses logout')
            <script>
                Swal.fire("Berhasil", "Anda Telah Logout", "success");
            </script>
        @endif

        {{--  validasi pegawai dan guru --}}
        @if (session('message') == 'data kosong')
            <script>
                Swal.fire("Warning", "Data anda tidak terdaftar, Silahkan mengisi biodata di bawah", "error");
            </script>
        @endif
        @if (session('message') == 'data ada')
            <script>
                Swal.fire("Berhasil", "Data anda terdaftar", "success");
            </script>
        @endif
        @if (session('message') == 'form kosong')
            <script>
                Swal.fire("Error", "Silahkan mengisi form inputan KTP", "error");
            </script>
        @endif

        @if (session('message') == 'user daftar')
            <script>
                Swal.fire("Berhasil", "Berhasil registrasi sebagai eksternal BBGTK SulSel", "success");
            </script>
        @endif

        @if (session('message') == 'nik sudah ada')
            <script>
                Swal.fire("Warning", "NIK anda telah terdaftar sebelum nya", "warning");
            </script>
        @endif

        @if (session('message') == 'sukses daftar sekolah')
            <script>
                Swal.fire("Success",
                    "Sekolah berhasil didaftarkan, silahkan login dengan nama yang telah anda input dengan password 12345(bisa diubah ketika login). untuk meliha data sekolah yang telah anda daftarkan",
                    "success");
            </script>
        @endif

        @if (session('message') == 'sukses daftar')
            <script>
                // Swal.fire("Berhasil", "Berhasil registrasi Kegiatan", "success");

                $(document).ready(function() {

                    // var val = '{{ session('id') }}'
                    var val = {!! json_encode(session('id')) !!};
                    console.log('id nya user : ', val.id);
                    var url = '{{ route('peserta.cetakByUser', ['id' => ':id']) }}'
                    url = url.replace(':id', val.id)
                    console.log('link nya user : ', url);

                    $.ajax({
                        // headers: {
                        //     "X-CSRF-TOKEN": token,
                        // },
                        url: url, // Ganti dengan route yang sesuai untuk mengambil status
                        type: 'GET',

                        success: function(response) {
                            console.log(response);
                            console.log(val, '{{ session('no_ktp') }}');
                            Swal.fire("Berhasil", "Berhasil registrasi Kegiatan", "success").then((result) => {
                                if (result.isConfirmed) {
                                    // Arahkan ke URL PDF untuk memulai download
                                    window.location.href = url;
                                }
                            });

                        },
                        error: function(error) {
                            console.error("AJAX Error:", error);
                            Swal.fire("Error", "Ajax Error.", "error");
                        },
                    });
                })
            </script>
        @endif

    </div><!-- Body inner end -->
</body>

</html>
