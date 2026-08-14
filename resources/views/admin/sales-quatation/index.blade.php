@extends('layouts.app')
@section('title', 'Sales Quatation')
@section('content')
    <style>
        .nav-pills .type-tab {
            color: #6e6b7b;
            border: 1px solid transparent;
            box-shadow: none !important;
        }
        .nav-pills .type-tab:hover,
        .nav-pills .type-tab:focus {
            color: var(--mainColor);
            background-color: rgba(239, 127, 27, 0.12);
            border-color: transparent;
        }
        .nav-pills .type-tab.active {
            color: #fff;
            background-color: var(--mainColor);
            border-color: transparent;
        }
        .nav-pills .type-tab.active:focus,
        .nav-pills .type-tab.active:active {
            border-color: transparent;
            box-shadow: none !important;
        }
    </style>
    <div class="row">
        <div class="col-12 mb-1">
            <h4 class="content-header-title float-start">{{ __('message.Sales Quatation List') }}</h4>
            @can('sales-quatation-create')
                <a role="button" class="btn btn-sm btn-primary float-end" href="{{ route('sales-quatation.create') }}"><i
                        class="fa fa-plus me-25"></i> {{ __('message.Add New') }}</a>
            @endcan
        </div>

        <div class="col-12">
            <ul class="nav nav-pills" id="typeTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active type-tab" data-type="" href="javascript:void(0);">All</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link type-tab" data-type="trading" href="javascript:void(0);">B2B</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link type-tab" data-type="resident" href="javascript:void(0);">Resident With Subsidy</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link type-tab" data-type="roof" href="javascript:void(0);">Solar RoofTop</a>
                </li>
            </ul>
        </div>
        <div class="col-12">
            <div class="card p-1">
            <div class="row">
                <div class="col-12">
                    <h3>Filter</h3>
                </div>
                <div class="col-sm-12 col-md-3 col-lg-2 custom-input-group">
                    <label class="form-label" for="from_date">From Date</label>
                    <input type="text" class="form-control flatpickr-date" name="date" id="from_date" placeholder="dd-mm-yyyy">
                </div>
                <div class="col-sm-12 col-md-3 col-lg-2 custom-input-group">
                    <label class="form-label" for="to_date">To Date</label>
                    <input type="text" class="form-control flatpickr-date" name="date" id="to_date" placeholder="dd-mm-yyyy">
                </div>
                <div class="col-sm-12 col-md-6 col-lg-2 custom-input-group">
                    <label class="form-label" for="consumer">Name / Mobile</label>
                    <input type="text" class="form-control" name="consumer" id="consumer" placeholder="Name / Mobile">
                </div>
                <div class="col-sm-12 col-md-3 col-lg-2 custom-input-group">
                    <label class="form-label" for="assign">Agent / Sales Person</label>
                    <select class="form-select select2" name="assign" id="assign">
                        <option value="" selected>ALL</option>
                        @foreach($agentSalesPerson as $value)
                        <option value="{{$value->id}}" {{ (isset($lead) && $lead->agent_sales_person_id == $value->id ) ? 'selected' : '' }}>{{ $value->name}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-sm-12 col-md-3 col-lg-2 custom-input-group">
                    <label class="form-label" for="current_status">Status</label>
                    <select class="form-select select2" name="current_status" id="current_status">
                        <option value="" selected>ALL</option>
                        @php  $salesQuatationStatus = salesQuotationStatus(); @endphp
                        @foreach($salesQuatationStatus as $k => $v):
                            <option value="{{ $v['id'] }}">{{ $v['name'] }}</option>
                        @endforeach;
                    </select>
                </div>

                <div class="col-sm-12 col-md-4 col-lg-12 custom-input-group pt-1">
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-gradient-primary btn-sm filter" type="button" data-bs-toggle="tooltip" data-placement="top" title="Click to Filter">
                            <i data-feather='search'></i>
                        </button>
                        <button class="btn btn-gradient-danger btn-sm reset ms-1" type="reset" data-bs-toggle="tooltip" data-placement="top" title=" Click to Reset Filter">
                            <i data-feather='x'></i>
                        </button>
                        <button class="btn btn-gradient-success btn-sm download ms-1" type="button" data-bs-toggle="tooltip" data-placement="top" title="Click to Download Excel">
                            <i data-feather='download'></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        </div>

        <div class="col-12">
            <div class="card p-1 mb-0">
                <table id="table" class="datatables-basic table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('message.Action') }}</th>
                            <th>Status</th>
                            <th>{{ __('message.Type') }}</th>
                            <th>{{ __('message.Name') }}</th>
                            <th>{{ __('message.Mobile') }}</th>
                            <th>{{ __('message.Address') }}</th>
                            <th>{{ __('message.Date') }}</th>
                            <th>Agent</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-transparent border-bottom">
                    <h4 class="text-center mb-0" id="exampleModalTitle">{{ __('message.Details') }}</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-2" id="body">

                </div>
            </div>
        </div>
    </div>
@endsection

@section('pagescript')
    <script type="text/javascript">
        'use strict';
        const URL = "{{ route('sales-quatation.index') }}";
        var table = '';
        var form_type = '';
        $(function() {
            table = $('#table').DataTable({
                ajax: {
                    url: URL,
                    data: function(d) {
                        d.mstatus = '';
                        d.consumer = $('#consumer').val();
                        d.form_type = form_type;
                        d.assign = $('#assign').val();
                        d.from_date = $('#from_date').val();
                        d.to_date = $('#to_date').val();
                        d.current_status = $('#current_status').val();
                    }
                },
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
                        data: 'current_status',
                        name: 'current_status'
                    },
                    {
                        data: 'form_type',
                        name: 'form_type'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'mobile',
                        name: 'mobile'
                    },
                    {
                        data: 'address',
                        name: 'address'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'agent_sales_person.name',
                        name: 'agent_sales_person.name'
                    },
                ],
                initComplete: function(settings, json) {
                    var tooltipTriggerList = [].slice.call(document.querySelectorAll(
                        '[data-bs-toggle="tooltip"]'));
                    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                        return new bootstrap.Tooltip(tooltipTriggerEl)
                    })
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
                                    toastr.success("{{ __('message.Deleted successfully.') }}",
                                        "{{ __('message.Success') }}");
                                } else if (response.data.status == false && response.data.server_error) {
                                    toastr.error(response.data.server_error, "{{ __('message.Error') }}");
                                } else {
                                    toastr.error(
                                        "{{ __('message.Something went wrong. Please try again.') }}",
                                        "{{ __('message.Error') }}");
                                }
                            })
                            .catch(function() {
                                toastr.error("{{ __('message.Something went wrong. Please try again.') }}",
                                    "{{ __('message.Error') }}");
                            });
                    } else {
                        Swal.fire({
                            text: "{{ __('message.Your data is safe.') }}"
                        });
                    }
                });
        });

        $(document).on('click', '.view', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            var url = "{{ route('sales-quatation.show', 'id') }}".replace('id', id);
            $("#exampleModal").modal("show");
            $.ajax({
                url: url,
                type: 'get',
                datatype: 'json',
                data: {
                    "_token": "{{ csrf_token() }}",
                },
                success: function(response) {
                    $("#body").html(response.html);
                    if (feather) {
                        feather.replace({
                            width: 14,
                            height: 14
                        });
                    }
                }
            });
        });

        $(document).on('click', '.filter', function() {
            table.draw();
        });

        $(document).on('click', '.type-tab', function() {
            $('.type-tab').removeClass('active');
            $(this).addClass('active');
            form_type = $(this).data('type');
            table.draw();
        });

        $(document).on('click', '.download', function() {
            $.ajax({
                url: "{{route('sales-quatation-export')}}",
                type: 'POST',
                datatype: 'json',
                data: {
                    "from_date": $('#from_date').val(),
                    "to_date": $('#to_date').val(),
                    "consumer": $('#consumer').val(),
                    "form_type": form_type,
                    "assign": $('#assign').val(),
                    "current_status": $('#current_status').val(),
                    "_token": "{{ csrf_token() }}",
                },
                cache: false,
                xhr: function() {
                    var xhr = new XMLHttpRequest();
                    xhr.onreadystatechange = function() {
                        if (xhr.readyState == 2) {
                            if (xhr.status == 200) {
                                xhr.responseType = "blob";
                            } else {
                                xhr.responseType = "text";
                            }
                        }
                    };
                    return xhr;
                },
                success: function(data) {
                    var blob = new Blob([data], {
                        type: "application/octetstream"
                    });
                    var fileName = 'Sales_Quatation.xlsx';
                    var isIE = false || !!document.documentMode;
                    if (isIE) {
                        window.navigator.msSaveBlob(blob, fileName);
                    } else {
                        var url = window.URL || window.webkitURL;
                        var link = url.createObjectURL(blob);
                        var a = $("<a />");
                        a.attr("download", fileName);
                        a.attr("href", link);
                        $("body").append(a);
                        a[0].click();
                        $("body").remove(a);
                    }
                }
            });
        });

        $(document).on('click', '.reset', function() {
            $('#from_date').val('');
            $('#to_date').val('');
            $('#consumer').val('');
            $('#assign').val('');
            $('#current_status').val('');

            form_type = '';
            $('.type-tab').removeClass('active');
            $('.type-tab[data-type=""]').addClass('active');

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

            $('.select2').select2();
            table.draw();
        });
        flatpickr('.flatpickr-date', {
            enableTime: false,
            dateFormat: 'd-m-Y',
            defaultDate: '',
        });
        $(document).on('click', '.change-status', function() {
            let status = $(this).data('status');
            let id = $(this).data('id');
            var url = "{{ route('sales-quatation-status') }}";
            Swal.fire({
                    title: "{{ __('message.Are you sure?') }}",
                    text: "{{ __('message.You won`t be able to revert this!') }}",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: "Yes, Change it!",
                    customClass: {
                        confirmButton: 'btn btn-primary',
                        cancelButton: 'btn btn-outline-danger ms-1'
                    },
                    buttonsStyling: false
                })
                .then(function(result) {
                    if (result.value) {
                        $.ajax({
                            url: url,
                            type: 'POST',
                            datatype: 'json',
                            data: {
                                "id": id,
                                "status": status,
                                "_token": "{{ csrf_token() }}",
                            },
                            success: function(response) {
                                if (response.status) {
                                    table.ajax.reload(null, false);
                                    toastr.success(response.message, 'Success');
                                } else {
                                    toastr.error(response.server_error, 'Opps!');

                                }
                            }
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
