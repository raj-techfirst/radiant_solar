@extends('layouts.app')
@section('title', 'Goods Return')
@section('content')

<div class="row">
    <div class="col-12 mb-50">
        <h4 class="content-header-title float-start border-0 ms-1">Goods Return {{(isset($data) && isset($data->id)) ? 'Edit' : 'Add' }}</h4>
        @can('delivery-challan-return-list')
        <a href="{{ route('delivery-challan-return.index') }}" class="btn btn-sm btn-primary float-end"><i class="fa fa-angle-double-left me-25"></i> List</a>
        @endcan
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form id="form" class="form p-0 form-repeater" method="post" action="javascript:void(0);">
                    @csrf

                    <div class="row">
                        <input type="hidden" name="id" id="id" value="{{(isset($data) && isset($data->id)) ? $data->id : '' }}">

                        <div class="col-12 col-sm-12 col-md-4 col-lg-4 form-group custom-input-group">
                            <label class="form-label" for="challan_date">Date</label>
                            <input class="form-control" autocomplete="off" name="challan_date" id="challan_date" value="{{(isset($data)) ? date('d-m-Y',strtotime($data->challan_date)) : date('d-m-Y')}}">
                        </div>

                        @if(!isset($data))
                        <div class="col-12 col-md-8 col-lg-8 mb-1 custom-input-group">
                            <label class="form-label w-100">Issue Type <span class="text-danger">*</span></label>
                            <label class="form-label btn btn-outline-primary"><input type="radio" name="issue_type" value="project" {{ (isset($data) && ($data->issue_type == 'project') ? 'checked' : 'checked')}}> Project Wise</label>
                            <label class="form-label btn  btn-outline-primary"><input type="radio" name="issue_type" value="installer" {{ (isset($data) && ($data->issue_type == 'installer') ? 'checked' : '')}}> Installer Wise</label>
                        </div>
                        @else
                        <input type="hidden" name="issue_type" value="{{ $data->issue_type }}">
                        @endif

                        <!-- Installer Wise -->
                        <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group installer-wise {{ (isset($data) && ($data->issue_type == 'project') ? 'd-none' : '')}} {{ (!isset($data)) ? 'd-none' : '' }}">
                            <label class="form-label" for="installer_id">Installer <span class="text-danger">*</span></label>
                            <select class="form-select select2 get-stock" name="installer_id" id="installer_id">
                                <option selected disabled>{{ __('message.-- Select --') }}</option>
                                @foreach($installer as $value)
                                <option value="{{ $value->user->id }}" {{ (isset($data) && ($data->installer_id == $value->user->id) ? 'selected' : '')}}>{{ $value->user->name.' '. $value->user->last_name   }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- / Installer Wise -->

                        <!-- Project Wise -->
                        <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group project-wise {{ (isset($data) && ($data->issue_type == 'installer') ? 'd-none' : '')}}">
                            <label class="form-label" for="project_id">Project <span class="text-danger">*</span></label>
                            <select class="form-select select2 get-stock" name="project_id" id="project_id">
                                <option selected disabled>{{ __('message.-- Select --') }}</option>
                                @foreach($project as $value)
                                <option value="{{ $value->id }}" {{ (isset($data) && ($data->sales_master_id == $value->id) ? 'selected' : '')}}>{{ $value->consumer_name }} | {{ $value->consumer_number }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- / Project Wise -->


                        <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                            <label class="form-label" for="warehouse_id">Warehouse <span class="text-danger">*</span></label>
                            <select class="form-select select2" name="warehouse_id" id="warehouse_id">
                                <option selected disabled>{{ __('message.-- Select --') }}</option>
                                @foreach($warehouse as $value)
                                <option value="{{ $value->id }}" {{ (isset($data) && ($data->warehouse_id == $value->id) ? 'selected' : '')}}>{{ $value->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-group mt-1 d-none" id="table_col">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered" id="purchaseTable">
                                    <thead>
                                        <tr>
                                            <th class="text-center" width="35px">#</th>
                                            <th class="text-center" width="150px">Type</th>
                                            <th class="text-center">Item</th>
                                            <th class="text-center" width="10%">Stock</th>
                                            <th class="text-center" width="15%">Qty.</th>
                                            <th class="text-center" width="6%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody data-repeater-list="invoice" class="sub_data">

                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td class="text-end" colspan="9">
                                                <button class="badge badge-light-success border-0 add-new m-0" type="button" data-repeater-create>
                                                    <i data-feather="plus" class="me-0"></i>
                                                    <!-- <span>Add</span> -->
                                                </button>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-group custom-input-group">
                            <label class="form-label">Remark</label>
                            <textarea class="form-control" autocomplete="off" name="remark" placeholder="Type here..">{{(isset($data)) ? $data->remark : old('remark')}}</textarea>
                            <span class="invalid-feedback d-block" role="alert"></span>
                        </div>
                        <div class="col-12 col-sm-12 col-md-12 col-lg-12 py-2 text-end">
                            <button type="submit" class="btn btn-sm btn-gradient-primary save">Submit</button>
                            <button type="reset" class="btn btn-sm btn-outline-danger reset float-end mx-50">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('pagescript')
<script type="application/javascript">
    $("#challan_date").flatpickr({
        altInput: true,
        defaultDate: new Date(),
        altFormat: 'd-m-Y',
        dateFormat: 'Y-m-d',
        maxDate: new Date(),
    });
    $(document).on('change', '.get-stock', function() {
        var me = $(this);
        var id = me.val();
        var type = $("input[name='issue_type']:checked").val();
        var temp = $('.clone_row').length;
        if (id != null) {
            $.ajax({
                type: "get",
                url: "{{route('get-return-stock')}}",
                data: {
                    'id': id,
                    'type': type,
                    "_token": "{{ csrf_token() }}",
                },
                dataType: 'json',
                cache: false,
                success: function(response) {
                    if (response.status_code == 403) {
                        $('#table_col').addClass('d-none');
                        $(".sub_data").html('');
                        toastr.warning(response.message, "Warning");
                    } else {
                        me.attr('disabled', true);
                        $('#table_col').removeClass('d-none');
                        $(".sub_data").html('');
                        $(".sub_data").html(response.html);
                        $('.product_id').select2();
                        if (temp == 0) {
                            clone();
                        }

                        $('.sub_data tr').each(function() {
                            if ($(this).find('.type').val() == "Item") {
                                $(this).find('.product_id').trigger('change');
                            } else {
                                $(this).find('.item_group_id').trigger('change');
                            }
                        });

                        if (feather) {
                            feather.replace({
                                width: 14,
                                height: 14
                            });
                        }
                    }
                }
            });
        } else {
            $('#table_col').addClass('d-none');
        }
    });

    function clone() {
        $('.form-repeater, .repeater-default').repeater({
            show: function() {
                $(this).slideDown();
                var obj = $(this);
                var obj = $(this);
                var sr = $('.sr_no').length;
                obj.find('.sr_no').text(sr);

                obj.find('.product_id').removeAttr('disabled');
                obj.find('.variant-delete').removeAttr('data-id');
                obj.find('.product_id').next('.select2-container').remove();

                obj.find('.type').val('Item').trigger('change');

                $('.item_group_id').select2({
                    placeholder: "--Select--",
                    allowClear: true,
                    width: "100%",
                    selectOnClose: true,
                });

                $('.product_id').select2({
                    placeholder: "--Select--",
                    allowClear: true,
                    width: "100%",
                    selectOnClose: true,
                });

                // obj.find('.product_id').focus();

                obj.find('.custom-select2').on('change', function() {
                    var element = $(this).attr('name');
                    $('#form').validate().showErrors({
                        [element]: ''
                    });
                });

                if (feather) {
                    feather.replace({
                        width: 14,
                        height: 14
                    });
                }
            },
            hide: function(deleteElement) {
                var obj = $(this);
                var len = obj.parent().parent().find('.remove-item').length;
                if (len != 0) {
                    if (len > 1) {
                        $(this).slideUp(deleteElement);
                        setTimeout(function() {
                            updateSerialNumbers();
                        }, 500);
                    } else {
                        Swal.fire({
                            text: "Can't delete first item",
                            icon: 'warning',
                            confirmButtonText: 'OK',
                        });
                    }
                }
            }
        });
    }

    function updateSerialNumbers() {
        $('.sr_no').each(function(index) {
            $(this).text(index + 1);
        });
    }

    $(document).on('.remove-item').on('keydown', function() {
        var me = $(this);
        me.closest('tr').next('tr').find('.product_id').focus();
    });

    $(document).on('click', '.remove-item', function() {
        if (($('.remove-item').length) > 1) {
            $(this).parent().parent().remove();
            setTimeout(function() {
                updateSerialNumbers();
                calculationAmount();
            }, 200);
        } else {
            Swal.fire({
                text: "Can`t delete first item",
                icon: 'warning',
                confirmButtonText: 'OK',
            });
        }
    });

    $("#form").validate({
        rules: {
            warehouse_id: {
                required: true,
            },
            project_id: {
                required: true,
            },
        },
        messages: {
            warehouse_id: {
                required: "Select warehouse"
            },
            project_id: {
                required: "Select project"
            },
        },
        errorElement: "p",
        errorClass: "text-danger mb-0",
        highlight: function(element) {
            $(element).addClass('has-error');
        },
        unhighlight: function(element) {
            $(element).removeClass('has-error');
        },
        errorPlacement: function(error, element) {
            $(element).closest('.custom-input-group').append(error);
        }
    });

    $(document).on('change', '.product_id', function() {
        var unit = $(this).find('option:selected').data('unit');
        var gst = $(this).find('option:selected').data('gst');
        var stock = $(this).find('option:selected').data('stock');
        $(this).closest('tr').find('.unit_type').html(unit);
        $(this).closest('tr').find('.gst').val(gst);
        $(this).closest('tr').find('.stock-find').val(stock);
        $(this).closest('tr').find('.stock').val(stock);
        var row = $(this).closest('tr');
        calculateTotal(row);
    });

    $(document).on('click', '.reset', function() {
        location.reload();
    });

    $(document).on('click', '.save', function() {

        if ($("#form").valid()) {
            $('select').attr('disabled', false);
            var formData = new FormData($("#form")[0]);

            $.ajax({
                type: "POST",
                url: "{{ route('delivery-challan-return.store') }}",
                data: formData,
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $(".save").html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Wait`);
                    $(".save").attr('disabled', true);
                },
                success: function(response) {
                    $('select').attr('disabled', true);
                    $(".save").html("Submit");
                    $(".save").attr('disabled', false);
                    if (response.status_code == 500) {
                        toastr.error(response.message, "Error");
                    } else if (response.status_code == 403) {
                        toastr.warning(response.message, "Warning");
                    } else if (response.status_code == 201) {
                        $.each(response.errors, function(key, value) {
                            $('#error_' + key).html('<p class="text-danger mb-0">' + value + '</p>');
                        });
                        toastr.warning(response.message, "Warning");
                    } else {
                        $('#form')[0].reset();
                        toastr.success(response.message, "Success");
                        setTimeout(function() {
                            location.href = response.data;
                        }, 500);
                    }
                }
            });
        } else {
            return false;
        }
    });

    $(document).on('input', '.quantity', function() {
        var row = $(this).closest('tr');
        calculateTotal(row);
        calculationAmount();
        // updateSubtotals();
    });

    $(document).on('input', '.rate', function() {
        calculationAmount();
        // updateSubtotals();
    });

    function calculateTotal(row) {
        var quantity = parseFloat(row.find('.quantity').val()) || 0;
        var stock = parseFloat(row.find('.stock-find').val()) || 0;
        if (quantity > stock) {
            toastr.warning("Quantity cannot be greater than stock!", "Warning");
            row.find('.quantity').val(stock);
            quantity = stock;
        }

        var finalStock = stock - quantity;
        row.find('.stock').val(finalStock);
        updateSubtotals();
        calculationAmount();

    }

    function calculationAmount() {
        $(".amount").each(function() {
            let quantity = $(this).closest('.clone_row').find('.quantity').val();
            let price = $(this).closest('.clone_row').find('.rate').val();

            var fianlTotal = 0.00;
            let getBaseAmount = (quantity * price);
            fianlTotal = (getBaseAmount);
            $(this).val(parseFloat(fianlTotal).toFixed(2));

            let gst = $(this).closest('.clone_row').find('.gst').val();
            let gstFind = ((fianlTotal * gst) / 100);
            $(this).closest('.clone_row').find('.gst-amt').val(gstFind.toFixed(2));
        });
        updateSubtotals();
    }

    function updateSubtotals() {
        var totalAmount = gtotalAmount = 0;
        $('.amount').each(function() {
            var total = $(this);
            var rowTotal = parseFloat(total.val()) || 0;
            totalAmount += rowTotal;
        });
        $('.sub_amount').val(totalAmount.toFixed(2));

        $('.gst-amt').each(function() {
            var gtotal = $(this);
            var growTotal = parseFloat(gtotal.val()) || 0;
            gtotalAmount += growTotal;
        });
        $('.gst_amount').val(gtotalAmount.toFixed(2));
        $('.cgst,.sgst').val((gtotalAmount / 2).toFixed(2));
        $('.total').val((totalAmount + gtotalAmount).toFixed(2));
    }

    $(document).on('change', '.item_group_id', function() {
        var unit = $(this).find('option:selected').data('unit');
        var gst = $(this).find('option:selected').data('gst');
        var stock = $(this).find('option:selected').data('stock');
        $(this).closest('tr').find('.unit_type').html(unit);
        $(this).closest('tr').find('.gst').val(gst);
        $(this).closest('tr').find('.stock-find').val(stock);
        $(this).closest('tr').find('.stock').val(stock);
        var row = $(this).closest('tr');
        calculateTotal(row);
    });

    $(document).on('change', '.type', function() {
        if ($(this).val() == 'Item') {
            $(this).parent().parent().parent().find('.type-item-group').addClass('d-none');
            $(this).parent().parent().parent().find('.type-item').removeClass('d-none');
        } else {
            $(this).parent().parent().parent().find('.type-item-group').removeClass('d-none');
            $(this).parent().parent().parent().find('.type-item').addClass('d-none');
        }
    });

    $("input[name='issue_type']").change(function() {
        var issue_type = $("input[name='issue_type']:checked").val();
        $('#table_col').addClass('d-none');
        $(".sub_data").html('');
        if (issue_type == "project") {
            $(".text-danger.mb-0.custom-error").remove();
            $(".installer-wise").addClass('d-none');
            $(".project-wise").removeClass('d-none');
        } else {
            $(".installer-wise").removeClass('d-none');
            $(".project-wise").addClass('d-none');
        }
    });
</script>
@endsection