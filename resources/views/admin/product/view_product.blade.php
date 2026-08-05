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
            <table id="table" class="datatables-basic table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('message.Action') }}</th>
                        <th>{{ __('message.Category') }}</th>
                        <th>{{ __('message.Product') }}</th>
                        <th>{{ __('message.Price') }}</th>
                        <th>{{ __('message.Description') }}</th>
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
                <h4 class="text-center mb-0" id="exampleModalTitle">{{ __('message.Add Product') }}</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-1" id="body">
                <form id="form" action="javascript:void(0);" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-12 mb-1 custom-input-group">
                            <label class="form-label" for="category_id">{{ __('message.Category') }}</label>
                            <select class="form-control select2 select2-sm custom-select2" name="category_id" id="category_id">
                                <option selected disabled>{{ __('message.-- Select --') }}</option>
                                @foreach($category as $value)
                                <option value="{{$value->id}}" {{ (isset($lead) && ($lead->category_id == $value->id) ? 'selected' : '')}}>{{$value->category_name}}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 mb-1 custom-input-group">
                            <input type="hidden" name="product_id" id="product_id" value="">
                            <input type="text" class="form-control" name="product_name" id="product_name" placeholder="{{ __('message.Product') }} *">
                            <span class="invalid-feedback d-block" id="error_product_name" role="alert"></span>
                        </div>
                        <div class="col-12 mb-1 custom-input-group">
                            <input type="number" class="form-control" name="product_price" id="product_price" placeholder="{{ __('message.Price') }}">
                            <span class="invalid-feedback d-block" id="error_product_price" role="alert"></span>
                        </div>
                        <div class="col-12 mb-1 custom-input-group">
                            <textarea class="form-control" name="description" id="description" placeholder="{{ __('message.Type here..') }}">{{ ((isset($product) && isset($product->description)) ? $product->description : '')  }}</textarea>
                        </div>

                        <div class="col-12">
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
        table = $('#table').DataTable({
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
                    data: 'category_id',
                    name: 'category_id'
                },
                {
                    data: 'product_name',
                    name: 'product_name'
                },
                {
                    data: 'product_price',
                    name: 'product_price'
                },
                {
                    data: 'description',
                    name: 'description'
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
        var formData = new FormData($("#form")[0]);
        if ($("#product_name").val() != "") {
            $.ajax({
                type: "POST",
                url: "{{route('product.store')}}",
                data: formData,
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $("#error_product_name").html(' ');
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
                        }, 1000);
                    }
                }
            });
        } else {
            $("#form").validate({
                rules: {
                    product_name: {
                        required: true,
                    },
                },
                messages: {
                    product_name: {
                        required: "{{ __('message.Enter product') }}"
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

    $("#inlineModal").on("hidden.bs.modal", function(e) {
        $(this).find('form').trigger('reset');
        $("#category_id").val("").trigger('change');
        $("#product_id").val("");
        $("#product_name-error").html("");
        $("#description").text("");
        $("#exampleModalTitle").html("{{ __('message.Add Item') }}");

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
                    $("#exampleModalTitle").html("{{ __('message.Edit Product') }}");
                    $("#product_id").val(id);
                    $("#category_id").val(data.result.category_id).trigger('change');
                    $("#product_name").val(data.result.product_name);
                    $("#product_price").val(data.result.product_price);
                    $("#description").text(data.result.description);
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
                                toastr.warning("{{ __('message.This product has been used.') }}", "{{ __('message.Warning') }}");
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