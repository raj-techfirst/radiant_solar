@extends('layouts.app')
@section('title', 'Sub Division')
@section('content')
<div class="row">
    <div class="col-12 mb-1">
        <h4 class="content-header-title float-start">Sub Division List</h4>
        @can('sub-division-create')
        <button type="button" data-bs-toggle="modal" data-bs-target="#inlineModal" class="btn btn-sm btn-primary float-end"><i class="fa fa-plus me-25"></i> {{ __('message.Add New') }}</button>
        @endcan
    </div>
    <div class="col-12">
        <div class="card p-1">
            <table id="sub_division" class="datatables-basic table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('message.Action') }}</th>
                        <th>Name</th>
                        <th>Division Name</th>
                        <th>Circle Name</th>
                        <th>DISCOM</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="inlineModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-transparent border-bottom">
                <h4 class="text-center mb-0" id="exampleModalTitle">Add Sub Division</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-1" id="body">
                <form id="form" class="form" action="javascript:void(0);" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-12 col-md-12 mb-1 custom-input-group">
                            <input type="hidden" name="sub_division_id" id="sub_division_id" value="">
                            <label class="form-label" for="name">Name<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="name" placeholder="{{ __('message.Name') }} *">
                            <span class="invalid-feedback d-block" id="error_name" role="alert"></span>
                        </div>
                        <div class="col-12 col-md-12 mb-1 custom-input-group">
                            <label class="form-label" for="division_name">Division Name<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="division_name" id="division_name" placeholder="Division Name *">
                            <span class="invalid-feedback d-block" id="error_division_name" role="alert"></span>
                        </div>
                        <div class="col-12 col-md-12 mb-1 custom-input-group">
                            <label class="form-label" for="circle_name">Circle name<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="circle_name" id="circle_name" placeholder="Circle name *">
                            <span class="invalid-feedback d-block" id="error_circle_name" role="alert"></span>
                        </div>
                        <div class="col-12 col-md-12 mb-1 custom-input-group">
                            <label class="form-label" for="discom">DISCOM <span class="text-danger">*</span></label>
                            <input type="hidden" name="discom_address" id="discom_address" value="" />
                            <select class="form-control anlayst form-select select2 custom-select2 w-100" name="discom" id="discom">
                                <option selected disabled>{{ __('message.-- Select --') }}</option>
                                @foreach($discoms as $value)
                                <option value="{{$value->discom_name}}" data-address="{{$value->address}}">{{$value->discom_name}}</option>
                                @endforeach
                            </select>
                            <span class="invalid-feedback d-block" id="error_discom" role="alert"></span>
                        </div>
                        <div class="col-md-12 col-12">
                            <button type="submit" class="btn btn-sm btn-primary float-end save">{{ __('message.Submit') }}</button>
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
    'use strict';
    const URL = "{{route('sub-division.index')}}";

    var table = '';
    $(function() {
        table = $('#sub_division').DataTable({
            ajax: URL,
            processing: true,
            serverSide: true,
            fixedHeader: true,
            scrollX: true,
            aLengthMenu: [
                [20, -1],
                [20, "All"],
            ],
            columns: [{
                    data: 'id',
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    sortable: false
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'division_name',
                    name: 'division_name'
                }, {
                    data: 'circle_name',
                    name: 'circle_name'
                }, {
                    data: 'discom',
                    name: 'discom'
                },
            ],
            initComplete: function(settings, json) {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                })
            }
        });
    });

    $('#discom').change(function() {
        var selectedOption = $(this).find('option:selected');
        var discom_address = selectedOption.data('address');
        $('#discom_address').val(discom_address);
    });

    $(document).on('click', '.save', function() {
        $("#form").validate({
            rules: {
                name: {
                    required: true,
                },
                division_name: {
                    required: true,
                },
                circle_name: {
                    required: true,
                },
                discom: {
                    required: true,
                },
            },
            messages: {
                name: {
                    required: "Enter Name"
                },
                division_name: {
                    required: "Enter Division Name"
                },
                circle_name: {
                    required: "Enter Circle Name"
                },
                discom: {
                    required: "Enter DISCOM"
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
                url: "{{route('sub-division.store')}}",
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
                        $('#form')[0].reset();
                        // toastr.success("{{ __('message.Saved successfully.') }}", "{{ __('message.Success') }}");
                        toastr.success(response.message, "{{ __('message.Success') }}");
                        setTimeout(function() {
                            location.href = response.data;
                        }, 1000);
                    }
                }
            });
            इ
        } else {
            return false;
        }
    });

    $("#inlineModal").on("hidden.bs.modal", function(e) {
        $(this).find('form').trigger('reset');
        $("#sub_division_id").val("");
        $("#name-error").html("");
        $("#division_name-error").html("");
        $("#circle_name-error").html("");
        $("#discom-error").html("");
        $("#exampleModalTitle").html("Add Sub Division");
    });

    $(document).on('click', '.edit', function() {
        var id = $(this).data('id');
        var url = "{{route('sub-division.edit','id')}}".replace('id', id);
        $.ajax({
            type: "GET",
            url: url,
            dataType: 'json',
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function() {},
            success: function(data) {
                if (data.msg_type == "success") {
                    $("#exampleModalTitle").html("Edit Sub Division");
                    $("#name").val(data.result.name);
                    $("#division_name").val(data.result.division_name);
                    $("#circle_name").val(data.result.circle_name);
                    $("#discom").val(data.result.discom);
                    $("#discom").select2();
                    $("#discom").trigger('change');
                    $("#sub_division_id").val(id);
                    $("#inlineModal").modal('show');
                } else {
                    swal(data.msg_content, {
                        icon: "error",
                    });
                }
            }
        });
    });

    $(document).on('click', '.delete', function() {
        var btn = $(this);
        var id = btn.data('id');
        Swal.fire({
                title: "{{ __('message.Are you sure?') }}",
                text: "{{ __('message.You won`t be able to revert this!') }}",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: "{{ __('message.Yes, delete it!') }}",
                customClass: {
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-outline-danger ms-1'
                },
                buttonsStyling: false
            })
            .then(function(result) {
                if (result.value) {
                    axios.delete(URL + '/' + id)
                        .then(function(response) {
                            if (response.data.status == true) {
                                table.ajax.reload(null, false);
                                toastr.success("{{ __('message.Deleted successfully.') }}", "{{ __('message.Success') }}");
                            } else if (response.data.status == false && response.data.server_error) {
                                toastr.error("{{ __('message.Something went wrong. Please try again.') }}", "{{ __('message.Error') }}");
                            } else {
                                toastr.warning("{{ __('message.This Sub Division has been used.') }}", "{{ __('message.Warning') }}");
                            }
                        })
                        .catch(function() {
                            toastr.error("{{ __('message.Something went wrong. Please try again.') }}", "{{ __('message.Error') }}");
                        });
                } else {
                    Swal.fire({
                        text: "{{ __('message.Your data is safe.') }}"
                    });
                }
            });
    });
</script>
@endsection