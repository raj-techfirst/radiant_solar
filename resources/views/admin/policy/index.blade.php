@extends('layouts.app')
@section('title', 'Terms & Conditions')
@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-lite.min.css" />
@endsection
@section('content')
<div class="row">
    <div class="col-12">
        @if((isset($policy) && isset($policy->id)))
        <h4 class="card-title mb-1">Edit Terms & Conditions</h4>
        @else
        <h4 class="card-title mb-1">Add Terms & Conditions</h4>
        @endif
    </div>
    <div class="col-12">
        <div class="card p-1">

            <hr>
            <form id="form" action="javascript:void(0)" method="POST">
                @csrf
                <div class="row">
                    @if((isset($policy) && isset($policy->id)))
                    <input type="hidden" id="policy" name="policy_id" value="{{ $policy->id }}">
                    @endif
                    <div class="form-group radioeffect">
                        <input name="form_type" id="radio1" value="trading" class="form-check-input form_type" type="radio" {{ isset($sales_quatation) && $sales_quatation->form_type == 'trading' ? 'checked' : '' }} checked>
                        <label for="radio1" class="form-check-label radGroup1">{{ __('message.Trading') }} &nbsp; &nbsp;</label>
                        <input name="form_type" id="radio2" value="resident" class="form-check-input form_type" type="radio" {{ isset($sales_quatation) && $sales_quatation->form_type == 'resident' ? 'checked' : '' }}>
                        <label for="radio2" class="form-check-label radGroup1">{{ __('message.Resident With Subsidy') }}&nbsp; &nbsp;</label>
                        <input name="form_type" id="radio3" value="roof" class="form-check-input form_type" type="radio" {{ isset($sales_quatation) && $sales_quatation->form_type == 'roof' ? 'checked' : '' }}>
                        <label for="radio3" class="form-check-label radGroup1">{{ __('message.Solar RoofTop') }}</label>
                    </div>

                    <div class="col-lg-12 light-style form-group custom-input-group mt-1 " id="item_trading">
                        <textarea id="editor">{!! ((isset($policy) && isset($policy->policy)) ? $policy->policy : '') !!}</textarea>
                    </div>
                </div>

                <div class="col-md-12 mt-2">
                    <button type="submit" class="btn btn-sm btn-primary float-end save">{{ __('message.Submit') }}</button>
                    <a role="botton" class="btn btn-sm btn-primary float-end mx-1" href="{{route('policy.index')}}">{{ __('message.Cancel') }}</a>
                </div>
            </form>
        </div>
    </div>

</div>

@endsection
@section('pagescript')

<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote.min.js"></script>
<script>
    $('#editor').summernote({
        placeholder: 'Hello stand alone ui',
        tabsize: 2,
        height: 500,
        toolbar: [
            ['font', ['bold', 'underline', 'clear']],
            ['para', ['ul', 'ol']],

        ]
    });
    $("#form").validate({
        rules: {
            policy: {
                required: true,
            },
        },
        messages: {
            policy: "Enter Policy"
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
    $(document).on('click', '.save', function() {
        if ($("#form").valid()) {
            var formData = new FormData($("#form")[0]);
            formData.append('policy', $("#editor").val());
            $.ajax({
                type: "POST",
                url: "{{route('policy.store')}}",
                data: formData,
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $("#error_name").html(' ');
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
                        toastr.success(response.message, "{{ __('message.Success') }}");
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

    $(document).on('change', '.form_type', function() {
        var value = $('.form_type:checked').val();

        var url = "{{route('policy.show','id')}}".replace('id', 2);
        $.ajax({
            url: url,
            type: 'get',
            datatype: 'json',
            data: {
                "form_type": value,
                "_token": "{{ csrf_token() }}",
            },
            success: function(response) {
                $('#editor').summernote('destroy');
                $("#editor").val(response.policy.policy);
                $('#editor').summernote({
                    placeholder: 'Hello stand alone ui',
                    tabsize: 2,
                    height: 500,
                    toolbar: [
                        ['font', ['bold', 'underline', 'clear']],
                        ['para', ['ul', 'ol']],
                    ]
                });
            }
        });
    });
</script>
@endsection