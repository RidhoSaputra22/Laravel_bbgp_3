<div class="border rounded p-3 mb-3 validator-field bg-light">
    <div class="d-flex justify-content-between mb-2">
        <strong>Butir Validasi</strong>
        <button type="button" class="btn btn-danger btn-sm remove-validator-field"><i class="fas fa-times"></i></button>
    </div>
    <div class="row">
        <div class="form-group col-md-7">
            <label>Pertanyaan / Indikator <span class="text-danger">*</span></label>
            <input type="text" class="form-control" required
                name="sections[{{ $sectionIndex }}][fields][{{ $fieldIndex }}][label]"
                value="{{ $field['label'] ?? '' }}">
        </div>
        <div class="form-group col-md-5">
            <label>Tipe Jawaban</label>
            <select class="form-control"
                name="sections[{{ $sectionIndex }}][fields][{{ $fieldIndex }}][field_type]">
                @foreach ($fieldTypes as $value => $label)
                    <option value="{{ $value }}" @selected(($field['field_type'] ?? 'likert') === $value)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group col-md-7">
            <label>Penjelasan / Kriteria</label>
            <textarea class="form-control" rows="3"
                name="sections[{{ $sectionIndex }}][fields][{{ $fieldIndex }}][description]">{{ $field['description'] ?? '' }}</textarea>
        </div>
        <div class="form-group col-md-5">
            <label>Opsi Jawaban</label>
            <textarea class="form-control" rows="3"
                name="sections[{{ $sectionIndex }}][fields][{{ $fieldIndex }}][options_text]">{{ $field['options_text'] ?? '' }}</textarea>
            <small class="text-muted">Satu opsi per baris; wajib untuk pilihan, radio, dan checkbox.</small>
        </div>
        <div class="form-group col-md-3">
            <label>Skor Maksimum</label>
            <input type="number" step="0.01" min="0.01" class="form-control"
                name="sections[{{ $sectionIndex }}][fields][{{ $fieldIndex }}][max_score]"
                value="{{ $field['max_score'] ?? '' }}">
        </div>
        <div class="form-group col-md-9 d-flex align-items-end flex-wrap">
            @foreach ([
                'is_required' => 'Wajib diisi',
                'is_scored' => 'Masuk perhitungan skor',
                'is_active' => 'Butir aktif',
            ] as $flag => $label)
                <div class="mr-4 mb-2">
                    <input type="hidden"
                        name="sections[{{ $sectionIndex }}][fields][{{ $fieldIndex }}][{{ $flag }}]" value="0">
                    <label class="custom-switch">
                        <input type="checkbox" class="custom-switch-input"
                            name="sections[{{ $sectionIndex }}][fields][{{ $fieldIndex }}][{{ $flag }}]" value="1"
                            @checked((bool) ($field[$flag] ?? false))>
                        <span class="custom-switch-indicator"></span>
                        <span class="custom-switch-description">{{ $label }}</span>
                    </label>
                </div>
            @endforeach
        </div>
    </div>
</div>
