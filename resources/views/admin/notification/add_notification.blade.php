@extends('layouts.app')
@section('title', 'Notification')
@section('content')
<div class="row">
    <div class="col-12">
        @if((isset($notification) && isset($notification->id)))
        <h4 class="card-title mb-1">Notification <small>Edit</small></h4>
        @else
        <h4 class="card-title mb-1">Notification <small>Add</small></h4>
        @endif
    </div>
    <div class="col-12">
        <div class="card p-1">
            <form id="form" class="form" action="javascript:void(0);" method="POST">
                @csrf
                <div class="row">
                    @if((isset($notification) && isset($notification->id)))
                    <input type="hidden" name="notification_id" value="{{ $notification->id }}">
                    @endif
                    <div class="col-12 col-md-12 mb-1 custom-input-group">
                        <label class="form-label" for="user_id">User<span class="text-danger">*</span></label>
                        <select class="form-control" name="user_id" id="user_id">
                            <option selected disabled>-- Select User --</option>
                            @foreach ($user as $item)
                            <option value="{{ $item->id }}" {{ (isset($notification) && $notification->user_id == $item->id ) ? 'selected' : '' }}>{{ $item->name}}</option>
                            @endforeach
                        </select>
                        <span class="invalid-feedback d-block" id="error_user_id" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-12 mb-1 custom-input-group">
                        <label class="form-label" for="title">Title<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="title" id="title" placeholder="Title" value="{{ ((isset($notification) && isset($notification->title)) ? $notification->title : '' )}}">
                        <span class="invalid-feedback d-block" id="error_title" role="alert"></span>
                    </div>
                    <div class="col-12 col-md-12 mb-1 custom-input-group">
                        <label class="form-label" for="description">Description<span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="description" id="description" placeholder="Description" value="{{ ((isset($notification) && isset($notification->description)) ? $notification->description : '' )}}">
                        <span class="invalid-feedback d-block" id="error_description" role="alert"></span>
                    </div>
                    <div class="col-md-12 col-12 custom-input-group">
                        <button type="submit" class="btn btn-sm btn-primary float-end save">Submit</button>
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
        if ($("#title").val() != "") {
            $.ajax({
                type: "POST",
                url: "{{route('notification.store')}}",
                data: formData,
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $("#error_title").html(' ');
                    $(".save").html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Wait`);
                    $(".save").attr('disabled', true);
                },
                success: function(response) {
                    $(".save").html("Submit");
                    $(".save").attr('disabled', false);
                    if (response.server_error && response.status == false) {
                        toastr.error("Something went wrong. Please try again.", "Error");
                    } else if (response.status == false) {
                        $.each(response.errors, function(key, value) {
                            $('#error_' + key).html('<p class="text-danger mb-0">' + value + '</p>');
                        });
                        toastr.warning("Please input proper data.", "Warning");
                    } else {
                        $('#form')[0].reset();
                        toastr.success("Saved successfully.", "Success");
                        setTimeout(function() {
                            location.href = response.data;
                        }, 2000);
                    }
                }
            });
        } else {
            $("#form").validate({
                rules: {
                    user_id: {
                        required: true,
                    },
                    title: {
                        required: true,
                    },
                    description: {
                        required: true,
                    },
                    description: {
                        required: true,
                    },
                },
                messages: {
                    user_id: {
                        required: "Select user"
                    },
                    title: {
                        required: "Enter title"
                    },
                    description: {
                        required: "Enter description"
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