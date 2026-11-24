@extends('layouts.app')
@section('title', 'Excel Consumer Preview')
@section('content')
    <div class="row">
        <div class="col-12 mb-1">
            <h4 class="content-header-title float-start">Excel Consumer Preview</h4>
            <div class="float-end">
                <a role="button" class="btn btn-sm btn-outline-secondary"
                    href="{{ route('sales-master.index') }}">
                    <i data-feather="arrow-left" class="me-25"></i> Back to Sales Order
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card p-1">
                <form action="{{ route('sales-master-consumer-preview-upload') }}" method="POST"
                    enctype="multipart/form-data" novalidate>
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-1">
                            <label class="form-label" for="excel_file">Excel File<sup class="error">*</sup></label>
                            <input type="file" class="form-control @error('excel_file') is-invalid @enderror"
                                id="excel_file" name="excel_file" accept=".xlsx,.xls,.csv">
                            <small class="text-muted">Required column: Consumer Number. Optional column: Consumer Name.</small>
                            @error('excel_file')
                                <span class="invalid-feedback d-block fw-bold text-danger" role="alert"
                                    style="font-size:1.05rem;">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-6 d-flex align-items-end justify-content-end mb-1">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i data-feather="upload" class="me-25"></i> Upload &amp; Preview
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if ($preview)
        <div id="preview-update-record-wrapper" class="position-fixed bottom-0 end-0 p-3 d-none"
            style="z-index: 1040;">
            <button type="button" class="btn btn-gradient-primary btn-sm shadow" id="preview-update-record-btn"
                data-bs-toggle="modal" data-bs-target="#updateRecordModal">
                <i data-feather="edit-2" class="me-25"></i> Update Record
            </button>
        </div>

        <div class="row mt-1">
            <div class="col-12">
                <div class="card p-1">
                    <div class="row">
                        <div class="col-12">
                            <h3>Filter</h3>
                        </div>
                        <div class="col-sm-12 col-md-4 col-lg-3 custom-input-group">
                            <label class="form-label" for="preview_filter_name">Consumer Name</label>
                            <input type="text" class="form-control" id="preview_filter_name"
                                placeholder="Search by consumer name">
                        </div>
                        <div class="col-sm-12 col-md-4 col-lg-3 custom-input-group">
                            <label class="form-label" for="preview_filter_number">Consumer Number</label>
                            <input type="text" class="form-control" id="preview_filter_number"
                                placeholder="Search by consumer number">
                        </div>
                        <div class="col-sm-12 col-md-4 col-lg-6 custom-input-group pt-2">
                            <div class="d-flex justify-content-end">
                                <button class="btn btn-gradient-primary btn-sm" type="button" id="preview-filter-btn"
                                    data-bs-toggle="tooltip" data-placement="top" title="Click to Filter">
                                    <i data-feather="search"></i>
                                </button>
                                <button class="btn btn-gradient-danger btn-sm ms-1" type="button" id="preview-reset-btn"
                                    data-bs-toggle="tooltip" data-placement="top" title="Click to Reset Filter">
                                    <i data-feather="x"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-1">
            <div class="col-12">
                <div class="card p-1">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div>
                            <h5 class="mb-0">Preview Result</h5>
                            @if (!empty($preview['file_name']))
                                <small class="text-muted">File: {{ $preview['file_name'] }}</small>
                            @endif
                        </div>
                        @if (!empty($preview['generated_at']))
                            <small class="text-muted">{{ $preview['generated_at'] }}</small>
                        @endif
                    </div>
                    @php
                        $defaultUpdate = $preview['default_update'] ?? [];
                        $defaultUpdateStatus = $defaultUpdate['status'] ?? 'skipped';
                        $defaultUpdateErrors = $defaultUpdate['errors'] ?? [];
                    @endphp
                    @if (in_array($defaultUpdateStatus, ['pending', 'done'], true))
                        <div id="preview-default-update-alert"
                            class="alert alert-dismissible fade show py-1 px-2 small mb-1 {{ $defaultUpdateStatus === 'done' ? (!empty($defaultUpdateErrors) ? 'alert-warning' : 'alert-success') : 'alert-info' }}"
                            role="alert" data-pending="{{ $defaultUpdateStatus === 'pending' ? '1' : '0' }}">
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            <div class="pe-3">
                                <div id="preview-default-update-message">
                                    @if ($defaultUpdateStatus === 'pending')
                                        <span class="spinner-border spinner-border-sm me-1" role="status"
                                            aria-hidden="true"></span>
                                        Applying default field updates...
                                    @else
                                        {{ $defaultUpdate['message'] ?? 'Default fields updated.' }}
                                    @endif
                                </div>
                                <div id="preview-default-update-errors"
                                    class="mt-1 fw-bold text-danger {{ empty($defaultUpdateErrors) ? 'd-none' : '' }}"
                                    style="font-size:1.05rem;">
                                    @foreach ($defaultUpdateErrors as $error)
                                        <div>{{ $error }}</div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                    @include('admin.sale.consumer-preview', [
                        'matched' => $preview['matched'],
                        'unmatched' => $preview['unmatched'],
                    ])
                </div>
            </div>
        </div>

        @include('admin.sale.update-record-modal')
    @else
        <div class="row mt-1">
            <div class="col-12">
                <div class="alert alert-secondary mb-0">No preview generated yet. Upload an Excel file to see results.</div>
            </div>
        </div>
    @endif
