@extends('layouts.app')
@section('title', 'Task')
@section('content')
<div class="row">
    <div class="col-12 mb-1">
        <h4 class="content-header-title float-start">{{ __('message.Task List') }}</h4>
        @can('task-create')
        <a role="button" class="btn btn-sm btn-primary float-end" href="{{route('task.create')}}"><i class="fa fa-plus me-25"></i> {{ __('message.Add New') }}</a>
        @endcan
        <a role="button" class="btn btn-sm btn-primary float-end filter mx-1" data-text="Filter">{{ __('message.Filter') }}</a>
    </div>
    <div class="col-12" id="filter_form">
        <div class="card p-1">
            <div class="card-header p-0">
                <h4 class="card-title mb-1">{{ __('message.Filter') }}</h4>
            </div>
            <div class="card-body p-0">
                <form id="task_report" class="form" action="javascript:void(0)" method="POST">
                    @csrf
                    <div class="row">
                        <!--Start User-->
                        <div class="col-12 col-md-4 mb-1">
                            <label class="form-label" for="user">{{ __('message.Employee') }}</label>
                            <select id="user" name="user" class="form-select">
                                @if(Auth::user()->roles[0]->name != 'Sales')
                                <option selected>All</option>
                                @endif
                                @foreach($companyProfile as $item)
                                <option value="{{$item->id}}">{{$item->user->name}} {{$item->user->last_name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <!--End User-->

                        <!--Start Status-->
                        <div class="col-12 col-md-4 mb-1">
                            <label class="form-label" for="status">{{ __('message.Status') }}</label>
                            <select class="form-control" name="status" id="status">
                                <option selected>All</option>
                                <option value="1">Pending</option>
                                <option value="2">In Progress</option>
                                <option value="3">Completed</option>
                                <option value="4">Cancelled</option>
                            </select>
                        </div>
                        <!--End Status-->

                        <!--Start product-->
                        <div class="col-12 col-md-4 mb-1">
                            <label class="form-label" for="product">{{ __('message.Product') }}</label>
                            <select id="product" name="product" class="form-select">
                                <option selected>All</option>
                                @foreach($product as $item)
                                <option value="{{$item->id}}">{{$item->product_name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <!--End product-->

                        <!--Start start date-->
                        <div class="col-md-6 col-12 mb-1">
                            <label class="form-label" for="s_date">{{ __('message.Start Date') }}</label>
                            <input type="text" class="form-control date" name="s_date" id="s_date" autocomplete="off" placeholder="{{ __('message.Start Date') }}" readonly>
                        </div>
                        <!--End start date-->

                        <!--Start end date-->
                        <div class="col-md-6 col-12 mb-1">
                            <label class="form-label" for="e_date">{{ __('message.End Date') }}</label>
                            <input type="text" class="form-control date" name="e_date" id="e_date" autocomplete="off" placeholder="{{ __('message.End Date') }}" readonly>
                        </div>
                        <!--End end date-->

                        <div class="col-md-12 col-12 text-end">
                            <button type="button" class="btn btn-sm btn-primary task-report mx-1" id="submit">{{ __('message.Submit') }}</button>
                            <button type="submit" class="btn btn-sm btn-primary task-report-export">{{ __('message.Download') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card p-1">
            <table id="table" class="datatables-basic table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('message.Action') }}</th>
                        <th>{{ __('message.Priority') }}</th>
                        <th>{{ __('message.Status') }}</th>
                        <th>{{ __('message.Task Name') }}</th>
                        <th>{{ __('message.Task Date') }}</th>
                        <th>{{ __('message.Product') }}</th>
                        <th>{{ __('message.Timespand') }}</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="5" style="text-align:right">{{ __('message.Total Time') }} :</th>
                        <th class="text-center"></th>
                    </tr>
                </tfoot>
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
            <div class="modal-body p-0" id="body">

            </div>
        </div>
    </div>
</div>
@endsection

@section('pagescript')
<script type="application/javascript">
    'use strict';
    const URL = "{{route('task.index')}}";

    var table = '';
    $(function() {
        table = $('#table').DataTable({
            processing: true,
            serverSide: true,
            fixedHeader: true,
            scrollX: true,
            aLengthMenu: [
                [20, -1],
                [20, "All"],
            ],
            ajax: {
                url: URL,
                data: function(d) {
                    d.user = $('#user').val();
                    d.product = $('#product').val();
                    d.status = $('#status').val();
                    d.s_date = $('#s_date').val();
                    d.e_date = $('#e_date').val();
                }
            },
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
                    data: 'priority',
                    name: 'priority'
                },
                {
                    data: 'status',
                    name: 'status'
                },
                {
                    data: 'task_name',
                    name: 'task_name'
                },
                {
                    data: 'task_date',
                    name: 'task_date'
                },
                {
                    data: 'product_id',
                    name: 'product_id'
                },
                {
                    data: 'timespand',
                    name: 'timespand',
                },
                {
                    data: 'hours',
                    name: 'hours',
                    visible: false
                },
                {
                    data: 'minutes',
                    name: 'minutes',
                    visible: false
                },
            ],
            footerCallback: function(row, data, start, end, display) {
                let api = this.api();

                // Remove the formatting to get integer data for summation
                let intVal = function(i) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '') * 1 :
                        typeof i === 'number' ?
                        i :
                        0;
                };

                // Total over all pages
                let totalForH = api
                    .column(8)
                    .data()
                    .reduce((a, b) => intVal(a) + intVal(b), 0);

                // Total over all pages
                let totalForM = api
                    .column(9)
                    .data()
                    .reduce((a, b) => intVal(a) + intVal(b), 0);

                let totalTemp = parseInt(totalForH * 60) + parseInt(totalForM);
                var totalHours = Math.floor(totalTemp / 60);
                var totalMinutes = totalTemp % 60;
                let totalSum = totalHours.toString().padStart(2, '0') + "h:" + totalMinutes.toString().padStart(2, '0') + "m";

                let pageTotalForH = api
                    .column(8, {
                        page: 'current'
                    })
                    .data()
                    .reduce((a, b) => intVal(a) + intVal(b), 0);

                let pageTotalForM = api
                    .column(9, {
                        page: 'current'
                    })
                    .data()
                    .reduce((a, b) => intVal(a) + intVal(b), 0);

                let pageTemp = parseInt(pageTotalForH * 60) + parseInt(pageTotalForM);
                var pageHours = Math.floor(pageTemp / 60);
                var pageMinutes = pageTemp % 60;
                let pageSum = pageHours.toString().padStart(2, '0') + "h:" + pageMinutes.toString().padStart(2, '0') + "m";

                api.column(5).footer().innerHTML =
                    pageSum + '<br> (' + totalSum + ')';
            },
            initComplete: function(settings, json) {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll(
                    '[data-bs-toggle="tooltip"]'));
                var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                })
            }
        });
    });

    $(document).on('click', '.view', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var url = "{{route('task.show','id')}}".replace('id', id);
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
            }
        });
    });

    $(document).on('click', '.status', function() {
        let status = $(this).data('value');
        let id = $(this).data('id');
        var route = "{{route('task.show','id')}}".replace('id', id);
        $.ajax({
            type: "get",
            url: route,
            dataType: 'json',
            data: {
                "_token": "{{ csrf_token() }}",
                "id": id,
                "status": status,
            },
            success: function(response) {
                if (response.status == true) {
                    table.ajax.reload(null, false);
                    toastr.success("{{ __('message.Status updated successfully.') }}",
                        "{{ __('message.Success') }}");
                } else {
                    toastr.error("{{ __('message.Something wen`t wrong. Please try again.') }}",
                        "{{ __('message.Error') }}");
                }
            },
            error: function(error) {
                toastr.error("{{ __('message.Something went wrong. Please try again.') }}",
                    "{{ __('message.Error') }}");
                $(document.body).css('pointer-events', '');
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
                            } else {
                                toastr.error("{{ __('message.Something went wrong. Please try again.') }}",
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

    $(document).ready(function() {
        $(".date").datepicker({
            dateFormat: 'dd-mm-yy',
        });

        $("#filter_form").hide();
        $(document).on('click', '.filter', function() {
            if ($(this).data('text') == "Filter") {
                $(this).text("{{ __('message.Close') }}");
                $(this).data('text','Close');
            } else {
                $(this).text("{{ __('message.Filter') }}");
                $(this).data('text','Filter');
            };
            $("#filter_form").toggle();
        });
    });

    $(document).on('click', '.task-report', function() {
        table.draw();
    });

    $(document).on('click', '.task-report-export', function() {
        $('#task_report').attr('action', "{{route('task-export')}}");
        $('form#task_report').submit();
    });
</script>
@endsection