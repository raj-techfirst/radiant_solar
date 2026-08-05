@extends('layouts.app')
@section('title', 'Role Management')
@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="content-header-title float-start">Role Management</h4>
        @can('role-create')
            <a href="{{ route('roles.create') }}" class="btn btn-sm btn-primary float-end"><i class="fa fa-plus me-25"></i> Create New Role</a>
        @endcan
    </div>
    <div class="col-12">
        <div class="card p-1">
            <table id="table" class="datatables-basic table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Action</th>
                        <th>Name</th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('pagescript')
<script type="application/javascript">
    'use strict';
    const URL = "{{route('roles.index')}}";
    var table = '';
    $(function() {
        table = $('#table').DataTable({
            ajax: URL,
            processing: true,
            serverSide: true,
            fixedHeader: true,
            scrollX: true,
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
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    sortable: false
                },
                {
                    data: 'name',
                    name: 'name'
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

    $(document).on('click', '.delete', function() {
        var btn = $(this);
        var id = btn.data('id');
        Swal.fire({
                title: "Are you sure?",
                text: "You won`t be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: "Yes, delete it!",
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
                            } else {
                                toastr.error(response.data.message, "Error");
                            }
                        })
                        .catch(function() {
                            toastr.error("Something went wrong. Please try again.", "Error");
                        });
                } else {
                    Swal.fire({
                        text: "Your data is safe."
                    });
                }
            });
    });
</script>
@endsection