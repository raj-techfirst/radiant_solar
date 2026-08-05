<!DOCTYPE html>
<html class="loading" lang="en" data-textdirection="ltr">

<head>

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="">
    <meta name="description" content="">
    <meta name="keywords" content="{{ env('APP_NAME') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="author" content="TechFirst">
    <title>@yield('title')</title>
    <link rel="shortcut icon" href="{{asset('img/fav.png')}}" />
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;1,400;1,500;1,600" rel="stylesheet">

    <!-- start css -->
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/css/fontawesome.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/vendors/css/vendors.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/css/core/select2.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/vendors/css/tables/datatable/dataTables.bootstrap5.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/vendors/css/tables/datatable/responsive.bootstrap5.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/vendors/css/tables/datatable/buttons.bootstrap5.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/vendors/css/tables/datatable/rowGroup.bootstrap5.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/vendors/css/extensions/toastr.min.css')}}">

    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/vendors/css/pickers/pickadate/pickadate.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/vendors/css/pickers/flatpickr/flatpickr.min.css')}}">

    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/css/bootstrap.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('plugins/summernote/summernote-bs4.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/css/bootstrap-extended.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/css/colors.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/css/components.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/css/core/menu/menu-types/vertical-menu.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/css/plugins/extensions/ext-component-toastr.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/css/plugins/extensions/ext-component-sweet-alerts.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/vendors/css/extensions/sweetalert2.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/css/custom.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('plugins/jquery-ui/css/jquery-ui.min.css')}}" />
    <link rel="stylesheet" type="text/css" href="{{asset('plugins/fancybox/jquery.fancybox.css')}}">

    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/vendors/css/charts/chart-apex.min.css')}}">

    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/vendors/css/forms/wizard/bs-stepper.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/css/plugins/forms/form-wizard.min.css')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('app-assets/css/style.css?abc=65')}}">

    @yield('css')

    <!-- end css -->
</head>
<!-- end head -->

<!-- start body -->

<body class="vertical-layout vertical-menu-modern navbar-floating footer-static" data-open="click" data-menu="vertical-menu-modern" data-col="">
  
@yield('content')

  

    <!-- start script -->
    <script src="{{asset('app-assets/vendors/js/vendors.min.js')}}"></script>
    <script src="{{asset('plugins/jquery-ui/js/jquery.validate.min.js')}}"></script>
    <script src="{{asset('plugins/jquery-ui/js/jquery-ui.min.js')}}"></script>
    <script src="{{asset('plugins/jquery-ui/js/ckeditor.js')}}"></script>
    <script src="{{asset('app-assets/js/form/select2.full.min.js')}}"></script>
    <script src="{{asset('plugins/fancybox/jquery.fancybox.min.js')}}"></script>
    <script src="{{asset('app-assets/js/form/form-select2.js')}}"></script>

    <script src="{{asset('app-assets/vendors/js/pickers/pickadate/picker.js')}}"></script>
    <script src="{{asset('app-assets/vendors/js/pickers/pickadate/picker.date.js')}}"></script>
    <script src="{{asset('app-assets/vendors/js/pickers/pickadate/picker.time.js')}}"></script>
    <script src="{{asset('app-assets/vendors/js/pickers/pickadate/legacy.js')}}"></script>
    <script src="{{asset('app-assets/vendors/js/pickers/flatpickr/flatpickr.min.js')}}"></script>

    <!-- <script src="{{asset('app-assets/vendors/js/jquery.repeater.min.js')}}"></script> -->
    <script src="{{asset('app-assets/vendors/js/extensions/sweetalert2.all.min.js')}}"></script>
    <script src="{{asset('app-assets/vendors/js/tables/datatable/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('app-assets/vendors/js/tables/datatable/dataTables.bootstrap5.min.js')}}"></script>
    <script src="{{asset('app-assets/vendors/js/tables/datatable/dataTables.responsive.min.js')}}"></script>
    <script src="{{asset('app-assets/vendors/js/tables/datatable/responsive.bootstrap5.min.js')}}"></script>
    <script src="{{asset('app-assets/js/scripts/extensions/ext-component-sweet-alerts.js')}}"></script>
    <script src="{{asset('app-assets/vendors/js/extensions/toastr.min.js')}}"></script>
    <script src="{{asset('app-assets/js/scripts/extensions/ext-component-toastr.js')}}"></script>
    <script src="{{asset('plugins/summernote/summernote-bs4.min.js')}}"></script>

    <script src="{{asset('app-assets/vendors/js/forms/repeater/jquery.repeater.min.js')}}"></script>
    <script src="{{asset('app-assets/js/scripts/forms/form-repeater.js')}}"></script>
    <script src="{{asset('app-assets/vendors/js/charts/apexcharts.min.js')}}"></script>

    <script src="{{asset('app-assets/js/core/app-menu.js')}}"></script>
    <script src="{{asset('app-assets/js/core/app.js')}}"></script>
    <script src="{{asset('js/app.js')}}"></script>
    <script src="{{asset('app-assets/js/form/bs-stepper.js')}}"></script>
    <script src="{{asset('app-assets/js/form/form-wizard-icons.js')}}"></script>
    @yield('script')
    <script>
        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
            $('.flatpickr-date-time').flatpickr({
                enableTime: true
            });
        });

        $('.summernote').summernote({
            height: 200,
            minHeight: 200,
            maxHeight: 600,
        });

        //change language
        var url = "{{route('language')}}";
        $(".lang-change").click(function() {
            window.location.href = url + "?lang=" + $(this).data('value');
        });
        var url = "{{route('soft')}}";
        $(".soft-change").click(function() {
            window.location.href = url + "?soft=" + $(this).data('value');
        });
        $(".year-change").click(function() {
            window.location.href = "{{route('years')}}" + "?year=" + $(this).data('value');
        });
        $("#changeModal").on("hidden.bs.modal", function(e) {
            $(this).find('form_password').trigger('reset');
            $(".custom-error").html("");
            $(".invalid-feedback").html("");
        });
        $('.toggle-password').click(function() {
            $(this).children().toggleClass('fa fa-eye fa fa-eye-slash');
        });
        $(document).on('click', '.change-password', function() {
            var formData = new FormData($("#password_form")[0]);
            if ($("#current_password").val() != "" && $("#password").val() != "" && $("#confirm_password").val() != "") {
                $.ajax({
                    type: "POST",
                    url: "{{route('update-password')}}",
                    data: formData,
                    dataType: 'json',
                    cache: false,
                    contentType: false,
                    processData: false,
                    beforeSend: function() {
                        $("#error_confirm_password").html(' ');
                        $(".change-password").html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> {{ __('message.Wait') }}`);
                        $(".change-password").attr('disabled', true);
                    },
                    success: function(response) {
                        $(".change-password").html("{{ __('message.Submit') }}");
                        $(".change-password").attr('disabled', false);
                        if (response.server_error && response.status == false) {
                            toastr.error("{{ __('message.Something went wrong. Please try again.') }}", "{{ __('message.Error') }}");
                        } else if (response.status == false && response.label) {
                            toastr.warning("{{ __('message.Current password does not match.') }}", "{{ __('message.Warning') }}");
                        } else if (response.status == false) {
                            $.each(response.errors, function(key, value) {
                                $('#error_' + key).html('<p class="text-danger mb-0">' + value + '</p>');
                            });
                            toastr.warning("{{ __('message.Please input proper data.') }}", "{{ __('message.Warning') }}");
                        } else {
                            $('#form')[0].reset();
                            toastr.success("{{ __('message.Password updated successfully.') }}", "{{ __('message.Success') }}");
                            location.reload(true);
                        }
                    }
                });
            } else {
                $("#password_form").validate({
                    rules: {
                        current_password: {
                            required: true,
                        },
                        password: {
                            required: true,
                        },
                        confirm_password: {
                            required: true,
                        },
                    },
                    messages: {
                        current_password: {
                            required: "{{ __('message.Enter current password') }}",
                        },
                        password: {
                            required: "{{ __('message.Enter new password') }}",
                        },
                        confirm_password: {
                            required: "{{ __('message.Enter confirm password') }}",
                        },
                    },
                    errorElement: "p",
                    errorClass: "text-danger mb-0 custom-error",

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
            var soft = "{{session()->get('soft')}}";
            if (soft == "") {
                $("#first_crm").trigger('click');
            }
        })
    </script>
    @yield('pagescript')
    <!-- end script -->
</body>
<!-- end body -->

</html>