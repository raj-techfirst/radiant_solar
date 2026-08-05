@extends('layouts.app')
@section('title', 'Estimate')
@section('content')
<div class="row">
    <div class="col-12">
        @if((isset($estimate) && isset($estimate->id)))
        <h4 class="card-title mb-1">{{ __('message.Edit Estimate') }}</h4>
        @else
        <h4 class="card-title mb-1">{{ __('message.Add Estimate') }}</h4>
        @endif
    </div>
    <div class="col-12">
        <div class="card p-1">
            <form id="form" class="form invoice-repeater" action="javascript:void(0)" method="POST">
                @csrf
                <div class="row">
                    @if((isset($estimate) && isset($estimate->id)))
                    <input type="hidden" id="estimate_id" name="estimate_id" value="{{ $estimate->id }}">
                    @endif
                    <div class="col-12 col-md-12 mb-1 custom-input-group">
                        <label class="form-label" for="lead_id">{{ __('message.Client') }} <span class="text-danger">*</span></label>
                        <select class="form-control" name="lead_id" id="lead_id">
                            <option selected disabled>{{ __('message.-- Select --') }}</option>
                            @foreach($leadMaster as $value)
                            <option value="{{$value->id}}" {{ (isset($estimate) && ($estimate->lead_master_id == $value->id) ? 'selected' : '')}}>{{$value->name}} {{$value->last_name}} - {{$value->mobile}} ({{$value->lead_title}})</option>
                            @endforeach
                        </select>
                        <span class="invalid-feedback d-block" id="error_lead_id" role="alert"></span>
                    </div>
                </div>
                <div class="row d-none" id="company_detail">
                    <hr>
                    <div class="col-md-6">
                        <h2>{{$companyProfile->user->company_name}}</h2>
                        <p>{{$companyProfile->address}}</p>
                        @if(!is_null($companyProfile->state_id))
                        <p>{{$companyProfile->state->state_name}}</p>
                        @endif
                        @if(!is_null($companyProfile->city_id))
                        <p>{{$companyProfile->city->city_name}}</p>
                        @endif
                        @if(!is_null($companyProfile->user->mobile))
                        <p>Mo.No. : {{$companyProfile->user->mobile}}</p>
                        @endif
                    </div>
                    <div class="col-md-6 text-end" id="client_detail">

                    </div>
                    <hr>
                </div>
                <div class="row">
                    <div class="col-12 col-md-4 mb-1 custom-input-group">
                        <label class="form-label" for="estimate_title">{{ __('message.Estimate Title') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="estimate_title" id="estimate_title" placeholder="{{ __('message.Estimate Title') }}" value="{{ ((isset($estimate) && isset($estimate->estimate_title)) ? $estimate->estimate_title : '')  }}">
                        <span class="invalid-feedback d-block" id="error_estimate_title" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-4 mb-1 custom-input-group">
                        <label class="form-label" for="estimate_date">{{ __('message.Estimate Date') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control date" name="estimate_date" id="estimate_date" autocomplete="off" placeholder="{{ __('message.Estimate Date') }}" value="{{ ((isset($estimate) && isset($estimate->estimate_date)) ? date('d-m-Y', strtotime($estimate->estimate_date)) : date('d-m-Y'))  }}" readonly>
                        <span class="invalid-feedback d-block" id="error_estimate_date" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-4 mb-1 custom-input-group">
                        <label class="form-label" for="expiry_date">{{ __('message.Expiry Date') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control date" name="expiry_date" id="expiry_date" autocomplete="off" placeholder="{{ __('message.Expiry Date') }}" value="{{ ((isset($estimate) && isset($estimate->expiry_date)) ? date('d-m-Y', strtotime($estimate->expiry_date)) : '')  }}" readonly>
                        <span class="invalid-feedback d-block" id="error_expiry_date" role="alert"></span>
                    </div>
                    <hr>
                    @if((isset($estimate) && isset($estimate->id)))
                    <div data-repeater-list="invoice">
                        @foreach($estimate->estimateItem as $key => $value)
                        <div data-repeater-item>
                            <div class="row">
                                <input type="hidden" name="estimateItem_id" value="{{ $value->id }}">
                                <div class="col-12 col-md-6 col-lg-2 mb-1 custom-input-group">
                                    <label class="form-label" for="category_id">{{ __('message.Category') }}</label>
                                    <select class="form-control select2  custom-select2" name="category_id" id="category_id">
                                        <option selected disabled>{{ __('message.-- Select --') }}</option>
                                        @foreach($category as $item)
                                        <option value="{{$item->id}}" {{ ($value->category_id == $item->id) ? 'selected' : ''}}>{{$item->category_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-6 col-lg-2 mb-1">
                                    <label class="form-label">{{ __('message.Product') }} <span class="text-danger">*</span></label>
                                    <select class="form-select product_select" name="product_id" data-key="0">
                                        <option value="" selected disabled>{{ __('message.-- Select --') }}</option>
                                        @foreach ($product as $item)
                                        <option value="{{ $item->id }}" {{ ($value->product_id == $item->id ) ? 'selected' : '' }}>{{ $item->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-6 col-lg-2 mb-1">
                                    <label class="form-label">{{ __('message.Quantity') }} <span class="text-danger">*</span></label>
                                    <input type="number" min="1" class="form-control quantity" name="quantity" placeholder="{{ __('message.Quantity') }}" value="{{ ((isset($value) && isset($value->quantity)) ? $value->quantity : '')  }}">
                                </div>

                                <div class="col-12 col-md-6 col-lg-2 mb-1">
                                    <label class="form-label">{{ __('message.Unit') }} <span class="text-danger">*</span></label>
                                    <select class="form-select unit_select" name="unit_id" data-key="0">
                                        <option value="" selected disabled>{{ __('message.-- Select --') }}</option>
                                        @foreach ($unit as $item)
                                        <option value="{{ $item->id }}" {{ ($value->unit_id == $item->id ) ? 'selected' : '' }}>{{ $item->unit_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-6 col-lg-1 mb-1">
                                    <label class="form-label">{{ __('message.Rate') }} <span class="text-danger">*</span></label>
                                    <input type="number" min="1" class="form-control rate" name="rate" placeholder="{{ __('message.Rate') }}" value="{{ $value->rate }}">
                                </div>
                                <div class="col-12 col-md-6 col-lg-2 mb-1">
                                    <label class="form-label">{{ __('message.Total') }}</label>
                                    <input type="number" class="form-control total" name="total" placeholder="{{ __('message.Total') }}" value="{{ $value->total }}" disabled>
                                </div>
                                <div class="col-lg-1 mb-1">
                                    <button class="btn btn-outline-danger text-nowrap px-1 mt-2 float-end data-repeater-delete remove-item" data-id="{{$value->id}}" data-repeater-delete type="button">
                                        <i data-feather="x"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div data-repeater-list="invoice">
                        <div data-repeater-item>
                            <div class="row">
                                <div class="col-12 col-md-6 col-lg-2 mb-1 custom-input-group">
                                    <label class="form-label" for="category_id">{{ __('message.Category') }}</label>
                                    <select class="form-control select2  custom-select2" name="category_id" id="category_id">
                                        <option selected disabled>{{ __('message.-- Select --') }}</option>
                                        @foreach($category as $value)
                                        <option value="{{$value->id}}" {{ (isset($estimate) && ($estimate->category_id == $value->id) ? 'selected' : '')}}>{{$value->category_name}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 col-md-6 col-lg-2 mb-1">
                                    <label class="form-label">{{ __('message.Product') }} <span class="text-danger">*</span></label>
                                    <select class="form-select product_select  select2  custom-select2" name="product_id" data-key="0">
                                        <option value="" selected>{{ __('message.-- Select --') }}</option>
                                        @foreach ($product as $item)
                                        <option value="{{ $item->id }}" {{ (isset($estimate) && $estimate->product_id == $item->id ) ? 'selected' : '' }}>{{ $item->name}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 col-md-6 col-lg-2 mb-1">
                                    <label class="form-label">{{ __('message.Quantity') }} <span class="text-danger">*</span></label>
                                    <input type="number" min="1" class="form-control quantity" name="quantity" placeholder="{{ __('message.Quantity') }}" value="{{ old('quantity') }}">
                                </div>

                                <div class="col-12 col-md-6 col-lg-2 mb-1">
                                    <label class="form-label">{{ __('message.Unit') }} <span class="text-danger">*</span></label>
                                    <select class="form-select unit_select" name="unit_id" data-key="0">
                                        <option value="" selected>{{ __('message.-- Select --') }}</option>
                                        @foreach ($unit as $item)
                                        <option value="{{ $item->id }}" {{ (isset($estimate) && $estimate->unit_id == $item->id ) ? 'selected' : '' }}>{{ $item->unit_name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 col-md-6 col-lg-1 mb-1">
                                    <label class="form-label">{{ __('message.Rate') }} <span class="text-danger">*</span></label>
                                    <input type="number" min="1" class="form-control rate" name="rate" placeholder="{{ __('message.Rate') }}" value="{{ old('rate') }}">
                                </div>
                                <div class="col-12 col-md-6 col-lg-2 mb-1">
                                    <label class="form-label">{{ __('message.Total') }}</label>
                                    <input type="number" class="form-control total" name="total" placeholder="{{ __('message.Total') }}" value="" disabled>
                                </div>
                                <div class="col-lg-1 mb-1">
                                    <button class="btn btn-outline-danger text-nowrap px-1 mt-2 float-end data-repeater-delete remove-item" data-repeater-delete type="button">
                                        <i data-feather="x"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    <hr>
                    <div class="col-md-7">
                        <button class="btn btn-sm btn-icon btn-primary" type="button" data-repeater-create>
                            <i class="fa fa-plus me-25"></i> <span>{{ __('message.Add New') }}</span>
                        </button>
                    </div>
                    <div class="col-md-5">
                        <div class="row align-items-center">
                            <div class="col-md-5 text-end mb-1">
                                <label class="">{{ __('message.SUB TOTAL') }}</label>
                            </div>
                            <div class="col-md-7 mb-1">
                                <input type="number" class="form-control subtotal" name="subtotal" placeholder="{{ __('message.Sub Total') }}" value="" disabled>
                            </div>
                            <div class="col-md-5 text-end mb-1">
                                <label class="">{{ __('message.DISCOUNT(%)') }}</label>
                            </div>
                            <div class="col-md-7 mb-1">
                                <input type="number" class="form-control discount" name="discount" placeholder="{{ __('message.Discount') }}" value="{{ ((isset($estimate) && isset($estimate->discount)) ? $estimate->discount : '')  }}">
                            </div>
                            <div class="col-md-5 text-end mb-1">
                                <label class="">{{ __('message.TOTAL') }}</label>
                            </div>
                            <div class="col-md-7 mb-1">
                                <input type="number" class="form-control grand-total" name="grand-total" placeholder="{{ __('message.Total') }}" value="" disabled>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 form-group editor-space mb-1">
                        <label class="form-label" for="remark">{{ __('message.Notes') }}</label>
                        <textarea id="editor1" class="ckeditor" name="remark" cols="100" rows="10">{!! ((isset($estimate) && isset($estimate->remark)) ? $estimate->remark : '')  !!}</textarea>
                    </div>
                    @if(!is_null($company->terms_conditions))
                    <div class="col-12">

                        <h4><b>Terms & Conditions :</b></h4>
                        {!! $company->terms_conditions !!}
                    </div>
                    @endif

                    <div class="col-md-12">
                        <button type="submit" class="btn btn-sm btn-primary float-end save">{{ __('message.Submit') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('pagescript')
<script>
    ClassicEditor
        .create(document.querySelector('#editor1'))
        .catch(error => {
            console.error(error);
        });
    $(document).ready(function() {

        var date = new Date();
        var minDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() - 2);
        $("#estimate_date").datepicker({
            dateFormat: 'dd-mm-yy',
            minDate: minDate,
        });

        var minDateExp = new Date(date.getFullYear(), date.getMonth(), date.getDate());
        $("#expiry_date").datepicker({
            dateFormat: 'dd-mm-yy',
            minDate: minDateExp,
        });

        $("#estimate_date").change(function() {
            startDate = $(this).datepicker('getDate');
            $("#expiry_date").datepicker('option', 'minDate', startDate);
        });
        $("#expiry_date").change(function() {
            endDate = $(this).datepicker('getDate');
            $("#estimate_date").datepicker('option', 'maxDate', endDate);
        });
    });

    $(document).on('click', '.save', function() {
        var text = $('.ck-content').children().text();
        $("#editor1").val(text);
        var formData = new FormData($("#form")[0]);
        if ($("#lead_id").val() != "" && $("#estimate_title").val() != "" && $("#estimate_date").val() != "" && $('#expiry_date').val() != "") {
            $.ajax({
                type: "POST",
                url: "{{route('estimate.store')}}",
                data: formData,
                dataType: 'json',
                // cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $("#error_name").html(' ');
                    $(".save").html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> {{ __('message.Wait') }}`);
                    $(".save").attr('disabled', true);
                },
                success: function(response) {
                    $(".save").html("{{ __('message.Submit') }}");
                    $(".save").attr('disabled', false);
                    if (response.server_error && response.status == false) {
                        toastr.error("{{ __('message.Something went wrong. Please try again.') }}", "{{ __('message.Error') }}");
                    } else if (response.status == false) {
                        $.each(response.errors, function(key, value) {
                            $('#error_' + key).html('<p class="text-danger mb-0">' + value + '</p>');
                        });
                        toastr.warning("{{ __('message.Please input proper data.') }}", "{{ __('message.Warning') }}");
                    } else {
                        $('#form')[0].reset();
                        toastr.success("{{ __('message.Saved successfully.') }}", "{{ __('message.Success') }}");
                        setTimeout(function() {
                            location.href = response.data;
                        }, 2000);
                    }
                }
            });
        } else {
            $("#form").validate({
                rules: {
                    lead_id: {
                        required: true,
                    },
                    estimate_title: {
                        required: true,
                    },
                    estimate_date: {
                        required: true,
                        date: false
                    },
                    expiry_date: {
                        required: true,
                        date: false
                    },
                },
                messages: {
                    lead_id: {
                        required: "{{ __('message.Select client') }}",
                    },
                    estimate_title: {
                        required: "{{ __('message.Enter estimate title') }}",
                    },
                    estimate_date: {
                        required: "{{ __('message.Select estimate date') }}",
                    },
                    expiry_date: {
                        required: "{{ __('message.Enter expiry date') }}",
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
        }
    });

    //delete product using ajax
    $(document).on('click', '.remove-item', function() {
        if (($('.remove-item').length) > 1) {
            var btn = $(this);
            var id = btn.data('id');
            if (id != null) {
                $.ajax({
                    url: "{{route('remove-single-item')}}",
                    type: "POST",
                    dataType: 'JSON',
                    data: {
                        "_token": "{{ csrf_token() }}",
                        "id": id,
                    },
                    success: function(response) {
                        $(this).slideDown();
                    }
                });
            }
        } else {
            Swal.fire({
                text: "Cannot remove first item",
                icon: 'warning',
                confirmButtonText: 'OK',
            });
        }
    });

    if ($('#estimate_id').val() != '' && $('#estimate_id').val() != undefined) {
        changeClient();
        get_subtotal();
        get_dis();
    }

    $(document).on("change", ".product_select", function() {
        var id = $(this).val();
        var me = $(this);
        changeProduct(id, me);
    });

    function changeProduct(product_id, me) {
        // var product_id = $("#product_id").val();
        var route = "{{route('estimate.update','id')}}".replace('id', product_id);
        $.ajax({
            type: 'PUT',
            url: route,
            datatype: 'json',
            data: {
                "product_id": product_id,
                "_token": "{{ csrf_token() }}"
            },
            success: function(response) {
                me.parent().parent().parent().find('.rate').val('');
                me.parent().parent().parent().find('.quantity').val(1);
                me.parent().parent().parent().find('.rate').val((response.rate).toFixed(2));
                get_total(me);
            }
        });
    }

    $(document).on('change', '#lead_id', function() {
        changeClient();
    });

    function changeClient() {
        var lead_id = $("#lead_id").val();
        var route = "{{route('estimate.show','id')}}".replace('id', lead_id);
        $.ajax({
            type: 'get',
            url: route,
            datatype: 'json',
            data: {
                "lead_id": lead_id,
            },
            success: function(response) {
                $('#client_detail').empty('');
                $('#company_detail').removeClass('d-none');
                if (response.company_name != null) {
                    var name = response.company_name;
                } else {
                    var name = '';
                }

                if (response.state_id != null) {
                    var state = response.state.state_name;
                } else {
                    var state = '';
                }
                if (response.city_id != null) {
                    var city = response.city.city_name;
                } else {
                    var city = '';
                }
                if (response.pincode != null) {
                    var pincode = response.pincode;
                } else {
                    var pincode = '';
                }
                $('#client_detail').html('<h2>' + name +
                    '</h2><p>' + state +
                    '</p><p>' + city +
                    '</p><p>' + pincode +
                    '</p><p>Mo.No. : ' + response.mobile + '</p>');
            }
        });
    }

    $(function() {
        'use strict';
        // form repeater jquery
        $('.invoice-repeater, .repeater-default').repeater({
            show: function() {
                $(this).slideDown();
                var obj = $(this).children(':last-child').children(':last-child').find('.remove-item');
                obj.removeAttr('data-id');
                // Feather Icons
                if (feather) {
                    feather.replace({
                        width: 14,
                        height: 14
                    });
                }
            },
            hide: function(deleteElement) {
                if (($('.remove-item').length) > 1) {
                    $(this).slideUp(deleteElement);
                } else {
                    Swal.fire({
                        text: "Cannot remove first item",
                        icon: 'warning',
                        confirmButtonText: 'OK',
                    });
                }
            }
        });
    });

    $(document).on("keyup", ".quantity", function() {
        var id = $(this).val();
        var me = $(this);
        get_total(me);
    });

    $(document).on("keyup", ".rate", function() {
        var id = $(this).val();
        var me = $(this);
        get_total(me);
    });

    $(document).on('keyup', '.discount', function() {
        get_dis();
    });

    $(document).on('click', '.remove-item', function() {
        setTimeout(function() {
            get_subtotal();
            get_dis();
        }, 1500);
    });

    function get_total(me) {
        var rate = me.parent().parent().parent().find('.rate').val();
        var qty = me.parent().parent().parent().find('.quantity').val();
        var total = eval(qty * rate);
        me.parent().parent().parent().find('.total').val(total.toFixed(2));
        get_subtotal();
        get_dis();
    }

    function get_subtotal() {
        var sum = 0;
        $('.total').each(function() {
            sum += parseFloat($(this).val());
        });
        $(".subtotal").val(sum.toFixed(2));
        get_grand_total();
    }

    function get_dis() {
        var dis = $(".discount").val();
        var subt = $(".subtotal").val();
        var gt = eval(subt - (subt * dis / 100));
        $(".grand-total").val(gt.toFixed(2));
    }

    function get_grand_total() {
        var sub_total = parseInt($(".subtotal").val());
        $(".grand-total").val(sub_total.toFixed(2));
    }
</script>
@endsection