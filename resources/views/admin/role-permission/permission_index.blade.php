@extends('layouts.app')
@section('title', 'Permisson')
@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="content-header-title float-start">Permisson List</h4>
        <button type="button" data-bs-toggle="modal" data-bs-target="#inlineModal" class="btn btn-sm btn-primary float-end"><i class="fa fa-plus me-25"></i> Add New</button>
    </div>
    <div class="col-12">
        <div class="card p-1">
            <table id="table" class="datatables-basic table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Action</th>
                        <th>Module</th>
                        <th>Operation</th>
                        <th>Link</th>
                        <th>Type</th>
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
                <h4 class="text-center mb-0" id="exampleModalTitle">Add Permission</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-1" id="body">
                <form id="form" class="form" action="javascript:void(0);" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-group custom-input-group mb-25">
                            <label class="form-label" for="title_tag">Module <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title_tag" id="title_tag" placeholder="Module Name">
                            <span class="invalid-feedback d-block" id="error_title_tag" role="alert"></span>
                        </div>
                        <!-- <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-group custom-input-group">
                            <label class="form-label" for="title">Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="title" id="title" placeholder="Title">
                            <span class="invalid-feedback d-block" id="error_title" role="alert"></span>
                        </div> -->
                        <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-group custom-input-group mb-25">
                            <label class="form-label" for="title">Select Operation <span class="text-danger">*</span></label>
                            <select class="form-select select2 custom-select2" name="title" id="title">
                                <option value="" selected disabled>-- Select --</option>
                                <option value="List">List</option>
                                <option value="Create">Create</option>
                                <option value="Edit">Edit</option>
                                <option value="Delete">Delete</option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-group custom-input-group mb-25">
                            <label class="form-label" for="name">Link <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="name" placeholder="Link Name">
                            <span class="invalid-feedback d-block" id="error_name" role="alert"></span>
                        </div>

                        <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-group custom-input-group mb-25">
                            <label class="form-label" for="type">Type <span class="text-danger">*</span></label>
                            <select class="form-select select2 custom-select2" name="type" id="type">
                                <option value="" selected disabled>-- Select --</option>
                                <option value="CRM">CRM</option>
                                <option value="ERP">ERP</option>
                                <!-- <option value="Both">Both</option> -->
                            </select>
                        </div>


                        <div class="col-12 col-sm-12 col-md-12 col-lg-12 form-group mt-1">
                            <input type="hidden" name="id" id="id" value="">
                            <button type="submit" class="btn btn-sm btn-primary float-end save">Submit</button>
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
    const URL = "{{route('permissions.index')}}";
    var table = '';
    $(function() {
        table = $('#table').DataTable({
            ajax: URL,
            processing: true,
            serverSide: true,
            fixedHeader: true,
            scrollX: true,
            order: [0, 'desc'],
            aLengthMenu: [
                [10, 25, 50, 100],
                [10, 25, 50, 100, "All"]
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
                    data: 'title_tag',
                    name: 'title_tag'
                },
                {
                    data: 'title',
                    name: 'title'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'type',
                    name: 'type'
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

    $(document).ready(function() {
        $("#form").validate({
            rules: {
                title_tag: {
                    required: true,
                },
                title: {
                    required: true,
                },
                name: {
                    required: true,
                },
            },
            messages: {
                title_tag: {
                    required: "Enter module name"
                },
                title: {
                    required: "Select operation"
                },
                name: {
                    required: "Enter link name"
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
    });


    $(document).on('click', '.save', function() {
        var formData = new FormData($("#form")[0]);
        if ($("#form").valid()) {
            $.ajax({
                type: "POST",
                url: "{{route('permissions.store')}}",
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
                        $("#inlineModal").modal('hide');
                        table.ajax.reload(null, true);
                    }
                }
            });
        } else {
            return false;
        }
    });

    $("#inlineModal").on("hidden.bs.modal", function(e) {
        $(this).find('form').trigger('reset');
        $("#id").val("");
        $(".custom-error").html("");
        $(".invalid-feedback").html("");
        $(".select2").val("").trigger("change");
        $("#exampleModalTitle").html("Add Permissions");
    });

    $(document).on('click', '.edit', function() {
        var id = $(this).data('id');
        var url = "{{route('permissions.edit','id')}}".replace('id', id);
        $.ajax({
            type: "GET",
            url: url,
            dataType: 'json',
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function() {},
            success: function(data) {
                if (data.status_code == 200) {
                    $("#exampleModalTitle").html("Edit Permissions");
                    $("#name").val(data.result.name);
                    $("#title_tag").val(data.result.title_tag);
                    // $("#title").val(data.result.title);
                    $("#title").val(data.result.title).trigger('change');
                    $("#type").val(data.result.type).trigger('change');
                    $("#id").val(id);
                    $("#inlineModal").modal('show');
                } else {
                    toastr.error(data.message, "Error");
                }
            }
        });
    });

    $(document).on('click', '.delete', function() {
        var id = $(this).data('id');
        Swal.fire({
            title: "Are you sure?",
            text: "You won`t be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: "Yes, delete it!",
            customClass: {
                confirmButton: 'btn btn-primary',
                cancelButton: 'btn btn-outline-danger ms-1'
            },
            buttonsStyling: false
        }).then(function(result) {
            if (result.value) {
                axios.delete(URL + '/' + id)
                    .then(function(response) {
                        if (response.data.status_code == 200) {
                            table.ajax.reload(null, false);
                            toastr.success(response.data.message, "Success");
                        } else if (response.data.status_code == 201) {
                            toastr.warning(response.data.message, "Warning");
                        } else {
                            toastr.error(response.data.message, "Error");
                        }
                    }).catch(function() {
                        toastr.error("Something went wrong. Please try again.", "Error");
                    });
            } else {
                Swal.fire({
                    text: "Your data is safe."
                });
            }
        });
    });
</script>
@endsection