@extends('layouts.app')
@section('title', 'Purchase Order')
@section('content')
<div class="row">
    <div class="col-12 py-1">
        <h4 class="content-header-title float-start">Get Serial Numbers</h4>
    </div>

    <div class="col-12">
        <div class="card p-1">
            <form id="form" action="javascript:void(0)" method="POST">
                @csrf
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
                    <div class="col-sm-12 col-md-3 col-lg-2 custom-input-group">
                        <label class="form-label" for="warehouse_id">Warehouse</label>
                        <select class="form-select  custom-select2 select2" name="warehouse_id" id="warehouse_id">
                            <option value="" selected disabled>-- Select --</option>
                            @foreach ($warehouse as $value)
                            <option value="{{ $value->id }}">{{ $value->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3 col-lg-3 custom-input-group">
                        <label class="form-label" for="group_type">Item</label>
                        <select class="form-select custom-select2 select2" name="item_group_id" id="item_group_id">
                            <option value="" selected disabled>-- Select --</option>
                            @foreach ($itemGroup as $k => $v)
                            <option value="{{ $v->id }}" data-unit="{{ $v->unit->unit_name }}" data-gst="{{ $v->gst_rate }}">{{ $v->item_code }} {{ getItemGropName($v) }}</option>
                            @endforeach
                        </select>
                    </div>


                    <div class="col-sm-12 col-md-3 col-lg-3 custom-input-group">
                        <label class="form-label" for="serial_number">Serial Number</label>
                        <input type="text" class="form-control" name="serial_number" id="serial_number" placeholder="Enter Serial Number">
                    </div>

                    <div class="pt-2 col-12 col-md-12 col-lg-12 custom-input-group">
                        <div class="d-flex justify-content-end">
                            <button class="btn btn-gradient-primary btn-sm filter" type="submit" data-bs-toggle="tooltip" data-placement="top" title="Click to Filter">
                                <i data-feather='search'></i>
                            </button>
                            <button class="btn btn-gradient-danger btn-sm reset ms-1" type="reset" data-bs-toggle="tooltip" data-placement="top" title=" Click to Reset Filter">
                                <i data-feather='x'></i>
                            </button>
                            <!-- <button class="btn btn-gradient-success btn-sm download ms-1" type="button" data-bs-toggle="tooltip" data-placement="top" title="Click to Download">
                                <i data-feather='download'></i>
                            </button> -->
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="col-12">
        <div class="card p-1">
            <div class="table-responsive">
                <table id="table" class="datatables-basic table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th style="width: 30%;">Item</th>
                            <th>Serial Number</th>
                            <th>Current Status</th>
                            <th>Added At</th>
                            <th>Warranty</th>
                            <th>Guarantee</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="inlineModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-transparent border-bottom">
                <h4 class="text-center mb-0" id="exampleModalTitle">Serial Number Tracking</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-1" id="body">
                <h1>a</h1>
            </div>
        </div>
    </div>
</div>
@endsection

@section('pagescript')
<script>
    'use strict';
    var table;

    $(function() {
        $("#form").validate({
            rules: {
                item_group_id: {
                    required: true
                },
                // warehouse_id: {
                //     required: true
                // }
            },
            messages: {
                item_group_id: {
                    required: "Please Select Item"
                },
                // warehouse_id: {
                //     required: "Please Select Warehouse"
                // }
            },
            errorElement: "p",
            errorClass: "text-danger mb-0 custom-error",
            highlight: function(element) {
                $(element).addClass('has-error');
            },
            unhighlight: function(element) {
                $(element).removeClass('has-error');
            },
            errorPlacement: function(error, element) {
                $(element).closest('.custom-input-group').append(error);
            }
        });

        flatpickr('.flatpickr-date', {
            enableTime: false,
            dateFormat: 'd-m-Y',
            defaultDate: ''
        });

        $(document).on('click', '.filter', function() {
            loadTable();
        });
        $(document).on('click', '.reset', function() {
            resetForm();
        });
    });

    function initializeTable() {
        table = $('#table').DataTable({
            ajax: {
                url: "{{route('get-serial-numbers')}}",
                data: function(d) {
                    d.from_date = $('#from_date').val();
                    d.to_date = $('#to_date').val();
                    d.item_group_id = $('#item_group_id').val();
                    d.warehouse_id = $('#warehouse_id').val();
                    d.serial_number = $('#serial_number').val();
                }
            },
            processing: true,
            serverSide: true,
            fixedHeader: false,
            scroll: false,
            bDestroy: true,
            aLengthMenu: [
                [20, -1],
                [20, "All"]
            ],
            columns: [{
                    data: 'id',
                    render: function(data, type, row, meta) {
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'item_group',
                    name: 'item_group'
                },
                {
                    data: 'serial_number',
                    name: 'serial_number'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'created_at',
                    name: 'created_at'
                },
                {
                    data: 'warranty',
                    name: 'warranty'
                },
                {
                    data: 'guarantee',
                    name: 'guarantee'
                }
            ],
            initComplete: function(settings, json) {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }
        });
    }

    function loadTable() {
        if ($("#form").valid()) {
            if (!$.fn.dataTable.isDataTable('#table')) {
                initializeTable();
            } else {
                table.ajax.reload();
            }
        } else {
            return false;
        }
    }

    function resetForm() {
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
        $('#item_group_id').val('');
        $('#warehouse_id').val('');
        $('#serial_number').val('');
        $('.select2').select2();

        if ($.fn.dataTable.isDataTable('#table')) {
            $('#table').DataTable().clear().destroy();
        }

        $("#table tbody").empty();
    }

    function trackNo(id) {

        $.ajax({
            type: "POST",
            url: "{{route('track-serial-number')}}",
            dataType: 'json',
            data: {
                "id": id,
                "_token": "{{ csrf_token() }}",
            },
            success: function(response) {
                $("#body").html(response.html);
                $("#inlineModal").modal('show');
            }
        });
    }
</script>

@endsection