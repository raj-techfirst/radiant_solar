@extends('layouts.app')
@section('title', 'Change Password')
@section('content')
<div class="row">
     <div class="col-12">
          <h4 class="card-title mb-1">{{ __('message.Password') }} <small>{{ __('message.Change') }}</small></h4>
     </div>
     <div class="col-12">
          <div class="card p-1">
               <form id="form" action="javascript:void(0)" method="POST">
                    @csrf
                    <div class="row">
                         <div class="col-12 col-md-4 col-lg-4 mb-1 custom-input-group form-password-toggle">
                              <label class="form-label" for="current_password">{{ __('message.Current Password') }} <span class="text-danger">*</span></label>
                              <div class="input-group ">
                                   <input type="password" class="form-control" name="current_password" id="current_password" placeholder="{{ __('message.Current Password') }}" aria-describedby="basic-default-password2">
                                   <span id="basic-default-password2" class="input-group-text cursor-pointer toggle-password">
                                        <i class="fa fa-eye"></i>
                                   </span>
                              </div>
                              <span class="invalid-feedback d-block" id="error_current_password" role="alert"></span>
                         </div>
                         <div class="col-12 col-md-4 col-lg-4 mb-1 custom-input-group form-password-toggle">
                              <label class="form-label" for="password">{{ __('message.New Password') }} <span class="text-danger">*</span></label>
                              <div class="input-group ">
                                   <input type="password" class="form-control" name="password" id="password" placeholder="{{ __('message.New Password') }}" aria-describedby="basic-default-password3">
                                   <span id="basic-default-password3" class="input-group-text cursor-pointer toggle-password">
                                        <i class="fa fa-eye"></i>
                                   </span>
                              </div>
                              <span class="invalid-feedback d-block" id="error_password" role="alert"></span>

                         </div>
                         <div class="col-12 col-md-4 col-lg-4 mb-1 custom-input-group form-password-toggle">
                              <label class="form-label" for="confirm_password">{{ __('message.Confirm Password') }} <span class="text-danger">*</span></label>
                              <div class="input-group ">
                                   <input type="password" class="form-control" name="confirm_password" id="confirm_password" placeholder="{{ __('message.Confirm Password') }}" aria-describedby="basic-default-password4">
                                   <span id="basic-default-password4" class="input-group-text cursor-pointer toggle-password">
                                        <i class="fa fa-eye"></i>
                                   </span>
                              </div>
                              <span class="invalid-feedback d-block" id="error_confirm_password" role="alert"></span>
                         </div>

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
     $('.toggle-password').click(function() {
          $(this).children().toggleClass('fa fa-eye fa fa-eye-slash');
     });

     $(document).on('click', '.save', function() {
          var formData = new FormData($("#form")[0]);
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
                         $(".save").html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> {{ __('message.Wait') }}`);
                         $(".save").attr('disabled', true);
                    },
                    success: function(response) {
                         $(".save").html("{{ __('message.Submit') }}");
                         $(".save").attr('disabled', false);
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
                              setTimeout(function() {
                                   location.reload(true);
                              }, 1500);
                         }
                    }
               });
          } else {
               $("#form").validate({
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