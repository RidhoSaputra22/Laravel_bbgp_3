@extends('layouts.app', ['title' => 'Profile'])

@section('content')
    @push('styles')
      <link rel="stylesheet" href="{{ asset('library/bootstrap-social/bootstrap-social.css')}}">
    @endpush

    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Profile</h1>
                <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="#">Dashboard</a></div>
                    <div class="breadcrumb-item">Profile</div>
                </div>
            </div>
            <div class="section-body">
                <h2 class="section-title">Hi, {{ session('name') }}!</h2>
                <p class="section-lead">
                    Silahkan ubah profile anda di halaman ini.
                </p>

                <div class="row mt-sm-4 justify-content-center">
                   
                    <div class="col-12 col-md-12 col-lg-7">
                        <div class="card">
                            <form method="post" action="{{ route('profile.update') }}" class="needs-validation" novalidate="">
                                @csrf
                                @method('PUT')
                                <input type="hidden" class="form-control"  name="id" value="{{ $data->id }}" required="">
                                <div class="card-header">
                                    <h4>Edit Profile</h4>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="form-group col-12">
                                            <label>Nama Lengkap</label>
                                            <input type="text" class="form-control" placeholder="Masukkan nama lengkap anda" name="name" value="{{ $data->name }}" required="">
                                            <div class="invalid-feedback">
                                                Please fill in the name
                                            </div>
                                        </div>
                                        
                                    </div>
                                    <div class="row">
                                        <div class="form-group  col-12">
                                            <label>Username</label>
                                            <input type="text" class="form-control" placeholder="Masukkan username anda" name="username" value="{{ $data->username }}" required="">
                                            <div class="invalid-feedback">
                                                Please fill in the username
                                            </div>
                                        </div>
                                     </div>
                                     
                                     <div class="row">
                                        <div class="form-group  col-12">
                                            <label>Ubah Password (bisa kosong jika tidak ada perubahan)</label>
                                            <input type="password" class="form-control" value="" name="password" placeholder="Masukkan password baru anda" >
                                            <input type="hidden" class="form-control" value="{{ $data->password }}" name="oldPassword" >
                                        </div>
                                     </div>
                                    
                                </div>
                                <div class="card-footer text-right">
                                    <a href="{{ route('dashboard') }}" class="btn btn-warning mr-2">Kembali</a>
                                    <button class="btn btn-primary">Simpan Perubahan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    @push('scripts')
        
    @endpush
@endsection
