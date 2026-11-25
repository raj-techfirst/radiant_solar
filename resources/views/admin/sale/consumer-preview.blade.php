@php
    $missingInExcel = $missingInExcel ?? [];
    $matchedCount = count($matched);
    $unmatchedCount = count($unmatched);
    $missingInExcelCount = count($missingInExcel);
    $ignoredCount = count($ignored);
    $recommendedFields = [
        'consumer_name' => 'Consumer Name',
        'discom_name' => 'DISCOM',
        'circle_name' => 'Circle',
        'division_name' => 'Division',
        'sub_division_name' => 'Sub Division',
    ];
    $recommendedValue = function ($row, $source, $field) {
        return trim((string) ($row[$source][$field] ?? ''));
    };
    $sameRecommendedValue = function ($first, $second) {
        $first = preg_replace('/\s+/', ' ', trim((string) $first));
        $second = preg_replace('/\s+/', ' ', trim((string) $second));

        return strcasecmp($first, $second) === 0;
    };
@endphp

<style>
    #preview-matched-section .preview-table-scroll,
    #preview-unmatched-section .preview-table-scroll,
    #preview-missing-section .preview-table-scroll,
    #preview-ignored-section .preview-table-scroll {
        max-height: calc(100vh - 260px);
        min-height: 300px;
        overflow: auto;
        scrollbar-width: thin;
    }

    .preview-table-scroll::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }

    .preview-table-scroll::-webkit-scrollbar-thumb {
        background: #c9c9c9;
        border-radius: 4px;
    }

    .preview-compact-table {
        margin-bottom: 0;
        min-width: 760px;
    }

    #consumer-preview-tabs {
        flex-wrap: nowrap;
        overflow-x: auto;
        overflow-y: hidden;
    }

    #consumer-preview-tabs .nav-link {
        white-space: nowrap;
    }

    .preview-compact-table thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background-color: #f4f5f7;
        padding: 0.35rem 0.5rem;
        font-size: 0.82rem;
        text-transform: uppercase;
        letter-spacing: .02em;
        white-space: nowrap;
        box-shadow: inset 0 -1px 0 #dee2e6;
    }

    .preview-compact-table tbody td {
        padding: 0.2rem 0.5rem;
        font-size: 0.9rem;
        line-height: 1.25;
        vertical-align: middle;
        white-space: nowrap;
    }

    .preview-compact-table tbody tr:nth-child(even) {
        background-color: #fafafa;
    }

    .preview-compact-table tbody tr:hover {
        background-color: #fff6ec;
    }

    .preview-compact-table th.pv-num,
    .preview-compact-table td.pv-num {
        width: clamp(90px, 8vw, 110px);
        max-width: clamp(90px, 8vw, 110px);
        padding-left: 0.35rem;
        padding-right: 0.35rem;
    }

    .preview-compact-table th.pv-num {
        white-space: normal;
        line-height: 1.1;
    }

    .pv-cell {
        max-width: clamp(120px, 15vw, 220px);
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .pv-cell.pv-name {
        width: clamp(110px, 12vw, 160px);
        max-width: clamp(110px, 12vw, 160px);
        white-space: normal;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        line-height: 1.2;
    }

    /* per-column widths */
    .pv-col-discom_name .pv-cell {
        max-width: clamp(160px, 20vw, 300px);
    }

    .pv-col-circle_name .pv-cell,
    .pv-col-division_name .pv-cell {
        max-width: clamp(100px, 10vw, 140px);
    }

    .pv-db {
        color: #dc3545;
        font-size: 0.82rem;
    }

    .pv-tag {
        display: inline-block;
        margin-right: 3px;
        padding: 0 3px;
        font-size: 0.7rem;
        font-weight: 700;
        color: #fff;
        background: #dc3545;
        border-radius: 3px;
        line-height: 1.2;
    }

    /* tablets */
    @media (max-width: 991.98px) {
        #preview-matched-section .preview-table-scroll,
        #preview-unmatched-section .preview-table-scroll,
    #preview-missing-section .preview-table-scroll,
        #preview-ignored-section .preview-table-scroll {
            max-height: calc(100vh - 220px);
        }

        .preview-compact-table thead th {
            font-size: 0.76rem;
        }

        .preview-compact-table tbody td {
            font-size: 0.84rem;
        }

        .pv-db {
            font-size: 0.76rem;
        }
    }

    /* phones */
    @media (max-width: 575.98px) {
        #preview-matched-section .preview-table-scroll,
        #preview-unmatched-section .preview-table-scroll,
    #preview-missing-section .preview-table-scroll,
        #preview-ignored-section .preview-table-scroll {
            max-height: calc(100vh - 200px);
            min-height: 220px;
        }

        .preview-compact-table thead th {
            font-size: 0.7rem;
            padding: 0.25rem 0.35rem;
        }

        .preview-compact-table tbody td {
            font-size: 0.78rem;
            padding: 0.15rem 0.35rem;
        }

        .pv-db {
            font-size: 0.7rem;
        }

        #preview-matched-section h5,
        #preview-unmatched-section h5,
        #preview-missing-section h5,
        #preview-ignored-section h5 {
            font-size: 1rem;
        }
    }
