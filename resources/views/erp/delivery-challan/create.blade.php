@extends('layouts.app')
@section('title', 'Goods Issue')
@section('content')

<div class="row">
    <div class="col-12 mb-50">
        <h4 class="content-header-title float-start border-0 ms-1">Goods Issue {{(isset($data) && isset($data->id)) ? 'Edit' : 'Add' }}</h4>
        @can('delivery-challan-list')
        <a href="{{ route('delivery-challan.index') }}" class="btn btn-sm btn-primary float-end"><i class="fa fa-angle-double-left me-25"></i> List</a>
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
                        <div class="col-12 col-md-12 col-lg-8 mb-1 custom-input-group">
                            <label class="form-label w-100">Issue Type <span class="text-danger">*</span></label>
                            <label class="form-label btn btn-outline-primary"><input type="radio" name="issue_type" value="project" {{ (isset($data) && ($data->issue_type == 'project') ? 'checked' : 'checked')}}> Project Wise</label>
                            <label class="form-label btn btn-outline-primary"><input type="radio" name="issue_type" value="installer" {{ (isset($data) && ($data->issue_type == 'installer') ? 'checked' : '')}}> Installer Wise</label>
                            <label class="form-label btn btn-outline-primary"><input type="radio" name="issue_type" value="warehouse" {{ (isset($data) && ($data->issue_type == 'warehouse') ? 'checked' : '')}}> Warehouse Wise</label>
                            <label class="form-label btn btn-outline-primary"><input type="radio" name="issue_type" value="trading" {{ (isset($data) && ($data->issue_type == 'trading') ? 'checked' : '')}}> B2B </label>
                        </div>
                        @else
                        <input type="hidden" name="issue_type" value="{{ $data->issue_type }}">
                        @endif

                        <!-- BOM -->
                        <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group d-none">
                            <label class="form-label" for="bom_id">BOM</label>
                            <select class="form-select select2" name="bom_id" id="bom_id">
                                <option value="0">None</option>
                                @foreach($boms as $bomValue)
                                <option value="{{ $bomValue->id }}">{{ $bomValue->bom_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- / BOM -->

                        <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                            <label class="form-label" for="warehouse_id">Warehouse From <span class="text-danger">*</span></label>
                            <select class="form-select select2" name="warehouse_id" id="warehouse_id">
                                <option selected disabled>{{ __('message.-- Select --') }}</option>
                                @foreach($warehouse as $value)
                                <option value="{{ $value->id }}" {{ (isset($data) && ($data->warehouse_id == $value->id) ? 'selected' : '')}}>{{ $value->name }}</option>
                                @endforeach

                            </select>
                        </div>

                        <!-- Installer Wise -->
                        <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group installer-wise d-none ">
                            <label class="form-label" for="installer_id">Installer <span class="text-danger">*</span></label>
                            <select class="form-select select2" name="installer_id" id="installer_id">
                                <option selected disabled>{{ __('message.-- Select --') }}</option>
                                @foreach($installer as $value)
                                <option value="{{ $value->user->id }}" {{ (isset($data) && ($data->installer_id == $value->user->id) ? 'selected' : '')}}>{{ $value->user->name.' '. $value->user->last_name   }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group trading-wise d-none">
                            <label class="form-label" for="quotations_id">Sales Quotation</label>
                            <select class="form-select select2" name="quotations_id" id="quotations_id">
                                <option disabled>{{ __('message.-- Select --') }}</option>
                                @foreach($quotations as $value)
                                <option value="{{ $value->id }}">{{ $value->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group installer-wise d-none">
                            <label class="form-label" for="project_ids">Projects</label>
                            <select class="form-select select2" name="project_ids[]" id="project_ids" multiple>
                                <option disabled>{{ __('message.-- Select --') }}</option>
                                @foreach($project as $value)
                                <option value="{{ $value->id }}" {{ (isset($data) && (in_array($value->id,explode(',',$data->sales_master_id))) ? 'selected' : '')}}>{{ $value->consumer_name }} | {{ $value->consumer_number }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- / Installer Wise -->

                        <!-- Project Wise -->
                        <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group project-wise d-none">
                            <label class="form-label" for="project_id">Project <span class="text-danger">*</span></label>
                            <select class="form-select select2" name="project_id" id="project_id">
                                <option selected disabled>{{ __('message.-- Select --') }}</option>
                                @foreach($project as $value)
                                <option value="{{ $value->id }}" {{ (isset($data) && ($data->sales_master_id == $value->id) ? 'selected' : '')}}>{{ $value->consumer_name }} | {{ $value->consumer_number }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- / Project Wise -->

                        <!-- Warehouse Wise -->
                        <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group warehouse-wise d-none">
                            <label class="form-label" for="warehouse_id_from">Warehouse To<span class="text-danger">*</span></label>
                            <select class="form-select select2" name="warehouse_id_from" id="warehouse_id_from">
                                <option selected disabled>{{ __('message.-- Select --') }}</option>
                                @foreach($warehouse as $value)
                                <option value="{{ $value->id }}" {{ (isset($data) && ($data->warehouse_from_id == $value->id) ? 'selected' : '')}}>{{ $value->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- / Warehouse Wise -->

                        <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-group mt-1 d-none" id="table_col">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered" id="purchaseTable">
                                    <thead>
                                        <tr>
                                            <th class="text-center" width="30px">#</th>
                                            <th class="text-center" width="150px">Type</th>
                                            <th class="text-center" width="400px">Item</th>
                                            <th class="text-center" width="100px">Required</th>
                                            <th class="text-center" width="80px">Stock</th>
                                            <th class="text-center" width="150px">Qty.</th>
                                            <th class="text-center b2b d-none" width="100px">GST (%)</th>
                                            <th class="text-center b2b d-none" width="150px">Rate</th>
                                            <th class="text-center b2b d-none" width="150px">GST Amt.</th>
                                            <th class="text-center b2b d-none" width="150px">Amount</th>
                                            <th class="text-center" width="30px"></th>
                                        </tr>
                                    </thead>
                                    <tbody data-repeater-list="invoice" class="sub_data">

                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="6" class="b2b-col"></td>

                                            <td class="text-center">
                                                <button class="badge badge-light-success border-0 add-new m-0" type="button" data-repeater-create>
                                                    <i data-feather="plus" class="me-0"></i>
                                                    <!-- <span>Add</span> -->
                                                </button>
                                            </td>
                                        </tr>
                                        <tr class="b2b d-none">
                                            <!-- <td colspan="6" class="text-end"><b>Taxable Amount<b></td> -->
                                            <td colspan="8" class="text-end"><b>Sub Total</b></td>
                                            <td>
                                                <input type="number" class="form-control gst_amount" placeholder="0" value="" disabled>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control sub_amount" placeholder="0" value="" disabled>
                                            </td>
                                            <td class="text-end">

                                            </td>
                                        </tr>
                                        <tr class="b2b d-none">
                                            <td colspan="8"></td>
                                            <td class="text-end"><b>CGST</b></td>
                                            <td>
                                                <input type="number" class="form-control cgst" placeholder="0" value="" disabled>
                                            </td>
                                            <td></td>
                                        </tr>
                                        <tr class="b2b d-none">
                                            <td colspan="8"></td>
                                            <td class="text-end"><b>SGST</b></td>
                                            <td>
                                                <input type="number" class="form-control sgst" placeholder="0" value="" disabled>
                                            </td>
                                            <td></td>
                                        </tr>
                                        <tr class="b2b d-none">
                                            <td colspan="8"></td>
                                            <td class="text-end"><b>Total</b></td>
                                            <td>
                                                <input type="number" class="form-control total" placeholder="0" value="" disabled>
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        
                        <div class="col-12"></div>

                        <div class="col-12 col-sm-12 col-md-4 col-lg-4 form-group custom-input-group">
                            <label class="form-label">Vehicle No.</label>
                            <input type="text" class="form-control" name="vehicle_no" id="vehicle_no" placeholder="Vehicle no.">
                            <span class="invalid-feedback d-block" role="alert"></span>
                        </div>
                        <div class="col-12 col-sm-12 col-md-8 col-lg-8 form-group custom-input-group">
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

    $(document).ready(function() {
        $("input[name='issue_type']").trigger('change');
        if ("{{Request::segment(2)}}" != 'create') {
            $('#warehouse_id').trigger('change');
        }
    });
    $(document).on('change', '#warehouse_id', function() {
        $('.product_id').select2();

        var temp = $('.clone_row').length;
        $('#table_col').addClass('d-none');
        var id = $('#warehouse_id').val();

        var delivery_challan_id = $('#id').val();
        var bom_id = $('#bom_id').val();

        $('#warehouse_id_from option:disabled').prop('disabled', false);
        $('#warehouse_id_from option[value="' + id + '"]').prop('disabled', true);

        if (id != null) {
            $.ajax({
                type: "get",
                url: "{{route('get-warehouse-stock')}}",
                data: {
                    'id': id,
                    'delivery_challan_id': delivery_challan_id,
                    'bom_id': bom_id,
                    'type': 'Challan',
                    "_token": "{{ csrf_token() }}",
                },
                dataType: 'json',
                cache: false,
                success: function(response) {
                    if (response.status_code == 403) {
                        $('#table_col').addClass('d-none');
                        toastr.clear();
                        toastr.warning(response.message, "Warning");
                    } else {
                        $('#warehouse_id').attr('disabled', true);
                        $('#bom_id').attr('disabled', true);
                        $('#table_col').removeClass('d-none');
                        $(".sub_data").html('');
                        $(".sub_data").html(response.html);
                        $('.product_id').select2();
                        var issue_type = $("input[name='issue_type']:checked").val();
                        if (issue_type != "trading") {
                            if (temp == 0) {
                                clone();
                            }
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
                var obj = $(this);
                var sr = $('.sr_no').length;
                obj.find('.sr_no').text(sr);
                obj.find('.product_id').removeAttr('disabled');
                obj.find('.variant-delete').removeAttr('data-id');
                obj.find('.product_id').next('.select2-container').remove();
                obj.find('.type').val('Item').trigger('change');
                obj.find('.required-item').html('-');
                obj.find('.dc-meta-id, [name*="delivery_challan_meta_id"]').val('').attr('value', '');
                obj.find('.quantity').val('').attr('value', '');
                obj.find('.rate').val('').attr('value', '');
                obj.find('.stock').val('');
                obj.find('.stock-find').val('');
                obj.find('.gst-amt').val('');
                obj.find('.amount').val('');
                obj.find('.unit_type').html('');
                obj.find('.product_id').val('').trigger('change');
                obj.find('.item_group_id').val('').trigger('change');
                $(this).slideDown();
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
            // project_id: {
            //     required: true,
            // },
        },
        messages: {
            warehouse_id: {
                required: "Select warehouse"
            },
            // project_id: {
            //     required: "Select project"
            // },
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
        var price = $(this).find('option:selected').data('price');

        $(this).closest('tr').find('.unit_type').html(unit);
        $(this).closest('tr').find('.gst').val(gst);
        $(this).closest('tr').find('.gst').val(gst);
        $(this).closest('tr').find('.stock-find').val(stock);
        $(this).closest('tr').find('.rate').val(price);
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
                url: "{{ route('delivery-challan.store') }}",
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
                        toastr.clear();
                        toastr.error(response.message, "Error");
                    } else if (response.status_code == 403) {
                        toastr.clear();
                        toastr.warning(response.message, "Warning");
                    } else if (response.status_code == 201) {
                        $.each(response.errors, function(key, value) {
                            $('#error_' + key).html('<p class="text-danger mb-0">' + value + '</p>');
                        });
                        toastr.clear();
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

    //edit time delete single order item
    $(document).on('click', '.variant-delete', function() {
        if (($('.variant-delete').length) > 1) {
            var btn = $(this);
            var id = $(this).data('id');
            var item_id = btn.closest('tr').find('.product_id').val();
            var item_group_id = btn.closest('tr').find('.item_group_id').val();
            var warehouse_id = $('#warehouse_id').val();
            var warehouse_from_id = $('#warehouse_id_from').val();
            var project_id = $('#project_id').val();
            if (id != undefined) {
                Swal.fire({
                        title: "Are you sure?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: "Yes, Delete",
                        customClass: {
                            confirmButton: 'btn btn-primary',
                            cancelButton: 'btn btn-outline-danger ms-1'
                        },
                        buttonsStyling: false
                    })
                    .then(function(result) {
                        if (result.value) {
                            $.ajax({
                                type: "POST",
                                url: "{{route('delivery-challan-remove')}}",
                                data: {
                                    'id': id,
                                    'warehouse_id': warehouse_id,
                                    'warehouse_from_id': warehouse_from_id,
                                    'project_id': project_id,
                                    'item_id': item_id,
                                    'item_group_id': item_group_id,
                                    "_token": "{{ csrf_token() }}",
                                },
                                dataType: 'json',
                                cache: false,
                                success: function(response) {
                                    if (response.status_code == 200) {
                                        btn.parent().parent().slideUp();
                                        setTimeout(function() {
                                            btn.parent().parent().remove();
                                        }, 300);
                                        toastr.clear();
                                        toastr.success(response.message, "Success");
                                    } else if (response.status_code == 201) {
                                        toastr.clear();
                                        toastr.warning(response.message, "Warning");
                                    } else if (response.status_code == 403) {
                                        toastr.clear();
                                        toastr.warning(response.message, "Warning");
                                    } else {
                                        toastr.clear();
                                        toastr.error(response.message, "Error");
                                    }
                                }
                            });
                        }
                    });
            } else {
                btn.parent().parent().remove();
                updateSerialNumbers();
            }
        } else {
            Swal.fire({
                text: "Can`t delete first item",
                icon: 'warning',
                confirmButtonText: 'OK',
            });
        }
    });

    $(document).on('input', '.quantity', function() {
        var row = $(this).closest('tr');
        calculateTotal(row);
        calculationAmount();
    });

    $(document).on('input', '.rate', function() {
        calculationAmount();
        // updateSubtotals();
    });

    function calculateTotal(row) {

        var quantity = parseFloat(row.find('.quantity').val()) || 0;
        var stock = parseFloat(row.find('.stock-find').val()) || 0;

        if (quantity > stock) {
            var noStockSpan = row.find('small.no-stock');
            if (noStockSpan.length > 0) {
                row.addClass('bg-light-danger');
            } else if (stock == 0) {
                row.addClass('bg-light-danger');
            } else {
                row.addClass('bg-light-warning');
            }
            toastr.clear();
            toastr.warning("Quantity cannot be greater than stock!", "Warning");
            row.find('.quantity').val(stock);
            quantity = stock;

        } else {
            row.removeClass('bg-light-warning');
            row.removeClass('bg-light-danger');
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
        var price = $(this).find('option:selected').data('price');

        $(this).closest('tr').find('.unit_type').html(unit);
        $(this).closest('tr').find('.gst').val(gst);
        $(this).closest('tr').find('.stock-find').val(stock);
        $(this).closest('tr').find('.stock').val(stock);
        $(this).closest('tr').find('.rate').val(price);
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

        $('select').attr('disabled', false);
        $(".sub_data").html('');
        $('#warehouse_id').val('');
        $('#project_id').val('');
        $('#project_ids').val('');
        $('#installer_id').val('');
        $('#quotations_id').val('');
        $('#warehouse_id').select2();
        $('select').select2();

        if (issue_type == "project") {
            $(".text-danger.mb-0.custom-error").remove();
            $(".warehouse-wise").addClass('d-none');
            $(".installer-wise").addClass('d-none');
            $(".project-wise").removeClass('d-none');
            $(".trading-wise").addClass('d-none');
            $(".b2b").addClass('d-none');
            $(".b2b-col").attr('colspan', '6');
        }

        if (issue_type == "installer") {
            $(".text-danger.mb-0.custom-error").remove();
            $(".warehouse-wise").addClass('d-none');
            $(".installer-wise").removeClass('d-none');
            $(".project-wise").addClass('d-none');
            $(".trading-wise").addClass('d-none');
            $(".b2b").addClass('d-none');
            $(".b2b-col").attr('colspan', '6');
        }

        if (issue_type == "warehouse") {
            $(".text-danger.mb-0.custom-error").remove();
            $(".warehouse-wise").removeClass('d-none');
            $(".installer-wise").addClass('d-none');
            $(".project-wise").addClass('d-none');
            $(".trading-wise").addClass('d-none');
            $(".b2b").addClass('d-none');
            $(".b2b-col").attr('colspan', '6');
        }

        if (issue_type == "trading") {
            $(".text-danger.mb-0.custom-error").remove();
            $(".warehouse-wise").addClass('d-none');
            $(".installer-wise").addClass('d-none');
            $(".project-wise").addClass('d-none');
            $(".trading-wise").removeClass('d-none');
            $(".b2b").removeClass('d-none');
            $(".b2b-col").attr('colspan', '10');
        }

    });

    $(document).on('change', '#project_ids', function() {

        var project_ids = $(this).val();
        $('.product_id').select2();

        var temp = 0;
        $('#table_col').addClass('d-none');
        var id = $('#warehouse_id').val();

        var delivery_challan_id = $('#id').val();
        var bom_id = $('#bom_id').val();

        $('#warehouse_id_from option:disabled').prop('disabled', false);
        $('#warehouse_id_from option[value="' + id + '"]').prop('disabled', true);

        if (id != null) {
            $.ajax({
                type: "get",
                url: "{{route('get-warehouse-stock')}}",
                data: {
                    'project_ids': project_ids,
                    'id': id,
                    'delivery_challan_id': delivery_challan_id,
                    'bom_id': bom_id,
                    'type': 'Challan',
                    "_token": "{{ csrf_token() }}",
                },
                dataType: 'json',
                cache: false,
                success: function(response) {
                    if (response.status_code == 403) {
                        $('#table_col').addClass('d-none');
                        toastr.clear();
                        toastr.warning(response.message, "Warning");
                    } else {
                        $('#warehouse_id').attr('disabled', true);
                        $('#bom_id').attr('disabled', true);
                        $('#table_col').removeClass('d-none');
                        $(".sub_data").html('');
                        $(".sub_data").html(response.html);
                        $('.product_id').select2();
                        // if (temp == 0) {
                        //     clone();
                        // }

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

    $(document).on('change', '#project_id', function() {

        var project_ids = [$(this).val()];
        $('.product_id').select2();

        var temp = 0;
        $('#table_col').addClass('d-none');
        var id = $('#warehouse_id').val();

        var delivery_challan_id = $('#id').val();
        var bom_id = $('#bom_id').val();

        $('#warehouse_id_from option:disabled').prop('disabled', false);
        $('#warehouse_id_from option[value="' + id + '"]').prop('disabled', true);

        if (id != null) {
            $.ajax({
                type: "get",
                url: "{{route('get-warehouse-stock')}}",
                data: {
                    'project_ids': project_ids,
                    'id': id,
                    'delivery_challan_id': delivery_challan_id,
                    'bom_id': bom_id,
                    'type': 'Challan',
                    "_token": "{{ csrf_token() }}",
                },
                dataType: 'json',
                cache: false,
                success: function(response) {
                    if (response.status_code == 403) {
                        $('#table_col').addClass('d-none');
                        toastr.clear();
                        toastr.warning(response.message, "Warning");
                    } else {
                        $('#warehouse_id').attr('disabled', true);
                        $('#bom_id').attr('disabled', true);
                        $('#table_col').removeClass('d-none');
                        $(".sub_data").html('');
                        $(".sub_data").html(response.html);
                        $('.product_id').select2();
                        // if (temp == 0) {
                        //     clone();
                        // }

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

    $(document).on('change', '#quotations_id', function() {

        var quotations_id = $(this).val();

        var temp = 0;
        $('#table_col').addClass('d-none');
        var id = $('#warehouse_id').val();

        var delivery_challan_id = $('#id').val();
        var bom_id = $('#bom_id').val();

        $('#warehouse_id_from option:disabled').prop('disabled', false);
        $('#warehouse_id_from option[value="' + id + '"]').prop('disabled', true);

        if (id != null) {
            $.ajax({
                type: "get",
                url: "{{route('get-warehouse-stock')}}",
                data: {
                    'quotations_id': quotations_id,
                    'id': id,
                    'delivery_challan_id': delivery_challan_id,
                    'bom_id': bom_id,
                    'type': 'Challan',
                    "_token": "{{ csrf_token() }}",
                },
                dataType: 'json',
                cache: false,
                success: function(response) {
                    if (response.status_code == 403) {
                        $('#table_col').addClass('d-none');
                        toastr.clear();
                        toastr.warning(response.message, "Warning");
                    } else {
                        $('#warehouse_id').attr('disabled', true);
                        $('#bom_id').attr('disabled', true);
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
</script>
@endsection