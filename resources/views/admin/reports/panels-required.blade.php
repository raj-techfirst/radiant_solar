@extends('layouts.app')
@section('title', 'Panels Required Report')
@section('content')
<div class="row">
    <div class="col-12 mt-2">
        <div class="card p-1">
            <div class="row">
                <div class="col-12">
                    <h3>Filter</h3>
                </div>
                <div class="col-sm-12 col-md-3 col-lg-2 custom-input-group">
                    <label class="form-label" for="district_id">District</label>
                    <select class="form-select select2" name="district_id" id="district_id">
                        <option value="" selected>ALL District</option>
                        @foreach($districts as $value)
                        <option value="{{$value->id}}">{{ $value->name}}</option>
                        @endforeach
                    </select>
                </div> 
                <div class="col-sm-12 col-md-3 col-lg-2 custom-input-group">
                    <label class="form-label" for="panel_company_id">Panel Company</label>
                    <select class="form-select select2" name="panel_company_id" id="panel_company_id">
                        <option value="" selected>ALL Panel Company</option>
                        @foreach($panelCompany as $value)
                        <option value="{{$value->id}}">{{ $value->name}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-sm-12 col-md-3 col-lg-2 custom-input-group">
                    <label class="form-label" for="panel_watt_id">Panel Watts</label>
                    <select class="form-select select2" name="panel_watt_id" id="panel_watt_id">
                        <option value="" selected>ALL Panel Watts</option>
                        @foreach($panelwatts as $value)
                        <option value="{{$value->id}}">{{ $value->name}}</option>
                        @endforeach
                    </select>
                </div>


                <div class="col-sm-12 col-md-3 col-lg-2 custom-input-group">
                    <label class="form-label" for="agent_sales_person_id">Agent</label>
                    <select class="form-select select2" name="agent_sales_person_id" id="agent_sales_person_id">
                        <option value="" selected>ALL Agent</option>
                        @foreach($agentSalesPerson as $value)
                        <option value="{{$value->id}}">{{ $value->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-12 col-md-3 col-lg-2 custom-input-group pt-2">
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-gradient-primary btn-sm filter" type="button" data-bs-toggle="tooltip" data-placement="top" title="Click to Filter">
                            <i data-feather='search'></i>
                        </button>
                        <button class="btn btn-gradient-danger btn-sm reset ms-1" type="reset" data-bs-toggle="tooltip" data-placement="top" title=" Click to Reset Filter">
                            <i data-feather='x'></i>
                        </button>
                        <button class="btn btn-gradient-success btn-sm download ms-1" type="button" data-bs-toggle="tooltip" data-placement="top" title="Click to Download">
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
                            <th>District</th>
                            <th>Panel</th>
                            <th>Panel Watt</th>
                            <th>Total Panel</th>
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
    const URL = "{{route('panels-required-reports')}}";
    var table = '';
    $(function() {

        table = $('#table').DataTable({
            ajax: {
                url: URL,
                data: function(d) {
                    d.agent_sales_person_id = $('#agent_sales_person_id').val();
                    d.district_id = $('#district_id').val();
                    d.panel_company_id = $('#panel_company_id').val();
                    d.panel_watt_id = $('#panel_watt_id').val();
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
                    data: 'id',
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'district.name',
                    name: 'district.name'
                },
                {
                    data: 'panel.name',
                    name: 'panel.name'
                },
                {
                    data: 'panelwatt.name',
                    name: 'panelwatt.name'
                },
                {
                    data: 'total_panel',
                    name: 'total_panel'
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
        $.ajax({
            url: "{{route('panels-required-download')}}",
            type: 'POST',
            datatype: 'json',
            data: {
                "agent_sales_person_id": $('#agent_sales_person_id').val(),
                "district_id": $('#district_id').val(),
                "panel_company_id": $('#panel_company_id').val(),
                "panel_watt_id": $('#panel_watt_id').val(),
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
                var fileName = 'panel_List.xlsx';
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
        $('#agent_sales_person_id').val('');
        $('#district_id').val('');
        $('#panel_company_id').val('');
        $('#panel_watt_id').val('');
        $('.select2').select2();
        table.draw();
    });
</script>

@endsection