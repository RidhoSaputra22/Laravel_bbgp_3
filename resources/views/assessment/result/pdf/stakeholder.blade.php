<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Hasil Assessment Stakeholder</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #16324f;
            font-size: 12px;
            line-height: 1.45;
            margin: 24px;
        }

        h1,
        h2,
        h3,
        h4,
        p {
            margin: 0;
        }

        .page-header {
            border-bottom: 2px solid #1376bd;
            padding-bottom: 14px;
            margin-bottom: 18px;
        }

        .eyebrow {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #1376bd;
        }

        .page-title {
            margin-top: 6px;
            font-size: 22px;
            font-weight: 700;
            color: #0d3557;
        }

        .page-subtitle {
            margin-top: 6px;
            color: #52708f;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .meta-table td {
            width: 50%;
            vertical-align: top;
            padding: 8px 10px;
            border: 1px solid #d7e4ef;
        }

        .meta-label {
            display: block;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #6c86a0;
            margin-bottom: 4px;
        }

        .meta-value {
            font-weight: 700;
            color: #16324f;
        }

        .assessment-block {
            margin-top: 22px;
        }

        .assessment-heading {
            padding: 10px 12px;
            background: #1376bd;
            color: #fff;
        }

        .assessment-title {
            font-size: 15px;
            font-weight: 700;
        }

        .assessment-meta {
            margin-top: 4px;
            font-size: 10px;
            opacity: 0.92;
        }

        .assessment-description {
            margin-top: 8px;
            color: #385675;
        }

        .form-card {
            margin-top: 14px;
            border: 1px solid #d7e4ef;
            page-break-inside: avoid;
        }

        .form-header {
            padding: 10px 12px;
            background: #f4f8fb;
            border-bottom: 1px solid #d7e4ef;
        }

        .form-title {
            font-size: 13px;
            font-weight: 700;
            color: #0d3557;
        }

        .form-meta {
            margin-top: 4px;
            font-size: 10px;
            color: #5e7893;
        }

        .form-description {
            margin-top: 6px;
            color: #4d6782;
        }

        .question {
            padding: 12px;
            border-top: 1px solid #e4edf5;
            page-break-inside: avoid;
        }

        .question:first-child {
            border-top: none;
        }

        .question-label {
            font-weight: 700;
            color: #0d3557;
        }

        .question-description,
        .question-help,
        .question-meta {
            margin-top: 4px;
            color: #617b95;
            font-size: 10px;
        }

        .answer-box {
            margin-top: 8px;
            padding: 10px;
            border: 1px solid #d7e4ef;
            background: #fcfdff;
        }

        .answer-text {
            white-space: pre-line;
        }

        .answer-empty {
            color: #7d91a6;
            font-style: italic;
        }

        .file-preview {
            margin-top: 10px;
            max-width: 220px;
            max-height: 220px;
            border: 1px solid #d7e4ef;
            padding: 4px;
            background: #fff;
        }

        .repeater-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 11px;
        }

        .repeater-table th,
        .repeater-table td {
            border: 1px solid #d7e4ef;
            padding: 6px 8px;
            vertical-align: top;
        }

        .repeater-table th {
            background: #f4f8fb;
            text-align: left;
            color: #23486d;
        }

        .empty-state {
            margin-top: 16px;
            padding: 12px;
            border: 1px dashed #c6d8e8;
            color: #617b95;
            background: #f8fbfe;
        }
    </style>
</head>

