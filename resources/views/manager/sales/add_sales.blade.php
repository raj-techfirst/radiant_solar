@extends('layouts.app')
@section('title', 'Sales Officer')
@section('content')
<div class="row">
    <div class="col-12">
        @if((isset($company) && isset($company->id)))
        <h4 class="card-title mb-1">{{ __('message.Edit Sales Officer') }}</h4>
        @else
        <h4 class="card-title mb-1">{{ __('message.Add Sales Officer') }}</small></h4>
        @endif
    </div>
    <div class="col-12">
        <div class="card p-1">
            <form id="form" action="javascript:void(0)" method="POST">
                @csrf
                <div class="row">
                    @if((isset($company) && isset($company->id)))
                    <input type="hidden" id="sales_id" name="sales_id" value="{{ $company->id }}">
                    @endif
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
                    <div class="col-12 col-md-6 col-lg-6 mb-1 custom-input-group">
                        <label class="form-label" for="mobile ">{{ __('message.Mobile') }}</label>
                        <input type="text" maxlength="10" class="form-control" name="mobile" id="mobile" placeholder="{{ __('message.Mobile No.') }}" value="{{ ((isset($company) && isset($company->user->mobile)) ? $company->user->mobile : '')  }}">
                        <span class="invalid-feedback d-block" id="error_mobile" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-6 mb-1 custom-input-group">
                        <label class="form-label" for="email">{{ __('message.Email') }}<span class="text-danger">*</span></label>
                        <input type="email" class="form-control" name="email" id="email" placeholder="{{ __('message.Email Address') }}" value="{{ ((isset($company) && isset($company->user->email)) ? $company->user->email : '')  }}" @if((isset($company) && isset($company->id))) disabled @else : @endif>
                        <span class="invalid-feedback d-block" id="error_email" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-6 mb-1 custom-input-group">
                        <label class="form-label" for="password">{{ __('message.Password') }} @if(isset($company))<span class="text-danger"></span> @else <span class="text-danger">*</span>@endif</label>
                        <div class="input-group input-group-merge">
                            <span id="basic-default-password" class="input-group-text cursor-pointer toggle-password">
                                <i class="fa fa-lock"></i>
                            </span>
                            <input type="password" class="form-control" name="password" id="password" placeholder="{{ __('message.Password') }}" value="">
                        </div>
                        <span class="invalid-feedback d-block" id="error_password" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-6 mb-1 custom-input-group">
                        <label class="form-label" for="confirm_password">{{ __('message.Confirm Password') }} @if(isset($company))<span class="text-danger"></span> @else <span class="text-danger">*</span>@endif</label>
                        <div class="input-group input-group-merge">
                            <span id="basic-default-password" class="input-group-text cursor-pointer toggle-password">
                                <i class="fa fa-lock"></i>
                            </span>
                            <input type="password" class="form-control" name="confirm_password" id="confirm_password" placeholder="{{ __('message.Confirm Password') }}" value="">
                        </div>
                        <span class="invalid-feedback d-block" id="error_confirm_password" role="alert"></span>
                    </div>

                    <div class="col-12 col-md-6 col-lg-6 mb-1 custom-input-group">
                        <label class="form-label" for="state_id">{{ __('message.State') }}<span class="text-danger">*</span></label>
                        <select class="form-control" name="state_id" id="state_id">
                            <option selected disabled>--{{ __('message.Select State') }}--</option>
                            @foreach($state as $value)
                            <option value="{{$value->id}}" {{ (isset($company) && $company->state_id == $value->id ) ? 'selected' : '' }}>{{ $value->state_name}}</option>
                            @endforeach
                        </select>
                        <span class="invalid-feedback d-block" id="error_state_id" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-6 col-lg-6 mb-1 custom-input-group">
                        <label class="form-label" for="city_id">{{ __('message.City') }}<span class="text-danger">*</span></label>
                        <select class="form-control" name="city_id" id="city_id">
                            <option selected disabled>--{{ __('message.Select City') }}--</option>
                            @foreach ($city as $item)
                            <option value="{{ $item->id }}" {{ (isset($company) && $company->city_id == $item->id ) ? 'selected' : '' }}>{{ $item->city_name}}</option>
                            @endforeach
                        </select>
                        <span class="invalid-feedback d-block" id="error_city_id" role="alert"></span>
                    </div>
                    <div class="col-12 col-lg-8 mb-1 custom-input-group">
                        <label class="form-label" for="address">{{ __('message.Address') }}</label>
                        <input type="text" class="form-control" name="address" id="address" placeholder="{{ __('message.Address') }}" value="{{ ((isset($company) && isset($company->address)) ? $company->address : '')  }}">
                        <span class="invalid-feedback d-block" id="error_address" role="alert"></span>
                    </div>

                    <div class="col-md-12 mt-2">
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
    $('.toggle-password').click(function() {
        $(this).children().toggleClass('fa fa-lock fa fa-unlock');
        let input = $(this).next();
        input.attr('type', input.attr('type') === 'password' ? 'text' : 'password');
    });

    $(document).ready(function() {
        $(document).on('keypress', '#mobile', function() {
            if ($("#mobile").val().length > 9) {
                $("#mobile").attr('type', 'text');
            } else {
                $("#mobile").attr('type', 'number');
            }
        });
    });
    $(document).on('click', '.save', function() {
        var formData = new FormData($("#form")[0]);
        if ($("#sales_id").val()) {
            var temp = ($("#name").val() != "" && $("#last_name").val() != "" && $("#state_id").val() != null && $("#city_id").val() != null);
        } else {
            var temp = ($("#name").val() != "" && $("#last_name").val() != "" && $("#email").val() != "" && $("#password").val() != "" && $("#confirm_password").val() != null && $("#state_id").val() != null && $("#city_id").val() != null);
        }
        if (temp) {
            $.ajax({
                type: "POST",
                url: "{{route('sales.store')}}",
                data: formData,
                dataType: 'json',
                cache: false,
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
                    } else if (response.status == false && response.label) {
                        if (response.label == 'manager') {
                            toastr.warning("{{ __('message.Your manger limit`s has been ended.') }}", "{{ __('message.Warning') }}");
                        } else {
                            toastr.warning("{{ __('message.Your sales limit`s has been ended.') }}", "{{ __('message.Warning') }}");
                        }
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
                    password: {
                        required: true,
                    },
                    confirm_password: {
                        required: true,
                    },
                    state_id: {
                        required: true,
                    },
                    city_id: {
                        required: true,
                    },
                    email: {
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
                    email: {
                        required: "{{ __('message.Enter email') }}",
                    },
                    password: {
                        required: "{{ __('message.Enter password') }}",
                    },
                    confirm_password: {
                        required: "{{ __('message.Enter confirm password') }}",
                    },
                    state_id: {
                        required: "{{ __('message.Select state') }}",
                    },
                    city_id: {
                        required: "{{ __('message.Select city') }}",
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
                    $("#city_id").append(`<option selected disabled>--{{ __('message.Select City') }}--</option>`);
                    $.each(response, function(i, value) {
                        $("#city_id").append('<option value="' + value.id + '">' + value.city_name + '</option>');
                    });
                }
            }
        });
    }
</script>
@endsection