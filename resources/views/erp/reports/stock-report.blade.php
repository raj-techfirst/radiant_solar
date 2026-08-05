@extends('layouts.app')
@section('title', 'Project Wise Dispach Report')
@section('content')
<div class="row">
    <div class="col-12 mt-2">
        <div class="card p-1">
            <div class="row">
                <div class="col-12">
                    <h3>Filter</h3>
                </div>
                <div class="col-sm-12 col-md-3 col-lg-3 custom-input-group">
                    <label class="form-label" for="warehouse_id">Warehouse</label>
                    <select class="form-select  custom-select2 select2" name="warehouse_id" id="warehouse_id">
                        <option value="0" selected>-- ALL --</option>
                        @foreach ($warehouse as $value)
                        <option value="{{ $value->id }}">{{ $value->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3 col-lg-3 m-0">
                    <label class="form-label" for="category_id">BOS Category</label>
                    <select class="form-select select2" name="category_id" id="category_id">
                        <option value="" selected disabled>-- All --</option>
                        @foreach ($categories as $ck => $cv)
                        <option value="{{ $cv->id }}">{{ $cv->category_name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-3 col-lg-3 m-0">
                    <label class="form-label" for="type">Panel/Inverter</label>
                    <select class="form-select select2" name="type" id="type">
                        <option value="" disabled>-- All --</option>
                        <option value="panel" selected>Panel</option>
                        <option value="inverter">Inverter</option>
                    </select>
                </div>
                <div class="col-sm-12 col-md-3 col-lg-3 custom-input-group pt-2">
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-gradient-primary btn-sm filter" type="button" data-bs-toggle="tooltip" data-placement="top" title="Click to Filter">
                            <i data-feather='search'></i>
                        </button>
                        <button class="btn btn-gradient-danger btn-sm reset ms-1" type="reset" data-bs-toggle="tooltip" data-placement="top" title=" Click to Reset Filter">
                            <i data-feather='x'></i>
                        </button>

                        <button data-type="pdf" class="btn btn-gradient-info btn-sm download ms-1" type="button" data-bs-toggle="tooltip" data-placement="top" title="Click to Download PDF">
                            <i data-feather='printer'></i>
                        </button>
                        <button data-type="excel" class="btn btn-gradient-success btn-sm download ms-1" type="button" data-bs-toggle="tooltip" data-placement="top" title="Click to Download Excel">
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
                            <th>Warehouse</th>
                            <th>Category</th>
                            <th>Product</th>
                            <th>Unit</th>
                            <th>Qty</th>
                            <th class="text-end pe-2">Total Value</th>
                            <th class="text-end pe-2">GST Amount</th>
                            <th class="text-end pe-2">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
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
<script type="text/javascript">
    'use strict';
    const URL = "{{route('stock-report')}}";
    var table = '';

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

    $(function() {

        table = $('#table').DataTable({
            ajax: {
                url: URL,
                data: function(d) {
                    d.warehouse_id = $('#warehouse_id').val();
                    d.category_id = $('#category_id').val();
                    d.type = $('#type').val();
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
                    data: 'warehouse_name',
                    name: 'warehouse_name'
                },
                {
                    data: 'category_name',
                    name: 'category_name'
                },
                {
                    data: 'item_dis_name',
                    name: 'item_dis_name'
                },
                {
                    data: 'unit_name',
                    name: 'unit_name'
                },
                {
                    data: 'quantity',
                    name: 'quantity'
                },
                {
                    data: 'total_value',
                    name: 'total_value',
                    className: 'text-end pe-2'
                },
                {
                    data: 'gst_amount',
                    name: 'gst_amount',
                    className: 'text-end pe-2'
                },
                {
                    data: 'total_amount',
                    name: 'total_amount',
                    className: 'text-end pe-2'
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
            url: "{{route('stock-report')}}",
            type: 'GET',
            datatype: 'json',
            data: {
                "downloadtype": type,
                "warehouse_id": $('#warehouse_id').val(),
                "category_id": $('#category_id').val(),
                "type": $('#type').val(),
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
                    var fileName = 'stock-report.pdf';
                } else {
                    var fileName = 'stock-report.xlsx';
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
        $('#category_id').val('');
        $('#type').val('');
        $('.select2').select2();
        table.draw();
    });
</script>

@endsection