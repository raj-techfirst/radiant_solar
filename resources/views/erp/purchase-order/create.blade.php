@extends('layouts.app')
@section('title', 'Purchase Order')
@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="content-header-title float-start">
            Add Purchase Order
        </h4>
        <style>
            .input-group>.select2-container--bootstrap {
                width: auto;
                flex: 1 1 auto;
            }

            .input-group>.select2-container--bootstrap .select2-selection--single {
                height: 100%;
                line-height: inherit;
                padding: 0.5rem 1rem;
            }
        </style>

        <a href="{{route('purchase-order.index')}}" role="button" class="btn btn-sm btn-gradient-primary float-end"><i class="fa fa-angle-double-left me-25"></i> List</a>

        @can('product-create')
        <!-- <button type="button" data-bs-toggle="modal" data-bs-target="#inlineModal" class="btn btn-sm btn-primary float-end me-25"><i class="fa fa-plus me-25"></i> Add New Bill of Supply</button> -->
        @endcan
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-body p-1">
                <form id="form" class="form p-0 form-repeater" method="post" action="javascript:void(0);">
                    @csrf
                    <div class="row">
                        <div class="col-12 col-sm-12 col-md-4 col-lg-4 form-group custom-input-group">
                            <label class="form-label" for="purchase_date">Date</label>
                            <input class="form-control" autocomplete="off" name="purchase_date" id="purchase_date" value="{{(isset($purchaseOrder)) ? date('d-m-Y',strtotime($purchaseOrder->purchase_date)) : date('d-m-Y')}}">
                            <span class="invalid-feedback d-block" id="error_date" role="alert"></span>
                        </div>
                        <div class="col-12 col-sm-12 col-md-6 col-lg-4 form-group custom-input-group">
                            <label class="form-label" for="supplier_id">Supplier Name <span class="text-danger">*</span></label>
                            <select class="form-select select2 custom-select2" name="supplier_id" id="supplier_id">
                                <option value="" selected disabled>-- Select --</option>
                                @foreach ($supplierList as $ve)
                                <option value="{{ $ve->id }}">{{ $ve->name}}</option>
                                @endforeach
                            </select>
                            <span class="invalid-feedback d-block" id="error_supplier_id" role="alert"></span>
                        </div>
                        <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-group mt-3">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered" id="purchaseTable">
                                    <thead>
                                        <tr>
                                        <th class="text-center"  style="width:30px">#</th>
                                            <th style="max-width:130px" class="text-center">Type</th>
                                            <th style="min-width:calc(100% - 930px)" class="text-center">Item</th>
                                            <th style="max-width:200px" class="text-center">Qty.</th>
                                            <th style="max-width:50px" class="text-center">G%</th>
                                            <th style="max-width:130px" class="text-center">Rate</th>
                                            <th style="max-width:130px" class="text-center">GST Amt.</th>
                                            <th style="max-width:130px" class="text-center">Amount</th>
                                            <th style="max-width:50px" class="text-center"></th>
                                        </tr>
                                    </thead>
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
                                                <input type="text" class="form-control mt-50" name="item_group_remark" placeholder="Remark If Any*">
                                            </td>
                                            <td class="custom-input-group type-item ">
                                                <select class="form-select product_id custom-select2 " name="product_id" required>
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
                                                <input type="text" class="form-control gst" name="gst" value="" readonly>
                                            </td>
                                            <td class="custom-input-group">
                                                <input type="number" class="form-control price" name="price" required>
                                            </td>
                                            <td class="custom-input-group">
                                                <input type="number" class="form-control gst-amt number" name="gst_amt" value="" readonly>
                                            </td>
                                            <td>
                                                <input type="number" readonly class="form-control total_amount" name="total">
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="badge badge-light-danger m-0 border-0 data-repeater-delete remove-item" data-repeater-delete>
                                                    <i data-feather='trash-2'></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
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
                                            <td class="text-center">
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
                        <div class="col-12 col-sm-12 col-md-12 col-lg-6 form-group custom-input-group">
                            <label class="form-label">Remark</label>
                            <textarea class="form-control" autocomplete="off" name="remark" placeholder="Type here.." rows="6">{{(isset($purchaseOrder)) ? $purchaseOrder->remark : old('remark')}}</textarea>
                            <span class="invalid-feedback d-block" role="alert"></span>
                        </div>
                        <div class="col-12 col-sm-12 col-md-12 col-lg-6 form-group custom-input-group">
                            <label class="form-label">Shipping Address</label>
                            <div class="row">
                                <div class="col-12 col-sm-12 col-md-12 col-lg-6">
                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-group custom-input-group">
                                        <label class="form-label" for="shipping_name">Name</label>
                                        <input class="form-control" name="shipping_name" id="shipping_name" value="{{ env('APP_NAME') }}">
                                    </div>
                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-group custom-input-group">
                                        <label class="form-label" for="shipping_mobile">Mobile</label>
                                        <input class="form-control" name="shipping_mobile" id="shipping_mobile" value="{{ env('APP_OWNER_MOBILE') }}">
                                    </div>
                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-group custom-input-group">
                                        <label class="form-label" for="shipping_email">Email</label>
                                        <input class="form-control" name="shipping_email" id="shipping_email" value="{{ env('APP_OWNER_EMAIL') }}">
                                    </div>

                                </div>
                                <div class="col-12 col-sm-12 col-md-12 col-lg-6">
                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-group custom-input-group">
                                        <label class="form-label" for="shipping_address">Address</label>
                                        <textarea rows="3" class="form-control" name="shipping_address" id="shipping_address">{{ env('APP_OWNER_ADDRESS') }}</textarea>
                                    </div>
                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-group custom-input-group">
                                        <label class="form-label" for="shipping_gst">GSTIN</label>
                                        <input class="form-control" name="shipping_gst" id="shipping_gst" value="{{ env('APP_OWNER_GST') }}">
                                    </div>
                                </div>
                            </div>
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
<div class="modal fade" id="inlineModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-transparent border-bottom">
                <h4 class="text-center mb-0" id="exampleModalTitle">Add Item</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-1" id="body">
                <form id="form" class="form" action="javascript:void(0);" method="POST">
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('pagescript')
<script type="application/javascript">
    $("#inlineModal").on("hidden.bs.modal", function(e) {
        $(this).find('form').trigger('reset');
        $("#product_id").val("");
        $(".invalid-feedback").html("");
        $(".custom-error").html("");
        $("#exampleModalTitle").html("Add Item");
    });

    $("#purchase_date").flatpickr({
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

        $('.form-repeater, .repeater-default').repeater({
            show: function() {
                $('.new-material:not(:last)').remove();
                $(this).slideDown();
                var obj = $(this);
                var sr = $('.sr_no').length;
                $(this).find('.sr_no').text(sr);
                if (feather) {
                    feather.replace({
                        width: 14,
                        height: 14
                    });
                }
                $('.sub_data .select2-container').remove();
                obj.find('.type').val('Item').trigger('change');
                obj.find('.product_id').select2();
                obj.find('.item_group_id').select2();
            },
            hide: function(deleteElement) {
                if (($('.remove-item').length) > 1) {
                    $(this).slideUp(deleteElement);
                } else {
                    Swal.fire({
                        text: "Cannot delete first item",
                        icon: 'warning',
                        confirmButtonText: 'OK',
                    });
                }
            }
        });
    });

    $("#form").validate({
        rules: {
            supplier_id: {
                required: true,
            },
            purchase_date: {
                required: true,
            }
        },
        messages: {
            supplier_id: {
                required: "Select supplier"
            },
            purchase_date: {
                required: "Select date"
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
        var gst = $(this).find('option:selected').data('gst');
        var unit = $(this).find('option:selected').data('unit');
        $(this).closest('tr').find('.unit_type').html(unit);
        $(this).closest('tr').find('.gst').val(gst);
    });

    $(document).on('change', '.item_group_id', function() {
        var gst = $(this).find('option:selected').data('gst');
        var unit = $(this).find('option:selected').data('unit');
        $(this).closest('tr').find('.unit_type').html(unit);
        $(this).closest('tr').find('.gst').val(gst);
    });

    $(document).on('click', '.save', function() {
        if ($("#form").valid()) {
            $('select').attr('disabled', false);
            var formData = new FormData($("#form")[0]);
            $.ajax({
                type: "POST",
                url: "{{ route('purchase-order.store') }}",
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
                        Swal.fire({
                            title: "Error",
                            text: response.message,
                            icon: 'error'
                        })
                    } else if (response.status_code == 403) {
                        Swal.fire({
                            title: "Warning",
                            text: response.message,
                            icon: 'warning'
                        })
                    } else if (response.status_code == 201) {
                        $.each(response.errors, function(key, value) {
                            $('#error_' + key).html('<p class="text-danger mb-0">' + value + '</p>');
                        });
                        Swal.fire({
                            title: "Warning",
                            text: response.message,
                            icon: 'warning'
                        })
                    } else {
                        $('#form')[0].reset();
                        Swal.fire({
                            title: "Success",
                            text: response.message,
                            icon: 'success'
                        })
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

    $(document).on('blur', '.quantity', function() {
        calculationAmount();
    });

    $(document).on('blur', '.price', function() {
        calculationAmount();

    });

    $(document).on('change', '.tax', function() {
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

    // $(document).on('keydown', '.price', function(e) {
    //     var priceElements = $('.price');
    //     if (priceElements.last()[0] === this && (e.key === "Tab" || e.keyCode === 9)) {
    //         $('.add-new').trigger('click');
    //     }
    // });
</script>
@endsection