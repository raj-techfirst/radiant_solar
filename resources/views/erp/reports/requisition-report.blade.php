@extends('layouts.app')
@section('title', 'Requisition Report')
@section('content')
<div class="row">
    <div class="col-12 mt-2">
        <div class="card p-1">
            <div class="row">
                <div class="col-12">
                    <h3>Filter</h3>
                </div>
                <!-- <div class="col-sm-12 col-md-6 col-lg-9 custom-input-group">
                    <label class="form-label" for="consumer">Consumer</label>
                    <select class="form-control anlayst  form-select select2 custom-select2" name="sales_master_id[]" id="sales_master_id" multiple>
                        @foreach($salesMaster as $value)
                        <option value="{{$value->id}}">{{$value->consumer_name}} - {{$value->consumer_number}} - K/W : {{$value->register_kw}}</option>
                        @endforeach
                    </select>
                </div> -->
                <div class="col-sm-12 col-md-6 col-lg-2 custom-input-group">
                    <label class="form-label" for="consumer">Consumer</label>
                    <input type="text" class="form-control" name="consumer" id="consumer" placeholder="Name / Mobile / Consumer Number">
                </div>
                <div class="col-sm-12 col-md-3 col-lg-2 custom-input-group">
                    <label class="form-label" for="status">Status(Include)</label>
                    <select class="form-select select2" name="status" id="status">
                        <option value="" selected>ALL Status</option>
                        @foreach(allSalesStatus() as $statusKey => $statusValue)
                        <option value="{{ $statusValue['value'] }}" @if($statusValue['value'] == "dispach_pending_list") selected @endif>{{$statusValue['name']}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-12 col-md-3 col-lg-2 custom-input-group">
                    <label class="form-label" for="not_status">Status(Not Include)</label>
                    <select class="form-select select2" name="not_status" id="not_status">
                        <option value="" selected>None</option>
                        @foreach(allSalesStatus() as $statusKey => $statusValue)
                        <option value="{{ $statusValue['value'] }}" @if($statusValue['value'] == "installation_done") selected @endif>{{$statusValue['name']}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-12 col-md-6 col-lg-3 custom-input-group pt-2">
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-gradient-primary btn-sm filter" type="button" data-bs-toggle="tooltip" data-placement="top" title="Click to Filter">
                            <i data-feather='search'></i>
                        </button>
                        <button class="btn btn-gradient-danger btn-sm reset ms-50" type="reset" data-bs-toggle="tooltip" data-placement="top" title=" Click to Reset Filter">
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
    <div class="col-12 append-div">

    </div>
</div>

@endsection

@section('pagescript')
<script type="text/javascript">
    'use strict';
    $(document).on('click', '.download', function() {
        var type = $(this).attr('data-type');
        $.ajax({
            url: "{{route('get-required-stock-report')}}",
            type: 'POST',
            datatype: 'json',
            data: {
                "type": type,
                // "consumer": $('#sales_master_id').val(),
                "consumer": $('#consumer').val(),
                "status": $('#status').val(),
                "not_status": $('#not_status').val(),
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
                    var fileName = 'requisition-report.pdf';
                } else {
                    var fileName = 'requisition-report.xlsx';
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
    $(document).on('click', '.filter', function() {
        $.ajax({
            type: "POST",
            url: "{{route('get-required-stock-report')}}",
            data: {
                // "consumer": $('#sales_master_id').val(),
                "consumer": $('#consumer').val(),
                "status": $('#status').val(),
                "not_status": $('#not_status').val(),
                "_token": "{{ csrf_token() }}",
            },
            dataType: 'json',
            cache: false,
            beforeSend: function() {
                $(".append-div").html(`<div class="card"><div class="row"><div class="col-lg-12 col-12 text-center p-5"><img src="{{ asset('img/loader.gif') }}" style="max-height: 50px;" /><br/><br/><h4>Please wait a moment, we're processing the data.</h4></div></div></div>`);
            },
            success: function(response) {
                $(".append-div").html(response.html);
            }
        });
    });

    $(document).on('click', '.reset', function() {
        $('#consumer').val('');
        $('#status').val('');
        $('#not_status').val('');
    });
</script>

@endsection