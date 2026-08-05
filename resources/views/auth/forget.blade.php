@extends('layouts.guest')
@section('title', 'Forget Password')
@section('content')
<div class="content-wrapper">
    <div class="content-header row">
    </div>
    <div class="content-body">
        <div class="auth-wrapper auth-basic px-2">
            <div class="auth-inner my-2">
                <div class="card mb-0">
                    <div class="card-body">
                        <a class="" href="#">
                            <span class="brand-logo m-0">
                                <img src="{{asset('img/logo.png')}}">
                            </span>
                        </a>
                        <h4 class="card-title my-1">Forget Password? 🔒</h4>
                        <p class="card-text mb-1">Enter your email and we'll send you instructions to reset your password</p>
                        <form id="form" class="form form-horizontal" action="javascript:void(0);" method="post">
                            @csrf
                            <div class="row">
                                <div class="col-12 mb-1">
                                    <div class="custom-input-group">
                                        <div class="input-group input-group-merge ">
                                            <span class="input-group-text">
                                                <span class="fa fa-envelope"></span>
                                            </span>
                                            <input type="email" class="form-control" name="email" id="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="Email Address" />
                                        </div>
                                        <span class="invalid-feedback d-block" id="error_email" role="alert"></span>
                                    </div>
                                </div>
                                <div class="col-12 mb-1">
                                    <button type="submit" class="btn btn-primary w-100 save" tabindex="4">Send OTP</button>
                                </div>
                                <div class="col-12 text-center">
                                    <a href="{{route('login')}}" class="d-flex align-items-center justify-content-center">
                                        <i class="fa fa-chevron-left "></i>
                                        &nbsp; Back to login
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('pagescript')
<script type="text/javascript">
    $(document).on('click', '.save', function() {
        var formData = new FormData($("#form")[0]);
        if ($("#email").val() != "") {
            $.ajax({
                type: "POST",
                url: "{{route('forget-now')}}",
                data: formData,
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $("#error_email").html(' ');
                    $(".save").html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> {{ __('message.Wait') }}`);
                    $(".save").attr('disabled', true);
                },
                success: function(response) {
                    $(".save").html("Send OTP");
                    $(".save").attr('disabled', false);
                    if (response.server_error && response.status == false) {
                        toastr.error("{{ __('message.Something went wrong. Please try again.') }}", "{{ __('message.Error') }}");
                    } else if (response.status == false) {
                        toastr.warning("Credential record do not match our records please correct email/mobile input.", "{{ __('message.Warning') }}");
                    } else if (response.errors && response.status == false) {
                        $.each(response.errors, function(key, value) {
                            $('#error_' + key).html('<p class="text-danger mb-0">' + value + '</p>');
                        });
                        toastr.warning("{{ __('message.Please input proper data.') }}", "{{ __('message.Warning') }}");
                    } else {
                        $('#form')[0].reset();
                        toastr.success("OTP send via given email.", "Success");
                        setTimeout(function() {
                            location.href = response.data;
                        }, 2000);
                    }
                }
            });
        } else {
            $("#form").validate({
                rules: {
                    email: {
                        required: true,
                    },
                },
                messages: {
                    email: {
                        required: "{{ __('message.Enter email') }}"
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
</script>
@endsection