@push('scripts')
    <script>
        window.assessmentRepeaterField = function(config) {
            return {
                columns: Array.isArray(config.columns) ? config.columns : [],
                minRows: Number(config.minRows ?? 0),
                maxRows: Number(config.maxRows ?? 0),
                rows: [],

                init() {
                    const initialRows = Array.isArray(config.initialRows) ? config.initialRows : [];

                    if (initialRows.length) {
                        this.rows = initialRows.map((row, index) => this.normalizeRow(row, index));
                    } else {
                        const initialCount = Math.max(this.minRows, 1);

                        this.rows = Array.from({
                            length: initialCount
                        }, (_, index) => this.buildRow(index));
                    }
                },
                buildRow(seed = 0) {
                    const row = {
                        _key: `${Date.now()}-${Math.random()}-${seed}`,
                    };

                    this.columns.forEach((column) => {
                        row[column.nama_field] = '';
                    });

                    return row;
                },
                normalizeRow(row, seed = 0) {
                    const normalizedRow = this.buildRow(seed);

                    this.columns.forEach((column) => {
                        const value = row?.[column.nama_field];
                        normalizedRow[column.nama_field] = typeof value === 'string' || typeof value === 'number' ?
                            String(value) : '';
                    });

                    return normalizedRow;
                },
                fieldName(rowIndex, columnName) {
                    const prefix = String(config.fieldNamePrefix || 'answers');

                    return `${prefix}[${rowIndex}][${columnName}]`;
                },
                canAdd() {
                    return this.maxRows <= 0 || this.rows.length < this.maxRows;
                },
                canRemove() {
                    return this.rows.length > Math.max(this.minRows, 1);
                },
                addRow() {
                    if (!this.canAdd()) {
                        return;
                    }

                    this.rows.push(this.buildRow(this.rows.length));
                },
                removeRow(index) {
                    if (!this.canRemove()) {
                        return;
                    }

                    this.rows.splice(index, 1);
                },
            };
        };

        window.assessmentExamFlow = function(config) {
            return {
                currentAssessmentIndex: Number(config.initialIndex ?? 0),
                totalAssessments: Number(config.totalAssessments ?? 0),
                assessmentItems: Array.isArray(config.assessmentItems) ? config.assessmentItems : [],
                autosaveUrl: typeof config.autosaveUrl === 'string' ? config.autosaveUrl : '',
                resultUrl: typeof config.resultUrl === 'string' ? config.resultUrl : '',
                deadlineAt: typeof config.deadlineAt === 'string' ? config.deadlineAt : null,
                showFinishModal: false,
                isSubmitting: false,
                isAutosaving: false,
                deadlineWatcherId: null,
                deadlineSubmissionTriggered: false,

                init() {
                    this.$nextTick(() => {
                        const form = this.formElement();

                        if (!form) {
                            return;
                        }

                        ['input', 'change'].forEach((eventName) => {
                            form.addEventListener(eventName, (event) => {
                                const fieldWrapper = event.target?.closest('[data-assessment-field]');

                                if (!fieldWrapper) {
                                    return;
                                }

                                this.clearFieldError(fieldWrapper);
                            });
                        });

                        this.startDeadlineWatcher();
                    });
                },
                destroy() {
                    if (this.deadlineWatcherId) {
                        clearInterval(this.deadlineWatcherId);
                    }
                },
                formElement() {
                    return this.$refs.assessmentExamForm ?? null;
                },
                getAssessmentPanel(index) {
                    const form = this.formElement();

                    if (!form) {
                        return null;
                    }

                    return form.querySelector(`[data-assessment-panel="${index}"]`);
                },
                isBusy() {
                    return this.isSubmitting || this.isAutosaving || this.deadlineSubmissionTriggered;
                },
                openFinishModal() {
                    if (this.isBusy()) {
                        return;
                    }

                    if (!this.validateCurrentAssessment()) {
                        return;
                    }

                    this.showFinishModal = true;
                },
                submitConfirmedForm() {
                    if (this.isBusy()) {
                        return;
                    }

                    const validation = this.validateAllAssessments();

                    if (!validation.valid) {
                        this.showFinishModal = false;
                        this.currentAssessmentIndex = validation.assessmentIndex;

                        this.$nextTick(() => {
                            this.focusFieldById(validation.fieldId);
                        });

                        return;
                    }

                    const form = this.formElement();

                    if (!form) {
                        return;
                    }

                    this.isSubmitting = true;
                    form.submit();
                },
                handleSubmit() {
                    if (this.isBusy()) {
                        return;
                    }

                    if (!this.showFinishModal) {
                        this.openFinishModal();
                        return;
                    }

                    this.submitConfirmedForm();
                },
                isCurrent(index) {
                    return this.currentAssessmentIndex === index;
                },
                currentAssessmentMeta() {
                    return this.assessmentItems[this.currentAssessmentIndex] ?? {
                        index: 0,
                        form_count: 0,
                        question_count: 0,
                        field_ids: [],
                    };
                },
                isFirstAssessment() {
                    return this.currentAssessmentIndex <= 0;
                },
                isLastAssessment() {
                    return this.totalAssessments > 0
                        ? this.currentAssessmentIndex >= this.totalAssessments - 1
                        : true;
                },
                progressWidth() {
                    if (this.totalAssessments <= 0) {
                        return 0;
                    }

                    return Math.round(((this.currentAssessmentIndex + 1) / this.totalAssessments) * 100);
                },
                async goToAssessment(index) {
                    if (this.isBusy() || this.totalAssessments <= 0) {
                        return;
                    }

                    const boundedIndex = Math.max(0, Math.min(index, this.totalAssessments - 1));

                    if (boundedIndex === this.currentAssessmentIndex) {
                        return;
                    }

                    if (boundedIndex > this.currentAssessmentIndex) {
                        if (!this.validateCurrentAssessment()) {
                            return;
                        }

                        const snapshotStatus = await this.saveCurrentAssessmentSnapshot();

                        if (snapshotStatus !== 'saved') {
                            return;
                        }
                    }

                    this.currentAssessmentIndex = boundedIndex;
                    this.showFinishModal = false;

                    this.$nextTick(() => {
                        this.scrollToTop();
                    });
                },
                async saveCurrentAssessmentSnapshot() {
                    if (!this.autosaveUrl) {
                        return 'saved';
                    }

                    const form = this.formElement();
                    const currentMeta = this.currentAssessmentMeta();
                    const fieldIds = Array.isArray(currentMeta.field_ids) ? currentMeta.field_ids : [];

                    if (!form || fieldIds.length === 0) {
                        return 'saved';
                    }

                    this.clearAllFieldErrors();
                    this.isAutosaving = true;

                    try {
                        const formData = new FormData(form);
                        formData.append('active_assessment_index', String(this.currentAssessmentIndex));
                        fieldIds.forEach((fieldId) => {
                            formData.append('field_ids[]', String(fieldId));
                        });

                        const response = await fetch(this.autosaveUrl, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });
                        const payload = await this.parseJsonResponse(response);

                        if (!response.ok) {
                            if (response.status === 422 && payload?.errors) {
                                this.applyServerErrors(payload.errors);
                            } else if (payload?.message) {
                                window.alert(payload.message);
                            } else {
                                window.alert('Snapshot jawaban belum berhasil disimpan.');
                            }

                            return 'failed';
                        }

                        if (payload?.status === 'expired_submitted' && payload?.redirect_url) {
                            window.location.href = payload.redirect_url;

                            return 'expired';
                        }

                        return 'saved';
                    } catch (error) {
                        window.alert('Terjadi kendala saat menyimpan snapshot jawaban. Silakan coba lagi.');

                        return 'failed';
                    } finally {
                        this.isAutosaving = false;
                    }
                },
                async submitExpiredBecauseDeadline() {
                    if (this.deadlineSubmissionTriggered) {
                        return;
                    }

                    const form = this.formElement();

                    if (!form) {
                        return;
                    }

                    this.deadlineSubmissionTriggered = true;
                    this.isSubmitting = true;
                    this.showFinishModal = false;

                    try {
                        const formData = new FormData(form);
                        formData.append('active_assessment_index', String(this.currentAssessmentIndex));

                        const response = await fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                Accept: 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });
                        const payload = await this.parseJsonResponse(response);

                        if (payload?.redirect_url) {
                            window.location.href = payload.redirect_url;

                            return;
                        }

                        if (response.ok && this.resultUrl) {
                            window.location.href = this.resultUrl;

                            return;
                        }

                        if (payload?.errors) {
                            this.applyServerErrors(payload.errors);
                        }

                        if (this.resultUrl) {
                            window.location.href = this.resultUrl;
                            return;
                        }

                        window.location.reload();
                    } catch (error) {
                        if (this.resultUrl) {
                            window.location.href = this.resultUrl;
                            return;
                        }

                        window.location.reload();
                    } finally {
                        this.deadlineSubmissionTriggered = false;
                        this.isSubmitting = false;
                    }
                },
                startDeadlineWatcher() {
                    if (!this.deadlineAt) {
                        return;
                    }

                    const checkDeadline = () => {
                        if (Date.now() <= new Date(this.deadlineAt).getTime()) {
                            return;
                        }

                        if (this.deadlineWatcherId) {
                            clearInterval(this.deadlineWatcherId);
                        }

                        this.submitExpiredBecauseDeadline();
                    };

                    checkDeadline();
                    this.deadlineWatcherId = window.setInterval(checkDeadline, 1000);
                },
                validateCurrentAssessment() {
                    const validation = this.validateAssessment(this.currentAssessmentIndex);

                    if (validation.valid) {
                        return true;
                    }

                    this.focusFieldById(validation.fieldId);

                    return false;
                },
                validateAllAssessments() {
                    for (let assessmentIndex = 0; assessmentIndex < this.totalAssessments; assessmentIndex += 1) {
                        const validation = this.validateAssessment(assessmentIndex);

                        if (!validation.valid) {
                            return {
                                valid: false,
                                assessmentIndex,
                                fieldId: validation.fieldId,
                            };
                        }
                    }

                    return {
                        valid: true,
                        assessmentIndex: this.currentAssessmentIndex,
                        fieldId: null,
                    };
                },
                validateAssessment(index) {
                    const panel = this.getAssessmentPanel(index);

                    if (!panel) {
                        return {
                            valid: true,
                            fieldId: null,
                        };
                    }

                    const fieldWrappers = Array.from(panel.querySelectorAll('[data-assessment-field]'));

                    for (const fieldWrapper of fieldWrappers) {
                        const validation = this.validateField(fieldWrapper);

                        if (!validation.valid) {
                            return validation;
                        }
                    }

                    return {
                        valid: true,
                        fieldId: null,
                    };
                },
                validateField(fieldWrapper) {
                    this.clearFieldError(fieldWrapper);

                    const fieldId = fieldWrapper.dataset.fieldId ?? null;
                    const fieldType = fieldWrapper.dataset.fieldType ?? 'text';
                    const fieldLabel = fieldWrapper.dataset.fieldLabel ?? 'field ini';
                    const isRequired = fieldWrapper.dataset.required === '1';
                    const hasExistingFile = fieldWrapper.dataset.hasExistingFile === '1';
                    let message = null;

                    if (fieldType === 'radio') {
                        const inputs = Array.from(fieldWrapper.querySelectorAll('input[type="radio"]'));
                        const hasSelection = inputs.some((input) => input.checked);

                        if (isRequired && !hasSelection) {
                            message = `Pilih satu jawaban untuk pertanyaan ${fieldLabel}.`;
                        }
                    } else if (fieldType === 'checkbox') {
                        const inputs = Array.from(fieldWrapper.querySelectorAll('input[type="checkbox"]'));
                        const hasSelection = inputs.some((input) => input.checked);

                        if (isRequired && !hasSelection) {
                            message = `Minimal pilih satu jawaban untuk pertanyaan ${fieldLabel}.`;
                        }
                    } else if (fieldType === 'file') {
                        const input = fieldWrapper.querySelector('input[type="file"]');
                        const uploadedFile = input?.files?.[0] ?? null;

                        if (isRequired && !uploadedFile && !hasExistingFile) {
                            message = `File untuk pertanyaan ${fieldLabel} wajib diunggah.`;
                        } else if (uploadedFile && uploadedFile.size > 5 * 1024 * 1024) {
                            message = `File untuk pertanyaan ${fieldLabel} maksimal 5 MB.`;
                        }
                    } else if (fieldType === 'repeater') {
                        const repeaterInputs = Array.from(fieldWrapper.querySelectorAll('input, select, textarea'));
                        const rows = new Map();

                        repeaterInputs.forEach((input) => {
                            const name = input.getAttribute('name') || '';
                            const match = name.match(/\[(\d+)\]\[([^\]]+)\]$/);

                            if (!match) {
                                return;
                            }

                            const rowIndex = match[1];
                            const items = rows.get(rowIndex) || [];
                            items.push(input);
                            rows.set(rowIndex, items);
                        });

                        const filledRows = Array.from(rows.values()).filter((inputs) => {
                            return inputs.some((input) => String(input.value || '').trim() !== '');
                        });

                        if (isRequired && filledRows.length === 0) {
                            message = `Minimal isi satu baris pada pertanyaan ${fieldLabel}.`;
                        } else {
                            for (const [index, inputs] of Array.from(rows.entries())) {
                                const hasContent = inputs.some((input) => String(input.value || '').trim() !== '');

                                if (!hasContent) {
                                    continue;
                                }

                                const missingRequiredInput = inputs.find((input) => {
                                    return input.dataset.repeaterRequired === '1'
                                        && String(input.value || '').trim() === '';
                                });

                                if (missingRequiredInput) {
                                    const columnLabel = missingRequiredInput.dataset.repeaterLabel || 'Kolom';
                                    message = `${columnLabel} pada baris ${Number(index) + 1} untuk pertanyaan ${fieldLabel} wajib diisi.`;
                                    break;
                                }
                            }
                        }
                    } else {
                        const input = fieldType === 'textarea'
                            ? fieldWrapper.querySelector('textarea')
                            : (fieldType === 'select'
                                ? fieldWrapper.querySelector('select')
                                : fieldWrapper.querySelector('input:not([type="radio"]):not([type="checkbox"]):not([type="file"])'));

                        if (!input) {
                            return {
                                valid: true,
                                fieldId,
                            };
                        }

                        const rawValue = typeof input.value === 'string' ? input.value : '';
                        const value = rawValue.trim();

                        if (isRequired && value === '') {
                            message = `Jawaban untuk pertanyaan ${fieldLabel} wajib diisi.`;
                        } else if (fieldType === 'email' && value !== '' && !this.isValidEmail(value)) {
                            message = `Format email pada pertanyaan ${fieldLabel} tidak valid.`;
                        } else if (fieldType === 'number' && value !== '' && Number.isNaN(Number(value))) {
                            message = `Jawaban pada pertanyaan ${fieldLabel} harus berupa angka.`;
                        } else if (fieldType === 'date' && value !== '' && !this.isValidDate(value)) {
                            message = `Format tanggal pada pertanyaan ${fieldLabel} tidak valid.`;
                        }
                    }

                    if (!message) {
                        return {
                            valid: true,
                            fieldId,
                        };
                    }

                    this.setFieldError(fieldWrapper, message);

                    return {
                        valid: false,
                        fieldId,
                    };
                },
                applyServerErrors(errors) {
                    this.clearAllFieldErrors();

                    Object.entries(errors || {}).forEach(([key, messages]) => {
                        const match = String(key).match(/^answers\.(\d+)(?:\.|$)/);

                        if (!match) {
                            return;
                        }

                        const fieldId = match[1];
                        const form = this.formElement();
                        const fieldWrapper = form?.querySelector(`[data-field-id="${fieldId}"]`);

                        if (!fieldWrapper) {
                            return;
                        }

                        const message = Array.isArray(messages) ? messages[0] : messages;
                        this.setFieldError(fieldWrapper, String(message || 'Input tidak valid.'));
                    });

                    const firstKey = Object.keys(errors || {}).find((key) => /^answers\.\d+/.test(String(key)));

                    if (!firstKey) {
                        return;
                    }

                    const match = String(firstKey).match(/^answers\.(\d+)(?:\.|$)/);

                    if (!match) {
                        return;
                    }

                    this.focusFieldById(match[1]);
                },
                clearAllFieldErrors() {
                    const form = this.formElement();

                    if (!form) {
                        return;
                    }

                    form.querySelectorAll('[data-assessment-field]').forEach((fieldWrapper) => {
                        this.clearFieldError(fieldWrapper);
                    });
                },
                async parseJsonResponse(response) {
                    try {
                        return await response.json();
                    } catch (error) {
                        return null;
                    }
                },
                setFieldError(fieldWrapper, message) {
                    fieldWrapper.classList.add('border-red-500/50', 'bg-red-50/50');

                    const errorElement = fieldWrapper.querySelector('[data-field-error]');

                    if (!errorElement) {
                        return;
                    }

                    errorElement.textContent = message;
                    errorElement.classList.remove('hidden');
                },
                clearFieldError(fieldWrapper) {
                    fieldWrapper.classList.remove('border-red-500/50', 'bg-red-50/50');

                    const errorElement = fieldWrapper.querySelector('[data-field-error]');

                    if (!errorElement) {
                        return;
                    }

                    errorElement.textContent = '';
                    errorElement.classList.add('hidden');
                },
                focusFieldById(fieldId) {
                    if (!fieldId) {
                        this.scrollToTop();

                        return;
                    }

                    const form = this.formElement();

                    if (!form) {
                        return;
                    }

                    const fieldWrapper = form.querySelector(`[data-field-id="${fieldId}"]`);

                    if (!fieldWrapper) {
                        this.scrollToTop();

                        return;
                    }

                    this.scrollToElement(fieldWrapper);

                    const focusTarget = this.resolveFocusTarget(fieldWrapper);

                    if (!focusTarget) {
                        return;
                    }

                    window.setTimeout(() => {
                        focusTarget.focus({
                            preventScroll: true,
                        });
                    }, 180);
                },
                resolveFocusTarget(fieldWrapper) {
                    return fieldWrapper.querySelector('input:not([type="hidden"]), select, textarea, button');
                },
                scrollToTop() {
                    const topAnchor = this.$refs.assessmentFlowTop;

                    if (!topAnchor) {
                        return;
                    }

                    this.scrollToElement(topAnchor, 24);
                },
                scrollToElement(element, offset = 120) {
                    const top = element.getBoundingClientRect().top + window.scrollY - offset;

                    window.scrollTo({
                        top: Math.max(top, 0),
                        behavior: 'smooth',
                    });
                },
                isValidEmail(value) {
                    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
                },
                isValidDate(value) {
                    if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) {
                        return false;
                    }

                    const [year, month, day] = value.split('-').map((part) => Number(part));
                    const date = new Date(year, month - 1, day);

                    if (Number.isNaN(date.getTime())) {
                        return false;
                    }

                    return date.getFullYear() === year
                        && date.getMonth() === month - 1
                        && date.getDate() === day;
                },
            };
        };

        document.addEventListener('DOMContentLoaded', function() {
            ['wa-chat-container', 'wa-toggle-btn', 'back-to-top'].forEach(function(id) {
                document.getElementById(id)?.classList.add('hidden');
            });
        });
    </script>
@endpush
