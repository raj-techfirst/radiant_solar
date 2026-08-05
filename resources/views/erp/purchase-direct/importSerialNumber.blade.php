@extends('layouts.app')
@section('title', 'Upload Serial Number')
@section('content')
<style>
    /* Custom z-index for the first and second modals */
    .modal-backdrop {
        z-index: 1040;
        /* Default backdrop z-index */
    }

    .modal.show {
        z-index: 1050;
        /* Default z-index for modals */
    }

    /* Custom backdrop for the second modal */
    .modal-second-backdrop {
        z-index: 1060;
        /* Higher z-index for second modal's backdrop */
    }

    .modal-second.show {
        z-index: 1070;
        /* Higher z-index for second modal */
    }
</style>
<div class="row">
    <div class="col-12 mb-50">
        <h4 class="content-header-title float-start border-0 ms-1">Upload Serial Number</h4>
        @can('purchase-direct-list')
        <a href="{{ route('purchase-direct.index') }}" class="btn btn-sm btn-primary float-end"><i class="fa fa-angle-double-left me-25"></i> List</a>
        @endcan
        <a href="{{ asset('img/serial_number_import.xlsx') }}" class="btn btn-sm btn-info float-end me-2">
            <i data-feather='download'></i> Download Excel Sample
        </a>
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-body p-1">
                <div class="row">
                    <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                        <h5><b>Date : </b> {{date('d-m-Y',strtotime($data->date))}} </h5>
                        <h5><b> Invoice No.</b> : {{ $data->supplier_number  }}</h5>
                    </div>
                    <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                        <h5><b>Supplier : </b> {{ $data->supplier->name }} </h5>
                        <h5><b>Warehouse </b> {{ $data->warehouse->name }} </h5>
                    </div>
                    <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-group mt-1">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered" id="purchaseTable">
                                <thead>
                                    <tr>
                                        <th class="text-center">#</th>
                                        <th width="35%" class="text-center">Item</th>
                                        <th class="text-center">Quantity</th>
                                        <th class="text-center">Excel Upload</th>
                                        <th style="width:145px;" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                @if((isset($data) && isset($data->id)))
                                <tbody>
                                    @php $i = 0; @endphp
                                    @foreach($data->purchase_direct_meta as $key => $value)
                                    @if($value->type == 'ItemGroup')
                                    @php $i++; @endphp
                                    <tr data-repeater-item class="clone_row @if($value->serial_numbers_count->count() == $value->quantity) bg-light-success  @elseif($value->serial_numbers_count->count() != 0) bg-light-warning @endif">
                                        <td class="text-center">
                                            <b class="sr_no">{{ $i }}</b>
                                        </td>
                                        <td class="custom-input-group type-item-group">
                                            {{ $value->itemGroup->item_code }} {{ getItemGropName($value,1) }}
                                        </td>
                                        <td class="custom-input-group">
                                            <div class="input-group">
                                                {{ $value->quantity }}
                                                {{ $value->unit->unit_name }}
                                            </div>
                                        </td>
                                        <td class="custom-input-group">
                                            <input type="file" class="form-control bg-transparent" name="uploadExcel" accept=".xls,.xlsx" onchange="validateFile(this)">
                                        </td>
                                        <td class="">
                                            <button type="button" class="btn btn-sm btn-secondary upload" data-id="{{$value->id}}" disabled>
                                                <i class='fas fa-save'></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-info download" data-id="{{$value->id}}">
                                                <i data-feather='download'></i>
                                            </button>
                                            @if($value->serial_numbers_count->count() != 0)

                                            <button type="button" class="btn btn-sm btn-success view" data-id="{{$value->id}}">
                                                <i data-feather='eye'></i>
                                            </button>
                                            @endif
                                        </td>
                                    </tr>
                                    @endif
                                    @endforeach
                                </tbody>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="inlineModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="rowDetailsModalLabel">Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="appnedData">
            </div>
        </div>
    </div>
