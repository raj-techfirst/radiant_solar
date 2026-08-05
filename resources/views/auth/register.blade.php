@extends('layouts.guest')
@section('title', 'Customer Management Software and Mobile Application. Task Management Software and Mobile Application')
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
                        <!-- <h4 class="card-title my-1">Adventure starts here 🚀</h4> -->
                        <p class="card-text my-1 ">Welcome! We're delighted you're here. Please complete the form below to get started.</p>
                        <form class="form form-horizontal register-form" action="{{route('register')}}" method="post">
                            @csrf
                            <div class="row">
                                <div class="col-sm-12 mb-1">
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text">
                                            <span class="fa fa-user"></span>
                                        </span>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" placeholder="First Name" />
                                        @error('name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-sm-12 mb-1">
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text">
                                            <span class="fa fa-user"></span>
                                        </span>
                                        <input type="text" class="form-control @error('last_name') is-invalid @enderror" name="last_name" value="{{ old('last_name') }}" placeholder="Last Name" />
                                        @error('last_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-sm-12 mb-1">
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text">
                                            <span class="fa fa-industry"></span>
                                        </span>
                                        <input type="text" class="form-control @error('company_name') is-invalid @enderror" name="company_name" value="{{ old('company_name') }}" placeholder="Company Name">
                                    </div>
                                </div>
                                <div class="col-sm-12  mb-1">
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text">
                                            <span class="fa fa-phone"></span>
                                        </span>
                                        <input type="number" maxlength="10" class="form-control @error('mobile') is-invalid @enderror" id="mobile" name="mobile" value="{{ old('mobile') }}" autocomplete="mobile" placeholder="Mobile Number" />
                                        @error('mobile')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-sm-12  mb-1">
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text">
                                            <span class="fa fa-envelope"></span>
                                        </span>
                                        <input type="text" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" autocomplete="email" placeholder="Email Address" />

                                        @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-sm-12  mb-1">
                                    <div class="input-group input-group-merge">
                                        <span id="basic-default-password" class="input-group-text cursor-pointer toggle-password">
                                            <i class="fa fa-lock"></i>
                                        </span>
                                        <input type="password" id="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="Password" aria-describedby="basic-default-password">
                                        @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-sm-12  mb-1">
                                    <div class="input-group input-group-merge">
                                        <span id="basic-default-password" class="input-group-text cursor-pointer toggle-password">
                                            <i class="fa fa-lock"></i>
                                        </span>
                                        <input type="password" name="confirm_password" id="confirm_password" class="form-control " placeholder="Confirm Password">
                                    </div>
                                </div>

                                <div class="col-12 mb-1">
                                    <button type="submit" class="btn btn-primary w-100 sign-up" tabindex="4">Sign up</button>
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
    $('.toggle-password').click(function() {
        $(this).children().toggleClass('fa fa-lock fa fa-unlock');
        let input = $(this).next();
        input.attr('type', input.attr('type') === 'password' ? 'text' : 'password');
    });
    $(document).on('click', '.sign-up', function() {
        $(this).attr('disabled', true);
        $(this).html('Wait a moment...');
        $(".register-form").submit();

    });
    $(document).on('keypress', '#mobile', function() {
        if ($("#mobile").val().length > 9) {
            $("#mobile").attr('type', 'text');
        } else {
            $("#mobile").attr('type', 'number');
        }
    });
</script>
@endsection