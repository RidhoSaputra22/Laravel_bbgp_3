import autofillConfig from './forms.json';

(function () {
    'use strict';

    var BUTTON_ID = 'dev-form-autofill-button';
    var STATUS_ID = 'dev-form-autofill-status';
    var STYLE_ID = 'dev-form-autofill-style';
    var OPTION_TIMEOUT = 5000;
    var OPTION_POLL_INTERVAL = 100;

    if (window.__devFormAutofillLoaded) {
        return;
    }

    window.__devFormAutofillLoaded = true;

    function addStyles() {
        if (document.getElementById(STYLE_ID)) {
            return;
        }

        var style = document.createElement('style');
        style.id = STYLE_ID;
        style.textContent = [
            '#dev-form-autofill-button{position:fixed;right:104px;bottom:30px;z-index:10000;width:56px;height:56px;border:0;border-radius:50%;background:#6f42c1;color:#fff;box-shadow:0 5px 18px rgba(0,0,0,.28);cursor:pointer;font:700 24px/1 Arial,sans-serif;transition:transform .15s,background .15s}',
            '#dev-form-autofill-button:hover{background:#59339d;transform:scale(1.06)}',
            '#dev-form-autofill-button:disabled{cursor:wait;opacity:.7;transform:none}',
            '#dev-form-autofill-status{position:fixed;right:104px;bottom:96px;z-index:10000;max-width:280px;padding:8px 12px;border-radius:6px;background:#212529;color:#fff;box-shadow:0 3px 12px rgba(0,0,0,.22);font:13px/1.4 Arial,sans-serif;opacity:0;pointer-events:none;transition:opacity .15s}',
            '#dev-form-autofill-status.is-visible{opacity:1}',
            '@media (max-width:600px){#dev-form-autofill-button{right:88px;bottom:24px}#dev-form-autofill-status{right:88px;bottom:88px;max-width:220px}}'
        ].join('');

        document.head.appendChild(style);
    }

    function createUi() {
        if (document.getElementById(BUTTON_ID)) {
            return null;
        }

        addStyles();

        var button = document.createElement('button');
        button.id = BUTTON_ID;
        button.type = 'button';
        button.title = 'Isi otomatis form dev';
        button.setAttribute('aria-label', 'Isi otomatis form dev');
        button.textContent = '⚡';

        var status = document.createElement('div');
        status.id = STATUS_ID;
        status.setAttribute('role', 'status');
        status.setAttribute('aria-live', 'polite');

        document.body.appendChild(button);
        document.body.appendChild(status);

        return { button: button, status: status };
    }

    function showStatus(status, message) {
        status.textContent = message;
        status.classList.add('is-visible');
        window.clearTimeout(status.__hideTimer);
        status.__hideTimer = window.setTimeout(function () {
            status.classList.remove('is-visible');
        }, 5000);
    }

    function toArray(value) {
        return Array.isArray(value) ? value : [value];
    }

    function sameValue(option, value) {
        return String(option.value) === String(value) ||
            String(option.textContent).trim() === String(value).trim();
    }

    function hasAllOptions(select, value) {
        return toArray(value).every(function (item) {
            return Array.prototype.some.call(select.options, function (option) {
                return sameValue(option, item);
            });
        });
    }

    function waitForOptions(select, value, timeout) {
        var startedAt = Date.now();

        return new Promise(function (resolve) {
            function check() {
                if (!select.disabled && hasAllOptions(select, value)) {
                    resolve(true);
                    return;
                }

                if (Date.now() - startedAt >= timeout) {
                    resolve(false);
                    return;
                }

                window.setTimeout(check, OPTION_POLL_INTERVAL);
            }

            check();
        });
    }

    function dispatchEvents(element, field) {
        var eventNames = field.event ? [field.event] :
            (element.tagName.toLowerCase() === 'select' ||
                element.type === 'checkbox' || element.type === 'radio'
                ? ['change']
                : ['input', 'change']);

        eventNames.forEach(function (eventName) {
            element.dispatchEvent(new Event(eventName, { bubbles: true }));
        });
    }

    function setSelectValue(select, value) {
        var values = toArray(value);
        var matched = 0;

        Array.prototype.forEach.call(select.options, function (option) {
            var selected = values.some(function (item) {
                return sameValue(option, item);
            });

            option.selected = selected;
            matched += selected ? 1 : 0;
        });

        return matched === values.length;
    }

    function setCheckedValue(element, value) {
        if (typeof value === 'boolean') {
            element.checked = value;
            return true;
        }

        if (Array.isArray(value)) {
            element.checked = value.map(String).indexOf(String(element.value)) !== -1;
            return true;
        }

        element.checked = String(element.value) === String(value);
        return element.type === 'radio' || element.checked;
    }

    async function fillElement(element, field, stats) {
        var type = (element.type || '').toLowerCase();

        if (type === 'file') {
            stats.skipped += 1;
            stats.files += 1;
            return;
        }

        if (element.tagName.toLowerCase() === 'select') {
            if (field.waitForOption && !(await waitForOptions(element, field.value, OPTION_TIMEOUT))) {
                stats.timeout += 1;
                return;
            }

            if (!setSelectValue(element, field.value)) {
                stats.missing += 1;
                return;
            }

            dispatchEvents(element, field);
            stats.filled += 1;
            return;
        }

        if (type === 'checkbox' || type === 'radio') {
            if (!setCheckedValue(element, field.value)) {
                stats.missing += 1;
                return;
            }

            dispatchEvents(element, field);
            stats.filled += 1;
            return;
        }

        if ('value' in element) {
            element.value = field.value === null || field.value === undefined
                ? ''
                : String(field.value);
            dispatchEvents(element, field);
            stats.filled += 1;
            return;
        }

        stats.missing += 1;
    }

    async function fillForm(form, profile, stats) {
        var fields = Array.isArray(profile.fields) ? profile.fields : [];

        for (var i = 0; i < fields.length; i += 1) {
            var field = fields[i];
            var elements;

            try {
                elements = Array.prototype.slice.call(form.querySelectorAll(field.selector));
            } catch (error) {
                stats.invalidSelector += 1;
                continue;
            }

            if (!elements.length) {
                stats.missing += 1;
                continue;
            }

            for (var j = 0; j < elements.length; j += 1) {
                await fillElement(elements[j], field, stats);
            }
        }
    }

    function pathMatches(configuredPath) {
        if (!configuredPath) {
            return true;
        }

        var currentPath = window.location.pathname.replace(/\/$/, '') || '/';

        return toArray(configuredPath).some(function (path) {
            var normalizedPath = String(path).replace(/\/$/, '') || '/';

            if (normalizedPath.slice(-1) === '*') {
                return currentPath.indexOf(normalizedPath.slice(0, -1)) === 0;
            }

            return currentPath === normalizedPath;
        });
    }

    function matchingForms(profile) {
        if (!pathMatches(profile.path)) {
            return [];
        }

        try {
            return Array.prototype.slice.call(document.querySelectorAll(profile.form || 'form'));
        } catch (error) {
            return [];
        }
    }

    async function fillConfiguredForms() {
        var stats = {
            filled: 0,
            missing: 0,
            skipped: 0,
            files: 0,
            timeout: 0,
            invalidSelector: 0,
            forms: 0
        };
        var profiles = Array.isArray(autofillConfig.forms) ? autofillConfig.forms : [];
        var tasks = [];

        profiles.forEach(function (profile) {
            matchingForms(profile).forEach(function (form) {
                stats.forms += 1;
                tasks.push(fillForm(form, profile, stats));
            });
        });

        await Promise.all(tasks);
        return stats;
    }

    function summary(stats) {
        if (!stats.forms) {
            return 'Tidak ada konfigurasi autofill yang cocok di halaman ini.';
        }

        var message = 'Autofill selesai: ' + stats.filled + ' field pada ' + stats.forms + ' form.';

        if (stats.missing) {
            message += ' Tidak ditemukan: ' + stats.missing + '.';
        }

        if (stats.timeout) {
            message += ' Timeout option: ' + stats.timeout + '.';
        }

        if (stats.files) {
            message += ' File dilewati: ' + stats.files + '.';
        }

        if (stats.invalidSelector) {
            message += ' Selector invalid: ' + stats.invalidSelector + '.';
        }

        return message;
    }

    function init() {
        var ui = createUi();

        if (!ui) {
            return;
        }

        ui.button.addEventListener('click', async function () {
            ui.button.disabled = true;
            ui.button.textContent = '…';

            try {
                showStatus(ui.status, summary(await fillConfiguredForms()));
            } catch (error) {
                showStatus(ui.status, 'Autofill gagal. Lihat console untuk detail.');
                console.error('[dev-form-autofill]', error);
            } finally {
                ui.button.disabled = false;
                ui.button.textContent = '⚡';
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
