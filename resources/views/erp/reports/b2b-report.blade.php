@extends('layouts.app')
@section('title', 'B2B Report')
@section('content')
<style>
    .nav-pills .b2b-tab {
        color: #6e6b7b;
        border: 1px solid transparent;
        box-shadow: none !important;
    }
    .nav-pills .b2b-tab:hover,
    .nav-pills .b2b-tab:focus {
        color: var(--mainColor);
        background-color: rgba(239, 127, 27, 0.12);
        border-color: transparent;
    }
    .nav-pills .b2b-tab.active {
        color: #fff;
        background-color: var(--mainColor);
        border-color: transparent;
    }
    .nav-pills .b2b-tab.active:focus,
    .nav-pills .b2b-tab.active:active {
        border-color: transparent;
        box-shadow: none !important;
    }
    .nav-pills .b2b-tab .badge {
        color: #fff;
    }
</style>
<div class="row">
    <div class="col-12 mb-1">
        <h4 class="content-header-title float-start">B2B Report</h4>
    </div>
</div>
<div class="card p-1">
    <div class="row">
        <div class="col-12">
            <ul class="nav nav-pills" id="b2bTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active b2b-tab" id="rate-tab" data-bs-toggle="tab" href="#rate" role="tab" aria-controls="rate" aria-selected="true">B2B Rate Report <span class="badge bg-gradient-primary ms-50 mt-25">{{ $b2bRate }}</span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link b2b-tab" id="accept-tab" data-bs-toggle="tab" href="#accept" role="tab" aria-controls="accept" aria-selected="false">B2B Accept Report <span class="badge bg-gradient-primary ms-50 mt-25">{{ $b2bAccept }}</span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link b2b-tab" id="dispatch-tab" data-bs-toggle="tab" href="#dispatch" role="tab" aria-controls="dispatch" aria-selected="false">B2B Dispatch Report <span class="badge bg-gradient-primary ms-50 mt-25">{{ $b2bDispatch }}</span></a>
                </li>
            </ul>
        </div>
    </div>
