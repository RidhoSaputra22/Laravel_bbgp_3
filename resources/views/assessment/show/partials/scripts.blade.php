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
                notifyMutation(action) {
                    this.$nextTick(() => {
                        this.$root?.dispatchEvent(new CustomEvent('assessment:repeater-mutated', {
                            bubbles: true,
                            detail: {
                                action,
                            },
                        }));
                    });
                },
                addRow() {
                    if (!this.canAdd()) {
                        return;
                    }

                    this.rows.push(this.buildRow(this.rows.length));
                    this.notifyMutation('add_row');
                },
                removeRow(index) {
                    if (!this.canRemove()) {
                        return;
                    }

                    this.rows.splice(index, 1);
                    this.notifyMutation('remove_row');
                },
            };
        };

        window.assessmentIsValidHttpUrl = function(value) {
            const normalizedValue = String(value || '').trim();

            if (!normalizedValue) {
                return false;
            }

            try {
                const parsedUrl = new URL(normalizedValue);
                return ['http:', 'https:'].includes(String(parsedUrl.protocol || '').toLowerCase());
            } catch (error) {
                return false;
            }
        };

        window.createAssessmentSecurityGuard = function(component, securityConfig) {
            const config = securityConfig && typeof securityConfig === 'object' ? securityConfig : null;

            if (!config || config.enabled !== true) {
                return {
                    init() {},
                    destroy() {},
                };
            }

            const seriousIndicators = Array.from(document.querySelectorAll('[data-security-serious-indicator]'));
            const chancesIndicators = Array.from(document.querySelectorAll('[data-security-chances-indicator]'));
            const warningIndicators = Array.from(document.querySelectorAll('[data-security-warning-indicator]'));
            const examContent = document.querySelector('[data-assessment-exam-content]');
            const warningOverlay = document.querySelector('[data-security-overlay]');
            const warningMessage = document.querySelector('[data-security-warning-message]');
            const warningType = document.querySelector('[data-security-warning-type]');
            const overlayViolations = document.querySelector('[data-security-overlay-violations]');
            const overlayChances = document.querySelector('[data-security-overlay-chances]');
            const overlayWarnings = document.querySelector('[data-security-overlay-warning-count]');
            const warningTimer = document.querySelector('[data-security-warning-timer]');
            const warningButton = document.querySelector('[data-security-warning-button]');
            const maxSeriousViolations = Math.max(1, Number(config.maxSeriousViolations ?? 3));
            const temporaryLockDurationInSeconds = Math.max(1, Number(config.temporaryLockSeconds ?? 2));
            const fullscreenGracePeriodInSeconds = Math.max(3, Number(config.fullscreenGraceSeconds ?? 10));
            const requireFullscreenMode = Boolean(config.requireFullscreen);
            let seriousViolationCount = Math.max(0, Number(config.seriousViolationCount ?? 0));
            let warningOnlyTotal = Math.max(0, Number(config.warningViolationCount ?? 0));
            let hadFullscreen = false;
            let pageWasHidden = false;
            let activeLockMode = null;
            let countdownIntervalId = null;
            let fullscreenRetryStarted = false;
            let isDisqualifying = Boolean(config.disqualified ?? false);
            let fileDialogGraceUntil = 0;
            let lastViolationFingerprint = null;
            let lastViolationAt = 0;
            let retryFullscreenHandler = null;
            const listeners = [];

            const bind = (target, eventName, handler, options = false) => {
                if (!target) {
                    return;
                }

                target.addEventListener(eventName, handler, options);
                listeners.push(() => target.removeEventListener(eventName, handler, options));
            };

            const nowIso = () => new Date().toISOString();
            const remainingSeriousChances = () => Math.max(0, maxSeriousViolations - seriousViolationCount);
            const isFullscreenActive = () => Boolean(document.fullscreenElement);
            const shouldIgnoreBecauseFileDialog = () => Date.now() < fileDialogGraceUntil;

            const syncServerState = (payload) => {
                if (!payload || typeof payload !== 'object') {
                    return;
                }

                if (Number.isFinite(Number(payload.seriousViolationCount))) {
                    seriousViolationCount = Math.max(0, Number(payload.seriousViolationCount));
                }

                if (Number.isFinite(Number(payload.warningViolationCount))) {
                    warningOnlyTotal = Math.max(0, Number(payload.warningViolationCount));
                }

                if (payload.disqualified === true) {
                    isDisqualifying = true;
                }

                if (typeof payload.disqualificationReason === 'string' && payload.disqualificationReason.trim() !== '') {
                    config.disqualificationReason = payload.disqualificationReason.trim();
                }

                updateViolationUi();
            };

            const showOverlay = () => {
                warningOverlay?.classList.remove('hidden');
                warningOverlay?.classList.add('flex');
            };

            const hideOverlay = () => {
                warningOverlay?.classList.add('hidden');
                warningOverlay?.classList.remove('flex');
            };

            const applyExamLock = () => {
                component.showFinishModal = false;
                examContent?.classList.add('pointer-events-none', 'select-none', 'blur-sm');
            };

            const removeExamLock = () => {
                examContent?.classList.remove('pointer-events-none', 'select-none', 'blur-sm');
            };

            const clearWarningTimers = () => {
                if (countdownIntervalId) {
                    window.clearInterval(countdownIntervalId);
                    countdownIntervalId = null;
                }
            };

            const redirectToSafety = (url) => {
                window.location.replace(url || config.resultUrl || window.location.href);
            };

            const updateViolationUi = () => {
                const chancesLeft = remainingSeriousChances();
                const violationText = `Pelanggaran: ${seriousViolationCount}/${maxSeriousViolations}`;
                const chancesText = `Sisa kesempatan: ${chancesLeft}`;
                const warningOnlyText = `Warning tidak sengaja: ${warningOnlyTotal}`;

                seriousIndicators.forEach((node) => {
                    node.textContent = violationText;
                });

                chancesIndicators.forEach((node) => {
                    node.textContent = chancesText;
                    node.className = `text-xs ${chancesLeft <= 1 ? 'text-red-600' : 'text-slate-500'}`;
                });

                warningIndicators.forEach((node) => {
                    node.textContent = warningOnlyText;
                });

                if (overlayViolations) {
                    overlayViolations.textContent = violationText;
                }

                if (overlayChances) {
                    overlayChances.textContent = chancesText;
                    overlayChances.className = `text-sm ${chancesLeft <= 1 ? 'text-red-600' : 'text-slate-700'}`;
                }

                if (overlayWarnings) {
                    overlayWarnings.textContent = warningOnlyText;
                }
            };

            const applyViolationType = (type, countsTowardDisqualify) => {
                if (!warningType) {
                    return;
                }

                if (type === 'intentional') {
                    warningType.textContent = countsTowardDisqualify
                        ? 'Tipe: Sengaja - dihitung sebagai pelanggaran'
                        : 'Tipe: Sengaja';
                    warningType.className = 'mt-4 text-sm font-semibold text-red-600';
                    return;
                }

                if (type === 'system') {
                    warningType.textContent = 'Tipe: Sistem Guard';
                    warningType.className = 'mt-4 text-sm font-semibold text-slate-700';
                    return;
                }

                warningType.textContent = countsTowardDisqualify
                    ? 'Tipe: Tidak Sengaja - tetap dihitung'
                    : 'Tipe: Tidak Sengaja - warning saja';
                warningType.className = 'mt-4 text-sm font-semibold text-amber-700';
            };

            const resetOverlayContent = () => {
                if (warningButton) {
                    warningButton.disabled = true;
                    warningButton.textContent = 'Tunggu...';
                }

                if (warningTimer) {
                    warningTimer.textContent = 'Mohon tunggu sebentar...';
                }

                applyViolationType('unintentional', false);
            };

            const requestFullscreen = async () => {
                if (!requireFullscreenMode || isFullscreenActive() || !document.documentElement.requestFullscreen) {
                    return;
                }

                try {
                    await document.documentElement.requestFullscreen();
                } catch (error) {
                    return;
                }
            };

            const stopFullscreenRetry = () => {
                if (!fullscreenRetryStarted || !retryFullscreenHandler) {
                    return;
                }

                fullscreenRetryStarted = false;
                document.removeEventListener('click', retryFullscreenHandler, true);
                document.removeEventListener('keydown', retryFullscreenHandler, true);
                document.removeEventListener('pointerdown', retryFullscreenHandler, true);
                document.removeEventListener('touchstart', retryFullscreenHandler, true);
                retryFullscreenHandler = null;
            };

            const startFullscreenRetry = () => {
                if (!requireFullscreenMode || fullscreenRetryStarted) {
                    return;
                }

                fullscreenRetryStarted = true;
                retryFullscreenHandler = () => {
                    void requestFullscreen();

                    if (isFullscreenActive()) {
                        stopFullscreenRetry();
                    }
                };

                document.addEventListener('click', retryFullscreenHandler, true);
                document.addEventListener('keydown', retryFullscreenHandler, true);
                document.addEventListener('pointerdown', retryFullscreenHandler, true);
                document.addEventListener('touchstart', retryFullscreenHandler, true);
            };

            const unlockExam = ({
                force = false
            } = {}) => {
                if (!examContent || !warningOverlay || isDisqualifying) {
                    return;
                }

                if (activeLockMode === 'fullscreen' && requireFullscreenMode && !isFullscreenActive() && !force) {
                    return;
                }

                activeLockMode = null;
                clearWarningTimers();
                removeExamLock();
                hideOverlay();
                resetOverlayContent();
            };

            const appendNestedFormValue = (formData, key, value) => {
                if (value === undefined || value === null) {
                    return;
                }

                if (Array.isArray(value)) {
                    value.forEach((item, index) => appendNestedFormValue(formData, `${key}[${index}]`, item));
                    return;
                }

                if (value instanceof File) {
                    formData.append(key, value);
                    return;
                }

                if (typeof value === 'object') {
                    Object.entries(value).forEach(([nestedKey, nestedValue]) => {
                        appendNestedFormValue(formData, `${key}[${nestedKey}]`, nestedValue);
                    });
                    return;
                }

                formData.append(key, String(value));
            };

            const parseJsonResponse = async (response) => {
                try {
                    return await response.json();
                } catch (error) {
                    return null;
                }
            };

            const postViolationToServer = async (payload) => {
                try {
                    const response = await fetch(config.violationUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': config.csrfToken || '',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify(payload),
                    });
                    const serverPayload = await parseJsonResponse(response);

                    if (serverPayload?.redirect_url) {
                        redirectToSafety(serverPayload.redirect_url);
                        return;
                    }

                    if (serverPayload?.status === 'expired_submitted') {
                        redirectToSafety(serverPayload.redirect_url || config.resultUrl);
                        return;
                    }

                    syncServerState(serverPayload);

                    if (serverPayload?.requires_disqualification && !isDisqualifying) {
                        void disqualifyExam(
                            serverPayload.reason || 'Assessment dihentikan oleh sistem guard karena pelanggaran aturan ujian.',
                            {
                                recordTrigger: false,
                                metadata: {
                                    source: 'server_threshold',
                                },
                            }
                        );
                    }
                } catch (error) {
                    return;
                }
            };

            const disqualifyExam = async (reason, options = {}) => {
                if (isDisqualifying) {
                    return;
                }

                isDisqualifying = true;
                activeLockMode = 'disqualified';
                clearWarningTimers();
                applyExamLock();
                showOverlay();
                updateViolationUi();

                if (warningMessage) {
                    warningMessage.textContent = reason;
                }

                applyViolationType('system', false);

                if (warningTimer) {
                    warningTimer.textContent = 'Assessment akan dihentikan dan jawaban terakhir diproses.';
                }

                if (warningButton) {
                    warningButton.disabled = true;
                    warningButton.textContent = 'Memproses...';
                }

                const form = component.formElement();
                const formData = form ? new FormData(form) : new FormData();

                if (!form && config.csrfToken) {
                    formData.append('_token', config.csrfToken);
                }

                formData.append('reason', reason);
                formData.append('record_trigger', options.recordTrigger ? '1' : '0');
                formData.append('client_occurred_at', nowIso());
                formData.append('stage_index', String(Number(component.currentAssessmentIndex ?? config.stageIndex ?? 0)));
                appendNestedFormValue(formData, 'metadata', options.metadata || {});

                if (options.triggerEvent && typeof options.triggerEvent === 'object') {
                    appendNestedFormValue(formData, 'trigger_event', options.triggerEvent);
                }

                try {
                    const response = await fetch(config.disqualifyUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                        body: formData,
                    });
                    const serverPayload = await parseJsonResponse(response);

                    if (serverPayload?.security) {
                        syncServerState(serverPayload.security);
                    }

                    redirectToSafety(serverPayload?.redirect_url || config.resultUrl);
                } catch (error) {
                    redirectToSafety(config.resultUrl);
                }
            };

            const requireFullscreen = (message = 'Mode fullscreen wajib aktif untuk melanjutkan ujian.', options = {}) => {
                if (!requireFullscreenMode || !examContent || !warningOverlay || !warningMessage) {
                    return;
                }

                clearWarningTimers();
                activeLockMode = 'fullscreen';
                warningMessage.textContent = message;
                applyViolationType(
                    options.type ?? 'unintentional',
                    options.countsTowardDisqualify ?? false,
                );
                applyExamLock();
                showOverlay();

                let remainingSeconds = fullscreenGracePeriodInSeconds;

                const updateFullscreenWarning = () => {
                    if (warningTimer) {
                        warningTimer.textContent = `Kembali ke mode fullscreen dalam ${remainingSeconds} detik atau ujian akan dihentikan.`;
                    }

                    if (warningButton) {
                        warningButton.disabled = false;
                        warningButton.textContent = `Aktifkan Fullscreen (${remainingSeconds})`;
                    }
                };

                updateFullscreenWarning();
                void requestFullscreen();
                startFullscreenRetry();

                countdownIntervalId = window.setInterval(() => {
                    if (isFullscreenActive()) {
                        unlockExam({
                            force: true
                        });
                        return;
                    }

                    remainingSeconds -= 1;

                    if (remainingSeconds <= 0) {
                        void disqualifyExam(
                            'Anda didiskualifikasi karena tidak kembali ke mode fullscreen dalam batas waktu guard.',
                            {
                                recordTrigger: true,
                                triggerEvent: {
                                    event_key: 'fullscreen_timeout',
                                    message,
                                    type: 'system',
                                    mode: 'fullscreen',
                                    client_occurred_at: nowIso(),
                                    metadata: {
                                        reason: 'fullscreen_timeout',
                                    },
                                },
                                metadata: {
                                    source: 'fullscreen_timeout',
                                },
                            }
                        );
                        return;
                    }

                    updateFullscreenWarning();
                }, 1000);
            };

            const temporaryLockModeMessage = (remainingSeconds) => {
                if (warningType?.textContent?.includes('Sengaja')) {
                    return `Pelanggaran sengaja tercatat. Laman ujian dikunci sementara selama ${remainingSeconds} detik.`;
                }

                return `Warning tercatat. Laman ujian dikunci sementara selama ${remainingSeconds} detik.`;
            };

            const startWarningCountdown = () => {
                if (!warningTimer || !warningButton) {
                    return;
                }

                clearWarningTimers();
                let remainingSeconds = temporaryLockDurationInSeconds;

                warningButton.disabled = true;
                warningButton.textContent = `Tunggu ${remainingSeconds} detik`;
                warningTimer.textContent = temporaryLockModeMessage(remainingSeconds);

                countdownIntervalId = window.setInterval(() => {
                    remainingSeconds -= 1;

                    if (remainingSeconds <= 0) {
                        if (!requireFullscreenMode || isFullscreenActive()) {
                            unlockExam({
                                force: true
                            });
                        } else {
                            requireFullscreen('Kembali ke mode fullscreen untuk melanjutkan ujian.');
                        }
                        return;
                    }

                    warningButton.textContent = `Tunggu ${remainingSeconds} detik`;
                    warningTimer.textContent = temporaryLockModeMessage(remainingSeconds);
                }, 1000);
            };

            const registerViolation = ({
                eventKey,
                message,
                mode = 'temporary',
                type = 'unintentional',
                metadata = {},
            }) => {
                if (isDisqualifying) {
                    return;
                }

                const now = Date.now();
                const fingerprint = `${eventKey}:${type}:${mode}:${message}`;

                if (lastViolationFingerprint === fingerprint && now - lastViolationAt < 400) {
                    return;
                }

                lastViolationFingerprint = fingerprint;
                lastViolationAt = now;

                const effectiveMode = !requireFullscreenMode && mode === 'fullscreen'
                    ? 'temporary'
                    : mode;
                const countsTowardDisqualify = type === 'intentional';
                const violationPayload = {
                    event_key: eventKey,
                    message,
                    type,
                    mode: effectiveMode,
                    stage_index: Number(component.currentAssessmentIndex ?? config.stageIndex ?? 0),
                    client_occurred_at: nowIso(),
                    metadata,
                };

                if (type === 'intentional') {
                    seriousViolationCount = Math.min(maxSeriousViolations, seriousViolationCount + 1);
                } else if (type === 'unintentional') {
                    warningOnlyTotal += 1;
                }

                updateViolationUi();

                if (countsTowardDisqualify && seriousViolationCount >= maxSeriousViolations) {
                    void disqualifyExam(
                        'Anda didiskualifikasi karena telah melakukan pelanggaran serius berulang selama ujian.',
                        {
                            recordTrigger: true,
                            triggerEvent: violationPayload,
                            metadata: {
                                source: 'local_threshold',
                            },
                        }
                    );
                    return;
                }

                void postViolationToServer(violationPayload);

                const chancesLeft = remainingSeriousChances();
                const violationSummary = countsTowardDisqualify
                    ? `${message} Ini termasuk pelanggaran sengaja ke-${seriousViolationCount} dari ${maxSeriousViolations}. Sisa kesempatan anda ${chancesLeft}.`
                    : `${message} Ini termasuk pelanggaran tidak sengaja. Sistem hanya memberi warning dan tidak mengurangi kesempatan anda.`;

                if (activeLockMode === 'fullscreen') {
                    if (warningMessage) {
                        warningMessage.textContent = violationSummary;
                    }

                    applyViolationType(type, countsTowardDisqualify);
                    showOverlay();
                    void requestFullscreen();
                    return;
                }

                if (effectiveMode === 'fullscreen' && activeLockMode === 'temporary') {
                    requireFullscreen(violationSummary, {
                        type,
                        countsTowardDisqualify,
                    });
                    return;
                }

                if (activeLockMode === 'temporary') {
                    if (warningMessage) {
                        warningMessage.textContent = violationSummary;
                    }

                    applyViolationType(type, countsTowardDisqualify);
                    showOverlay();
                    return;
                }

                if (effectiveMode === 'fullscreen') {
                    requireFullscreen(violationSummary, {
                        type,
                        countsTowardDisqualify,
                    });
                    return;
                }

                if (!examContent || !warningOverlay || !warningMessage) {
                    return;
                }

                activeLockMode = 'temporary';
                warningMessage.textContent = violationSummary;
                applyViolationType(type, countsTowardDisqualify);
                applyExamLock();
                showOverlay();
                startWarningCountdown();
            };

            const preventAndWarn = (event, message, options = {}) => {
                event.preventDefault();
                event.stopPropagation();
                event.stopImmediatePropagation?.();

                if (event.repeat) {
                    return;
                }

                registerViolation({
                    message,
                    ...options,
                });
            };

            const isModifierPressed = (event) => event.ctrlKey || event.metaKey;

            const blockNavigationShortcut = (event) => {
                const key = String(event.key || '').toLowerCase();
                const modifierPressed = isModifierPressed(event);
                const intentionalMessage =
                    'Aksi tersebut diblokir karena terindikasi upaya akses yang tidak diperbolehkan.';
                const warningMessageText =
                    'Tombol kombinasi tersebut diblokir. Tetap di laman ujian sampai sesi selesai.';

                if (event.key === 'F5' || event.key === 'F11' || event.key === 'F12' || key === 'printscreen') {
                    return preventAndWarn(
                        event,
                        event.key === 'F12' || key === 'printscreen' ? intentionalMessage : warningMessageText,
                        {
                            eventKey: event.key === 'F12' || key === 'printscreen'
                                ? 'blocked_sensitive_shortcut'
                                : 'blocked_refresh_shortcut',
                            type: event.key === 'F12' || key === 'printscreen' ? 'intentional' : 'unintentional',
                        },
                    );
                }

                if (event.altKey && ['tab', 'f4', 'arrowleft', 'arrowright'].includes(key)) {
                    return preventAndWarn(event, warningMessageText, {
                        eventKey: 'blocked_alt_navigation_shortcut',
                        type: 'unintentional',
                    });
                }

                if (event.shiftKey && modifierPressed && ['i', 'j', 'c', 'k', 's'].includes(key)) {
                    return preventAndWarn(event, intentionalMessage, {
                        eventKey: 'blocked_devtools_shortcut',
                        type: 'intentional',
                    });
                }

                if (modifierPressed && ['u', 't', 'n', 'w', 'r', 'p', 's', 'c', 'v', 'x'].includes(key)) {
                    return preventAndWarn(
                        event,
                        ['c', 'v', 'x'].includes(key) ? intentionalMessage : warningMessageText,
                        {
                            eventKey: ['c', 'v', 'x'].includes(key)
                                ? 'blocked_clipboard_shortcut'
                                : 'blocked_navigation_shortcut',
                            type: ['c', 'v', 'x'].includes(key) ? 'intentional' : 'unintentional',
                        },
                    );
                }

                if (modifierPressed && event.altKey && key === 'delete') {
                    return preventAndWarn(event, warningMessageText, {
                        eventKey: 'blocked_system_shortcut',
                        type: 'unintentional',
                    });
                }

                if (event.ctrlKey && event.shiftKey && key === 'escape') {
                    return preventAndWarn(event, intentionalMessage, {
                        eventKey: 'blocked_task_manager_shortcut',
                        type: 'intentional',
                    });
                }
            };

            const blockMouseAndClipboardActions = (event) => {
                const blockedEvents = {
                    contextmenu: {
                        eventKey: 'blocked_context_menu',
                        message: 'Klik kanan dinonaktifkan selama ujian berlangsung.',
                        type: 'unintentional',
                    },
                    copy: {
                        eventKey: 'blocked_copy',
                        message: 'Copy dinonaktifkan selama ujian berlangsung.',
                        type: 'intentional',
                    },
                    cut: {
                        eventKey: 'blocked_cut',
                        message: 'Cut dinonaktifkan selama ujian berlangsung.',
                        type: 'intentional',
                    },
                    paste: {
                        eventKey: 'blocked_paste',
                        message: 'Paste dinonaktifkan selama ujian berlangsung.',
                        type: 'intentional',
                    },
                    dragstart: {
                        eventKey: 'blocked_dragstart',
                        message: 'Drag dinonaktifkan selama ujian berlangsung.',
                        type: 'unintentional',
                    },
                };

                if (!blockedEvents[event.type]) {
                    return;
                }

                preventAndWarn(event, blockedEvents[event.type].message, blockedEvents[event.type]);
            };

            const armFileDialogGrace = () => {
                fileDialogGraceUntil = Date.now() + 30000;
            };

            const handleFileInputPointer = (event) => {
                const target = event.target;

                if (target instanceof HTMLInputElement && target.type === 'file') {
                    armFileDialogGrace();
                }
            };

            const handleFileInputKey = (event) => {
                const target = event.target;

                if (
                    target instanceof HTMLInputElement &&
                    target.type === 'file' &&
                    ['enter', ' '].includes(String(event.key || '').toLowerCase())
                ) {
                    armFileDialogGrace();
                }
            };

            const handleFileInputChange = (event) => {
                const target = event.target;

                if (target instanceof HTMLInputElement && target.type === 'file') {
                    fileDialogGraceUntil = 0;
                }
            };

            return {
                init() {
                    if (isDisqualifying) {
                        redirectToSafety(config.resultUrl);
                        return;
                    }

                    history.pushState(null, '', window.location.href);
                    updateViolationUi();
                    resetOverlayContent();

                    if (requireFullscreenMode) {
                        void requestFullscreen();
                        startFullscreenRetry();

                        window.setTimeout(() => {
                            if (!isFullscreenActive()) {
                                requireFullscreen('Ujian hanya bisa dikerjakan dalam mode fullscreen.');
                            }
                        }, 300);
                    }

                    bind(document, 'keydown', blockNavigationShortcut, true);
                    bind(document, 'contextmenu', blockMouseAndClipboardActions, true);
                    bind(document, 'copy', blockMouseAndClipboardActions, true);
                    bind(document, 'cut', blockMouseAndClipboardActions, true);
                    bind(document, 'paste', blockMouseAndClipboardActions, true);
                    bind(document, 'dragstart', blockMouseAndClipboardActions, true);
                    bind(document, 'click', handleFileInputPointer, true);
                    bind(document, 'keydown', handleFileInputKey, true);
                    bind(document, 'change', handleFileInputChange, true);

                    bind(warningButton, 'click', async () => {
                        if (isDisqualifying) {
                            return;
                        }

                        if (activeLockMode === 'fullscreen') {
                            await requestFullscreen();

                            if (isFullscreenActive()) {
                                unlockExam({
                                    force: true
                                });
                            }

                            return;
                        }

                        if (!requireFullscreenMode) {
                            unlockExam({
                                force: true
                            });
                            return;
                        }

                        if (isFullscreenActive()) {
                            return;
                        }

                        requireFullscreen('Mode fullscreen wajib aktif untuk melanjutkan ujian.');
                    });

                    bind(window, 'popstate', () => {
                        history.pushState(null, '', window.location.href);
                        registerViolation({
                            eventKey: 'blocked_history_navigation',
                            message: 'Anda mencoba keluar dari halaman ujian.',
                            mode: requireFullscreenMode && !isFullscreenActive() ? 'fullscreen' : 'temporary',
                            type: 'unintentional',
                        });
                    });

                    bind(document, 'visibilitychange', () => {
                        if (document.visibilityState === 'hidden') {
                            pageWasHidden = true;
                            return;
                        }

                        if (!pageWasHidden) {
                            return;
                        }

                        pageWasHidden = false;

                        if (shouldIgnoreBecauseFileDialog()) {
                            fileDialogGraceUntil = 0;
                            return;
                        }

                        registerViolation({
                            eventKey: 'visibility_focus_loss',
                            message: 'Anda terdeteksi keluar dari fokus ujian.',
                            mode: requireFullscreenMode && !isFullscreenActive() ? 'fullscreen' : 'temporary',
                            type: 'unintentional',
                        });
                    });

                    bind(window, 'focus', () => {
                        if (requireFullscreenMode) {
                            void requestFullscreen();
                        }

                        if (shouldIgnoreBecauseFileDialog()) {
                            fileDialogGraceUntil = 0;

                            if (activeLockMode === 'fullscreen' && (!requireFullscreenMode || isFullscreenActive())) {
                                unlockExam({
                                    force: true
                                });
                            }

                            return;
                        }

                        if (requireFullscreenMode && !isFullscreenActive()) {
                            requireFullscreen('Kembali ke mode fullscreen untuk melanjutkan ujian.');
                            return;
                        }

                        if (activeLockMode === 'fullscreen') {
                            unlockExam({
                                force: true
                            });
                        }
                    });

                    bind(document, 'fullscreenchange', () => {
                        if (!requireFullscreenMode) {
                            return;
                        }

                        if (isFullscreenActive()) {
                            hadFullscreen = true;

                            if (activeLockMode === 'fullscreen') {
                                unlockExam({
                                    force: true
                                });
                            }

                            return;
                        }

                        if (shouldIgnoreBecauseFileDialog()) {
                            return;
                        }

                        if (hadFullscreen || activeLockMode === 'fullscreen') {
                            registerViolation({
                                eventKey: 'fullscreen_exit',
                                message: 'Mode fullscreen dimatikan.',
                                mode: 'fullscreen',
                                type: 'unintentional',
                            });
                        }
                    });
                },
                destroy() {
                    clearWarningTimers();
                    stopFullscreenRetry();
                    listeners.splice(0).forEach((cleanup) => cleanup());
                },
            };
        };

        window.assessmentExamFlow = function(config) {
            return {
                currentAssessmentIndex: Number(config.initialIndex ?? 0),
                totalAssessments: Number(config.totalAssessments ?? 0),
                assessmentItems: Array.isArray(config.assessmentItems) ? config.assessmentItems : [],
                questionItems: Array.isArray(config.questionItems) ? config.questionItems : [],
                stageFlowEnabled: Boolean(config.stageFlowEnabled ?? false),
                flaggedFieldIds: [],
                currentQuestionFieldId: Number(config.initialQuestionFieldId ?? 0),
                questionStateByFieldId: {},
                autosaveUrl: typeof config.autosaveUrl === 'string' ? config.autosaveUrl : '',
                resultUrl: typeof config.resultUrl === 'string' ? config.resultUrl : '',
                deadlineAt: typeof config.deadlineAt === 'string' ? config.deadlineAt : null,
                textareaWordLimits: config.textareaWordLimits && typeof config.textareaWordLimits === 'object'
                    ? config.textareaWordLimits
                    : {},
                securityConfig: config.security && typeof config.security === 'object' ? config.security : null,
                securityGuard: null,
                showFinishModal: false,
                isSubmitting: false,
                isAutosaving: false,
                autosaveActionThreshold: Math.max(1, Number(config.autosaveActionThreshold ?? 3)),
                autosaveActionCount: 0,
                autosaveBucket: null,
                autosaveTraceSequence: 0,
                autosaveQueued: false,
                autosaveQueuedReason: null,
                deadlineWatcherId: null,
                deadlineSubmissionTriggered: false,

                init() {
                    this.flaggedFieldIds = this.normalizeFieldIdList(config.initialFlaggedFieldIds ?? []);
                    this.autosaveBucket = this.createAutosaveBucket();

                    this.$nextTick(() => {
                        const form = this.formElement();

                        if (!form) {
                            return;
                        }

                        ['input', 'change'].forEach((eventName) => {
                            form.addEventListener(eventName, (event) => {
                                this.normalizeNumberInput(event.target);
                            }, true);
                        });

                        ['input', 'change'].forEach((eventName) => {
                            form.addEventListener(eventName, (event) => {
                                const fieldWrapper = event.target?.closest('[data-assessment-field]');

                                if (!fieldWrapper) {
                                    return;
                                }

                                this.updateTextareaWordCounter(event.target);
                                this.clearFieldError(fieldWrapper);
                                this.setCurrentQuestion(fieldWrapper.dataset.fieldId);
                                this.syncQuestionState(fieldWrapper.dataset.fieldId);

                                if (eventName === 'input') {
                                    this.trackFieldMutation(event.target, 'field_input');
                                    return;
                                }

                                void this.handleFieldChangeAction(event.target);
                            });
                        });

                        form.addEventListener('assessment:repeater-mutated', (event) => {
                            const fieldWrapper = event.target?.closest?.('[data-assessment-field]');

                            if (!fieldWrapper) {
                                return;
                            }

                            const normalizedFieldId = Number(fieldWrapper.dataset.fieldId ?? 0);

                            if (!normalizedFieldId) {
                                return;
                            }

                            const action = String(event.detail?.action ?? 'mutated');

                            this.setCurrentQuestion(normalizedFieldId);
                            this.markFieldAsDirty(normalizedFieldId, `repeater_${action}`, {
                                assessmentIndex: Number(fieldWrapper.dataset.assessmentIndex ?? this.currentAssessmentIndex),
                            });
                            this.syncQuestionState(normalizedFieldId);

                            void this.registerAutosaveAction(`repeater_${action}`, {
                                fieldId: normalizedFieldId,
                                assessmentIndex: Number(fieldWrapper.dataset.assessmentIndex ?? this.currentAssessmentIndex),
                            });
                        });

                        form.addEventListener('focusin', (event) => {
                            const fieldWrapper = event.target?.closest('[data-assessment-field]');

                            if (!fieldWrapper) {
                                return;
                            }

                            this.setCurrentQuestion(fieldWrapper.dataset.fieldId);
                        });

                        this.refreshAllTextareaWordCounters();
                        this.refreshAllQuestionStates();

                        if (!this.currentQuestionFieldId) {
                            this.currentQuestionFieldId = this.firstQuestionFieldId(this.currentAssessmentIndex);
                        }

                        this.startDeadlineWatcher();
                        this.refreshSecurityGuard();
                    });
                },
                destroy() {
                    if (this.deadlineWatcherId) {
                        clearInterval(this.deadlineWatcherId);
                    }

                    this.securityGuard?.destroy?.();
                },
                currentStageMeta() {
                    return this.assessmentItems[this.currentAssessmentIndex]?.stage_meta || {};
                },
                currentSecurityConfig() {
                    const baseConfig = this.securityConfig && typeof this.securityConfig === 'object'
                        ? { ...this.securityConfig }
                        : {};

                    if (!this.stageFlowEnabled) {
                        return {
                            ...baseConfig,
                            stageIndex: this.currentAssessmentIndex,
                        };
                    }

                    const stageMeta = this.currentStageMeta();

                    if (
                        stageMeta.is_interactive === false
                        || stageMeta.requires_start_button === true
                        || stageMeta.read_only === true
                    ) {
                        return {
                            ...baseConfig,
                            enabled: false,
                            requireFullscreen: false,
                            stageIndex: this.currentAssessmentIndex,
                        };
                    }

                    return {
                        ...baseConfig,
                        enabled: Boolean(stageMeta.security_enabled),
                        requireFullscreen: Boolean(stageMeta.require_fullscreen),
                        maxSeriousViolations: Math.max(
                            1,
                            Number(stageMeta.security_max_serious_violations ?? baseConfig.maxSeriousViolations ?? 3),
                        ),
                        temporaryLockSeconds: Math.max(
                            1,
                            Number(stageMeta.security_temporary_lock_seconds ?? baseConfig.temporaryLockSeconds ?? 2),
                        ),
                        fullscreenGraceSeconds: Math.max(
                            3,
                            Number(stageMeta.security_fullscreen_grace_seconds ?? baseConfig.fullscreenGraceSeconds ?? 10),
                        ),
                        stageIndex: this.currentAssessmentIndex,
                    };
                },
                currentSecurityEnabled() {
                    return Boolean(this.currentSecurityConfig()?.enabled);
                },
                refreshSecurityGuard() {
                    this.securityGuard?.destroy?.();
                    this.securityGuard = window.createAssessmentSecurityGuard(this, this.currentSecurityConfig());
                    this.securityGuard.init();
                },
                formElement() {
                    return this.$refs.assessmentExamForm ?? null;
                },
                createAutosaveBucket() {
                    return {
                        dirtyFieldIds: [],
                        flaggedDirty: false,
                        hasMutations: false,
                        startedAt: null,
                        trace: [],
                    };
                },
                normalizeAutosaveBucket(bucket) {
                    const normalizedBucket = bucket && typeof bucket === 'object'
                        ? bucket
                        : this.createAutosaveBucket();

                    return {
                        dirtyFieldIds: this.normalizeFieldIdList(normalizedBucket.dirtyFieldIds ?? []),
                        flaggedDirty: Boolean(normalizedBucket.flaggedDirty),
                        hasMutations: Boolean(normalizedBucket.hasMutations),
                        startedAt: typeof normalizedBucket.startedAt === 'string' ? normalizedBucket.startedAt : null,
                        trace: Array.isArray(normalizedBucket.trace) ? normalizedBucket.trace : [],
                    };
                },
                ensureAutosaveBucket() {
                    this.autosaveBucket = this.normalizeAutosaveBucket(this.autosaveBucket);

                    return this.autosaveBucket;
                },
                hasPendingAutosaveMutations() {
                    const bucket = this.ensureAutosaveBucket();

                    return bucket.hasMutations || bucket.flaggedDirty || bucket.dirtyFieldIds.length > 0;
                },
                appendAutosaveTrace(type, details = {}) {
                    const bucket = this.ensureAutosaveBucket();
                    const normalizedFieldId = Number(details.fieldId ?? 0);
                    const normalizedAssessmentIndex = Number(details.assessmentIndex ?? -1);
                    const lastTraceEntry = bucket.trace[bucket.trace.length - 1] ?? null;

                    if (
                        type === 'field_input'
                        && lastTraceEntry
                        && lastTraceEntry.type === 'field_input'
                        && Number(lastTraceEntry.field_id ?? 0) === normalizedFieldId
                        && Number(lastTraceEntry.assessment_index ?? -1) === normalizedAssessmentIndex
                    ) {
                        lastTraceEntry.client_occurred_at = new Date().toISOString();
                        lastTraceEntry.changed = lastTraceEntry.changed || Boolean(details.changed);

                        return lastTraceEntry;
                    }

                    this.autosaveTraceSequence += 1;

                    const traceEntry = {
                        sequence: this.autosaveTraceSequence,
                        type: String(type || 'unknown'),
                        changed: Boolean(details.changed),
                        client_occurred_at: new Date().toISOString(),
                    };

                    if (Number.isInteger(Number(details.fieldId)) && Number(details.fieldId) > 0) {
                        traceEntry.field_id = Number(details.fieldId);
                    }

                    if (Number.isInteger(Number(details.assessmentIndex)) && Number(details.assessmentIndex) >= 0) {
                        traceEntry.assessment_index = Number(details.assessmentIndex);
                    }

                    if (Number.isInteger(Number(details.fromAssessmentIndex)) && Number(details.fromAssessmentIndex) >= 0) {
                        traceEntry.from_assessment_index = Number(details.fromAssessmentIndex);
                    }

                    if (Number.isInteger(Number(details.toAssessmentIndex)) && Number(details.toAssessmentIndex) >= 0) {
                        traceEntry.to_assessment_index = Number(details.toAssessmentIndex);
                    }

                    bucket.trace = [...bucket.trace, traceEntry].slice(-30);

                    return traceEntry;
                },
                mergeAutosaveBucket(bucket) {
                    const currentBucket = this.ensureAutosaveBucket();
                    const normalizedBucket = this.normalizeAutosaveBucket(bucket);

                    if (!normalizedBucket.hasMutations && !normalizedBucket.flaggedDirty && normalizedBucket.dirtyFieldIds.length === 0) {
                        return;
                    }

                    this.autosaveBucket = {
                        dirtyFieldIds: this.normalizeFieldIdList([
                            ...normalizedBucket.dirtyFieldIds,
                            ...currentBucket.dirtyFieldIds,
                        ]),
                        flaggedDirty: normalizedBucket.flaggedDirty || currentBucket.flaggedDirty,
                        hasMutations: normalizedBucket.hasMutations || currentBucket.hasMutations,
                        startedAt: normalizedBucket.startedAt || currentBucket.startedAt,
                        trace: [...normalizedBucket.trace, ...currentBucket.trace].slice(-30),
                    };
                },
                consumeAutosaveBucket() {
                    const bucketToFlush = this.normalizeAutosaveBucket(this.autosaveBucket);

                    this.autosaveBucket = this.createAutosaveBucket();

                    return bucketToFlush;
                },
                markFieldAsDirty(fieldId, traceType = 'field_mutated', details = {}) {
                    const normalizedFieldId = Number(fieldId);

                    if (!normalizedFieldId) {
                        return;
                    }

                    const bucket = this.ensureAutosaveBucket();

                    bucket.hasMutations = true;
                    bucket.startedAt = bucket.startedAt || new Date().toISOString();
                    bucket.dirtyFieldIds = this.normalizeFieldIdList([
                        ...bucket.dirtyFieldIds,
                        normalizedFieldId,
                    ]);

                    this.appendAutosaveTrace(traceType, {
                        ...details,
                        fieldId: normalizedFieldId,
                        changed: true,
                    });
                },
                markFlagMutation(fieldId) {
                    const bucket = this.ensureAutosaveBucket();

                    bucket.hasMutations = true;
                    bucket.flaggedDirty = true;
                    bucket.startedAt = bucket.startedAt || new Date().toISOString();

                    this.appendAutosaveTrace('flag_toggled', {
                        fieldId,
                        assessmentIndex: this.currentAssessmentIndex,
                        changed: true,
                    });
                },
                isTrackableAnswerTarget(target) {
                    return (
                        target instanceof HTMLInputElement
                        || target instanceof HTMLSelectElement
                        || target instanceof HTMLTextAreaElement
                    ) && typeof target.name === 'string'
                        && /^answers\[\d+\]/.test(target.name);
                },
                extractFieldIdFromAnswerName(name) {
                    const match = String(name ?? '').match(/^answers\[(\d+)\](?:\[|$)/);

                    if (!match) {
                        return 0;
                    }

                    return Number(match[1] ?? 0);
                },
                trackFieldMutation(target, traceType = 'field_mutated') {
                    if (!this.isTrackableAnswerTarget(target)) {
                        return;
                    }

                    const fieldId = this.extractFieldIdFromAnswerName(target.name);
                    const fieldWrapper = target.closest?.('[data-assessment-field]');

                    this.markFieldAsDirty(fieldId, traceType, {
                        assessmentIndex: Number(fieldWrapper?.dataset?.assessmentIndex ?? this.currentAssessmentIndex),
                    });
                },
                async handleFieldChangeAction(target) {
                    if (!this.isTrackableAnswerTarget(target)) {
                        return;
                    }

                    const fieldId = this.extractFieldIdFromAnswerName(target.name);
                    const fieldWrapper = target.closest?.('[data-assessment-field]');

                    this.trackFieldMutation(target, 'field_change');

                    await this.registerAutosaveAction('field_change', {
                        fieldId,
                        assessmentIndex: Number(fieldWrapper?.dataset?.assessmentIndex ?? this.currentAssessmentIndex),
                    });
                },
                async registerAutosaveAction(actionType, details = {}) {
                    const hasPendingMutations = this.hasPendingAutosaveMutations();

                    if (hasPendingMutations) {
                        this.appendAutosaveTrace(actionType, {
                            ...details,
                            changed: false,
                        });
                    }

                    this.autosaveActionCount += 1;

                    if (this.autosaveActionCount < this.autosaveActionThreshold) {
                        return 'skipped';
                    }

                    this.autosaveActionCount = 0;

                    if (!this.hasPendingAutosaveMutations()) {
                        return 'skipped';
                    }

                    if (this.isAutosaving) {
                        this.autosaveQueued = true;
                        this.autosaveQueuedReason = String(actionType || 'queued_autosave');

                        return 'queued';
                    }

                    return await this.saveCurrentAssessmentSnapshot({
                        reason: String(actionType || 'autosave_threshold_reached'),
                    });
                },
                findDirtyFieldIdsFromFormData(sourceFormData, dirtyFieldIds) {
                    const dirtyFieldIdSet = new Set(this.normalizeFieldIdList(dirtyFieldIds));
                    const matchedFieldIds = new Set();

                    for (const [key] of sourceFormData.entries()) {
                        const fieldId = this.extractFieldIdFromAnswerName(key);

                        if (!fieldId || !dirtyFieldIdSet.has(fieldId)) {
                            continue;
                        }

                        matchedFieldIds.add(fieldId);
                    }

                    return Array.from(matchedFieldIds.values());
                },
                buildAutosavePayload(bucket, reason = 'autosave_threshold_reached') {
                    const form = this.formElement();

                    if (!form) {
                        return null;
                    }

                    const normalizedBucket = this.normalizeAutosaveBucket(bucket);
                    const sourceFormData = new FormData(form);
                    const formData = new FormData();
                    const csrfToken = sourceFormData.get('_token');

                    if (typeof csrfToken === 'string' && csrfToken !== '') {
                        formData.append('_token', csrfToken);
                    }

                    formData.append('active_assessment_index', String(this.currentAssessmentIndex));
                    this.flaggedFieldIds.forEach((fieldId) => {
                        formData.append('flagged_field_ids[]', String(fieldId));
                    });

                    const dirtyFieldIds = this.normalizeFieldIdList(normalizedBucket.dirtyFieldIds);

                    dirtyFieldIds.forEach((fieldId) => {
                        formData.append('field_ids[]', String(fieldId));
                    });

                    for (const [key, value] of sourceFormData.entries()) {
                        const fieldId = this.extractFieldIdFromAnswerName(key);

                        if (!fieldId || !dirtyFieldIds.includes(fieldId)) {
                            continue;
                        }

                        formData.append(key, value);
                    }

                    formData.append('client_snapshot_bucket', JSON.stringify({
                        flush_reason: reason,
                        threshold: this.autosaveActionThreshold,
                        dirty_field_ids: dirtyFieldIds,
                        form_data_field_ids: this.findDirtyFieldIdsFromFormData(sourceFormData, dirtyFieldIds),
                        flagged_dirty: normalizedBucket.flaggedDirty,
                        started_at: normalizedBucket.startedAt,
                        trace: normalizedBucket.trace,
                    }));

                    return formData;
                },
                filterAutosaveTraceEntries(trace, allowedFieldIds, includeFieldlessEntries = false) {
                    const allowedFieldIdSet = new Set(this.normalizeFieldIdList(allowedFieldIds));

                    return (Array.isArray(trace) ? trace : []).filter((entry) => {
                        const traceFieldId = Number(entry?.field_id ?? 0);

                        if (traceFieldId > 0) {
                            return allowedFieldIdSet.has(traceFieldId);
                        }

                        return includeFieldlessEntries;
                    });
                },
                validateFieldForAutosave(fieldWrapper) {
                    return this.validateField(fieldWrapper, {
                        enforceRequired: false,
                        enforceFlagged: false,
                        enforceRepeaterCompleteness: false,
                    });
                },
                splitAutosaveBucket(bucket) {
                    const normalizedBucket = this.normalizeAutosaveBucket(bucket);
                    const savableFieldIds = [];
                    const deferredFieldIds = [];

                    normalizedBucket.dirtyFieldIds.forEach((fieldId) => {
                        const fieldWrapper = this.getFieldWrapper(fieldId);

                        if (!fieldWrapper) {
                            savableFieldIds.push(fieldId);
                            return;
                        }

                        const validation = this.validateFieldForAutosave(fieldWrapper);

                        if (validation.valid) {
                            savableFieldIds.push(fieldId);
                        } else {
                            deferredFieldIds.push(fieldId);
                        }

                        this.syncQuestionState(fieldId);
                    });

                    const hasSavablePayload = normalizedBucket.flaggedDirty || savableFieldIds.length > 0;
                    const commonTrace = this.filterAutosaveTraceEntries(normalizedBucket.trace, [], true);

                    return {
                        savableBucket: {
                            dirtyFieldIds: savableFieldIds,
                            flaggedDirty: normalizedBucket.flaggedDirty,
                            hasMutations: hasSavablePayload,
                            startedAt: normalizedBucket.startedAt,
                            trace: [
                                ...(hasSavablePayload ? commonTrace : []),
                                ...this.filterAutosaveTraceEntries(normalizedBucket.trace, savableFieldIds),
                            ].slice(-30),
                        },
                        deferredBucket: {
                            dirtyFieldIds: deferredFieldIds,
                            flaggedDirty: false,
                            hasMutations: deferredFieldIds.length > 0,
                            startedAt: normalizedBucket.startedAt,
                            trace: [
                                ...(!hasSavablePayload ? commonTrace : []),
                                ...this.filterAutosaveTraceEntries(normalizedBucket.trace, deferredFieldIds),
                            ].slice(-30),
                        },
                    };
                },
                isNumberInput(target) {
                    return target instanceof HTMLInputElement && target.type === 'number';
                },
                sanitizeNumberInputValue(value) {
                    const normalizedValue = String(value ?? '').replace(/,/g, '.');
                    let sanitizedValue = '';
                    let hasDecimalSeparator = false;
                    let hasSign = false;

                    for (const character of normalizedValue) {
                        if (/\d/.test(character)) {
                            sanitizedValue += character;
                            continue;
                        }

                        if (character === '-' && !hasSign && sanitizedValue === '') {
                            sanitizedValue += character;
                            hasSign = true;
                            continue;
                        }

                        if (character === '.' && !hasDecimalSeparator) {
                            sanitizedValue += character;
                            hasDecimalSeparator = true;
                        }
                    }

                    return sanitizedValue;
                },
                normalizeNumberInput(target) {
                    if (!this.isNumberInput(target)) {
                        return;
                    }

                    const sanitizedValue = this.sanitizeNumberInputValue(target.value);

                    if (target.value !== sanitizedValue) {
                        target.value = sanitizedValue;
                    }
                },
                getAssessmentPanel(index) {
                    const form = this.formElement();

                    if (!form) {
                        return null;
                    }

                    return form.querySelector(`[data-assessment-panel="${index}"]`);
                },
                getFieldWrapper(fieldId) {
                    const form = this.formElement();

                    if (!form) {
                        return null;
                    }

                    return form.querySelector(`[data-field-id="${Number(fieldId)}"]`);
                },
                countWords(value) {
                    const normalizedValue = String(value ?? '').trim();

                    if (normalizedValue === '') {
                        return 0;
                    }

                    return normalizedValue.split(/\s+/u).filter(Boolean).length;
                },
                resolveTextareaWordLimits(input) {
                    const fallbackMin = Number(this.textareaWordLimits?.min ?? 0);
                    const fallbackMax = Number(this.textareaWordLimits?.max ?? 0);
                    const min = Number(input?.dataset?.minWords ?? fallbackMin);
                    const max = Number(input?.dataset?.maxWords ?? fallbackMax);

                    return {
                        min: Number.isFinite(min) && min > 0 ? min : 0,
                        max: Number.isFinite(max) && max > 0 ? max : 0,
                    };
                },
                formatTextareaWordCountText(input) {
                    const wordCount = this.countWords(input?.value ?? '');
                    const {
                        min,
                        max
                    } = this.resolveTextareaWordLimits(input);
                    const rules = [];

                    if (min > 0) {
                        rules.push(`Minimal ${min} kata`);
                    }

                    if (max > 0) {
                        rules.push(`maksimal ${max} kata`);
                    }

                    return `${wordCount} kata${rules.length ? ` / ${rules.join(', ')}` : ''}`;
                },
                getTextareaWordValidationMessage(input, subjectLabel, prefix = 'Jawaban untuk pertanyaan') {
                    const value = String(input?.value ?? '').trim();

                    if (value === '') {
                        return null;
                    }

                    const wordCount = this.countWords(value);
                    const {
                        min,
                        max
                    } = this.resolveTextareaWordLimits(input);

                    if (min > 0 && wordCount < min) {
                        return `${prefix} ${subjectLabel} minimal ${min} kata. Saat ini ${wordCount} kata.`;
                    }

                    if (max > 0 && wordCount > max) {
                        return `${prefix} ${subjectLabel} maksimal ${max} kata. Saat ini ${wordCount} kata.`;
                    }

                    return null;
                },
                updateTextareaWordCounter(target) {
                    const textarea = target instanceof HTMLTextAreaElement
                        ? target
                        : target?.closest?.('textarea[data-textarea-word-limit]');

                    if (!textarea || textarea.dataset.textareaWordLimit !== '1') {
                        return;
                    }

                    const container = textarea.parentElement;
                    const counter = container?.querySelector?.('[data-word-count-display]');

                    if (!counter) {
                        return;
                    }

                    counter.textContent = this.formatTextareaWordCountText(textarea);

                    const hasContent = String(textarea.value ?? '').trim() !== '';
                    const isValid = this.getTextareaWordValidationMessage(
                        textarea,
                        textarea.dataset.repeaterLabel || textarea.name || 'field ini',
                        'Jawaban untuk pertanyaan'
                    ) === null;

                    counter.classList.toggle('text-red-600', hasContent && !isValid);
                    counter.classList.toggle('text-slate-500', !hasContent || isValid);
                },
                refreshAllTextareaWordCounters() {
                    const form = this.formElement();

                    if (!form) {
                        return;
                    }

                    form.querySelectorAll('textarea[data-textarea-word-limit="1"]').forEach((textarea) => {
                        this.updateTextareaWordCounter(textarea);
                    });
                },
                normalizeFieldIdList(values) {
                    return Array.from(new Set((Array.isArray(values) ? values : [values])
                        .map((value) => Number(value))
                        .filter((value) => Number.isInteger(value) && value > 0)));
                },
                questionItemByFieldId(fieldId) {
                    return this.questionItems.find((item) => Number(item.field_id) === Number(fieldId)) ?? null;
                },
                firstQuestionFieldId(assessmentIndex) {
                    const assessmentMeta = this.assessmentItems.find((item) => Number(item.index) === Number(assessmentIndex));
                    const fieldIds = Array.isArray(assessmentMeta?.field_ids) ? assessmentMeta.field_ids : [];

                    return Number(fieldIds[0] ?? 0);
                },
                setCurrentQuestion(fieldId) {
                    const normalizedFieldId = Number(fieldId);

                    if (!normalizedFieldId) {
                        return;
                    }

                    this.currentQuestionFieldId = normalizedFieldId;
                },
                isFieldFlagged(fieldId) {
                    return this.flaggedFieldIds.includes(Number(fieldId));
                },
                async toggleFlag(fieldId) {
                    if (this.isInteractionLocked()) {
                        return;
                    }

                    const normalizedFieldId = Number(fieldId);

                    if (!normalizedFieldId) {
                        return;
                    }

                    if (this.isFieldFlagged(normalizedFieldId)) {
                        this.flaggedFieldIds = this.flaggedFieldIds.filter((item) => item !== normalizedFieldId);
                    } else {
                        this.flaggedFieldIds = [...this.flaggedFieldIds, normalizedFieldId];
                    }

                    this.setCurrentQuestion(normalizedFieldId);
                    this.syncQuestionState(normalizedFieldId);

                    const fieldWrapper = this.getFieldWrapper(normalizedFieldId);

                    if (fieldWrapper) {
                        this.clearFieldError(fieldWrapper);
                    }

                    this.markFlagMutation(normalizedFieldId);
                    await this.registerAutosaveAction('flag_toggle', {
                        fieldId: normalizedFieldId,
                        assessmentIndex: Number(fieldWrapper?.dataset?.assessmentIndex ?? this.currentAssessmentIndex),
                    });
                },
                questionState(fieldId) {
                    return this.questionStateByFieldId[String(Number(fieldId))] ?? {
                        answered: false,
                        hasAnswer: false,
                        invalid: false,
                        flagged: this.isFieldFlagged(fieldId),
                    };
                },
                answeredQuestionCount() {
                    return this.questionItems.filter((item) => this.questionState(item.field_id).answered).length;
                },
                unansweredQuestionCount() {
                    return Math.max(this.questionItems.length - this.answeredQuestionCount(), 0);
                },
                flaggedQuestionCount() {
                    return this.flaggedFieldIds.length;
                },
                flaggedUnansweredQuestionCount() {
                    return this.questionItems.filter((item) => {
                        return this.isFieldFlagged(item.field_id) && !this.questionState(item.field_id).answered;
                    }).length;
                },
                questionButtonClass(fieldId, assessmentIndex) {
                    const isAnswered = Boolean(this.questionState(fieldId).answered);
                    const isFlagged = this.isFieldFlagged(fieldId);
                    const isCurrentQuestion = Number(this.currentQuestionFieldId) === Number(fieldId);
                    const isCurrentAssessment = Number(this.currentAssessmentIndex) === Number(assessmentIndex);
                    const classes = [];

                    if (isFlagged) {
                        if (isAnswered) {
                            classes.push('border-amber-500 bg-amber-500 text-white hover:bg-amber-600');
                        } else {
                            classes.push('border-amber-300 bg-amber-200 text-amber-900 hover:bg-amber-300');
                        }
                    } else if (isAnswered) {
                        classes.push('border-[#1376bd] bg-[#1376bd] text-white hover:bg-[#0d5f98]');
                    } else {
                        classes.push('border-[#d7e3ee] bg-white text-slate-700 hover:border-[#1376bd] hover:text-[#1376bd]');
                    }

                    if (isCurrentAssessment) {
                        classes.push('shadow-sm');
                    }

                    if (isCurrentQuestion) {
                        classes.push('ring-2 ring-[#0d5f98] ring-offset-2');
                    }

                    return classes.join(' ');
                },
                fieldWrapperClass(fieldId, assessmentIndex) {
                    const isCurrentQuestion = Number(this.currentQuestionFieldId) === Number(fieldId);
                    const isCurrentAssessment = Number(this.currentAssessmentIndex) === Number(assessmentIndex);

                    if (!isCurrentQuestion || !isCurrentAssessment) {
                        return '';
                    }

                    return [
                        'ring-2',
                        'ring-[#1376bd]',
                        'ring-offset-8',
                        'shadow-sm',
                        'shadow-[#1376bd]/10',
                    ].join(' ');
                },
                questionButtonTitle(fieldId) {
                    const item = this.questionItemByFieldId(fieldId);
                    const state = this.questionState(fieldId);
                    const parts = [];

                    if (item?.label) {
                        parts.push(item.label);
                    }

                    if (state.invalid) {
                        parts.push('Jawaban belum valid');
                    } else {
                        parts.push(state.answered ? 'Sudah dijawab' : 'Belum dijawab');
                    }

                    if (this.isFieldFlagged(fieldId)) {
                        parts.push('Ditandai');
                    }

                    return parts.join(' | ');
                },
                isBusy() {
                    return this.isSubmitting || this.isAutosaving || this.deadlineSubmissionTriggered;
                },
                isInteractionLocked() {
                    return this.isSubmitting || this.deadlineSubmissionTriggered;
                },
                openFinishModal() {
                    if (this.stageFlowEnabled) {
                        this.submitCurrentStage();
                        return;
                    }

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

                    this.refreshAllQuestionStates();
                    this.showFinishModal = true;
                },
                submitConfirmedForm() {
                    if (this.isBusy()) {
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
                    if (this.stageFlowEnabled) {
                        this.submitCurrentStage();
                        return;
                    }

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
                        stage_meta: {},
                    };
                },
                canAccessAssessment(index) {
                    const assessmentMeta = this.assessmentItems[index] ?? null;

                    if (!assessmentMeta) {
                        return false;
                    }

                    if (!this.stageFlowEnabled) {
                        return true;
                    }

                    return Boolean(assessmentMeta.stage_meta?.can_access);
                },
                canSwitchAwayFromCurrentStage() {
                    if (!this.stageFlowEnabled) {
                        return true;
                    }

                    return this.currentStageMeta().can_switch_away !== false;
                },
                currentStageStatus() {
                    return String(this.currentStageMeta().status || '');
                },
                showDraftButton() {
                    if (!this.stageFlowEnabled) {
                        return false;
                    }

                    const stageMeta = this.currentStageMeta();

                    return Boolean(stageMeta.allow_draft)
                        && stageMeta.requires_start_button !== true
                        && stageMeta.read_only !== true
                        && stageMeta.is_interactive !== false;
                },
                showStageFinalizeButton() {
                    if (!this.stageFlowEnabled) {
                        return this.isLastAssessment();
                    }

                    const stageMeta = this.currentStageMeta();
                    const status = this.currentStageStatus();

                    return stageMeta.can_access === true
                        && stageMeta.requires_start_button !== true
                        && stageMeta.read_only !== true
                        && stageMeta.is_interactive !== false
                        && ['in_progress', 'ready'].includes(status);
                },
                currentStageFinalizeLabel() {
                    if (!this.stageFlowEnabled) {
                        return 'Selesai Assessment';
                    }

                    return this.currentStageMeta().finalize_mode === 'auto'
                        ? 'Selesaikan Tahap'
                        : 'Simpan Permanen';
                },
                canGoPreviousStage() {
                    return !this.isFirstAssessment() && this.canSwitchAwayFromCurrentStage();
                },
                canGoNextStage() {
                    if (this.isLastAssessment() || !this.canSwitchAwayFromCurrentStage()) {
                        return false;
                    }

                    return this.canAccessAssessment(this.currentAssessmentIndex + 1);
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
                ensureHiddenInput(name, value) {
                    const form = this.formElement();

                    if (!form) {
                        return null;
                    }

                    let input = form.querySelector(`input[name="${name}"]`);

                    if (!(input instanceof HTMLInputElement)) {
                        input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = name;
                        form.appendChild(input);
                    }

                    input.value = String(value ?? '');

                    return input;
                },
                setSubmissionScope(scope = 'attempt') {
                    this.ensureHiddenInput('submission_scope', scope);
                    this.ensureHiddenInput('stage_index', this.currentAssessmentIndex);
                },
                buildCurrentStageBucket(reason = 'manual_stage_draft') {
                    const currentMeta = this.currentAssessmentMeta();
                    const fieldIds = Array.isArray(currentMeta.field_ids) ? currentMeta.field_ids : [];

                    return {
                        dirtyFieldIds: this.normalizeFieldIdList(fieldIds),
                        flaggedDirty: true,
                        hasMutations: fieldIds.length > 0,
                        startedAt: new Date().toISOString(),
                        trace: [{
                            sequence: this.autosaveTraceSequence + 1,
                            type: reason,
                            changed: true,
                            assessment_index: this.currentAssessmentIndex,
                            client_occurred_at: new Date().toISOString(),
                        }],
                    };
                },
                async saveDraftForCurrentStage() {
                    if (!this.stageFlowEnabled || !this.showDraftButton() || this.isBusy()) {
                        return;
                    }

                    const result = await this.saveCurrentAssessmentSnapshot({
                        reason: 'manual_stage_draft',
                        bucket: this.buildCurrentStageBucket('manual_stage_draft'),
                    });

                    if (result === 'saved') {
                        window.alert('Draft tahap berhasil disimpan.');
                    }
                },
                submitCurrentStage() {
                    if (this.isBusy()) {
                        return;
                    }

                    if (!this.stageFlowEnabled) {
                        this.openFinishModal();
                        return;
                    }

                    if (!this.showStageFinalizeButton()) {
                        return;
                    }

                    const validation = this.validateAssessment(this.currentAssessmentIndex);

                    if (!validation.valid) {
                        this.focusFieldById(validation.fieldId);
                        return;
                    }

                    const form = this.formElement();

                    if (!form) {
                        return;
                    }

                    this.setSubmissionScope('stage');
                    this.isSubmitting = true;
                    form.submit();
                },
                async goToAssessment(index) {
                    if (this.isInteractionLocked() || this.totalAssessments <= 0) {
                        return;
                    }

                    const boundedIndex = Math.max(0, Math.min(index, this.totalAssessments - 1));

                    if (boundedIndex === this.currentAssessmentIndex || !this.canAccessAssessment(boundedIndex)) {
                        return;
                    }

                    if (!this.canSwitchAwayFromCurrentStage()) {
                        return;
                    }

                    const previousAssessmentIndex = this.currentAssessmentIndex;

                    this.switchToAssessment(boundedIndex);
                    void this.registerAutosaveAction('navigate_assessment', {
                        fromAssessmentIndex: previousAssessmentIndex,
                        toAssessmentIndex: boundedIndex,
                    });
                },
                closeQuestionNavigationPanel(trigger) {
                    const detailsElement = trigger?.closest?.('details');

                    if (!(detailsElement instanceof HTMLDetailsElement) || !detailsElement.open) {
                        return Promise.resolve();
                    }

                    detailsElement.open = false;

                    return new Promise((resolve) => {
                        window.requestAnimationFrame(() => {
                            window.requestAnimationFrame(resolve);
                        });
                    });
                },
                async goToQuestion(fieldId, assessmentIndex, event = null) {
                    if (this.isInteractionLocked()) {
                        return;
                    }

                    const normalizedFieldId = Number(fieldId);
                    const boundedAssessmentIndex = Math.max(0, Math.min(Number(assessmentIndex), this.totalAssessments - 1));
                    const triggerElement = event?.currentTarget ?? event?.target ?? null;

                    if (!normalizedFieldId || !this.canAccessAssessment(boundedAssessmentIndex)) {
                        return;
                    }

                    if (
                        boundedAssessmentIndex !== this.currentAssessmentIndex
                        && !this.canSwitchAwayFromCurrentStage()
                    ) {
                        return;
                    }

                    if (
                        boundedAssessmentIndex === this.currentAssessmentIndex
                        && normalizedFieldId === Number(this.currentQuestionFieldId)
                    ) {
                        void this.closeQuestionNavigationPanel(triggerElement);
                        return;
                    }

                    const previousAssessmentIndex = this.currentAssessmentIndex;

                    void this.closeQuestionNavigationPanel(triggerElement);
                    this.switchToAssessment(boundedAssessmentIndex, normalizedFieldId);
                    void this.registerAutosaveAction('navigate_question', {
                        fieldId: normalizedFieldId,
                        fromAssessmentIndex: previousAssessmentIndex,
                        toAssessmentIndex: boundedAssessmentIndex,
                        assessmentIndex: boundedAssessmentIndex,
                    });
                },
                switchToAssessment(index, questionFieldId = null) {
                    this.currentAssessmentIndex = index;
                    this.showFinishModal = false;
                    this.currentQuestionFieldId = Number(questionFieldId) || this.firstQuestionFieldId(index);
                    this.refreshSecurityGuard();

                    this.$nextTick(() => {
                        if (this.currentQuestionFieldId) {
                            this.focusFieldById(this.currentQuestionFieldId);
                            return;
                        }

                        this.scrollToTop();
                    });
                },
                async saveCurrentAssessmentSnapshot(options = {}) {
                    if (!this.autosaveUrl) {
                        return 'saved';
                    }

                    const reason = String(options.reason || 'autosave_threshold_reached');
                    const consumedBucket = options.bucket
                        ? this.normalizeAutosaveBucket(options.bucket)
                        : this.consumeAutosaveBucket();
                    const {
                        savableBucket,
                        deferredBucket
                    } = this.splitAutosaveBucket(consumedBucket);

                    if (deferredBucket.hasMutations) {
                        this.mergeAutosaveBucket(deferredBucket);
                    }

                    if (!savableBucket.hasMutations && !savableBucket.flaggedDirty && savableBucket.dirtyFieldIds.length === 0) {
                        return deferredBucket.hasMutations ? 'deferred_invalid' : 'saved';
                    }

                    const formData = this.buildAutosavePayload(savableBucket, reason);

                    if (!formData) {
                        this.mergeAutosaveBucket(savableBucket);
                        return 'saved';
                    }

                    this.isAutosaving = true;

                    try {
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

                            this.mergeAutosaveBucket(savableBucket);

                            return 'failed';
                        }

                        if (payload?.status === 'expired_submitted' && payload?.redirect_url) {
                            window.location.href = payload.redirect_url;

                            return 'expired';
                        }

                        return 'saved';
                    } catch (error) {
                        this.mergeAutosaveBucket(savableBucket);
                        window.alert('Terjadi kendala saat menyimpan snapshot jawaban. Silakan coba lagi.');

                        return 'failed';
                    } finally {
                        this.isAutosaving = false;

                        if (this.autosaveQueued && !this.isBusy() && this.hasPendingAutosaveMutations()) {
                            const queuedReason = this.autosaveQueuedReason || 'queued_autosave';

                            this.autosaveQueued = false;
                            this.autosaveQueuedReason = null;

                            window.setTimeout(() => {
                                void this.saveCurrentAssessmentSnapshot({
                                    reason: queuedReason,
                                });
                            }, 0);
                        } else {
                            this.autosaveQueued = false;
                            this.autosaveQueuedReason = null;
                        }
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
                validateField(fieldWrapper, options = {}) {
                    this.clearFieldError(fieldWrapper);
                    const validation = this.resolveFieldValidation(fieldWrapper, options);

                    if (validation.valid) {
                        return validation;
                    }

                    this.setFieldError(fieldWrapper, validation.message);

                    return validation;
                },
                resolveFieldValidation(fieldWrapper, options = {}) {

                    const fieldId = Number(fieldWrapper.dataset.fieldId ?? 0);
                    const fieldType = fieldWrapper.dataset.fieldType ?? 'text';
                    const fieldLabel = fieldWrapper.dataset.fieldLabel ?? 'field ini';
                    const isRequired = fieldWrapper.dataset.required === '1';
                    const hasExistingFile = fieldWrapper.dataset.hasExistingFile === '1';
                    const enforceRequired = options.enforceRequired !== false;
                    const enforceFlagged = options.enforceFlagged !== false;
                    const enforceRepeaterCompleteness = options.enforceRepeaterCompleteness !== false;
                    const requiresAnswer =
                        (enforceRequired && isRequired)
                        || (enforceFlagged && this.isFieldFlagged(fieldId));
                    let message = null;

                    if (fieldType === 'radio') {
                        const inputs = Array.from(fieldWrapper.querySelectorAll('input[type="radio"]'));
                        const hasSelection = inputs.some((input) => input.checked);

                        if (requiresAnswer && !hasSelection) {
                            message = `Pilih satu jawaban untuk pertanyaan ${fieldLabel}.`;
                        }
                    } else if (fieldType === 'checkbox') {
                        const inputs = Array.from(fieldWrapper.querySelectorAll('input[type="checkbox"]'));
                        const hasSelection = inputs.some((input) => input.checked);

                        if (requiresAnswer && !hasSelection) {
                            message = `Minimal pilih satu jawaban untuk pertanyaan ${fieldLabel}.`;
                        }
                    } else if (fieldType === 'file') {
                        const fileInputMode = fieldWrapper.dataset.fileInputMode || 'file';

                        if (fileInputMode === 'link') {
                            const input = fieldWrapper.querySelector('input[type="url"]');
                            const linkValue = String(input?.value || '').trim();

                            if (requiresAnswer && !linkValue && !hasExistingFile) {
                                message = `Link file untuk pertanyaan ${fieldLabel} wajib diisi.`;
                            } else if (linkValue && !window.assessmentIsValidHttpUrl(linkValue)) {
                                message = `Link file untuk pertanyaan ${fieldLabel} harus berupa URL yang valid.`;
                            }
                        } else {
                            const input = fieldWrapper.querySelector('input[type="file"]');
                            const uploadedFile = input?.files?.[0] ?? null;

                            if (requiresAnswer && !uploadedFile && !hasExistingFile) {
                                message = `File untuk pertanyaan ${fieldLabel} wajib diunggah.`;
                            } else if (uploadedFile && uploadedFile.size > 5 * 1024 * 1024) {
                                message = `File untuk pertanyaan ${fieldLabel} maksimal 5 MB.`;
                            }
                        }
                    } else if (fieldType === 'repeater') {
                        const rows = this.extractRepeaterRows(fieldWrapper);
                        const filledRows = Array.from(rows.values()).filter((inputs) => {
                            return inputs.some((input) => String(input.value || '').trim() !== '');
                        });

                        if (requiresAnswer && filledRows.length === 0) {
                            message = `Minimal isi satu baris pada pertanyaan ${fieldLabel}.`;
                        } else {
                            for (const [index, inputs] of Array.from(rows.entries())) {
                                const hasContent = inputs.some((input) => String(input.value || '').trim() !== '');

                                if (!hasContent) {
                                    continue;
                                }

                                if (enforceRepeaterCompleteness) {
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

                                const invalidTextareaInput = inputs.find((input) => {
                                    return input instanceof HTMLTextAreaElement
                                        && String(input.value || '').trim() !== ''
                                        && this.getTextareaWordValidationMessage(input, fieldLabel) !== null;
                                });

                                if (invalidTextareaInput) {
                                    const columnLabel = invalidTextareaInput.dataset.repeaterLabel || 'Kolom';
                                    const wordCount = this.countWords(invalidTextareaInput.value || '');
                                    const {
                                        min,
                                        max
                                    } = this.resolveTextareaWordLimits(invalidTextareaInput);

                                    if (min > 0 && wordCount < min) {
                                        message = `Kolom ${columnLabel} pada baris ${Number(index) + 1} untuk pertanyaan ${fieldLabel} minimal ${min} kata. Saat ini ${wordCount} kata.`;
                                    } else if (max > 0 && wordCount > max) {
                                        message = `Kolom ${columnLabel} pada baris ${Number(index) + 1} untuk pertanyaan ${fieldLabel} maksimal ${max} kata. Saat ini ${wordCount} kata.`;
                                    }

                                    break;
                                }

                                const invalidUrlInput = inputs.find((input) => {
                                    return input instanceof HTMLInputElement
                                        && input.type === 'url'
                                        && String(input.value || '').trim() !== ''
                                        && !window.assessmentIsValidHttpUrl(input.value || '');
                                });

                                if (invalidUrlInput) {
                                    const columnLabel = invalidUrlInput.dataset.repeaterLabel || 'Kolom';
                                    message = `Kolom ${columnLabel} pada baris ${Number(index) + 1} untuk pertanyaan ${fieldLabel} harus berupa URL yang valid.`;
                                    break;
                                }
                            }
                        }
                    } else {
                        const input = fieldType === 'textarea'
                            ? fieldWrapper.querySelector('textarea')
                            : (fieldType === 'select'
                                ? fieldWrapper.querySelector('select')
                                : fieldWrapper.querySelector('input:not([type="radio"]):not([type="checkbox"]):not([type="file"]):not([type="hidden"])'));

                        if (!input) {
                            return {
                                valid: true,
                                fieldId,
                            };
                        }

                        const rawValue = typeof input.value === 'string' ? input.value : '';
                        const value = rawValue.trim();
                        const allowsOtherInput = fieldWrapper.dataset.allowOtherInput === '1';
                        const selectOtherOptionValue = String(fieldWrapper.dataset.selectOtherOptionValue || '').trim();
                        const selectOtherInput = fieldType === 'select' && allowsOtherInput
                            ? fieldWrapper.querySelector('input[name^="answers["][name$="[other_text]"]')
                            : null;
                        const otherTextValue = String(selectOtherInput?.value || '').trim();

                        if (requiresAnswer && value === '') {
                            message = `Jawaban untuk pertanyaan ${fieldLabel} wajib diisi.`;
                        } else if (
                            fieldType === 'select'
                            && allowsOtherInput
                            && value === selectOtherOptionValue
                            && otherTextValue === ''
                        ) {
                            message = `Isi jawaban lainnya untuk pertanyaan ${fieldLabel}.`;
                        } else if (fieldType === 'textarea' && value !== '') {
                            message = this.getTextareaWordValidationMessage(input, fieldLabel);
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

                    return {
                        valid: false,
                        fieldId,
                        message,
                    };
                },
                applyServerErrors(errors) {
                    this.clearAllFieldErrors();

                    Object.entries(errors || {}).forEach(([key, messages]) => {
                        const match = String(key).match(/^answers\.(\d+)(?:\.|$)/);

                        if (!match) {
                            return;
                        }

                        const fieldId = Number(match[1]);
                        const fieldWrapper = this.getFieldWrapper(fieldId);

                        if (!fieldWrapper) {
                            return;
                        }

                        const message = Array.isArray(messages) ? messages[0] : messages;
                        this.setFieldError(fieldWrapper, String(message || 'Input tidak valid.'));
                    });

                    this.refreshAllQuestionStates();

                    const firstKey = Object.keys(errors || {}).find((key) => /^answers\.\d+/.test(String(key)));

                    if (!firstKey) {
                        return;
                    }

                    const match = String(firstKey).match(/^answers\.(\d+)(?:\.|$)/);

                    if (!match) {
                        return;
                    }

                    this.focusFieldById(Number(match[1]));
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
                    fieldWrapper.classList.add(
                        'rounded-sm',
                        'border',
                        'border-red-200',
                        'bg-red-50/70',
                        'px-3',
                        'py-2',
                    );

                    const errorElement = fieldWrapper.querySelector('[data-field-error]');

                    if (!errorElement) {
                        return;
                    }

                    errorElement.textContent = message;
                    errorElement.classList.remove('hidden');
                },
                clearFieldError(fieldWrapper) {
                    fieldWrapper.classList.remove(
                        'rounded-sm',
                        'border',
                        'border-red-200',
                        'bg-red-50/70',
                        'px-3',
                        'py-2',
                    );

                    const errorElement = fieldWrapper.querySelector('[data-field-error]');

                    if (!errorElement) {
                        return;
                    }

                    errorElement.textContent = '';
                    errorElement.classList.add('hidden');
                },
                fieldHasVisibleError(fieldWrapper) {
                    const errorElement = fieldWrapper?.querySelector?.('[data-field-error]');

                    if (!errorElement) {
                        return false;
                    }

                    return !errorElement.classList.contains('hidden')
                        && String(errorElement.textContent || '').trim() !== '';
                },
                refreshAllQuestionStates() {
                    const form = this.formElement();

                    if (!form) {
                        return;
                    }

                    form.querySelectorAll('[data-assessment-field]').forEach((fieldWrapper) => {
                        this.syncQuestionState(fieldWrapper.dataset.fieldId);
                    });
                },
                syncQuestionState(fieldId) {
                    const normalizedFieldId = Number(fieldId);
                    const fieldWrapper = this.getFieldWrapper(normalizedFieldId);

                    if (!normalizedFieldId || !fieldWrapper) {
                        return;
                    }

                    const hasAnswer = this.fieldHasContent(fieldWrapper);
                    const hasVisibleError = this.fieldHasVisibleError(fieldWrapper);
                    const validation = hasAnswer
                        ? this.resolveFieldValidation(fieldWrapper)
                        : {
                            valid: true,
                            fieldId: normalizedFieldId,
                            message: null,
                        };

                    this.questionStateByFieldId[String(normalizedFieldId)] = {
                        answered: hasAnswer && validation.valid && !hasVisibleError,
                        hasAnswer,
                        invalid: hasVisibleError || (hasAnswer && !validation.valid),
                        flagged: this.isFieldFlagged(normalizedFieldId),
                        assessmentIndex: Number(fieldWrapper.dataset.assessmentIndex ?? 0),
                    };
                },
                fieldHasContent(fieldWrapper) {
                    const fieldType = fieldWrapper.dataset.fieldType ?? 'text';
                    const hasExistingFile = fieldWrapper.dataset.hasExistingFile === '1';

                    if (fieldType === 'radio') {
                        return Array.from(fieldWrapper.querySelectorAll('input[type="radio"]')).some((input) => input.checked);
                    }

                    if (fieldType === 'checkbox') {
                        return Array.from(fieldWrapper.querySelectorAll('input[type="checkbox"]')).some((input) => input.checked);
                    }

                    if (fieldType === 'file') {
                        const fileInputMode = fieldWrapper.dataset.fileInputMode || 'file';

                        if (fileInputMode === 'link') {
                            const input = fieldWrapper.querySelector('input[type="url"]');
                            return String(input?.value || '').trim() !== '' || hasExistingFile;
                        }

                        const input = fieldWrapper.querySelector('input[type="file"]');

                        return Boolean(input?.files?.length) || hasExistingFile;
                    }

                    if (fieldType === 'repeater') {
                        return Array.from(this.extractRepeaterRows(fieldWrapper).values()).some((inputs) => {
                            return inputs.some((input) => String(input.value || '').trim() !== '');
                        });
                    }

                    const input = fieldType === 'textarea'
                        ? fieldWrapper.querySelector('textarea')
                        : (fieldType === 'select'
                            ? fieldWrapper.querySelector('select')
                            : fieldWrapper.querySelector('input:not([type="radio"]):not([type="checkbox"]):not([type="file"]):not([type="hidden"])'));

                    if (!input) {
                        return false;
                    }

                    const value = String(input.value || '').trim();

                    if (fieldType === 'select') {
                        const allowsOtherInput = fieldWrapper.dataset.allowOtherInput === '1';
                        const selectOtherOptionValue = String(fieldWrapper.dataset.selectOtherOptionValue || '').trim();

                        if (!value) {
                            return false;
                        }

                        if (allowsOtherInput && value === selectOtherOptionValue) {
                            return true;
                        }

                        return true;
                    }

                    return value !== '';
                },
                extractRepeaterRows(fieldWrapper) {
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

                    return rows;
                },
                focusFieldById(fieldId) {
                    if (!fieldId) {
                        this.scrollToTop();

                        return;
                    }

                    const fieldWrapper = this.getFieldWrapper(fieldId);

                    if (!fieldWrapper) {
                        this.scrollToTop();

                        return;
                    }

                    const targetAssessmentIndex = Number(fieldWrapper.dataset.assessmentIndex ?? this.currentAssessmentIndex);
                    this.currentQuestionFieldId = Number(fieldId);

                    if (targetAssessmentIndex !== this.currentAssessmentIndex) {
                        this.currentAssessmentIndex = targetAssessmentIndex;
                        this.showFinishModal = false;

                        this.$nextTick(() => {
                            const nextWrapper = this.getFieldWrapper(fieldId);

                            if (nextWrapper) {
                                this.scrollAndFocusField(nextWrapper);
                            }
                        });

                        return;
                    }

                    this.scrollAndFocusField(fieldWrapper);
                },
                resolveFocusTarget(fieldWrapper) {
                    return fieldWrapper.querySelector('input:not([type="hidden"]), select, textarea, button');
                },
                scrollAndFocusField(fieldWrapper) {
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
