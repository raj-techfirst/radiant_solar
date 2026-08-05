@extends('layouts.app')
@section('title', 'Project Stock Adjustment')
@section('content')
<div class="row">
    <div class="col-12 mb-50">
        <h4 class="content-header-title float-start">Project Stock Adjustment List</h4>
        @can('warehouse-stock-adjust-create')
        <a role="button" class="btn btn-sm btn-primary float-end" href="{{route('project-stock-adjustments.create')}}"><i class="fa fa-plus me-25"></i> Add New</a>
        @endcan
    </div>
    <div class="col-12">
        <div class="card p-1">
            <div class="table-responsive">
                <table id="table" class="datatables-basic table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Action</th>
                            <th>Project</th>
                            <th>Date</th>
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
<script type="application/javascript">
    'use strict';
    const URL = "{{route('project-stock-adjustments.index')}}";

    var table = '';
    $(function() {
        table = $('#table').DataTable({
            ajax: URL,
            processing: true,
            serverSide: true,
            fixedHeader: true,
            scrollX: true,
            bScrollInfinite: true,
            bScrollCollapse: true,
            sScrollY: "465px",
            aLengthMenu: [
                [15, 30, 50, 100, -1],
                [15, 30, 50, 100, "All"]
            ],
            order: [
                [0, 'desc']
            ],
            columns: [{
                    data: 'id',
                    render: function(data, type, row, meta) {
                        var rowNumber = meta.row + meta.settings._iDisplayStart + 1;
                        var isResponsive = meta.settings.responsive && meta.settings.responsive.details;
                        if (type === 'display' && isResponsive && meta.settings.responsive.details.type === 'column') {
                            return '';
                        } else {
                            return rowNumber;
                        }
                    },
                    orderable: false,
                    createdCell: function(td, cellData, rowData, row, col) {
                        var isResponsive = table.responsive.hasHidden();
                        if (isResponsive) {
                            $(td).addClass('dtr-control');
                        } else {
                            $(td).removeClass('dtr-control');
                        }
                    }
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    sortable: false
                },
                {
                    data: 'project_name',
                    name: 'project_name',
                    orderable: false,
                    sortable: false,
                },
                {
                    data: 'created_at',
                    name: 'created_at'
                },
            ],
            initComplete: function(settings, json) {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                })
            },
            // responsive: {
            //     details: {
            //         display: $.fn.dataTable.Responsive.display.modal({
            //             header: function(row) {
            //                 var rowNumber = row.index() + 1;
            //                 return 'Record No. : ' + rowNumber;
            //             }
            //         }),
            //         type: "column",
            //         renderer: function(t, a, e) {
            //             e = $.map(e, function(t, a) {
            //                 return "" !== t.title ? '<tr data-dt-row="' + t.rowIndex + '" data-dt-column="' + t.columnIndex + '"><td>' + t.title + " :</td> <td>" + t.data + "</td></tr>" : ""
            //             }).join("");
            //             return !!e && $('<table class="table table-sm"/><tbody />').append(e)
            //         }
            //     }
            // }
        });
    });
</script>
@endsection