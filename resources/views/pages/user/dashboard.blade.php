@extends('layouts.app', ['title' => 'Dashboard Guru'])

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Dashboard Guru</h1>
            </div>

            <div class="section-body">
                <div class="card card-primary">
                    <div class="card-body">
                        <h4>Selamat datang, {{ $guru->nama_lengkap }}</h4>
                        <p class="mb-0 text-muted">
                            {{ $guru->eksternal_jabatan ?: 'Data eksternal' }}
                            @if ($guru->satuan_pendidikan)
                                &middot; {{ $guru->satuan_pendidikan }}
                            @endif
                        </p>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-3 col-md-6">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-primary"><i class="fas fa-user-check"></i></div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>Profil</h4></div>
                                <div class="card-body">{{ $guru->is_verif === 'sudah' ? 'Terverifikasi' : 'Belum diverifikasi' }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-info"><i class="fas fa-calendar-alt"></i></div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>Kegiatan</h4></div>
                                <div class="card-body">{{ $activities->count() }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-warning"><i class="fas fa-clipboard-list"></i></div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>Assessment</h4></div>
                                <div class="card-body">{{ $assessments->count() }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-success"><i class="fas fa-file-signature"></i></div>
                            <div class="card-wrap">
                                <div class="card-header"><h4>RTL perlu tindakan</h4></div>
                                <div class="card-body">{{ $pendingRtlCount }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-7">
                        <div class="card">
                            <div class="card-header">
                                <h4>Kegiatan Saya</h4>
                                <div class="card-header-action">
                                    <a href="{{ route('user.rtl.index') }}" class="btn btn-sm btn-outline-primary">RTL & Sertifikat</a>
                                </div>
                            </div>
                            <div class="card-body">
                                @forelse ($activities->take(5) as $activity)
                                    @php($event = $activity->kegiatan)
                                    <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                        <div>
                                            <div class="font-weight-bold">{{ $event->nama_kegiatan }}</div>
                                            <small class="text-muted">{{ $event->tempat_kegiatan ?: 'Tempat belum ditentukan' }}</small>
                                        </div>
                                        <small class="text-muted text-right">
                                            {{ $event->tgl_kegiatan ? \Illuminate\Support\Carbon::parse($event->tgl_kegiatan)->format('d/m/Y') : '-' }}
                                        </small>
                                    </div>
                                @empty
                                    <p class="text-muted mb-0">Belum ada kegiatan yang terdaftar.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="card">
                            <div class="card-header"><h4>Akses Cepat</h4></div>
                            <div class="card-body">
                                <a href="{{ route('assessment.portal.dashboard') }}" class="btn btn-primary btn-block">
                                    <i class="fas fa-clipboard-list"></i> Buka Assessment
                                </a>
                                <a href="{{ route('guru.show', session('no_ktp')) }}" class="btn btn-outline-primary btn-block">
                                    <i class="fas fa-user"></i> Lihat Biodata
                                </a>
                                <a href="{{ route('guru.edit.user', $guru->id) }}" class="btn btn-outline-secondary btn-block">
                                    <i class="fas fa-edit"></i> Edit Biodata
                                </a>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header"><h4>Status RTL</h4></div>
                            <div class="card-body">
                                @forelse ($rtls->take(3) as $rtl)
                                    <div class="d-flex justify-content-between border-bottom py-2">
                                        <span>{{ $rtl->kegiatan?->nama_kegiatan ?: 'Kegiatan' }}</span>
                                        <span class="badge badge-{{ $rtl->status === 'approved' ? 'success' : ($rtl->status === 'rejected' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($rtl->status) }}
                                        </span>
                                    </div>
                                @empty
                                    <p class="text-muted mb-0">Belum ada RTL.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
