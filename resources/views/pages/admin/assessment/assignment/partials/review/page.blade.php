@extends('layouts.app', ['title' => 'Detail Hasil Assessment'])

@section('content')
    @php
        $snapshot = $attempt->structure_snapshot ?? [];
        $overallLevel = data_get($scoringSummary, 'overall.level.short_label');
        $overallScore = data_get($scoringSummary, 'overall.formatted_score', '-');
        $systemScoredItems = (int) data_get($scoringSummary, 'system_scoring.completed_items', 0);
        $statusLabel = data_get($scoringSummary, 'status_label', 'Belum Ada Skor');
        $statusDescription = data_get($scoringSummary, 'status_description', '-');
        $securityEventRows = $attempt->securityEvents ?? collect();
        $seriousViolationCount = (int) ($attempt->serious_violation_count ?? 0);
        $warningViolationCount = (int) ($attempt->warning_violation_count ?? 0);
        $isDisqualified = $attempt->disqualified_at !== null;
    @endphp

    <div class="main-content">
        <section class="section">
            <div class="section-header">
                <h1>Detail Hasil Assessment</h1>
                <div class="section-header-breadcrumb">
                    <a href="{{ route('assessment.assignment.show', $target->assignment->id) }}" class="btn btn-light mr-2">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>

            <div class="section-body">
                @if (session('message') === 'auto_scoring_only')
                    <div class="alert alert-info">
                        Seluruh skor assessment dihitung otomatis oleh sistem. Halaman ini hanya menampilkan hasil dan alasan penilaian.
                    </div>
                @endif

                <div class="row">
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-primary">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Skor Umum</h4>
                                </div>
                                <div class="card-body">
                                    {{ $overallScore }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-success">
                                <i class="fas fa-layer-group"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Level Umum</h4>
                                </div>
                                <div class="card-body">
                                    {{ $overallLevel ?: '-' }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-warning">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Status Penilaian</h4>
                                </div>
                                <div class="card-body" style="font-size: 0.95rem;">
                                    {{ $statusLabel }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-12">
                        <div class="card card-statistic-1">
                            <div class="card-icon bg-danger">
                                <i class="fas fa-clipboard-list"></i>
                            </div>
                            <div class="card-wrap">
                                <div class="card-header">
                                    <h4>Dinilai Sistem</h4>
                                </div>
                                <div class="card-body">
                                    {{ $systemScoredItems }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header">
                                <h4>Ringkasan Peserta</h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <div class="text-muted small">Nama Peserta</div>
                                        <div class="font-weight-bold">{{ optional($target->guru)->nama_lengkap ?: '-' }}</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="text-muted small">Satuan Pendidikan</div>
                                        <div class="font-weight-bold">{{ optional($target->guru)->satuan_pendidikan ?: '-' }}</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="text-muted small">Kode Penugasan</div>
                                        <div class="font-weight-bold">{{ $target->assignment->kode_penugasan ?: '-' }}</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="text-muted small">Status Skor</div>
                                        <div class="font-weight-bold">{{ $statusLabel }}</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="text-muted small">Pelanggaran Serius</div>
                                        <div class="font-weight-bold">{{ $seriousViolationCount }}</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <div class="text-muted small">Warning Tidak Sengaja</div>
                                        <div class="font-weight-bold">{{ $warningViolationCount }}</div>
                                    </div>
                                </div>
                                <div class="alert alert-light border mb-0">
                                    {{ $statusDescription }}
                                </div>
                                @if ($isDisqualified)
                                    <div class="alert alert-danger mt-3 mb-0">
                                        Peserta didiskualifikasi oleh guard ujian.
                                        {{ $attempt->disqualification_reason ?: 'Tidak ada alasan tambahan yang tersimpan.' }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        @if ($securityEventRows->isNotEmpty())
                            <div class="card">
                                <div class="card-header">
                                    <h4>Log Guard Ujian</h4>
                                </div>
                                <div class="card-body table-responsive">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead>
                                            <tr>
                                                <th>Waktu</th>
                                                <th>Event</th>
                                                <th>Tipe</th>
                                                <th>Mode</th>
                                                <th>Dampak</th>
                                                <th>Pesan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($securityEventRows as $event)
                                                <tr>
                                                    <td>
                                                        {{ $event->client_occurred_at?->format('d M Y H:i:s') ?: $event->created_at?->format('d M Y H:i:s') ?: '-' }}
                                                    </td>
                                                    <td>{{ $event->event_key }}</td>
                                                    <td>{{ ucfirst($event->violation_type ?: '-') }}</td>
                                                    <td>{{ $event->lock_mode ?: '-' }}</td>
                                                    <td>
                                                        {{ $event->counts_toward_disqualify ? 'Hitung serius' : 'Warning / sistem' }}
                                                    </td>
                                                    <td>{{ $event->message }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        @foreach ($snapshot['assessments'] ?? [] as $assessment)
                            <div class="card">
                                <div class="card-header">
                                    <h4>{{ $assessment['judul'] }}</h4>
                                    <div class="text-muted">{{ $assessment['kode_assessment'] }}</div>
                                </div>
                                <div class="card-body">
                                    @if (!empty($assessment['deskripsi']))
                                        <p class="text-muted">{{ $assessment['deskripsi'] }}</p>
                                    @endif

                                    @foreach ($assessment['forms'] ?? [] as $form)
                                        <div class="border rounded p-3 mb-4">
                                            <div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
                                                <div>
                                                    <div class="font-weight-bold">{{ $form['judul_form'] }}</div>
                                                    <small class="text-muted">
                                                        {{ $form['kode_form'] ?: 'Tanpa kode form' }}
                                                        @if (!empty($form['kompetensi_label']))
                                                            • {{ $form['kompetensi_label'] }}
                                                        @endif
                                                        @if (!empty($form['indikator_kode']))
                                                            • Indikator {{ $form['indikator_kode'] }}
                                                        @endif
                                                    </small>
                                                </div>
                                            </div>

                                            @if (!empty($form['deskripsi']))
                                                <p class="text-muted">{{ $form['deskripsi'] }}</p>
                                            @endif

                                            @foreach ($form['fields'] ?? [] as $field)
                                                @php
                                                    $fieldId = (int) ($field['id'] ?? 0);
                                                    $answer = $answerLookup[$fieldId] ?? null;
                                                    $resolvedAnswerText = \App\Support\Assessment\AssessmentAnswerViewHelper::resolveAnswerText(
                                                        $field,
                                                        $answer,
                                                    );
                                                    $selectedValues = \App\Support\Assessment\AssessmentAnswerViewHelper::resolveSelectedValues(
                                                        $field,
                                                        $answer,
                                                    );
                                                    $hasAnswer = \App\Support\Assessment\AssessmentAnswerViewHelper::hasAnswer(
                                                        $field,
                                                        $answer,
                                                    );
                                                    $repeaterColumns = \App\Support\Assessment\AssessmentAnswerViewHelper::resolveRepeaterColumns(
                                                        $field,
                                                        $answer,
                                                    );
                                                    $repeaterRows = \App\Support\Assessment\AssessmentAnswerViewHelper::resolveRepeaterRows(
                                                        $answer,
                                                    );
                                                    $fieldType = $field['tipe_field'] ?? 'text';
                                                    $inputType = match ($fieldType) {
                                                        'number' => 'number',
                                                        'email' => 'email',
                                                        default => 'text',
                                                    };
                                                @endphp

                                                <div class="card shadow-none border mb-3">
                                                    <div class="card-body">
                                                        <div class="d-flex flex-wrap justify-content-between gap-2">
                                                            <div>
                                                                <div class="font-weight-bold">{{ $field['label'] }}</div>
                                                                @if (!empty($field['deskripsi']))
                                                                    <small class="text-muted">{{ $field['deskripsi'] }}</small>
                                                                @endif
                                                            </div>
                                                            <div class="text-right">
                                                                @if (!empty($answer['answered_at']))
                                                                    <small class="text-muted d-block">Tersimpan:
                                                                        {{ $answer['answered_at'] }} WITA</small>
                                                                @endif
                                                                @if ($answer && is_numeric($answer['final_score'] ?? null))
                                                                    <span class="badge badge-success d-block mt-2">
                                                                        Skor sistem: {{ number_format((float) $answer['final_score'], 2) }}
                                                                    </span>
                                                                @endif
                                                                @if (!empty($answer['final_score_label']))
                                                                    <small class="text-muted d-block mt-1">
                                                                        {{ $answer['final_score_label'] }}
                                                                    </small>
                                                                @endif
                                                                @if ($fieldType === 'radio' && data_get($answer, 'payload.level_kompetensi_label'))
                                                                    <span class="badge badge-primary">
                                                                        {{ data_get($answer, 'payload.level_kompetensi_label') }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        <div class="mt-3">
                                                            @switch($fieldType)
                                                                @case('textarea')
                                                                    <textarea class="form-control" rows="4" readonly>{{ $resolvedAnswerText }}</textarea>
                                                                @break

                                                                @case('select')
                                                                    <select class="form-control" disabled>
                                                                        @foreach ($field['opsi_field'] ?? [] as $option)
                                                                            <option value="{{ is_array($option) ? ($option['value'] ?? '') : $option }}"
                                                                                @selected(($selectedValues[0] ?? '') === (is_array($option) ? ($option['value'] ?? '') : $option))>
                                                                                {{ is_array($option) ? ($option['label'] ?? ($option['value'] ?? '')) : $option }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                @break

                                                                @case('radio')
                                                                    @foreach ($field['opsi_field'] ?? [] as $option)
                                                                        @php
                                                                            $optionValue = is_array($option)
                                                                                ? (string) ($option['value'] ?? '')
                                                                                : (string) $option;
                                                                            $optionLabel = is_array($option)
                                                                                ? ($option['label'] ?? $optionValue)
                                                                                : $optionValue;
                                                                        @endphp
                                                                        <div class="custom-control custom-radio mb-2">
                                                                            <input type="radio" class="custom-control-input"
                                                                                id="review-radio-{{ $fieldId }}-{{ $loop->index }}"
                                                                                @checked(($selectedValues[0] ?? '') === $optionValue) disabled>
                                                                            <label class="custom-control-label"
                                                                                for="review-radio-{{ $fieldId }}-{{ $loop->index }}">
                                                                                {{ $optionLabel }}
                                                                            </label>
                                                                        </div>
                                                                    @endforeach
                                                                @break

                                                                @case('checkbox')
                                                                    @foreach ($field['opsi_field'] ?? [] as $option)
                                                                        @php
                                                                            $optionValue = is_array($option)
                                                                                ? (string) ($option['value'] ?? '')
                                                                                : (string) $option;
                                                                            $optionLabel = is_array($option)
                                                                                ? ($option['label'] ?? $optionValue)
                                                                                : $optionValue;
                                                                        @endphp
                                                                        <div class="custom-control custom-checkbox mb-2">
                                                                            <input type="checkbox" class="custom-control-input"
                                                                                id="review-checkbox-{{ $fieldId }}-{{ $loop->index }}"
                                                                                @checked(in_array($optionValue, $selectedValues, true)) disabled>
                                                                            <label class="custom-control-label"
                                                                                for="review-checkbox-{{ $fieldId }}-{{ $loop->index }}">
                                                                                {{ $optionLabel }}
                                                                            </label>
                                                                        </div>
                                                                    @endforeach
                                                                @break

                                                                @case('file')
                                                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                                                        <input type="text" class="form-control"
                                                                            value="{{ $resolvedAnswerText ?: 'Tidak ada file yang diunggah' }}"
                                                                            readonly>
                                                                        @if (!empty($answer['file_url']))
                                                                            <a href="{{ $answer['file_url'] }}" class="btn btn-outline-primary mt-2"
                                                                                target="_blank" rel="noopener">
                                                                                <i class="fas fa-download"></i> Lihat File
                                                                            </a>
                                                                        @endif
                                                                    </div>
                                                                @break

                                                                @case('repeater')
                                                                    @if ($repeaterRows === [] || $repeaterColumns === [])
                                                                        <div class="alert alert-light border mb-0">
                                                                            Belum ada data tabel yang dikirim peserta.
                                                                        </div>
                                                                    @else
                                                                        <div class="table-responsive">
                                                                            <table class="table table-bordered">
                                                                                <thead>
                                                                                    <tr>
                                                                                        @foreach ($repeaterColumns as $column)
                                                                                            <th>{{ $column['label'] ?? $column['nama_field'] }}</th>
                                                                                        @endforeach
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody>
                                                                                    @foreach ($repeaterRows as $row)
                                                                                        <tr>
                                                                                            @foreach ($repeaterColumns as $column)
                                                                                                @php
                                                                                                    $cellValue = \App\Support\Assessment\AssessmentAnswerViewHelper::formatRepeaterCell(
                                                                                                        $column,
                                                                                                        $row[$column['nama_field']] ?? '',
                                                                                                    );
                                                                                                    $cellRawValue = trim((string) ($row[$column['nama_field']] ?? ''));
                                                                                                @endphp
                                                                                                <td>
                                                                                                    @if (($column['tipe_field'] ?? null) === 'url' && $cellRawValue !== '')
                                                                                                        <a href="{{ $cellRawValue }}" target="_blank" rel="noopener">
                                                                                                            {{ $cellValue }}
                                                                                                        </a>
                                                                                                    @else
                                                                                                        {{ $cellValue }}
                                                                                                    @endif
                                                                                                </td>
                                                                                            @endforeach
                                                                                        </tr>
                                                                                    @endforeach
                                                                                </tbody>
                                                                            </table>
                                                                        </div>
                                                                    @endif
                                                                @break

                                                                @default
                                                                    <input type="{{ $inputType }}" class="form-control"
                                                                        value="{{ $resolvedAnswerText }}" readonly>
                                                            @endswitch

                                                            @unless ($hasAnswer)
                                                                <small class="text-warning d-block mt-2">
                                                                    Peserta belum mengisi jawaban pada pertanyaan ini.
                                                                </small>
                                                            @endunless
                                                        </div>

                                                        @if (!empty($answer['auto_score_reason']))
                                                            <div class="alert alert-light border mt-4 mb-0">
                                                                <div class="font-weight-bold mb-1">Alasan Auto Score</div>
                                                                <div>{{ $answer['auto_score_reason'] }}</div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h4>Ringkasan Skor Kompetensi</h4>
                            </div>
                            <div class="card-body">
                                @forelse ($scoringSummary['competencies'] ?? [] as $competency)
                                    <div class="border rounded p-3 mb-3">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <div class="font-weight-bold">{{ $competency['label'] }}</div>
                                                <small class="text-muted">
                                                    {{ $competency['recommendation_category'] ?: 'Belum ada rekomendasi' }}
                                                </small>
                                            </div>
                                            <div class="text-right">
                                                <div class="font-weight-bold">{{ $competency['formatted_score'] ?: '-' }}</div>
                                                <small class="text-muted">
                                                    {{ data_get($competency, 'level.short_label', 'Belum ada level') }}
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="alert alert-light border mb-0">
                                        Ringkasan skor kompetensi belum tersedia.
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header">
                                <h4>Narasi Otomatis</h4>
                            </div>
                            <div class="card-body">
                                <p class="mb-0">{{ $scoringSummary['narrative'] ?? 'Belum ada narasi otomatis.' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
