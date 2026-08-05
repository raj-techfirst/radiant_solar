@extends('layouts.app')
@section('title', 'Warehouse Stock Adjustment')
@section('content')
<div class="row">
    <div class="col-12 mb-50">
        <h4 class="content-header-title float-start">Stock Adjustment | Date : {{ $data[0]->created_at->format('d-m-Y') }} | User : {{ $data[0]->user->name }} | Warehouse : {{ $data[0]->warehouse->name }}</h4>
        <a role="button" class="btn btn-sm btn-primary float-end" href="{{route('stock-adjustments.index')}}"><i class="fa fa-arrow-left me-25"></i> Back</a>
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
    const URL = "{{route('daily-stock-adujst', ['id' => $data[0]->warehouse_id, 'date' => $data[0]->created_at->format('Y-m-d')])}}";

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