@extends('layouts.app')
@section('title', 'Item')
@section('content')
<div class="row">
    <div class="col-12 mb-1">
        <h4 class="content-header-title float-start">Item List</h4>

        @can('product-create')
        <button type="button" data-bs-toggle="modal" data-bs-target="#inlineModal" class="btn btn-sm btn-primary float-end"><i class="fa fa-plus me-25"></i> {{ __('message.Add New') }}</button>
        @endcan
    </div>
    <div class="col-12">
        <div class="card p-1">
            <table id="product" class="datatables-basic table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('message.Action') }}</th>
                        <th>Item Category</th>
                        <th>Item Name</th>
                        <!-- <th>Item Code</th> -->
                        <th>HSN Code</th>
                        <th>GST Rate</th>
                        <th>Unit</th>
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
                <h4 class="text-center mb-0" id="exampleModalTitle">Add Item</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-1" id="body">
                <form id="form" class="form" action="javascript:void(0);" method="POST">
                    @csrf
                    <div class="row">
                    <div class="col-12 col-md-12 mb-1 custom-input-group">
                            <label class="form-label" for="category_id">Category<span class="text-danger">*</span></label>
                            <select class="form-select custom-select2 select2" name="category_id" id="category_id" required>
                                <option value="" selected disabled>-- Select --</option>
                                @foreach ($categories as $ck => $cv)
                                <option value="{{ $cv->id }}">{{ $cv->category_name}}</option>
                                @endforeach
                            </select>
                            <span class="invalid-feedback d-block" id="error_category_id" role="alert"></span>
                        </div>
                        <div class="col-12 col-md-12 mb-1 custom-input-group">
                            <input type="hidden" name="product_id" id="product_id" value="">
                            <label class="form-label" for="name">Item Name<span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="name" placeholder="Item Name *">
                            <span class="invalid-feedback d-block" id="error_name" role="alert"></span>
                        </div>
                        <div class="col-12 col-md-12 mb-1 custom-input-group">
                            <label class="form-label" for="item_code">Item Code</label>
                            <input type="text" class="form-control" name="item_code" id="item_code" placeholder="Item Code">
                            <span class="invalid-feedback d-block" id="error_item_code" role="alert"></span>
                        </div>
                        <div class="col-12 col-md-12 mb-1 custom-input-group">
                            <label class="form-label" for="hsn_code">HSN Code</label>
                            <input type="text" class="form-control" name="hsn_code" id="hsn_code" placeholder="HSN Code ">
                            <span class="invalid-feedback d-block" id="error_hsn_code" role="alert"></span>
                        </div>
                        <div class="col-12 col-md-12 mb-1 custom-input-group">
                            <label class="form-label" for="gst_rate">GST Rate<span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="gst_rate" id="gst_rate" placeholder="GST Rate *">
                            <span class="invalid-feedback d-block" id="error_gst_rate" role="alert"></span>
                        </div>
                         <div class="col-12 col-md-12 mb-1 custom-input-group">
                            <label class="form-label" for="moq_level">MOQ Level<span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="moq_level" id="moq_level" placeholder="MOQ Level *">
                            <span class="invalid-feedback d-block" id="error_moq_level" role="alert"></span>
                        </div>
                        <div class="col-12 col-md-12 mb-1 custom-input-group">
                            <label class="form-label" for="unit_id">Unit<span class="text-danger">*</span></label>
                            <select class="form-select custom-select2 select2" name="unit_id" id="unit_id" required>
                                <option value="" selected disabled>-- Select --</option>
                                @foreach ($units as $k => $v)
                                <option value="{{ $v->id }}">{{ $v->unit_name}}</option>
                                @endforeach
                            </select>
                            <span class="invalid-feedback d-block" id="error_unit_id" role="alert"></span>
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
    const URL = "{{route('product.index')}}";

    var table = '';
    $(function() {
        table = $('#product').DataTable({
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
                    data: 'cat_name',
                    name: 'cat_name'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                // {
                //     data: 'item_code',
                //     name: 'item_code'
                // },
                {
                    data: 'hsn_code',
                    name: 'hsn_code'
                },
                {
                    data: 'gst_rate',
                    name: 'gst_rate'
                },
                {
                    data: 'unit.unit_name',
                    name: 'unit.unit_name'
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


    $(document).on('click', '.save', function() {
        $("#form").validate({
            rules: {
                name: {
                    required: true,
                },
                // item_code: {
                //     required: true,
                // },
                gst_rate: {
                    required: true,
                },
            },
            messages: {
                name: {
                    required: "Enter item name"
                },
                // item_code: {
                //     required: "Enter item code"
                // },
                gst_rate: {
                    required: "Enter GST rate"
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
        if ($("#form").valid()) {
            var formData = new FormData($("#form")[0]);
            $.ajax({
                type: "POST",
                url: "{{route('product.store')}}",
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
        } else {
            return false;
        }
    });


    $("#inlineModal").on("hidden.bs.modal", function(e) {
        $(this).find('form').trigger('reset');
        $("#product_id").val("");
        $(".invalid-feedback").html("");
        $(".custom-error").html("");
        // $("#name-error").html("");
        // $("#item_code-error").html("");
        // $("#gst_rate-error").html("");
        $("#exampleModalTitle").html("Add Item");
    });

    $(document).on('click', '.edit', function() {
        var id = $(this).data('id');
        var url = "{{route('product.edit','id')}}".replace('id', id);
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
                    $("#exampleModalTitle").html("Edit Item");
                    $("#name").val(data.result.name);
                    $("#unit_id").val(data.result.unit_id);
                    $("#category_id").val(data.result.category_id);
                    $('select').select2();
                    $("#item_code").val(data.result.item_code);
                    $("#hsn_code").val(data.result.hsn_code);
                    $("#gst_rate").val(data.result.gst_rate);
                    $("#product_id").val(id);
                    $("#moq_level").val(data.result.moq_level);
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
