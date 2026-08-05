@extends('layouts.app')
@section('title', 'Bank')
@section('content')
<div class="row">
    <div class="col-12">
        @if((isset($bank) && isset($bank->id)))
        <h4 class="card-title mb-1">Edit Bank</h4>
        @else
        <h4 class="card-title mb-1">Add Bank</h4>
        @endif
    </div>
    <div class="col-12">
        <div class="card p-1">
            <form id="form" class="form invoice-repeater" action="javascript:void(0)" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="row">
                        <input type="hidden" name="bank_id" value="@if((isset($bank) && isset($bank->id))) {{ $bank->id }} @endif">
                        <div class="col-12 col-md-6 col-lg-4 mb-1">
                            <label class="form-label">Bank Holder Name<span class="text-danger">*</span></label>
                            <input type="text" class="form-control " name="holder_name" id="holder_name" placeholder="Bank Holder Name" value="{{ ((isset($bank) && isset($bank->holder_name)) ? $bank->holder_name : '')  }}">
                            <span class="invalid-feedback d-block" id="error_holder_name" role="alert"></span>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4 col-lg-4 mb-1">
                            <label class="form-label">Bank Name<span class="text-danger">*</span> </label>
                            <input type="text" class="form-control " name="name" id="name" placeholder="Bank Name" value="{{ ((isset($bank) && isset($bank->name)) ? $bank->name : '')  }}">
                            <span class="invalid-feedback d-block" id="error_name" role="alert"></span>

                        </div>
                        <div class="col-12 col-sm-6 col-md-4 col-lg-4 mb-1">
                            <label class="form-label">Account Number<span class="text-danger">*</span> </label>
                            <input type="text" class="form-control " name="accout_number" id="accout_number" placeholder="Bank Account Number" value="{{ ((isset($bank) && isset($bank->account_number)) ? $bank->account_number : '')  }}">
                            <span class="invalid-feedback d-block" id="error_accout_number" role="alert"></span>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4 col-lg-4 mb-1">
                            <label class="form-label">IFSC Code<span class="text-danger">*</span> </label>
                            <input type="text" class="form-control" name="ifsc_number" id="ifsc_number" placeholder="Bank IFSC Code" value="{{ ((isset($bank) && isset($bank->ifsc_number)) ? $bank->ifsc_number : '')  }}">
                            <span class="invalid-feedback d-block" id="error_ifsc_number" role="alert"></span>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4 col-lg-4 mb-1">
                            <label class="form-label">Bank Branch<span class="text-danger">*</span> </label>
                            <input type="text" class="form-control " name="branch" id="branch" placeholder="Bank Branch Name" value="{{ ((isset($bank) && isset($bank->branch)) ? $bank->branch : '')  }}">
                            <span class="invalid-feedback d-block" id="error_branch" role="alert"></span>
                        </div>
                        <div class="col-12 col-sm-6 col-md-4 col-lg-4 mb-1 pt-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="default" value="1" {{ ((isset($bank) && isset($bank->default) && $bank->default == 1)  ? 'checked' : '')  }} id="default">
                                <label class="form-check-label" for="default"> Default Bank </label>
                            </div>
                        </div>
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
    $(function() {
        'use strict';
        $(document).on('click', '.remove-item', function() {
            if (($('.remove-item').length) > 1) {
                $(this).parent().parent().parent().remove();
            } else {
                Swal.fire({
                    text: "Cannot remove first item",
                    icon: 'warning',
                    confirmButtonText: 'OK',
                });
            }
        });
    });

    $(document).ready(function() {
    // Initialize form validation
    $("#form").validate({
        rules: {
            holder_name: {
                required: true,
            },
            name: {
                required: true,
            },
            accout_number: {
                required: true,
            },
            ifsc_number: {
                required: true,
            },
            branch: {
                required: true,
            }
        },
        messages: {
            holder_name: {
                required: "Enter Bank Holder Name",
            },
            name: {
                required: "Enter Bank Name",
            },
            accout_number: {
                required: "Enter Bank Account number",
            },
            ifsc_number: {
                required: "Enter Bank IFSC Code",
            },
            branch: {
                required: "Enter Bank Branch Name",
            }
        },
        errorElement: "p",
        errorClass: "text-danger mb-0",
        highlight: function(element) {
            $(element).closest('.form-control').addClass('has-error');
        },
        unhighlight: function(element) {
            $(element).closest('.form-control').removeClass('has-error');
        },
        errorPlacement: function(error, element) {
            if (element.closest('.custom-input-group').length) {
                element.closest('.custom-input-group').append(error);
            } else {
                error.insertAfter(element);
            }
        }
    });

    // Handle form submission
    $("#form").on('submit', function(event) {
        event.preventDefault(); // Prevent default form submission

        if ($(this).valid()) {
            submitForm($(this));
        }
    });

    // Function to handle form submission via AJAX
    function submitForm(form) {
        var formData = new FormData(form[0]);
        $.ajax({
            type: "POST",
            url: "{{ route('bank.store') }}",
            data: formData,
            dataType: 'json',
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function() {
                $(".save").html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> {{ __("message.Wait") }}');
                $(".save").attr('disabled', true);
                $(".text-danger").html('');
            },
            success: function(response) {
                $(".save").html("{{ __('message.Submit') }}");
                $(".save").attr('disabled', false);
                if (response.status == false) {
                    if (response.server_error) {
                        toastr.error("{{ __('message.Something went wrong. Please try again.') }}", "{{ __('message.Error') }}");
                    } else if (response.label && response.label == 'manager') {
                        toastr.warning("{{ __('message.Your manager limit has been reached.') }}", "{{ __('message.Warning') }}");
                    } else {
                        toastr.warning("{{ __('message.Your bank limit has been reached.') }}", "{{ __('message.Warning') }}");
                    }

                    if (response.errors) {
                        $.each(response.errors, function(key, value) {
                            $('#error_' + key).html('<p class="text-danger mb-0">' + value + '</p>');
                        });
                    }
                } else {
                    $('#form')[0].reset();
                    toastr.success(response.message, "{{ __('message.Success') }}");
                    setTimeout(function() {
                        location.href = response.data;
                    }, 2000);
                }
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
                toastr.error("{{ __('message.Something went wrong. Please try again.') }}", "{{ __('message.Error') }}");
                $(".save").html("{{ __('message.Submit') }}");
                $(".save").attr('disabled', false);
            }
        });
    }
});

</script>
@endsection