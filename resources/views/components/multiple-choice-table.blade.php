@props([
    'id',
    'name',
    'headers' => [],
    'items' => [],
    'selected' => [],
    'description' => null,
    'searchPlaceholder' => 'Cari data...',
    'emptyMessage' => 'Data tidak tersedia.',
    'selectedTitle' => 'Data Terpilih',
    'ajaxUrl' => null,
    'pageSize' => 10,
    'initialSelectedItems' => [],
])

@php
    $selectedValues = collect($selected)->map(fn ($value) => (string) $value)->all();
    $normalizedInitialSelectedItems = collect($initialSelectedItems)
        ->map(function ($item) {
            return [
                'id' => (string) data_get($item, 'id', ''),
                'label' => (string) data_get($item, 'label', data_get($item, 'text', '')),
                'description' => (string) data_get($item, 'description', ''),
                'cells' => collect(data_get($item, 'cells', []))->values()->all(),
                'payload' => data_get($item, 'payload', []),
            ];
        })
        ->filter(fn ($item) => filled($item['id']))
        ->values()
        ->all();
@endphp

@once
    @push('styles')
        <style>
            .multiple-choice-table {
                border: 1px solid #e4e6fc;
                border-radius: .2rem;
                padding: 1rem;
                background: #fff;
            }

            .multiple-choice-table__toolbar {
                display: flex;
                gap: 0.75rem;
                flex-wrap: wrap;
                justify-content: space-between;
                align-items: start;
                margin-bottom: 1rem;
            }

            .multiple-choice-table__search {
                min-width: 260px;
                flex: 1 1 320px;
            }

            .multiple-choice-table__table-wrapper {
                border: 1px solid #e4e6fc;
                border-radius: .2rem;
                overflow-x: auto;
                overflow-y: visible;
                position: relative;
            }

            .multiple-choice-table__toolbar-filters {
                margin-bottom: 1rem;
            }

            .multiple-choice-table__table {
                margin-bottom: 0;
                border-collapse: separate;
                border-spacing: 0;
            }

            .multiple-choice-table .dataTables_wrapper .dataTables_length,
            .multiple-choice-table .dataTables_wrapper .dataTables_info,
            .multiple-choice-table .dataTables_wrapper .dataTables_paginate {
                padding-top: 0.85rem;
            }

            .multiple-choice-table__table thead th {
                position: sticky;
                top: 0;
                z-index: 99;
                background-color: #f8f9fa !important;
                background-clip: padding-box;
                opacity: 1;
                box-shadow: 0 1px 0 #e4e6fc;
            }

            .multiple-choice-table__table tbody tr {
                cursor: pointer;
                transition: background-color 0.2s ease;
            }

            .multiple-choice-table__table tbody tr:hover {
                background: #f8f9ff;
            }

            .multiple-choice-table__table tbody tr.is-selected {
                background: rgba(103, 119, 239, 0.08);
            }

            .multiple-choice-table__empty-search {
                border-top: 1px solid #e4e6fc;
                padding: 1rem;
                text-align: center;
                color: #6c757d;
                background: #fcfcff;
            }

            .multiple-choice-table__selected-summary {
                display: none;
                margin-top: 0.75rem;
                color: #6c757d;
                font-size: 0.9rem;
            }

            .multiple-choice-table__selected-summary.is-visible {
                display: block;
            }

            .multiple-choice-table__footer {
                display: flex;
                gap: 0.75rem;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
                margin-top: 1rem;
            }

            .multiple-choice-table__pagination {
                display: flex;
                gap: 0.5rem;
                align-items: center;
                flex-wrap: wrap;
            }

            .multiple-choice-table__page-label {
                min-width: 108px;
                text-align: center;
                color: #6c757d;
                font-size: 0.9rem;
            }

            @media (max-width: 767.98px) {
                .multiple-choice-table {
                    padding: 0.875rem;
                }

                .multiple-choice-table__toolbar {
                    align-items: stretch;
                }

                .multiple-choice-table__toolbar-actions {
                    width: 100%;
                    display: flex;
                    gap: 0.5rem;
                }

                .multiple-choice-table__toolbar-actions .btn {
                    flex: 1 1 auto;
                }

                .multiple-choice-table__footer {
                    align-items: stretch;
                }

                .multiple-choice-table__pagination {
                    width: 100%;
                    justify-content: space-between;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            (function() {
                function escapeHtml(value) {
                    const node = document.createElement('div');
                    node.textContent = value == null ? '' : String(value);

                    return node.innerHTML;
                }

                function parseJson(value, fallback) {
                    try {
                        const parsed = JSON.parse(value || 'null');

                        return parsed ?? fallback;
                    } catch (error) {
                        return fallback;
                    }
                }

                function normalizeItem(rawItem) {
                    const payload = rawItem && typeof rawItem.payload === 'object' && rawItem.payload !== null ? rawItem.payload : {};
                    const cells = Array.isArray(rawItem && rawItem.cells) ? rawItem.cells : [];
                    const id = String(rawItem && rawItem.id != null ? rawItem.id : '');
                    const label = String(rawItem && (rawItem.label ?? rawItem.text ?? id));
                    const description = String(rawItem && rawItem.description != null ? rawItem.description : '');
                    const search = [label, description]
                        .concat(cells)
                        .filter((value) => value != null && String(value).trim() !== '')
                        .join(' ')
                        .toLowerCase();

                    return {
                        id: id,
                        label: label,
                        description: description,
                        cells: cells,
                        payload: payload,
                        search: search,
                    };
                }

                function buildRowMarkup(item, tableId, isSelected, rowIndex, inputName) {
                    const cellsHtml = item.cells.map((cell) => {
                        return '<td class="align-middle">' + escapeHtml(cell == null ? '-' : String(cell)) + '</td>';
                    }).join('');

                    return `
                        <tr
                            data-role="mct-row"
                            data-item-id="${escapeHtml(item.id)}"
                            data-item-label="${escapeHtml(item.label)}"
                            data-item-description="${escapeHtml(item.description)}"
                            data-search="${escapeHtml(item.search)}"
                            data-item-payload='${escapeHtml(JSON.stringify(item.payload || {}))}'
                            class="${isSelected ? 'is-selected' : ''}"
                        >
                            <td class="text-center align-middle">
                                <div class="custom-control custom-checkbox d-inline-block">
                                    <input
                                        type="checkbox"
                                        class="custom-control-input"
                                        id="${escapeHtml(tableId)}-item-${rowIndex}"
                                        data-role="mct-checkbox"
                                        data-input-name="${escapeHtml(inputName)}"
                                        ${isSelected ? 'checked' : ''}
                                    >
                                    <label class="custom-control-label" for="${escapeHtml(tableId)}-item-${rowIndex}"></label>
                                </div>
                            </td>
                            ${cellsHtml}
                        </tr>
                    `;
                }

                function buildEmptyRowMarkup(colspan, message) {
                    return `
                        <tr data-role="mct-empty-row">
                            <td colspan="${colspan}" class="text-center text-muted py-4">
                                ${escapeHtml(message)}
                            </td>
                        </tr>
                    `;
                }

                function initMultipleChoiceTable(element) {
                    if (!element || element.dataset.initialized === 'true') {
                        return;
                    }

                    element.dataset.initialized = 'true';

                    const tableId = element.dataset.tableId;
                    const inputName = element.dataset.inputName;
                    const ajaxUrl = element.dataset.ajaxUrl || '';
                    const isRemote = ajaxUrl !== '';
                    const pageSize = Math.max(Number(element.dataset.pageSize || 10), 1);
                    let selectedTitle = element.dataset.selectedTitle || 'Data Terpilih';
                    let emptyMessage = element.dataset.emptyMessage || 'Data tidak tersedia.';

                    const searchInput = element.querySelector('[data-role="mct-search"]');
                    const selectAllButton = element.querySelector('[data-action="select-all"]');
                    const clearButton = element.querySelector('[data-action="clear-all"]');
                    const masterCheckbox = element.querySelector('[data-role="mct-master-checkbox"]');
                    const selectedSummaryNode = element.querySelector('[data-role="mct-selected-summary"]');
                    const hiddenInputsNode = element.querySelector('[data-role="mct-hidden-inputs"]');
                    const tbodyNode = element.querySelector('[data-role="mct-body"]');
                    const paginationInfoNode = element.querySelector('[data-role="mct-pagination-info"]');
                    const paginationLabelNode = element.querySelector('[data-role="mct-pagination-label"]');
                    const previousPageButton = element.querySelector('[data-action="prev-page"]');
                    const nextPageButton = element.querySelector('[data-action="next-page"]');
                    const footerNode = element.querySelector('[data-role="mct-footer"]');
                    const columnCount = Number(element.dataset.columnCount || 1);

                    const selectedMap = new Map();
                    const initialSelectedItems = parseJson(element.dataset.initialSelectedItems, []);
                    let currentPage = 1;
                    let lastPage = 1;
                    let totalItems = 0;
                    let rangeFrom = 0;
                    let rangeTo = 0;
                    let keyword = '';
                    let searchTimer = null;
                    let remoteItems = [];
                    let localItems = [];

                    initialSelectedItems
                        .map(normalizeItem)
                        .filter((item) => item.id !== '')
                        .forEach((item) => {
                            selectedMap.set(item.id, item);
                        });

                    function getCheckbox(row) {
                        return row.querySelector('[data-role="mct-checkbox"]');
                    }

                    function hydrateItemFromRow(row) {
                        return normalizeItem({
                            id: row.dataset.itemId || '',
                            label: row.dataset.itemLabel || '',
                            description: row.dataset.itemDescription || '',
                            payload: parseJson(row.dataset.itemPayload, {}),
                            cells: Array.from(row.querySelectorAll('td'))
                                .slice(1)
                                .map((cell) => cell.textContent.trim()),
                        });
                    }

                    function getSelectedItems() {
                        return Array.from(selectedMap.values()).map((item) => ({
                            id: item.id,
                            label: item.label,
                            description: item.description,
                            payload: item.payload,
                        }));
                    }

                    function emitChange() {
                        const selectedItems = getSelectedItems();

                        element.dispatchEvent(new CustomEvent('multiple-choice-table:change', {
                            bubbles: true,
                            detail: {
                                tableId: tableId,
                                inputName: inputName,
                                selectedIds: selectedItems.map((item) => item.id),
                                selectedItems: selectedItems,
                            },
                        }));
                    }

                    function updateHiddenInputs() {
                        if (!hiddenInputsNode) {
                            return;
                        }

                        hiddenInputsNode.innerHTML = '';

                        Array.from(selectedMap.values()).forEach((item) => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = inputName + '[]';
                            input.value = item.id;
                            hiddenInputsNode.appendChild(input);
                        });
                    }

                    function updateSelectedSummary() {
                        if (!selectedSummaryNode) {
                            return;
                        }

                        const selectedItems = Array.from(selectedMap.values());

                        if (selectedItems.length === 0) {
                            selectedSummaryNode.textContent = '';
                            selectedSummaryNode.classList.remove('is-visible');

                            return;
                        }

                        const previewLabels = selectedItems.slice(0, 3).map((item) => item.label);
                        let summaryText = selectedTitle + ': ' + selectedItems.length + ' dipilih';

                        if (previewLabels.length > 0) {
                            summaryText += ' (' + previewLabels.join(', ');

                            if (selectedItems.length > previewLabels.length) {
                                summaryText += ' dan ' + (selectedItems.length - previewLabels.length) + ' lainnya';
                            }

                            summaryText += ')';
                        }

                        selectedSummaryNode.textContent = summaryText;
                        selectedSummaryNode.classList.add('is-visible');
                    }

                    function getRenderedRows() {
                        return Array.from(element.querySelectorAll('[data-role="mct-row"]'));
                    }

                    function getVisibleItems() {
                        if (isRemote) {
                            return remoteItems;
                        }

                        return localItems.filter((item) => {
                            return keyword === '' || item.search.includes(keyword);
                        });
                    }

                    function updateMasterCheckbox() {
                        if (!masterCheckbox) {
                            return;
                        }

                        const visibleItems = getVisibleItems().filter((item) => item.id !== '');
                        const visibleSelectedCount = visibleItems.filter((item) => {
                            return selectedMap.has(item.id);
                        }).length;

                        masterCheckbox.checked = visibleItems.length > 0 && visibleSelectedCount === visibleItems.length;
                        masterCheckbox.indeterminate = visibleSelectedCount > 0 && visibleSelectedCount < visibleItems.length;
                    }

                    function syncRenderedRows() {
                        getRenderedRows().forEach((row) => {
                            const checkbox = getCheckbox(row);
                            const isSelected = selectedMap.has(String(row.dataset.itemId || ''));

                            if (checkbox) {
                                checkbox.checked = isSelected;
                            }

                            row.classList.toggle('is-selected', isSelected);
                        });

                        updateMasterCheckbox();
                    }

                    function syncState(shouldEmitChange) {
                        updateHiddenInputs();
                        updateSelectedSummary();
                        syncRenderedRows();

                        if (shouldEmitChange !== false) {
                            emitChange();
                        }
                    }

                    function setItemSelected(item, checked) {
                        if (!item || item.id === '') {
                            return;
                        }

                        if (checked) {
                            selectedMap.set(item.id, item);
                        } else {
                            selectedMap.delete(item.id);
                        }
                    }

                    function setRowsSelection(rows, checked) {
                        rows.forEach((row) => {
                            setItemSelected(hydrateItemFromRow(row), checked);
                        });

                        syncState();
                    }

                    function setItemsSelection(items, checked) {
                        items.forEach((item) => {
                            setItemSelected(item, checked);
                        });

                        syncState();
                    }

                    function replaceLocalItems(items, options) {
                        if (isRemote) {
                            return;
                        }

                        const detail = options && typeof options === 'object' ? options : {};
                        const normalizedItems = Array.isArray(items) ? items.map(normalizeItem).filter((item) => item.id !== '') : [];
                        const itemMap = new Map(normalizedItems.map((item) => [item.id, item]));

                        localItems = normalizedItems;

                        if (typeof detail.emptyMessage === 'string' && detail.emptyMessage.trim() !== '') {
                            emptyMessage = detail.emptyMessage;
                        }

                        if (typeof detail.selectedTitle === 'string' && detail.selectedTitle.trim() !== '') {
                            selectedTitle = detail.selectedTitle;
                        }

                        if (searchInput) {
                            searchInput.value = '';
                        }

                        keyword = '';

                        if (Array.isArray(detail.selectedIds)) {
                            selectedMap.clear();

                            detail.selectedIds
                                .map((id) => String(id))
                                .forEach((id) => {
                                    if (itemMap.has(id)) {
                                        selectedMap.set(id, itemMap.get(id));
                                    }
                                });
                        } else if (detail.preserveSelection === true) {
                            const nextSelectedEntries = Array.from(selectedMap.keys())
                                .filter((id) => itemMap.has(id))
                                .map((id) => [id, itemMap.get(id)]);

                            selectedMap.clear();
                            nextSelectedEntries.forEach(([id, item]) => {
                                selectedMap.set(id, item);
                            });
                        } else {
                            selectedMap.clear();
                        }

                        renderLocalRows();
                        syncState(detail.emitChange !== false);
                    }

                    function bindRowEvents(rows) {
                        rows.forEach((row) => {
                            const checkbox = getCheckbox(row);

                            if (!checkbox || row.dataset.bound === 'true') {
                                return;
                            }

                            row.dataset.bound = 'true';

                            checkbox.addEventListener('change', function() {
                                setRowsSelection([row], checkbox.checked);
                            });

                            row.addEventListener('click', function(event) {
                                if (event.target.closest('input, label, button, a')) {
                                    return;
                                }

                                checkbox.checked = !checkbox.checked;
                                setRowsSelection([row], checkbox.checked);
                            });
                        });
                    }

                    function renderRows(items, message) {
                        if (!tbodyNode) {
                            return;
                        }

                        if (!Array.isArray(items) || items.length === 0) {
                            tbodyNode.innerHTML = buildEmptyRowMarkup(columnCount, message || emptyMessage);
                            updateMasterCheckbox();

                            return;
                        }

                        tbodyNode.innerHTML = items.map((item, index) => {
                            return buildRowMarkup(
                                item,
                                tableId,
                                selectedMap.has(item.id),
                                (isRemote ? ((currentPage - 1) * pageSize) : 0) + index + 1,
                                inputName
                            );
                        }).join('');

                        bindRowEvents(getRenderedRows());
                        syncRenderedRows();
                    }

                    function updatePaginationFooter() {
                        if (!isRemote || !footerNode) {
                            return;
                        }

                        if (paginationInfoNode) {
                            paginationInfoNode.textContent = totalItems === 0 ?
                                'Tidak ada data guru.' :
                                'Menampilkan ' + rangeFrom + ' sampai ' + rangeTo + ' dari ' + totalItems + ' entri';
                        }

                        if (paginationLabelNode) {
                            paginationLabelNode.textContent = 'Halaman ' + currentPage + ' / ' + lastPage;
                        }

                        if (previousPageButton) {
                            previousPageButton.disabled = currentPage <= 1;
                        }

                        if (nextPageButton) {
                            nextPageButton.disabled = currentPage >= lastPage;
                        }
                    }

                    function fetchRemoteRows() {
                        if (!isRemote) {
                            return;
                        }

                        if (tbodyNode) {
                            tbodyNode.innerHTML = buildEmptyRowMarkup(columnCount, 'Memuat data guru...');
                        }

                        const params = new URLSearchParams();
                        params.set('page', String(currentPage));
                        params.set('per_page', String(pageSize));

                        if (keyword !== '') {
                            params.set('q', keyword);
                        }

                        window.fetch(ajaxUrl + '?' + params.toString(), {
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                credentials: 'same-origin',
                            })
                            .then(function(response) {
                                if (!response.ok) {
                                    throw new Error('HTTP ' + response.status);
                                }

                                return response.json();
                            })
                            .then(function(response) {
                                const items = Array.isArray(response.items) ? response.items : [];
                                const pagination = response.pagination || {};

                                currentPage = Number(pagination.current_page || currentPage || 1);
                                lastPage = Math.max(Number(pagination.last_page || 1), 1);
                                totalItems = Number(pagination.total || items.length || 0);
                                rangeFrom = Number(pagination.from || 0);
                                rangeTo = Number(pagination.to || 0);
                                remoteItems = items.map(normalizeItem);

                                renderRows(remoteItems, items.length === 0 ? (keyword === '' ?
                                    emptyMessage :
                                    'Tidak ada data yang cocok dengan pencarian.') : '');
                                updatePaginationFooter();
                            })
                            .catch(function() {
                                totalItems = 0;
                                rangeFrom = 0;
                                rangeTo = 0;
                                lastPage = 1;
                                currentPage = 1;
                                remoteItems = [];
                                renderRows([], 'Gagal memuat data guru. Coba lagi.');
                                updatePaginationFooter();
                            });
                    }

                    function renderLocalRows() {
                        const visibleItems = getVisibleItems();
                        const message = keyword === '' ? emptyMessage : 'Tidak ada data yang cocok dengan pencarian.';

                        renderRows(visibleItems, message);
                    }

                    function initLocalMode() {
                        const initialRows = getRenderedRows();

                        localItems = initialRows
                            .map((row) => hydrateItemFromRow(row))
                            .filter((item) => item.id !== '');

                        initialRows.forEach((row) => {
                            const checkbox = getCheckbox(row);

                            if (checkbox && checkbox.checked) {
                                setItemSelected(hydrateItemFromRow(row), true);
                            }
                        });

                        renderLocalRows();
                        syncState();
                    }

                    function applySearch() {
                        const nextKeyword = ((searchInput ? searchInput.value : '') || '').trim().toLowerCase();

                        if (isRemote) {
                            keyword = nextKeyword;
                            currentPage = 1;

                            if (searchTimer) {
                                window.clearTimeout(searchTimer);
                            }

                            searchTimer = window.setTimeout(function() {
                                fetchRemoteRows();
                            }, 300);

                            return;
                        }

                        keyword = nextKeyword;
                        renderLocalRows();
                    }

                    if (searchInput) {
                        searchInput.addEventListener('input', function() {
                            applySearch();
                        });
                    }

                    if (selectAllButton) {
                        selectAllButton.addEventListener('click', function() {
                            setItemsSelection(getVisibleItems(), true);
                        });
                    }

                    if (clearButton) {
                        clearButton.addEventListener('click', function() {
                            selectedMap.clear();
                            syncState();
                        });
                    }

                    if (masterCheckbox) {
                        masterCheckbox.addEventListener('change', function() {
                            setItemsSelection(getVisibleItems(), masterCheckbox.checked);
                        });
                    }

                    if (previousPageButton) {
                        previousPageButton.addEventListener('click', function() {
                            if (currentPage <= 1) {
                                return;
                            }

                            currentPage -= 1;
                            fetchRemoteRows();
                        });
                    }

                    if (nextPageButton) {
                        nextPageButton.addEventListener('click', function() {
                            if (currentPage >= lastPage) {
                                return;
                            }

                            currentPage += 1;
                            fetchRemoteRows();
                        });
                    }

                    element.addEventListener('multiple-choice-table:set-items', function(event) {
                        const detail = event.detail || {};

                        replaceLocalItems(detail.items || [], detail);
                    });

                    element.multipleChoiceTable = {
                        getSelectedItems: getSelectedItems,
                        setItems: replaceLocalItems,
                    };

                    if (isRemote) {
                        syncState();
                        fetchRemoteRows();
                    } else {
                        initLocalMode();
                    }
                }

                document.addEventListener('DOMContentLoaded', function() {
                    document.querySelectorAll('[data-multiple-choice-table]').forEach((element) => {
                        initMultipleChoiceTable(element);
                    });
                });
            })();
        </script>
    @endpush
@endonce

<div class="multiple-choice-table"
    data-multiple-choice-table
    data-table-id="{{ $id }}"
    data-input-name="{{ $name }}"
    data-selected-title="{{ $selectedTitle }}"
    data-ajax-url="{{ $ajaxUrl ?? '' }}"
    data-page-size="{{ $pageSize }}"
    data-column-count="{{ count($headers) + 1 }}"
    data-empty-message="{{ $emptyMessage }}"
    data-initial-selected-items='@json($normalizedInitialSelectedItems)'>
    <div class="multiple-choice-table__toolbar">
        <div class="multiple-choice-table__search">
            <input type="text" class="form-control" data-role="mct-search" placeholder="{{ $searchPlaceholder }}">
            @if ($description)
                <small class="text-muted d-block mt-2">{{ $description }}</small>
            @endif
        </div>
        <div class="multiple-choice-table__toolbar-actions">
            <button type="button" class="btn btn-outline-primary btn-sm" data-action="select-all">
                Pilih Semua
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm" data-action="clear-all">
                Kosongkan
            </button>
        </div>
    </div>

    <div data-role="mct-hidden-inputs"></div>

    <div class="multiple-choice-table__table-wrapper table-responsive ">
        <table class="table table-striped table-hover multiple-choice-table__table" id="{{ $id }}-table">
            <thead>
                <tr>
                    <th class="text-center" style="width: 56px;">
                        <div class="custom-control custom-checkbox d-inline-block">
                            <input type="checkbox" class="custom-control-input" id="{{ $id }}-master-checkbox"
                                data-role="mct-master-checkbox">
                            <label class="custom-control-label" for="{{ $id }}-master-checkbox"></label>
                        </div>
                    </th>
                    @foreach ($headers as $header)
                        <th>{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody data-role="mct-body">
                @forelse ($items as $item)
                    @php
                        $itemId = (string) data_get($item, 'id', '');
                        $itemLabel = (string) data_get($item, 'label', data_get($item, 'text', $itemId));
                        $itemDescription = (string) data_get($item, 'description', '');
                        $itemCells = collect(data_get($item, 'cells', []))->values()->all();
                        $itemPayload = data_get($item, 'payload', []);
                        $searchValue = mb_strtolower(
                            trim(
                                collect([$itemLabel, $itemDescription])
                                    ->merge($itemCells)
                                    ->filter(fn ($value) => filled($value))
                                    ->implode(' '),
                            ),
                        );
                        $isSelected = in_array($itemId, $selectedValues, true);
                    @endphp
                    <tr data-role="mct-row" data-item-id="{{ $itemId }}" data-item-label="{{ $itemLabel }}"
                        data-item-description="{{ $itemDescription }}" data-search="{{ $searchValue }}"
                        data-item-payload='@json($itemPayload)' class="{{ $isSelected ? 'is-selected' : '' }}">
                        <td class="text-center align-middle">
                            <div class="custom-control custom-checkbox d-inline-block">
                                <input type="checkbox" class="custom-control-input"
                                    id="{{ $id }}-item-{{ $loop->iteration }}" data-role="mct-checkbox"
                                    @checked($isSelected)>
                                <label class="custom-control-label"
                                    for="{{ $id }}-item-{{ $loop->iteration }}"></label>
                            </div>
                        </td>
                        @foreach ($headers as $columnIndex => $header)
                            <td class="align-middle">
                                {{ $itemCells[$columnIndex] ?? '-' }}
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr data-role="mct-empty-row">
                        <td colspan="{{ count($headers) + 1 }}" class="text-center text-muted py-4">
                            {{ $emptyMessage }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="multiple-choice-table__empty-search d-none" data-role="mct-empty-search">
            Tidak ada data yang cocok dengan pencarian.
        </div>
    </div>

    <div class="multiple-choice-table__selected-summary" data-role="mct-selected-summary"></div>

    <div class="multiple-choice-table__footer {{ $ajaxUrl ? '' : 'd-none' }}" data-role="mct-footer">
        <small class="text-muted" data-role="mct-pagination-info"></small>
        <div class="multiple-choice-table__pagination">
            <button type="button" class="btn btn-outline-secondary btn-sm" data-action="prev-page">
                Sebelumnya
            </button>
            <span class="multiple-choice-table__page-label" data-role="mct-pagination-label">Halaman 1 / 1</span>
            <button type="button" class="btn btn-outline-secondary btn-sm" data-action="next-page">
                Berikutnya
            </button>
        </div>
    </div>
</div>
