@extends('layouts.app')
@section('title', 'Goods Receive Note')
@section('content')
<div class="row">
    <div class="col-12 mb-50">
        <h4 class="content-header-title float-start">Goods Receive Note</h4>
        
        <a href="{{ route('goods-receive-note.create') }}" class="btn btn-sm btn-primary float-end"><i class="fa fa-plus me-25"></i> Add New</a>
        
    </div>
    <div class="col-12">
        <div class="card p-1">
            <table id="table" class="datatables-basic table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Action</th>
                        <th>Date</th>
                        <th>Warehouse</th>
                        <th>Supplier</th>
                        <th>Invoice No.</th>
                        <th>GRN No.</th>
                        <th>Remark</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
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
    const URL = "{{route('goods-receive-note.index')}}";

    var table = '';
    $(function() {
        table = $('#table').DataTable({
            ajax: URL,
            processing: true,
            serverSide: true,
            fixedHeader: true,
            scrollX: true,
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
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    sortable: false
                },
                {
                    data: 'date',
                    name: 'date',
                },
                {
                    data: function(row) {
                        return row.warehouse && row.warehouse.name ? row.warehouse.name : '';
                    },
                    name: 'warehouse.name',
                    orderable: false,
                    sortable: false,
                },
                {
                    data: function(row) {
                        return row.supplier && row.supplier.name ? row.supplier.name : '';
                    },
                    name: 'supplier.name',
                    orderable: false,
                    sortable: false,
                },
                {
                    data: 'grn_number',
                    name: 'grn_number',
                },
                {
                    data: 'invoice_number',
                    name: 'invoice_number',
                },
                {
                    data: 'remark',
                    name: 'remark',
                },
                {
                    data: 'status',
                    name: 'status',
                    visible: false
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

    $(document).on('click', '.view', function() {
        $('.modal').modal('hide');
        var id = $(this).data('id');
        var url = "{{route('goods-receive-note.show','id')}}".replace('id', id);
        $.ajax({
            type: "GET",
            url: url,
            data: {
                "_token": "{{ csrf_token() }}",
                'id': id
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

    $(document).on('click', '.delete', function() {
        var btn = $(this);
        var id = btn.data('id');
        Swal.fire({
                title: "Are you sure?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: "Yes, Delete",
                cancelButtonText: "Cancel",
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
                            if (response.data.status_code == 200) {
                                table.ajax.reload(null, false);
                                toastr.success(response.data.message, "Success");
                            } else if (response.data.status_code == 201) {
                                toastr.warning(response.data.message, "Warning");
                            } else if (response.data.status_code == 403) {
                                toastr.warning(response.data.message, "Warning");
                            } else {
                                toastr.error(response.data.message, "Error");
                            }
                        })
                        .catch(function() {
                            toastr.error("Something went wrong. Please try again.",
                                "Error");
                        });
                }
            });
    });
</script>
@endsection