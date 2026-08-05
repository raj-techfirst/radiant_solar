@extends('layouts.app')
@section('title', 'B2B Dispach Report')
@section('content')
<div class="row">
    <div class="col-12 mt-2">
        <div class="card p-1">
            <div class="row">
                <div class="col-12">
                    <h3>Filter</h3>
                </div>

                <div class="col-sm-12 col-md-3 col-lg-2 pe-50 custom-input-group">
                    <label class="form-label" for="warehouse_id">Warehouse</label>
                    <select class="form-select  custom-select2 select2" name="warehouse_id" id="warehouse_id">
                        <option value="" selected disabled>-- Select --</option>
                        @foreach ($warehouse as $value)
                        <option value="{{ $value->id }}">{{ $value->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-12 col-md-6 col-lg-2 ps-50 pe-50 custom-input-group">
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
                        <button data-type="pdf" class="btn btn-gradient-info btn-sm download ms-50" type="button" data-bs-toggle="tooltip" data-placement="top" title="Click to Download PDF">
                            <i data-feather='printer'></i>
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
                            <th>Goods Issue Date</th>
                            <th>Goods Issue No</th>
                            <th>Taxabale amount</th>
                            <th>GST Amount</th>
                            <th>Total Amount</th>
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
    const URL = "{{route('b2b-dispach')}}";
    var table = '';
    $(function() {

        table = $('#table').DataTable({
            ajax: {
                url: URL,
                data: function(d) {
                    d.consumer = $('#consumer').val();
                    d.warehouse_id = $('#warehouse_id').val();
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
            columns: [{
                    data: 'dc.id',
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
                    data: 'goods_issue_date',
                    name: 'goods_issue_date'
                },

                {
                    data: 'goods_issue_no',
                    name: 'goods_issue_no'
                },
                {
                    data: 'taxabale_amount',
                    name: 'taxabale_amount'
                },
                {
                    data: 'gst_amount',
                    name: 'gst_amount',
                },
                {
                    data: 'total_amount',
                    name: 'total_amount',
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

    $(document).on('click', '.filter', function() {
        table.draw();
    });
    $(document).on('click', '.download', function() {
        var type = $(this).attr('data-type');
        $.ajax({
            url: "{{route('b2b-dispach')}}",
            type: 'GET',
            datatype: 'json',
            data: {
                "download": type,
                "type": "pdf",
                "warehouse_id": $('#warehouse_id').val(),
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

                if (type == 'pdf') {
                    var fileName = 'b2b-dispach.pdf';
                } else {
                    var fileName = 'b2b-dispach.xlsx';
                }

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
        $('#warehouse_id').val('');
        $('#consumer').val('');
        $('#agent_sales_person_id').val('');
        $('.select2').select2();
        table.draw();
    });
</script>

@endsection