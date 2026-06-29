@extends('layouts.app', ['title' => 'Edit Data Pegawai BBGTK'])
@section('content')
   @push('styles')
      <link rel="stylesheet" href="{{ asset('library/select2/dist/css/select2.min.css') }}">
      <link rel="stylesheet" href="{{ asset('library/selectric/public/selectric.css') }}">
   @endpush

   <div class="main-content">
      <section class="section">
         <div class="section-header">
            <h1>Edit Data Pegawai BBGTK</h1>
            {{-- <div class="section-header-breadcrumb">
                    <div class="breadcrumb-item active"><a href="#">Dashboard</a></div>
                    <div class="breadcrumb-item"><a href="#">Bootstrap Components</a></div>
                    <div class="breadcrumb-item">Form</div>
                </div> --}}
         </div>

         <div class="section-body">

            <div class="row">

               <div class="col-md-12 col-lg-12">
                  <form action="{{ route('pegawai.update.user') }}" method="POST" enctype="multipart/form-data">
                     @csrf
                     @method('PUT')
                     <input type="hidden" name="id" value="{{ $pegawai->id }}">
                     <div class="card">
                        <div class="card-body">
                           <div class="row">
                              <div class="col-md-6">
                                 <div class="form-group">
                                    <label>Nama Lengkap</label>
                                    <input value="{{ $pegawai->nama_lengkap }}" name="nama_lengkap" type="text"
                                       class="form-control">
                                 </div>
                                 <div class="form-group">
                                    <label>Email</label>
                                    <input value="{{ $pegawai->email }}" name="email" type="text"
                                       class="form-control">
                                 </div>
                              </div>
                              <div class="col-md-6">
                                 <div class="form-group">
                                    <label>Nomor KTP</label>
                                    <input value="{{ $pegawai->no_ktp }}" name="no_ktp" type="number"
                                       class="form-control">
                                 </div>
                                 <div class="form-group">
                                    <label>NIP</label>
                                    <input value="{{ $pegawai->nip }}" name="nip" type="text" class="form-control">
                                 </div>
                              </div>
                           </div>

                           <div class="row">

                              <div class="col-md">
                                 <div class="form-group">
                                    <label>Status Kepegawaian</label>
                                    <select required name="status_kepegawaian" class="form-control select2">
                                       <option value="">-- Pilih status kepegawaian --</option>
                                       @foreach ($datas['s_kepegawaian'] as $v)
                                          <option {{ $pegawai->status_kepegawaian == $v->name ? 'selected' : '' }}
                                             value="{{ $v->name }}">{{ $v->name }}</option>
                                       @endforeach

                                    </select>
                                 </div>
                              </div>
                              <div class="col-md">
                                 <div class="form-group">
                                    <label>Tempat Lahir</label>
                                    <input value="{{ $pegawai->tempat_lahir }}" name="tempat_lahir" type="text"
                                       class="form-control">
                                 </div>
                              </div>
                              <div class="col-md">
                                 <div class="form-group">
                                    <label>Tanggal Lahir</label>
                                    <input required value="{{ $pegawai->tempat_lahir }}" name="tgl_lahir" type="date"
                                       class="form-control">
                                 </div>
                              </div>
                           </div>

                           <div class="row">

                              <div class="col-md-3">
                                 <div class="form-group">
                                    <label>Jenis Kelamin</label>
                                    <select required name="gender" class="form-control ">
                                       <option value="">-- Pilih Jenis Kelamin --</option>
                                       <option {{ $pegawai->gender == 'Laki-laki' ? 'selected' : '' }} value="Laki-laki">
                                          Laki-laki</option>
                                       <option {{ $pegawai->gender == 'Perempuan' ? 'selected' : '' }} value="Perempuan">
                                          Perempuan</option>
                                    </select>
                                 </div>
                              </div>
                              <div class="col-md">
                                 <div class="form-group">
                                    <label>Alamat Rumah</label>
                                    <input value="{{ $pegawai->alamat }}" type="text" name="alamat_rumah"
                                       class="form-control">
                                 </div>
                              </div>

                           </div>

                           <div class="row">
                              <div class="col-md-3">
                                 <div class="form-group">
                                    <label>Agama</label>
                                    <select required name="agama" class="form-control ">
                                       <option value="">-- Pilih Agama --</option>
                                       <option {{ $pegawai->agama == 'Islam' ? 'selected' : '' }} value="Islam">Islam
                                       </option>
                                       <option {{ $pegawai->agama == 'Kristen' ? 'selected' : '' }} value="Kristen">
                                          Kristen</option>
                                       <option {{ $pegawai->agama == 'Katolik' ? 'selected' : '' }} value="Katolik">
                                          Katolik</option>
                                       <option {{ $pegawai->agama == 'Hindu' ? 'selected' : '' }} value="Hindu">Hindu
                                       </option>
                                       <option {{ $pegawai->agama == 'Buddha' ? 'selected' : '' }} value="Buddha">Buddha
                                       </option>
                                    </select>
                                 </div>
                              </div>
                              <div class="col-md-3">
                                 <div class="form-group">
                                    <label>Pendidikan Terakhir</label>
                                    <select required name="pendidikan" class="form-control ">
                                       <option value="">-- Pilih pendidikan terakhir --</option>
                                       @foreach ($datas['s_gelar'] as $v)
                                          <option {{ $pegawai->pendidikan == $v->name ? 'selected' : '' }}
                                             value="{{ $v->name }}">{{ $v->name }}</option>
                                       @endforeach

                                    </select>
                                 </div>
                              </div>
                              <div class="col-md-3">
                                 <div class="form-group">
                                    <label>Satuan Pendidikan</label>
                                    <select required name="satuan_pendidikan" class="form-control select2">
                                       <option value="">-- Pilih status Satuan Pendidikan --</option>
                                       @foreach ($datas['s_kependidikan'] as $v)
                                          <option {{ $pegawai->satuan_pendidikan == $v->name ? 'selected' : '' }}
                                             value="{{ $v->name }}">{{ $v->name }}</option>
                                       @endforeach

                                    </select>
                                 </div>
                              </div>
                              <div class="col-md-3">
                                 <div class="form-group">
                                    <label>Kabupaten / Kota</label>
                                    <select required name="kabupaten" class="form-control select2">
                                       <option value="">-- Pilih Kabupaten / Kota --</option>
                                       @foreach ($datas['s_kabupaten'] as $v)
                                          <option {{ $pegawai->kabupaten == $v->name ? 'selected' : '' }}
                                             value="{{ $v->name }}">{{ $v->name }}</option>
                                       @endforeach

                                    </select>
                                 </div>
                              </div>

                           </div>


                           <div class="row">
                              {{-- <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Alamat Satuan Pendidikan</label>
                                                <input type="text" name="alamat_satuan" class="form-control">
                                            </div>
                                        </div> --}}
                              {{-- <div class="col-md-3">
                                            <div class="form-group">
                                                <label>Status</label>
                                                <select required name="status" class="form-control ">
                                                    <option value="">-- Kawin/Belum Kawin --</option>
                                                    <option value="Kawin">Kawin</option>
                                                    <option value="Belum Kawin">Belum Kawin</option>
                                                </select>
                                            </div>
                                        </div> --}}



                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label>Nomor Handphone</label>
                                    <input value="{{ $pegawai->no_hp }}" name="no_hp" type="number"
                                       class="form-control">
                                 </div>
                              </div>
                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label>Nomor Whatsapp</label>
                                    <input value="{{ $pegawai->no_wa }}" name="no_wa" type="number"
                                       class="form-control">
                                 </div>
                              </div>

                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label>Golongan / Jabatan</label>
                                    <select required name="golongan" class="form-control select2"
                                       data-select2-opts='{"tags": true}'>
                                       <option value="">-- Pilih Golongan --</option>
                                       <option {{ $pegawai->golongan == 'Golongan V' ? 'selected' : '' }}
                                          value="Golongan V">Golongan V</option>
                                       <option {{ $pegawai->golongan == 'Golongan IX' ? 'selected' : '' }}
                                          value="Golongan IX">Golongan IX</option>
                                       @foreach ($datas['golongan'] as $v)
                                          @if ($v->name != 'Golongan V' && $v->name != 'Golongan IX')
                                             <option {{ $pegawai->golongan == $v->name ? 'selected' : '' }}
                                                value="{{ $v->name }}">{{ $v->name }}</option>
                                          @endif
                                       @endforeach
                                    </select>
                                 </div>
                              </div>

                           </div>


                           <div class="row">


                              <div class="col-md-3">
                                 <div class="form-group">
                                    <label>Bank</label>
                                    <select name="jenis_bank" class="form-control" id="">
                                       <option>-- Pilih Bank --</option>
                                       <option {{ $pegawai->jenis_bank == 'Bank BCA' ? 'selected' : '' }}
                                          value="Bank BCA">Bank BCA</option>
                                       <option {{ $pegawai->jenis_bank == 'Bank BRI' ? 'selected' : '' }}
                                          value="Bank BRI">Bank BRI</option>
                                       <option {{ $pegawai->jenis_bank == 'Bank BNI' ? 'selected' : '' }}
                                          value="Bank BNI">Bank BNI</option>
                                       <option {{ $pegawai->jenis_bank == 'Bank BTN' ? 'selected' : '' }}
                                          value="Bank BTN">Bank BTN</option>
                                       <option {{ $pegawai->jenis_bank == 'Bank Mandiri' ? 'selected' : '' }}
                                          value="Bank Mandiri">Bank Mandiri</option>
                                       <option {{ $pegawai->jenis_bank == 'Bank Syariah Indonesia' ? 'selected' : '' }}
                                          value="Bank Syariah Indonesia">Bank Syariah Indonesia</option>
                                    </select>
                                 </div>
                              </div>

                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label>Nomor Rekening</label>
                                    <input value="{{ $pegawai->no_rek }}" type="number" name="no_rek"
                                       class="form-control">
                                 </div>
                              </div>
                              {{-- <div class="col-md-5">
                                            <div class="form-group">
                                                <label>Pas Foto</label>
                                                <input required type="file" name="pas_foto" class="form-control">
                                            </div>
                                        </div> --}}


                           </div>


                        </div>

                        <div class="card-footer text-right">
                           <button class="btn btn-primary " type="submit">Submit</button>
                           <button class="btn btn-secondary mx-1" type="reset">Reset</button>
                           <a href="{{ route('pegawai.show', session('no_ktp')) }}" class="btn btn-warning">Kembali</a>
                        </div>
                  </form>
               </div>

            </div>



         </div>
   </div>
   </section>
   </div>

   @push('scripts')
      <script src="{{ asset('library/select2/dist/js/select2.full.min.js') }}"></script>
   @endpush
@endsection
