@php
    $matchedCount = count($matched);
    $unmatchedCount = count($unmatched);
@endphp

<ul class="nav nav-tabs mb-2" id="consumer-preview-tabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="preview-matched-tab" type="button" role="tab"
            data-preview-filter="matched">
            Matched: {{ $matchedCount }}
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="preview-unmatched-tab" type="button" role="tab"
            data-preview-filter="unmatched">
            Not matched: {{ $unmatchedCount }}
        </button>
    </li>
</ul>

<div id="preview-matched-section">
    <div class="row">
        <div class="col-12">
            <h5 class="mb-1">Matched Records</h5>
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th style="width: 30px;">
                                <input type="checkbox" class="preview-select-all" data-preview-select-all="matched"
                                    title="Select all visible matched records">
                            </th>
                            <th>#</th>
                            <th>Excel Consumer Name</th>
                            <th>DB Consumer Name</th>
                            <th>DB Consumer Number</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($matched as $key => $row)
                            <tr data-preview-name="{{ trim(($row['consumer_name'] ?? '') . ' ' . ($row['db_consumer_name'] ?? '')) }}"
                                data-preview-number="{{ $row['db_consumer_number'] ?? '' }}">
                                <td>
                                    <input type="checkbox" class="preview-row-checkbox" data-preview-group="matched"
                                        value="{{ $row['sales_master_id'] ?? '' }}">
                                </td>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $row['consumer_name'] ?: '-' }}</td>
                                <td>{{ $row['db_consumer_name'] ?: '-' }}</td>
                                <td>{{ $row['db_consumer_number'] ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr data-preview-empty="1">
                                <td colspan="5" class="text-center">No matched records.</td>
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
            <h5 class="mb-1">Not Matched Records</h5>
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th style="width: 30px;">
                                <input type="checkbox" class="preview-select-all" data-preview-select-all="unmatched"
                                    title="Select all visible not matched records">
                            </th>
                            <th>#</th>
                            <th>Consumer Name</th>
                            <th>Consumer Number</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($unmatched as $key => $row)
                            <tr data-preview-name="{{ $row['consumer_name'] ?? '' }}"
                                data-preview-number="{{ $row['consumer_number'] ?? '' }}">
                                <td>
                                    <input type="checkbox" class="preview-row-checkbox" data-preview-group="unmatched"
                                        value="{{ $row['consumer_number'] ?? '' }}">
                                </td>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $row['consumer_name'] ?: '-' }}</td>
                                <td>{{ $row['consumer_number'] ?: '-' }}</td>
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
