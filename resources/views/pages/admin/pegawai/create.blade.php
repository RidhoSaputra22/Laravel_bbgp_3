@extends('layouts.app', ['title' => 'Tambah Data Pegawai'])
@section('content')
   @push('styles')
      <link rel="stylesheet" href="{{ asset('library/select2/dist/css/select2.min.css') }}">
      <link rel="stylesheet" href="{{ asset('library/selectric/public/selectric.css') }}">
   @endpush

   <div class="main-content">
      <section class="section">
         <div class="section-header">
            <h1>Tambah Data Pegawai</h1>
         </div>

         <div class="section-body">

            <div class="row">

               <div class="col-md-12 col-lg-12">
                  <form action="{{ route('pegawai.store') }}" method="POST" enctype="multipart/form-data">
                     @csrf
                     <div class="card">
                        <div class="card-body">
                           <div class="row">
                              <div class="col-md-6">
                                 <div class="form-group">
                                    <label>Nama Lengkap</label>
                                    <input name="nama_lengkap" type="text" class="form-control">
                                 </div>
                                 <div class="form-group">
                                    <label>NIP</label>
                                    <input name="nip" type="text" class="form-control">
                                 </div>
                              </div>
                              <div class="col-md-6">
                                 <div class="form-group">
                                    <label>Nomor KTP</label>
                                    <input name="no_ktp" type="number" class="form-control">
                                 </div>

                                 <div class="col-md">
                                    <div class="form-group">
                                       <label>Status Kepegawaian</label>
                                       <select required name="jenis_pegawai" class="form-control selectric">
                                          <option value="">-- Pilih status kepegawaian --</option>
                                          <option value="BBGP">Pegawai BBGTK</option>
                                          <option value="PPNPN">Pegawai PPNPN</option>

                                       </select>
                                    </div>
                                 </div>

                              </div>
                           </div>



                           <div class="row">



                           </div>


                           <div class="row">




                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label>Golongan (kosongkan jika tidak ada)</label>
                                    <select required name="golongan" class="form-control select2"
                                       data-select2-opts='{"tags": true}'>
                                       <option value="">-- Pilih Golongan --</option>
                                       <option value="Golongan V">Golongan V</option>
                                       <option value="Golongan IX">Golongan IX</option>
                                       @foreach ($datas['golongan'] as $v)
                                          @if ($v->name != 'Golongan V' && $v->name != 'Golongan IX')
                                             <option value="{{ $v->name }}">{{ $v->name }}</option>
                                          @endif
                                       @endforeach
                                    </select>
                                 </div>
                              </div>

                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label>Jabatan</label>
                                    <select required name="jabatan" class="form-control select2" data-select2-opts='{"tags": true}'>
                                       <option value="">-- Pilih Jabatan --</option>
                                       @foreach ($datas['jabatan'] as $v)
                                          <option value="{{ $v->name }}">{{ $v->name }}</option>
                                       @endforeach
                                    </select>
                                 </div>
                              </div>

                           </div>


                           <div class="row">







                           </div>


                        </div>

                        <div class="card-footer text-right">
                           <button class="btn btn-primary " type="submit">Submit</button>
                           <button class="btn btn-secondary mx-1" type="reset">Reset</button>
                           <a href="{{ route('pegawai.index') }}" class="btn btn-warning">Kembali</a>
                        </div>
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
