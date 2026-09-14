@extends('layouts.app', ['title' => 'Hasil Evaluasi Pelaksanaan'])

@push('styles')
    <style>
        .evaluation-result-card {
            border: 0;
            box-shadow: 0 8px 24px rgba(40, 65, 120, .06);
        }

        .evaluation-result-card .card-body {
            padding: 1.25rem;
        }

        .evaluation-result-kpi-label {
            color: #8492a6;
            font-size: .78rem;
            letter-spacing: .02em;
            margin-bottom: .35rem;
        }

        .evaluation-result-kpi-value {
            color: #25396f;
            font-size: 1.65rem;
            font-weight: 700;
            line-height: 1.1;
        }

        .evaluation-result-filter {
            background: linear-gradient(135deg, #f7f9ff, #fff);
            border: 1px solid #e5ebfa;
        }

        .evaluation-progress {
            background: #edf1f8;
            border-radius: 1rem;
            height: .45rem;
            min-width: 90px;
            overflow: hidden;
        }

        .evaluation-progress > span {
            background: #6777ef;
            border-radius: inherit;
            display: block;
            height: 100%;
        }

        .evaluation-score-bar {
            align-items: center;
            display: flex;
            gap: .75rem;
            margin-bottom: .9rem;
        }

        .evaluation-score-bar:last-child {
            margin-bottom: 0;
        }

        .evaluation-score-bar__label {
            color: #6c7a91;
            flex: 0 0 75px;
            font-size: .85rem;
        }

        .evaluation-score-bar__value {
            color: #25396f;
            flex: 0 0 42px;
            font-size: .85rem;
            font-weight: 700;
            text-align: right;
        }

        .evaluation-score-bar .evaluation-progress {
            flex: 1 1 auto;
        }
    </style>
@endpush

@section('content')
    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Hasil Evaluasi Pelaksanaan</h1>
                <div class="section-header-breadcrumb">
                    <a href="{{ route('evaluasi.pelaksanaan.bank-soal.index') }}" class="btn btn-primary">
                        <i class="fas fa-database"></i> Bank Soal
                    </a>
                </div>
            </div>

            <div class="section-body">
                <div class="card evaluation-result-card evaluation-result-filter mb-4">
                    <div class="card-body">
                        <form method="GET" action="{{ route('evaluasi.pelaksanaan.hasil.index') }}">
                            <div class="form-row align-items-end">
                                <div class="col-md-8">
                                    <div class="form-group mb-md-0">
                                        <label for="assessment_id">Filter Bank Soal</label>
                                        <select class="form-control" name="assessment_id" id="assessment_id">
                                            <option value="0">Semua bank soal evaluasi</option>
                                            @foreach ($evaluations as $evaluation)
                                                <option value="{{ $evaluation->id }}"
                                                    {{ $selectedEvaluationId === $evaluation->id ? 'selected' : '' }}>
                                                    {{ $evaluation->judul }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-filter"></i> Terapkan Filter
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card evaluation-result-card">
                            <div class="card-body">
                                <div class="evaluation-result-kpi-label">Bank Soal</div>
                                <div class="evaluation-result-kpi-value">{{ $stats['bank_total'] }}</div>
                                <small class="text-muted">{{ $stats['form_total'] }} form tersedia</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card evaluation-result-card">
                            <div class="card-body">
                                <div class="evaluation-result-kpi-label">Peserta Evaluasi</div>
                                <div class="evaluation-result-kpi-value">{{ $stats['participant_total'] }}</div>
                                <small class="text-muted">{{ $stats['assignment_total'] }} penugasan</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card evaluation-result-card">
                            <div class="card-body">
                                <div class="evaluation-result-kpi-label">Evaluasi Selesai</div>
                                <div class="evaluation-result-kpi-value">{{ $stats['submitted_total'] }}</div>
                                <small class="text-muted">{{ $stats['completion_rate'] }}% tingkat penyelesaian</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card evaluation-result-card">
                            <div class="card-body">
                                <div class="evaluation-result-kpi-label">Rata-rata Nilai</div>
                                <div class="evaluation-result-kpi-value">
                                    {{ $stats['average_score'] !== null ? number_format($stats['average_score'], 2) : '-' }}
                                </div>
                                <small class="text-muted">Skala penilaian 1–5</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card evaluation-result-card">
                            <div class="card-header">
                                <h4>Ringkasan Penugasan</h4>
                                <div class="card-header-action">
                                    <span class="text-muted">{{ $stats['question_total'] }} pertanyaan di bank soal</span>
                                </div>
                            </div>
                            <div class="card-body">
                                @if ($assignmentRows->isEmpty())
                                    <div class="empty-state" data-height="220">
                                        <div class="empty-state-icon bg-primary"><i class="fas fa-chart-bar"></i></div>
                                        <h2>Belum ada hasil evaluasi</h2>
                                        <p class="lead">Hasil akan tampil setelah bank soal digunakan pada penugasan.</p>
                                    </div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Penugasan</th>
                                                    <th>Bank Soal</th>
                                                    <th>Peserta</th>
                                                    <th>Penyelesaian</th>
                                                    <th>Rata-rata</th>
                                                    <th></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($assignmentRows as $row)
                                                    <tr>
                                                        <td>
                                                            <div class="font-weight-bold">{{ $row['title'] }}</div>
                                                            <small class="text-muted">{{ $row['code'] }}</small>
                                                        </td>
                                                        <td>
                                                            <small>{{ \Illuminate\Support\Str::limit($row['evaluation_titles'], 42) }}</small>
                                                        </td>
                                                        <td>{{ $row['completed_total'] }}/{{ $row['target_total'] }}</td>
                                                        <td style="min-width: 125px">
                                                            <div class="d-flex justify-content-between small mb-1">
                                                                <span>{{ $row['completion_rate'] }}%</span>
                                                            </div>
                                                            <div class="evaluation-progress">
                                                                <span style="width: {{ $row['completion_rate'] }}%"></span>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            {{ $row['average_score'] !== null ? number_format($row['average_score'], 2) : '-' }}
                                                        </td>
                                                        <td class="text-right">
                                                            <a href="{{ $row['detail_url'] }}" class="btn btn-info btn-sm" title="Lihat detail">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="card evaluation-result-card">
                            <div class="card-header">
                                <h4>Distribusi Nilai</h4>
                            </div>
                            <div class="card-body">
                                @forelse ($scoreDistribution as $distribution)
                                    <div class="evaluation-score-bar">
                                        <span class="evaluation-score-bar__label">{{ $distribution['label'] }}</span>
                                        <div class="evaluation-progress">
                                            <span style="width: {{ $distribution['percent'] }}%"></span>
                                        </div>
                                        <span class="evaluation-score-bar__value">{{ $distribution['count'] }}</span>
                                    </div>
                                @empty
                                    <p class="text-muted mb-0">Belum ada nilai yang dapat dihitung.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card evaluation-result-card">
                    <div class="card-header">
                        <h4>Hasil Per Peserta</h4>
                        <div class="card-header-action">
                            <span class="text-muted">Menampilkan maksimal 25 hasil terbaru</span>
                        </div>
                    </div>
                    <div class="card-body">
                        @if ($recentResults->isEmpty())
                            <p class="text-muted mb-0">Belum ada peserta yang mengisi evaluasi.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Peserta</th>
                                            <th>Penugasan</th>
                                            <th>Status</th>
                                            <th>Nilai</th>
                                            <th>Waktu selesai</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($recentResults as $result)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    <div class="font-weight-bold">{{ $result['participant_name'] }}</div>
                                                    <small class="text-muted">{{ $result['school'] }}</small>
                                                </td>
                                                <td>
                                                    <div>{{ $result['assignment_title'] }}</div>
                                                    <small class="text-muted">{{ $result['evaluation_titles'] }}</small>
                                                </td>
                                                <td>
                                                    <span class="badge badge-{{ $result['status'] === 'submitted' ? 'success' : ($result['status'] === 'in_progress' ? 'warning' : 'secondary') }}">
                                                        {{ $result['status_label'] }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <strong>{{ $result['score_label'] }}</strong>
                                                    @if ($result['score_level'])
                                                        <small class="d-block text-muted">{{ $result['score_level'] }}</small>
                                                    @endif
                                                </td>
                                                <td>{{ $result['submitted_at'] ?: '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
