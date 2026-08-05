@extends('layouts.guest')
@section('title', 'Customer Management Software and Mobile Application. Task Management Software and Mobile Application')
@section('content')
<style>
    .otp-container {
        display: flex;
        justify-content: center;
        align-items: center;
        /* min-height: 10vh; */
    }

    .input {
        width: 40px;
        border: none;
        border-bottom: 3px solid rgba(0, 0, 0, 0.5);
        margin: 0 10px;
        text-align: center;
        font-size: 36px;
        cursor: not-allowed;
        pointer-events: none;
    }

    .input:focus {
        border-bottom: 3px solid #5fcafb;
        outline: none;
    }

    .input:nth-child(1) {
        cursor: pointer;
        pointer-events: all;
    }
</style>

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
                        <h4 class="card-title my-1 text-center">Verify Your Account</h4>
                        <p class="card-text text-center">Enter OTP Code sent to <br /> {{ $email }}</p>
                        <form class="form form-horizontal otp-form" action="{{route('otp')}}" method="post">
                            @csrf
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="otp-container">
                                        <div id="inputs" class="inputs">
                                            <input class="input" type="text" autocomplete="off" name="otp_1" id="otp_1" inputmode="numeric" maxlength="1" />
                                            <input class="input" type="text" autocomplete="off" name="otp_2" id="otp_2" inputmode="numeric" maxlength="1" />
                                            <input class="input" type="text" autocomplete="off" name="otp_3" id="otp_3" inputmode="numeric" maxlength="1" />
                                            <input class="input" type="text" autocomplete="off" name="otp_4" id="otp_4" inputmode="numeric" maxlength="1" />
                                            <input class="input" type="text" autocomplete="off" name="otp_5" id="otp_5" inputmode="numeric" maxlength="1" />
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-12 my-2 text-center">
                                    <input class="hidden" type="text" autocomplete="off" name="otp" id="otp" inputmode="numeric" maxlength="5" />
                                    @error('otp')
                                    <span class="text-danger error py-2" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror

                                    @error('resendotp')
                                    <span class="text-success py-2" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-12 mb-2">
                                <button type="button" class="btn btn-primary w-100 verify">VERIFY</button>
                            </div>
                            <div class="col-12 text-center resend-container">
                                <a class="d-flex align-items-center justify-content-center timer">
                                    Resend OTP in &nbsp; <span id="timer" class="text-info"></span>
                                </a>
                                <a class="d-flex align-items-center justify-content-center resend-msg d-none">
                                    If you didn't receive a code !!
                                </a>
                                <a href="{{route('resend-otp')}}" class="d-flex align-items-center resend d-none justify-content-center">
                                    RESEND
                                </a>
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
    let timerOn = true;

    function timer(remaining) {
        var m = Math.floor(remaining / 60);
        var s = remaining % 60;

        m = m < 10 ? '0' + m : m;
        s = s < 10 ? '0' + s : s;
        document.getElementById('timer').innerHTML = m + ':' + s;
        remaining -= 1;

        if (remaining >= 0 && timerOn) {
            setTimeout(function() {
                timer(remaining);
            }, 1000);
            return;
        }

        if (!timerOn) {
            // Do validate stuff here
            return;
        }

        // Do timeout stuff here
        $('.timer').remove();
        $('.resend').removeClass('d-none');
        $('.resend-msg').removeClass('d-none');
    }

    timer(60);

    $('.toggle-password').click(function() {
        $(this).children().toggleClass('fa fa-lock fa fa-unlock');
        let input = $(this).next();
        input.attr('type', input.attr('type') === 'password' ? 'text' : 'password');
    });

    $(document).on('click', '.resend', function() {
        $('.resend-container').html('<a class="d-flex align-items-center justify-content-center">Wait a moment...</a>');
    });
    $(document).on('click', '.verify', function() {
        $(this).attr('disabled', true);
        $(this).html('Wait a moment...');
        var otp = $("#otp_1").val() + $("#otp_2").val() + $("#otp_3").val() + $("#otp_4").val() + $("#otp_5").val();
        $("#otp").val(otp);
        $('.otp-form').submit();
    });
    $(document).on('keypress', '#mobile', function() {
        if ($("#mobile").val().length > 9) {
            $("#mobile").attr('type', 'text');
        } else {
            $("#mobile").attr('type', 'number');
        }
    });

    const inputs = document.getElementById("inputs");

    inputs.addEventListener("input", function(e) {
        const target = e.target;
        const val = target.value;

        if (isNaN(val)) {
            target.value = "";
            return;
        }

        if (val != "") {
            const next = target.nextElementSibling;
            if (next) {
                next.focus();
            }
        }
    });

    inputs.addEventListener("keyup", function(e) {
        const target = e.target;
        const key = e.key.toLowerCase();

        if (key == "backspace" || key == "delete") {
            target.value = "";
            const prev = target.previousElementSibling;
            if (prev) {
                prev.focus();
            }
            return;
        }
    });
</script>
@endsection