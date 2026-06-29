@php
    $instrumentLabel = $assessment->instrument_type
        ? \App\Enum\AssessmentInstrumentType::tryFrom($assessment->instrument_type)?->label()
        : null;
    $statusBadge =
        [
            'publish' => 'success',
            'draft' => 'warning',
            'nonaktif' => 'secondary',
        ][$assessment->status] ?? 'secondary';

    $activeForms = $assessment->forms
        ->filter(function ($form) {
            return $form->is_active && $form->fields->where('is_active', true)->isNotEmpty();
        })
        ->values();

    $activeFieldsCount = $activeForms->sum(function ($form) {
        return $form->fields->where('is_active', true)->count();
    });

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
        .assessment-preview-page .custom-file-label {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
@endpush

<div class="assessment-preview-page">
    <div class="card card-primary shadow-sm border-0">
        <div class="card-header">
            <div>
                <h4 class="mb-1">Preview Form User</h4>
                <small class="text-muted">Tampilan ini mengikuti form dan pertanyaanaktif yang akan dilihat user.</small>
            </div>
        </div>
        <div class="card-body bg-light">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <div class="row align-items-start">
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="text-muted small">Kode Assessment</div>
                            <div class="font-weight-bold">{{ $assessment->kode_assessment }}</div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="text-muted small">Slug</div>
                            <div class="font-weight-bold">{{ $assessment->slug }}</div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="text-muted small">Struktur Aktif</div>
                            <div class="font-weight-bold">{{ $activeForms->count() }} form</div>
                            <small class="text-muted">{{ $activeFieldsCount }} pertanyaanaktif</small>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="text-muted small">Status</div>
                            <div>
                                <span class="badge badge-{{ $statusBadge }}">{{ ucfirst($assessment->status) }}</span>
                                <span class="badge badge-{{ $assessment->is_active ? 'primary' : 'light' }}">
                                    {{ $assessment->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                                @if ($instrumentLabel)
                                    <span class="badge badge-info">{{ $instrumentLabel }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <h3 class="mb-2">{{ $assessment->judul }}</h3>

                    @if ($assessment->deskripsi)
                        <p class="text-muted mb-3">{{ $assessment->deskripsi }}</p>
                    @endif

                    @if ($assessment->petunjuk)
                        <div class="alert alert-light border mb-0">
                            <div class="font-weight-bold mb-1">Petunjuk Pengisian</div>
                            <div>{{ $assessment->petunjuk }}</div>
                        </div>
                    @endif
                </div>
            </div>

            @if ($activeForms->isEmpty())
                <div class="empty-state" data-height="260">
                    <div class="empty-state-icon bg-secondary">
                        <i class="fas fa-eye-slash"></i>
                    </div>
                    <h2>Belum ada form aktif untuk dipreview</h2>
                    <p class="lead mb-0">
                        Aktifkan form dan field pada halaman edit agar preview tampil untuk admin.
                    </p>
                </div>
            @else
                @foreach ($activeForms as $form)
                    @php
                        $activeFields = $form->fields->where('is_active', true)->values();
                    @endphp
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white">
                            <div>
                                <h4 class="mb-1">{{ $form->judul_form }}</h4>
                                <small class="text-muted">Bagian {{ $loop->iteration }} •
                                    {{ $form->kode_form }}</small>
                                @if ($form->kompetensi || $form->indikator_kode)
                                    <div class="mt-2">
                                        @if ($form->kompetensi)
                                            <span class="badge badge-info">
                                                {{ \App\Enum\KompetensiGuru::tryFrom($form->kompetensi)?->label() ?: ucfirst($form->kompetensi) }}
                                            </span>
                                        @endif
                                        @if ($form->indikator_kode)
                                            <span class="badge badge-light border">
                                                Indikator {{ $form->indikator_kode }}
                                            </span>
                                        @endif
                                        <span class="badge badge-{{ $form->is_scoreable ? 'success' : 'secondary' }}">
                                            {{ $form->is_scoreable ? 'Masuk penilaian' : 'Hanya pengumpulan data' }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="card-body">
                            @if ($form->deskripsi)
                                <p class="text-muted">{{ $form->deskripsi }}</p>
                            @endif

                            <div class="row">
                                @foreach ($activeFields as $field)
                                    @php
                                        $fieldWidth = $field->lebar_kolom ?: 'col-md-12';
                                        $fieldLabelId = 'preview-field-' . $form->id . '-' . $field->id;
                                    @endphp
                                    <div class="{{ $fieldWidth }}">
                                        <div class="form-group">
                                            <label for="{{ $fieldLabelId }}">
                                                {{ $field->label }}
                                                @if ($field->is_required)
                                                    <span class="text-danger">*</span>
                                                @endif
                                            </label>

                                            @if ($field->deskripsi)
                                                <small
                                                    class="form-text text-muted mb-2">{{ $field->deskripsi }}</small>
                                            @endif

                                            @switch($field->tipe_field)
                                                @case('textarea')
                                                    <textarea id="{{ $fieldLabelId }}" class="form-control" rows="3" placeholder="{{ $field->placeholder }}"
                                                        readonly></textarea>
                                                @break

                                                @case('select')
                                                    <select id="{{ $fieldLabelId }}" class="form-control" disabled>
                                                        <option value="" selected>
                                                            {{ $field->placeholder ?: '-- Pilih salah satu --' }}
                                                        </option>
                                                        @foreach ($field->opsi_field ?? [] as $option)
                                                            <option value="{{ $option }}">
                                                                {{ $option }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                @break

                                                @case('radio')
                                                    @forelse ($field->opsi_field ?? [] as $option)
                                                        @php
                                                            $normalizedOption = \App\Support\Assessment\ChoiceOptionNormalizer::normalize(
                                                                $option,
                                                                $loop->index,
                                                            );
                                                            $optionCode =
                                                                trim((string) ($normalizedOption['value'] ?? '')) ?:
                                                                $generateChoiceLabel($loop->index);
                                                            $optionText =
                                                                trim((string) ($normalizedOption['label'] ?? '')) ?:
                                                                'Belum ada isi jawaban';
                                                            $optionId = $fieldLabelId . '-' . $loop->index;
                                                        @endphp
                                                        <label for="{{ $optionId }}"
                                                            class="d-block rounded border bg-white px-3 py-3 mb-2">
                                                            <div class="d-flex align-items-start">
                                                                <input type="radio" class="mt-1 mr-3"
                                                                    id="{{ $optionId }}" name="{{ $field->nama_field }}"
                                                                    disabled>
                                                                <div class="flex-grow-1">
                                                                    <div class="space-x-2">
                                                                        {{ $optionCode }}.
                                                                        {{ $optionText }}

                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </label>
                                                    @empty
                                                        <div class="text-muted">Belum ada opsi.</div>
                                                    @endforelse
                                                @break

                                                @case('checkbox')
                                                    @forelse ($field->opsi_field ?? [] as $option)
                                                        @php
                                                            $optionId = $fieldLabelId . '-' . $loop->index;
                                                        @endphp
                                                        <div class="custom-control custom-checkbox mb-2">
                                                            <input type="checkbox" class="custom-control-input"
                                                                id="{{ $optionId }}" name="{{ $field->nama_field }}[]"
                                                                disabled>
                                                            <label class="custom-control-label" for="{{ $optionId }}">
                                                                {{ $option }}
                                                            </label>
                                                        </div>
                                                    @empty
                                                        <div class="text-muted">Belum ada opsi.</div>
                                                    @endforelse
                                                @break

                                                @case('file')
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input"
                                                            id="{{ $fieldLabelId }}" disabled>
                                                        <label class="custom-file-label" for="{{ $fieldLabelId }}">
                                                            Pilih file
                                                        </label>
                                                    </div>
                                                @break

                                                @case('repeater')
                                                    @php
                                                        $columns = collect(data_get($field->opsi_field, 'columns', []))
                                                            ->filter(fn($column) => is_array($column))
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
                                                                            <th>{{ $column['label'] ?? $column['nama_field'] }}</th>
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
                                                                                        type="{{ in_array(($column['tipe_field'] ?? 'text'), ['text', 'email', 'number', 'date'], true) ? ($column['tipe_field'] ?? 'text') : 'text' }}"
                                                                                        class="form-control" value=""
                                                                                        placeholder="{{ $column['placeholder'] ?? '' }}" readonly>
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
                                                        type="{{ in_array($field->tipe_field, ['text', 'email', 'number', 'date'], true) ? $field->tipe_field : 'text' }}"
                                                        id="{{ $fieldLabelId }}" class="form-control" value=""
                                                        placeholder="{{ $field->placeholder }}" readonly>
                                            @endswitch

                                            @if ($field->bantuan)
                                                <small class="form-text text-muted">{{ $field->bantuan }}</small>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
