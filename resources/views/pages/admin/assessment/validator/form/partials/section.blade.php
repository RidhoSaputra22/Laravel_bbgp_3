<div class="card validator-section" data-section-index="{{ $sectionIndex }}">
    <div class="card-header">
        <h4>Bagian / Aspek QA</h4>
        <div class="card-header-action">
            <button type="button" class="btn btn-outline-danger btn-sm remove-validator-section">
                <i class="fas fa-trash"></i> Hapus Bagian
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="form-group col-md-5">
                <label>Judul Bagian <span class="text-danger">*</span></label>
                <input type="text" class="form-control" required
                    name="sections[{{ $sectionIndex }}][title]"
                    value="{{ $section['title'] ?? '' }}">
            </div>
            <div class="form-group col-md-7">
                <label>Deskripsi Bagian</label>
                <input type="text" class="form-control"
                    name="sections[{{ $sectionIndex }}][description]"
                    value="{{ $section['description'] ?? '' }}">
            </div>
        </div>
        <div class="validator-fields">
            @foreach (($section['fields'] ?? []) as $fieldIndex => $field)
                @include('pages.admin.assessment.validator.form.partials.field', compact(
                    'sectionIndex', 'fieldIndex', 'field', 'fieldTypes'
                ))
            @endforeach
        </div>
        <button type="button" class="btn btn-outline-info add-validator-field">
            <i class="fas fa-plus"></i> Tambah Butir
        </button>
    </div>
</div>