</div>
<div class="modal fade modal-second" id="secondModal" tabindex="-1" aria-labelledby="secondModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12 col-md-12 mb-1 custom-input-group">
                        <input type="hidden" name="serial_number_id" id="serial_number_id" value="">
                        <label for="serial_number">Serial Number</label>
                        <input type="text" class="form-control" name="serial_number" id="serial_number" placeholder="Enter serial number *">
                        <span class="invalid-feedback d-block" id="error_serial_number" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 mb-1 custom-input-group">
                        <label for="warranty_start">Warranty start</label>
                        <input type="date" class="form-control date" name="warranty_start" id="warranty_start" placeholder="Enter warranty start">
                        <span class="invalid-feedback d-block" id="error_warranty_start" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 mb-1 custom-input-group">
                        <label for="warranty_end">Warranty end</label>
                        <input type="date" class="form-control date" name="warranty_end" id="warranty_end" placeholder="Enter warranty start">
                        <span class="invalid-feedback d-block" id="error_warranty_end" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 mb-1 custom-input-group">
                        <label for="guarantee_start">Guarantee start</label>
                        <input type="date" class="form-control date" name="guarantee_start" id="guarantee_start" placeholder="Enter Guarantee start">
                        <span class="invalid-feedback d-block" id="error_guarantee_start" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 mb-1 custom-input-group">
                        <label for="guarantee_end">Guarantee end</label>
                        <input type="date" class="form-control date" name="guarantee_end" id="guarantee_end" placeholder="Enter Guarantee end">
                        <span class="invalid-feedback d-block" id="error_guarantee_end" role="alert"></span>
                    </div>
                    <div class="col-md-12 col-12">
                        <button type="button" class="btn btn-sm btn-primary float-end update-serial-number waves-effect waves-float waves-light">Update</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('pagescript')
