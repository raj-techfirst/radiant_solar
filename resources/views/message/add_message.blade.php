@extends('layouts.app')
@section('title', 'Message')
@section('content')
<div class="row">
     <div class="col-12">
          @if((isset($message) && isset($message->id)))
          <h4 class="card-title mb-1">{{ __('message.Edit') }} <small>Message</small></h4>
          @else
          <h4 class="card-title mb-1">{{ __('message.Add') }} <small>Message</small></h4>
          @endif
     </div>
     <div class="col-12">
          <div class="card p-1">
               <form id="form" class="form" action="javascript:void(0);" method="POST">
                    @csrf
                    <div class="row">
                         @if((isset($message) && isset($message->id)))
                         <input type="hidden" name="message_id" value="{{ $message->id }}">
                         @endif
                         <div class="col-12 col-md-4 col-lg-4 mb-1 custom-input-group">
                              <label class="form-label" for="welcome">Welcome<span class="text-danger">*</span></label>
                              <input type="text" class="form-control" name="welcome" id="welcome" placeholder="Welcome Message" value="{{ ((isset($message) && isset($message->welcome)) ? $message->welcome : old('welcome'))  }}">
                              <span class="invalid-feedback d-block" id="error_welcome" role="alert"></span>
                         </div>
                         <div class="col-12 col-md-4 col-lg-4  mb-1 custom-input-group">
                              <label class="form-label" for="follow_up">Follow Up<span class="text-danger">*</span></label>
                              <input type="text" class="form-control" name="follow_up" id="follow_up" placeholder="Follow Up Message" value="{{ ((isset($message) && isset($message->follow_up)) ? $message->follow_up : old('follow_up'))  }}">
                              <span class="invalid-feedback d-block" id="error_follow_up" role="alert"></span>
                         </div>
                         <div class="col-12 col-md-4 col-lg-4  mb-1 custom-input-group">
                              <label class="form-label" for="not_interested">Not Interested<span class="text-danger">*</span></label>
                              <input type="text" class="form-control" name="not_interested" id="not_interested" placeholder="Not Interested Message" value="{{ ((isset($message) && isset($message->not_interested)) ? $message->not_interested : old('not_interested'))  }}">
                              <span class="invalid-feedback d-block" id="error_not_interested" role="alert"></span>
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
     $(document).on('click', '.save', function() {
          var formData = new FormData($("#form")[0]);
          if ($("#welcome").val() != "" && $("#follow_up").val() != "" && $("#not_interested").val() != "") {
               $.ajax({
                    type: "POST",
                    url: "{{route('message.store')}}",
                    data: formData,
                    dataType: 'json',
                    cache: false,
                    contentType: false,
                    processData: false,
                    beforeSend: function() {
                         $("#error_welcome").html(' ');
                         $(".save").html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> {{ __('message.Wait') }}`);
                         $(".save").attr('disabled', true);
                    },
                    success: function(response) {
                         $(".save").html("{{ __('message.Submit') }}");
                         $(".save").attr('disabled', false);
                         if (response.server_error && response.status == false) {
                              toastr.error("{{ __('message.Something went wrong. Please try again.') }}", "{{ __('message.Error') }}");
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
                        welcome: {
                              required: true,
                         },
                         follow_up: {
                              required: true,
                         },
                         not_interested: {
                              required: true,
                         },
                    },
                    messages: {
                        welcome: {
                              required: "Enter welcome Message"
                         },
                         follow_up: {
                              required: "Enter Follow Up Message"
                         },
                         not_interested: {
                              required: "Enter Not Interested Message"
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