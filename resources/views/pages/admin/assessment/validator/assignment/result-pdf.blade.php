<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Hasil Validasi {{ $assignment->code }}</title>
    <style>
        @page {
            margin: 34px 38px 46px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #000;

            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            line-height: 1.45;
        }

        h1,
        h2,
        h3,
        h4,
        p {
            margin: 0;
        }

        .page-header {
            border-bottom: 3px solid #000;
            padding-bottom: 14px;
            margin-bottom: 18px;
        }

        .brand-table,
        .meta-table,
        .summary-table,
        .source-table {
            width: 100%;
            border-collapse: collapse;
        }

        .brand-mark {
            width: 42px;
            height: 42px;
            border: 0.1px solid #000;
            background: #fff;
            color: #000;

            text-align: center;
            vertical-align: middle;
            font-size: 13px;
            font-weight: bold;
            letter-spacing: .04em;
        }

        .brand-name {
            padding-left: 10px;

            font-size: 12px;
            font-weight: bold;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .brand-subtitle {
            padding-left: 10px;

            font-size: 9px;
        }

        .document-label {

            font-size: 9px;
            text-align: right;
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .document-code {
            margin-top: 4px;

            font-size: 10px;
            font-weight: bold;
            text-align: right;
        }

        .title {
            margin-top: 19px;

            font-size: 22px;
            font-weight: bold;
        }

        .subtitle {
            margin-top: 5px;

            font-size: 10px;
        }

        .section-title {

            font-size: 13px;
            font-weight: bold;
        }

        .meta-table {
            margin-bottom: 10px;
        }




        .section-block {
            margin-top: 16px;
            border: 0;
            page-break-inside: auto;
            page-break-before: always;
        }

        .answers-title {
            margin: 0 0 10px;
            padding: 0;
            border-bottom: 0;
        }

        .section-heading {
            padding: 9px 0;

            color: #000;

        }

        .section-number {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .section-name {
            font-size: 13px;
            font-weight: bold;
        }

        .section-description {


            font-size: 9px;
        }

        .answer-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .answer-table th,
        .answer-table td {
            border: 0.1px solid #000;
            padding: 8px 9px;
            vertical-align: top;
            page-break-inside: avoid;
        }

        .answer-table th {
            background: #fff;

            font-size: 8px;
            font-weight: bold;
            letter-spacing: .04em;
            text-align: left;
            text-transform: uppercase;
        }

        .answer-table th:first-child,
        .answer-table td:first-child {
            width: 7%;
            text-align: center;
        }

        .answer-table th:nth-child(2),
        .answer-table td:nth-child(2) {
            width: 48%;
        }

        .answer-table th:nth-child(3),
        .answer-table td:nth-child(3) {
            width: 32%;
        }

        .answer-table th:last-child,
        .answer-table td:last-child {
            width: 13%;
            text-align: center;
        }

        .answer-table tbody tr:nth-child(even) {
            background: #fff;
        }


        .table-answer {

            white-space: pre-line;
        }

        .table-answer.empty {

            font-style: italic;
        }

        .table-score {
            display: inline-block;


            font-size: 8px;

        }

        .question {
            padding: 11px;
            border-top: 1px solid #000;
            page-break-inside: auto;
        }

        .question:first-child {
            border-top: none;
        }

        .question-head {
            width: 100%;
            border-collapse: collapse;
        }

        .question-head td {
            padding: 0;
            vertical-align: top;
        }

        .question-label {

            font-size: 10.5px;
            font-weight: bold;
        }

        .question-type {

            font-size: 8px;
            text-align: right;
        }

        .question-description {
            margin-top: 3px;

            font-size: 9px;
        }

        .answer-box {
            margin-top: 7px;
            min-height: 27px;
            padding: 7px 9px;
            border: 0.1px solid #000;
            background: #fff;
            page-break-inside: auto;
        }

        .answer-head {
            width: 100%;
            border-collapse: collapse;
        }

        .answer-head td {
            padding: 0;
            vertical-align: top;
        }

        .answer-label {

            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .answer-text {
            margin-top: 3px;

            white-space: pre-line;
        }

        .answer-empty {

            font-style: italic;
        }

        .score {
            display: inline-block;
            padding: 3px 6px;
            border: 0.1px solid #000;
            border-radius: 9px;
            background: #fff;

            font-size: 8px;
            font-weight: bold;
        }

        .footer {
            position: fixed;
            right: 0;
            bottom: -25px;
            left: 0;
            padding-top: 7px;
            border-top: 1px solid #000;

            font-size: 8px;
        }

        .footer .page-number:after {
            content: counter(page);
        }
    </style>
</head>

<body>
    @php
        $validatorName = $assignment->validator?->guru?->nama_lengkap
            ?? $assignment->validator?->name
            ?? data_get($assignment->validator_snapshot, 'name', '-');
        $isSubmitted = $assignment->status === 'submitted';
        $recommendation = $assignment->recommendation_label ?: 'Belum diberikan';
    @endphp

    <div class="page-header">
        <table class="brand-table">
            <tr>
                <td style="width: 136px; vertical-align: middle;">
                    <div class="brand-mark">BBGTK</div>
                </td>
                <td>
                    <div class="brand-name">BBGTK Provinsi Sulawesi Selatan</div>
                    <div class="brand-subtitle">Laporan hasil validasi instrumen assessment</div>
                </td>
                <td>
                    <div class="document-label">Dokumen hasil QA</div>
                    <div class="document-code">{{ $assignment->code }}</div>
                </td>
            </tr>
        </table>
        <h1 class="title">Hasil Validasi Assessment</h1>
        <p class="subtitle">Rekapitulasi seluruh pertanyaan, jawaban, skor, dan kesimpulan validator.</p>
    </div>

    <div class="section-title">Informasi Penugasan</div>
    <table class="meta-table">
        <tr>
            <td>
                <span class="meta-label">Judul Penugasan</span>
                <div class="meta-value">{{ $assignment->title }}</div>
            </td>
            <td>
                <span class="meta-label">Form QA</span>
                <div class="meta-value">{{ $assignment->validatorForm->title }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <span class="meta-label">Validator</span>
                <div class="meta-value">{{ $validatorName }}</div>
                @if (data_get($assignment->validator_snapshot, 'email'))
                    <div class="source-meta">{{ data_get($assignment->validator_snapshot, 'email') }}</div>
                @endif
            </td>
            <td>
                <span class="meta-label">Periode Penugasan</span>
                <div class="meta-value">
                    {{ $assignment->start_date?->format('d/m/Y') ?? '-' }}
                    s/d {{ $assignment->due_date?->format('d/m/Y') ?? '-' }}
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <span class="meta-label">Status</span>
                <div class="meta-value">
                    <span class="status {{ $isSubmitted ? '' : 'pending' }}">{{ $assignment->status_label }}</span>
                </div>
            </td>
            <td>
                <span class="meta-label">Dikirim Pada</span>
                <div class="meta-value">
                    {{ $assignment->submitted_at?->timezone('Asia/Makassar')->format('d/m/Y H:i') ?? '-' }}
                    @if ($assignment->submitted_at) WITA @endif
                </div>
            </td>
        </tr>
    </table>

    <table class="summary-table">
        <tr>
            <td>
                <div class="summary-value">{{ $answeredQuestions }}/{{ $totalQuestions }}</div>
                <div class="summary-label">Pertanyaan terjawab</div>
            </td>
            <td>
                <div class="summary-value">
                    {{ $assignment->score_percentage !== null ? number_format($assignment->score_percentage, 2).'%' : '-' }}
                </div>
                <div class="summary-label">Skor QA</div>
            </td>
            <td>
                <div class="summary-value summary-recommendation">{{ $recommendation }}</div>
                <div class="summary-label">Rekomendasi akhir</div>
            </td>
            <td>
                <div class="summary-value">{{ $generatedAt->format('d/m/Y') }}</div>
                <div class="summary-label">Tanggal dokumen</div>
            </td>
        </tr>
    </table>



    @foreach ($assignment->validatorForm->sections as $section)
        <section class="section-block">
            @if ($loop->first)
                <div class="section-title answers-title">Daftar Jawaban Validator</div>
            @endif
            <div class="section-heading">
                <div class="section-number">Bagian {{ $loop->iteration }}</div>
                <div class="section-name">{{ $section->title }}</div>
                @if ($section->description)
                    <div class="section-description">{{ $section->description }}</div>
                @endif
            </div>

            <table class="answer-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Indikator / Aspek yang Dinilai</th>
                        <th>Jawaban Validator</th>
                        <th>Skor</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($section->fields->where('is_active', true) as $field)
                        @php
                            $response = $responseLookup->get($field->id);
                            $answer = $response?->answer_text;
                            if ($field->field_type === 'checkbox' && is_array($response?->answer_payload)) {
                                $answer = collect($response->answer_payload)->implode(', ');
                            }
                            $fieldType = \App\Models\ValidatorFormField::TYPES[$field->field_type] ?? ucfirst($field->field_type);
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <div class="table-question">{{ $field->label }}</div>

                            </td>
                            <td>
                                @if (filled($answer))
                                    <div class="table-answer">{{ $answer }}</div>
                                @else
                                    <div class="table-answer empty">Belum diisi</div>
                                @endif
                            </td>
                            <td>
                                @if ($response?->score !== null)
                                    <span class="table-score">{{ $response->score }} / {{ $field->max_score }}</span>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">Tidak ada butir aktif pada bagian ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    @endforeach

    <div class="section-title">Kesimpulan Validator</div>
    <table class="meta-table">
        <tr>
            <td>
                <span class="meta-label">Rekomendasi Akhir</span>
                <div class="meta-value">{{ $recommendation }}</div>
            </td>
            <td>
                <span class="meta-label">Nilai</span>
                <div class="meta-value">
                    {{ $assignment->score_total !== null ? $assignment->score_total.' / '.$assignment->score_max : '-' }}
                </div>
            </td>
        </tr>
    </table>
    <div class="notes">
        <strong>Catatan akhir:</strong><br>{{ $assignment->final_notes ?: 'Tidak ada catatan akhir.' }}
    </div>

    <div class="footer">
        BBGTK Provinsi Sulawesi Selatan · {{ $assignment->code }}
        <span style="float: right;">Halaman <span class="page-number"></span></span>
    </div>
</body>

</html>
