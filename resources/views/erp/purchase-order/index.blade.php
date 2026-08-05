@extends('layouts.app')
@section('title', 'Purchase Order')
@section('content')
<div class="row">
    <div class="col-12">
        <h4 class="content-header-title float-start">Purchase Order List</h4>
        @can('purchase-order-create')
        <a href="{{route('purchase-order.create')}}" role="button" class="btn btn-sm btn-gradient-primary float-end"><i class="fa fa-plus me-25"></i> Add New</a>
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
                            <th>Date</th>
                            <th>PO Number</th>
                            <th>Supplier Name</th>
                            <th class="text-center">Total</th>
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
<div class="modal fade" id="inlineModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content" id="appnedData">
        </div>
    </div>
</div>
<div class="modal fade" id="reciveModal" tabindex="-1" aria-labelledby="reciveModal" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="appnedReaciveData">
        </div>
    </div>
</div>
<!-- start return po model form -->
<div class="modal fade" id="returnModal" tabindex="-1" aria-labelledby="returnModal" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="appendReturnData">
        </div>
    </div>
</div>
<!-- end return po model form -->

<!-- Modal for row details -->
<div class="modal fade" id="rowDetailsModal" tabindex="-1" aria-labelledby="rowDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rowDetailsModalLabel">Row Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <tbody id="modalRowDetails"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('pagescript')
<script type="application/javascript">
    'use strict';
    const URL = "{{route('purchase-order.index')}}";

    var table = '';
    var city_id = '';
    $(function() {
        table = $('#table').DataTable({
            ajax: {
                url: URL,
                data: function(d) {
                    d.from_date = $('#from_date').val();
                    d.to_date = $('#to_date').val();
                    d.vendor_id = $('#vendor_id').val();
                    d.currency_id = $('#currency_id').val();
                    d.status = $('#status').val();
                }
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
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    sortable: false,
                    className: 'text-nowrap'
                },
                {
                    data: 'purchase_date',
                    name: 'purchase_date',
                    className: 'text-nowrap'
                },
                {
                    data: 'po_number',
                    name: 'po_number',
                    className: 'text-nowrap'
                },
                {
                    data: 'supplier.name',
                    name: 'supplier.name'
                },
                {
                    data: 'total_amount',
                    name: 'total_amount',
                    className: 'text-nowrap text-end'
                },
                {
                    data: 'status',
                    name: 'status'
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
        var url = "{{route('purchase-order.show','id')}}".replace('id', id);
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


    /*Reacive Order Details Start*/
    $(document).on('click', '.receive', function() {
        $('.modal').modal('hide');
        var id = $(this).data('id');
        $.ajax({
            type: "POST",
            url: "{{route('purchase-receive')}}",
            data: {
                'id': id,
                "_token": "{{ csrf_token() }}",
            },
            success: function(response) {
                $('#appnedReaciveData').html('');
                $('#appnedReaciveData').html(response)
                $("#reciveModal").modal('show');
                if (feather) {
                    feather.replace({
                        width: 14,
                        height: 14
                    });
                }
                $('.touchspin').TouchSpin({
                    buttondown_class: 'btn btn-primary',
                    buttonup_class: 'btn btn-primary',
                    buttondown_txt: feather.icons['minus'].toSvg(),
                    buttonup_txt: feather.icons['plus'].toSvg()
                });
                $('.touchspin-color').each(function(index) {
                    var down = 'btn btn-primary',
                        up = 'btn btn-primary',
                        $this = $(this);
                    if ($this.data('bts-button-down-class')) {
                        down = $this.data('bts-button-down-class');
                    }
                    if ($this.data('bts-button-up-class')) {
                        up = $this.data('bts-button-up-class');
                    }
                    $this.TouchSpin({
                        mousewheel: false,
                        buttondown_class: down,
                        buttonup_class: up,
                        buttondown_txt: feather.icons['minus'].toSvg(),
                        buttonup_txt: feather.icons['plus'].toSvg()
                    });
                });
            },
            error: function(response) {
                Swal.fire({
                    title: "Error",
                    text: response.responseJSON.message,
                    icon: 'error'
                })
            }
        });
    });

    $(document).ready(function() {
        $("#formReceive").validate({
            rules: {
                invoice_number: {
                    required: true,
                }
            },
            messages: {
                invoice_number: {
                    required: "Enter invoice number"
                }
            },
            errorElement: "p",
            errorClass: "text-danger mb-0",

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
    });

    $(document).on('click', '.save-receive', function() {
        var formData = new FormData($("#formReceive")[0]);
        if ($("#formReceive").valid()) {
            $.ajax({
                type: "POST",
                url: "{{route('purchase-receive-store')}}",
                data: formData,
                dataType: 'json',
                cache: false,
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $(".save-receive").html(`<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Wait`);
                    $(".save-receive").attr('disabled', true);
                },
                success: function(response) {
                    $(".save-receive").html("Submit");
                    $(".save-receive").attr('disabled', false);
                    if (response.status_code == 500) {
                        Swal.fire({
                            title: "Error",
                            text: response.message,
                            icon: 'error'
                        })
                    } else if (response.status_code == 403) {
                        Swal.fire({
                            title: "Warning",
                            text: response.message,
                            icon: 'warning'
                        })
                    } else if (response.status_code == 201) {
                        $.each(response.errors, function(key, value) {
                            $('#error_' + key).html('<p class="text-danger mb-0">' + value + '</p>');
                        });
                        Swal.fire({
                            title: "Warning",
                            text: response.message,
                            icon: 'warning'
                        })
                    } else {
                        $('#formReceive')[0].reset();
                        Swal.fire({
                            title: "Success",
                            text: response.message,
                            icon: 'success'
                        })
                        setTimeout(function() {
                            location.href = response.data;
                        }, 500);
                    }
                }
            });
        } else {
            return false;
        }
    });
    /*Reacive Order End*/

    $(document).on('change', '.receive_qty', function() {
        $(this).closest('tr').find('.damage_qty').val('0');
        calculationQty($(this));
    });

    $(document).on('change', '.damage_qty', function() {
        calculationQty($(this));
    });

    $(document).on('change', '.missing_qty', function() {
        calculationQty($(this));
    });

    function calculationQty(me) {
        $(".remaining_quty").each(function() {
            let quantity = $(this).closest('tr').find('.quantity').val();
            let premaining_quty = ($(this).closest('tr').find('.premaining_quty').val() != '') ? $(this).closest('tr').find('.premaining_quty').val() : 0.0;
            let receive = ($(this).closest('tr').find('.receive_qty').val() != '') ? $(this).closest('tr').find('.receive_qty').val() : 0.0;
            let fqty = 0;
            if (premaining_quty > 0) {
                fqty = premaining_quty - receive;
            } else {
                fqty = quantity - receive;
            }
            const FinalQty = fqty;
            if (FinalQty > quantity) {
                if ((me.val() - 1) <= 0) {
                    me.val(me.val() - 1);
                } else {
                    me.val('0');
                    toastr.warning("You can not receive more than PO quantity.", "Warning");
                    $(this).closest('tr').find('.receive_qty').val('0');
                    $(this).closest('tr').find('.remaining_quty').val('0');
                }
                calculationQty(me);
                if (me.val() <= 0) {
                    me.val('0')
                }
            } else {
                $(this).closest('tr').find('.remaining_quty').val(FinalQty);
            }
        });
    }

    $('.table-responsive').on('show.bs.dropdown', function() {
        $('.table-responsive').css("overflow", "inherit");
    });

    $('.table-responsive').on('hide.bs.dropdown', function() {
        $('.table-responsive').css("overflow", "auto");
    });

    $(document).on('click', '.change-status', function() {
        let status = $(this).data('value');
        let id = $(this).data('id');
        Swal.fire({
            title: "Are you sure?",
            text: "You Want to change.!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: "Yes, change it!",
            customClass: {
                confirmButton: 'btn btn-gradient-primary',
                cancelButton: 'btn btn-outline-danger ms-1'
            },
            buttonsStyling: false
        }).then(function(result) {
            if (result.value) {
                $.ajax({
                    type: "POST",
                    url: "{{route('purchase-status-change')}}",
                    data: {
                        "status": status,
                        "id": id,
                        "_token": "{{ csrf_token() }}",
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status_code == 200) {
                            table.ajax.reload(null, false);
                            Swal.fire({
                                title: "Success",
                                text: response.message,
                                icon: 'success'
                            })
                        } else if (response.status_code == 201) {
                            Swal.fire({
                                title: "Warning",
                                text: response.message,
                                icon: 'warning'
                            })
                        } else {
                            Swal.fire({
                                title: "Error",
                                text: response.message,
                                icon: 'error'
                            })
                        }
                    }
                });
            }
        });
    });
</script>
<script src="{{asset('js/delete.js')}}"></script>
@endsection