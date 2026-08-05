@extends('layouts.app')
@section('title', 'Project Stock Adjustment')
@section('content')
<div class="row">
    <div class="col-12 mb-50">
        <h4 class="content-header-title float-start">Project Stock Adjustment | Date : {{ $data[0]->created_at->format('d-m-Y') }} | User : {{ $data[0]->user->name }} |
            Project :
            @if ($data[0]->issue_type == "project")
            {{ $data[0]->project->consumer_name }}
            @else
            {{ '(Ins) ' . $data[0]->installer->name . ' ' . $data[0]->installer->last_name }}
            @endif
        </h4>
        <a role="button" class="btn btn-sm btn-primary float-end" href="{{route('project-stock-adjustments.index')}}"><i class="fa fa-arrow-left me-25"></i> Back</a>
    </div>
    <div class="col-12">
        <div class="card p-1">
            <div class="table-responsive">
                <table id="table" class="datatables-basic table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Item</th>
                            <th>Current Stock</th>
                            <th>Real Stock</th>
                            <th>Difference</th>
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

    const URL =  $(location).attr('href');

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
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                {
                    data: 'item_name',
                    name: 'item_name'
                },
                {
                    data: 'current_stock',
                    name: 'current_stock'
                },
                {
                    data: 'real_stock',
                    name: 'real_stock'
                },
                {
                    data: 'difference',
                    name: 'difference'
                },
                {
                    data: 'created_at',
                    name: 'created_at',
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
</script>
@endsection