<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title }} &mdash; BBGTK Sulsel</title>

    <!-- General CSS Files -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
        integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    {{-- <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css"
        integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous"> --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.7.2/css/all.min.css"
        integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('library/selectric/public/selectric.css') }}">


    <!-- CSS Libraries -->
    @stack('styles')

    <!-- Template CSS -->
    <link rel="stylesheet" href="{{ asset('library/selectric/public/selectric.css') }}">

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('library/select2/dist/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/bootstrap-timepicker/css/bootstrap-timepicker.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/bootstrap-daterangepicker/daterangepicker.css') }}">


</head>

<body class="{{ $title == 'Layout Transparent' ? 'layout-2' : ($title == 'Layout Top Navigation' ? 'layout-3' : '') }}">

    <div id="app">
        <div class="main-wrapper {{ $title == 'Layout Top Navigation' ? 'container' : '' }}">

            {{-- Header and Sidebar --}}
            @if ($title == 'Layout Transparent')
                @include('components.transparent.header')

                @include('components.transparent.sidebar')
            @elseif ($title == 'Layout Top Navigation')
                @include('components.top-navigation.navbar')
            @else
                @include('components.default.header')

                @include('components.default.sidebar')
            @endif
            <!-- Main Content -->
            @yield('content')

            {{-- Footer --}}
            @include('components.footer')

        </div>
    </div>

    @stack('modals')

    <!-- General JS Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"
        integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous">
    </script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"
        integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.nicescroll/3.7.6/jquery.nicescroll.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.24.0/moment.min.js"></script>
    <script src="{{ asset('library/selectric/public/jquery.selectric.min.js') }}"></script>

    <script src="{{ asset('js/stisla.js') }}"></script>

    <!-- JS Libraies -->
    <script src="{{ asset('library/izitoast/dist/js/iziToast.min.js') }}"></script>
    <script src="{{ asset('library/sweetalert/dist/sweetalert.min.js') }}"></script>
    <script src="{{ asset('library/summernote/dist/summernote-bs4.js') }}"></script>
    <script src="{{ asset('library/select2/dist/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('library/bootstrap-timepicker/js/bootstrap-timepicker.min.js') }}"></script>
    <script src="{{ asset('library/bootstrap-daterangepicker/daterangepicker.js') }}"></script>
    <script src="{{ asset('library/selectric/public/jquery.selectric.min.js') }}"></script>




    <!-- JS Libraies -->
    @stack('scripts')


    <!-- Template JS File -->
    <script src="{{ asset('js/scripts.js') }}"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
    @if (session('message') == 'username sudah ada')
        <script>
            // iziToast.success({
            //     title: 'Sukses',
            //     message: 'Berhasil tambah data',
            //     position: 'topRight'
            // });
            swal("Warning", "Username sudah terdaftar", "error");
        </script>
    @endif
    {{-- success store data --}}
    @if (session('message') == 'store')
        <script>
            // iziToast.success({
            //     title: 'Sukses',
            //     message: 'Berhasil tambah data',
            //     position: 'topRight'
            // });
            swal("Berhasil", "Berhasil tambah data", "success");
        </script>
    @endif
    @if (session('message') == 'size gambar')
        <script>
            // iziToast.success({
            //     title: 'Sukses',
            //     message: 'Berhasil update data',
            //     position: 'topRight'
            // });
            swal("Danger", "Gambar tidak valid, pastikan gambar jpg, png, atau jpeg dengan (size min. 512kb)", "error");
        </script>
    @endif
    @if (session('message') == 'size bukti')
        <script>
            // iziToast.success({
            //     title: 'Sukses',
            //     message: 'Berhasil update data',
            //     position: 'topRight'
            // });
            swal("Danger", "Bukti tidak valid, pastikan file ekstensi pdf dengan (size min. 1.5 mb)", "error");
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
            swal("Berhasil", "Berhasil update data", "success");
        </script>
    @endif

    @if (session('message') == 'sukses update data sekolah')
        <script>
            swal("Success", "Berhasil update data sekolah", "success");
        </script>
    @endif

    @if (session('message') == 'error form')
        <script>
            swal("Warning", "Ada kesalahan dalam pengisian form anda", "warning");
        </script>
    @endif

    @if (session('message') == 'error nik')
        <script>
            swal("Peringatan", "NIK atau NIP yang anda masukkan sudah terdaftar", "error");
        </script>
    @endif

    @if (session('message') == 'error golongan')
        <script>
            swal("Warning", "Golongan tidak valid", "error");
        </script>
    @endif

    {{-- success login --}}
    @if (session('message') == 'sukses login')
        <script>
            swal("Berhasil", "Berhasil Login", "success");
        </script>
    @endif

    {{-- validasi barang keluar --}}
    @if (session('message') == 'stok error')
        <script>
            swal("Warning", "Jumlah yang anda masukkan tidak valid dengan Stok barang", "error");
        </script>
    @endif

    {{-- failed login --}}
    @if (session('message') == 'gagal login')
        <script>
            swal("Warning", "Periksa kembali username dan password anda", "error");
        </script>
    @endif

    {{--  login dulu --}}
    @if (session('message') == 'need login')
        <script>
            swal("Warning", "Anda harus login terlebih dahulu", "error");
        </script>
    @endif

    {{--  update proffile --}}
    @if (session('message') == 'update profile')
        <script>
            swal("Berhasil", "Berhasil update profile, silahkan login ulang untuk melihat perubahan", "success");
        </script>
    @endif

    {{--  update proffile --}}
    @if (session('message') == 'error surat')
        <script>
            swal("Error", "Tidak ada data honor dari kegiatan ini", "error");
        </script>
    @endif

    {{--  update proffile --}}
    @if (session('message') == 'error suratk')
        <script>
            swal("Error", "Tidak ada data kuitansi dari kegiatan ini", "error");
        </script>
    @endif

    @if (session('message') == 'session expired')
        <script>
            swal("Session Expired", "Sesi login anda telah habis, Silahkan login kembali", "warning");
        </script>
    @endif



</body>

</html>
