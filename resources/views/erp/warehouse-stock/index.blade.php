@extends('layouts.app')
@section('title', 'Warehouse Stock')
@section('content')
<div class="row">
    <div class="col-12 mb-50">
        <h4 class="content-header-title float-start">Warehouse Stock</h4>
        <!-- @can('warehouse-stock-create')
        <a href="{{ route('warehouse-stock.create') }}" class="btn btn-sm btn-primary float-end"><i class="fa fa-angle-double-right me-25"></i> Stock Transfer</a>
        @endcan -->
    </div>

    <div class="col-12">
        <div class="card p-1 mb-1">
            <form id="report" action="javascript:void(0)" method="POST">
                @csrf
                <div class="row g-1 pt-25 align-items-end">
                    <div class="col-12 col-md-3 col-lg-2 m-0">
                        <label class="form-label" for="from_date">From Date</label>
                        <input type="text" class="form-control" name="fdate" id="from_date" placeholder="dd-mm-yyyy">
                    </div>
                    <div class="col-12 col-md-3 col-lg-2 m-0">
                        <label class="form-label" for="from_date">To Date</label>
                        <input type="text" class="form-control" name="tdate" id="to_date" placeholder="dd-mm-yyyy">
                    </div>

                    <div class="col-12 col-md-3 col-lg-4 m-0">
                        <label class="form-label" for="warehouse_id">Warehouse</label>
                        <select class="form-select select2" name="warehouse_id" id="warehouse_id">
                            <option value="" selected disabled>-- Select --</option>
                            @foreach ($warehouse as $value)
                            <option value="{{ $value->id }}">{{ $value->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-2 col-lg-2 m-0">
                        <label class="form-label" for="category_id">BOS Category</label>
                        <select class="form-select select2" name="category_id" id="category_id">
                            <option value="" selected disabled>-- All --</option>
                            @foreach ($categories as $ck => $cv)
                            <option value="{{ $cv->id }}">{{ $cv->category_name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-2 col-lg-2 m-0">
                    <label class="form-label" for="type">Panel/Inverter</label>
                    <select class="form-select select2" name="type" id="type">
                        <option value="" selected disabled>-- All --</option>
                        <option value="panel" >Panel</option>
                        <option value="inverter">Inverter</option>
                    </select>
                </div>
                    <div class="col-12 col-md-3 col-lg-3 mb-0 mt-50">
                        <label class="form-label" for="item_id">BOS</label>
                        <select class="form-select select2" name="item_id" id="item_id">
                            <option value="" selected disabled>-- All --</option>
                            @foreach ($item as $me)
                            <option value="{{ $me->id }}">{{ $me->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-5 col-lg-5 mb-0 mt-50">
                        <label class="form-label" for="item_group_id">Panel/Inverter (Items)</label>
                        <select class="form-select select2" name="item_group_id" id="item_group_id">
                            <option value="" selected disabled>-- All --</option>
                            @foreach ($itemGroup as $v)
                            <option value="{{ $v->id }}" data-unit="{{ $v->unit->unit_name }}">{{ $v->item_code }} {{ getItemGropName($v) }}</option>
                            @endforeach
                        </select>
                    </div>


                    <div class="col-12 col-md-4 col-lg-4 text-end mt-50">

                        <button class="btn btn-gradient-primary btn-sm filter" type="button" data-bs-toggle="tooltip" data-placement="top" title="Click to Filter">
                            <i data-feather='search'></i>
                        </button>
                        <button class="btn btn-gradient-danger btn-sm reset ms-50" type="reset" data-bs-toggle="tooltip" data-placement="top" title=" Click to Reset Filter">
                            <i data-feather='x'></i>
                        </button>
                        <button data-type="excel" class="btn btn-gradient-success btn-sm export ms-50" type="submit" data-bs-toggle="tooltip" data-placement="top" title="Click to Download">
                            <i data-feather='download'></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="col-12">
        <div class="card p-1">
            <table id="table" class="datatables-basic table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Action</th>
                        <th>Warehouse</th>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Updated At</th>
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
    <div class="modal-dialog modal-dialog-centered  modal-xl">
        <div class="modal-content" id="modal_content">

        </div>
    </div>
</div>
@endsection

@section('pagescript')
<script type="application/javascript">
    'use strict';
    const URL = "{{route('warehouse-stock.index')}}";

    var table = '';
    $(function() {
        table = $('#table').DataTable({
            ajax: {
                url: URL,
                data: function(d) {
                    d.from_date = $('#from_date').val();
                    d.to_date = $('#to_date').val();
                    d.warehouse_id = $('#warehouse_id').val();
                    d.item_id = $('#item_id').val();
                    d.item_group_id = $('#item_group_id').val();
                    d.category_id = $('#category_id').val();
                    d.type = $('#type').val();
                }
            },
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
                    data: function(row) {
                        return row.warehouse && row.warehouse.name ? row.warehouse.name : '';
                    },
                    name: 'warehouse.name',
                    orderable: false,
                    sortable: false,
                },
                {
                    data: 'item_name',
                    name: 'item_name',
                    orderable: false,
                    sortable: false,
                },
                {
                    data: 'quantity',
                    name: 'quantity',
                },
                {
                    data: 'updated_at',
                    name: 'updated_at',
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

    $(document).on('click', '.filter', function() {
        table.draw();
    });

    $(document).on('click', '.export', function() {
        $('#report').attr('action', "{{route('export-stock')}}");
        $('#report').submit();
    });

    $("#from_date").flatpickr({
        altInput: true,
        altFormat: 'd-m-Y',
        dateFormat: 'Y-m-d'
    });

    $("#to_date").flatpickr({
        altInput: true,
        altFormat: 'd-m-Y',
        dateFormat: 'Y-m-d'
    });

    $(document).on('click', '.reset', function() {
        $('#from_date').val('');
        $('#to_date').val('');
        $('#item_group_id').val('');
        $('#category_id').val('');
        $('#type').val('');

        $("#from_date").flatpickr({
            altInput: true,
            altFormat: 'd-m-Y',
            dateFormat: 'Y-m-d'
        });

        $("#to_date").flatpickr({
            altInput: true,
            altFormat: 'd-m-Y',
            dateFormat: 'Y-m-d'
        });

        $('#warehouse_id').val(null).trigger('change');
        $('#item_id').val(null).trigger('change');

        $('.select-with-add').select2();
        $('.select2').select2();
        table.draw();
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

    $(document).on('click', '.view', function() {
        var id = $(this).data('id');
        var route = "{{route('warehouse-stock.show','id')}}".replace('id', id);
        $("#exampleModal").modal("show");
        $.ajax({
            type: "get",
            url: route,
            dataType: 'json',
            data: {
                "_token": "{{ csrf_token() }}",
            },
            success: function(response) {
                $("#modal_content").html(response.html);
                $('#viewData').DataTable({
                    "pageLength": 25,
                    "fixedHeader": true,
                    "aLengthMenu": [
                        [25, 50, -1],
                        [25, 50, "All"]
                    ],
                    "bScrollCollapse": true,
                });

                if (feather) {
                    feather.replace({
                        width: 14,
                        height: 14
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