</style>

<ul class="nav nav-tabs mb-2" id="consumer-preview-tabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="preview-matched-tab" type="button" role="tab"
            data-preview-filter="matched">
            Recommended Update: {{ $matchedCount }}
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="preview-unmatched-tab" type="button" role="tab"
            data-preview-filter="unmatched">
            Not Exist in DB: {{ $unmatchedCount }}
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="preview-missing-tab" type="button" role="tab"
            data-preview-filter="missing">
            Not Exist in Excel: {{ $missingInExcelCount }}
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="preview-ignored-tab" type="button" role="tab"
            data-preview-filter="ignored">
            Ignored: {{ $ignoredCount }}
        </button>
    </li>
</ul>

<div id="preview-matched-section">
    <div class="row">
        <div class="col-12">
            <h5 class="mb-1">Recommended Update Records</h5>
            <div class="table-responsive preview-table-scroll">
                <table class="table table-sm table-bordered preview-compact-table">
                    <thead>
                        <tr>
                            <th style="width: 30px;">
                                <input type="checkbox" class="preview-select-all" data-preview-select-all="matched"
                                    title="Select all visible recommended updates">
                            </th>
                            <th>#</th>
                            <th class="pv-num">Consumer Number</th>
                            @foreach ($recommendedFields as $field => $label)
                                <th class="pv-col-{{ $field }}">{{ $label }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($matched as $key => $row)
                            @php
                                $previewName = trim(implode(' ', array_merge(
                                    array_values($row['excel_recommended'] ?? []),
                                    array_values($row['db_recommended'] ?? [])
                                )));
                            @endphp
                            <tr data-preview-name="{{ $previewName }}"
                                data-preview-number="{{ $row['db_consumer_number'] ?? '' }}">
                                <td>
                                    <input type="checkbox" class="preview-row-checkbox" data-preview-group="matched"
                                        value="{{ $row['sales_master_id'] ?? '' }}">
                                </td>
                                <td>{{ $key + 1 }}</td>
                                <td class="pv-num">{{ $row['db_consumer_number'] ?: ($row['consumer_number'] ?? '-') }}</td>
                                @foreach ($recommendedFields as $field => $label)
                                    @php
                                        $excelText = $recommendedValue($row, 'excel_recommended', $field);
                                        $dbText = $recommendedValue($row, 'db_recommended', $field);
                                    @endphp
                                    <td class="pv-col-{{ $field }}">
                                        <div class="pv-cell {{ $field === 'consumer_name' ? 'pv-name' : '' }}"
                                            title="Excel: {{ $excelText }}">{{ $excelText ?: '-' }}</div>
                                        @unless ($sameRecommendedValue($excelText, $dbText))
                                            <div class="pv-cell pv-db {{ $field === 'consumer_name' ? 'pv-name' : '' }}"
                                                title="DB: {{ $dbText }}"><span class="pv-tag">DB</span>{{ $dbText ?: '-' }}</div>
                                        @endunless
                                    </td>
                                @endforeach
                            </tr>
                        @empty
                            <tr data-preview-empty="1">
                                <td colspan="{{ 3 + count($recommendedFields) }}" class="text-center">No recommended updates.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="preview-unmatched-section" class="mt-2 d-none">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <h5 class="mb-0">Not Matched Records</h5>
            </div>
            <div class="table-responsive preview-table-scroll">
                <table class="table table-sm table-bordered preview-compact-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Consumer Name</th>
                            <th class="pv-num">Consumer Number</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($unmatched as $key => $row)
                            @php
                                $rowRecommended = $row['recommended'] ?? [];
                            @endphp
                            <tr data-preview-name="{{ $row['consumer_name'] ?? '' }}"
                                data-preview-number="{{ $row['consumer_number'] ?? '' }}"
                                data-preview-row="{{ $row['row'] ?? '' }}"
                                data-discom="{{ $rowRecommended['discom_name'] ?? '' }}"
                                data-circle="{{ $rowRecommended['circle_name'] ?? '' }}"
                                data-division="{{ $rowRecommended['division_name'] ?? '' }}"
                                data-sub-division="{{ $rowRecommended['sub_division_name'] ?? '' }}"
                                data-sub-division-id="{{ $row['sub_division_id'] ?? '' }}">
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $row['consumer_name'] ?: '-' }}</td>
                                <td class="pv-num">{{ $row['consumer_number'] ?: '-' }}</td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a class="btn btn-sm btn-outline-primary preview-create-row"
                                            href="{{ route('sales-master-consumer-preview-create-from-unmatched-page', ['row' => $row['row'] ?? '', 'consumer_number' => $row['consumer_number'] ?? '']) }}"
                                            title="Create quotation and/or sales order">
                                            <i data-feather="plus" class="me-25"></i> Create
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger preview-ignore-row"
                                            title="Ignore this record">
                                            <i data-feather="eye-off" class="me-25"></i> Ignore
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr data-preview-empty="1">
                                <td colspan="4" class="text-center">No unmatched records.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="preview-missing-section" class="mt-2 d-none">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <h5 class="mb-0">Not Exist in Excel Records</h5>
            </div>
            <div class="table-responsive preview-table-scroll">
                <table class="table table-sm table-bordered preview-compact-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Consumer Name</th>
                            <th class="pv-num">Consumer Number</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($missingInExcel as $key => $row)
                            <tr data-preview-name="{{ $row['consumer_name'] ?? '' }}"
                                data-preview-number="{{ $row['consumer_number'] ?? '' }}">
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $row['consumer_name'] ?: '-' }}</td>
                                <td class="pv-num">{{ $row['consumer_number'] ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr data-preview-empty="1">
                                <td colspan="3" class="text-center">No DB records missing from Excel.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="preview-ignored-section" class="mt-2 d-none">
    <div class="row">
        <div class="col-12">
            <h5 class="mb-1">Ignored Customers</h5>
            <div class="table-responsive preview-table-scroll">
                <table class="table table-sm table-bordered preview-compact-table">
                    <thead>
                        <tr>
                            <th style="width: 30px;">#</th>
                            <th>Consumer Name</th>
                            <th class="pv-num">Consumer Number</th>
                            <th>Ignore Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($ignored as $key => $row)
                            <tr data-preview-name="{{ $row['consumer_name'] ?? '' }}"
                                data-preview-number="{{ $row['consumer_number'] ?? '' }}">
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $row['consumer_name'] ?: '-' }}</td>
                                <td class="pv-num">{{ $row['consumer_number'] ?: '-' }}</td>
                                <td>{{ $row['ignore_date'] ?: '-' }}</td>
                                <td>
                                    <a class="btn btn-sm btn-outline-primary"
                                        href="{{ route('sales-master-consumer-preview-create-from-unmatched-page', ['row' => 0, 'consumer_number' => $row['consumer_number'] ?? '']) }}"
                                        title="Create quotation and sales order">
                                        Create
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr data-preview-empty="1">
                                <td colspan="5" class="text-center">No ignored customers.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>