<body>
    <div class="page-header">
        <div class="eyebrow">Portal Assessment BBGTK</div>
        <div class="page-title">Hasil Assessment Stakeholder</div>
        <p class="page-subtitle">
            Dokumen ini merangkum seluruh jawaban yang telah dikirim peserta untuk form stakeholder.
        </p>
    </div>

    <table class="meta-table">
        <tr>
            <td>
                <span class="meta-label">Nama Peserta</span>
                <div class="meta-value">{{ $guru->nama_lengkap ?: '-' }}</div>
            </td>
            <td>
                <span class="meta-label">Satuan Pendidikan</span>
                <div class="meta-value">{{ $guru->satuan_pendidikan ?: '-' }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <span class="meta-label">Kode Penugasan</span>
                <div class="meta-value">{{ $target->assignment->kode_penugasan ?: '-' }}</div>
            </td>
            <td>
                <span class="meta-label">Judul Penugasan</span>
                <div class="meta-value">{{ $target->assignment->judul_penugasan ?: '-' }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <span class="meta-label">Target Ketenagaan</span>
                <div class="meta-value">{{ $targetKetenagaanLabel }}</div>
            </td>
            <td>
                <span class="meta-label">Status Pengiriman</span>
                <div class="meta-value">{{ data_get($scoringSummary, 'status_label', 'Selesai') }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <span class="meta-label">Mulai Dikerjakan</span>
                <div class="meta-value">{{ $attempt->started_at?->format('d M Y H:i') ? $attempt->started_at?->format('d M Y H:i') . ' WITA' : '-' }}</div>
            </td>
            <td>
                <span class="meta-label">Dikirim Pada</span>
                <div class="meta-value">{{ $attempt->submitted_at?->format('d M Y H:i') ? $attempt->submitted_at?->format('d M Y H:i') . ' WITA' : '-' }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <span class="meta-label">Total Soal</span>
                <div class="meta-value">{{ (int) data_get($summary, 'total_questions', 0) }}</div>
            </td>
            <td>
                <span class="meta-label">Dokumen Dibuat</span>
                <div class="meta-value">{{ $generatedAt->format('d M Y H:i') }} WITA</div>
            </td>
        </tr>
    </table>

    @forelse ($assessmentSections as $assessment)
        <section class="assessment-block">
            <div class="assessment-heading">
                <div class="assessment-title">{{ $assessment['title'] }}</div>
                <div class="assessment-meta">
                    {{ $assessment['code'] ?: 'Tanpa kode assessment' }}
                    @if (!empty($assessment['instrument_label']))
                        | {{ $assessment['instrument_label'] }}
                    @endif
                </div>
            </div>

            @if (!empty($assessment['description']))
                <p class="assessment-description">{{ $assessment['description'] }}</p>
            @endif

            @foreach ($assessment['forms'] as $form)
                <div class="form-card">
                    <div class="form-header">
                        <div class="form-title">{{ $form['title'] }}</div>
                        <div class="form-meta">
                            {{ $form['code'] ?: 'Tanpa kode form' }}
                            @if (!empty($form['competency_label']))
                                | {{ $form['competency_label'] }}
                            @endif
                            @if (!empty($form['indicator_code']))
                                | Indikator {{ $form['indicator_code'] }}
                            @endif
                            @if (!empty($form['indicator_label']))
                                | {{ $form['indicator_label'] }}
                            @endif
                        </div>
                        @if (!empty($form['description']))
                            <p class="form-description">{{ $form['description'] }}</p>
                        @endif
                    </div>

                    @foreach ($form['questions'] as $question)
                        <div class="question">
                            <div class="question-label">{{ $question['label'] ?: 'Pertanyaan' }}</div>

                            @if (!empty($question['description']))
                                <div class="question-description">{{ $question['description'] }}</div>
                            @endif

                            @if (!empty($question['help']))
                                <div class="question-help">{{ $question['help'] }}</div>
                            @endif

                            @if (!empty($question['answered_at']))
                                <div class="question-meta">Tersimpan: {{ $question['answered_at'] }} WITA</div>
                            @endif

                            <div class="answer-box">
                                @if ($question['type'] === 'repeater')
                                    @if (($question['repeater_rows'] ?? []) === [] || ($question['repeater_columns'] ?? []) === [])
                                        <div class="answer-empty">Belum ada data tabel yang dikirim peserta.</div>
                                    @else
                                        <table class="repeater-table">
                                            <thead>
                                                <tr>
                                                    @foreach ($question['repeater_columns'] as $column)
                                                        <th>{{ $column['label'] ?? 'Kolom' }}</th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($question['repeater_rows'] as $row)
                                                    <tr>
                                                        @foreach ($question['repeater_columns'] as $column)
                                                            @php
                                                                $columnKey = (string) ($column['key'] ?? $column['nama_field'] ?? '');
                                                            @endphp
                                                            <td>
                                                                {{ \App\Support\Assessment\AssessmentAnswerViewHelper::formatRepeaterCell($column, $row[$columnKey] ?? null) }}
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    @endif
                                @elseif ($question['type'] === 'file')
                                    @if (!empty($question['file_name']))
                                        <div class="answer-text">{{ $question['file_name'] }}</div>
                                    @else
                                        <div class="answer-empty">Tidak ada file yang diunggah.</div>
                                    @endif

                                    @if (!empty($question['file_url']))
                                        <div class="question-meta">Referensi file: {{ $question['file_url'] }}</div>
                                    @endif

                                    @if (!empty($question['file_preview_data_uri']))
                                        <img src="{{ $question['file_preview_data_uri'] }}" alt="Preview file"
                                            class="file-preview">
                                    @endif
                                @else
                                    @if (!empty($question['has_answer']))
                                        <div class="answer-text">{{ $question['answer_text'] }}</div>
                                    @else
                                        <div class="answer-empty">Tidak dijawab.</div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </section>
    @empty
        <div class="empty-state">
            Belum ada jawaban yang dapat ditampilkan pada hasil assessment ini.
        </div>
    @endforelse
</body>

</html>
