@extends('layouts.app')
@section('title', 'Edit Role')
@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="content-header-title float-start">Edit Role</h4>
        {{--<a href="{{ route('roles.index') }}" class="btn btn-sm btn-primary float-end"><i class="fa fa-plus me-25"></i> Create New Role</a>--}}
    </div>
    <div class="col-12">
        <div class="card">
            <div class="card-body p-1">
                <form id="form" class="form p-0" action="javascript:void(0);" method="Post">
                    @csrf
                    @method('put')
                    <div class="row">
                        <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-group custom-input-group mb-1">
                            <label class="form-label" for="name">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="name" value="{{$role->name}}">
                        </div>
                        @php $no = 0; $flag = null; $previousType = null; @endphp
                        @foreach($permissionTitle as $key => $item)
                        @foreach($permission as $keys => $value)
                        @if($item->title_tag == $value->title_tag)
                        @php $flag = 1; @endphp

                        @if($item->id == $value->id)
                        @if($previousType !== $value->type)
                        @php $previousType = $value->type;  @endphp
                        <div class="col-12 col-lg-12 my-25"><h2>{{$value->type}}</h2></div>
                        @endif
                        <div class="col-12 col-lg-3 my-25">
                            <div class="d-flex">
                                <div class="form-check">
                                    <label class="form-check-label text-nowrap" for="permission-all-{{$item->title_tag}}"> <b>{{$value->title_tag}}</b></label>
                                    @if(in_array($value->id, $rolePermissions))
                                    <input type="checkbox" class="form-check-input permission-all" name="permission-all[]" value="{{ str_replace(' ', '', $value->title_tag) }}" id="permission-all-{{$item->title_tag}}" checked />
                                    @else
                                    <input type="checkbox" class="form-check-input permission-all" name="permission-all[]" value="{{ str_replace(' ', '', $value->title_tag) }}" id="permission-all-{{$item->title_tag}}" />
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                        <div class="col-12 col-sm-3 col-md-3 col-lg-2 my-25">
                            <div class="d-flex">
                                <div class="form-check">
                                    @if(in_array($value->id, $rolePermissions))
                                    <input type="checkbox" class="form-check-input {{ str_replace(' ', '', $value->title_tag) }}" name="permission[]" value="{{$value->id}}" id="permission{{$value->id}}" checked />
                                    @else
                                    <input type="checkbox" class="form-check-input {{ str_replace(' ', '', $value->title_tag) }}" name="permission[]" value="{{$value->id}}" id="permission{{$value->id}}" />
                                    @endif
                                    <label class="form-check-label" for="permission{{$value->id}}"> {{$value->title}} </label>
                                </div>
                            </div>
                        </div>
                        @php $no++; @endphp
                        @if($no % 4 == 0)
                        <div class="col-12 col-lg-3 my-25"></div>
                        @endif
                        @endif
                        @endforeach
                        @if($flag !== null)
                        <div class="col-12">
                            <hr class="my-25">
                        </div>
                        @endif
                        @php $no = 0; $flag = null; @endphp
                        @endforeach
                        <div class="col-12 col-sm-12 col-md-12 col-lg-12 pt-1 text-end">
                            <button type="submit" class="btn btn-sm btn-primary save">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@section('pagescript')
<script type="application/javascript">
    $(document).ready(function() {
        $("#form").validate({
            rules: {
                name: {
                    required: true,
                },
                "permission[]": {
                    required: true
                }
            },
            messages: {
                name: {
                    required: "Enter role name"
                },
                "permission[]": {
                    required: 'Select permission',
                }
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
            var formData = new FormData($("#form")[0]);
            if ($("#form").valid()) {
                $.ajax({
                    type: "POST",
                    url: "{{$url}}",
                    data: formData,
                    dataType: 'json',
                    cache: false,
                    contentType: false,
                    processData: false,
                    beforeSend: function() {
                        $(".invalid-feedback").html(' ');
                        $(".save").html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Wait`);
                        $(".save").attr('disabled', true);
                    },
                    success: function(response) {
                        $(".save").html("Submit");
                        $(".save").attr('disabled', false);
                        if (response.status_code == 500) {
                            toastr.error(response.message, "Error");
                        } else if (response.status_code == 403) {
                            toastr.warning(response.message, "Warning");
                        } else if (response.status_code == 201) {
                            $.each(response.errors, function(key, value) {
                                $('#error_' + key).html('<p class="text-danger mb-0">' + value + '</p>');
                            });
                            toastr.warning(response.message, "Warning");
                        } else {
                            $('#form')[0].reset();
                            toastr.success(response.message, "Success");
                            setTimeout(function() {
                                location.href = response.data;
                            }, 800);
                        }
                    }
                });
            } else {
                return false;
            }
        });
    });

    $('.permission-all').click(function() {
        var me = $(this).val();
        if ($(this).prop('checked') == true) {
            $("." + me).prop('checked', true);
        } else {
            $("." + me).prop('checked', false);
        }
    });
</script>
@endsection