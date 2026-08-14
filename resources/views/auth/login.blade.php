@extends('layouts.guest')
@section('title', env("APP_NAME"))
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

                        <h4 class="card-title my-1">Welcome to {{ env("APP_NAME") }} 👋</h4>
                        <p class="card-text mb-1">Please sign-in to your account </p>
                        <!-- <p class="card-text mb-1">Customer Management Software and Mobile Application. Task Management Software and Mobile Application</p> -->
                        <form class="form form-horizontal" action="{{route('login')}}" method="post">
                            @csrf
                            <div class="row">
                                <div class="col-12 mb-75">
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text">
                                            <span class="fa fa-envelope"></span>
                                        </span>
                                        <input type="text" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="Email Address" />
                                        @error('email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12 mb-1">
                                    <div class="input-group input-group-merge">
                                        <span id="basic-default-password" class="input-group-text cursor-pointer toggle-password">
                                            <i class="fa fa-lock"></i>
                                        </span>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="Password" />
                                        @error('password')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-12 text-end mb-25">
                                    {{-- <small><a href="{{route('forget')}}">Forget Password?</a></small> --}}
                                </div>
                                <div class="col-12 mb-1">
                                    <button type="submit" class="btn btn-primary w-100" tabindex="4">Sign in</button>
                                </div>
                                <div class="col-12 text-center">
                                    <!-- <a href="{{route('register')}}">You don't have an account?</a> -->
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
</script>
@endsection