<script type="application/javascript">
    // $(".date").flatpickr({
    //     altInput: true,
    //     altFormat: 'd-m-Y',
    //     dateFormat: 'Y-m-d',
    // });
    var customSerialno = '';

    function validateFile(input) {
        const file = input.files[0];
        const $uploadButton = $(input).closest('tr').find('.upload');
        const allowedExtensions = /(\.xls|\.xlsx)$/i;
        const maxSize = 1 * 1024 * 1024; // 1 MB

        const resetButtonState = (isValid) => {
            if (isValid) {
                $uploadButton.removeClass('btn-secondary').addClass('btn-success').removeAttr('disabled');
            } else {
                $uploadButton.addClass('btn-secondary').removeClass('btn-success').attr('disabled', true);
            }
        };

        if (!allowedExtensions.exec(file.name)) {
            toastr.warning('Please upload an Excel file.', "Opps!");
            input.value = "";
            resetButtonState(false);
            return false;
        }

        if (file.size > maxSize) {
            toastr.warning('File size must be less than 1 MB.', "Opps!");
            input.value = "";
            resetButtonState(false);
            return false;
        }

        resetButtonState(true);
        return true;
    }

    $(document).on('click', '.upload', function() {
        var uploadButton = $(this);
        var fileInput = $(this).closest('tr').find('input[type="file"]');
        var file = fileInput[0].files[0];
        var dataId = $(this).data('id');

        if (!file) {
            toastr.error('Please select a file to upload.', "Opps!");
            return;
        }

        if (!validateFile(fileInput[0])) {
            return;
        }

        var formData = new FormData();
        formData.append('uploadExcel', file);
        formData.append('id', dataId);
        formData.append('_token', "{{ csrf_token() }}");

        uploadButton.prop('disabled', true).addClass('btn-secondary').removeClass('btn-success');

        var icon = uploadButton.find('i');
        icon.removeClass('fa-save');
        icon.addClass('fa-spinner fa-spin');

        $.ajax({
            url: "{{ route('import-item-group-serial-number-store') }}",
            method: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                var icon = uploadButton.find('i');
                icon.addClass('fa-save');
                icon.removeClass('fa-spinner fa-spin');
                uploadButton.prop('disabled', false).removeClass('btn-secondary').addClass('btn-success');
                if (response.status_code == '200') {
                    toastr.success(response.message, "Success");
                    location.reload();
                } else {
                    if (response.html) {
                        $('#appnedData').html('');
                        $('#appnedData').html(response.html)
                        $("#inlineModal").modal('show');
                    } else {
                        toastr.warning(response.message, "Opps!");
                    }
                }
            },
            error: function(xhr, status, error) {
                toastr.error('An error occurred while uploading the file.', "Opps!");
                uploadButton.prop('disabled', false).removeClass('btn-secondary').addClass('btn-danger');
            }
        });
    });

    $(document).on('click', '.download', function() {
        var dataId = $(this).data('id');
        $.ajax({
            url: "{{route('download-serial-number')}}",
            type: 'POST',
            datatype: 'json',
            data: {
                "id": dataId,
                "_token": "{{ csrf_token() }}",
            },
            cache: false,
            xhr: function() {
                var xhr = new XMLHttpRequest();
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 2) {
                        if (xhr.status == 200) {
                            xhr.responseType = "blob";
                        } else {
                            xhr.responseType = "text";
                        }
                    }
                };
                return xhr;
            },
            success: function(data) {
                if (data.status_code && data.status_code == 201) {
                    toastr.warning(response.message, "Opps!");
                } else {
                    var blob = new Blob([data], {
                        type: "application/octetstream"
                    });
                    var fileName = 'serial-number.xlsx';
                    var isIE = false || !!document.documentMode;
                    if (isIE) {
                        window.navigator.msSaveBlob(blob, fileName);
                    } else {
                        var url = window.URL || window.webkitURL;
                        var link = url.createObjectURL(blob);
                        var a = $("<a />");
                        a.attr("download", fileName);
                        a.attr("href", link);
                        $("body").append(a);
                        a[0].click();
                        $("body").remove(a);
                    }
                }
            }
        });

    });
    $(document).on('click', '.view', function() {
        var dataId = $(this).data('id');
        $.ajax({
            url: "{{route('view-serial-number')}}",
            type: 'POST',
            datatype: 'json',
            data: {
                "id": dataId,
                "_token": "{{ csrf_token() }}",
            },
            cache: false,
            success: function(response) {
                if (response.html) {
                    $('#appnedData').html('');
                    $('#appnedData').html(response.html);
                    $("#inlineModal").modal('show');
                } else {
                    toastr.warning(response.message, "Opps!");
                }
            }
        });
    });

    $(document).on('click', '.edit-serial-number', function() {
        $("#error_serial_number").html('');
        var dataId = $(this).attr('data-id');
        var serialno = $(this).attr('data-serialno');
        var warranty_start = $(this).attr('data-warranty-start');
        var warranty_end = $(this).attr('data-warrantyend');
        var guarantee_start = $(this).attr('data-guarantee-start');
        var guarantee_end = $(this).attr('data-guarantee-end');

        $("#serial_number_id").val(dataId);
        $("#serial_number").val(serialno);
        $("#warranty_start").val(warranty_start);
        $("#warranty_end").val(warranty_end);
        $("#guarantee_start").val(guarantee_start);
        $("#guarantee_end").val(guarantee_end);
        $("#secondModal").modal('show');

        $(".date").flatpickr({
            altInput: true,
            altFormat: 'd-m-Y',
            dateFormat: 'Y-m-d',
        });

    });

    $('#secondModal').on('shown.bs.modal', function() {

        var backdrop = $('<div class="modal-backdrop fade modal-second-backdrop"></div>');
        $('body').append(backdrop);
        backdrop.addClass('show');
    });

    $('#secondModal').on('hidden.bs.modal', function() {
        $('.modal-second-backdrop').remove();
    });

    $(document).on('click', '.update-serial-number', function() {
        var dataId = $("#serial_number_id").val();
        var serialno = $("#serial_number").val();
        var warranty_start = $("#warranty_start").val();
        var warranty_end = $("#warranty_end").val();
        var guarantee_start = $("#guarantee_start").val();
        var guarantee_end = $("#guarantee_end").val();
        $.ajax({
            url: "{{route('update-serial-number')}}",
            type: 'POST',
            datatype: 'json',
            data: {
                "id": dataId,
                "serial_number": serialno,
                "warranty_start_date": warranty_start,
                "warranty_end_date": warranty_end,
                "guarantee_start_date": guarantee_start,
                "guarantee_end_date": guarantee_end,
                "_token": "{{ csrf_token() }}",
            },
            cache: false,
            success: function(response) {
                if (response.status_code == 201) {
                    if (response.errors && response.errors.serial_number) {
                        toastr.error(response.errors.serial_number[0], "Opps!");
                        $("#error_serial_number").html(response.errors.serial_number[0]);
                    } else {
                        toastr.error("Something went wrong. Please try again.", "Opps!");
                    }
                } else if (response.status_code == 200) {
                    $(".data_serial_number_" + dataId).attr('data-serialno', serialno)
                    $(".view_serial_number_" + dataId).html(serialno);
                    $(".view_warranty_start_date_" + dataId).html(response.data.warranty_start_date);
                    $(".view_warranty_end_date_" + dataId).html(response.data.warranty_end_date);
                    $(".view_guarantee_start_date_" + dataId).html(response.data.guarantee_start_date);
                    $(".view_guarantee_end_date_" + dataId).html(response.data.guarantee_end_date);
                    toastr.success(response.message, "Success");
                    $("#secondModal").modal('hide');
                } else {
                    toastr.warning(response.message, "Opps!");
                }
            }
        });
    });
</script>
@endsection