@extends('layouts.app')
@section('title', 'DISCOM')
@section('content')
<div class="row">
    <div class="col-12 mb-1">
        <h4 class="content-header-title float-start">DISCOM List</h4>
        @can('discom-create')
        <button type="button" data-bs-toggle="modal" data-bs-target="#inlineModal" class="btn btn-sm btn-primary float-end"><i class="fa fa-plus me-25"></i> {{ __('message.Add New') }}</button>
        @endcan
    </div>
    <div class="col-12">
        <div class="card p-1">
            <table id="discom_table" class="datatables-basic table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('message.Action') }}</th>
                        <th>DISCOM Name</th>
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
                <h4 class="text-center mb-0" id="exampleModalTitle">Add DISCOM</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-1" id="body">
                <form id="form" class="form" action="javascript:void(0);" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-12 col-md-12 mb-1 custom-input-group">
                            <input type="hidden" name="discom_id" id="discom_id" value="">
                            <label class="form-label" for="discom_name">DISCOM Name<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="discom_name" id="discom_name" placeholder="DISCOM Name *">
                            <span class="invalid-feedback d-block" id="error_discom_name" role="alert"></span>
                        </div>
                        <div class="col-12 col-md-12 mb-1 custom-input-group">
                            <label class="form-label" for="address">Address<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="address" id="address" placeholder="Address *">
                            <span class="invalid-feedback d-block" id="error_address" role="alert"></span>
                        </div>


                        <div class="col-12 col-md-12 mb-1 mt-2 custom-input-group">
                            <h4>Sales Order PDFs</h4>
                        </div>
                        @php $last_group = ''; @endphp
                        @foreach(salespdfs() as $key => $value)
                        @if($last_group != '' && $last_group != $value['group'])
                        <div class="col-12 col-md-12 mb-1 custom-input-group">
                            <hr />
                        </div>
                        @endif
                        <div class="col-12 col-md-12 mb-1 custom-input-group">
                            <div class="form-check">
                                <label class="form-check-label text-nowrap" for="{{ $value['id'] }}"><b>{{ $value['name'] }}</b></label>
                                <input type="checkbox" class="form-check-input" name="pdfs[]" value="{{ $value['id'] }}" id="{{ $value['id'] }}">
                            </div>
                        </div>
                        @php $last_group = $value['group']; @endphp
                        @endforeach



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
    const URL = "{{route('discom.index')}}";

    var table = '';
    $(function() {
        table = $('#discom_table').DataTable({
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
                    data: 'discom_name',
                    name: 'discom_name'
                },
                {
                    data: 'address',
                    name: 'address'
                }
            ],
            initComplete: function(settings, json) {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                })
            }
        });
    });

    $(document).on('click', '.save', function() {
        $("#form").validate({
            rules: {
                discom_name: {
                    required: true,
                },
                address: {
                    required: true,
                }
            },
            messages: {
                discom_name: {
                    required: "Enter DISCOM Name"
                },
                address: {
                    required: "Enter Address"
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
        if ($("#form").valid()) {
            var formData = new FormData($("#form")[0]);
            $.ajax({
                type: "POST",
                url: "{{route('discom.store')}}",
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
        $("#discom_id").val("");
        $("#error_discom_name").html("");
        $("#error_address").html("");
        $("#exampleModalTitle").html("Add DISCOM");
    });

    $(document).on('click', '.edit', function() {
        var id = $(this).data('id');
        var url = "{{route('discom.edit','id')}}".replace('id', id);
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
                    $("#exampleModalTitle").html("Edit DISCOM");
                    $("#discom_name").val(data.result.discom_name);
                    $("#address").val(data.result.address);

                    var selectedPdfs = JSON.parse(data.result.selected_pdfs);

                    if (selectedPdfs && selectedPdfs.length > 0) {
                        selectedPdfs.forEach(function(pdf) {
                            if (typeof pdf.id === "string") {
                                $("#" + pdf.id).prop('checked', true);
                            } else {
                                console.error("Invalid pdf id:", pdf.id);
                            }
                        });
                    }

                    $("#discom_id").val(id);
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
                            } else {
                                toastr.error("{{ __('message.Something went wrong. Please try again.') }}", "{{ __('message.Error') }}");
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