@extends('layouts.app')
@section('title', 'Profile')
@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="card-title mb-1">{{ __('message.Edit Profile') }}</small></h4>
    </div>
    <div class="col-12">
        <div class="card p-1">
            <form id="form" class="form" action="javascript:void(0)" method="POST">
                @csrf
                <div class="row">
                    <div class="col-12 col-md-6 col-lg-6 mb-1 custom-input-group">
                        <label class="form-label" for="name">{{ __('message.First Name') }}<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="name" placeholder="{{ __('message.First Name') }}" value="{{ ((isset($company) && isset($company->user->name)) ? $company->user->name : '')  }}">
                        <span class="invalid-feedback d-block" id="error_name" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-6 mb-1 custom-input-group">
                        <label class="form-label" for="last_name">{{ __('message.Last Name') }}<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="last_name" id="last_name" placeholder="{{ __('message.Last Name') }}" value="{{ ((isset($company) && isset($company->user->last_name)) ? $company->user->last_name : '')  }}">
                        <span class="invalid-feedback d-block" id="error_last_name" role="alert"></span>
                    </div>
                    @if(Auth::user()->roles[0]->name == 'Owner')
                    <div class="col-12 col-md-6 col-lg-6 mb-1 custom-input-group">
                        <label class="form-label" for="company_name">{{ __('message.Company Name') }}<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="company_name" id="company_name" placeholder="{{ __('message.Company Name') }}" value="{{ ((isset($company) && isset($company->user->company_name)) ? $company->user->company_name : '')  }}">
                        <span class="invalid-feedback d-block" id="error_company_name" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-6 mb-1 custom-input-group">
                        <label class="form-label" for="business_name">{{ __('message.Business Name') }}</span></label>
                        <input type="text" class="form-control" name="business_name" id="business_name" placeholder="{{ __('message.Business Name') }}" value="{{ ((isset($company) && isset($company->business_name)) ? $company->business_name : '')  }}">
                        <span class="invalid-feedback d-block" id="error_business_name" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-6 mb-1 custom-input-group">
                        <label class="form-label" for="indiamart_key">{{ __('message.IndiaMART Key') }}</span></label>
                        <input type="text" class="form-control" name="indiamart_key" id="indiamart_key" placeholder="{{ __('message.IndiaMART Key') }}" value="{{ ((isset($company) && isset($company->indiamart_key)) ? $company->indiamart_key : '')  }}">
                    </div>
                    <div class="col-12 col-md-6 col-lg-6 mb-1 custom-input-group">
                        <label class="form-label" for="justdial_key">{{ __('message.JustDial Key') }}</span></label>
                        <input type="text" class="form-control" name="justdial_key" id="justdial_key" placeholder="{{ __('message.JustDial Key') }}" value="{{ ((isset($company) && isset($company->justdial_key)) ? $company->justdial_key : '')  }}">
                    </div>
                    <div class="col-12 col-md-6 col-lg-6 mb-1 custom-input-group">
                        <label class="form-check-label mb-50" for="is_indiamart">{{ __('message.Is IndiaMART') }}</label>
                        <div class="form-check form-check-primary form-switch">
                            <input type="checkbox" class="form-check-input" value="1" name="is_indiamart" id="is_indiamart" {{ isset($company) && ($company->is_indiamart == 1) ? 'checked' : ''}}>
                        </div>
                    </div>
                    <div class="col-12 col-md-6 col-lg-6 mb-1 custom-input-group">
                        <label class="form-check-label mb-50" for="is_justdial">{{ __('message.Is JustDial') }}</label>
                        <div class="form-check form-check-primary form-switch">
                            <input type="checkbox" class="form-check-input" value="1" name="is_justdial" id="is_justdial" {{ isset($company) && ($company->is_justdial == 1) ? 'checked' : ''}}>
                        </div>
                    </div>
                    @endif
                    <div class="col-12 col-md-6 col-lg-3 mb-1 custom-input-group">
                        <label class="form-label" for="email ">{{ __('message.Email') }}</label>
                        <input type="email" class="form-control" name="email" id="email" placeholder="{{ __('message.Email') }}" value="{{ ((isset($company) && isset($company->user->email)) ? $company->user->email : '')  }}" disabled>
                        <span class="invalid-feedback d-block" id="error_email" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3 mb-1 custom-input-group">
                        <label class="form-label" for="mobile ">{{ __('message.Mobile') }}</label>
                        <input type="text" class="form-control" maxlength="10" name="mobile" id="mobile" placeholder="{{ __('message.Mobile No.') }}" value="{{ ((isset($company) && isset($company->user->mobile)) ? $company->user->mobile : '')  }}">
                        <span class="invalid-feedback d-block" id="error_mobile" role="alert"></span>
                    </div>

                    <div class="col-12 col-md-6 col-lg-6 mb-1 custom-input-group">
                        <label class="form-label" for="address">{{ __('message.Address') }}</label>
                        <input type="text" class="form-control" name="address" id="address" placeholder="{{ __('message.Address') }}" value="{{ ((isset($company) && isset($company->address)) ? $company->address : '')  }}">
                        <span class="invalid-feedback d-block" id="error_address" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-6 mb-1 custom-input-group">
                        <label class="form-label" for="state_id">{{ __('message.State') }}<span class="text-danger">*</span></label>
                        <select class="form-control" name="state_id" id="state_id">
                            <option selected disabled>{{ __('message.Select State') }}</option>
                            @foreach($state as $value)
                            <option value="{{$value->id}}" {{ (isset($company) && $company->state_id == $value->id ) ? 'selected' : '' }}>{{ $value->state_name}}</option>
                            @endforeach
                        </select>
                        <span class="invalid-feedback d-block" id="error_state_id" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-6 mb-1 custom-input-group">
                        <label class="form-label" for="city_id">{{ __('message.City') }}<span class="text-danger">*</span></label>
                        <select class="form-control" name="city_id" id="city_id">
                            <option selected disabled>{{ __('message.Select City') }}</option>
                            @foreach ($city as $item)
                            <option value="{{ $item->id }}" {{ (isset($company) && $company->city_id == $item->id ) ? 'selected' : '' }}>{{ $item->city_name}}</option>
                            @endforeach
                        </select>
                        <span class="invalid-feedback d-block" id="error_city_id" role="alert"></span>
                    </div>
                    <div class="col-md-12 form-group editor-space mb-1">
                        <label class="form-label" for="terms_conditions">{{ __('message.Terms & Conditions') }}</label>
                        <!-- <textarea id="terms_conditions" class="form-control summernote terms_conditions" name="terms_conditions" cols="100" rows="10">{!! ((isset($company) && isset($company->terms_conditions)) ? $company->terms_conditions : '')  !!}</textarea> -->
                        <textarea id="editor1" class="ckeditor" name="terms_conditions" cols="100" rows="10">{!! ((isset($company) && isset($company->terms_conditions)) ? $company->terms_conditions : '')  !!}</textarea>
                    </div>


                    {{-- @if((isset($message) && isset($message->id)))
                    <div data-repeater-list="invoice">
                        @foreach($message as $key => $value)
                        <div data-repeater-item>
                            <div class="row">
                                <input type="hidden" name="messagem_id" value="{{ $value->id }}">
                    <div class="col-12 col-md-6 col-lg-5 mb-1">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <select class="form-select product_select" name="title" data-key="0">
                            <option value="" selected disabled>{{ __('message.-- Select --') }}</option>
                            <option value="welcome">Welcome</option>
                            <option value="follow_up">Follow Up</option>
                            <option value="not_interested">Not Interested</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6 col-lg-5 mb-1">
                        <label class="form-label">Message <span class="text-danger">*</span></label>
                        <input type="text" min="1" class="form-control quantity" name="message" placeholder="Message" value="{{ $value->message }}">
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

                <div class="col-12 col-md-6 col-lg-5 mb-1">
                    <label class="form-label">Title <span class="text-danger">*</span></label>
                    <select class="form-select product_select" name="title" data-key="0">
                        <option value="" selected disabled>{{ __('message.-- Select --') }}</option>
                        <option value="welcome">Welcome</option>
                        <option value="follow_up">Follow Up</option>
                        <option value="not_interested">Not Interested</option>
                    </select>
                </div>

                <div class="col-12 col-md-6 col-lg-5 mb-1">
                    <label class="form-label">Message <span class="text-danger">*</span></label>
                    <input type="text" min="1" class="form-control quantity" name="message" placeholder="Message" value="{{ old('message') }}">
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
    <div class="col-md-7">
        <button class="btn btn-sm btn-icon btn-primary" type="button" data-repeater-create>
            <i class="fa fa-plus me-25"></i> <span>{{ __('message.Add New') }}</span>
        </button>
    </div> --}}

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
<script type="text/javascript">
    // $('.summernote').summernote({
    //     height: 200,
    //     minHeight: 200,
    //     maxHeight: 600,
    // });
    // $('.note-insert').remove();


    ClassicEditor
        .create(document.querySelector('#editor1'))
        .catch(error => {
            console.error(error);
        });
    // ClassicEditor
    //     .create(document.querySelector('#terms_conditions'))
    //     .catch(error => {
    //         console.error(error);
    //     });
    $(document).on('keypress', '#mobile', function() {
        if ($("#mobile").val().length > 9) {
            $("#mobile").attr('type', 'text');
        } else {
            $("#mobile").attr('type', 'number');
        }
    });



    $(document).on('click', '.save', function() {
        var text = $('.ck-content').children().html();
        // $("#terms_conditions").val(text);
        $("#editor1").val(text);
        var formData = new FormData($("#form")[0]);
        // var data = {
        //     "_token": $('#token').val()
        // };
        if ($("#name").val() != "" && $("#last_name").val() != "" && $("#company_name").val() != "" && $("#state_id").val() != null && $("#city_id").val() != null) {
            $.ajax({
                type: "POST",
                url: "{{route('profile.store')}}",
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                dataType: 'json',
                beforeSend: function() {
                    $(".save").html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> {{ __('message.Wait') }}`);
                    $(".save").attr('disabled', true);
                },
                success: function(response) {
                    console.log(response);
                    $(".save").html("{{ __('message.Submit') }}");
                    $(".save").attr('disabled', false);
                    if (response.server_error && response.status == false) {
                        toastr.error("{{ __('message.Something went wrong. Please try again.') }}", "{{ __('message.Error') }}");
                    } else if (response.code == "404" && response.status == false) {
                        toastr.warning("Source not found.", "{{ __('message.Warning') }}");
                    } else if (response.status != true && response.status != false) {
                        toastr.warning("{{ __('message.CRM key that you are using is incorrect.') }}", "{{ __('message.Warning') }}");
                        setTimeout(function() {
                            location.href = response.data;
                        }, 2000);
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
                    name: {
                        required: true,
                    },
                    last_name: {
                        required: true,
                    },
                    company_name: {
                        required: true,
                    },
                    state_id: {
                        required: true,
                    },
                    city_id: {
                        required: true,
                    }
                },
                messages: {
                    name: {
                        required: "{{ __('message.Enter first name') }}",
                    },
                    last_name: {
                        required: "{{ __('message.Enter last name') }}",
                    },
                    company_name: {
                        required: "{{ __('message.Enter company name') }}",
                    },

                    state_id: {
                        required: "{{ __('message.Select state') }}",
                    },
                    city_id: {
                        required: "{{ __('message.Select city') }}",
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

    $(document).ready(function() {
        var city_id = $("#city_id").val();
        if (city_id != null) {
            changeCity(0);
        }
        $(document).on('change', '#state_id', function() {
            changeCity();
        });
    });

    function changeCity(wherefrom = 1) {
        var state_id = $("#state_id").val();
        var route = "{{route('change-city')}}";
        $.ajax({
            type: 'get',
            url: route,
            datatype: 'json',
            data: {
                "state_id": state_id,
            },
            success: function(response) {
                if (wherefrom == 1) {
                    $("#city_id").empty('');
                    $("#city_id").append(`<option selected disabled>{{ __('message.Select City') }}</option>`);
                    $.each(response, function(i, value) {
                        $("#city_id").append('<option value="' + value.id + '">' + value.city_name + '</option>');
                    });
                }
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
</script>
@endsection