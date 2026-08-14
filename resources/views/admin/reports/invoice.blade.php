@extends('layouts.app')
@section('title', 'Invoice Report')
@section('content')
<div class="row">
    <div class="col-12 mb-1">
        <h4 class="content-header-title float-start">Invoice Report</h4>
    </div>
    <div class="col-12 mt-2">
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
                    <label class="form-label" for="from_date">To Date</label>
                    <input type="text" class="form-control flatpickr-date" name="date" id="to_date" placeholder="dd-mm-yyyy">
                </div>
                <div class="col-sm-12 col-md-6 col-lg-2 custom-input-group">
                    <label class="form-label" for="consumer">Consumer</label>
                    <input type="text" class="form-control" name="consumer" id="consumer" placeholder="Name / Mobile / Consumer Number">
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
                <div class="col-sm-12 col-md-3 col-lg-2 custom-input-group">
                    <label class="form-label" for="status">Status(Include)</label>
                    <select class="form-select select2" name="status" id="status">
                        <option value="" selected>ALL Status</option>
                        @foreach(allSalesStatus() as $statusKey => $statusValue)
                        <option value="{{ $statusValue['value'] }}">{{$statusValue['name']}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-sm-12 col-md-3 col-lg-2 custom-input-group">
                    <label class="form-label" for="not_status">Status(Not Include)</label>
                    <select class="form-select select2" name="not_status" id="not_status">
                        <option value="" selected>None</option>
                        @foreach(allSalesStatus() as $statusKey => $statusValue)
                        <option value="{{ $statusValue['value'] }}">{{$statusValue['name']}}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-sm-12 col-md-3 col-lg-2 custom-input-group">
                    <label class="form-label" for="invoice">Invoice Generated</label>
                    <select class="form-select select2" name="invoice" id="invoice">
                        <option value="" selected>All</option>
                        <option value="YES">YES</option>
                        <option value="NO">NO</option>
                    </select>
                </div> 
                <div class="col-sm-12 col-md-12 col-lg-10 custom-input-group pt-2">
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
                            <th>Consumer No.</th>

                            <th>Invoice No</th>
                            <th>Invoice Date</th>
                            <th>Consumer Name</th>
                            <th>Reg. Kw</th>

                            <th>System Cost</th>
                            <th>Meter Charge</th>
                            <th>Registration Fee</th>
                            <th>Other Charge</th>
                            <th>Total Cost</th>
                            <th>Payment Received</th>
                            <th>Payment Pending</th>

                            <th>Agent</th>
                            <th>Installer</th>
                            <th>Consumer Type</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="statusexampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-transparent border-bottom">
                <h4 class="text-center mb-0" id="exampleModalTitle"> Form</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-2" id="status-body">

            </div>
        </div>
    </div>
</div>
@endsection

@section('pagescript')
<script type="text/javascript">
    'use strict';
    const URL = "{{route('invoice-reports')}}";
    var table = '';
    $(function() {
        flatpickr('.flatpickr-date', {
            enableTime: false,
            dateFormat: 'd-m-Y',
            defaultDate: '',
        });

        table = $('#table').DataTable({
            ajax: {
                url: URL,
                data: function(d) {
                    d.from_date = $('#from_date').val();
                    d.to_date = $('#to_date').val();
                    d.consumer = $('#consumer').val();
                    d.agent_sales_person_id = $('#agent_sales_person_id').val();
                    d.status = $('#status').val();
                    d.not_status = $('#not_status').val();
                    d.invoice = $('#invoice').val();
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
                    data: 'consumer_number',
                    name: 'consumer_number'
                },
                {
                    data: 'invoice_no',
                    name: 'invoice_no'
                },
                {
                    data: 'invoice_date',
                    name: 'invoice_date'
                },
                {
                    data: 'consumer',
                    name: 'consumer'
                },
                {
                    data: 'register_kw',
                    name: 'register_kw'
                },
                {
                    data: 'total_system',
                    name: 'total_system'
                },
                {
                    data: 'salesquatationfull.meter_charges',
                    name: 'salesquatationfull.meter_charges'
                },
                {
                    data: 'salesquatationfull.registration_fee',
                    name: 'salesquatationfull.registration_fee'
                },
                {
                    data: 'salesquatationfull.other_charge_amount',
                    name: 'salesquatationfull.other_charge_amount'
                },
                {
                    data: 'salesquatationfull.total_amount',
                    name: 'salesquatationfull.total_amount'
                },
                {
                    data: 'received_amonut',
                    name: 'received_amonut'
                },
                {
                    data: 'pending_amonut',
                    name: 'pending_amonut'
                },
                {
                    data: 'agentsalesperson.name',
                    name: 'agentsalesperson.name',
                },
                {
                    data: 'installer',
                    name: 'installer',
                },
                {
                    data: 'consumer_type',
                    name: 'consumer_type',
                },
                {
                    data: 'application_pending',
                    name: 'application_pending',
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
    $(document).on('click', '.download', function() {
        $.ajax({
            url: "{{route('invoice-download')}}",
            type: 'POST',
            datatype: 'json',
            data: {
                "from_date": $('#from_date').val(),
                "to_date": $('#to_date').val(),
                "consumer": $('#consumer').val(),
                "status": $('#status').val(),
                "invoice": $('#invoice').val(),
                "agent_sales_person_id": $('#agent_sales_person_id').val(),
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
                var fileName = 'Invoice_Report.xlsx';
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
        $('#agent_sales_person_id').val('');
        $('#status').val('');
        $('#not_status').val('');
        $('#invoice').val('');
        $('.select2').select2();
        table.draw();
    });

    $(document).on('click', '.status-view', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var url = "{{route('status-view','id')}}".replace('id', id);
        $("#statusexampleModal").modal("show");
        $.ajax({
            url: url,
            type: 'POST',
            datatype: 'json',
            data: {
                "id": id,
                "type": 'totalcollectionreport',
                "_token": "{{ csrf_token() }}",
            },
            success: function(response) {
                $("#status-body").html(response.html);

                flatpickr('.flatpickr-date', {
                    enableTime: false,
                    dateFormat: 'd-m-Y',
                    defaultDate: '',
                });

                if (feather) {
                    feather.replace({
                        width: 14,
                        height: 14
                    });
                }

                var bsStepper = document.querySelectorAll('.bs-stepper'),
            verticalWizard = document.querySelector('.vertical-wizard-example');

        // Adds crossed class
        if (typeof bsStepper !== undefined && bsStepper !== null) {
            for (var el = 0; el < bsStepper.length; ++el) {
                bsStepper[el].addEventListener('show.bs-stepper', function(event) {
                    var index = event.detail.indexStep;
                    var numberOfSteps = $(event.target).find('.step').length - 1;
                    var line = $(event.target).find('.step');

                    for (var i = 0; i < index; i++) {
                        line[i].classList.add('crossed');

                        for (var j = index; j < numberOfSteps; j++) {
                            line[j].classList.remove('crossed');
                        }
                    }
                    if (event.detail.to == 0) {
                        for (var k = index; k < numberOfSteps; k++) {
                            line[k].classList.remove('crossed');
                        }
                        line[0].classList.remove('crossed');
                    }
                });
            }
        }

        // Vertical Wizard
        // --------------------------------------------------------------------
        if (typeof verticalWizard !== undefined && verticalWizard !== null) {
            var verticalStepper = new Stepper(verticalWizard, {
                linear: false
            });
            $(verticalWizard)
                .find('.btn-next')
                .on('click', function() {
                    verticalStepper.next();
                });
            $(verticalWizard)
                .find('.btn-prev')
                .on('click', function() {
                    verticalStepper.previous();
                });

            $(verticalWizard)
                .find('.btn-submit')
                .on('click', function() {
                    alert('Submitted..!!');
                });
        }
            }
        });
    });

    $(document).on('click', '.save-status-form', function(e) {
        e.preventDefault();
        var id = $("#sales_master_id").val();
        var url = "{{route('status-save','id')}}".replace('id', id);
        var formData = $('#form').serialize();
        $.ajax({
            url: url,
            type: 'POST',
            datatype: 'json',
            data: formData,
            success: function(response) {
                if (response.status) {
                    $("#statusexampleModal").modal("hide");
                    toastr.success(response.message, "{{ __('message.Success') }}");
                } else {
                    toastr.error("{{ __('message.Something went wrong. Please try again.') }}", "{{ __('message.Error') }}");
                }
            }
        });
    });
</script>

@endsection