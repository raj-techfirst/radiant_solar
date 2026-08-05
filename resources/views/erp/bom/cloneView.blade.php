@extends('layouts.app')
@section('title', 'Clone BOM')
@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="content-header-title float-start">
            Clone BOM
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
        <a href="{{route('bom.index')}}" role="button" class="btn btn-sm btn-gradient-primary float-end"><i class="fa fa-angle-double-left me-25"></i> List</a>
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-body p-1">
                <form id="form" class="form p-0 form-repeater" method="post" action="{{ route('bom.store') }}">
                    @csrf
                    <input type="hidden" name="id" value="0">
                    <div class="row">
                        <div class="col-12 col-sm-12 col-md-4 col-lg-4 form-group custom-input-group">
                            <label class="form-label" for="bom_name">BOM Name</label>
                            <input class="form-control" autocomplete="off" name="bom_name" id="bom_name" value="{{(isset($bOM)) ? $bOM->bom_name : ''}}">
                            <span class="invalid-feedback d-block" id="error_bom_name" role="alert"></span>
                        </div>
                        <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-group mt-3">
                            <div class="table-responsive">
                                <table class="table form-table table-bordered table-sm" id="purchaseTable">
                                    <thead>
                                        <tr>
                                            <th width="3%" class="text-center">#</th>
                                            <th style="width:150px;" class="text-center">Type</th>
                                            <th width="55%" class="text-center">Item</th>
                                            <th width="15%" class="text-center">Quantity</th>
                                            <th width="5%" class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody data-repeater-list="invoice" class="sub_data">
                                        @foreach ($bOMMeta as $key => $po)
                                        <tr data-repeater-item class="clone_row">
                                            <td class="text-center">
                                                <b class="sr_no">{{$key+1}}</b>
                                            </td>
                                            <td class="custom-input-group">
                                                <div class="d-flex">
                                                    <select class="form-select custom-select2 type" name="type" required>
                                                        <option value="Item" {{ $po->type == "Item" ? 'selected' : '' }}>BOS</option>
                                                        <option value="ItemGroup" {{ $po->type == "ItemGroup" ? 'selected' : '' }}>Panel/Inverter</option>
                                                    </select>
                                                </div>
                                            </td>
                                            <td class="custom-input-group type-item-group {{ $po->type == 'Item' ? 'd-none' : '' }} ">
                                                <div class="d-flex">
                                                    <select class="form-select item_group_id custom-select2" name="item_group_id" required>
                                                        <option value="" selected disabled>-- Select --</option>
                                                        @foreach ($itemGroup as $k => $v)
                                                        <option value="{{ $v->id }}" @if($po->item_group_id == $v->id) {{'selected'}} @endif data-unit="{{ $v->unit->unit_name }}" >{{ $v->item_code }} {{ getItemGropName($v) }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </td>
                                            <td class="custom-input-group type-item {{ $po->type == 'ItemGroup' ? 'd-none' : '' }} ">
                                                <input type="hidden" name="meta_id" value="0">
                                                <select class="form-select product_id custom-select2" name="product_id" required>
                                                    <option value="" selected disabled>-- Select --</option>
                                                    @foreach ($items as $k => $v)
                                                    <option value="{{ $v->id }}" @if($po->item_id == $v->id) {{'selected'}} @endif data-unit="{{ $v->unit->unit_name }}" >{{ $v->item_code}} {{ $v->name}}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td class="custom-input-group">
                                                <div class="input-group">
                                                    <input type="number" class="form-control quantity" name="quantity" value="{{$po->quantity}}">
                                                    <span class="input-group-text unit_type"></span>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="badge badge-light-danger border-0 data-repeater-delete remove-item" data-repeater-delete>
                                                    <i data-feather='trash-2'></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="7" class="text-end">
                                                <button class="badge badge-light-success border-0" type="button" data-repeater-create>
                                                    <i data-feather="plus" class="me-25"></i>
                                                    <span>Add More</span>
                                                </button>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-group custom-input-group">
                            <label class="form-label">Remark</label>
                            <textarea class="form-control" autocomplete="off" name="remark" placeholder="Type here..">{{(isset($bOM)) ? $bOM->remarks : old('remark')}}</textarea>
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
    $(document).ready(function() {
        $('.product_id').select2();
        $('.item_group_id').select2();

        $('.product_id').trigger('change');
        $('.item_group_id').trigger('change');

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
                $('.sub_data .select2-container').remove();
                obj.find('.type').val('Item').trigger('change');
                obj.find('.product_id').select2();
                obj.find('.item_group_id').select2();

                $('.product_id').select2();
                $('.item_group_id').select2();
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
            bom_name: {
                required: true,
            }
        },
        messages: {
            supplier_id: {
                required: "Please Enter Name"
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
        var unit = $(this).find('option:selected').data('unit');
        $(this).closest('tr').find('.unit_type').html(unit);
    });

    $(document).on('change', '.item_group_id', function() {
        var unit = $(this).find('option:selected').data('unit');
        $(this).closest('tr').find('.unit_type').html(unit);
    });

    $(document).on('click', '.save', function() {
        if ($("#form").valid()) {
            $('select').attr('disabled', false);
            var formData = new FormData($("#form")[0]);
            var action = $("#form").attr('action');
            $.ajax({
                type: "POST",
                url: action,
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