</div>
<div class="tab-content">
    <div class="tab-pane fade show active" id="rate" role="tabpanel" aria-labelledby="rate-tab">
        <div class="col-12">
            <div class="card p-1">
                <div class="row">
                    <div class="col-12">
                        <h3>Filter</h3>
                    </div>

                    <div class="col-sm-12 col-md-6 col-lg-2 pe-50 custom-input-group">
                        <label class="form-label" for="rate_consumer">Name</label>
                        <input type="text" class="form-control" name="consumer" id="rate_consumer" placeholder="Name / Mobile">
                    </div>
                    <div class="col-sm-12 col-md-3 col-lg-3 ps-50 pe-50 custom-input-group">
                        <label class="form-label" for="rate_agent_sales_person_id">Agent</label>
                        <select class="form-select select2" name="agent_sales_person_id" id="rate_agent_sales_person_id">
                            <option value="" selected>ALL Agent</option>
                            @foreach($agentSalesPerson as $value)
                            <option value="{{$value->id}}">{{ $value->name}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-sm-12 col-md-3 col-lg-3 ps-50 custom-input-group pt-1">
                        <div class="d-flex justify-content-end">
                            <button class="btn btn-gradient-primary btn-sm rate-filter" type="button" data-bs-toggle="tooltip" data-placement="top" title="Click to Filter">
                                <i data-feather='search'></i>
                            </button>
                            <button class="btn btn-gradient-danger btn-sm rate-reset ms-50" type="reset" data-bs-toggle="tooltip" data-placement="top" title=" Click to Reset Filter">
                                <i data-feather='x'></i>
                            </button>
                            <button class="btn btn-gradient-success btn-sm rate-download ms-50" type="button" data-bs-toggle="tooltip" data-placement="top" title="Click to Download Excel">
                                <i data-feather='download'></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card p-1">
                <div class="table-responsive">
                    <table id="rate_table" class="datatables-basic table table-hover">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Mobile</th>
                            <th>KW</th>
                            <th>Lead Date</th>
                            <th>Agent</th>
                            <th>Item Detail</th>
                            <th>Nos</th>
                            <th>Rate</th>
                            <th>GST</th>
                            <th>Total Taxable</th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane fade" id="accept" role="tabpanel" aria-labelledby="accept-tab">
        <div class="col-12">
            <div class="card p-1">
                <div class="row">
                    <div class="col-12">
                        <h3>Filter</h3>
                    </div>

                    <div class="col-sm-12 col-md-6 col-lg-2 pe-50 custom-input-group">
                        <label class="form-label" for="accept_consumer">Name</label>
                        <input type="text" class="form-control" name="consumer" id="accept_consumer" placeholder="Name / Mobile">
                    </div>
                    <div class="col-sm-12 col-md-3 col-lg-3 ps-50 pe-50 custom-input-group">
                        <label class="form-label" for="accept_agent_sales_person_id">Agent</label>
                        <select class="form-select select2" name="agent_sales_person_id" id="accept_agent_sales_person_id">
                            <option value="" selected>ALL Agent</option>
                            @foreach($agentSalesPerson as $value)
                            <option value="{{$value->id}}">{{ $value->name}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-sm-12 col-md-3 col-lg-3 ps-50 custom-input-group pt-1">
                        <div class="d-flex justify-content-end">
                            <button class="btn btn-gradient-primary btn-sm accept-filter" type="button" data-bs-toggle="tooltip" data-placement="top" title="Click to Filter">
                                <i data-feather='search'></i>
                            </button>
                            <button class="btn btn-gradient-danger btn-sm accept-reset ms-50" type="reset" data-bs-toggle="tooltip" data-placement="top" title=" Click to Reset Filter">
                                <i data-feather='x'></i>
                            </button>
                            <button class="btn btn-gradient-success btn-sm accept-download ms-50" type="button" data-bs-toggle="tooltip" data-placement="top" title="Click to Download Excel">
                                <i data-feather='download'></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card p-1">
                <div class="table-responsive">
                    <table id="accept_table" class="datatables-basic table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Mobile</th>
                                <th>Address</th>
                                <th>GST No</th>
                                <th>Total Amount</th>
                                <th>Quotation Date</th>
                                <th>Agent</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane fade" id="dispatch" role="tabpanel" aria-labelledby="dispatch-tab">
        <div class="col-12">
            <div class="card p-1">
                <div class="row">
                    <div class="col-12">
                        <h3>Filter</h3>
                    </div>

                    <div class="col-sm-12 col-md-6 col-lg-2 pe-50 custom-input-group">
                        <label class="form-label" for="dispatch_consumer">Name</label>
                        <input type="text" class="form-control" name="consumer" id="dispatch_consumer" placeholder="Name / Mobile">
                    </div>
                    <div class="col-sm-12 col-md-3 col-lg-3 ps-50 pe-50 custom-input-group">
                        <label class="form-label" for="dispatch_agent_sales_person_id">Agent</label>
                        <select class="form-select select2" name="agent_sales_person_id" id="dispatch_agent_sales_person_id">
                            <option value="" selected>ALL Agent</option>
                            @foreach($agentSalesPerson as $value)
                            <option value="{{$value->id}}">{{ $value->name}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-sm-12 col-md-3 col-lg-3 ps-50 custom-input-group pt-1">
                        <div class="d-flex justify-content-end">
                            <button class="btn btn-gradient-primary btn-sm dispatch-filter" type="button" data-bs-toggle="tooltip" data-placement="top" title="Click to Filter">
                                <i data-feather='search'></i>
                            </button>
                            <button class="btn btn-gradient-danger btn-sm dispatch-reset ms-50" type="reset" data-bs-toggle="tooltip" data-placement="top" title=" Click to Reset Filter">
                                <i data-feather='x'></i>
                            </button>
                            <button class="btn btn-gradient-success btn-sm dispatch-download ms-50" type="button" data-bs-toggle="tooltip" data-placement="top" title="Click to Download Excel">
                                <i data-feather='download'></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card p-1">
                <div class="table-responsive">
                    <table id="dispatch_table" class="datatables-basic table table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Mobile</th>
                                <th>Address</th>
                                <th>GST No</th>
                                <th>Total Amount</th>
                                <th>Quotation Date</th>
                                <th>Agent</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('pagescript')
<script type="text/javascript">
    'use strict';
    const ACCEPT_URL = "{{route('b2b-accept')}}";
    const DISPATCH_URL = "{{route('b2b-dispatch')}}";
    const RATE_URL = "{{route('b2b-rate')}}";
    var accept_table = '';
    var dispatch_table = '';
    var rate_table = '';

    function downloadExcel(url, filename, prefix) {
        $.ajax({
            url: url,
            type: 'GET',
            datatype: 'json',
            data: {
                "download": "excel",
                "consumer": $('#' + prefix + '_consumer').val(),
                "agent_sales_person_id": $('#' + prefix + '_agent_sales_person_id').val(),
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
                var isIE = false || !!document.documentMode;
                if (isIE) {
                    window.navigator.msSaveBlob(blob, filename);
                } else {
                    var url = window.URL || window.webkitURL;
                    var link = url.createObjectURL(blob);
                    var a = $("<a />");
                    a.attr("download", filename);
                    a.attr("href", link);
                    $("body").append(a);
                    a[0].click();
                    $("body").remove(a);
                }
            }
        });
    }

    function initAcceptTable() {
        if (accept_table !== '') {
            accept_table.draw();
            return;
        }
        accept_table = $('#accept_table').DataTable({
            ajax: {
                url: ACCEPT_URL,
                data: function(d) {
                    d.consumer = $('#accept_consumer').val();
                    d.agent_sales_person_id = $('#accept_agent_sales_person_id').val();
                }
            },
            processing: true,
            serverSide: true,
            fixedHeader: false,
            scroll: false,
            aLengthMenu: [
                [20, -1],
                [20, "All"],
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
                    data: 'gst_no',
                    name: 'gst_no'
                },
                {
                    data: 'total_amount',
                    name: 'total_amount'
                },
                {
                    data: 'quotation_date',
                    name: 'quotation_date'
                },
                {
                    data: 'agent_name',
                    name: 'agent_name'
                }
            ],
            initComplete: function(settings, json) {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                })
            }
        });
    }

    function initDispatchTable() {
        if (dispatch_table !== '') {
            dispatch_table.draw();
            return;
        }
        dispatch_table = $('#dispatch_table').DataTable({
            ajax: {
                url: DISPATCH_URL,
                data: function(d) {
                    d.consumer = $('#dispatch_consumer').val();
                    d.agent_sales_person_id = $('#dispatch_agent_sales_person_id').val();
                }
            },
            processing: true,
            serverSide: true,
            fixedHeader: false,
            scroll: false,
            aLengthMenu: [
                [20, -1],
                [20, "All"],
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
                    data: 'gst_no',
                    name: 'gst_no'
                },
                {
                    data: 'total_amount',
                    name: 'total_amount'
                },
                {
                    data: 'quotation_date',
                    name: 'quotation_date'
                },
                {
                    data: 'agent_name',
                    name: 'agent_name'
                }
            ],
            initComplete: function(settings, json) {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                })
            }
        });
    }

    function initRateTable() {
        if (rate_table !== '') {
            rate_table.draw();
            return;
        }
        rate_table = $('#rate_table').DataTable({
            ajax: {
                url: RATE_URL,
                data: function(d) {
                    d.consumer = $('#rate_consumer').val();
                    d.agent_sales_person_id = $('#rate_agent_sales_person_id').val();
                }
            },
            processing: true,
            serverSide: true,
            fixedHeader: false,
            scroll: false,
            aLengthMenu: [
                [20, -1],
                [20, "All"],
            ],
            order: [],
            columns: [{
                    data: 'sr_no',
                    name: 'sr_no'
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
                    data: 'kw',
                    name: 'kw'
                },
                {
                    data: 'lead_date',
                    name: 'lead_date'
                },
                {
                    data: 'agent_name',
                    name: 'agent_name'
                },
                {
                    data: 'item_detail',
                    name: 'item_detail'
                },
                {
                    data: 'nos',
                    name: 'nos'
                },
                {
                    data: 'rate',
                    name: 'rate'
                },
                {
                    data: 'item_gst',
                    name: 'item_gst'
                },
                {
                    data: 'total_taxable',
                    name: 'total_taxable'
                }
            ],
            initComplete: function(settings, json) {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                })
            }
        });
    }

    $(document).on('shown.bs.tab', 'a[data-bs-toggle="tab"]', function(e) {
        var target = $(e.target).attr('href');
        if (target == '#accept') {
            initAcceptTable();
        } else if (target == '#dispatch') {
            initDispatchTable();
        } else if (target == '#rate') {
            initRateTable();
        }
    });

    $(document).on('click', '.accept-filter', function() {
        initAcceptTable();
    });
    $(document).on('click', '.dispatch-filter', function() {
        initDispatchTable();
    });
    $(document).on('click', '.rate-filter', function() {
        initRateTable();
    });

    $(document).on('click', '.accept-download', function() {
        downloadExcel(ACCEPT_URL, 'b2b-accept.xlsx', 'accept');
    });
    $(document).on('click', '.dispatch-download', function() {
        downloadExcel(DISPATCH_URL, 'b2b-dispatch.xlsx', 'dispatch');
    });
    $(document).on('click', '.rate-download', function() {
        downloadExcel(RATE_URL, 'b2b-rate.xlsx', 'rate');
    });

    $(document).on('click', '.accept-reset', function() {
        $('#accept_consumer').val('');
        $('#accept_agent_sales_person_id').val('');
        $('.select2').select2();
        initAcceptTable();
    });
    $(document).on('click', '.dispatch-reset', function() {
        $('#dispatch_consumer').val('');
        $('#dispatch_agent_sales_person_id').val('');
        $('.select2').select2();
        initDispatchTable();
    });
    $(document).on('click', '.rate-reset', function() {
        $('#rate_consumer').val('');
        $('#rate_agent_sales_person_id').val('');
        $('.select2').select2();
        initRateTable();
    });

    $(document).ready(function() {
        initRateTable();
    });
</script>

@endsection