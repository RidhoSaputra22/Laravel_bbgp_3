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
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css"
        integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">


    <!-- CSS Libraries -->
    @stack('styles')

    <!-- Template CSS -->
    <link rel="stylesheet" href="{{ asset('library/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('library/datatables.net-select-bs4/css/select.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">
</head>

<body class="layout-3">

    <div id="app">
        <div class="main-wrapper container">

            {{-- Header and Sidebar --}}
            @include('components.top-navigation.navbar')

            <!-- Main Content -->
            @yield('content')

            {{-- Footer --}}
            @include('components.footer')

        </div>
    </div>

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
    <script src="{{ asset('js/stisla.js') }}"></script>

    <!-- JS Libraies -->
    <script src="{{ asset('library/izitoast/dist/js/iziToast.min.js') }}"></script>
    <script src="{{ asset('library/sweetalert/dist/sweetalert.min.js') }}"></script>
    <script src="{{ asset('library/summernote/dist/summernote-bs4.js') }}"></script>
    <script src="{{ asset('library/datatables/media/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('library/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('library/datatables.net-select-bs4/js/select.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('js/page/modules-datatables.js') }}"></script>

    <!-- JS Libraies -->
    @stack('scripts')

    <!-- Template JS File -->
    <script src="{{ asset('js/scripts.js') }}"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
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

    {{-- success login --}}
    @if (session('message') == 'sukses login')
        <script>
            swal("Berhasil", "Berhasil Login", "success");
        </script>
    @endif

    
    @if (session('message') == 'error golongan')
        <script>
            swal("Warning", "Golongan tidak valid", "error");
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


    {{--  succces logout --}}
    @if (session('message') == 'sukses logout')
        <script>
            swal("Berhasil", "Anda Telah Logout", "success");
        </script>
    @endif

    {{--  validasi pegawai dan guru --}}
    @if (session('message') == 'data kosong')
        <script>
            swal("Warning", "Data anda tidak terdaftar, Silahkan mengisi biodata di bawah", "error");
        </script>
    @endif
    @if (session('message') == 'data ada')
        <script>
            swal("Berhasil", "Data anda terdaftar", "success");
        </script>
    @endif
    @if (session('message') == 'form kosong')
        <script>
            swal("Error", "Silahkan mengisi form inputan KTP", "error");
        </script>
    @endif

    @if (session('message') == 'user daftar')
        <script>
            swal("Berhasil", "Berhasil registrasi, data akan di verifikasi terlebih dahulu", "success");
        </script>
    @endif

    @if (session('message') == 'sukses daftar')
        <script>
            swal("Berhasil", "Berhasil registrasi Kegiatan", "success");
        </script>
    @endif

    @if (session('message') == 'session expired')
        <script>
            swal("Session Expired", "Sesi login anda telah habis, Silahkan login kembali", "warning");
        </script>
    @endif



</body>

</html>
