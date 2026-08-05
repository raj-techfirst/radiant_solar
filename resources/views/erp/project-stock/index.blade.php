@extends('layouts.app')
@section('title', 'Project Wise Stock')
@section('content')
<div class="row">
    <div class="col-12 mb-50">
        <h4 class="content-header-title float-start">Project Wise Stock</h4>
        @can('project-wise-stock-create')
        <!-- <a href="{{ route('project-wise-stock.create') }}" class="btn btn-sm btn-primary float-end"><i class="fa fa-plus me-25"></i> Add New</a> -->
        @endcan
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

                    <div class="col-12 col-md-3 col-lg-3 m-0">
                        <label class="form-label" for="project_id">Project</label>
                        <select class="form-select select2" name="project_id" id="project_id">
                            <option value="" selected disabled>-- Select --</option>
                            @foreach ($project as $value)
                            <option value="{{ $value->id }}">{{ $value->consumer_name }} | {{ $value->consumer_number }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3 col-lg-3 m-0 d-none">
                        <label class="form-label" for="item_id">Item</label>
                        <select class="form-select select2" name="item_id" id="item_id">
                            <option value="" selected disabled>-- Select --</option>
                            @foreach ($item as $me)
                            <option value="{{ $me->id }}">{{ $me->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-3 col-lg-3 m-0">
                        <label class="form-label" for="installer_id">Installer</label>
                        <select class="form-select select2" name="installer_id" id="installer_id">
                            <option selected disabled>{{ __('message.-- Select --') }}</option>
                            @foreach($installer as $value)
                            <option value="{{ $value->user->id }}" {{ (isset($data) && ($data->installer_id == $value->user->id) ? 'selected' : '')}}>{{ $value->user->name.' '. $value->user->last_name   }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-4 col-lg-2">

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
                        <th>Project</th>
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
                <h4 class="text-center mb-0" id="exampleModalTitle">Add Project Wise</h4>
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
    const URL = "{{route('project-wise-stock.index')}}";

    var table = '';
    $(function() {
        table = $('#table').DataTable({
            ajax: {
                url: URL,
                data: function(d) {
                    d.fdate = $('#from_date').val();
                    d.tdate = $('#to_date').val();
                    d.project_id = $('#project_id').val();
                    d.item_id = $('#item_id').val();
                    d.installer_id = $('#installer_id').val();
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
                    data: 'project_id',
                    name: 'project_id',
                    orderable: false,
                    sortable: false,
                },
                {
                    data: function(row) {
                        return row.warehouse && row.warehouse.name ? row.warehouse.name : '';
                    },
                    name: 'warehouse.name',
                    visible: false,
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
        $('#report').attr('action', "{{route('export-project-stock')}}");
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

        $('#project_id').val(null).trigger('change');
        $('#item_id').val(null).trigger('change');
        $('#installer_id').val(null).trigger('change');

        $('.select-with-add').select2();
        table.draw();
    });

    $(document).on('click', '.view', function() {
        var id = $(this).data('id');
        var route = "{{route('project-wise-stock.show','id')}}".replace('id', id);
        $("#exampleModal").modal("show");
        $.ajax({
            type: "get",
            url: route,
            dataType: 'json',
            data: {
                'id': id,
                "_token": "{{ csrf_token() }}",
            },
            success: function(response) {
                $("#modal_content").html(response.html);
                $('#history_table').DataTable({
                    "pageLength": 10,
                    "fixedHeader": true,
                    "aLengthMenu": [
                        [10, 25, 50, -1],
                        [10, 25, 50, "All"]
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