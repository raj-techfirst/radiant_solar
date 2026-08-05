@extends('layouts.app')
@section('title', 'Calculator')

@section('content')
<style>
    .calc-table tr td,
    .calc-table tr th,
    .calc-table tr td input {
        padding: 3px;
    }
</style>
<div class="row">
    <div class="col-12 mb-1">
        <h4 class="content-header-title float-start">Calculator</h4>
        @can('rate-calculator-create')
        <a  href="{{ route('rate-calculator.create') }}"  class="btn btn-sm btn-primary float-end"><i class="fa fa-plus me-25"></i> {{ __('message.Add New') }}</a>
        @endcan
    </div>
    <div class="col-12">
        <div class="card p-1">
            <table id="product" class="datatables-basic table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('message.Action') }}</th>
                        <th>Name / Mobile</th>
                        <th>K/W</th>
                        
                        <th>Subtotal</th>
                        <th>GST</th>
                        <th>Total Amt</th>
                        <th>Per Watt</th>

                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>



                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-transparent border-bottom">
                <h4 class="text-center mb-0" id="exampleModalTitle">Details</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-2" id="body">

            </div>
        </div>
    </div>
</div>

@endsection

@section('pagescript')

<script type="application/javascript">
    'use strict';
    const URL = "{{route('rate-calculator.index')}}";


    var table = '';
    $(function() {


        
        table = $('#product').DataTable({
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
                },
                {
                    data: 'pv_capacity_kw',
                    name: 'pv_capacity_kw'
                },
                {
                    data: 'totalRate',
                    name: 'totalRate'
                },
                {
                    data: 'gst_amount',
                    name: 'gst_amount'
                },
                {
                    data: 'total_amount',
                    name: 'total_amount'
                },
                {
                    data: 'per_watt',
                    name: 'per_watt'
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
            }
        });
    });

    $(document).on('click', '.view', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        var url = "{{route('rate-calculator.show','id')}}".replace('id', id);
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
                if (feather) {
                    feather.replace({
                        width: 14,
                        height: 14
                    });
                }
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
                                toastr.success("{{ __('message.Deleted successfully.') }}", "{{ __('message.Success') }}");
                            } else if (response.data.status == false && response.data.server_error) {
                                toastr.error("{{ __('message.Something went wrong. Please try again.') }}", "{{ __('message.Error') }}");
                            } else {
                                toastr.warning("{{ __('message.This Note has been used.') }}", "{{ __('message.Warning') }}");
                            }
                        })
                        .catch(function() {
                            toastr.error("{{ __('message.Something went wrong. Please try again.') }}", "{{ __('message.Error') }}");
                        });
                } else {
                    Swal.fire({
                        text: "{{ __('message.Your data is safe.') }}"
                    });
                }
            });
    });
</script>
@endsection