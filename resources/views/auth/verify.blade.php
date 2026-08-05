@extends('layouts.guest')
@section('title', 'Verify')
@section('content')
<div class="content-wrapper">
    <div class="content-header row">
    </div>
    <div class="content-body">
        <div class="auth-wrapper auth-basic px-2">
            <div class="auth-inner my-2">
                <!-- Login basic -->
                <div class="card mb-0">
                    <div class="card-body">
                        <a class="" href="#">
                            <span class="brand-logo m-0">
                                <img src="{{asset('img/logo.png')}}">
                            </span>
                        </a>

                        <h4 class="card-title my-1">Verify your email 👋</h4>
                        <p class="card-text mb-1">Account activation OTP sent to your email address: {{ session()->get('forget_email') }} Please follow the OTP inside to continue.</p>
                        <form id="form" class="form form-horizontal" action="javascript:void(0);" method="post">
                            @csrf
                            <div class="row">
                                <div class="col-12 mb-1">
                                    <div class="custom-input-group">
                                        <div class="input-group input-group-merge ">
                                            <span class="input-group-text">
                                                <span class="fa fa-key"></span>
                                            </span>
                                            <input id="otp" type="text" class="form-control" name="otp" placeholder="OTP Verify">
                                        </div>
                                        <span class="invalid-feedback d-block" id="error_otp" role="alert"></span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary w-100 save" tabindex="4">Verify</button>
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
        if ($("#otp").val() != "") {
            $.ajax({
                type: "POST",
                url: "{{route('otp-verify')}}",
                data: formData,
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $("#error_otp").html(' ');
                    $(".save").html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> {{ __('message.Wait') }}`);
                    $(".save").attr('disabled', true);
                },
                success: function(response) {
                    $(".save").html("Verify");
                    $(".save").attr('disabled', false);
                    if (response.server_error && response.status == false) {
                        toastr.error("{{ __('message.Something went wrong. Please try again.') }}", "{{ __('message.Error') }}");
                    } else if (response.status == false && response.errors) {
                        toastr.warning("Please enter correct OTP.", "{{ __('message.Warning') }}");
                    } else if (response.status == false) {
                        toastr.warning("Entered OTP does not match.", "{{ __('message.Warning') }}");
                    } else {
                        $('#form')[0].reset();
                        toastr.success("OTP verified successfully.", "{{ __('message.Success') }}");
                        setTimeout(function() {
                            location.href = response.data;
                        }, 2000);
                    }
                }
            });
        } else {
            $("#form").validate({
                rules: {
                    otp: {
                        required: true,
                    },
                },
                messages: {
                    otp: {
                        required: "Enter OTP"
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