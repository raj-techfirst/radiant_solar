@extends('layouts.app')
@section('title', 'Project Wise Stock Report')
@section('content')
<div class="row">
    <div class="col-12 mt-2">
        <div class="card p-1">
            <div class="row">
                <div class="col-12">
                    <h3>Filter</h3>
                </div>
                <div class="col-sm-12 col-md-6 col-lg-9 custom-input-group">
                    <label class="form-label" for="consumer">Consumer</label>
                    <select class="form-control anlayst  form-select select2 custom-select2" name="sales_master_id" id="sales_master_id">
                        <option selected disabled>{{ __('message.-- Select --') }}</option>
                        @foreach($salesMaster as $value)
                        <option value="{{$value->id}}">{{$value->consumer_name}} - {{$value->consumer_number}} - K/W : {{$value->register_kw}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-12 col-md-6 col-lg-3 custom-input-group pt-2">
                    <div class="d-flex justify-content-end">
                        <!-- <button class="btn btn-gradient-success btn-sm download ms-1" type="button" data-bs-toggle="tooltip" data-placement="top" title="Click to Download">
                            <i data-feather='download'></i>
                        </button> -->

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
                            <th style="width:30px;">#</th>
                            <th style="min-width:450px;">Item Name</th>
                            <th style="width:100px;">Use stock</th>
                            <th style="width:100px;">A. Stock</th>
                            <th style="width:100px;">Total stock</th>
                            <th style="width:150px;">Taxabale</th>
                            <th style="width:150px;">GST</th>
                            <th style="width:150px;">Total</th>
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
    const URL = "{{route('project-wise-stock-report')}}";
    var table = '';

    function initializeTable() {
        table = $('#table').DataTable({
            ajax: {
                url: URL,
                data: function(d) {
                    d.consumer = $('#sales_master_id').val();
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
                    data: 'item_dis_name',
                    name: 'item_dis_name'
                },
                {
                    data: 'use_quantity',
                    name: 'use_quantity'
                },
                {
                    data: 'quantity',
                    name: 'quantity'
                },
                {
                    data: 'total_qty',
                    name: 'total_qty'
                },
                {
                    data: 'taxable_amount',
                    name: 'taxable_amount',
                    class: 'text-end'
                },
                {
                    data: 'gst_amount',
                    name: 'gst_amount',
                    class: 'text-end'
                },
                {
                    data: 'total_amount',
                    name: 'total_amount',
                    class: 'text-end'
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

    $('#sales_master_id').on('change', function() {
        if (!$.fn.dataTable.isDataTable('#table')) {
            initializeTable();
        } else {
            table.ajax.reload();
        }

    });
    $(document).on('click', '.download', function() {
        var type = $(this).attr('data-type');
        $.ajax({
            url: "{{route('project-wise-stock-report')}}",
            type: 'GET',
            datatype: 'json',
            data: {
                "type" : type,
                "consumer": $('#sales_master_id').val(),
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
                    var fileName = 'project_wise_stock.pdf';
                } else {
                    var fileName = 'project-wise-stock.xlsx';
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
    
</script>

@endsection