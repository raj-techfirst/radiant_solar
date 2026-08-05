@extends('layouts.app')
@section('title', 'Convert To Goods Issue')
@section('content')
<div class="row">
    <div class="col-12 mb-50">
        <h4 class="content-header-title float-start border-0 ms-1">Convert To Goods Issue</h4>
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-body p-1">
                <form id="form" class="form p-0 form-repeater" method="post" action="javascript:void(0);">
                    @csrf
                    <div class="row">
                    <div class="col-12 col-sm-12 col-md-4 col-lg-3 form-group custom-input-group">
                            <label class="form-label" for="date">Date</label>
                            <input class="form-control" autocomplete="off" name="challan_date" id="date" value="{{ date('d-m-Y') }}">
                            <span class="invalid-feedback d-block" id="error_date" role="alert"></span>
                        </div>

                        <div class="col-12 col-md-12 col-lg-8 mb-1 custom-input-group">
                            <label class="form-label w-100">Issue Type <span class="text-danger">*</span></label>
                            <label class="form-label btn btn-outline-primary"><input type="radio" name="issue_type" value="project" checked> Project Wise</label>
                            <label class="form-label btn  btn-outline-primary"><input type="radio" name="issue_type" value="installer"> Installer Wise</label>
                        </div>

                        <!-- Installer Wise -->
                        <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group installer-wise d-none">
                            <label class="form-label" for="installer_id">Installer <span class="text-danger">*</span></label>
                            <select class="form-select select2" name="installer_id" id="installer_id">
                                <option selected disabled>{{ __('message.-- Select --') }}</option>
                                @foreach($installer as $value)
                                <option value="{{ $value->user->id }}">{{ $value->user->name.' '. $value->user->last_name   }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group installer-wise d-none">
                            <label class="form-label" for="project_ids">Projects</label>
                            <select class="form-select select2" name="project_ids[]" id="project_ids" multiple>
                                <option disabled>{{ __('message.-- Select --') }}</option>
                                @foreach($project as $value)
                                <option value="{{ $value->id }}">{{ $value->consumer_name }} | {{ $value->consumer_number }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- / Installer Wise -->

                        <!-- Project Wise -->

                        <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group project-wise">
                            <label class="form-label" for="project_id">Project <span class="text-danger">*</span></label>
                            <select class="form-select select2" name="project_id" id="project_id">
                                <option selected disabled>{{ __('message.-- Select --') }}</option>
                                @foreach($project as $value)
                                <option value="{{ $value->id }}">{{ $value->consumer_name }} | {{ $value->consumer_number }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- / Project Wise -->

                        <input type="hidden" name="warehouse_id" id="warehouse_id" value="{{ (isset($data)) ? $data->warehous_id : '' }}">

                        <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-group mt-1">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered" id="purchaseTable">
                                    <thead>
                                        <tr>
                                            <th class="text-center">#</th>
                                            <th class="text-center" width="150px">Type</th>
                                            <th class="text-center" width="20%">Item</th>
                                            <th class="text-center" width="10%">Stock</th>
                                            <th class="text-center" width="15%">Qty.</th>
                                            <th class="text-center" width="10%">GST (%)</th>
                                            <th class="text-center">Rate</th>
                                            <th class="text-center">GST Amt.</th>
                                            <th class="text-center">Amount</th>
                                            <th class="text-center" width="6%">Action</th>

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

                                            <td class="custom-input-group  type-item {{ $value->type != 'Item' ? 'd-none' : '' }} ">
                                                <div class="d-flex">
                                                    <select class="form-select product_id custom-select2" name="item_id" required>
                                                        <option value="" selected disabled>-- Select --</option>
                                                        @foreach ($warehouseStock as $k => $v)
                                                        <option value="{{ $v['id'] }}" data-unit="{{ $v['unit'] }}" data-gst="{{ $v['gst_rate'] }}" @if($value->item_id == $v['id'] ) selected @endif data-stock="{{ $v['stock'] }}">{{ $v['name'].' ('.$v['stock'].')' }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </td>

                                            <td class="custom-input-group type-item-group {{ $value->type != 'ItemGroup' ? 'd-none' : '' }} ">
                                                <div class="d-flex">
                                                    <select class="form-select item_group_id custom-select2" name="item_group_id" required>
                                                        <option value="" selected disabled>-- Select --</option>
                                                        @foreach ($warehouseStockItemGroup as $k => $vg)
                                                        <option value="{{ $vg['id'] }}" data-unit="{{ $vg['unit'] }}" data-gst="{{ $vg['gst_rate'] }}" @if($value->item_group_id == $vg['id'] ) selected @endif data-stock="{{ $vg['stock'] }}" >{{ $vg['name'].' ('.$vg['stock'].')' }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </td>

                                            <td class="custom-input-group">
                                                <input type="hidden" class="stock-find" value="">
                                                <input type="number" class="form-control stock" placeholder="Quantity" value="{{ $value->quantity }}" readonly>
                                            </td>

                                            <td class="custom-input-group">
                                                <div class="input-group">
                                                    <input type="number" class="form-control quantity" name="quantity" value="{{ $value->quantity }}" required>
                                                    <span class="input-group-text unit_type"></span>
                                                </div>
                                            </td>

                                            <td class="custom-input-group">
                                                <input type="text" class="form-control gst" value="" readonly>
                                            </td>

                                            <td class="custom-input-group">
                                                <input type="number" min="1" class="form-control rate number" name="rate" placeholder="Rate" value="{{$value->price}}" required>
                                            </td>

                                            <td class="custom-input-group">
                                                <input type="number" class="form-control gst-amt number" name="gst_amt" value="0" readonly>
                                            </td>

                                            <td class="custom-input-group">
                                                <input type="number" class="form-control amount number" name="amount" placeholder="Amount" value="{{$value->total}}" readonly>
                                            </td>

                                            <td class="text-center">
                                                <button type="button" class="badge badge-light-danger border-0 variant-delete" data-id="{{$value->id}}">
                                                    <i data-feather='trash-2'></i>
                                                </button>
                                            </td>

                                        </tr>
                                        @endforeach
                                    </tbody>
                                    @endif

                                    <tfoot>
                                        <tr>
                                            <td colspan="6" class="text-end"><b>Taxable Amount<b></td>
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
                                            <td colspan="7"></td>
                                            <td class="text-end"><b>CGST</b></td>
                                            <td>
                                                <input type="number" class="form-control cgst" placeholder="0" value="" disabled>
                                            </td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td colspan="7"></td>
                                            <td class="text-end"><b>SGST</b></td>
                                            <td>
                                                <input type="number" class="form-control sgst" placeholder="0" value="" disabled>
                                            </td>
                                            <td></td>
                                        </tr>
                                        <tr>
                                            <td colspan="7"></td>
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
    function clone() {
        $("#date").flatpickr({
        altInput: true,
        defaultDate: new Date(),
        altFormat: 'd-m-Y',
        dateFormat: 'Y-m-d',
        maxDate: new Date(),
    });
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

    $(document).on('change', '.product_id', function() {

        if ($(this).closest('td').hasClass('d-none') === false) {
            var unit = $(this).find('option:selected').data('unit');
            var gst = $(this).find('option:selected').data('gst');
            var stock = $(this).find('option:selected').data('stock');
            $(this).closest('tr').find('.unit_type').html(unit);
            $(this).closest('tr').find('.gst').val(gst);
            $(this).closest('tr').find('.stock-find').val(stock);
            $(this).closest('tr').find('.stock').val(stock);
            var row = $(this).closest('tr');
            calculateTotal(row);
        }
    });

    $(document).on('click', '.reset', function() {
        location.reload();
    });

    $(document).on('click', '.save', function() {
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

    });

    //edit time delete single order item
    $(document).on('click', '.variant-delete', function() {
        if (($('.variant-delete').length) > 1) {
            var btn = $(this);
            var id = $(this).data('id');
            var item_id = btn.closest('tr').find('.product_id').val();
            var warehouse_id = $('#warehouse_id').val();
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
                            toastr.success('Deleted', "Success");
                            btn.parent().parent().slideUp();
                            // $.ajax({
                            //     type: "POST",
                            //     url: "{{route('delivery-challan-remove')}}",
                            //     data: {
                            //         'id': id,
                            //         'warehouse_id': warehouse_id,
                            //         'project_id': project_id,
                            //         'item_id': item_id,
                            //         "_token": "{{ csrf_token() }}",
                            //     },
                            //     dataType: 'json',
                            //     cache: false,
                            //     success: function(response) {
                            //         if (response.status_code == 200) {
                            //             btn.parent().parent().slideUp();
                            //             setTimeout(function() {
                            //                 btn.parent().parent().remove();
                            //             }, 300);
                            //             toastr.success(response.message, "Success");
                            //         } else if (response.status_code == 201) {
                            //             toastr.warning(response.message, "Warning");
                            //         } else if (response.status_code == 403) {
                            //             toastr.warning(response.message, "Warning");
                            //         } else {
                            //             toastr.error(response.message, "Error");
                            //         }
                            //     }
                            // });
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
        if ($(this).closest('td').hasClass('d-none') === false) {
            var unit = $(this).find('option:selected').data('unit');
            var gst = $(this).find('option:selected').data('gst');
            var stock = $(this).find('option:selected').data('stock');
            $(this).closest('tr').find('.unit_type').html(unit);
            $(this).closest('tr').find('.gst').val(gst);
            $(this).closest('tr').find('.stock-find').val(stock);
            $(this).closest('tr').find('.stock').val(stock);
            var row = $(this).closest('tr');
            calculateTotal(row);
        }
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
        if (issue_type == "project") {
            $(".text-danger.mb-0.custom-error").remove();
            $(".installer-wise").addClass('d-none');
            $(".project-wise").removeClass('d-none');
        } else {
            $(".installer-wise").removeClass('d-none');
            $(".project-wise").addClass('d-none');
        }
    });

    $(document).ready(function() {
        $('.product_id').trigger('change');
        $('.item_group_id').trigger('change');
        clone();
    });
</script>
@endsection