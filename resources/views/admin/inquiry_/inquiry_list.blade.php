@extends('layouts.app')
@section('title', 'Complaint Management')
@section('content')
    <div class="row">
        <div class="col-12 mb-1">
            <h4 class="content-header-title float-start">Complaint List</h4>


            <button type="button" data-bs-toggle="modal" data-bs-target="#addexampleModal"
                class="btn btn-sm btn-primary float-end"><i class="fa fa-plus me-25"></i> {{ __('message.Add New') }}</button>

        </div>
        <div class="col-12">
            <div class="card p-1">
                <table id="inquiry" class="datatables-basic table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Action</th>
                            <th>Consumer Name</th>
                            <th>Consumer Number</th>
                            <th>Contact Number</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-l">
            <div class="modal-content" id="modal_content">>

            </div>
        </div>
    </div>

    <div class="modal fade" id="addexampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-l">
            <div class="modal-content" id="add_modal_content">

                <div class="modal-header pb-0 bg-transparent">
                    <h4 class="text-center mb-0"> Add New</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <form class="auth-register-form" id="form" action="javascript:void(0)" method="POST">
                        @csrf
                        <div class="row">
                            <hr>

                            <div class="col-12 col-sm-12 col-md-12 col-lg-6 pe-1">
                                <div class="mb-1 custom-input-group">
                                    <label class="form-label" for="contact_number">Contact Number<span
                                            class="text-danger">*</span></label>
                                    <input type="number" maxlength="10" name="contact_number" id="contact_number"
                                        class="form-control number" placeholder="Contact Number" required />
                                    <span class="invalid-feedback d-block" id="error_contact_number" role="alert"></span>
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
                                    <span class="invalid-feedback d-block" id="error_consumer_number" role="alert"></span>
                                </div>
                            </div>

                            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                <div class="mb-1 custom-input-group">
                                    <label class="form-label" for="consumer_name">Consumer Name<span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="consumer_name" id="consumer_name"
                                        placeholder="Consumer Name" required />
                                    <span class="invalid-feedback d-block" id="error_consumer_name" role="alert"></span>
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

                            <div class="col-12 col-sm-12 col-md-12  col-lg-12">
                                <button type="submit" class="btn btn-primary save float-end w-100">Submit</button>
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
    const URL = "{{route('inquiry-list')}}";

    var table = '';
    $(function() {
        table = $('#inquiry').DataTable({
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
                    data: 'consumer_name',
                    name: 'category_name'
                },
                {
                    data: 'consumer_number',
                    name: 'consumer_number'
                },
                {
                    data: 'contact_number',
                    name: 'contact_number'
                },
                {
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    data: 'status',
                    name: 'status'
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

   $(document).on('blur', '#contact_number', function () {
        const contact_number = $(this).val().trim();
        const phoneRegex = /^[0-9]{10}$/;
        if (phoneRegex.test(contact_number)) {
            var url = "{{route('get-consumer-using-mobile')}}";
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
                    if(response.status == true)
                    {
                        $('#consumer_name').val(response.message[0].consumer_name);
                        $.each(response.message, function(key, value) {
                            $('#consumer_number').html('<option value="'+value.consumer_number+'" data-name="'+value.consumer_name+'">' + value.consumer_number + '</p>');
                        });
                    }
                    else
                    {
                        $('#consumer_number').html('<option value="" selected>Select Any</option>');
                        $('#consumer_name').val('');
                    }
                }
            });
        }
    });

     $(document).on('click', '.view', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var url = "{{route('inquiry-show','id')}}".replace('id', id);
        $("#exampleModal").modal("show");
        $.ajax({
            url: url,
            type: 'get',
            datatype: 'json',
            data: {
                "_token": "{{ csrf_token() }}",
            },
            success: function(response) {
                $("#modal_content").html(response.html);
                if (feather) {
                    feather.replace({
                        width: 14,
                        height: 14
                    });
                }
            }
        });
    });


    $(document).on('click', '.delete', function() {
        var btn = $(this);
        var id = btn.data('id');
        const Delete = "{{route('inquiry-delete','id')}}".replace('id', id);
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
                    axios.delete(Delete)
                        .then(function(response) {
                            if (response.data.status == true) {
                                table.ajax.reload(null, false);
                                toastr.success("{{ __('message.Deleted successfully.') }}", "{{ __('message.Success') }}");
                            } else if (response.data.status == false && response.data.server_error) {
                                toastr.error("{{ __('message.Something went wrong. Please try again.') }}", "{{ __('message.Error') }}");
                            } else {
                                toastr.warning("{{ __('message.This category has been used.') }}", "{{ __('message.Warning') }}");
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

     document.querySelectorAll('.number').forEach(function(input) {
        input.addEventListener('input', function(e) {
            this.value = this.value.replace(/\D/g, '').slice(0, 10);
            if (this.value.charAt(0) === '0') {
                this.value = this.value.substring(1);
            }
        });
    });

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
                url: "{{route('save-inquiry')}}",
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
                    if (response.status_code == 500) {
                        toastr.error(response.message, "{{ __('message.Error') }}");
                    } else if (response.status_code == 403) {
                        toastr.warning(response.message, "{{ __('message.Warning') }}");
                    } else if (response.status_code == 201) {
                        $.each(response.errors, function(key, value) {
                            $('#error_' + key).html('<p class="text-danger mb-0">' + value + '</p>');
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
</script>
@endsection
