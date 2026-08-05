@extends('layouts.app')
@section('title', 'BOM')
@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="content-header-title float-start">BOM List</h4>
        @can('bom-create')
        <a href="{{route('bom.create')}}" role="button" class="btn btn-sm btn-gradient-primary float-end"><i class="fa fa-plus me-25"></i> Add New</a>
        @endcan
    </div>
    <div class="col-12">
        <div class="card p-1">
            <div class="table-responsive">
                <table id="table" class="datatables-basic table table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>BOM Name</th>
                            <th>Remark</th>
                            <th>Action</th>
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="appnedData">
        </div>
    </div>
</div>

@endsection

@section('pagescript')
<script type="application/javascript">
    'use strict';
    const URL = "{{route('bom.index')}}";
    var table = '';
    var city_id = '';
    $(function() {
        table = $('#table').DataTable({
            ajax: {
                url: URL,
            },
            processing: true,
            serverSide: true,
            fixedHeader: false,
            scrollX: true,
            bScrollInfinite: true,
            bScrollCollapse: true,
            sScrollY: "465px",
            aLengthMenu: [
                [15, 30, 50, 100, -1],
                [15, 30, 50, 100, "All"]
            ],
            order: [
                [2, 'desc']
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
                    data: 'bom_name',
                    name: 'bom_name',
                    className: 'text-nowrap'
                },
                {
                    data: 'remarks',
                    name: 'remarks',
                    className: 'text-nowrap'
                },

                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    sortable: false,
                    className: 'text-nowrap'
                },
            ],
            initComplete: function(settings, json) {
                var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl)
                })
                if (feather) {
                    feather.replace({
                        width: 14,
                        height: 14
                    });
                }
            },
            responsive: {
                details: {
                    display: $.fn.dataTable.Responsive.display.modal({
                        header: function(t) {
                            return "Details of : " + t.data().po_number
                        }
                    }),
                    type: "column",
                    renderer: function(t, a, e) {
                        e = $.map(e, function(t, a) {
                            return "" !== t.title ? '<tr data-dt-row="' + t.rowIndex + '" data-dt-column="' + t.columnIndex + '"><td>' + t.title + " :</td> <td>" + t.data + "</td></tr>" : ""
                        }).join("");
                        return !!e && $('<table class="table table-sm"/><tbody />').append(e)
                    }
                }
            }
        });
    });

    /*View Details Start*/
    $(document).on('click', '.view', function() {
        $('.modal').modal('hide');
        var id = $(this).data('id');
        var url = "{{route('bom.show','id')}}".replace('id', id);
        $.ajax({
            type: "GET",
            url: url,
            data: {
                "_token": "{{ csrf_token() }}",
            },
            success: function(data) {
                $('#appnedData').html('');
                $('#appnedData').html(data)
                $("#inlineModal").modal('show');
                $('#inlineModal').on('shown.bs.modal', function() {

                });
            },
            error: function(data) {
                swal(data.message, {
                    icon: "error",
                });
            }
        });
    });
    /*View Details End*/
</script>
<script src="{{asset('js/delete.js')}}"></script>
@endsection