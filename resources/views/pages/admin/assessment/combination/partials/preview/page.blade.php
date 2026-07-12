@php
    $assessments = collect($snapshot['assessments'] ?? [])->values();
    $generateChoiceLabel = function (int $index): string {
        $label = '';
        $number = $index + 1;

        while ($number > 0) {
            $number--;
            $label = chr(65 + ($number % 26)) . $label;
            $number = intdiv($number, 26);
        }

        return $label;
    };
@endphp

@push('styles')
    <style>
        .combination-preview-page .custom-file-label {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
@endpush

<div class="combination-preview-page">
    <div class="card card-primary shadow-sm border-0">
        <div class="card-header">
            <div>
                <h4 class="mb-1">Preview Form Child Kombinasi</h4>
                <small class="text-muted">
                    Tampilan ini merujuk pada child soal yang tersimpan di kombinasi, bukan langsung ke bank soal aktif.
                </small>
            </div>
        </div>
        <div class="card-body bg-light">
            @if ($assessments->isEmpty())
                <div class="empty-state" data-height="260">
                    <div class="empty-state-icon bg-secondary">
                        <i class="fas fa-eye-slash"></i>
                    </div>
                    <h2>Belum ada child soal pada kombinasi ini</h2>
                    <p class="lead mb-0">
                        Struktur snapshot kombinasi belum tersedia.
                    </p>
                </div>
            @else
                @foreach ($assessments as $assessment)
                    @php
                        $forms = collect($assessment['forms'] ?? [])->values();
                        $questionCount = $forms->sum(fn ($form) => count($form['fields'] ?? []));
                        $instrumentLabel = $assessment['instrument_label'] ?? (\App\Enum\AssessmentInstrumentType::tryFromMixed($assessment['instrument_type'] ?? null)?->label());
                    @endphp
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white">
                            <div class="w-100 d-flex flex-wrap justify-content-between align-items-start">
                                <div>
                                    <h4 class="mb-1">{{ $assessment['judul'] ?? 'Assessment' }}</h4>
                                    <small class="text-muted">
                                        {{ $assessment['kode_assessment'] ?? '-' }} • {{ $forms->count() }} form •
                                        {{ $questionCount }} child soal
                                    </small>
                                </div>
                                <div class="mt-2 mt-md-0">
                                    @if ($instrumentLabel)
                                        <span class="badge badge-info">{{ $instrumentLabel }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            @if (filled($assessment['deskripsi'] ?? null))
                                <p class="text-muted">{{ $assessment['deskripsi'] }}</p>
                            @endif

                            @if (filled($assessment['petunjuk'] ?? null))
                                <div class="alert alert-light border mb-4">
                                    <div class="font-weight-bold mb-1">Petunjuk</div>
                                    <div>{{ $assessment['petunjuk'] }}</div>
                                </div>
                            @endif

                            @foreach ($forms as $form)
                                @php
                                    $fields = collect($form['fields'] ?? [])->values();
                                    $kompetensiLabel = $form['kompetensi_label'] ?? (\App\Enum\KompetensiGuru::tryFromMixed($form['kompetensi'] ?? null)?->label());
                                @endphp
                                <div class="card border shadow-none mb-4">
                                    <div class="card-header bg-white">
                                        <div>
                                            <h5 class="mb-1">{{ $form['judul_form'] ?? 'Form' }}</h5>
                                            <small class="text-muted">
                                                {{ $form['kode_form'] ?? '-' }} • {{ $fields->count() }} child soal
                                            </small>
                                            <div class="mt-2">
                                                @if ($kompetensiLabel)
                                                    <span class="badge badge-info">{{ $kompetensiLabel }}</span>
                                                @endif
                                                @if (filled($form['indikator_kode'] ?? null))
                                                    <span class="badge badge-light border">
                                                        Indikator {{ $form['indikator_kode'] }}
                                                    </span>
                                                @endif
                                                <span class="badge badge-{{ !empty($form['is_scoreable']) ? 'success' : 'secondary' }}">
                                                    {{ !empty($form['is_scoreable']) ? 'Masuk penilaian' : 'Hanya pengumpulan data' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        @if (filled($form['deskripsi'] ?? null))
                                            <p class="text-muted">{{ $form['deskripsi'] }}</p>
                                        @endif

                                        <div class="row">
                                            @foreach ($fields as $field)
                                                @php
                                                    $fieldWidth = $field['lebar_kolom'] ?? 'col-md-12';
                                                    $fieldLabelId = 'combination-preview-' . ($form['id'] ?? 'form') . '-' . ($field['id'] ?? $loop->index);
                                                @endphp
                                                <div class="{{ $fieldWidth }}">
                                                    <div class="form-group">
                                                        <label for="{{ $fieldLabelId }}">
                                                            {{ $field['label'] ?? 'Field' }}
                                                            @if (!empty($field['is_required']))
                                                                <span class="text-danger">*</span>
                                                            @endif
                                                        </label>

                                                        @if (filled($field['deskripsi'] ?? null))
                                                            <small class="form-text text-muted mb-2">
                                                                {{ $field['deskripsi'] }}
                                                            </small>
                                                        @endif

                                                        @switch($field['tipe_field'] ?? 'text')
                                                            @case('textarea')
                                                                <textarea id="{{ $fieldLabelId }}" class="form-control" rows="3"
                                                                    placeholder="{{ $field['placeholder'] ?? '' }}" readonly></textarea>
                                                            @break

                                                            @case('select')
                                                                @php
                                                                    $selectPreviewOptions = \App\Support\Assessment\ChoiceFieldOtherOption::appendOption(
                                                                        $field,
                                                                        is_array($field['opsi_field'] ?? null) ? $field['opsi_field'] : [],
                                                                    );
                                                                @endphp
                                                                <select id="{{ $fieldLabelId }}" class="form-control" disabled>
                                                                    <option value="" selected>
                                                                        {{ $field['placeholder'] ?? '-- Pilih salah satu --' }}
                                                                    </option>
                                                                    @foreach ($selectPreviewOptions as $option)
                                                                        <option value="{{ is_array($option) ? ($option['value'] ?? '') : $option }}">
                                                                            {{ is_array($option) ? ($option['label'] ?? $option['value'] ?? '-') : $option }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                @if (\App\Support\Assessment\ChoiceFieldOtherOption::isEnabled($field))
                                                                    <input type="text" class="form-control mt-2"
                                                                        placeholder="Tulis jawaban lainnya" disabled>
                                                                @endif
                                                            @break

                                                            @case('radio')
                                                                @forelse (($field['opsi_field'] ?? []) as $option)
                                                                    @php
                                                                        $normalizedOption = \App\Support\Assessment\ChoiceOptionNormalizer::normalize(
                                                                            $option,
                                                                            $loop->index,
                                                                        );
                                                                        $optionCode = trim((string) ($normalizedOption['value'] ?? '')) ?: $generateChoiceLabel($loop->index);
                                                                        $optionText = trim((string) ($normalizedOption['label'] ?? '')) ?: 'Belum ada isi jawaban';
                                                                        $optionId = $fieldLabelId . '-' . $loop->index;
                                                                    @endphp
                                                                    <label for="{{ $optionId }}"
                                                                        class="d-block rounded border bg-white px-3 py-3 mb-2">
                                                                        <div class="d-flex align-items-start">
                                                                            <input type="radio" class="mt-1 mr-3"
                                                                                id="{{ $optionId }}"
                                                                                name="{{ $field['nama_field'] ?? 'field_' . ($field['id'] ?? $loop->index) }}"
                                                                                disabled>
                                                                            <div class="flex-grow-1">
                                                                                {{ $optionCode }}. {{ $optionText }}
                                                                            </div>
                                                                        </div>
                                                                    </label>
                                                                @empty
                                                                    <div class="text-muted">Belum ada opsi.</div>
                                                                @endforelse
                                                            @break

                                                            @case('checkbox')
                                                                @forelse (($field['opsi_field'] ?? []) as $option)
                                                                    @php
                                                                        $optionId = $fieldLabelId . '-' . $loop->index;
                                                                        $optionLabel = is_array($option) ? ($option['label'] ?? $option['value'] ?? '-') : $option;
                                                                    @endphp
                                                                    <div class="custom-control custom-checkbox mb-2">
                                                                        <input type="checkbox" class="custom-control-input"
                                                                            id="{{ $optionId }}" disabled>
                                                                        <label class="custom-control-label" for="{{ $optionId }}">
                                                                            {{ $optionLabel }}
                                                                        </label>
                                                                    </div>
                                                                @empty
                                                                    <div class="text-muted">Belum ada opsi.</div>
                                                                @endforelse
                                                            @break

                                                            @case('file')
                                                                @if (data_get($field, 'opsi_field.input_mode') === 'link')
                                                                    <input type="url" class="form-control"
                                                                        id="{{ $fieldLabelId }}"
                                                                        placeholder="{{ $field['placeholder'] ?? 'https://drive.google.com/file/d/.../view' }}"
                                                                        disabled>
                                                                @else
                                                                    <div class="custom-file">
                                                                        <input type="file" class="custom-file-input"
                                                                            id="{{ $fieldLabelId }}" disabled>
                                                                        <label class="custom-file-label" for="{{ $fieldLabelId }}">
                                                                            Pilih file
                                                                        </label>
                                                                    </div>
                                                                @endif
                                                            @break

                                                            @case('repeater')
                                                                @php
                                                                    $columns = collect(data_get($field, 'opsi_field.columns', []))
                                                                        ->filter(fn ($column) => is_array($column))
                                                                        ->values();
                                                                @endphp
                                                                @if ($columns->isEmpty())
                                                                    <div class="alert alert-light border mb-0">
                                                                        Konfigurasi tabel belum tersedia.
                                                                    </div>
                                                                @else
                                                                    <div class="table-responsive">
                                                                        <table class="table table-bordered">
                                                                            <thead>
                                                                                <tr>
                                                                                    @foreach ($columns as $column)
                                                                                        <th>{{ $column['label'] ?? $column['nama_field'] ?? '-' }}</th>
                                                                                    @endforeach
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                <tr>
                                                                                    @foreach ($columns as $column)
                                                                                        <td>
                                                                                            @if (($column['tipe_field'] ?? 'text') === 'select')
                                                                                                <select class="form-control" disabled>
                                                                                                    <option value="">Pilih</option>
                                                                                                    @foreach (($column['opsi_field'] ?? []) as $option)
                                                                                                        <option value="{{ $option }}">{{ $option }}</option>
                                                                                                    @endforeach
                                                                                                </select>
                                                                                            @elseif (($column['tipe_field'] ?? 'text') === 'textarea')
                                                                                                <textarea class="form-control" rows="2" disabled></textarea>
                                                                                            @else
                                                                                                <input
                                                                                                    type="{{ in_array(($column['tipe_field'] ?? 'text'), ['text', 'email', 'number', 'date', 'url'], true) ? ($column['tipe_field'] ?? 'text') : 'text' }}"
                                                                                                    class="form-control"
                                                                                                    placeholder="{{ $column['placeholder'] ?? '' }}"
                                                                                                    readonly>
                                                                                            @endif
                                                                                        </td>
                                                                                    @endforeach
                                                                                </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                @endif
                                                            @break

                                                            @default
                                                                <input
                                                                    type="{{ in_array(($field['tipe_field'] ?? 'text'), ['text', 'email', 'number', 'date'], true) ? ($field['tipe_field'] ?? 'text') : 'text' }}"
                                                                    id="{{ $fieldLabelId }}" class="form-control"
                                                                    placeholder="{{ $field['placeholder'] ?? '' }}" readonly>
                                                        @endswitch

                                                        @if (filled($field['bantuan'] ?? null))
                                                            <small class="form-text text-muted">{{ $field['bantuan'] }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
