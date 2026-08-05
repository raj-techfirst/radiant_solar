@extends('layouts.app')
@section('title', 'Warehouse')
@section('content')
<div class="row">
    <div class="col-12 mb-50">
        <h4 class="content-header-title float-start">Warehouse List</h4>
        @can('warehouse-create')
        <button type="button" data-bs-toggle="modal" data-bs-target="#inlineModal" class="btn btn-sm btn-primary float-end"><i class="fa fa-plus me-25"></i> Add New</button>
        @endcan
    </div>
    <div class="col-12">
        <div class="card p-1">
            <table id="table" class="datatables-basic table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Action</th>
                        <th>Warehouse Name</th>
                        <th>Contact Person</th>
                        <th>Contact Person No.</th>
                        <th>Address</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="inlineModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-transparent border-bottom">
                <h4 class="text-center mb-0" id="exampleModalTitle">Add Warehouse</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-1" id="body">
                <form id="form" class="form" action="javascript:void(0);" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                            <label class="form-label" for="name">Warehouse Name <span class="text-danger">*</span></label>
                            <input type="hidden" name="id" id="id" value="">
                            <input type="text" class="form-control" name="name" id="name" placeholder="Warehouse Name">
                            <span class="invalid-feedback d-block" id="error_name" role="alert"></span>
                        </div>

                        <div class="col-12 col-md-6 mb-1 col-lg-4 custom-input-group">
                            <label class="form-label" for="contact_person">Contact Person</label>
                            <input type="text" class="form-control" name="contact_person" id="contact_person" placeholder="Contact Person">
                        </div>

                        <div class="col-12 col-md-6 col-lg-4 mb-1 custom-input-group">
                            <label class="form-label" for="contact_person_no">Contact Person No.</label>
                            <input type="number" maxlength="10" class="form-control number" name="contact_person_no" id="contact_person_no" placeholder="Contact Person No.">
                        </div>

                        <div class="col-12 col-md-12 col-lg-12 mb-1 custom-input-group">
                            <label class="form-label" for="address">Address <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="address" id="address" placeholder="Type here.."></textarea>
                            <span class="invalid-feedback d-block" id="error_address" role="alert"></span>
                        </div>

                        <div class="col-md-12 col-12">
                            <button type="submit" class="btn btn-sm btn-primary float-end save">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade fdv" id="exampleModal" tabindex="-1" aria-labelledby="detailModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered  modal-l">
        <div class="modal-content" id="modal_content">

        </div>
    </div>
</div>
@endsection

@section('pagescript')
<script type="application/javascript">
    'use strict';
    const URL = "{{route('warehouse.index')}}";

    var table = '';
    $(function() {
        table = $('#table').DataTable({
            ajax: URL,
            processing: true,
            serverSide: true,
            fixedHeader: true,
            scrollX: true,
            aLengthMenu: [
                [15, 30, 50, 100, -1],
                [15, 30, 50, 100, "All"]
            ],
            order: [
                [0, 'desc']
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
                    data: 'contact_person',
                    name: 'contact_person',
                },
                {
                    data: 'contact_person_no',
                    name: 'contact_person_no',
                },
                {
                    data: 'address',
                    name: 'address',
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

    $("#form").validate({
        rules: {
            name: {
                required: true,
            },
            address: {
                required: true,
            },
        },
        messages: {
            name: {
                required: "Enter warehouse name",
            },
            address: {
                required: "Enter address",
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

    $(document).on('click', '.save', function() {
        var formData = new FormData($("#form")[0]);
        if ($("#form").valid()) {
            $.ajax({
                type: "POST",
                url: "{{route('warehouse.store')}}",
                data: formData,
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $(".save").html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Wait');
                    $(".save").attr('disabled', true);
                },
                success: function(response) {
                    $(".save").html('Submit');
                    $(".save").attr('disabled', false);
                    if (response.status_code == 201) {
                        $.each(response.errors, function(key, value) {
                            toastr.error(value, 'Error');
                            // $('#error_' + key).html('<p class="text-danger mb-0">' + value + '</p>');
                        });
                        //toastr.warning('Please Input Propper Data.', 'Warning');
                    } else if (response.status_code == 500) {
                        toastr.error(response.message, 'Error');
                    } else {
                        $('#form')[0].reset();
                        toastr.success(response.message, 'Success');
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
        $("#exampleModalTitle").html("Add Warehouse");
    });

    $(document).on('click', '.edit', function() {
        var id = $(this).data('id');
        var url = "{{route('warehouse.edit','id')}}".replace('id', id);
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
                    $("#exampleModalTitle").html("Edit Warehouse");
                    $("#name").val(data.result.name);
                    $("#contact_person").val(data.result.contact_person);
                    $("#contact_person_no").val(data.result.contact_person_no);
                    $("#address").val(data.result.address);
                    $("#id").val(id);
                    $("#inlineModal").modal('show');
                } else {
                    swal(data.msg_content, {
                        icon: "error",
                    });
                }
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
                                toastr.warning("{{ __('message.This Item has been used.') }}", "{{ __('message.Warning') }}");
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