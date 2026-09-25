@extends('layouts.app', ['title' => 'Edit Data Pegawai'])
@section('content')
   @push('styles')
      <link rel="stylesheet" href="{{ asset('library/select2/dist/css/select2.min.css') }}">
      <link rel="stylesheet" href="{{ asset('library/selectric/public/selectric.css') }}">
   @endpush

   <div class="main-content">
      <section class="section">
         <div class="section-header">
            <h1>Edit Data Pegawai </h1>
         </div>

         <div class="section-body">
            <div class="row">
               <div class="col-md-12 col-lg-12">
                  <form action="{{ route('pegawai.update', $pegawai->id) }}" method="POST" enctype="multipart/form-data">
                     @csrf
                     @method('PUT')
                     <input type="hidden" name="id" value="{{ $pegawai->id }}">
                     <div class="card">
                        <div class="card-body">
                           <div class="row">
                              <div class="col-md-6">
                                 <div class="form-group">
                                    <label>Nama Lengkap</label>
                                    <input name="nama_lengkap" value="{{ $pegawai->nama_lengkap }}" type="text"
                                       class="form-control">
                                 </div>
                                 <div class="form-group">
                                    <label>NIP</label>
                                    <input name="nip" value="{{ $pegawai->nip }}" type="text" class="form-control">
                                 </div>
                              </div>
                              <div class="col-md-6">
                                 <div class="form-group">
                                    <label>Nomor KTP</label>
                                    <input name="no_ktp" value="{{ $pegawai->no_ktp }}" type="number"
                                       class="form-control">
                                 </div>

                                 <div class="col-md">
                                    <div class="form-group">
                                       <label>Status Kepegawaian</label>
                                       <select required name="jenis_pegawai" class="form-control selectric">
                                          <option value="">-- Pilih status kepegawaian --</option>
                                          <option {{ $pegawai->jenis_pegawai == 'BBGP' ? 'selected' : '' }} value="BBGP">
                                             Pegawai BBGTK</option>
                                          <option {{ $pegawai->jenis_pegawai == 'PPNPN' ? 'selected' : '' }}
                                             value="PPNPN">Pegawai PPNPN</option>

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
                                    <select name="golongan" class="form-control select2"
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

                              <div class="col-md-4">
                                 <div class="form-group">
                                    <label>Jabatan</label>
                                    <select required name="jabatan" class="form-control select2" data-select2-opts='{"tags": true}'>
                                       <option value="">-- Pilih Jabatan --</option>
                                       @foreach ($datas['jabatan'] as $v)
                                          <option {{ $pegawai->jabatan == $v->name ? 'selected' : '' }}
                                             value="{{ $v->name }}">{{ $v->name }}</option>
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
                           <a href="{{ session('role') == 'pegawai' ? route('pegawai.show', session('no_ktp')) : route('pegawai.index') }}"
                              class="btn btn-warning">Kembali</a>
                        </div>
                     </div>
                  </form>
               </div>
            </div>
         </div>
      </section>
   </div>

   @push('scripts')
      <script src="{{ asset('library/select2/dist/js/select2.full.min.js') }}"></script>
   @endpush
@endsection
