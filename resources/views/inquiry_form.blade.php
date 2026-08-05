@extends('layouts.guest')
@section('title', 'Inquiry')
@section('content')
    <div class="app-content">
        <div class="content-wrapper">
            <div class="content-body">
                <div class="auth-wrapper auth-basic px-2 px-lg-5 px-xl-5">

                    <div class="card mb-0 w-100 mx-lg-5 mx-xl-5" style="max-width: 800px;">
                        <div class="card-body">
                            <a href="javascript:void(0);" class="brand-logo">
                                <img class="img-fluid" src="{{ asset('img/logo.png') }}" alt="Logo" title="Logo">
                            </a>
                            <!-- <h4 class="card-title mb-1">Inquiry starts here 🚀</h4> -->
                            <!-- <p class="card-text mb-2">Make your app management easy and fun!</p> -->
                            <form class="auth-register-form mt-2" id="form" action="javascript:void(0)" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-12 col-sm-12 col-md-12 col-lg-6 pe-1">
                                        <div class="mb-1 custom-input-group">
                                            <label class="form-label" for="contact_number">Contact Number<span
                                                    class="text-danger">*</span></label>
                                            <input type="number" maxlength="10" name="contact_number" id="contact_number"
                                                class="form-control number" placeholder="Contact Number" required />
                                            <span class="invalid-feedback d-block" id="error_contact_number"
                                                role="alert"></span>
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-12 col-md-12 col-lg-6 ps-0">
                                        <div class="mb-1  custom-input-group">
                                            <label class="form-label" for="consumer_number">Consumer Number<span
                                                    class="text-danger">*</span></label>
                                            <select class="form-control form-select select2 custom-select2" required
                                                name="consumer_number" id="consumer_number">
                                                <option value="" selected>Select Any</option>
                                            </select>
                                            <span class="invalid-feedback d-block" id="error_consumer_number"
                                                role="alert"></span>
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                        <div class="mb-1 custom-input-group">
                                            <label class="form-label" for="consumer_name">Consumer Name<span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="consumer_name"
                                                id="consumer_name" placeholder="Consumer Name" required />
                                            <span class="invalid-feedback d-block" id="error_consumer_name"
                                                role="alert"></span>
                                        </div>
                                    </div>

                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                        <div class="mb-1 custom-input-group">
                                            <label class="form-label" for="image">Image</label>
                                            <input type="file" id="image" class="form-control" name="image"
                                                placeholder="Image" />
                                            <span class="invalid-feedback d-block" id="error_image" role="alert"></span>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                        <div class="mb-1 custom-input-group">
                                            <label class="form-label" for="problem">Problem<span
                                                    class="text-danger">*</span></label>
                                            <textarea class="form-control" name="problem" id="problem" rows="3" placeholder="..." required></textarea>
                                            <span class="invalid-feedback d-block" id="error_problem" role="alert"></span>
                                        </div>
                                    </div>
                                    {{-- <div class="col-12 col-sm-8 col-md-8 col-lg-8  mt-1 ">
                                        <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"
                                            data-size="light"></div>
                                    </div> --}}
                                    <div class="col-12 col-sm-4 col-md-4 mt-1 col-lg-4  mt-sm-3 mt-md-3 mt-lg-3 mt-xl-3">
                                        <button type="submit" class="btn btn-primary save float-end w-100">Submit</button>
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
        document.querySelectorAll('.number').forEach(function(input) {
            input.addEventListener('input', function(e) {
                this.value = this.value.replace(/\D/g, '').slice(0, 10);
                if (this.value.charAt(0) === '0') {
                    this.value = this.value.substring(1);
                }
            });
        });

        // document.getElementById("form").addEventListener("submit", function(event) {
        //     var response = grecaptcha.getResponse();
        //     if (response.length == 0) {
        //         alert("Please complete the reCAPTCHA");
        //         event.preventDefault();
        //     }
        // });
        $("#form").validate({
            rules: {
                consumer_name: {
                    required: true,
                },
                consumer_number: {
                    required: true,
                    regex: /^[0-9]*$/
                },
                contact_number: {
                    regex: /^[0-9]{10}$/,
                    required: true,
                    minlength: 10,
                },
                problem: {
                    required: true,
                }
            },
            messages: {
                consumer_name: {
                    required: "Enter Consumer Name"
                },
                consumer_number: {
                    required: "Enter Consumer Number",
                    regex: "Enter valid number"
                },
                contact_number: {
                    regex: "Enter valid number",
                    required: "Enter contact number",
                    minlength: "Enter at least 10 digits",
                },
                problem: {
                    required: "Enter Problem"
                }
            },
            errorElement: "small",
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
        $(document).on('click', '.save', function() {
            if ($("#form").valid()) {
                var formData = new FormData($("#form")[0]);
                $.ajax({
                    type: "POST",
                    url: "{{ route('save-inquiry') }}",
                    data: formData,
                    dataType: 'json',
                    cache: false,
                    contentType: false,
                    processData: false,
                    beforeSend: function() {
                        $("#error_name").html(' ');
                        $(".save").html(
                            `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> {{ __('message.Wait') }}`
                        );
                        $(".save").attr('disabled', true);
                    },
                    success: function(response) {
                        $(".save").html("{{ __('message.Submit') }}");
                        $(".save").attr('disabled', false);
                        if (response.status_code == 500) {
                            toastr.error(response.message, "{{ __('message.Error') }}");
                        } else if (response.status_code == 403) {
                            toastr.warning(response.message, "{{ __('message.Warning') }}");
                        } else if (response.status_code == 201) {
                            $.each(response.errors, function(key, value) {
                                $('#error_' + key).html('<p class="text-danger mb-0">' + value +
                                    '</p>');
                            });
                            toastr.warning(response.message, "{{ __('message.Warning') }}");
                        } else {
                            $('#form')[0].reset();
                            toastr.success(response.message, "{{ __('message.Success') }}");
                            setTimeout(function() {
                                location.href = response.data;
                            }, 100);
                        }
                    }
                });
            } else {
                return false;
            }
        });

        $(document).on('blur', '#contact_number', function() {
            const contact_number = $(this).val().trim();
            const phoneRegex = /^[0-9]{10}$/;
            if (phoneRegex.test(contact_number)) {
                var url = "{{ route('get-consumer-using-mobile') }}";
                $.ajax({
                    url: url,
                    type: 'post',
                    data: {
                        "contact_number": contact_number,
                        "_token": "{{ csrf_token() }}",
                    },
                    dataType: 'json',
                    success: function(response) {
                        $('#consumer_number').html('');
                        if (response.status == true) {
                            $('#consumer_name').val(response.message[0].consumer_name);
                            $.each(response.message, function(key, value) {
                                $('#consumer_number').html('<option value="' + value
                                    .consumer_number + '" data-name="' + value
                                    .consumer_name + '">' + value.consumer_number + '</p>');
                            });
                        } else {
                            $('#consumer_number').html('<option value="" selected>Select Any</option>');
                            $('#consumer_name').val('');
                        }
                    }
                });
            }
        });
    </script>
@endsection
