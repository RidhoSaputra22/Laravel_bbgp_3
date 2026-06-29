@extends('layouts.app', ['title' => 'Edit Penyewaan Ruangan'])

@section('content')
    @push('styles')
        <link rel="stylesheet" href="{{ asset('library/summernote/dist/summernote-bs4.css') }}">
        <style>
            .form-section {
                background: #f8f9fa;
                padding: 20px;
                border-radius: 8px;
                margin-bottom: 20px;
                border-left: 4px solid #6777ef;
            }

            .preview-image {
                max-width: 300px;
                margin-top: 10px;
                border-radius: 8px;
            }
        </style>
    @endpush

    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <div class="section-header-back">
                    <a href="{{ route('penyewaan.index') }}" class="btn btn-icon">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                </div>
                <h1>Edit Penyewaan Ruangan</h1>
            </div>

            <div class="section-body">
                <div class="row">
                    <div class="col-12">
                        <form action="{{ route('penyewaan.update', $data->id) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="card">
                                <div class="card-header">
                                    <h4>Form Edit Ruangan</h4>
                                </div>
                                <div class="card-body">

                                    <!-- Informasi Dasar -->
                                    <div class="form-section">
                                        <h5 class="mb-3"><i class="fas fa-info-circle mr-2"></i>Informasi Dasar</h5>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Tipe Ruangan <span class="text-danger">*</span></label>
                                                    <select name="tipe_ruangan" id="tipe_ruangan"
                                                        class="form-control @error('tipe_ruangan') is-invalid @enderror"
                                                        required>
                                                        <option value="">-- Pilih Tipe --</option>
                                                        <option value="asrama"
                                                            {{ old('tipe_ruangan', $data->tipe_ruangan) == 'asrama' ? 'selected' : '' }}>
                                                            Asrama</option>
                                                        <option value="aula"
                                                            {{ old('tipe_ruangan', $data->tipe_ruangan) == 'aula' ? 'selected' : '' }}>
                                                            Aula</option>
                                                        <option value="kelas"
                                                            {{ old('tipe_ruangan', $data->tipe_ruangan) == 'kelas' ? 'selected' : '' }}>
                                                            Kelas</option>
                                                        <option value="laboratorium"
                                                            {{ old('tipe_ruangan', $data->tipe_ruangan) == 'laboratorium' ? 'selected' : '' }}>
                                                            Laboratorium</option>
                                                    </select>
                                                    @error('tipe_ruangan')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Nama Ruangan <span class="text-danger">*</span></label>
                                                    <input type="text" name="nama_ruangan"
                                                        class="form-control @error('nama_ruangan') is-invalid @enderror"
                                                        value="{{ old('nama_ruangan', $data->nama_ruangan) }}" required>
                                                    @error('nama_ruangan')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Status <span class="text-danger">*</span></label>
                                                    <select name="status"
                                                        class="form-control @error('status') is-invalid @enderror" required>
                                                        <option value="tersedia"
                                                            {{ old('status', $data->status) == 'tersedia' ? 'selected' : '' }}>
                                                            Tersedia</option>
                                                        <option value="tidak_tersedia"
                                                            {{ old('status', $data->status) == 'tidak_tersedia' ? 'selected' : '' }}>
                                                            Tidak Tersedia</option>
                                                        <option value="maintenance"
                                                            {{ old('status', $data->status) == 'maintenance' ? 'selected' : '' }}>
                                                            Maintenance</option>
                                                    </select>
                                                    @error('status')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Status Aktif</label>
                                                    <div class="form-check mt-2">
                                                        <input type="checkbox" name="is_active" class="form-check-input"
                                                            id="is_active" value="1"
                                                            {{ old('is_active', $data->is_active) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="is_active">
                                                            Aktif (Tampilkan di website)
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Khusus Asrama -->
                                    <div class="form-section" id="asramaFields"
                                        style="display: {{ $data->tipe_ruangan == 'asrama' ? 'block' : 'none' }}">
                                        <h5 class="mb-3 text-primary">
                                            <i class="fas fa-bed mr-2"></i>Informasi Harga (Khusus Asrama)
                                        </h5>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Harga per Malam <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <span class="input-group-text">Rp</span>
                                                        </div>
                                                        <input type="number" name="harga_per_malam"
                                                            class="form-control @error('harga_per_malam') is-invalid @enderror"
                                                            value="{{ old('harga_per_malam', $data->harga_per_malam) }}"
                                                            min="0">
                                                        @error('harga_per_malam')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label>Rincian Harga</label>
                                                    <textarea name="rincian_harga" rows="4" class="form-control summernote @error('rincian_harga') is-invalid @enderror"
                                                        placeholder="Contoh: &#10;- Harga sudah termasuk listrik&#10;- Tidak termasuk makan&#10;- Deposit Rp 100.000">{{ old('rincian_harga', $data->rincian_harga) }}</textarea>
                                                    @error('rincian_harga')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Upload Foto -->
                                    <div class="form-section">
                                        <h5 class="mb-3"><i class="fas fa-image mr-2"></i>Foto Ruangan</h5>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Foto Utama</label>
                                                    <input type="file" name="foto_utama"
                                                        class="form-control @error('foto_utama') is-invalid @enderror"
                                                        accept="image/*" onchange="previewImage(event)">
                                                    <small class="text-muted">Format: JPG, PNG. Max: 2MB. Kosongkan jika
                                                        tidak ingin mengubah.</small>
                                                    @error('foto_utama')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror

                                                    @if ($data->foto_utama)
                                                        <img src="{{ asset('upload/penyewaan/' . $data->foto_utama) }}"
                                                            id="preview" class="preview-image">
                                                    @else
                                                        <img id="preview" class="preview-image" style="display: none;">
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <div class="card-footer text-right">
                                    <a href="{{ route('penyewaan.index') }}" class="btn btn-secondary mr-2">
                                        <i class="fas fa-times mr-1"></i>Batal
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save mr-1"></i>Update
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </div>

    @push('scripts')
        <script src="{{ asset('library/summernote/dist/summernote-bs4.js') }}"></script>
        <script>
            // Show/Hide Asrama Fields
            $(document).ready(function() {
                $('#tipe_ruangan').on('change', function() {
                    if ($(this).val() === 'asrama') {
                        $('#asramaFields').slideDown();
                        $('input[name="harga_per_malam"]').attr('required', true);
                    } else {
                        $('#asramaFields').slideUp();
                        $('input[name="harga_per_malam"]').attr('required', false);
                    }
                });
            });

            // Preview Image
            function previewImage(event) {
                const preview = document.getElementById('preview');
                const file = event.target.files[0];

                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }
                    reader.readAsDataURL(file);
                }
            }
        </script>
    @endpush
@endsection
