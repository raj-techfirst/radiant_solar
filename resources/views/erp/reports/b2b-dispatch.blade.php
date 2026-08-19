@extends('layouts.app')
@section('title', 'B2B Dispatch Report')
@section('content')
<div class="row">
    <div class="col-12 mb-1">
        <h4 class="content-header-title float-start">B2B Dispatch Report</h4>
    </div>
    <div class="col-12 mt-2">
        <div class="card p-1">
            <div class="row">
                <div class="col-12">
                    <h3>Filter</h3>
                </div>

                <div class="col-sm-12 col-md-6 col-lg-2 pe-50 custom-input-group">
                    <label class="form-label" for="consumer">Name</label>
                    <input type="text" class="form-control" name="consumer" id="consumer" placeholder="Name / Mobile">
                </div>
                <div class="col-sm-12 col-md-3 col-lg-3 ps-50 pe-50 custom-input-group">
                    <label class="form-label" for="agent_sales_person_id">Agent</label>
                    <select class="form-select select2" name="agent_sales_person_id" id="agent_sales_person_id">
                        <option value="" selected>ALL Agent</option>
                        @foreach($agentSalesPerson as $value)
                        <option value="{{$value->id}}">{{ $value->name}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-sm-12 col-md-3 col-lg-3 ps-50 custom-input-group pt-1">
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-gradient-primary btn-sm filter" type="button" data-bs-toggle="tooltip" data-placement="top" title="Click to Filter">
                            <i data-feather='search'></i>
                        </button>
                        <button class="btn btn-gradient-danger btn-sm reset ms-50" type="reset" data-bs-toggle="tooltip" data-placement="top" title=" Click to Reset Filter">
                            <i data-feather='x'></i>
                        </button>
                        <button data-type="excel" class="btn btn-gradient-success btn-sm download ms-50" type="button" data-bs-toggle="tooltip" data-placement="top" title="Click to Download Excel">
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
                <table id="table" class="datatables-basic table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Mobile</th>
                            <th>Quotation Date</th>
                            <th>Agent</th>
                            <th>Dispatch Date</th>
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

@endsection

@section('pagescript')
<script type="text/javascript">
    'use strict';
    const URL = "{{route('b2b-dispatch')}}";
    var table = '';
    var prevQuoteId = null;
    $(function() {

        table = $('#table').DataTable({
            ajax: {
                url: URL,
                data: function(d) {
                    d.consumer = $('#consumer').val();
                    d.agent_sales_person_id = $('#agent_sales_person_id').val();
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
                    data: 'quotation_date',
                    name: 'quotation_date'
                },
                {
                    data: 'agent_name',
                    name: 'agent_name'
                },
                {
                    data: 'dispatch_date',
                    name: 'dispatch_date'
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
                    data: 'gst',
                    name: 'gst'
                },
                {
                    data: 'total_taxable',
                    name: 'total_taxable'
                }
            ],
            createdRow: function(row, data, dataIndex) {
                if (data.id === prevQuoteId) {
                    var $cells = $(row).find('td');
                    $cells.eq(1).text('');
                    $cells.eq(2).text('');
                    $cells.eq(3).text('');
                    $cells.eq(4).text('');
                    $cells.eq(5).text('');
                    $cells.eq(9).text('');
                    $cells.eq(10).text('');
                }
                prevQuoteId = data.id;
            },
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
    $(document).on('click', '.download', function() {
        $.ajax({
            url: "{{route('b2b-dispatch')}}",
            type: 'GET',
            datatype: 'json',
            data: {
                "download": "excel",
                "consumer": $('#consumer').val(),
                "agent_sales_person_id": $('#agent_sales_person_id').val(),
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

                var fileName = 'b2b-dispatch.xlsx';

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
        $('#consumer').val('');
        $('#agent_sales_person_id').val('');
        $('.select2').select2();
        table.draw();
    });
</script>

@endsection
