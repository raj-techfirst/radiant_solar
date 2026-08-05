@extends('layouts.app')
@section('title', 'Source')
@section('content')
<div class="row">
     <div class="col-12">
          @if((isset($source) && isset($source->id)))
          <h4 class="card-title mb-1">{{ __('message.Edit') }} <small>{{ __('message.Source') }}</small></h4>
          @else
          <h4 class="card-title mb-1">{{ __('message.Add') }} <small>{{ __('message.Source') }}</small></h4>
          @endif
     </div>
     <div class="col-12">
          <div class="card p-1">
               <form id="form" class="form" action="javascript:void(0);" method="POST">
                    @csrf
                    <div class="row">
                         @if((isset($source) && isset($source->id)))
                         <input type="hidden" name="source_id" value="{{ $source->id }}">
                         @endif
                         <div class="col-12 col-md-4 mb-1 custom-input-group">
                              <label class="form-label" for="source_name">{{ __('message.Source') }}<span class="text-danger">*</span></label>
                              <input type="text" class="form-control" name="source_name" id="source_name" placeholder="{{ __('message.Source') }}" value="{{ ((isset($source) && isset($source->source_name)) ? $source->source_name : old('source_name'))  }}">
                              <span class="invalid-feedback d-block" id="error_source_name" role="alert"></span>
                         </div>
                         <div class="col-12 col-md-8 mb-1">
                              <label class="form-label" for="source_link">{{ __('message.Source Link') }}</label>
                              <input type="text" class="form-control" name="source_link" id="source_link" placeholder="{{ __('message.Source Link') }}" value="{{ ((isset($source) && isset($source->source_link)) ? $source->source_link : old('source_link'))  }}">
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
          $("#form").validate({
               rules: {
                    source_name: {
                         required: true,
                    },
               },
               messages: {
                    source_name: {
                         required: "{{ __('message.Enter source') }}"
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
          if ($("#form").valid()) {
               var formData = new FormData($("#form")[0]);
               $.ajax({
                    type: "POST",
                    url: "{{route('source.store')}}",
                    data: formData,
                    dataType: 'json',
                    cache: false,
                    contentType: false,
                    processData: false,
                    beforeSend: function() {
                         $("#error_source_name").html(' ');
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
               return false;
          }
     });
</script>
@endsection