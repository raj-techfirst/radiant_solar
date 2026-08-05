@extends('layouts.app')
@section('title', 'Goods Receipt')
@section('content')
<div class="row">
    <div class="col-12 mb-50">
        <h4 class="content-header-title float-start border-0 ms-1">Goods Receipt {{(isset($data) && isset($data->id)) ? 'Edit' : 'Add' }}</h4>
        @can('purchase-direct-list')
        <a href="{{ route('purchase-direct.index') }}" class="btn btn-sm btn-primary float-end"><i class="fa fa-angle-double-left me-25"></i> List</a>
        @endcan
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-body p-1">
                <form id="form" class="form p-0 form-repeater" method="post" action="javascript:void(0);">
                    @csrf
                    <div class="row">
                        <input type="hidden" name="id" value="{{(isset($data) && isset($data->id)) ? $data->id : '' }}">

                        <div class="col-12 col-sm-12 col-md-4 col-lg-3 form-group custom-input-group">
                            <label class="form-label" for="date">Date</label>
                            <input class="form-control" autocomplete="off" name="date" id="date" value="{{(isset($data)) ? date('d-m-Y',strtotime($data->date)) : date('d-m-Y')}}">
                            <span class="invalid-feedback d-block" id="error_date" role="alert"></span>
                        </div>
                        <div class="col-12 col-sm-12 col-md-6 col-lg-3 form-group custom-input-group">
                            <label class="form-label" for="supplier_id">Supplier Name <span class="text-danger">*</span></label>
                            <select class="form-select select2 custom-select2" name="supplier_id" id="supplier_id">
                                <option value="" selected disabled>-- Select --</option>
                                @foreach ($supplierList as $value)
                                <option value="{{ $value->id }}" {{ (isset($data) && ($data->supplier_id == $value->id) ? 'selected' : '')}}>{{ $value->name}}</option>
                                @endforeach
                            </select>
                            <span class="invalid-feedback d-block" id="error_supplier_id" role="alert"></span>
                        </div>

                        <div class="col-12 col-md-6 col-lg-3 mb-1 custom-input-group">
                            <label class="form-label" for="supplier_number"> Invoice No. <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="supplier_number" id="supplier_number" placeholder="Invoice No." value="{{ ((isset($data) && isset($data->supplier_number)) ? $data->supplier_number : '')  }}">
                            <span class="invalid-feedback d-block" id="error_supplier_number" role="alert"></span>
                        </div>

                        <div class="col-12 col-md-6 col-lg-3 mb-1 custom-input-group">
                            <label class="form-label" for="warehouse_out">Warehouse <span class="text-danger">*</span></label>
                            <select class="form-select select2" name="warehouse_id" id="warehouse_out">
                                <option selected disabled>{{ __('message.-- Select --') }}</option>
                                @foreach($warehouse as $value)
                                <option value="{{ $value->id }}" {{ (isset($data) && ($data->warehous_id == $value->id) ? 'selected' : '')}}>{{ $value->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-group mt-1">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered" id="purchaseTable">
                                    <thead>
                                        <tr>
                                            <th class="text-center">#</th>
                                            <th width="10%" class="text-center">Type</th>
                                            <th width="35%" class="text-center">Item</th>
                                            <th width="12%" class="text-center">Qty.</th>
                                            <th width="10%" class="text-center">GST (%)</th>
                                            <th width="10%" class="text-center">Rate</th>
                                            <th width="10%" class="text-center">GST Amt.</th>
                                            <th width="10%" class="text-center">Amount</th>
                                            <th width="5%" class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    @if((isset($data) && isset($data->id)))
                                    <tbody data-repeater-list="invoice" class="sub_data">
                                        @foreach($data->purchase_direct_meta as $key => $value)
                                        <tr data-repeater-item class="clone_row">
                                            <td class="text-center">
                                                <input type="hidden" name="purchase_direct_meta_id" value="{{ $value->id }}">
                                                <b class="sr_no">{{ $key+1 }}</b>
                                            </td>

                                            <td class="custom-input-group">
                                                <div class="d-flex">
                                                    <select class="form-select custom-select2 type" name="type" required>
                                                        <option value="Item" {{ $value->type == "Item" ? 'selected' : '' }}>BOS</option>
                                                        <option value="ItemGroup" {{ $value->type == "ItemGroup" ? 'selected' : '' }}>Panel/Inverter</option>
                                                    </select>
                                                </div>
                                            </td>

                                            <td class="custom-input-group type-item-group {{ $value->type == 'Item' ? 'd-none' : '' }} ">

                                                <select class="form-select item_group_id custom-select2" name="item_group_id" required>
                                                    <option value="" selected disabled>-- Select --</option>
                                                    @foreach ($itemGroup as $k => $v)
                                                    <option value="{{ $v->id }}" @if($value->item_group_id == $v->id) {{'selected'}} @endif data-unit="{{ $v->unit->unit_name }}" data-gst="{{ $v->gst_rate }}">{{ $v->item_code }} {{ getItemGropName($v) }}</option>
                                                    @endforeach
                                                </select>

                                                <input type="text" class="form-control mt-50" name="item_group_remark" placeholder="Remark If Any*" value="{{  ($value->type == 'ItemGroup') ? $value->remarks : '' }}">

                                            </td>
                                            <td class="custom-input-group type-item {{ $value->type == 'ItemGroup' ? 'd-none' : '' }} ">
                                                <select class="form-select product_id custom-select2" name="item_id" required>
                                                    <option value="" selected disabled>-- Select --</option>
                                                    @foreach ($items as $k => $v)
                                                    <option value="{{ $v->id }}" data-unit="{{ $v->unit->unit_name }}" data-gst="{{ $v->gst_rate }}" {{ ($value->item_id == $v->id ) ? 'selected' : '' }}>{{ $v->item_code}} {{ $v->name}}</option>
                                                    @endforeach
                                                </select>
                                                <input type="text" class="form-control mt-50" name="item_remark" placeholder="Remark If Any*" value="{{  ($value->type == 'Item') ? $value->remarks : '' }}">

                                            </td>
                                            <td class="custom-input-group">
                                                <div class="input-group">
                                                    <input type="number" class="form-control quantity" name="quantity" value="{{ $value->quantity }}" required>
                                                    <span class="input-group-text unit_type"></span>
                                                </div>
                                            </td>
                                            <td class="custom-input-group">
                                                <input type="text" class="form-control gst" name="gst" value="{{$value->gst}}" readonly>
                                            </td>
                                            <td class="custom-input-group">
                                                <input type="number" class="form-control price" name="price" value="{{$value->price}}">
                                            </td>
                                            <td class="custom-input-group">
                                                <input type="number" class="form-control gst-amt number" name="gst_amt" value="{{$value->gst_amt}}" readonly>
                                            </td>
                                            <td>
                                                <input type="number" readonly class="form-control total_amount" name="total" value="{{$value->total}}">
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-icon rounded-circle btn-link text-danger data-repeater-delete variant-delete" data-id="{{$value->id}}" data-repeater-delete>
                                                    <i data-feather='trash-2'></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    @else
                                    <tbody data-repeater-list="invoice" class="sub_data">
                                        <tr data-repeater-item class="clone_row">
                                            <td class="text-center">
                                                <b class="sr_no">1</b>
                                            </td>

                                            <td class="custom-input-group">
                                                <div class="d-flex">
                                                    <select class="form-select custom-select2 type" name="type" required>
                                                        <option value="Item" selected>BOS</option>
                                                        <option value="ItemGroup">Panel/Inverter</option>
                                                    </select>
                                                </div>
                                            </td>

                                            <td class="custom-input-group type-item-group d-none">
                                                <select class="form-select item_group_id custom-select2" name="item_group_id" required>
                                                    <option value="" selected disabled>-- Select --</option>
                                                    @foreach ($itemGroup as $k => $v)
                                                    <option value="{{ $v->id }}" data-unit="{{ $v->unit->unit_name }}" data-gst="{{ $v->gst_rate }}">{{ $v->item_code }} {{ getItemGropName($v) }}</option>
                                                    @endforeach
                                                </select>
                                                <input type="text" class="form-control mt-50" name="item_group_remark" placeholder="Remark If Any*" >
                                            </td>

                                            <td class="custom-input-group type-item">
                                                <select class="form-select product_id custom-select2" name="item_id" required>
                                                    <option value="" selected disabled>-- Select --</option>
                                                    @foreach ($items as $k => $v)
                                                    <option value="{{ $v->id }}" data-unit="{{ $v->unit->unit_name }}" data-gst="{{ $v->gst_rate }}">{{ $v->item_code}} {{ $v->name}}</option>
                                                    @endforeach
                                                </select>
                                                <input type="text" class="form-control mt-50" name="item_remark" placeholder="Remark If Any*">
                                            </td>
                                            <td class="custom-input-group">
                                                <div class="input-group">
                                                    <input type="number" class="form-control quantity" name="quantity" required>
                                                    <span class="input-group-text unit_type"></span>
                                                </div>
                                            </td>
                                            <td class="custom-input-group">
                                                <input type="text" class="form-control gst" name="gst" readonly>
                                            </td>
                                            <td class="custom-input-group">
                                                <input type="number" class="form-control price" name="price" required>
                                            </td>
                                            <td class="custom-input-group">
                                                <input type="number" class="form-control gst-amt number" name="gst_amt" readonly>
                                            </td>
                                            <td>
                                                <input type="number" readonly class="form-control total_amount" name="total">
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="badge badge-light-danger border-0 data-repeater-delete remove-item" data-repeater-delete>
                                                    <i data-feather='trash-2'></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                    @endif
                                    <tfoot>
                                        <tr>
                                            <td colspan="5" class="text-end"><b>Taxable Amount<b></td>
                                            <td class="text-end"><b>Sub Total</b></td>
                                            <td>
                                                <input type="number" class="form-control gst_amount" placeholder="0" value="" disabled>
                                            </td>
                                            <td>
                                                <input type="number" class="form-control sub_amount" placeholder="0" value="" disabled>
                                            </td>
                                            <td class="text-end">
                                                <button class="badge badge-light-success border-0 add-new m-0" type="button" data-repeater-create>
                                                    <i data-feather="plus" class="me-0"></i>
                                                    <!-- <span>Add</span> -->
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="6"></td>
                                            <td class="text-end"><b>CGST</b></td>
                                            <td>
                                                <input type="number" class="form-control cgst" placeholder="0" value="" disabled>
                                            </td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td colspan="6"></td>
                                            <td class="text-end"><b>SGST</b></td>
                                            <td>
                                                <input type="number" class="form-control sgst" placeholder="0" value="" disabled>
                                            </td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td colspan="6"></td>
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
                        <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-group custom-input-group">
                            <label class="form-label">Remark</label>
                            <textarea class="form-control" autocomplete="off" name="remark" placeholder="Type here..">{{(isset($data)) ? $data->remark : old('remark')}}</textarea>
                            <span class="invalid-feedback d-block" role="alert"></span>
                        </div>
                        <div class="col-12 col-sm-12 col-md-12 col-lg-12 py-2 text-end">
                            <button type="submit" class="btn btn-sm btn-gradient-primary save">Submit</button>
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
    $("#date").flatpickr({
        altInput: true,
        defaultDate: new Date(),
        altFormat: 'd-m-Y',
        dateFormat: 'Y-m-d',
        maxDate: new Date(),
    });

    var raw_material = '';
    var selectedVendorId = '';

    $(document).ready(function() {
        $('.product_id').select2();
        $('.item_group_id').select2();

        $('.product_id').trigger('change');
        $('.item_group_id').trigger('change');

        // if ($("#supplier_id").val() != null) {
        //     $('.product_id').trigger('change');
        // }
        $('.form-repeater, .repeater-default').repeater({
            show: function() {
                $('.new-material:not(:last)').remove();
                $(this).slideDown();
                var obj = $(this);
                var sr = $('.sr_no').length;
                obj.find('.sr_no').text(sr);

                obj.find('.product_id').removeAttr('disabled');
                obj.find('.variant-delete').removeAttr('data-id');
                obj.find('.product_id').next('.select2-container').remove();

                obj.find('.item_group_id').removeAttr('disabled');
                obj.find('.item_group_id').next('.select2-container').remove();

                obj.find('.type').val('Item').trigger('change');

                $('.product_id').select2({
                    placeholder: "--Select--",
                    allowClear: true,
                    width: "100%",
                    selectOnClose: true,
                });
                $('.item_group_id').select2({
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
                            text: "Can`t delete first item",
                            icon: 'warning',
                            confirmButtonText: 'OK',
                        });
                    }
                }
            }
        });
    });

    function updateSerialNumbers() {
        $('.sr_no').each(function(index) {
            $(this).text(index + 1);
        });
    }

    $(document).on('.remove-item').on('keydown', function() {
        var me = $(this);
        me.closest('tr').next('tr').find('.product_id').focus();
    });

    $("#form").validate({
        rules: {
            warehouse_id: {
                required: true,
            },
            supplier_id: {
                required: true,
            },
            date: {
                required: true,
            },
            supplier_number: {
                required: true,
            }
        },
        messages: {
            warehouse_id: {
                required: "Select warehouse"
            },
            supplier_id: {
                required: "Select supplier"
            },
            date: {
                required: "Select date"
            },
            supplier_number: {
                required: "Enter invoice no."
            }
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
        if ($(this).closest('td').hasClass('d-none') === false) {
            var gst = $(this).find('option:selected').data('gst');
            var unit = $(this).find('option:selected').data('unit');
            $(this).closest('tr').find('.unit_type').html(unit);
            $(this).closest('tr').find('.gst').val(gst);

            calculationAmount();
        }
    });

    $(document).on('change', '.item_group_id', function() {
        if ($(this).closest('td').hasClass('d-none') === false) {
            var gst = $(this).find('option:selected').data('gst');
            var unit = $(this).find('option:selected').data('unit');
            $(this).closest('tr').find('.unit_type').html(unit);
            $(this).closest('tr').find('.gst').val(gst);
            calculationAmount();
        }
    });

    $(document).on('click', '.save', function() {
        $('select').attr('disabled', false);
        var formData = new FormData($("#form")[0]);
        if ($("#form").valid()) {
            $.ajax({
                type: "POST",
                url: "{{ route('purchase-direct.store') }}",
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

    //edit time delete single order item
    $(document).on('click', '.variant-delete', function() {
        if (($('.variant-delete').length) > 1) {
            var btn = $(this);
            var id = $(this).data('id');
            var item_id = btn.closest('tr').find('.product_id').val();
            var warehouse_id = $('#warehouse_out').val();
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
                                url: "{{route('puchase-direct-remove')}}",
                                data: {
                                    'id': id,
                                    'warehouse_id': warehouse_id,
                                    'item_id': item_id,
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
                                        toastr.success(response.message, "Success");
                                    } else if (response.status_code == 201) {
                                        toastr.warning(response.message, "Warning");
                                    } else if (response.status_code == 403) {
                                        toastr.warning(response.message, "Warning");
                                    } else {
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

    function getMatiralList(supplier_id) {
        var ojbs = $('#purchaseTable tbody').find('tr:last');
        $.ajax({
            type: "POST",
            url: "#",
            dataType: 'json',
            data: {
                "_token": "{{csrf_token()}}",
                "id": supplier_id,
            },
            success: function(data) {
                if (data.status_code == 200) {
                    ojbs.find(".product_id").empty('');
                    ojbs.find(".product_id").append('<option value="">Raw Material List</option>');
                    $.each(data.result, function(index, row) {
                        ojbs.find(".product_id").append($("<option value='" + row.id + "'>" + row.name + " (" + row.vendor_raw_material_name + ")</option>"));
                    });
                } else {
                    Swal.fire({
                        title: "Error",
                        text: data.message,
                        icon: 'error'
                    })
                }
            },
            error: function(error) {
                $(document.body).css('pointer-events', '');
            }
        });
    }

    $(document).on('blur', '.quantity', function() {
        calculationAmount();
    });

    $(document).on('blur', '.price', function() {
        calculationAmount();
    });

    function calculationAmount() {
        $(".total_amount").each(function() {
            let quantity = $(this).closest('.clone_row').find('.quantity').val();
            let price = $(this).closest('.clone_row').find('.price').val();
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
        $('.total_amount').each(function() {
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

    $(document).on('change', '.type', function() {
        if ($(this).val() == 'Item') {
            $(this).parent().parent().parent().find('.type-item-group').addClass('d-none');
            $(this).parent().parent().parent().find('.type-item').removeClass('d-none');
        } else {
            $(this).parent().parent().parent().find('.type-item-group').removeClass('d-none');
            $(this).parent().parent().parent().find('.type-item').addClass('d-none');
        }
    });
</script>
@endsection