@endsection

@section('pagescript')
    @if ($preview)
        <script>
            function renderDefaultUpdateErrors(errors) {
                var $error = $('#preview-default-update-errors');

                if (!$error.length) {
                    return;
                }

                $error.empty();

                if (!errors || !errors.length) {
                    $error.addClass('d-none');
                    return;
                }

                $.each(errors, function(index, message) {
                    $error.append($('<div>').text(message));
                });

                $error.removeClass('d-none');
            }

            function runPreviewDefaultUpdates() {
                var $status = $('#preview-default-update-alert');
                var $message = $('#preview-default-update-message');

                if (!$status.length || !$message.length || String($status.data('pending')) !== '1' || $status.data('running') === 1) {
                    return;
                }

                renderDefaultUpdateErrors([]);

                $status.data('running', 1)
                    .removeClass('alert-success alert-warning alert-danger')
                    .addClass('alert-info');
                $message.html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>Applying default field updates...');

                $.ajax({
                    url: '{{ route('sales-master-consumer-preview-apply-default') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (!response.success) {
                            var failureMessage = response.message || 'Default fields could not be updated.';

                            $status.data('pending', 0)
                                .removeClass('alert-info alert-success alert-warning')
                                .addClass('alert-danger');
                            $message.html(failureMessage);
                            renderDefaultUpdateErrors([failureMessage]);

                            return;
                        }

                        var hasErrors = response.errors && response.errors.length;

                        renderDefaultUpdateErrors(response.errors || []);

                        $status.data('pending', 0)
                            .removeClass('alert-info alert-danger alert-warning alert-success')
                            .addClass(hasErrors ? 'alert-warning' : 'alert-success');
                        $message.html(response.message);

                        if (response.updated_count > 0 && !hasErrors && typeof Swal !== 'undefined') {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: response.message,
                                showConfirmButton: false,
                                timer: 1800
                            });
                        }
                    },
                    error: function(xhr) {
                        var message = 'Default fields could not be updated.';

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }

                        renderDefaultUpdateErrors([message]);

                        $status.data('pending', 1)
                            .removeClass('alert-info alert-success alert-warning')
                            .addClass('alert-danger');
                        $message.html(message + ' <button type="button" class="btn btn-sm btn-outline-danger ms-2" id="retry-default-update">Retry</button>');
                    },
                    complete: function() {
                        $status.data('running', 0);
                    }
                });
            }

            $(function() {
                runPreviewDefaultUpdates();
            });

            $(document).on('click', '#retry-default-update', function() {
                runPreviewDefaultUpdates();
            });

            function updatePreviewSelectAllState(group) {
                var $checkboxes = $('#preview-' + group + '-section tbody tr')
                    .not('[data-preview-empty]')
                    .not('.d-none')
                    .find('.preview-row-checkbox');
                var $checked = $checkboxes.filter(':checked');
                var $selectAll = $('#preview-' + group + '-section .preview-select-all');

                $selectAll.prop('checked', $checkboxes.length > 0 && $checked.length === $checkboxes.length);
                $selectAll.prop('indeterminate', $checked.length > 0 && $checked.length < $checkboxes.length);

                var hasMatchedSelection = $('.preview-row-checkbox[data-preview-group="matched"]:checked').length > 0;
                var activeTab = $('#consumer-preview-tabs .nav-link.active').data('preview-filter');

                $('#preview-update-record-wrapper').toggleClass('d-none', !hasMatchedSelection || activeTab !== 'matched');
            }

            function applyConsumerPreviewFilter() {
                var name = ($('#preview_filter_name').val() || '').trim().toLowerCase();
                var number = ($('#preview_filter_number').val() || '').trim().toLowerCase();
                var matchedCount = 0;
                var unmatchedCount = 0;

                $('#preview-matched-section tbody tr').each(function() {
                    var row = $(this);

                    if (row.attr('data-preview-empty')) {
                        row.toggleClass('d-none', name !== '' || number !== '');
                        return;
                    }

                    var rowName = String(row.attr('data-preview-name') || '').toLowerCase();
                    var rowNumber = String(row.attr('data-preview-number') || '').toLowerCase();
                    var visible = (name === '' || rowName.indexOf(name) !== -1) &&
                        (number === '' || rowNumber.indexOf(number) !== -1);

                    row.toggleClass('d-none', !visible);

                    if (!visible) {
                        row.find('.preview-row-checkbox').prop('checked', false);
                    }

                    if (visible) {
                        matchedCount++;
                    }
                });

                $('#preview-unmatched-section tbody tr').each(function() {
                    var row = $(this);

                    if (row.attr('data-preview-empty')) {
                        row.toggleClass('d-none', name !== '' || number !== '');
                        return;
                    }

                    var rowName = String(row.attr('data-preview-name') || '').toLowerCase();
                    var rowNumber = String(row.attr('data-preview-number') || '').toLowerCase();
                    var visible = (name === '' || rowName.indexOf(name) !== -1) &&
                        (number === '' || rowNumber.indexOf(number) !== -1);

                    row.toggleClass('d-none', !visible);

                    if (!visible) {
                        row.find('.preview-row-checkbox').prop('checked', false);
                    }

                    if (visible) {
                        unmatchedCount++;
                    }
                });

                $('#preview-matched-tab').html('Recommended Update: ' + matchedCount);
                $('#preview-unmatched-tab').html('Not matched: ' + unmatchedCount);

                updatePreviewSelectAllState('matched');
                updatePreviewSelectAllState('unmatched');
            }

            $(document).on('input', '#preview_filter_name, #preview_filter_number', applyConsumerPreviewFilter);
            $(document).on('click', '#preview-filter-btn', applyConsumerPreviewFilter);
            $(document).on('click', '#preview-reset-btn', function() {
                $('#preview_filter_name, #preview_filter_number').val('');
                applyConsumerPreviewFilter();
            });

            $(document).on('change', '.preview-select-all', function() {
                var group = $(this).data('preview-select-all');
                var checked = $(this).prop('checked');

                $('#preview-' + group + '-section tbody tr')
                    .not('[data-preview-empty]')
                    .not('.d-none')
                    .find('.preview-row-checkbox')
                    .prop('checked', checked);

                updatePreviewSelectAllState(group);
            });

            $(document).on('change', '.preview-row-checkbox', function() {
                updatePreviewSelectAllState($(this).data('preview-group'));
            });

            $(document).on('click', '.preview-ignore-row', function() {
                var $row = $(this).closest('tr');
                var consumerNumber = $row.attr('data-preview-number') || '';

                if (consumerNumber === '') {
                    return;
                }

                $.ajax({
                    url: '{{ route('sales-master-consumer-preview-ignore') }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        consumer_numbers: [consumerNumber]
                    },
                    dataType: 'json',
                    success: function(response) {
                        $row.remove();

                        if ($('#preview-unmatched-section tbody tr').not('[data-preview-empty]').length === 0) {
                            $('#preview-unmatched-section tbody').html(
                                '<tr data-preview-empty="1"><td colspan="4" class="text-center">No unmatched records.</td></tr>'
                            );
                        }

                        applyConsumerPreviewFilter();

                        Swal.fire({
                            icon: 'success',
                            title: 'Ignored',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {
                        var message = 'Unable to ignore the selected record.';

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: message
                        });
                    }
                });
            });

            $(document).on('click', '#consumer-preview-tabs [data-preview-filter]', function() {
                var filter = $(this).data('preview-filter');

                $('#consumer-preview-tabs .nav-link').removeClass('active');
                $(this).addClass('active');

                $('#preview-matched-section, #preview-unmatched-section').addClass('d-none');

                if (filter === 'matched') {
                    $('#preview-matched-section').removeClass('d-none');
                }

                if (filter === 'unmatched') {
                    $('#preview-unmatched-section').removeClass('d-none');
                }

                updatePreviewSelectAllState('matched');
                updatePreviewSelectAllState('unmatched');
            });
        </script>

        <script>
            $(function() {
                var originalSubmitHtml = $('#update-record-submit').html();

                function escapeHtml(value) {
                    return $('<div>').text(value == null ? '' : value).html();
                }

                function renderList(items, extraClass, inlineStyle) {
                    var html = '<ul class="mb-0 ps-3 ' + (extraClass || 'small') + '"' + (inlineStyle || '') + '>';

                    $.each(items || [], function(_, item) {
                        if (item && typeof item === 'object') {
                            html += '<li>Row ' + escapeHtml(item.row) + ': ' + escapeHtml(item.consumer_number) + '</li>';
                        } else {
                            html += '<li>' + escapeHtml(item) + '</li>';
                        }
                    });

                    return html + '</ul>';
                }

                function showError(message) {
                    $('#update-record-error').html(escapeHtml(message)).removeClass('d-none');
                }

                function showSwalError(message) {
                    Swal.fire({
                        title: "{{ __('message.Error') }}",
                        text: message,
                        icon: 'error',
                        customClass: {
                            confirmButton: 'btn btn-primary',
                            htmlContainer: 'text-danger fs-6'
                        },
                        buttonsStyling: false
                    });
                }

                function selectedSalesMasterIds() {
                    return $('.preview-row-checkbox[data-preview-group="matched"]:checked')
                        .map(function() {
                            return $(this).val();
                        })
                        .get()
                        .filter(function(value) {
                            return value !== undefined && value !== '';
                        });
                }

                $(document).on('submit', '#update-record-form', function(e) {
                    e.preventDefault();

                    var $form = $(this);
                    var $submit = $('#update-record-submit');
                    var selectedIds = selectedSalesMasterIds();

                    if (!selectedIds.length) {
                        showError('Please select at least one matched record.');
                        showSwalError('Please select at least one matched record.');
                        return;
                    }

                    if (!$('input[name="recommended_fields[]"]:checked').length) {
                        showError('Please select at least one recommend field.');
                        showSwalError('Please select at least one recommend field.');
                        return;
                    }

                    $('#update-record-error').addClass('d-none').html('');
                    $('#update-record-summary').addClass('d-none').html('');
                    $submit.prop('disabled', true)
                        .html('<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Updating...');

                    var data = $form.serialize() + '&' + $.param({
                        sales_master_ids: JSON.stringify(selectedIds)
                    });

                    $.ajax({
                        url: $form.attr('action'),
                        type: 'POST',
                        data: data,
                        dataType: 'json',
                        cache: false,
                        success: function(response) {
                            var html = '';

                            if (response.success) {
                                html += '<div class="alert alert-success py-1 mb-2">Updated: <strong>' +
                                    escapeHtml(response.updated_count) + '</strong> | Skipped: <strong>' +
                                    escapeHtml(response.skipped_count) + '</strong></div>';

                                if ((response.not_found || []).length) {
                                    html += '<div class="small fw-bold">Not found:</div>' + renderList(response.not_found);
                                }

                                if ((response.errors || []).length) {
                                    html += '<div class="fw-bold mt-2 text-danger" style="font-size:1.05rem;">Errors:</div>' +
                                        renderList(response.errors, 'text-danger', ' style="font-size:1.05rem;"');
                                }

                                $('#update-record-summary').html(html).removeClass('d-none');

                                Swal.fire({
                                    title: "{{ __('message.Success') }}",
                                    text: response.updated_count + ' record' + (response.updated_count === 1 ? '' : 's') +
                                        ' updated successfully.',
                                    icon: 'success',
                                    customClass: {
                                        confirmButton: 'btn btn-primary'
                                    },
                                    buttonsStyling: false
                                });
                            } else {
                                var message = (response.errors || ['Unable to complete the update.']).join(' ');
                                showError(message);
                                showSwalError(message);
                            }
                        },
                        error: function(xhr) {
                            var errors = xhr.responseJSON && xhr.responseJSON.errors
                                ? xhr.responseJSON.errors
                                : ['Unable to complete the update (HTTP ' + xhr.status + '). Please try again.'];
                            var message = errors.join(' ');
                            showError(message);
                            showSwalError(message);
                        },
                        complete: function() {
                            $submit.prop('disabled', false).html(originalSubmitHtml);
                        }
                    });
                });

                $('#updateRecordModal').on('hidden.bs.modal', function() {
                    $('#update-record-form')[0].reset();
                    $('input[name="recommended_fields[]"]').prop('checked', false);
                    $('#update-record-error').addClass('d-none').html('');
                    $('#update-record-summary').addClass('d-none').html('');
                    $('#update-record-submit').prop('disabled', false).html(originalSubmitHtml);
                });
            });
        </script>
    @endif

    @if (session()->has('message'))
        <script>
            toastr.success(@json(session('message')), @json(__('message.Success')));
        </script>
    @endif
@endsection
